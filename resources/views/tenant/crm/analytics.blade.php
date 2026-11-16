@extends('tenant.layout')

@section('title', 'CRM Reporting & Analytics')

@section('content')
<div class="flex-1 overflow-y-auto p-8 space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">CRM Reporting & Sales Analytics</h1>
            <p class="text-xs text-slate-400">Live KPIs, lead conversion ratios, pipeline velocity, and team performance metrics</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 font-mono">
                📅 Real-time Data
            </span>
        </div>
    </div>

    <!-- 4 Core Performance KPI Cards -->
    <div class="grid grid-cols-4 gap-4">
        <!-- 1. Conversion Rate -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-indigo-950/40 border border-slate-800/80 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1 font-semibold uppercase tracking-wider">
                <span>Lead Conversion</span>
                <span class="text-indigo-400 text-base">🎯</span>
            </div>
            <div class="text-3xl font-extrabold text-white mt-1">{{ $conversionRate }}%</div>
            <div class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                <span>Converted: <strong class="text-indigo-300">{{ $convertedLeads }}</strong></span>
                <span>Total Leads: <strong class="text-slate-200">{{ $totalLeads }}</strong></span>
            </div>
            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="bg-indigo-500 h-full rounded-full transition-all duration-500" style="width: {{ min(100, $conversionRate) }}%"></div>
            </div>
        </div>

        <!-- 2. Active Pipeline Value -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-cyan-950/40 border border-slate-800/80 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1 font-semibold uppercase tracking-wider">
                <span>Pipeline Value</span>
                <span class="text-cyan-400 text-base">💼</span>
            </div>
            <div class="text-3xl font-extrabold text-cyan-400 mt-1">${{ number_format($totalPipelineValue, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                <span>Active Deals in Progress</span>
                <span class="text-slate-300 font-bold font-mono">Live</span>
            </div>
            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="bg-cyan-500 h-full rounded-full transition-all duration-500" style="width: 70%"></div>
            </div>
        </div>

        <!-- 3. Closed Won Revenue -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-emerald-950/40 border border-slate-800/80 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1 font-semibold uppercase tracking-wider">
                <span>Won Revenue</span>
                <span class="text-emerald-400 text-base">💰</span>
            </div>
            <div class="text-3xl font-extrabold text-emerald-400 mt-1">${{ number_format($wonValue, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                <span>Deals Won: <strong class="text-emerald-300">{{ $dealsByStage['won']['count'] }}</strong></span>
                <span>Avg Size: <strong class="text-slate-200">${{ number_format($avgDealSize, 0) }}</strong></span>
            </div>
            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" style="width: 100%"></div>
            </div>
        </div>

        <!-- 4. Sales Win Rate -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-purple-950/40 border border-slate-800/80 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1 font-semibold uppercase tracking-wider">
                <span>Sales Win Rate</span>
                <span class="text-purple-400 text-base">🏆</span>
            </div>
            <div class="text-3xl font-extrabold text-purple-400 mt-1">{{ $winRate }}%</div>
            <div class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                <span>Lost Deals: <strong class="text-rose-400">{{ $dealsByStage['lost']['count'] }}</strong></span>
                <span>Activities Done: <strong class="text-slate-200">{{ $activitiesCompleted }}</strong></span>
            </div>
            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="bg-purple-500 h-full rounded-full transition-all duration-500" style="width: {{ min(100, $winRate) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Middle Row: Pipeline Stages & Inbound Lead Sources -->
    <div class="grid grid-cols-3 gap-6">
        <!-- Deals Pipeline by Stage Breakdown (2 cols) -->
        <div class="col-span-2 bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-white">Sales Pipeline Funnel</h3>
                    <p class="text-xs text-slate-400">Total deal value and deal volume across each ERP pipeline stage</p>
                </div>
                <span class="text-xs font-mono text-indigo-400 font-bold">5 Active Stages</span>
            </div>

            <div class="space-y-4">
                @php
                    $stageLabels = [
                        'qualification' => ['title' => '1. Qualification', 'color' => 'bg-slate-400'],
                        'proposal' => ['title' => '2. Proposal & Quotation Sent', 'color' => 'bg-blue-500'],
                        'negotiation' => ['title' => '3. Negotiation & Agreement', 'color' => 'bg-amber-500'],
                        'won' => ['title' => '4. Closed Won 🎉', 'color' => 'bg-emerald-500'],
                        'lost' => ['title' => '5. Closed Lost', 'color' => 'bg-rose-500'],
                    ];
                    $maxAmount = max(1, collect($dealsByStage)->max('amount'));
                @endphp

                @foreach($dealsByStage as $stageKey => $data)
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-200">{{ $stageLabels[$stageKey]['title'] }}</span>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-300 font-mono font-bold">{{ $data['count'] }} deals</span>
                                <span class="font-bold text-white font-mono">${{ number_format($data['amount'], 2) }}</span>
                            </div>
                        </div>
                        <div class="w-full bg-slate-950 h-2.5 rounded-full overflow-hidden border border-slate-800/80">
                            <div class="{{ $stageLabels[$stageKey]['color'] }} h-full rounded-full transition-all duration-700" style="width: {{ ($data['amount'] / $maxAmount) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Inbound Lead Sources Breakdown (1 col) -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-5">
            <div class="pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white">Lead Acquisition Sources</h3>
                <p class="text-xs text-slate-400">Where your high-value customer inquiries are originating</p>
            </div>

            <div class="space-y-3.5">
                @php
                    $sourceIcons = [
                        'facebook' => ['icon' => '🔵', 'label' => 'Facebook Messenger'],
                        'whatsapp' => ['icon' => '🟢', 'label' => 'WhatsApp Cloud Hotline'],
                        'telegram' => ['icon' => '✈️', 'label' => 'Telegram Official Bot'],
                        'webchat' => ['icon' => '💬', 'label' => 'Website Live Chat Widget'],
                        'direct_call' => ['icon' => '📞', 'label' => 'Direct Phone Call'],
                        'referral' => ['icon' => '🤝', 'label' => 'Client Referral'],
                    ];
                @endphp

                @forelse($leadsBySource as $src => $count)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/60 border border-slate-800/60">
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">{{ $sourceIcons[$src]['icon'] ?? '⚡' }}</span>
                            <span class="text-xs font-semibold text-slate-200">{{ $sourceIcons[$src]['label'] ?? ucfirst($src) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-indigo-400 font-mono">{{ $count }}</span>
                            <span class="text-[10px] text-slate-500">leads</span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-500">No leads recorded yet</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Lower Row: Sales Agent Performance Leaderboard -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div>
                <h3 class="text-sm font-bold text-white">Sales Team Performance Leaderboard</h3>
                <p class="text-xs text-slate-400">Conversion efficiency, closed deals, and revenue output per team member</p>
            </div>
            <div class="text-xs text-slate-400">
                Pending Follow-ups: <strong class="text-amber-400">{{ $activitiesPending }}</strong>
                <span class="mx-2">•</span>
                Completed: <strong class="text-emerald-400">{{ $activitiesCompleted }}</strong>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">
                        <th class="py-3 px-4">Sales Representative</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4 text-center">Leads Handled</th>
                        <th class="py-3 px-4 text-center">Opportunities</th>
                        <th class="py-3 px-4 text-center">Deals Won</th>
                        <th class="py-3 px-4 text-center">Activities Completed</th>
                        <th class="py-3 px-4 text-right">Revenue Closed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-xs">
                    @forelse($agentStats as $stat)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-white text-xs">
                                        {{ strtoupper(substr($stat['user']->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white">{{ $stat['user']->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $stat['user']->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $stat['user']->role === 'tenant_admin' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'bg-slate-800 text-slate-300' }}">
                                    {{ $stat['user']->role }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-semibold text-slate-200">
                                {{ $stat['leads_count'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-semibold text-slate-200">
                                {{ $stat['deals_count'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    {{ $stat['won_count'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-slate-300">
                                {{ $stat['activities_done'] }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-400 text-sm">
                                ${{ number_format($stat['revenue_closed'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-xs text-slate-500">No agents found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
