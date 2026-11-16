<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Services\Omnichannel\ChannelManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class SocialWebhookController extends Controller
{
    public function __construct(
        protected ChannelManager $channelManager
    ) {}

    /**
     * Meta (Facebook Messenger / Instagram) Webhook Endpoint.
     * Handles both GET (verification) and POST (incoming events).
     */
    public function handleMeta(Request $request, Channel $channel): Response|JsonResponse
    {
        // 1. Meta Webhook Verification Handshake
        if ($request->isMethod('get')) {
            $mode = $request->query('hub_mode');
            $token = $request->query('hub_verify_token');
            $challenge = $request->query('hub_challenge');

            if ($mode === 'subscribe' && $token === ($channel->verify_token ?: 'social_crm_secret')) {
                return response($challenge, 200);
            }

            return response('Unauthorized', 403);
        }

        // 2. Incoming Event Payload
        $payload = $request->all();
        Log::info("Meta Webhook for Channel {$channel->id}:", $payload);

        // Acknowledge Meta immediately with 200 OK so they don't timeout or retry
        if (isset($payload['entry'])) {
            foreach ($payload['entry'] as $entry) {
                // Check for messaging array (Messenger / Instagram DM)
                if (isset($entry['messaging'])) {
                    foreach ($entry['messaging'] as $messagingEvent) {
                        if (isset($messagingEvent['message']['text']) && !isset($messagingEvent['message']['is_echo'])) {
                            $senderId = $messagingEvent['sender']['id'];
                            $text = $messagingEvent['message']['text'];
                            $msgId = $messagingEvent['message']['mid'] ?? null;

                            $this->channelManager->processInboundMessage(
                                channel: $channel,
                                senderId: (string) $senderId,
                                senderName: 'FB User ' . substr($senderId, -4),
                                content: $text,
                                platformMessageId: $msgId,
                                metadata: $messagingEvent
                            );
                        }
                    }
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Telegram Bot Webhook Endpoint.
     */
    public function handleTelegram(Request $request, Channel $channel): JsonResponse
    {
        $payload = $request->all();
        Log::info("Telegram Webhook for Channel {$channel->id}:", $payload);

        $this->channelManager->parseAndProcessTelegramUpdate($channel, $payload);

        return response()->json(['status' => 'ok']);
    }

    /**
     * WhatsApp Cloud API Webhook Endpoint.
     */
    public function handleWhatsApp(Request $request, Channel $channel): Response|JsonResponse
    {
        if ($request->isMethod('get')) {
            $mode = $request->query('hub_mode');
            $token = $request->query('hub_verify_token');
            $challenge = $request->query('hub_challenge');

            if ($mode === 'subscribe' && $token === ($channel->verify_token ?: 'social_crm_secret')) {
                return response($challenge, 200);
            }

            return response('Unauthorized', 403);
        }

        $payload = $request->all();
        Log::info("WhatsApp Webhook for Channel {$channel->id}:", $payload);

        if (isset($payload['entry'][0]['changes'][0]['value']['messages'][0])) {
            $msgObj = $payload['entry'][0]['changes'][0]['value']['messages'][0];
            $senderNumber = $msgObj['from'];
            $messageId = $msgObj['id'];

            $contactName = $payload['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'] ?? ('+' . $senderNumber);

            $text = '';
            if (isset($msgObj['text']['body'])) {
                $text = $msgObj['text']['body'];
            } elseif (isset($msgObj['button']['text'])) {
                $text = $msgObj['button']['text'];
            }

            if ($text) {
                $this->channelManager->processInboundMessage(
                    channel: $channel,
                    senderId: (string) $senderNumber,
                    senderName: $contactName,
                    content: $text,
                    platformMessageId: $messageId,
                    metadata: $msgObj
                );
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Universal Dynamic Inbound Webhook for any custom platform (LinkedIn, Discord, Slack, etc.)
     */
    public function handleCustom(Request $request, Channel $channel): Response|JsonResponse
    {
        if ($request->isMethod('get')) {
            if ($request->has('challenge')) {
                return response($request->query('challenge'), 200);
            }
            if ($request->has('hub_challenge')) {
                return response($request->query('hub_challenge'), 200);
            }
            return response()->json(['status' => 'webhook_active', 'channel' => $channel->name]);
        }

        $payload = $request->all();
        Log::info("Custom Dynamic Webhook for Channel {$channel->id} ({$channel->platform}):", $payload);

        $mapping = $channel->settings['field_mapping'] ?? [];

        $senderId = null;
        if (!empty($mapping['sender_id'])) {
            $senderId = data_get($payload, $mapping['sender_id']);
        }
        if (!$senderId) {
            $senderId = data_get($payload, 'sender_id')
                ?? data_get($payload, 'author.id')
                ?? data_get($payload, 'from.id')
                ?? data_get($payload, 'user.id')
                ?? data_get($payload, 'user_id')
                ?? data_get($payload, 'id')
                ?? ('user_' . rand(1000, 9999));
        }

        $senderName = null;
        if (!empty($mapping['sender_name'])) {
            $senderName = data_get($payload, $mapping['sender_name']);
        }
        if (!$senderName) {
            $senderName = data_get($payload, 'sender_name')
                ?? data_get($payload, 'author.username')
                ?? data_get($payload, 'author.name')
                ?? data_get($payload, 'from.first_name')
                ?? data_get($payload, 'user.name')
                ?? data_get($payload, 'username')
                ?? ('Contact ' . substr((string) $senderId, -4));
        }

        $content = null;
        if (!empty($mapping['content'])) {
            $content = data_get($payload, $mapping['content']);
        }
        if (!$content) {
            $content = data_get($payload, 'content')
                ?? data_get($payload, 'text')
                ?? data_get($payload, 'message')
                ?? data_get($payload, 'body')
                ?? data_get($payload, 'data.text');
        }

        if ($content) {
            $msgId = data_get($payload, 'message_id') ?? data_get($payload, 'id') ?? ('cust_' . uniqid());

            $this->channelManager->processInboundMessage(
                channel: $channel,
                senderId: (string) $senderId,
                senderName: (string) $senderName,
                content: (string) $content,
                platformMessageId: (string) $msgId,
                metadata: $payload
            );
        }

        return response()->json(['status' => 'success', 'received' => true]);
    }
}
