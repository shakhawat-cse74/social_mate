@extends('tenant.layout')

@section('title', 'ERP Leads Management')

@section('content')
<div class="flex-1 overflow-y-auto p-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">ERP Leads Management</h1>
            <p class="text-xs text-slate-400">Qualify inbound social media and direct leads, and convert them to ERP Customers & Deals</p>
        </div>
        <button onclick="document.getElementById('modal-add-lead').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-xl text-xs font-bold text-white shadow-lg transition-all flex items-center gap-1.5">
            <span>+ Create ERP Lead</span>
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    <!-- Filters & Search -->
    <div class="p-4 bg-slate-900/80 border border-slate-800 rounded-2xl flex items-center justify-between gap-4">
        <form action="{{ route('tenant.crm.leads') }}" method="GET" class="flex items-center gap-3 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search lead title, company, email, phone..." class="w-72 bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">

            <select name="status" onchange="this.form.submit()" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>Contacted</option>
                <option value="qualified" {{ request('status') === 'qualified' ? 'selected' : '' }}>Qualified</option>
                <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted</option>
                <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Lost</option>
            </select>

            <select name="source" onchange="this.form.submit()" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none">
                <option value="">All Sources</option>
                <option value="facebook" {{ request('source') === 'facebook' ? 'selected' : '' }}>Facebook</option>
                <option value="whatsapp" {{ request('source') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                <option value="telegram" {{ request('source') === 'telegram' ? 'selected' : '' }}>Telegram</option>
                <option value="webchat" {{ request('source') === 'webchat' ? 'selected' : '' }}>Webchat</option>
                <option value="direct_call" {{ request('source') === 'direct_call' ? 'selected' : '' }}>Direct Call</option>
            </select>

            <button type="submit" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-xs font-semibold rounded-xl text-slate-200">Filter</button>
            @if(request()->hasAny(['search', 'status', 'source']))
                <a href="{{ route('tenant.crm.leads') }}" class="text-xs text-slate-500 hover:text-slate-300">Reset</a>
            @endif
        </form>

        <span class="text-xs text-slate-400">Total: <strong class="text-white">{{ $leads->total() }}</strong> Leads</span>
    </div>

    <!-- Leads Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-800 bg-slate-950/50 text-[11px] text-slate-400 uppercase">
                    <th class="p-4 font-semibold">Lead Code & Title</th>
                    <th class="p-4 font-semibold">Contact / Company</th>
                    <th class="p-4 font-semibold">Source</th>
                    <th class="p-4 font-semibold">Priority</th>
                    <th class="p-4 font-semibold">Est. Budget</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold">Assigned Agent</th>
                    <th class="p-4 font-semibold text-right">Conversion & Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse($leads as $lead)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="p-4">
                            <span class="text-[10px] font-mono font-bold text-indigo-400 block">{{ $lead->lead_code }}</span>
                            <span class="font-bold text-white text-xs">{{ $lead->title }}</span>
                        </td>

                        <td class="p-4">
                            <div class="font-semibold text-slate-200">{{ $lead->company_name ?: ($lead->first_name . ' ' . $lead->last_name) }}</div>
                            <div class="text-[11px] text-slate-400">{{ $lead->email ?: ($lead->phone ?: 'No direct contact') }}</div>
                        </td>

                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                @if($lead->source === 'facebook') bg-blue-500/20 text-blue-400
                                @elseif($lead->source === 'whatsapp') bg-emerald-500/20 text-emerald-400
                                @elseif($lead->source === 'telegram') bg-sky-500/20 text-sky-400
                                @else bg-slate-800 text-slate-300 @endif">
                                {{ $lead->source }}
                            </span>
                        </td>

                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                @if($lead->priority === 'urgent') bg-rose-500/20 text-rose-400
                                @elseif($lead->priority === 'high') bg-amber-500/20 text-amber-400
                                @else bg-slate-800 text-slate-400 @endif">
                                {{ $lead->priority }}
                            </span>
                        </td>

                        <td class="p-4 font-bold text-emerald-400">
                            ${{ number_format($lead->estimated_budget, 2) }}
                        </td>

                        <td class="p-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize
                                @if($lead->status === 'converted') bg-purple-500/20 text-purple-300 border border-purple-500/30
                                @elseif($lead->status === 'qualified') bg-emerald-500/20 text-emerald-300
                                @elseif($lead->status === 'contacted') bg-blue-500/20 text-blue-300
                                @else bg-slate-800 text-slate-400 @endif">
                                {{ $lead->status }}
                            </span>
                        </td>

                        <td class="p-4 text-slate-300">
                            {{ $lead->assignedUser?->name ?? 'Unassigned' }}
                        </td>

                        <td class="p-4 text-right space-x-2">
                            @if($lead->status !== 'converted')
                                <button onclick="openConvertModal({{ $lead->id }}, '{{ addslashes($lead->title) }}', {{ $lead->estimated_budget }})" class="px-2.5 py-1 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-300 border border-indigo-500/30 text-[11px] font-semibold transition-colors">
                                    Convert to Customer &rarr;
                                </button>
                            @else
                                <a href="{{ route('tenant.crm.customers.show', $lead->converted_customer_id) }}" class="text-[11px] text-purple-400 font-semibold hover:underline">
                                    View Customer #{{ $lead->converted_customer_id }}
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-500">No leads recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t border-slate-800">
            {{ $leads->links() }}
        </div>
    </div>
</div>

<!-- Modal: Create ERP Lead -->
<div id="modal-add-lead" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white">Create New ERP Sales Lead</h3>
            <button onclick="document.getElementById('modal-add-lead').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ route('tenant.crm.leads.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Lead Title / Requirement *</label>
                <input type="text" name="title" required placeholder="e.g. Bulk 50 Laptop Inquiry from City Bank" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Company / Organization</label>
                    <input type="text" name="company_name" placeholder="e.g. City Bank Ltd" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Contact Person Name</label>
                    <input type="text" name="first_name" placeholder="e.g. Tanvir Rahman" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Email</label>
                    <input type="email" name="email" placeholder="contact@company.com" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Phone Number</label>
                    <input type="text" name="phone" placeholder="+8801700000000" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Source</label>
                    <select name="source" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                        <option value="facebook">Facebook Page</option>
                        <option value="whatsapp">WhatsApp Hotline</option>
                        <option value="telegram">Telegram Bot</option>
                        <option value="webchat">Webchat Widget</option>
                        <option value="direct_call">Direct Call</option>
                        <option value="referral">Client Referral</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Priority</label>
                    <select name="priority" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent 🔥</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Estimated Budget ($)</label>
                    <input type="number" name="estimated_budget" value="0" step="0.01" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Assign Sales Representative</label>
                <select name="assigned_user_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->name }} ({{ $agent->role }})</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('modal-add-lead').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-xs rounded-xl text-slate-300">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 font-bold text-xs rounded-xl text-white shadow-lg">Save Lead</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Convert Lead to ERP Customer & Opportunity -->
<div id="modal-convert-lead" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white">Convert Lead to Customer & Deal</h3>
            <button onclick="document.getElementById('modal-convert-lead').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form id="form-convert-lead" method="POST" class="space-y-4">
            @csrf
            <p class="text-xs text-slate-300">
                Converting this lead will automatically create an <strong>ERP Customer Account</strong> and an associated <strong>Sales Opportunity / Deal</strong>.
            </p>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Deal Title</label>
                <input type="text" name="deal_title" id="convert-deal-title" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Deal Amount ($)</label>
                <input type="number" name="amount" id="convert-deal-amount" step="0.01" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('modal-convert-lead').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-xs rounded-xl text-slate-300">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 font-bold text-xs rounded-xl text-white shadow-lg">Confirm Conversion</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openConvertModal(leadId, leadTitle, leadBudget) {
        document.getElementById('form-convert-lead').action = `/tenant/crm/leads/${leadId}/convert`;
        document.getElementById('convert-deal-title').value = "Deal: " + leadTitle;
        document.getElementById('convert-deal-amount').value = leadBudget || 0;
        document.getElementById('modal-convert-lead').classList.remove('hidden');
    }
</script>
@endsection
