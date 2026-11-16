<?php

namespace App\Http\Controllers;

use App\Models\AiConfig;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\WebhookEndpoint;
use App\Services\Omnichannel\ChannelManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $channels = Channel::withCount('conversations')->get();
        $aiConfig = AiConfig::firstOrCreate([], [
            'name' => 'Default Omni AI Agent',
            'provider' => 'gemini',
            'model' => 'gemini-1.5-flash',
            'system_prompt' => 'You are a professional customer support representative.',
            'knowledge_base' => 'SocialMate CRM provides unified social inbox and AI auto-replies.',
            'auto_reply_enabled' => true,
            'handover_keywords' => ['human', 'agent', 'support', 'manush', 'kotha bolbo'],
        ]);

        $conversations = Conversation::with(['contact', 'channel', 'latestMessage'])
            ->orderByRaw('COALESCE(last_message_at, updated_at) DESC')
            ->get();

        $stats = [
            'total_contacts' => Contact::count(),
            'total_conversations' => Conversation::count(),
            'active_channels' => Channel::where('is_active', true)->count(),
            'total_deal_value' => Contact::sum('deal_value'),
        ];

        $webhooks = WebhookEndpoint::all();

        return view('dashboard', compact('channels', 'aiConfig', 'conversations', 'stats', 'webhooks'));
    }

    /**
     * Simulator: Test sending an inbound message as a customer from any platform.
     */
    public function simulateInbound(Request $request, ChannelManager $channelManager): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => 'nullable|exists:channels,id',
            'conversation_id' => 'nullable|exists:conversations,id',
            'sender_id' => 'nullable|string',
            'sender_name' => 'nullable|string',
            'content' => 'required|string',
        ]);

        if (!empty($validated['conversation_id'])) {
            $conv = Conversation::with(['channel', 'contact'])->findOrFail($validated['conversation_id']);
            $channel = $conv->channel;
            $senderId = $conv->contact->platform_sender_id ?: ('sim_' . $conv->contact_id);
            $senderName = $conv->contact->name ?: 'Customer';
        } else {
            $channel = Channel::findOrFail($validated['channel_id']);
            $senderId = $validated['sender_id'] ?: 'sim_default';
            $senderName = $validated['sender_name'] ?: 'Test User';
        }

        $message = $channelManager->processInboundMessage(
            channel: $channel,
            senderId: $senderId,
            senderName: $senderName,
            content: $validated['content'],
            platformMessageId: 'sim_' . uniqid()
        );

        return response()->json([
            'success' => true,
            'message' => 'Simulated message processed successfully',
            'data' => $message->load(['conversation.contact', 'conversation.channel']),
        ]);
    }
}
