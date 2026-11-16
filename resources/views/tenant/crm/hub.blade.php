@extends('tenant.layout')
@section('title', 'CRM Hub')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
.crm-root{font-family:'Inter',sans-serif}
.crm-tab{position:relative;display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:12px;font-weight:600;color:#64748b;border-bottom:2px solid transparent;cursor:pointer;transition:all .2s;white-space:nowrap;background:none;border-top:none;border-left:none;border-right:none;outline:none}
.crm-tab:hover{color:#cbd5e1}
.crm-tab.active{color:#fff;border-bottom-color:#6366f1}
.kpi-card{transition:transform .2s,box-shadow .2s,border-color .2s;cursor:pointer}
.kpi-card:hover{transform:translateY(-3px);box-shadow:0 12px 30px rgba(99,102,241,.12)}
.lead-row{transition:background .15s}
.lead-row:hover{background:rgba(99,102,241,.05)}
.kanban-col{transition:border-color .2s,background .2s}
.kanban-col.drag-over{border-color:#6366f1!important;background:rgba(99,102,241,.08)!important}
.deal-card{cursor:grab;transition:transform .15s,box-shadow .15s,border-color .15s}
.deal-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.3);border-color:rgba(99,102,241,.5)}
.deal-card.dragging{opacity:.4;transform:scale(.97)}
.activity-card{transition:border-color .15s,background .15s}
@keyframes urgentPulse{0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.4)}50%{box-shadow:0 0 0 5px rgba(239,68,68,0)}}
.priority-urgent{animation:urgentPulse 2s infinite}
@keyframes fillBar{from{width:0}}
.fill-bar{animation:fillBar 1s ease forwards}
.crm-modal{backdrop-filter:blur(8px)}
.crm-modal-box{animation:modalIn .2s ease;box-shadow:0 25px 60px rgba(0,0,0,.6),0 0 0 1px rgba(99,102,241,.15)}
@keyframes modalIn{from{opacity:0;transform:translateY(12px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}
.crm-scroll::-webkit-scrollbar{width:4px;height:4px}
.crm-scroll::-webkit-scrollbar-thumb{background:#334155;border-radius:99px}
.badge-indigo{background:rgba(99,102,241,.15);color:#a5b4fc;border:1px solid rgba(99,102,241,.3)}
.badge-emerald{background:rgba(16,185,129,.12);color:#6ee7b7;border:1px solid rgba(16,185,129,.25)}
.badge-amber{background:rgba(245,158,11,.12);color:#fcd34d;border:1px solid rgba(245,158,11,.25)}
.badge-rose{background:rgba(239,68,68,.12);color:#fca5a5;border:1px solid rgba(239,68,68,.25)}
.badge-sky{background:rgba(14,165,233,.12);color:#7dd3fc;border:1px solid rgba(14,165,233,.25)}
.badge-slate{background:rgba(100,116,139,.15);color:#94a3b8;border:1px solid rgba(100,116,139,.2)}
.badge-purple{background:rgba(168,85,247,.12);color:#d8b4fe;border:1px solid rgba(168,85,247,.25)}

/* Light Mode CRM overrides */
html.light .crm-tab{color:#64748b}
html.light .crm-tab:hover{color:#0f172a}
html.light .crm-tab.active{color:#0f172a!important;border-bottom-color:#6366f1!important}
html.light .kpi-card{background:linear-gradient(135deg,#ffffff 0%,#f8fafc 100%)!important;border-color:#e2e8f0!important;box-shadow:0 4px 15px rgba(0,0,0,.04)!important}
html.light .deal-card{background-color:#ffffff!important;border-color:#e2e8f0!important;box-shadow:0 2px 6px rgba(0,0,0,.04)!important}
html.light .kanban-col{background-color:#f8fafc!important;border-color:#e2e8f0!important}
html.light .activity-card{background-color:#ffffff!important;border-color:#e2e8f0!important;box-shadow:0 2px 6px rgba(0,0,0,.04)!important}
html.light .badge-indigo{background:rgba(99,102,241,.12);color:#4338ca;border:1px solid rgba(99,102,241,.25)}
html.light .badge-emerald{background:rgba(16,185,129,.12);color:#047857;border:1px solid rgba(16,185,129,.25)}
html.light .badge-amber{background:rgba(245,158,11,.12);color:#b45309;border:1px solid rgba(245,158,11,.25)}
html.light .badge-rose{background:rgba(239,68,68,.12);color:#b91c1c;border:1px solid rgba(239,68,68,.25)}
html.light .badge-sky{background:rgba(14,165,233,.12);color:#0369a1;border:1px solid rgba(14,165,233,.25)}
html.light .badge-purple{background:rgba(168,85,247,.12);color:#7e22ce;border:1px solid rgba(168,85,247,.25)}
html.light .badge-slate{background:rgba(100,116,139,.12);color:#334155;border:1px solid rgba(100,116,139,.2)}
</style>
@endpush
@section('content')
<div class="flex-1 flex flex-col overflow-hidden crm-root bg-slate-950">

{{-- TOP BAR --}}
<div class="shrink-0 px-4 sm:px-6 lg:px-8 py-3 sm:py-4 border-b border-slate-800/80 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-950/90 backdrop-blur-sm">
  <div class="flex items-center gap-3">
    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 flex items-center justify-center shadow-lg shadow-indigo-500/25 shrink-0">
      <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div>
      <h1 class="text-base font-extrabold text-white tracking-tight">CRM Hub</h1>
      <p class="text-[11px] text-slate-500">Leads &middot; Pipeline &middot; Activities &middot; Analytics</p>
    </div>
  </div>
  <div class="flex items-center gap-2 overflow-x-auto pb-1 max-w-full no-scrollbar">
    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-indigo-950/60 border border-indigo-800/40 shrink-0">
      <span class="text-[10px] text-indigo-400 font-semibold uppercase tracking-wide">Leads</span>
      <span class="text-sm font-extrabold text-white">{{ $totalLeads }}</span>
      <span class="text-[10px] text-indigo-300">({{ $conversionRate }}%)</span>
    </div>
    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-cyan-950/50 border border-cyan-800/30 shrink-0">
      <span class="text-[10px] text-cyan-400 font-semibold uppercase tracking-wide">Pipeline</span>
      <span class="text-sm font-extrabold text-cyan-300">${{ number_format($totalPipelineValue,0) }}</span>
    </div>
    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-950/50 border border-emerald-800/30 shrink-0">
      <span class="text-[10px] text-emerald-400 font-semibold uppercase tracking-wide">Won</span>
      <span class="text-sm font-extrabold text-emerald-300">${{ number_format($wonValue,0) }}</span>
    </div>
    @if($activitiesPending > 0)
    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-950/50 border border-amber-700/40 shrink-0">
      <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span></span>
      <span class="text-sm font-extrabold text-amber-300">{{ $activitiesPending }}</span>
      <span class="text-[10px] text-amber-400 font-semibold">Pending</span>
    </div>
    @endif
  </div>
</div>

{{-- TAB BAR --}}
<div class="shrink-0 flex items-center px-4 sm:px-6 border-b border-slate-800/60 bg-slate-950/70 overflow-x-auto no-scrollbar">
  @php $tabs=['overview'=>['l'=>'Overview','i'=>'◈'],'leads'=>['l'=>'Leads','i'=>'🎯'],'deals'=>['l'=>'Pipeline','i'=>'📊'],'activities'=>['l'=>'Activities','i'=>'⚡'],'analytics'=>['l'=>'Analytics','i'=>'📈']]; @endphp
  @foreach($tabs as $k=>$t)
  <button class="crm-tab {{ $activeTab===$k?'active':'' }}" onclick="switchTab('{{ $k }}')" id="tab-btn-{{ $k }}">
    <span>{{ $t['i'] }}</span><span>{{ $t['l'] }}</span>
    @if($k==='leads'&&$totalLeads>0)<span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold font-mono {{ $activeTab===$k?'bg-indigo-500/30 text-indigo-200':'bg-slate-800 text-slate-400' }}">{{ $totalLeads }}</span>@endif
    @if($k==='activities'&&$activitiesPending>0)<span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold font-mono {{ $activeTab===$k?'bg-amber-500/30 text-amber-200':'bg-slate-800 text-slate-400' }}">{{ $activitiesPending }}</span>@endif
  </button>
  @endforeach
  <div class="ml-auto py-2 flex gap-2">
    <div class="relative">
      <button id="fab-new" onclick="toggleDD()" class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition-all shadow-lg shadow-indigo-500/20">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Quick Add
      </button>
      <div id="qc-drop" class="hidden absolute right-0 top-full mt-2 bg-slate-900 border border-slate-700/60 rounded-2xl p-2 shadow-2xl min-w-[170px] z-40">
        <button onclick="hideDD();openModal('modal-add-lead')" class="w-full text-left px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-indigo-600 hover:text-white transition-all flex items-center gap-2">🎯 New Lead</button>
        <button onclick="hideDD();openModal('modal-add-deal')" class="w-full text-left px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-indigo-600 hover:text-white transition-all flex items-center gap-2">📊 New Deal</button>
        <button onclick="hideDD();openModal('modal-add-activity')" class="w-full text-left px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-indigo-600 hover:text-white transition-all flex items-center gap-2">⚡ Schedule</button>
      </div>
    </div>
  </div>
</div>

{{-- CONTENT --}}
<div class="flex-1 overflow-y-auto crm-scroll">

  {{-- OVERVIEW --}}
  <div id="tab-overview" class="tab-panel {{ $activeTab!=='overview'?'hidden':'' }} p-4 sm:p-6 lg:p-8 space-y-6">
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-medium">&#10003; {{ session('success') }}</div>
    @endif
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div onclick="switchTab('leads')" class="kpi-card p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-indigo-950/60 border border-slate-800 hover:border-indigo-500/40">
        <div class="flex items-center justify-between mb-4"><div class="w-9 h-9 rounded-xl bg-indigo-500/15 flex items-center justify-center border border-indigo-500/20"><svg class="w-4 h-4 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div><span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wide">Lead Conv.</span></div>
        <div class="text-3xl font-extrabold text-white mb-1">{{ $conversionRate }}%</div>
        <div class="text-[11px] text-slate-500 mb-3">{{ $convertedLeads }} of {{ $totalLeads }} leads</div>
        <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden"><div class="fill-bar h-full rounded-full bg-gradient-to-r from-indigo-600 to-violet-500" style="width:{{ min(100,$conversionRate) }}%"></div></div>
      </div>
      <div onclick="switchTab('deals')" class="kpi-card p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-cyan-950/50 border border-slate-800 hover:border-cyan-500/40">
        <div class="flex items-center justify-between mb-4"><div class="w-9 h-9 rounded-xl bg-cyan-500/15 flex items-center justify-center border border-cyan-500/20"><svg class="w-4 h-4 text-cyan-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="6" height="14" x="2" y="5" rx="2"/><rect width="6" height="10" x="10" y="9" rx="2"/><rect width="6" height="16" x="18" y="3" rx="2"/></svg></div><span class="text-[10px] font-bold text-cyan-400 uppercase tracking-wide">Pipeline</span></div>
        <div class="text-3xl font-extrabold text-cyan-300 mb-1">${{ number_format($totalPipelineValue,0) }}</div>
        <div class="text-[11px] text-slate-500 mb-3">{{ $deals->whereNotIn('stage',['lost'])->count() }} active deals</div>
        <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden"><div class="fill-bar h-full rounded-full bg-gradient-to-r from-cyan-600 to-sky-400" style="width:70%"></div></div>
      </div>
      <div onclick="switchTab('analytics')" class="kpi-card p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-emerald-950/50 border border-slate-800 hover:border-emerald-500/40">
        <div class="flex items-center justify-between mb-4"><div class="w-9 h-9 rounded-xl bg-emerald-500/15 flex items-center justify-center border border-emerald-500/20"><svg class="w-4 h-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wide">Won</span></div>
        <div class="text-3xl font-extrabold text-emerald-300 mb-1">${{ number_format($wonValue,0) }}</div>
        <div class="text-[11px] text-slate-500 mb-3">{{ $winRate }}% win rate &middot; avg ${{ number_format($avgDealSize,0) }}</div>
        <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden"><div class="fill-bar h-full rounded-full bg-gradient-to-r from-emerald-600 to-teal-400" style="width:{{ min(100,$winRate) }}%"></div></div>
      </div>
      <div onclick="switchTab('activities')" class="kpi-card p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-amber-950/40 border border-slate-800 hover:border-amber-500/40">
        <div class="flex items-center justify-between mb-4"><div class="w-9 h-9 rounded-xl bg-amber-500/15 flex items-center justify-center border border-amber-500/20"><svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><span class="text-[10px] font-bold text-amber-400 uppercase tracking-wide">Tasks</span></div>
        <div class="text-3xl font-extrabold {{ $activitiesPending>0?'text-amber-300':'text-emerald-300' }} mb-1">{{ $activitiesPending>0?$activitiesPending:'Done!' }}</div>
        <div class="text-[11px] text-slate-500 mb-3">{{ $activitiesCompleted }} done &middot; {{ $activitiesPending }} pending</div>
        @php $dr=($activitiesPending+$activitiesCompleted)>0?($activitiesCompleted/($activitiesPending+$activitiesCompleted))*100:100; @endphp
        <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden"><div class="fill-bar h-full rounded-full bg-gradient-to-r from-amber-600 to-orange-400" style="width:{{ $dr }}%"></div></div>
      </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
      <div class="col-span-1 lg:col-span-3 rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800/60"><div><h3 class="text-xs font-bold text-white">Recent Leads</h3><p class="text-[10px] text-slate-500 mt-0.5">Latest inbound</p></div><button onclick="switchTab('leads')" class="text-[11px] text-indigo-400 hover:text-indigo-300 font-semibold">All &rarr;</button></div>
        <div class="divide-y divide-slate-800/40">
          @forelse($leads->take(7) as $lead)
          <div class="flex items-center gap-3.5 px-5 py-3.5 hover:bg-indigo-950/20 transition-colors">
            <div class="w-8 h-8 rounded-xl flex-shrink-0 flex items-center justify-center text-xs font-bold text-white {{ match($lead->source) { 'facebook' => 'bg-blue-600', 'whatsapp' => 'bg-emerald-600', 'telegram' => 'bg-sky-600', default => 'bg-slate-600' } }}">{{ strtoupper(substr($lead->first_name?:($lead->company_name?:'?'),0,1)) }}</div>
            <div class="flex-1 min-w-0"><div class="text-xs font-bold text-white truncate">{{ $lead->title }}</div><div class="text-[11px] text-slate-400 truncate">{{ $lead->company_name?:trim($lead->first_name.' '.$lead->last_name) }}</div></div>
            <div class="flex items-center gap-2 flex-shrink-0"><span class="text-xs font-bold text-emerald-400 font-mono">${{ number_format($lead->estimated_budget,0) }}</span><span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ match($lead->status) { 'converted' => 'badge-indigo', 'qualified' => 'badge-emerald', 'lost' => 'badge-rose', default => 'badge-slate' } }}">{{ $lead->status }}</span></div>
          </div>
          @empty
          <div class="py-12 text-center"><div class="text-3xl mb-2">🎯</div><div class="text-xs text-slate-500">No leads yet</div></div>
          @endforelse
        </div>
      </div>
      <div class="col-span-1 lg:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800/60"><div><h3 class="text-xs font-bold text-white">Upcoming Tasks</h3><p class="text-[10px] text-slate-500 mt-0.5">{{ $activitiesPending }} pending</p></div><button onclick="switchTab('activities')" class="text-[11px] text-indigo-400 hover:text-indigo-300 font-semibold">All &rarr;</button></div>
        <div class="divide-y divide-slate-800/40">
          @forelse($activities->where('status','pending')->take(7) as $act)
          <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-indigo-950/20 transition-colors">
            <div class="mt-0.5 w-7 h-7 rounded-lg flex-shrink-0 flex items-center justify-center text-sm {{ match($act->type) { 'call' => 'bg-blue-500/15 text-blue-400', 'meeting' => 'bg-purple-500/15 text-purple-400', default => 'bg-amber-500/15 text-amber-400' } }}">{{ match($act->type) { 'call' => '📞', 'meeting' => '📅', default => '⚡' } }}</div>
            <div class="flex-1 min-w-0"><div class="text-xs font-semibold text-white truncate">{{ $act->subject }}</div><div class="text-[10px] text-slate-400">{{ $act->customer?->name??'General' }}</div><div class="text-[10px] mt-0.5 {{ $act->due_date?->isPast()?'text-rose-400 font-semibold':'text-slate-500' }}">{{ $act->due_date?->format('M d, h:i A') }}{{ $act->due_date?->isPast() ? ' ⚠' : '' }}</div></div>
            <form action="{{ route('tenant.crm.activities.complete',$act->id) }}" method="POST">@csrf<button type="submit" class="mt-1 w-6 h-6 flex items-center justify-center rounded-lg border border-slate-700 hover:border-emerald-500/50 hover:bg-emerald-500/10 text-slate-500 hover:text-emerald-400 text-xs font-bold transition-all">&#10003;</button></form>
          </div>
          @empty
          <div class="py-12 text-center"><div class="text-3xl mb-2">🎉</div><div class="text-xs text-slate-500">All caught up!</div></div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  {{-- LEADS --}}
  <div id="tab-leads" class="tab-panel {{ $activeTab!=='leads'?'hidden':'' }} p-4 sm:p-6 lg:p-8 space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div><h2 class="text-base font-extrabold text-white">Lead Management</h2><p class="text-[11px] text-slate-500 mt-0.5">{{ $totalLeads }} total &middot; {{ $convertedLeads }} converted ({{ $conversionRate }}%)</p></div>
      <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
        <div class="relative"><svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" id="lead-search" placeholder="Search..." oninput="filterLeads()" class="pl-8 pr-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 w-36 sm:w-48"></div>
        <select id="lead-status-filter" onchange="filterLeads()" class="bg-slate-900 border border-slate-800 rounded-xl px-2.5 sm:px-3 py-2 text-xs text-slate-300 focus:outline-none"><option value="">All Status</option><option value="new">New</option><option value="contacted">Contacted</option><option value="qualified">Qualified</option><option value="converted">Converted</option><option value="lost">Lost</option></select>
        <select id="lead-source-filter" onchange="filterLeads()" class="bg-slate-900 border border-slate-800 rounded-xl px-2.5 sm:px-3 py-2 text-xs text-slate-300 focus:outline-none"><option value="">All Sources</option><option value="facebook">Facebook</option><option value="whatsapp">WhatsApp</option><option value="telegram">Telegram</option><option value="webchat">Webchat</option><option value="referral">Referral</option><option value="direct_call">Direct Call</option></select>
        <button onclick="openModal('modal-add-lead')" class="flex items-center gap-1.5 px-3.5 sm:px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition-all"><svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>New Lead</button>
      </div>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
      @php $sCts=$leads->groupBy('status')->map->count(); @endphp
      <button onclick="qfLead('')" class="sp-btn px-3 py-1.5 rounded-xl text-[11px] font-semibold bg-indigo-600 text-white" data-s="">All <span class="opacity-70">{{ $totalLeads }}</span></button>
      @foreach(['new','contacted','qualified','converted','lost'] as $st) @php $c=$sCts->get($st,0); @endphp @if($c>0)<button onclick="qfLead('{{ $st }}')" class="sp-btn px-3 py-1.5 rounded-xl text-[11px] font-semibold bg-slate-800 text-slate-400 hover:text-white transition-all" data-s="{{ $st }}">{{ ucfirst($st) }} <span class="opacity-70">{{ $c }}</span></button>@endif @endforeach
    </div>
    <div class="rounded-2xl border border-slate-800 overflow-hidden bg-slate-900/50">
      <div class="overflow-x-auto w-full">
      <table class="w-full text-left min-w-[700px]">
        <thead><tr class="border-b border-slate-800 bg-slate-950/60 text-[10px] text-slate-500 uppercase font-bold tracking-widest"><th class="px-5 py-3.5">Lead / Contact</th><th class="px-4 py-3.5">Source</th><th class="px-4 py-3.5">Priority</th><th class="px-4 py-3.5">Budget</th><th class="px-4 py-3.5">Status</th><th class="px-4 py-3.5">Agent</th><th class="px-5 py-3.5 text-right">Action</th></tr></thead>
        <tbody class="divide-y divide-slate-800/40">
          @forelse($leads as $lead)
          <tr class="lead-row" data-status="{{ $lead->status }}" data-source="{{ $lead->source }}" data-search="{{ strtolower($lead->title.' '.$lead->company_name.' '.$lead->first_name.' '.$lead->email.' '.$lead->phone) }}">
            <td class="px-5 py-4"><div class="flex items-center gap-3"><div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center font-bold text-sm text-white shadow-sm {{ match($lead->source) { 'facebook' => 'bg-blue-600', 'whatsapp' => 'bg-emerald-600', 'telegram' => 'bg-sky-600', 'referral' => 'bg-purple-600', default => 'bg-slate-700' } }}">{{ strtoupper(substr($lead->first_name?:($lead->company_name?:'?'),0,1)) }}</div><div class="min-w-0"><div class="text-xs font-bold text-white truncate">{{ $lead->title }}</div><div class="text-[11px] text-slate-400 truncate flex items-center gap-1.5 mt-0.5"><span class="font-mono text-indigo-400/80 text-[9px]">{{ $lead->lead_code }}</span>@if($lead->company_name)<span class="text-slate-600">&middot;</span><span>{{ $lead->company_name }}</span>@elseif($lead->email)<span class="text-slate-600">&middot;</span><span>{{ $lead->email }}</span>@endif</div></div></div></td>
            <td class="px-4 py-4"><span class="text-[10px] font-bold px-2 py-1 rounded-lg {{ match($lead->source) { 'facebook' => 'badge-sky', 'whatsapp' => 'badge-emerald', 'referral' => 'badge-purple', default => 'badge-slate' } }}">{{ ucfirst(str_replace('_',' ',$lead->source)) }}</span></td>
            <td class="px-4 py-4"><span class="text-[10px] font-bold px-2 py-1 rounded-lg uppercase {{ match($lead->priority) { 'urgent' => 'badge-rose priority-urgent', 'high' => 'badge-amber', 'medium' => 'badge-sky', default => 'badge-slate' } }}">{{ $lead->priority }}</span></td>
            <td class="px-4 py-4"><span class="text-xs font-extrabold text-emerald-400 font-mono">${{ number_format($lead->estimated_budget,0) }}</span></td>
            <td class="px-4 py-4"><span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ match($lead->status) { 'converted' => 'badge-indigo', 'qualified' => 'badge-emerald', 'lost' => 'badge-rose', 'contacted' => 'badge-sky', default => 'badge-slate' } }}">{{ ucfirst($lead->status) }}</span></td>
            <td class="px-4 py-4"><div class="flex items-center gap-2"><div class="w-6 h-6 rounded-full bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-300">{{ strtoupper(substr($lead->assignedUser?->name??'?',0,1)) }}</div><span class="text-[11px] text-slate-400 truncate max-w-[70px]">{{ $lead->assignedUser?->name??'—' }}</span></div></td>
            <td class="px-5 py-4 text-right">@if($lead->status!=='converted')<button onclick="openConvertModal({{ $lead->id }},'{{ addslashes($lead->title) }}',{{ $lead->estimated_budget??0 }})" class="px-3 py-1.5 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-bold text-[10px] rounded-lg transition-all shadow-sm">Convert &rarr;</button>@else<span class="text-[11px] text-indigo-400 font-semibold">&#10003; Converted</span>@endif</td>
          </tr>
          @empty<tr><td colspan="7" class="py-16 text-center"><div class="text-4xl mb-3">🎯</div><div class="text-sm text-slate-400 mb-4">No leads yet</div><button onclick="openModal('modal-add-lead')" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl">+ Create First Lead</button></td></tr>@endforelse
        </tbody>
      </table>
      </div>
    </div>
  </div>

  {{-- PIPELINE --}}
  <div id="tab-deals" class="tab-panel {{ $activeTab!=='deals'?'hidden':'' }} p-4 sm:p-6 lg:p-8 flex flex-col gap-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div><h2 class="text-base font-extrabold text-white">Sales Pipeline</h2><p class="text-[11px] text-slate-500 mt-0.5">Active: <strong class="text-cyan-300">${{ number_format($totalPipelineValue,0) }}</strong> &middot; Won: <strong class="text-emerald-300">${{ number_format($wonValue,0) }}</strong> &middot; Win Rate: <strong class="text-purple-300">{{ $winRate }}%</strong></p></div>
      <button onclick="openModal('modal-add-deal')" class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition-all"><svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>New Deal</button>
    </div>
    <div class="flex gap-4 overflow-x-auto pb-4 crm-scroll">
      @php $kS=['qualification'=>['label'=>'Qualification','pct'=>'20%','c'=>'text-slate-200','border'=>'border-slate-700/60','head'=>'bg-slate-800/60','badge'=>'bg-slate-700 text-slate-200'],'proposal'=>['label'=>'Proposal','pct'=>'40%','c'=>'text-blue-200','border'=>'border-blue-800/40','head'=>'bg-blue-900/40','badge'=>'bg-blue-800/60 text-blue-200'],'negotiation'=>['label'=>'Negotiation','pct'=>'70%','c'=>'text-amber-200','border'=>'border-amber-800/40','head'=>'bg-amber-900/30','badge'=>'bg-amber-800/50 text-amber-200'],'won'=>['label'=>'Won 🎉','pct'=>'100%','c'=>'text-emerald-200','border'=>'border-emerald-700/40','head'=>'bg-emerald-900/30','badge'=>'bg-emerald-800/50 text-emerald-200'],'lost'=>['label'=>'Lost','pct'=>'0%','c'=>'text-rose-200','border'=>'border-rose-800/30','head'=>'bg-rose-900/20','badge'=>'bg-rose-800/40 text-rose-200']]; @endphp
      @foreach($kS as $sk=>$sc)
      @php $sd=$deals->where('stage',$sk); @endphp
      <div id="kanban-col-{{ $sk }}" ondragover="dOver(event)" ondragleave="dLeave(event)" ondrop="dDrop(event,'{{ $sk }}')" class="kanban-col flex-shrink-0 w-64 flex flex-col rounded-2xl border {{ $sc['border'] }} bg-slate-900/70 overflow-hidden">
        <div class="{{ $sc['head'] }} px-4 py-3 border-b {{ $sc['border'] }} flex items-center justify-between"><div><div class="text-xs font-bold {{ $sc['c'] }}">{{ $sc['label'] }}</div><div class="text-[10px] text-slate-500 mt-0.5">{{ $sc['pct'] }}</div></div><span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $sc['badge'] }}">{{ $sd->count() }}</span></div>
        <div class="flex-1 p-3 space-y-2.5 min-h-64">
          @forelse($sd as $deal)
          <div id="deal-card-{{ $deal->id }}" draggable="true" ondragstart="dStart(event,{{ $deal->id }})" class="deal-card p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-3">
            <div class="flex items-center justify-between"><span class="text-[9px] font-mono font-bold text-indigo-400">{{ $deal->deal_code }}</span><span class="text-xs font-extrabold text-emerald-400 font-mono">${{ number_format($deal->amount,0) }}</span></div>
            <div class="text-xs font-bold text-white leading-snug">{{ $deal->title }}</div>
            <div class="flex items-center gap-1.5 text-[11px] text-slate-400"><svg class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg><span class="truncate">{{ $deal->customer?->name??'Direct' }}</span></div>
            <div class="flex items-center justify-between pt-2 border-t border-slate-800/60 text-[10px]"><span class="text-slate-500">&#128197; {{ $deal->expected_close_date?->format('M d')??'N/A' }}</span><div class="flex items-center gap-1 text-slate-500"><div class="w-4 h-4 rounded-full bg-slate-800 flex items-center justify-center text-[8px] font-bold text-slate-300">{{ strtoupper(substr($deal->assignedUser?->name??'?',0,1)) }}</div><span>{{ $deal->probability }}%</span></div></div>
          </div>
          @empty<div class="py-10 text-center text-[11px] text-slate-600 border-2 border-dashed border-slate-800 rounded-xl">Drop here</div>@endforelse
        </div>
        <div class="px-4 py-2.5 border-t border-slate-800/40 bg-slate-950/40 text-[10px] text-slate-600 font-mono text-center">Total: <strong class="text-slate-400">${{ number_format($sd->sum('amount'),0) }}</strong></div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- ACTIVITIES --}}
  <div id="tab-activities" class="tab-panel {{ $activeTab!=='activities'?'hidden':'' }} p-4 sm:p-6 lg:p-8 space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div><h2 class="text-base font-extrabold text-white">Sales Activities</h2><p class="text-[11px] text-slate-500 mt-0.5">{{ $activitiesPending }} pending &middot; {{ $activitiesCompleted }} completed</p></div>
      <div class="flex items-center gap-2.5">
        <div class="flex rounded-xl border border-slate-800 overflow-hidden">
          @foreach(['all'=>'All','pending'=>'Pending','completed'=>'Done'] as $fk=>$fl)<button id="act-f-{{ $fk }}" onclick="filterActs('{{ $fk }}')" class="act-filter px-3.5 py-2 text-[11px] font-semibold transition-colors {{ $fk==='all'?'bg-indigo-600 text-white':'bg-slate-900 text-slate-400 hover:text-white' }}">{{ $fl }}</button>@endforeach
        </div>
        <button onclick="openModal('modal-add-activity')" class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition-all"><svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Schedule</button>
      </div>
    </div>
    <div class="space-y-3">
      @forelse($activities as $act)
      <div class="activity-card p-4 rounded-2xl border border-slate-800 bg-slate-900/60 {{ $act->status==='completed'?'opacity-60':'' }}" data-act-status="{{ $act->status }}">
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center text-lg {{ match($act->type) { 'call' => 'bg-blue-500/15 border border-blue-500/20', 'meeting' => 'bg-purple-500/15 border border-purple-500/20', 'follow_up' => 'bg-amber-500/15 border border-amber-500/20', default => 'bg-slate-800 border border-slate-700' } }}">{{ match($act->type) { 'call' => '📞', 'meeting' => '📅', 'follow_up' => '🔔', 'quotation_sent' => '📄', default => '✅' } }}</div>
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
              <div>
                <div class="text-sm font-bold text-white">{{ $act->subject }}</div>
                <div class="flex items-center gap-3 mt-1 text-[11px] text-slate-400">
                  <span>{{ $act->customer?->name??($act->deal?->title??'General') }}</span>
                  <span class="text-slate-600">&middot;</span>
                  <span class="{{ $act->due_date?->isPast()&&$act->status!=='completed'?'text-rose-400 font-semibold':'' }}">&#128197; {{ $act->due_date?->format('M d, Y h:i A')??'No date' }}{{ $act->due_date?->isPast() && $act->status !== 'completed' ? ' ⚠ Overdue' : '' }}</span>
                  <span class="text-slate-600">&middot;</span>
                  <span>&#128100; {{ $act->user?->name }}</span>
                </div>
                @if($act->description)<div class="text-[11px] text-slate-500 mt-1.5">{{ $act->description }}</div>@endif
                @if($act->outcome_notes)<div class="mt-2 px-3 py-1.5 rounded-lg bg-emerald-500/8 border border-emerald-500/15 text-[11px] text-emerald-400">&#10003; {{ $act->outcome_notes }}</div>@endif
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg {{ match($act->type) { 'call' => 'badge-sky', 'meeting' => 'badge-purple', 'follow_up' => 'badge-amber', default => 'badge-slate' } }}">{{ str_replace('_',' ',$act->type) }}</span>
                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $act->status==='completed'?'badge-emerald':'badge-amber' }}">{{ $act->status }}</span>
              </div>
            </div>
            @if($act->status!=='completed')
            <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center gap-2">
              <form action="{{ route('tenant.crm.activities.complete',$act->id) }}" method="POST" class="flex gap-2 flex-1">@csrf
                <input name="outcome_notes" placeholder="Outcome notes..." class="flex-1 text-[11px] bg-slate-950 border border-slate-800 rounded-lg px-3 py-1.5 text-white focus:outline-none focus:border-emerald-600">
                <button type="submit" class="px-4 py-1.5 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 text-[11px] font-bold rounded-lg border border-emerald-600/30 transition-all">&#10003; Done</button>
              </form>
            </div>
            @endif
          </div>
        </div>
      </div>
      @empty<div class="py-16 text-center"><div class="text-4xl mb-3">⚡</div><div class="text-sm text-slate-400 mb-4">No activities yet</div><button onclick="openModal('modal-add-activity')" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl">Schedule First</button></div>@endforelse
    </div>
  </div>

  {{-- ANALYTICS --}}
  <div id="tab-analytics" class="tab-panel {{ $activeTab!=='analytics'?'hidden':'' }} p-4 sm:p-6 lg:p-8 space-y-6">
    <div><h2 class="text-base font-extrabold text-white">CRM Analytics</h2><p class="text-[11px] text-slate-500 mt-0.5">Real-time insights, conversion tracking &amp; team performance</p></div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      @php $aK=[['l'=>'Lead Conversion','v'=>$conversionRate.'%','s'=>$convertedLeads.' / '.$totalLeads.' leads','b'=>min(100,$conversionRate),'bc'=>'from-indigo-600 to-violet-500','ic'=>'🎯','br'=>'border-indigo-800/30'],['l'=>'Active Pipeline','v'=>'$'.number_format($totalPipelineValue,0),'s'=>$deals->whereNotIn('stage',['lost'])->count().' deals','b'=>70,'bc'=>'from-cyan-600 to-sky-400','ic'=>'💼','br'=>'border-cyan-800/30'],['l'=>'Won Revenue','v'=>'$'.number_format($wonValue,0),'s'=>$dealsByStage['won']['count'].' won','b'=>100,'bc'=>'from-emerald-600 to-teal-400','ic'=>'💰','br'=>'border-emerald-800/30'],['l'=>'Win Rate','v'=>$winRate.'%','s'=>$dealsByStage['lost']['count'].' lost','b'=>min(100,$winRate),'bc'=>'from-purple-600 to-violet-400','ic'=>'🏆','br'=>'border-purple-800/30']]; @endphp
      @foreach($aK as $k)<div class="p-5 rounded-2xl bg-slate-900/70 border {{ $k['br'] }}"><div class="flex items-center justify-between mb-3"><span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">{{ $k['l'] }}</span><span class="text-xl">{{ $k['ic'] }}</span></div><div class="text-2xl font-extrabold text-white mb-0.5">{{ $k['v'] }}</div><div class="text-[11px] text-slate-500 mb-3">{{ $k['s'] }}</div><div class="h-1.5 bg-slate-800 rounded-full overflow-hidden"><div class="fill-bar h-full rounded-full bg-gradient-to-r {{ $k['bc'] }}" style="width:{{ $k['b'] }}%"></div></div></div>@endforeach
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <div class="col-span-1 lg:col-span-2 bg-slate-900/70 border border-slate-800 rounded-2xl p-5 space-y-4">
        <h3 class="text-xs font-extrabold text-white uppercase tracking-wide border-b border-slate-800 pb-3">Pipeline Funnel</h3>
        @php $fC=['qualification'=>['l'=>'Qualification','b'=>'bg-slate-500'],'proposal'=>['l'=>'Proposal','b'=>'bg-blue-500'],'negotiation'=>['l'=>'Negotiation','b'=>'bg-amber-500'],'won'=>['l'=>'Closed Won','b'=>'bg-emerald-500'],'lost'=>['l'=>'Closed Lost','b'=>'bg-rose-500']]; $mx=max(1,...array_column($dealsByStage,'amount')); @endphp
        @foreach($dealsByStage as $sk=>$sd)<div class="space-y-1.5"><div class="flex items-center justify-between text-xs"><span class="font-semibold text-slate-300">{{ $fC[$sk]['l'] }}</span><div class="flex items-center gap-3"><span class="text-[10px] font-mono bg-slate-800 text-slate-300 px-2 py-0.5 rounded-lg">{{ $sd['count'] }} deals</span><span class="font-extrabold font-mono text-white w-24 text-right">${{ number_format($sd['amount'],0) }}</span></div></div><div class="h-2 bg-slate-950 rounded-full overflow-hidden border border-slate-800/50"><div class="{{ $fC[$sk]['b'] }} fill-bar h-full rounded-full" style="width:{{ $mx>0?($sd['amount']/$mx)*100:0 }}%"></div></div></div>@endforeach
      </div>
      <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 space-y-3">
        <h3 class="text-xs font-extrabold text-white uppercase tracking-wide border-b border-slate-800 pb-3">Lead Sources</h3>
        @php $sI=['facebook'=>'🔵','whatsapp'=>'💬','telegram'=>'✈️','webchat'=>'🌐','direct_call'=>'📞','referral'=>'🤝']; $tS=array_sum($leadsBySource); @endphp
                @if(count($leadsBySource) > 0)
          @foreach($leadsBySource as $src => $cnt)
            @php $pct = $tS > 0 ? round(($cnt / $tS) * 100) : 0; @endphp
            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span>{{ $sI[$src] ?? '⚡' }}</span>
                  <span class="text-xs font-semibold text-slate-200">{{ ucfirst(str_replace('_', ' ', $src)) }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-xs font-bold text-indigo-400 font-mono">{{ $cnt }}</span>
                  <span class="text-[10px] text-slate-500">{{ $pct }}%</span>
                </div>
              </div>
              <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div class="fill-bar h-full bg-gradient-to-r from-indigo-600 to-violet-500 rounded-full" style="width:{{ $pct }}%"></div>
              </div>
            </div>
          @endforeach
        @else
          <div class="py-8 text-center text-xs text-slate-500">No leads yet</div>
        @endif
      </div>
    </div>
    <div class="bg-slate-900/70 border border-slate-800 rounded-2xl overflow-hidden">
      <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800"><h3 class="text-xs font-extrabold text-white uppercase tracking-wide">🏆 Team Leaderboard</h3><div class="text-[11px] text-slate-400">Done: <strong class="text-emerald-400">{{ $activitiesCompleted }}</strong> &middot; Pending: <strong class="text-amber-400">{{ $activitiesPending }}</strong></div></div>
      <table class="w-full text-left text-xs"><thead><tr class="text-[10px] text-slate-500 uppercase font-bold border-b border-slate-800 bg-slate-950/40 tracking-widest"><th class="py-3 px-6">Agent</th><th class="py-3 px-4 text-center">Leads</th><th class="py-3 px-4 text-center">Deals</th><th class="py-3 px-4 text-center">Won</th><th class="py-3 px-4 text-center">Activities</th><th class="py-3 px-6 text-right">Revenue</th></tr></thead>
      <tbody class="divide-y divide-slate-800/30">
        @foreach($agentStats->sortByDesc('revenue_closed') as $i=>$stat)<tr class="hover:bg-indigo-950/10 transition-colors"><td class="py-4 px-6"><div class="flex items-center gap-3"><div class="relative"><div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center font-bold text-white text-sm shadow-sm">{{ strtoupper(substr($stat['user']->name,0,1)) }}</div>@if($i===0&&$stat['revenue_closed']>0)<span class="absolute -top-1 -right-1 text-[10px]">🥇</span>@endif</div><div><div class="font-bold text-white">{{ $stat['user']->name }}</div><span class="text-[9px] uppercase font-bold {{ $stat['user']->role==='tenant_admin'?'badge-purple':'badge-slate' }} px-1.5 py-0.5 rounded mt-0.5 inline-block">{{ $stat['user']->role }}</span></div></div></td><td class="py-4 px-4 text-center font-bold font-mono text-slate-200">{{ $stat['leads_count'] }}</td><td class="py-4 px-4 text-center font-bold font-mono text-slate-200">{{ $stat['deals_count'] }}</td><td class="py-4 px-4 text-center"><span class="font-bold font-mono px-2.5 py-1 rounded-full badge-emerald">{{ $stat['won_count'] }}</span></td><td class="py-4 px-4 text-center font-mono text-slate-400">{{ $stat['activities_done'] }}</td><td class="py-4 px-6 text-right text-sm font-extrabold font-mono text-emerald-400">${{ number_format($stat['revenue_closed'],0) }}</td></tr>@endforeach
      </tbody></table>
    </div>
  </div>

</div>
</div>

{{-- MODALS --}}
@php $ic="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500 transition-colors"; $lc="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1.5"; @endphp
<div id="modal-add-lead" class="crm-modal fixed inset-0 bg-black/75 z-50 flex items-center justify-center p-4 hidden"><div class="crm-modal-box bg-slate-900 border border-slate-800/80 rounded-2xl w-full max-w-2xl p-6 space-y-5"><div class="flex items-center gap-3 pb-4 border-b border-slate-800"><div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-base">🎯</div><div><h3 class="text-sm font-extrabold text-white">Create New Lead</h3><p class="text-[11px] text-slate-500">Add inbound lead to pipeline</p></div><button onclick="closeModal('modal-add-lead')" class="ml-auto text-slate-500 hover:text-white text-xl">&#10005;</button></div><form action="{{ route('tenant.crm.leads.store') }}" method="POST" class="space-y-4">@csrf<div><label class="{{ $lc }}">Lead Title *</label><input type="text" name="title" required placeholder="Enterprise Software Deal" class="{{ $ic }}"></div><div class="grid grid-cols-2 gap-4"><div><label class="{{ $lc }}">First Name</label><input type="text" name="first_name" placeholder="Tanvir" class="{{ $ic }}"></div><div><label class="{{ $lc }}">Company</label><input type="text" name="company_name" placeholder="TechVista BD" class="{{ $ic }}"></div><div><label class="{{ $lc }}">Email</label><input type="email" name="email" placeholder="contact@company.com" class="{{ $ic }}"></div><div><label class="{{ $lc }}">Phone</label><input type="text" name="phone" placeholder="+8801700000000" class="{{ $ic }}"></div><div><label class="{{ $lc }}">Source</label><select name="source" class="{{ $ic }}"><option value="facebook">Facebook</option><option value="whatsapp">WhatsApp</option><option value="telegram">Telegram</option><option value="webchat">Webchat</option><option value="direct_call">Direct Call</option><option value="referral">Referral</option></select></div><div><label class="{{ $lc }}">Priority</label><select name="priority" class="{{ $ic }}"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div><div><label class="{{ $lc }}">Budget ($)</label><input type="number" name="estimated_budget" value="0" step="0.01" class="{{ $ic }}"></div><div><label class="{{ $lc }}">Agent</label><select name="assigned_user_id" class="{{ $ic }}">@foreach($agents as $ag)<option value="{{ $ag->id }}">{{ $ag->name }}</option>@endforeach</select></div></div><div class="flex justify-end gap-2.5 pt-3 border-t border-slate-800"><button type="button" onclick="closeModal('modal-add-lead')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-xs rounded-xl text-slate-300">Cancel</button><button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 font-bold text-xs rounded-xl text-white shadow-lg shadow-indigo-500/20">Create Lead</button></div></form></div></div>
<div id="modal-convert-lead" class="crm-modal fixed inset-0 bg-black/75 z-50 flex items-center justify-center p-4 hidden"><div class="crm-modal-box bg-slate-900 border border-slate-800/80 rounded-2xl w-full max-w-md p-6 space-y-5"><div class="flex items-center gap-3 pb-4 border-b border-slate-800"><div class="w-8 h-8 rounded-xl bg-gradient-to-br from-violet-600 to-indigo-600 flex items-center justify-center text-white">⚡</div><div><h3 class="text-sm font-extrabold text-white">Convert Lead &rarr; Deal</h3><p class="text-[11px] text-slate-500">Auto-creates Customer + Opportunity</p></div><button onclick="closeModal('modal-convert-lead')" class="ml-auto text-slate-500 hover:text-white text-xl">&#10005;</button></div><form id="form-convert" method="POST" class="space-y-4">@csrf<div><label class="{{ $lc }}">Deal Title</label><input type="text" name="deal_title" id="conv-title" class="{{ $ic }}"></div><div><label class="{{ $lc }}">Amount ($)</label><input type="number" name="amount" id="conv-amount" step="0.01" class="{{ $ic }}"></div><div class="flex justify-end gap-2.5 pt-3 border-t border-slate-800"><button type="button" onclick="closeModal('modal-convert-lead')" class="px-4 py-2 bg-slate-800 text-xs rounded-xl text-slate-300">Cancel</button><button type="submit" class="px-6 py-2 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 font-bold text-xs rounded-xl text-white shadow-lg">Confirm &rarr;</button></div></form></div></div>
<div id="modal-add-deal" class="crm-modal fixed inset-0 bg-black/75 z-50 flex items-center justify-center p-4 hidden"><div class="crm-modal-box bg-slate-900 border border-slate-800/80 rounded-2xl w-full max-w-lg p-6 space-y-5"><div class="flex items-center gap-3 pb-4 border-b border-slate-800"><div class="w-8 h-8 rounded-xl bg-cyan-600 flex items-center justify-center text-white">📊</div><div><h3 class="text-sm font-extrabold text-white">New Opportunity</h3><p class="text-[11px] text-slate-500">Add deal to pipeline</p></div><button onclick="closeModal('modal-add-deal')" class="ml-auto text-slate-500 hover:text-white text-xl">&#10005;</button></div><form action="{{ route('tenant.crm.deals.store') }}" method="POST" class="space-y-4">@csrf<div><label class="{{ $lc }}">Deal Title *</label><input type="text" name="title" required placeholder="Annual License" class="{{ $ic }}"></div><div class="grid grid-cols-2 gap-4"><div><label class="{{ $lc }}">Amount *</label><input type="number" name="amount" required step="0.01" placeholder="25000" class="{{ $ic }}"></div><div><label class="{{ $lc }}">Stage</label><select name="stage" class="{{ $ic }}"><option value="qualification">Qualification</option><option value="proposal">Proposal</option><option value="negotiation">Negotiation</option><option value="won">Won</option></select></div><div><label class="{{ $lc }}">Probability %</label><input type="number" name="probability" value="40" min="0" max="100" class="{{ $ic }}"></div><div><label class="{{ $lc }}">Close Date</label><input type="date" name="expected_close_date" class="{{ $ic }}"></div><div class="col-span-2"><label class="{{ $lc }}">Customer</label><select name="customer_id" class="{{ $ic }}"><option value="">— None —</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div></div><div class="flex justify-end gap-2.5 pt-3 border-t border-slate-800"><button type="button" onclick="closeModal('modal-add-deal')" class="px-4 py-2 bg-slate-800 text-xs rounded-xl text-slate-300">Cancel</button><button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 font-bold text-xs rounded-xl text-white shadow-lg shadow-indigo-500/20">Create Deal</button></div></form></div></div>
<div id="modal-add-activity" class="crm-modal fixed inset-0 bg-black/75 z-50 flex items-center justify-center p-4 hidden"><div class="crm-modal-box bg-slate-900 border border-slate-800/80 rounded-2xl w-full max-w-lg p-6 space-y-5"><div class="flex items-center gap-3 pb-4 border-b border-slate-800"><div class="w-8 h-8 rounded-xl bg-amber-600 flex items-center justify-center text-white">⚡</div><div><h3 class="text-sm font-extrabold text-white">Schedule Activity</h3><p class="text-[11px] text-slate-500">Plan call, meeting, or follow-up</p></div><button onclick="closeModal('modal-add-activity')" class="ml-auto text-slate-500 hover:text-white text-xl">&#10005;</button></div><form action="{{ route('tenant.crm.activities.store') }}" method="POST" class="space-y-4">@csrf<div class="grid grid-cols-2 gap-4"><div><label class="{{ $lc }}">Type</label><select name="type" class="{{ $ic }}"><option value="call">Phone Call</option><option value="meeting">Meeting</option><option value="follow_up">Follow-up</option><option value="task">Task</option><option value="quotation_sent">Quotation</option></select></div><div><label class="{{ $lc }}">Due Date *</label><input type="datetime-local" name="due_date" required class="{{ $ic }}"></div><div class="col-span-2"><label class="{{ $lc }}">Subject *</label><input type="text" name="subject" required placeholder="Follow up on Q4 proposal" class="{{ $ic }}"></div><div><label class="{{ $lc }}">Customer</label><select name="customer_id" class="{{ $ic }}"><option value="">— None —</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div><div><label class="{{ $lc }}">Agent</label><select name="user_id" class="{{ $ic }}">@foreach($agents as $ag)<option value="{{ $ag->id }}">{{ $ag->name }}</option>@endforeach</select></div><div class="col-span-2"><label class="{{ $lc }}">Description</label><textarea name="description" rows="2" placeholder="Context..." class="{{ $ic }} resize-none"></textarea></div></div><div class="flex justify-end gap-2.5 pt-3 border-t border-slate-800"><button type="button" onclick="closeModal('modal-add-activity')" class="px-4 py-2 bg-slate-800 text-xs rounded-xl text-slate-300">Cancel</button><button type="submit" class="px-6 py-2 bg-amber-600 hover:bg-amber-500 font-bold text-xs rounded-xl text-white shadow-lg shadow-amber-500/15">Schedule</button></div></form></div></div>

<script>
function switchTab(n){document.querySelectorAll('.tab-panel').forEach(p=>p.classList.add('hidden'));document.querySelectorAll('.crm-tab').forEach(b=>b.classList.remove('active'));document.getElementById('tab-'+n)?.classList.remove('hidden');document.getElementById('tab-btn-'+n)?.classList.add('active');const u=new URL(window.location);u.searchParams.set('tab',n);window.history.replaceState({},'',u);}
function openModal(id){document.getElementById(id)?.classList.remove('hidden');}
function closeModal(id){document.getElementById(id)?.classList.add('hidden');}
document.querySelectorAll('.crm-modal').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)closeModal(m.id);}));
function toggleDD(){document.getElementById('qc-drop').classList.toggle('hidden');}
function hideDD(){document.getElementById('qc-drop').classList.add('hidden');}
document.addEventListener('click',e=>{if(!e.target.closest('#qc-drop')&&!e.target.closest('#fab-new'))hideDD();});
function openConvertModal(id,title,budget){document.getElementById('form-convert').action=`/tenant/crm/leads/${id}/convert`;document.getElementById('conv-title').value='Deal: '+title;document.getElementById('conv-amount').value=budget||0;openModal('modal-convert-lead');}
function filterLeads(){const q=(document.getElementById('lead-search')?.value||'').toLowerCase(),st=document.getElementById('lead-status-filter')?.value||'',src=document.getElementById('lead-source-filter')?.value||'';document.querySelectorAll('.lead-row').forEach(r=>{r.style.display=(!q||r.dataset.search.includes(q))&&(!st||r.dataset.status===st)&&(!src||r.dataset.source===src)?'':'none';});}
function qfLead(s){document.getElementById('lead-status-filter').value=s;filterLeads();document.querySelectorAll('.sp-btn').forEach(b=>{const a=b.dataset.s===s;b.classList.toggle('bg-indigo-600',a);b.classList.toggle('text-white',a);b.classList.toggle('bg-slate-800',!a);b.classList.toggle('text-slate-400',!a);});}
function filterActs(s){document.querySelectorAll('.act-filter').forEach(b=>{b.classList.remove('bg-indigo-600','text-white');b.classList.add('bg-slate-900','text-slate-400');});const b=document.getElementById('act-f-'+s);b?.classList.add('bg-indigo-600','text-white');b?.classList.remove('bg-slate-900','text-slate-400');document.querySelectorAll('.activity-card').forEach(c=>{c.style.display=(s==='all'||c.dataset.actStatus===s)?'':'none';});}
let _dd=null;
function dStart(e,id){_dd=id;e.dataTransfer.effectAllowed='move';setTimeout(()=>document.getElementById('deal-card-'+id)?.classList.add('dragging'),0);}
function dOver(e){e.preventDefault();e.currentTarget.classList.add('drag-over');}
function dLeave(e){e.currentTarget.classList.remove('drag-over');}
async function dDrop(e,stage){e.preventDefault();e.currentTarget.classList.remove('drag-over');if(!_dd)return;document.getElementById('deal-card-'+_dd)?.classList.remove('dragging');const r=await fetch(`/tenant/crm/deals/${_dd}/stage`,{method:'PATCH',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},body:JSON.stringify({stage})});const d=await r.json();if(d.success)location.href='{{ route("tenant.crm.hub") }}?tab=deals';}
</script>
@endsection