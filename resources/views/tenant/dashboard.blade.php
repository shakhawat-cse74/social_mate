@extends('tenant.layout')

@section('title', 'Dashboard')

@section('content')
<div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Overview & Performance</h1>
            <p class="text-xs text-slate-400 mt-1">Live metrics for <strong class="text-indigo-400">{{ $tenant->name }}</strong></p>
        </div>
        <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Subscription Active
            </span>
            <a href="{{ route('tenant.inbox') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
                <span>💬 Open Live Inbox</span>
            </a>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-md">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Conversations</div>
            <div class="text-2xl font-black text-white mt-1.5">{{ $stats['total_conversations'] }}</div>
            <div class="text-[11px] text-indigo-400 mt-2 flex items-center gap-1">
                <span>💬 Active chats across channels</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-md">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">CRM Leads & Contacts</div>
            <div class="text-2xl font-black text-white mt-1.5">{{ $stats['total_contacts'] }}</div>
            <div class="text-[11px] text-emerald-400 mt-2 flex items-center gap-1">
                <span>👥 Potential buyers tracked</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-md">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Sales Pipeline Value</div>
            <div class="text-2xl font-black text-emerald-400 mt-1.5">${{ number_format($stats['total_deal_value'], 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-2">Estimated revenue in deals</div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-md">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Connected Social Channels</div>
            <div class="text-2xl font-black text-white mt-1.5">{{ $stats['connected_channels'] }} <span class="text-xs text-slate-500 font-normal">Active</span></div>
            <div class="text-[11px] text-purple-400 mt-2 flex items-center gap-1">
                <span>🌐 Meta, WhatsApp, Telegram</span>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Hub -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-5">
        <a href="{{ route('tenant.inbox') }}" class="p-5 sm:p-6 rounded-2xl bg-gradient-to-br from-indigo-950/40 to-slate-900/80 border border-indigo-500/20 hover:border-indigo-500/50 transition-all group shadow-lg">
            <div class="w-12 h-12 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center font-bold text-xl mb-4 group-hover:scale-105 transition-transform">
                💬
            </div>
            <h3 class="text-sm font-bold text-white group-hover:text-indigo-300 transition-colors">Omnichannel Live Inbox</h3>
            <p class="text-xs text-slate-400 mt-1">Reply to Facebook, WhatsApp, and Telegram inquiries in real-time from one screen.</p>
        </a>

        <a href="{{ route('tenant.pipeline') }}" class="p-5 sm:p-6 rounded-2xl bg-gradient-to-br from-purple-950/40 to-slate-900/80 border border-purple-500/20 hover:border-purple-500/50 transition-all group shadow-lg">
            <div class="w-12 h-12 rounded-xl bg-purple-600/20 text-purple-400 flex items-center justify-center font-bold text-xl mb-4 group-hover:scale-105 transition-transform">
                📊
            </div>
            <h3 class="text-sm font-bold text-white group-hover:text-purple-300 transition-colors">CRM Deals Pipeline</h3>
            <p class="text-xs text-slate-400 mt-1">Drag and drop leads through stages from New Lead to Deal Won.</p>
        </a>

        @if($tenant->ai_enabled)
            <a href="{{ route('tenant.ai') }}" class="p-5 sm:p-6 rounded-2xl bg-gradient-to-br from-emerald-950/40 to-slate-900/80 border border-emerald-500/20 hover:border-emerald-500/50 transition-all group shadow-lg">
                <div class="w-12 h-12 rounded-xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center font-bold text-xl mb-4 group-hover:scale-105 transition-transform">
                    🤖
                </div>
                <h3 class="text-sm font-bold text-white group-hover:text-emerald-300 transition-colors">AI Agent & Knowledge Base</h3>
                <p class="text-xs text-slate-400 mt-1">Train your AI with your company's FAQs, product specs, and pricing rules.</p>
            </a>
        @else
            <div class="p-5 sm:p-6 rounded-2xl bg-slate-900/40 border border-slate-800 text-slate-500">
                <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center font-bold text-xl mb-4">
                    🔒
                </div>
                <h3 class="text-sm font-bold text-slate-400">AI Agent (Plan Upgrade Required)</h3>
                <p class="text-xs text-slate-500 mt-1">Upgrade your subscription to unlock automated 24/7 AI customer service.</p>
            </div>
        @endif
    </div>

    <!-- Recent Conversations Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-4 sm:p-5 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300">Recent Customer Inquiries</h3>
            <a href="{{ route('tenant.inbox') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">View All in Inbox &rarr;</a>
        </div>

        <div class="overflow-x-auto w-full">
        <table class="w-full text-left border-collapse text-xs min-w-[600px]">
            <thead>
                <tr class="border-b border-slate-800 bg-slate-950/50 text-[11px] text-slate-400 uppercase">
                    <th class="p-4 font-semibold">Customer</th>
                    <th class="p-4 font-semibold">Channel</th>
                    <th class="p-4 font-semibold">Last Message</th>
                    <th class="p-4 font-semibold">AI Mode</th>
                    <th class="p-4 font-semibold">Time</th>
                    <th class="p-4 font-semibold text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse($recentConversations as $conv)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="p-4 font-bold text-white flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center text-xs">
                                {{ strtoupper(substr($conv->contact->name ?? 'C', 0, 1)) }}
                            </div>
                            <span>{{ $conv->contact->name ?? 'Guest User' }}</span>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                @if($conv->channel->platform === 'facebook') bg-blue-500/20 text-blue-400
                                @elseif($conv->channel->platform === 'whatsapp') bg-emerald-500/20 text-emerald-400
                                @elseif($conv->channel->platform === 'telegram') bg-sky-500/20 text-sky-400
                                @else bg-purple-500/20 text-purple-400 @endif">
                                {{ $conv->channel->platform }}
                            </span>
                        </td>
                        <td class="p-4 text-slate-300 truncate max-w-xs">
                            {{ $conv->latestMessage->content ?? 'No messages yet' }}
                        </td>
                        <td class="p-4">
                            <span class="text-[10px] px-2 py-0.5 rounded font-bold
                                @if($conv->ai_mode === 'active') bg-indigo-500/20 text-indigo-300
                                @elseif($conv->ai_mode === 'human_takeover') bg-amber-500/20 text-amber-300
                                @else bg-slate-800 text-slate-400 @endif">
                                {{ $conv->ai_mode }}
                            </span>
                        </td>
                        <td class="p-4 text-slate-400 text-[11px]">
                            {{ $conv->last_message_at ? $conv->last_message_at->diffForHumans() : 'New' }}
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('tenant.inbox') }}" class="px-3 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-300 font-semibold transition-colors">
                                Reply &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-500">No conversations yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection
