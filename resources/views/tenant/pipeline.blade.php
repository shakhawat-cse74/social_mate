@extends('tenant.layout')

@section('title', 'Deals Pipeline')

@section('content')
<div class="flex-1 overflow-x-auto p-8 flex flex-col space-y-6">
    <div class="flex items-center justify-between shrink-0">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Deals Pipeline (Sales Kanban)</h1>
            <p class="text-xs text-slate-400">Track and advance qualified social media leads to closed deals</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-400">Pipeline Total:</span>
            <span class="text-base font-extrabold text-emerald-400">${{ number_format($totalDealValue, 2) }}</span>
        </div>
    </div>

    <!-- 5 Kanban Columns -->
    <div class="flex-1 grid grid-cols-5 gap-4 min-w-[1100px] overflow-hidden">
        @php
            $stages = [
                'new' => ['name' => '1. New Leads', 'color' => 'slate'],
                'contacted' => ['name' => '2. Contacted', 'color' => 'blue'],
                'interested' => ['name' => '3. Interested / Qualified', 'color' => 'purple'],
                'won' => ['name' => '4. Deal Won 🎉', 'color' => 'emerald'],
                'lost' => ['name' => '5. Closed Lost', 'color' => 'rose'],
            ];
        @endphp

        @foreach($stages as $stageKey => $meta)
            @php $stageContacts = $contacts->where('pipeline_stage', $stageKey); @endphp
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-4 flex flex-col overflow-hidden">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 shrink-0">
                    <span class="text-xs font-bold text-white">{{ $meta['name'] }}</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 font-bold">
                        {{ $stageContacts->count() }}
                    </span>
                </div>

                <div class="flex-1 overflow-y-auto space-y-3 pt-3">
                    @forelse($stageContacts as $lead)
                        <div class="p-3.5 bg-slate-950/80 rounded-xl border border-slate-800 hover:border-slate-700 transition-colors shadow-sm space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-white">{{ $lead->name }}</span>
                                <span class="text-xs font-bold text-emerald-400">${{ number_format($lead->deal_value, 2) }}</span>
                            </div>
                            <div class="text-[11px] text-slate-400">
                                {{ ucfirst($lead->platform) }} • {{ $lead->phone ?: $lead->email }}
                            </div>
                            @if(!empty($lead->tags))
                                <div class="flex flex-wrap gap-1 pt-1">
                                    @foreach($lead->tags as $tag)
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-300">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-8 text-center text-[11px] text-slate-600">No leads in this stage</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
