<?php

namespace App\Services\Omnichannel;

use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\AI\AiAgentService;
use App\Services\Webhook\WebhookDispatcher;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChannelManager
{
    /**
     * Process an inbound message from any social media channel or webchat.
     */
    public function processInboundMessage(
        Channel $channel,
        string $senderId,
        string $senderName,
        string $content,
        ?string $platformMessageId = null,
        ?string $avatarUrl = null,
        array $metadata = [],
        string $messageType = 'text',
        ?array $attachments = null
    ): Message {
        // 1. Deduplication check
        if ($platformMessageId) {
            $existing = Message::where('platform_message_id', $platformMessageId)->first();
            if ($existing) {
                return $existing;
            }
        }

        // 2. Find or create Contact (CRM Lead)
        $contact = Contact::firstOrCreate(
            [
                'tenant_id' => $channel->tenant_id,
                'platform' => $channel->platform,
                'platform_sender_id' => $senderId,
            ],
            [
                'name' => $senderName ?: 'User #' . substr($senderId, -4),
                'avatar_url' => $avatarUrl,
                'lead_status' => 'lead',
                'pipeline_stage' => 'new',
                'tags' => ['new_lead', $channel->platform],
                'last_active_at' => now(),
            ]
        );

        $contact->update(['last_active_at' => now()]);

        // 3. Find or create Conversation thread
        $conversation = Conversation::firstOrCreate(
            [
                'channel_id' => $channel->id,
                'contact_id' => $contact->id,
            ],
            [
                'status' => 'open',
                'ai_mode' => $channel->ai_enabled ? 'active' : 'paused',
                'unread_count' => 0,
            ]
        );

        // 4. Save Inbound Message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'contact_id' => $contact->id,
            'direction' => 'inbound',
            'sender_type' => 'contact',
            'platform_message_id' => $platformMessageId,
            'message_type' => $messageType,
            'content' => $content,
            'attachments' => $attachments,
            'status' => 'delivered',
            'metadata' => $metadata,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'unread_count' => $conversation->unread_count + 1,
            'status' => 'open',
        ]);

        // 5. Dispatch Webhook event to external connected projects
        app(WebhookDispatcher::class)->dispatch('message.received', [
            'message_id' => $message->id,
            'conversation_id' => $conversation->id,
            'platform' => $channel->platform,
            'channel_name' => $channel->name,
            'sender_id' => $senderId,
            'sender_name' => $contact->name,
            'content' => $content,
            'created_at' => $message->created_at->toIso8601String(),
        ]);

        // 6. Trigger AI Agent if conversation has AI active or channel-level AI is enabled
        $isAiActive = ($conversation->ai_mode === 'active') || ($channel->ai_enabled && $conversation->ai_mode !== 'human_takeover' && $conversation->ai_mode !== 'paused');
        if ($isAiActive) {
            app(AiAgentService::class)->handleInboundMessage($conversation, $message);
        }

        return $message;
    }

    /**
     * Send an outbound reply to the customer via the respective platform API.
     */
    public function sendOutboundMessage(
        Conversation $conversation,
        string $content,
        string $senderType = 'human_agent',
        bool $aiGenerated = false,
        string $messageType = 'text',
        ?array $attachments = null
    ): Message {
        $channel = $conversation->channel;
        $contact = $conversation->contact;

        if (!$channel || !$channel->is_active) {
            $channelName = $channel->name ?? 'Social Channel';
            throw new \RuntimeException("Cannot send message: '{$channelName}' is currently disconnected or paused. Please turn it on to resume sending.");
        }

        // Platform-specific API sending
        $platformMessageId = null;
        $status = 'sent';

        try {
            switch ($channel->platform) {
                case 'telegram':
                    $platformMessageId = $this->sendTelegramMessage($channel, $contact->platform_sender_id, $content, $messageType, $attachments);
                    break;

                case 'facebook':
                case 'instagram':
                    $platformMessageId = $this->sendMetaMessage($channel, $contact->platform_sender_id, $content);
                    break;

                case 'whatsapp':
                    $platformMessageId = $this->sendWhatsAppMessage($channel, $contact->platform_sender_id, $content);
                    break;

                case 'webchat':
                    $platformMessageId = 'web_' . uniqid();
                    break;

                default:
                    // Universal Dynamic Outbound HTTP Sender for any custom platform
                    $platformMessageId = $this->sendDynamicCustomMessage($channel, $contact->platform_sender_id, $content);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error("Failed to send outbound message via {$channel->platform}: " . $e->getMessage());
            $status = 'failed';
        }

        // Save message in DB
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'contact_id' => $contact->id,
            'direction' => 'outbound',
            'sender_type' => $senderType,
            'platform_message_id' => $platformMessageId,
            'message_type' => $messageType,
            'content' => $content ?: ($messageType === 'image' ? 'Image' : 'File Attachment'),
            'attachments' => $attachments,
            'status' => $status,
            'ai_generated' => $aiGenerated,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'unread_count' => 0,
        ]);

        // Dispatch Webhook to external connected apps
        app(WebhookDispatcher::class)->dispatch('message.sent', [
            'message_id' => $message->id,
            'conversation_id' => $conversation->id,
            'platform' => $channel->platform,
            'content' => $content,
            'sender_type' => $senderType,
            'ai_generated' => $aiGenerated,
            'message_type' => $messageType,
            'attachments' => $attachments,
        ]);

        return $message;
    }

    /**
     * Telegram Bot Send Message (supports text, photo, document, audio).
     */
    protected function sendTelegramMessage(Channel $channel, string $chatId, string $text, string $messageType = 'text', ?array $attachments = null): ?string
    {
        $botToken = $channel->access_token;
        if (!$botToken) {
            return null;
        }

        // 1. Send with Photo Attachment
        if ($messageType === 'image' && !empty($attachments[0]['url'])) {
            $att = $attachments[0];
            $localRel = str_replace('/storage/', '', $att['url']);
            $fullPath = storage_path('app/public/' . $localRel);

            if (file_exists($fullPath)) {
                $response = Http::timeout(20)
                    ->attach('photo', file_get_contents($fullPath), basename($fullPath))
                    ->post("https://api.telegram.org/bot{$botToken}/sendPhoto", [
                        'chat_id' => $chatId,
                        'caption' => $text ?: null,
                    ]);

                if ($response->successful()) {
                    return (string) $response->json('result.message_id');
                }
                Log::warning('Telegram sendPhoto error: ' . $response->body());
            }
        }

        // 2. Send with Document / File Attachment
        if ($messageType === 'document' && !empty($attachments[0]['url'])) {
            $att = $attachments[0];
            $localRel = str_replace('/storage/', '', $att['url']);
            $fullPath = storage_path('app/public/' . $localRel);

            if (file_exists($fullPath)) {
                $response = Http::timeout(30)
                    ->attach('document', file_get_contents($fullPath), $att['name'] ?? basename($fullPath))
                    ->post("https://api.telegram.org/bot{$botToken}/sendDocument", [
                        'chat_id' => $chatId,
                        'caption' => $text ?: null,
                    ]);

                if ($response->successful()) {
                    return (string) $response->json('result.message_id');
                }
                Log::warning('Telegram sendDocument error: ' . $response->body());
            }
        }

        // 3. Send with Voice / Audio Attachment
        if (($messageType === 'audio' || $messageType === 'voice') && !empty($attachments[0]['url'])) {
            $att = $attachments[0];
            $localRel = str_replace('/storage/', '', $att['url']);
            $fullPath = storage_path('app/public/' . $localRel);

            if (file_exists($fullPath)) {
                // Try sendVoice first (for voice notes)
                $response = Http::timeout(30)
                    ->attach('voice', file_get_contents($fullPath), $att['name'] ?? 'voice_message.ogg')
                    ->post("https://api.telegram.org/bot{$botToken}/sendVoice", [
                        'chat_id' => $chatId,
                        'caption' => $text ?: null,
                    ]);

                if ($response->successful()) {
                    return (string) $response->json('result.message_id');
                }

                // Fallback to sendAudio
                $response = Http::timeout(30)
                    ->attach('audio', file_get_contents($fullPath), $att['name'] ?? 'audio.mp3')
                    ->post("https://api.telegram.org/bot{$botToken}/sendAudio", [
                        'chat_id' => $chatId,
                        'caption' => $text ?: null,
                    ]);

                if ($response->successful()) {
                    return (string) $response->json('result.message_id');
                }
                Log::warning('Telegram sendVoice/sendAudio error: ' . $response->body());
            }
        }

        // 4. Regular Text Message or Interactive Call Card
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        // If message contains a call URL, attach a Telegram 1-click Answer Call button
        if (preg_match('/(https?:\/\/[^\s]+)/', $text, $matches)) {
            $url = $matches[1];
            if (str_contains($text, 'Call') || str_contains($text, 'কল')) {
                $isWebapp = str_starts_with($url, 'https://');
                $button = ['text' => '📞 Answer Call / কল রিসিভ করুন', 'url' => $url];
                if ($isWebapp) {
                    $button = ['text' => '📞 Answer Call (ইন-অ্যাপ)', 'web_app' => ['url' => $url]];
                }
                $payload['reply_markup'] = [
                    'inline_keyboard' => [
                        [$button]
                    ]
                ];
            }
        }

        $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", $payload);

        if ($response->successful()) {
            return (string) $response->json('result.message_id');
        }

        Log::warning('Telegram send error: ' . $response->body());
        return null;
    }

    /**
     * Download Telegram file and store locally in public attachments storage.
     */
    public function downloadTelegramFile(string $botToken, string $fileId, string $defaultName = 'file.jpg'): ?array
    {
        try {
            $infoRes = Http::timeout(10)->get("https://api.telegram.org/bot{$botToken}/getFile", [
                'file_id' => $fileId,
            ]);

            if ($infoRes->successful() && !empty($infoRes->json('result.file_path'))) {
                $filePath = $infoRes->json('result.file_path');
                $downloadUrl = "https://api.telegram.org/file/bot{$botToken}/{$filePath}";

                $fileBinary = Http::timeout(20)->get($downloadUrl)->body();
                $ext = pathinfo($filePath, PATHINFO_EXTENSION) ?: pathinfo($defaultName, PATHINFO_EXTENSION) ?: 'jpg';
                $localFileName = 'tg_' . uniqid() . '.' . $ext;

                \Illuminate\Support\Facades\Storage::disk('public')->put('attachments/' . $localFileName, $fileBinary);
                $localUrl = '/storage/attachments/' . $localFileName;

                return [
                    'url' => $localUrl,
                    'name' => $defaultName ?: $localFileName,
                    'type' => in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? 'image' : (in_array(strtolower($ext), ['ogg', 'mp3', 'wav', 'm4a']) ? 'audio' : 'document'),
                    'size' => strlen($fileBinary),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to download Telegram file {$fileId}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Universal Parser for Telegram Updates (photos, documents, audio, stickers, text, reactions).
     */
    public function parseAndProcessTelegramUpdate(Channel $channel, array $up): ?Message
    {
        if (!$channel->is_active) return null;

        // 1. Handle reaction update
        if (isset($up['message_reaction'])) {
            $rx = $up['message_reaction'];
            $msgId = 'tg_' . ($rx['message_id'] ?? '');
            $message = Message::where('platform_message_id', $msgId)->first();
            if ($message) {
                $emojis = [];
                foreach ($rx['new_reaction'] ?? [] as $r) {
                    if (isset($r['emoji'])) {
                        $emojis[$r['emoji']] = ($emojis[$r['emoji']] ?? 0) + 1;
                    }
                }
                $message->update(['reactions' => $emojis]);
                return $message;
            }
        }

        $msg = $up['message'] ?? null;
        if (!$msg) return null;

        $from = $msg['from'] ?? [];
        $chat = $msg['chat'] ?? [];
        $senderId = (string) ($chat['id'] ?? $from['id'] ?? '');
        if (!$senderId) return null;

        $name = trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? ''));
        if (!$name && isset($from['username'])) {
            $name = '@' . $from['username'];
        }

        $msgId = 'tg_' . ($msg['message_id'] ?? uniqid());
        $token = $channel->access_token;
        $content = $msg['text'] ?? $msg['caption'] ?? '';
        $messageType = 'text';
        $attachments = [];

        // Check for Photos
        if (!empty($msg['photo']) && is_array($msg['photo'])) {
            $largest = end($msg['photo']);
            $fileId = $largest['file_id'] ?? null;
            if ($fileId && $token && !str_starts_with($token, 'demo_')) {
                $downloaded = $this->downloadTelegramFile($token, $fileId, 'photo.jpg');
                if ($downloaded) {
                    $attachments[] = $downloaded;
                    $messageType = 'image';
                    if (!$content) $content = 'Photo';
                }
            }
        }
        // Check for Document / File
        elseif (!empty($msg['document'])) {
            $doc = $msg['document'];
            $fileId = $doc['file_id'] ?? null;
            $fileName = $doc['file_name'] ?? 'document.pdf';
            if ($fileId && $token && !str_starts_with($token, 'demo_')) {
                $downloaded = $this->downloadTelegramFile($token, $fileId, $fileName);
                if ($downloaded) {
                    $attachments[] = $downloaded;
                    $messageType = 'document';
                    if (!$content) $content = $fileName;
                }
            }
        }
        // Check for Voice / Audio
        elseif (!empty($msg['voice']) || !empty($msg['audio'])) {
            $audio = $msg['voice'] ?? $msg['audio'];
            $fileId = $audio['file_id'] ?? null;
            if ($fileId && $token && !str_starts_with($token, 'demo_')) {
                $downloaded = $this->downloadTelegramFile($token, $fileId, 'voice.ogg');
                if ($downloaded) {
                    $attachments[] = $downloaded;
                    $messageType = 'audio';
                    if (!$content) $content = 'Voice Message';
                }
            }
        }
        // Check for Sticker
        elseif (!empty($msg['sticker'])) {
            $messageType = 'sticker';
            $content = $msg['sticker']['emoji'] ?? '🎨';
        }

        if (!$content && empty($attachments)) {
            return null;
        }

        return $this->processInboundMessage(
            channel: $channel,
            senderId: $senderId,
            senderName: $name ?: 'Telegram User',
            content: $content,
            platformMessageId: $msgId,
            metadata: $up,
            messageType: $messageType,
            attachments: !empty($attachments) ? $attachments : null
        );
    }

    /**
     * Meta (Facebook Messenger / Instagram DM) Send API.
     */
    protected function sendMetaMessage(Channel $channel, string $recipientPsid, string $text): ?string
    {
        $pageAccessToken = $channel->access_token;
        if (!$pageAccessToken) {
            return null;
        }

        $messagePayload = ['text' => $text];
        if (preg_match('/(https?:\/\/[^\s]+)/', $text, $matches) && (str_contains($text, 'Call') || str_contains($text, 'কল'))) {
            $url = $matches[1];
            $messagePayload = [
                'attachment' => [
                    'type' => 'template',
                    'payload' => [
                        'template_type' => 'button',
                        'text' => mb_substr($text, 0, 600),
                        'buttons' => [
                            [
                                'type' => 'web_url',
                                'url' => $url,
                                'title' => '📞 Answer Call'
                            ]
                        ]
                    ]
                ]
            ];
        }

        $response = Http::post("https://graph.facebook.com/v19.0/me/messages?access_token={$pageAccessToken}", [
            'recipient' => ['id' => $recipientPsid],
            'message' => $messagePayload,
            'messaging_type' => 'RESPONSE',
        ]);

        if ($response->successful()) {
            return (string) $response->json('message_id');
        }

        Log::warning('Meta send error: ' . $response->body());
        return null;
    }

    /**
     * WhatsApp Cloud API Send Message.
     */
    protected function sendWhatsAppMessage(Channel $channel, string $toPhoneNumber, string $text): ?string
    {
        $phoneNumberId = $channel->platform_account_id;
        $accessToken = $channel->access_token;

        if (!$phoneNumberId || !$accessToken) {
            return null;
        }

        $response = Http::withToken($accessToken)
            ->post("https://graph.facebook.com/v19.0/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $toPhoneNumber,
                'type' => 'text',
                'text' => ['preview_url' => false, 'body' => $text],
            ]);

        if ($response->successful()) {
            return (string) $response->json('messages.0.id');
        }

        Log::warning('WhatsApp send error: ' . $response->body());
        return null;
    }

    /**
     * Universal Dynamic Outbound Sender for custom added platforms (LinkedIn, Discord, Slack, etc.)
     */
    protected function sendDynamicCustomMessage(Channel $channel, string $recipientId, string $text): ?string
    {
        $settings = $channel->settings ?? [];
        $apiUrl = $settings['outbound_api_url'] ?? null;

        if (!$apiUrl) {
            return 'custom_' . uniqid();
        }

        $headers = [];
        if (!empty($channel->access_token)) {
            $headers['Authorization'] = 'Bearer ' . $channel->access_token;
        }
        if (!empty($settings['custom_headers']) && is_array($settings['custom_headers'])) {
            $headers = array_merge($headers, $settings['custom_headers']);
        }

        $bodyTemplate = $settings['outbound_body_template'] ?? null;
        $body = [];
        if ($bodyTemplate) {
            $rendered = str_replace(
                ['{{recipient_id}}', '{{sender_id}}', '{{content}}', '{{text}}'],
                [$recipientId, $recipientId, $text, $text],
                $bodyTemplate
            );
            $decoded = json_decode($rendered, true);
            $body = $decoded ?: ['recipient' => $recipientId, 'message' => $text];
        } else {
            $body = [
                'recipient_id' => $recipientId,
                'message' => $text,
                'channel_id' => $channel->id,
            ];
        }

        try {
            $response = Http::withHeaders($headers)->timeout(8)->post($apiUrl, $body);
            if ($response->successful()) {
                return (string) ($response->json('id') ?? $response->json('message_id') ?? ('out_' . uniqid()));
            }
            Log::warning("Dynamic channel {$channel->platform} outbound response error: " . $response->body());
        } catch (\Throwable $e) {
            Log::error("Dynamic channel {$channel->platform} outbound exception: " . $e->getMessage());
        }

        return 'custom_' . uniqid();
    }

    /**
     * Poll incoming Facebook messages from Meta Graph API conversations (works on localhost/local development).
     */
    public function syncFacebookPage(Channel $channel): int
    {
        if (!$channel->is_active || empty($channel->access_token) || str_starts_with($channel->access_token, 'demo_')) {
            return 0;
        }

        $pageId = $channel->platform_account_id;
        $token = $channel->access_token;
        if (!$pageId) return 0;

        $url = "https://graph.facebook.com/v20.0/{$pageId}/conversations";
        try {
            $response = Http::timeout(10)->get($url, [
                'fields' => 'id,snippet,updated_time,messages{id,message,from,created_time,attachments}',
                'access_token' => $token,
            ]);

            if (!$response->successful()) {
                Log::warning("Facebook Sync Failed for Channel {$channel->id}: " . $response->body());
                return 0;
            }

            $data = $response->json();
            $count = 0;

            if (!empty($data['data'])) {
                foreach ($data['data'] as $convThread) {
                    $messages = array_reverse($convThread['messages']['data'] ?? []);
                    foreach ($messages as $msg) {
                        $msgId = $msg['id'] ?? null;
                        if (!$msgId) continue;

                        $exists = Message::where('platform_message_id', $msgId)->exists();
                        if ($exists) continue;

                        $fromId = (string)($msg['from']['id'] ?? '');
                        $fromName = $msg['from']['name'] ?? 'Facebook User';
                        $isPage = ($fromId === (string)$pageId);

                        if ($isPage) {
                            continue; // Skip echo of page's own automated replies
                        }

                        $content = $msg['message'] ?? '';
                        $attachments = [];
                        if (!empty($msg['attachments']['data'])) {
                            foreach ($msg['attachments']['data'] as $att) {
                                $attUrl = $att['image_data']['url'] ?? ($att['file_url'] ?? null);
                                if ($attUrl) {
                                    $attachments[] = [
                                        'url' => $attUrl,
                                        'type' => !empty($att['image_data']) ? 'image' : 'file',
                                        'name' => $att['name'] ?? 'attachment',
                                    ];
                                }
                            }
                        }

                        $this->processInboundMessage(
                            channel: $channel,
                            senderId: $fromId,
                            senderName: $fromName,
                            content: $content ?: ($attachments ? '[Attachment]' : 'Message'),
                            platformMessageId: $msgId,
                            messageType: $attachments ? ($attachments[0]['type'] ?? 'image') : 'text',
                            attachments: $attachments ?: null,
                            metadata: [
                                'fb_thread_id' => $convThread['id'] ?? null,
                                'created_time' => $msg['created_time'] ?? null
                            ]
                        );
                        $count++;
                    }
                }
            }

            return $count;
        } catch (\Throwable $e) {
            Log::warning("Facebook Sync Exception: " . $e->getMessage());
            return 0;
        }
    }
}

