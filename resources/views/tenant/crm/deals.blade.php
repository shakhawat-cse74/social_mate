@extends('tenant.layout')

@section('title', 'ERP Deals & Sales Pipeline')

@section('content')
<div class="flex-1 overflow-x-auto p-8 flex flex-col space-y-6">
    <div class="flex items-center justify-between shrink-0">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">ERP Deals & Sales Opportunities</h1>
            <p class="text-xs text-slate-400">Track and advance high-value business deals through ERP sales stages</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-xs text-slate-400">
                Pipeline: <strong class="text-emerald-400 text-sm">${{ number_format($totalPipelineValue, 2) }}</strong>
                <span class="mx-2">•</span>
                Won: <strong class="text-purple-400 text-sm">${{ number_format($wonValue, 2) }}</strong>
            </div>
            <button onclick="document.getElementById('modal-add-deal').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-xl text-xs font-bold text-white shadow-lg transition-all flex items-center gap-1.5">
                <span>+ Create Opportunity</span>
            </button>
        </div>
    </div>

    <!-- 5 Pipeline Stages Kanban -->
    <div class="flex-1 grid grid-cols-5 gap-4 min-w-[1200px] overflow-hidden">
        @php
            $stages = [
                'qualification' => ['name' => '1. Qualification (20%)', 'color' => 'slate'],
                'proposal' => ['name' => '2. Proposal / Quote (40%)', 'color' => 'blue'],
                'negotiation' => ['name' => '3. Negotiation (70%)', 'color' => 'amber'],
                'won' => ['name' => '4. Closed Won (100%) 🎉', 'color' => 'emerald'],
                'lost' => ['name' => '5. Closed Lost ✕', 'color' => 'rose'],
            ];
        @endphp

        @foreach($stages as $stageKey => $meta)
            @php $stageDeals = $deals->where('stage', $stageKey); @endphp
            <div 
                id="column-{{ $stageKey }}"
                ondragover="handleDragOver(event)" 
                ondragleave="handleDragLeave(event)" 
                ondrop="handleDrop(event, '{{ $stageKey }}')" 
                class="stage-column bg-slate-900/60 border border-slate-800 rounded-2xl p-4 flex flex-col overflow-hidden transition-all"
            >
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 shrink-0">
                    <span class="text-xs font-bold text-white">{{ $meta['name'] }}</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 font-bold">
                        {{ $stageDeals->count() }}
                    </span>
                </div>

                <div class="flex-1 overflow-y-auto space-y-3 pt-3 min-h-[350px]">
                    @forelse($stageDeals as $deal)
                        <div 
                            id="deal-card-{{ $deal->id }}"
                            draggable="true" 
                            ondragstart="handleDragStart(event, {{ $deal->id }})"
                            class="deal-card cursor-grab active:cursor-grabbing p-3.5 bg-slate-950/80 rounded-xl border border-slate-800 hover:border-indigo-500/50 hover:shadow-lg hover:shadow-indigo-500/5 transition-all space-y-2.5"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-mono font-bold text-indigo-400">{{ $deal->deal_code }}</span>
                                <span class="text-xs font-bold text-emerald-400">${{ number_format($deal->amount, 2) }}</span>
                            </div>

                            <h4 class="text-xs font-bold text-white leading-snug">{{ $deal->title }}</h4>

                            <div class="text-[11px] text-slate-400 flex items-center gap-1.5">
                                <span>🏢</span>
                                <span class="truncate">{{ $deal->customer?->name ?? 'Direct Contact' }}</span>
                            </div>

                            <div class="flex items-center justify-between pt-1 border-t border-slate-900 text-[10px] text-slate-500">
                                <span>📅 {{ $deal->expected_close_date ? $deal->expected_close_date->format('M d') : 'N/A' }}</span>
                                <span class="truncate">👤 {{ $deal->assignedUser?->name ?? 'Unassigned' }}</span>
                            </div>

                            <!-- Stage Action Controls -->
                            <div class="pt-1 flex items-center justify-between">
                                <span class="text-[9px] text-slate-600 font-mono">⠿ Drag card</span>
                                <select onchange="updateStage({{ $deal->id }}, this.value)" class="bg-slate-900 border border-slate-800 rounded px-2 py-1 text-[10px] text-slate-300 focus:outline-none">
                                    <option value="qualification" {{ $deal->stage === 'qualification' ? 'selected' : '' }}>Stage: Qualify</option>
                                    <option value="proposal" {{ $deal->stage === 'proposal' ? 'selected' : '' }}>Stage: Proposal</option>
                                    <option value="negotiation" {{ $deal->stage === 'negotiation' ? 'selected' : '' }}>Stage: Negotiate</option>
                                    <option value="won" {{ $deal->stage === 'won' ? 'selected' : '' }}>Stage: Won 🎉</option>
                                    <option value="lost" {{ $deal->stage === 'lost' ? 'selected' : '' }}>Stage: Lost ✕</option>
                                </select>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-[11px] text-slate-600 border-2 border-dashed border-slate-800/40 rounded-xl">
                            Drag & drop deals here
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal: Add Deal -->
<div id="modal-add-deal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white">Create Sales Deal / Opportunity</h3>
            <button onclick="document.getElementById('modal-add-deal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ route('tenant.crm.deals.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Customer Account *</label>
                <select name="customer_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->customer_code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Deal Title *</label>
                <input type="text" name="title" required placeholder="e.g. Annual IT Support Contract" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Deal Value ($) *</label>
                    <input type="number" name="amount" required step="0.01" placeholder="2500.00" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Initial Stage</label>
                    <select name="stage" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                        <option value="qualification">Qualification (20%)</option>
                        <option value="proposal" selected>Proposal / Quote (40%)</option>
                        <option value="negotiation">Negotiation (70%)</option>
                        <option value="won">Closed Won (100%)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Win Probability (%)</label>
                    <input type="number" name="probability" value="40" min="0" max="100" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Expected Close Date</label>
                    <input type="date" name="expected_close_date" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('modal-add-deal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-xs rounded-xl text-slate-300">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 font-bold text-xs rounded-xl text-white shadow-lg">Save Deal</button>
            </div>
        </form>
    </div>
</div>

<script>
    let draggedDealId = null;

    function handleDragStart(e, dealId) {
        draggedDealId = dealId;
        e.dataTransfer.setData('text/plain', dealId);
        e.dataTransfer.effectAllowed = 'move';
        const card = document.getElementById(`deal-card-${dealId}`);
        if (card) {
            setTimeout(() => card.classList.add('opacity-40', 'scale-95'), 0);
        }
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        const col = e.currentTarget;
        col.classList.add('border-indigo-500', 'bg-indigo-950/30');
    }

    function handleDragLeave(e) {
        const col = e.currentTarget;
        col.classList.remove('border-indigo-500', 'bg-indigo-950/30');
    }

    async function handleDrop(e, targetStage) {
        e.preventDefault();
        const col = e.currentTarget;
        col.classList.remove('border-indigo-500', 'bg-indigo-950/30');

        const dealId = draggedDealId || e.dataTransfer.getData('text/plain');
        if (dealId) {
            const card = document.getElementById(`deal-card-${dealId}`);
            if (card) card.classList.remove('opacity-40', 'scale-95');
            await updateStage(dealId, targetStage);
        }
    }

    async function updateStage(dealId, newStage) {
        try {
            const res = await fetch(`/tenant/crm/deals/${dealId}/stage`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ stage: newStage })
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            }
        } catch (err) {
            console.error(err);
        }
    }
</script>
@endsection
