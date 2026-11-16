<?php

namespace App\Http\Controllers;

use App\Models\AiConfig;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\QuickReply;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TenantPortalController extends Controller
{
    protected function getTenant()
    {
        $tenant = Auth::user()->tenant;
        if (!$tenant) {
            abort(403, 'No tenant account associated with this user.');
        }
        return $tenant;
    }

    public function dashboard()
    {
        $tenant = $this->getTenant();

        $stats = [
            'total_conversations' => Conversation::whereHas('channel', fn($q) => $q->where('tenant_id', $tenant->id))->count(),
            'total_contacts' => Contact::where('tenant_id', $tenant->id)->count(),
            'total_deal_value' => Contact::where('tenant_id', $tenant->id)->sum('deal_value'),
            'connected_channels' => Channel::where('tenant_id', $tenant->id)->where('is_active', true)->count(),
            'active_agents' => User::where('tenant_id', $tenant->id)->count(),
        ];

        $recentConversations = Conversation::whereHas('channel', fn($q) => $q->where('tenant_id', $tenant->id))
            ->with(['contact', 'channel', 'latestMessage'])
            ->orderBy('last_message_at', 'desc')
            ->take(5)
            ->get();

        return view('tenant.dashboard', compact('tenant', 'stats', 'recentConversations'));
    }

    public function inbox()
    {
        $tenant = $this->getTenant();

        $channels = Channel::where('tenant_id', $tenant->id)->get();

        // Auto-poll telegram & facebook in background if active (seamless for local development)
        $channelManager = app(\App\Services\Omnichannel\ChannelManager::class);
        $tg = $channels->where('platform', 'telegram')->where('is_active', true)->first();
        if ($tg && $tg->access_token && !str_starts_with($tg->access_token, 'demo_')) {
            try {
                $this->pollTelegramUpdates($tg);
            } catch (\Throwable $e) {}
        }

        $fb = $channels->where('platform', 'facebook')->where('is_active', true)->first();
        if ($fb && $fb->access_token && !str_starts_with($fb->access_token, 'demo_')) {
            try {
                $channelManager->syncFacebookPage($fb);
            } catch (\Throwable $e) {}
        }

        $conversations = Conversation::whereHas('channel', fn($q) => $q->withTrashed()->where('tenant_id', $tenant->id))
            ->with(['contact', 'channel', 'latestMessage'])
            ->orderByRaw('COALESCE(last_message_at, updated_at) DESC')
            ->get();

        $this->ensureDefaultQuickReplies($tenant);
        $quickReplies = QuickReply::where('tenant_id', $tenant->id)->orderBy('id', 'asc')->get();

        return view('tenant.inbox', compact('tenant', 'channels', 'conversations', 'quickReplies'));
    }

    public function pollInbox(Request $request)
    {
        $tenant = $this->getTenant();
        $channels = Channel::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();

        $channelManager = app(\App\Services\Omnichannel\ChannelManager::class);
        $syncedCount = 0;
        foreach ($channels as $ch) {
            try {
                if ($ch->platform === 'telegram') {
                    $syncedCount += $this->pollTelegramUpdates($ch);
                } elseif ($ch->platform === 'facebook') {
                    $syncedCount += $channelManager->syncFacebookPage($ch);
                }
            } catch (\Throwable $e) {}
        }

        $conversationId = $request->input('conversation_id');
        $messages = [];
        if ($conversationId) {
            $conversation = Conversation::where('id', $conversationId)
                ->whereHas('channel', fn($q) => $q->withTrashed()->where('tenant_id', $tenant->id))
                ->with(['messages', 'contact'])
                ->first();
            if ($conversation) {
                $messages = $conversation->messages;
            }
        }

        $conversations = Conversation::whereHas('channel', fn($q) => $q->withTrashed()->where('tenant_id', $tenant->id))
            ->with(['contact', 'channel', 'latestMessage'])
            ->orderByRaw('COALESCE(last_message_at, updated_at) DESC')
            ->take(30)
            ->get();

        return response()->json([
            'success' => true,
            'synced_count' => $syncedCount,
            'messages' => $messages,
            'conversations' => $conversations,
        ]);
    }

    protected function pollTelegramUpdates(Channel $channel): int
    {
        if (!$channel->is_active) return 0;

        $token = $channel->access_token;
        if (!$token || str_starts_with($token, 'demo_')) return 0;

        $res = \Illuminate\Support\Facades\Http::timeout(4)->get("https://api.telegram.org/bot{$token}/getUpdates");
        $data = $res->json();
        $count = 0;
        $highestUpdateId = 0;

        if (!empty($data['result'])) {
            $channelManager = app(\App\Services\Omnichannel\ChannelManager::class);
            foreach ($data['result'] as $up) {
                $highestUpdateId = max($highestUpdateId, $up['update_id'] ?? 0);
                $saved = $channelManager->parseAndProcessTelegramUpdate($channel, $up);
                if ($saved) {
                    $count++;
                }
            }

            if ($highestUpdateId > 0) {
                \Illuminate\Support\Facades\Http::get("https://api.telegram.org/bot{$token}/getUpdates", [
                    'offset' => $highestUpdateId + 1,
                ]);
            }
        }

        return $count;
    }

    public function contacts()
    {
        $tenant = $this->getTenant();
        $contacts = Contact::where('tenant_id', $tenant->id)->latest('last_active_at')->paginate(20);

        return view('tenant.contacts', compact('tenant', 'contacts'));
    }

    public function pipeline()
    {
        $tenant = $this->getTenant();
        $contacts = Contact::where('tenant_id', $tenant->id)->get();
        $totalDealValue = $contacts->sum('deal_value');

        return view('tenant.pipeline', compact('tenant', 'contacts', 'totalDealValue'));
    }

    public function channels()
    {
        $tenant = $this->getTenant();
        $channels = Channel::where('tenant_id', $tenant->id)->get();
        $platforms = \App\Models\Platform::where(function($q) use ($tenant) {
            $q->whereNull('tenant_id')->orWhere('tenant_id', $tenant->id);
        })->where('is_active', true)->get();

        return view('tenant.channels', compact('tenant', 'channels', 'platforms'));
    }

    public function storePlatform(Request $request)
    {
        $tenant = $this->getTenant();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100',
            'short_code' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:30',
            'description' => 'nullable|string|max:255',
            'outbound_api_url' => 'nullable|url',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['slug'] ?: $validated['name'], '_');
        $shortCode = strtoupper($validated['short_code'] ?: substr($validated['name'], 0, 3));
        $color = $validated['color'] ?: '#6366f1';

        $platform = \App\Models\Platform::updateOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => $slug],
            [
                'name' => $validated['name'],
                'short_code' => $shortCode,
                'color' => $color,
                'description' => $validated['description'] ?? "Custom integration for {$validated['name']}",
                'outbound_api_url' => $validated['outbound_api_url'] ?? null,
                'is_builtin' => false,
                'is_active' => true,
            ]
        );

        $allowed = $tenant->allowed_channels ?? [];
        if (!in_array($slug, $allowed)) {
            $allowed[] = $slug;
            $tenant->update(['allowed_channels' => $allowed]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Platform '{$platform->name}' added to your available platforms list!",
                'platform' => $platform,
            ]);
        }

        return back()->with('success', "Platform '{$platform->name}' added to your available platforms list!");
    }

    public function destroyPlatform(\App\Models\Platform $platform, Request $request)
    {
        $tenant = $this->getTenant();
        if ($platform->is_builtin || ($platform->tenant_id && $platform->tenant_id !== $tenant->id)) {
            abort(403, 'Built-in platforms cannot be deleted.');
        }

        $name = $platform->name;
        $platform->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Platform '{$name}' removed."]);
        }

        return back()->with('success', "Platform '{$name}' removed.");
    }

    public function connectChannel(Request $request, \App\Services\Omnichannel\ChannelConnectionService $connectionService)
    {
        $tenant = $this->getTenant();
        $platform = $request->input('platform');

        // Check if tenant's plan allows this channel or if it is a custom added platform
        $allowed = $tenant->allowed_channels ?? ['facebook', 'whatsapp', 'telegram', 'instagram', 'webchat'];
        $isPlatformValid = in_array($platform, $allowed) || \App\Models\Platform::where('slug', $platform)->exists();

        if (!$isPlatformValid) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => "The '{$platform}' channel is not included in your current subscription plan."], 403);
            }
            return back()->with('error', "The '{$platform}' channel is not included in your current subscription plan. Please contact support to upgrade.");
        }

        $result = $connectionService->connect($tenant, $platform, $request->all());

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function testChannel(Channel $channel, \App\Services\Omnichannel\ChannelConnectionService $connectionService, Request $request)
    {
        $tenant = $this->getTenant();
        if ($channel->tenant_id !== $tenant->id) abort(403);

        $result = $connectionService->testConnection($channel);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function toggleChannelStatus(Channel $channel, Request $request)
    {
        $tenant = $this->getTenant();
        if ($channel->tenant_id !== $tenant->id) abort(403);

        $channel->update(['is_active' => !$channel->is_active]);

        $statusText = $channel->is_active ? 'Active & Connected' : 'Paused / Disconnected';
        $message = "Channel '{$channel->name}' is now {$statusText}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $channel->is_active,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function toggleChannelAi(Channel $channel)
    {
        $tenant = $this->getTenant();
        if ($channel->tenant_id !== $tenant->id) abort(403);

        $channel->update(['ai_enabled' => !$channel->ai_enabled]);

        return back()->with('success', "AI auto-reply for '{$channel->name}' is now " . ($channel->ai_enabled ? 'Enabled' : 'Disabled') . ".");
    }

    public function syncTelegram(Channel $channel, \App\Services\Omnichannel\ChannelManager $channelManager, Request $request)
    {
        $tenant = $this->getTenant();
        if ($channel->tenant_id !== $tenant->id) abort(403);

        $token = $channel->access_token;
        if (!$token || str_starts_with($token, 'demo_')) {
            if ($request->wantsJson()) return response()->json(['success' => true, 'count' => 0, 'message' => 'Demo channel.']);
            return back()->with('info', 'Demo channel does not require live polling.');
        }

        if ($channel->platform === 'facebook') {
            $count = $channelManager->syncFacebookPage($channel);
            $msg = $count > 0 ? "Synced {$count} new messages from Facebook Page '{$channel->name}'!" : "Facebook checked: No new messages in queue.";
            if ($request->wantsJson()) return response()->json(['success' => true, 'count' => $count, 'message' => $msg]);
            return back()->with('success', $msg);
        }

        $res = \Illuminate\Support\Facades\Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getUpdates");
        $data = $res->json();
        $count = 0;
        $highestUpdateId = 0;

        if (!empty($data['result'])) {
            foreach ($data['result'] as $up) {
                $highestUpdateId = max($highestUpdateId, $up['update_id'] ?? 0);
                $saved = $channelManager->parseAndProcessTelegramUpdate($channel, $up);
                if ($saved) {
                    $count++;
                }
            }

            // Confirm receipt to Telegram
            if ($highestUpdateId > 0) {
                \Illuminate\Support\Facades\Http::get("https://api.telegram.org/bot{$token}/getUpdates", [
                    'offset' => $highestUpdateId + 1,
                ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => $count > 0 ? "Synced {$count} new messages from Telegram!" : "Telegram checked: No new messages in queue.",
            ]);
        }

        return back()->with('success', $count > 0 ? "Synced {$count} new messages from Telegram!" : "Telegram checked: No new messages.");
    }

    public function deleteChannel(Channel $channel)
    {
        $tenant = $this->getTenant();
        if ($channel->tenant_id !== $tenant->id) abort(403);

        $name = $channel->name;
        // Soft delete preserves all customer conversation history permanently
        $channel->delete();

        return back()->with('success', "Channel '{$name}' credentials removed. Chat history is preserved.");
    }

    public function aiBot()
    {
        $tenant = $this->getTenant();

        if (!$tenant->ai_enabled) {
            return redirect()->route('tenant.dashboard')->with('error', 'AI Agent is not included in your current subscription plan.');
        }

        $aiConfig = AiConfig::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'name' => $tenant->name . ' AI Assistant',
                'is_active' => true,
                'provider' => 'gemini',
                'model' => 'gemini-1.5-flash',
                'system_prompt' => "You are an intelligent support assistant for {$tenant->name}. Help customers with product questions, pricing, and service inquiries.",
                'knowledge_base' => "Company: {$tenant->name}\nSupport: 24/7 online chat support.",
                'auto_reply_enabled' => true,
                'handover_keywords' => ['human', 'agent', 'support', 'manush', 'kotha bolbo', 'help'],
            ]
        );

        return view('tenant.ai-bot', compact('tenant', 'aiConfig'));
    }

    public function updateAiBot(Request $request)
    {
        $tenant = $this->getTenant();
        if (!$tenant->ai_enabled) abort(403);

        $aiConfig = AiConfig::firstOrCreate(['tenant_id' => $tenant->id]);

        $validated = $request->validate([
            'provider' => 'nullable|string|in:gemini,openai',
            'model' => 'nullable|string|max:50',
            'api_key' => 'nullable|string|max:255',
            'system_prompt' => 'required|string',
            'knowledge_base' => 'required|string',
            'handover_keywords' => 'nullable|string',
            'auto_reply_enabled' => 'nullable|boolean',
        ]);

        $keywords = array_map('trim', explode(',', $validated['handover_keywords'] ?? ''));

        $updateData = [
            'provider' => $validated['provider'] ?? 'gemini',
            'model' => $validated['model'] ?? ($validated['provider'] === 'openai' ? 'gpt-4o-mini' : 'gemini-1.5-flash'),
            'system_prompt' => $validated['system_prompt'],
            'knowledge_base' => $validated['knowledge_base'],
            'handover_keywords' => array_values(array_filter($keywords)),
            'auto_reply_enabled' => $request->boolean('auto_reply_enabled'),
        ];

        if ($request->filled('api_key')) {
            $updateData['api_key'] = trim($request->input('api_key'));
        }

        $aiConfig->update($updateData);

        return back()->with('success', 'AI Chatbot & Knowledge Base updated successfully!');
    }

    public function agents()
    {
        $tenant = $this->getTenant();
        $agents = User::where('tenant_id', $tenant->id)->get();

        return view('tenant.agents', compact('tenant', 'agents'));
    }

    public function storeAgent(Request $request)
    {
        $tenant = $this->getTenant();

        // Check max agent limit
        $currentCount = User::where('tenant_id', $tenant->id)->count();
        if ($currentCount >= $tenant->max_agents) {
            return back()->with('error', "Your plan allows a maximum of {$tenant->max_agents} agents. Please upgrade to add more.");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:tenant_admin,agent',
            'permissions' => 'nullable|array',
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'permissions' => $validated['permissions'] ?? ['can_reply', 'can_manage_leads'],
            'is_active' => true,
        ]);

        return back()->with('success', 'New team agent added successfully.');
    }

    public function settings()
    {
        $tenant = $this->getTenant();
        $webWidgetChannel = Channel::where('tenant_id', $tenant->id)->where('platform', 'webchat')->first();

        return view('tenant.settings', compact('tenant', 'webWidgetChannel'));
    }

    public function callRoom(Request $request, string $room)
    {
        $conversationId = $request->query('conv');
        $conversation = $conversationId ? Conversation::with(['contact', 'channel'])->find($conversationId) : null;
        $role = $request->query('role', 'customer');
        $callType = $request->query('type', 'audio');

        return view('tenant.call_room', [
            'room' => $room,
            'role' => $role,
            'callType' => $callType,
            'conversation' => $conversation,
            'contact' => $conversation?->contact,
            'channel' => $conversation?->channel,
        ]);
    }

    protected function ensureDefaultQuickReplies($tenant): void
    {
        if (QuickReply::where('tenant_id', $tenant->id)->count() === 0) {
            $defaults = [
                [
                    'title' => 'Product In Stock',
                    'message' => 'Thank you for your message! Yes, this item is in stock with 1 Year Official Replacement Warranty.',
                    'shortcut' => '/stock',
                ],
                [
                    'title' => 'Delivery Address',
                    'message' => 'Would you like us to confirm your delivery address and send you a checkout order link?',
                    'shortcut' => '/address',
                ],
                [
                    'title' => 'Specialist Review',
                    'message' => 'Our technical specialist is looking into your inquiry and will update you shortly.',
                    'shortcut' => '/review',
                ],
                [
                    'title' => 'bKash / Nagad Payment',
                    'message' => 'You can complete your payment via bKash or Nagad Merchant Number. Please share your TrxID once completed.',
                    'shortcut' => '/payment',
                ],
            ];
            foreach ($defaults as $item) {
                QuickReply::create([
                    'tenant_id' => $tenant->id,
                    'title' => $item['title'],
                    'message' => $item['message'],
                    'shortcut' => $item['shortcut'],
                ]);
            }
        }
    }

    public function getQuickReplies(): JsonResponse
    {
        $tenant = $this->getTenant();
        $this->ensureDefaultQuickReplies($tenant);
        $replies = QuickReply::where('tenant_id', $tenant->id)->orderBy('id', 'asc')->get();
        return response()->json(['success' => true, 'data' => $replies]);
    }

    public function storeQuickReply(Request $request): JsonResponse
    {
        $tenant = $this->getTenant();
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'message' => 'required|string|max:2000',
            'shortcut' => 'nullable|string|max:50',
        ]);

        $reply = QuickReply::create([
            'tenant_id' => $tenant->id,
            'title' => $validated['title'],
            'message' => $validated['message'],
            'shortcut' => $validated['shortcut'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quick reply added successfully!',
            'data' => $reply,
        ], 201);
    }

    public function updateQuickReply(Request $request, QuickReply $quickReply): JsonResponse
    {
        $tenant = $this->getTenant();
        if ($quickReply->tenant_id !== $tenant->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'message' => 'required|string|max:2000',
            'shortcut' => 'nullable|string|max:50',
        ]);

        $quickReply->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Quick reply updated successfully!',
            'data' => $quickReply,
        ]);
    }

    public function toggleQuickReplyStatus(QuickReply $quickReply): JsonResponse
    {
        $tenant = $this->getTenant();
        if ($quickReply->tenant_id !== $tenant->id) {
            abort(403);
        }

        $quickReply->update(['is_active' => !$quickReply->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Quick reply ' . ($quickReply->is_active ? 'enabled on chat' : 'hidden from chat'),
            'data' => $quickReply,
        ]);
    }

    public function destroyQuickReply(QuickReply $quickReply): JsonResponse
    {
        $tenant = $this->getTenant();
        if ($quickReply->tenant_id !== $tenant->id) {
            abort(403);
        }

        $quickReply->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quick reply deleted successfully!',
        ]);
    }
}
