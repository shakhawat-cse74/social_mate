<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Conversation;
use App\Services\Omnichannel\ChannelManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebchatWidgetController extends Controller
{
    public function __construct(
        protected ChannelManager $channelManager
    ) {}

    /**
     * Send message from web chat widget.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => 'nullable|integer',
            'session_id' => 'required|string',
            'name' => 'nullable|string',
            'content' => 'required|string|max:2000',
        ]);

        $channel = null;
        if (!empty($validated['channel_id'])) {
            $channel = Channel::find($validated['channel_id']);
        }

        if (!$channel) {
            $channel = Channel::firstOrCreate(
                ['platform' => 'webchat'],
                [
                    'name' => 'Default Webchat Widget',
                    'is_active' => true,
                    'ai_enabled' => true,
                ]
            );
        }

        $message = $this->channelManager->processInboundMessage(
            channel: $channel,
            senderId: $validated['session_id'],
            senderName: $validated['name'] ?: 'Web Visitor',
            content: $validated['content'],
            platformMessageId: 'web_' . uniqid()
        );

        return response()->json([
            'status' => 'sent',
            'message' => $message,
        ]);
    }

    /**
     * Poll / fetch conversation messages for web widget session.
     */
    public function getMessages(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return response()->json(['messages' => []]);
        }

        $conversation = Conversation::whereHas('contact', fn($q) => $q->where('platform_sender_id', $sessionId))
            ->with(['messages'])
            ->first();

        return response()->json([
            'conversation_id' => $conversation?->id,
            'status' => $conversation?->status ?? 'new',
            'ai_mode' => $conversation?->ai_mode ?? 'active',
            'messages' => $conversation?->messages ?? [],
        ]);
    }
}
