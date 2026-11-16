<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\Omnichannel\ChannelManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(
        protected ChannelManager $channelManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Conversation::query()
            ->with(['contact', 'channel', 'latestMessage'])
            ->orderBy('last_message_at', 'desc');

        if ($request->filled('channel_id')) {
            $query->where('channel_id', $request->channel_id);
        }

        if ($request->filled('platform')) {
            $query->whereHas('channel', fn($q) => $q->where('platform', $request->platform));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('ai_mode')) {
            $query->where('ai_mode', $request->ai_mode);
        }

        $conversations = $query->paginate($request->get('per_page', 30));

        return response()->json($conversations);
    }

    public function show(Conversation $conversation): JsonResponse
    {
        $conversation->load(['contact', 'channel', 'messages']);
        
        // Reset unread count when opened
        $conversation->update(['unread_count' => 0]);

        return response()->json($conversation);
    }

    public function reply(Request $request, Conversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'nullable|string|max:5000',
            'sender_type' => 'nullable|string|in:human_agent,ai_agent,system',
            'attachment' => 'nullable|file|max:20480', // 20MB
            'attachment_url' => 'nullable|string|max:1000', // e.g. GIF or external link
            'attachment_type' => 'nullable|string|in:image,document,audio,video',
        ]);

        $senderType = $validated['sender_type'] ?? 'human_agent';
        $content = $validated['content'] ?? '';
        $messageType = 'text';
        $attachments = null;

        // Handle uploaded file
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $ext = strtolower($file->getClientOriginalExtension());
            $origName = $file->getClientOriginalName();
            $filename = time() . '_' . \Illuminate\Support\Str::slug(pathinfo($origName, PATHINFO_FILENAME)) . '.' . $ext;
            
            $path = $file->storeAs('attachments', $filename, 'public');
            $url = '/storage/' . $path;

            $isAudio = in_array($ext, ['mp3', 'wav', 'ogg', 'm4a', 'webm', 'weba', 'aac', 'flac', 'oga']) 
                || str_starts_with($file->getMimeType() ?? '', 'audio/')
                || $request->input('message_type') === 'audio';

            $messageType = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']) ? 'image' : ($isAudio ? 'audio' : 'document');
            $attachments = [[
                'url' => $url,
                'name' => $origName,
                'type' => $messageType,
                'size' => $file->getSize(),
            ]];
        } elseif (!empty($validated['attachment_url'])) {
            $url = $validated['attachment_url'];
            $type = $validated['attachment_type'] ?? 'image';
            $messageType = $type;
            $attachments = [[
                'url' => $url,
                'name' => basename(parse_url($url, PHP_URL_PATH) ?: 'attachment'),
                'type' => $type,
            ]];
        }

        if (empty($content) && empty($attachments)) {
            return response()->json(['message' => 'Please provide a message or attach a file.'], 422);
        }

        try {
            $message = $this->channelManager->sendOutboundMessage(
                conversation: $conversation,
                content: $content,
                senderType: $senderType,
                aiGenerated: false,
                messageType: $messageType,
                attachments: $attachments
            );

            return response()->json([
                'message' => 'Message sent successfully',
                'data' => $message,
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function react(Request $request, \App\Models\Message $message): JsonResponse
    {
        $validated = $request->validate([
            'emoji' => 'required|string|max:10',
        ]);

        $emoji = $validated['emoji'];
        $reactions = $message->reactions ?: [];

        // Toggle reaction count
        if (isset($reactions[$emoji])) {
            $reactions[$emoji]++;
        } else {
            $reactions[$emoji] = 1;
        }

        $message->update(['reactions' => $reactions]);

        // If on Telegram, send native reaction to Telegram message
        $conversation = $message->conversation;
        if ($conversation && $conversation->channel && $conversation->channel->platform === 'telegram') {
            try {
                $channel = $conversation->channel;
                $token = $channel->access_token;
                $msgId = str_replace('tg_', '', $message->platform_message_id ?? '');
                $chatId = $conversation->contact->platform_sender_id ?? null;

                if ($token && $msgId && $chatId && !str_starts_with($token, 'demo_')) {
                    \Illuminate\Support\Facades\Http::timeout(5)->post("https://api.telegram.org/bot{$token}/setMessageReaction", [
                        'chat_id' => $chatId,
                        'message_id' => (int) $msgId,
                        'reaction' => [['type' => 'emoji', 'emoji' => $emoji]],
                    ]);
                }
            } catch (\Throwable $e) {
                // Ignore reaction errors on unsupported Telegram plans
            }
        }

        return response()->json([
            'success' => true,
            'reactions' => $message->reactions,
        ]);
    }

    public function toggleAi(Request $request, Conversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'ai_mode' => 'required|string|in:active,paused,human_takeover',
        ]);

        $conversation->update(['ai_mode' => $validated['ai_mode']]);

        return response()->json([
            'message' => 'AI mode updated',
            'ai_mode' => $conversation->ai_mode,
        ]);
    }

    public function updateStatus(Request $request, Conversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:open,pending,resolved,closed',
        ]);

        $conversation->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Status updated',
            'status' => $conversation->status,
        ]);
    }

    public function initiateCall(Request $request, Conversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'call_type' => 'required|string|in:audio,video',
        ]);

        $callType = $validated['call_type'];
        $roomName = 'call_' . $conversation->id . '_' . substr(md5(uniqid()), 0, 8);
        $callUrl = url("/call/{$roomName}?role=customer&type={$callType}&conv={$conversation->id}");

        $platform = $conversation->channel?->platform ?? 'social';
        $platformName = ucfirst($platform);

        // Platform-tailored call invite
        $inviteText = $callType === 'video'
            ? "📹 Incoming Video Call from Support Agent\nPlatform: {$platformName}\n\n👉 Tap below to answer now:\n{$callUrl}"
            : "📞 Incoming Voice Call from Support Agent\nPlatform: {$platformName}\n\n👉 Tap below to answer now:\n{$callUrl}";

        $message = $this->channelManager->sendOutboundMessage(
            conversation: $conversation,
            content: $inviteText,
            senderType: 'human_agent',
            messageType: 'text'
        );

        return response()->json([
            'success' => true,
            'call_type' => $callType,
            'room_name' => $roomName,
            'call_url' => $callUrl,
            'agent_call_url' => url("/call/{$roomName}?role=agent&type={$callType}&conv={$conversation->id}"),
            'message' => $message,
        ]);
    }
}
