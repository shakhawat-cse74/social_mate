@extends('tenant.layout')

@section('title', $customer->name . ' — 360° View')

@section('content')
<div class="flex-1 overflow-y-auto p-8 space-y-6">
    <!-- Customer Header -->
    <div class="flex items-center justify-between pb-6 border-b border-slate-800">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 via-violet-600 to-pink-500 flex items-center justify-center font-extrabold text-white text-xl shadow-lg">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-xl font-bold text-white tracking-tight">{{ $customer->name }}</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        {{ $customer->customer_code }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300">
                        {{ $customer->type }}
                    </span>
                </div>
                <div class="text-xs text-slate-400 mt-1 flex items-center gap-4">
                    <span>📧 {{ $customer->email ?: 'No email' }}</span>
                    <span>📞 {{ $customer->phone ?: 'No phone' }}</span>
                    <span>👤 {{ $customer->contact_person ?: 'No contact person' }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <button onclick="document.getElementById('modal-add-activity').classList.remove('hidden')" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs rounded-xl transition-all border border-slate-700 flex items-center gap-1.5">
                <span>📅 Schedule Activity</span>
            </button>
            <button onclick="document.getElementById('modal-add-deal').classList.remove('hidden')" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-1.5">
                <span>+ New Deal</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    <!-- 4 Key Metrics -->
    <div class="grid grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Lifetime Revenue</div>
            <div class="text-xl font-bold text-emerald-400 mt-1">${{ number_format($customer->total_revenue, 2) }}</div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Credit Limit</div>
            <div class="text-xl font-bold text-white mt-1">${{ number_format($customer->credit_limit, 2) }}</div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Payment Terms</div>
            <div class="text-xl font-bold text-purple-400 mt-1">{{ $customer->payment_terms }}</div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Opportunities / Deals</div>
            <div class="text-xl font-bold text-indigo-400 mt-1">{{ count($customer->deals) }} Active</div>
        </div>
    </div>

    <!-- 360 Tab Sections -->
    <div class="space-y-6">
        <!-- SECTION 1: Opportunities / Deals -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="text-lg">💼</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200">Sales Opportunities & Deals</h3>
                </div>
                <span class="text-xs text-slate-400">{{ count($customer->deals) }} Deals Registered</span>
            </div>

            <div class="space-y-2">
                @forelse($customer->deals as $deal)
                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800/80 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-white">{{ $deal->title }}</div>
                            <span class="text-[10px] font-mono text-indigo-400">{{ $deal->deal_code }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase
                                @if($deal->stage === 'won') bg-emerald-500/20 text-emerald-300
                                @elseif($deal->stage === 'lost') bg-rose-500/20 text-rose-300
                                @else bg-indigo-500/20 text-indigo-300 @endif">
                                {{ $deal->stage }} ({{ $deal->probability }}%)
                            </span>
                            <span class="text-xs font-bold text-emerald-400">${{ number_format($deal->amount, 2) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-4 text-center text-xs text-slate-500">No deals created yet.</div>
                @endforelse
            </div>
        </div>

        <!-- SECTION 2: Connected Social Conversations -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="text-lg">💬</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200">Connected Social Media Inquiries</h3>
                </div>
                <span class="text-xs text-slate-400">Linked by phone/email</span>
            </div>

            <div class="space-y-2">
                @forelse($linkedConversations as $lConv)
                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800/80 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                @if($lConv->channel->platform === 'facebook') bg-blue-500/20 text-blue-400
                                @elseif($lConv->channel->platform === 'whatsapp') bg-emerald-500/20 text-emerald-400
                                @elseif($lConv->channel->platform === 'telegram') bg-sky-500/20 text-sky-400
                                @else bg-purple-500/20 text-purple-400 @endif">
                                {{ $lConv->channel->platform }}
                            </span>
                            <span class="text-xs text-slate-300">{{ $lConv->latestMessage->content ?? 'No message' }}</span>
                        </div>
                        <a href="{{ route('tenant.inbox') }}" class="text-xs text-indigo-400 hover:underline font-semibold">
                            Open in Live Inbox &rarr;
                        </a>
                    </div>
                @empty
                    <div class="py-4 text-center text-xs text-slate-500">No social messages directly linked to this email or phone.</div>
                @endforelse
            </div>
        </div>

        <!-- SECTION 3: Planned Sales Activities -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📅</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200">Scheduled Activities & Follow-ups</h3>
                </div>
                <button onclick="document.getElementById('modal-add-activity').classList.remove('hidden')" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">+ Schedule Action</button>
            </div>

            <div class="space-y-2">
                @forelse($customer->activities as $act)
                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800/80 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-white flex items-center gap-2">
                                <span class="capitalize text-slate-400">[{{ $act->type }}]</span>
                                <span>{{ $act->subject }}</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Due: {{ $act->due_date ? $act->due_date->format('M d, Y h:i A') : 'No due date' }} • Assigned to {{ $act->user->name }}</div>
                        </div>

                        <div>
                            @if($act->status === 'completed')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300">Completed</span>
                            @else
                                <form action="{{ route('tenant.crm.activities.complete', $act->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-xs text-slate-200 font-semibold rounded-lg border border-slate-700">
                                        ✓ Mark Done
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-4 text-center text-xs text-slate-500">No scheduled activities yet.</div>
                @endforelse
            </div>
        </div>

        <!-- SECTION 4: Legal & Billing Information -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200 pb-2 border-b border-slate-800">Company & Billing Profile</h3>
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-500 block mb-0.5">Billing Address:</span>
                    <p class="text-slate-300">{{ $customer->billing_address ?: 'Not provided' }}</p>
                </div>
                <div>
                    <span class="text-slate-500 block mb-0.5">Shipping Address:</span>
                    <p class="text-slate-300">{{ $customer->shipping_address ?: 'Same as billing' }}</p>
                </div>
                <div>
                    <span class="text-slate-500 block mb-0.5">Tax / VAT ID:</span>
                    <p class="text-slate-300 font-mono">{{ $customer->tax_id ?: 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-slate-500 block mb-0.5">Website:</span>
                    <p class="text-indigo-400">{{ $customer->website ?: 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Deal -->
<div id="modal-add-deal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white">Create Deal for {{ $customer->name }}</h3>
            <button onclick="document.getElementById('modal-add-deal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ route('tenant.crm.deals.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Deal Title *</label>
                <input type="text" name="title" required placeholder="e.g. Annual Equipment Maintenance Contract" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Deal Value ($)</label>
                    <input type="number" name="amount" required step="0.01" placeholder="1500.00" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Stage</label>
                    <select name="stage" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                        <option value="qualification">Qualification (20%)</option>
                        <option value="proposal" selected>Proposal (40%)</option>
                        <option value="negotiation">Negotiation (70%)</option>
                        <option value="won">Closed Won (100%)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Probability (%)</label>
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

<!-- Modal: Add Activity -->
<div id="modal-add-activity" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white">Schedule Sales Activity</h3>
            <button onclick="document.getElementById('modal-add-activity').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ route('tenant.crm.activities.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Activity Type</label>
                <select name="type" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                    <option value="call">Phone Call</option>
                    <option value="meeting">Online / Physical Meeting</option>
                    <option value="follow_up">Quotation Follow-up</option>
                    <option value="task">General Task</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Subject *</label>
                <input type="text" name="subject" required placeholder="e.g. Call to discuss invoice & delivery terms" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Due Date & Time *</label>
                <input type="datetime-local" name="due_date" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('modal-add-activity').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-xs rounded-xl text-slate-300">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 font-bold text-xs rounded-xl text-white shadow-lg">Save Activity</button>
            </div>
        </form>
    </div>
</div>
@endsection
