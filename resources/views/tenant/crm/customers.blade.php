@extends('tenant.layout')

@section('title', 'ERP Customer Accounts')

@section('content')
<div class="flex-1 overflow-y-auto p-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">ERP Customer Accounts</h1>
            <p class="text-xs text-slate-400">Manage client organizations, credit limits, payment terms, and 360-degree customer records</p>
        </div>
        <button onclick="document.getElementById('modal-add-customer').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-xl text-xs font-bold text-white shadow-lg transition-all flex items-center gap-1.5">
            <span>+ Add Customer Account</span>
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    <!-- Search & Filters -->
    <div class="p-4 bg-slate-900/80 border border-slate-800 rounded-2xl flex items-center justify-between gap-4">
        <form action="{{ route('tenant.crm.customers') }}" method="GET" class="flex items-center gap-3 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer code, company name, email, phone..." class="w-80 bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">

            <select name="type" onchange="this.form.submit()" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none">
                <option value="">All Account Types</option>
                <option value="company" {{ request('type') === 'company' ? 'selected' : '' }}>Company (B2B)</option>
                <option value="individual" {{ request('type') === 'individual' ? 'selected' : '' }}>Individual (B2C)</option>
            </select>

            <button type="submit" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-xs font-semibold rounded-xl text-slate-200">Filter</button>
            @if(request()->hasAny(['search', 'type']))
                <a href="{{ route('tenant.crm.customers') }}" class="text-xs text-slate-500 hover:text-slate-300">Reset</a>
            @endif
        </form>

        <span class="text-xs text-slate-400">Total Accounts: <strong class="text-white">{{ $customers->total() }}</strong></span>
    </div>

    <!-- Accounts Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-800 bg-slate-950/50 text-[11px] text-slate-400 uppercase">
                    <th class="p-4 font-semibold">Account / Company</th>
                    <th class="p-4 font-semibold">Type</th>
                    <th class="p-4 font-semibold">Contact Person</th>
                    <th class="p-4 font-semibold">Payment Terms</th>
                    <th class="p-4 font-semibold">Credit Limit</th>
                    <th class="p-4 font-semibold">Lifetime Revenue</th>
                    <th class="p-4 font-semibold">Deals</th>
                    <th class="p-4 font-semibold text-right">360° Profile</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse($customers as $cust)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="p-4">
                            <span class="text-[10px] font-mono font-bold text-indigo-400 block">{{ $cust->customer_code }}</span>
                            <a href="{{ route('tenant.crm.customers.show', $cust->id) }}" class="font-bold text-white text-xs hover:text-indigo-400 transition-colors">
                                {{ $cust->name }}
                            </a>
                            <div class="text-[11px] text-slate-400">{{ $cust->email ?: $cust->phone }}</div>
                        </td>

                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $cust->type === 'company' ? 'bg-purple-500/20 text-purple-300' : 'bg-blue-500/20 text-blue-300' }}">
                                {{ $cust->type }}
                            </span>
                        </td>

                        <td class="p-4 text-slate-300">
                            {{ $cust->contact_person ?: '—' }}
                        </td>

                        <td class="p-4 text-slate-400">
                            {{ $cust->payment_terms }}
                        </td>

                        <td class="p-4 font-bold text-slate-300">
                            ${{ number_format($cust->credit_limit, 2) }}
                        </td>

                        <td class="p-4 font-bold text-emerald-400">
                            ${{ number_format($cust->total_revenue, 2) }}
                        </td>

                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 font-bold text-[10px]">
                                {{ $cust->deals_count }} Deals
                            </span>
                        </td>

                        <td class="p-4 text-right">
                            <a href="{{ route('tenant.crm.customers.show', $cust->id) }}" class="px-3 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-300 font-semibold transition-colors">
                                View 360° &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-500">No customer accounts registered yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t border-slate-800">
            {{ $customers->links() }}
        </div>
    </div>
</div>

<!-- Modal: Add Customer Account -->
<div id="modal-add-customer" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white">Create ERP Customer Account</h3>
            <button onclick="document.getElementById('modal-add-customer').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ route('tenant.crm.customers.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Company / Client Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Apex Corporation" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Account Type</label>
                    <select name="type" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                        <option value="company">Company (B2B)</option>
                        <option value="individual">Individual (B2C)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Contact Person</label>
                    <input type="text" name="contact_person" placeholder="e.g. Managing Director" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tax ID / Trade License</label>
                    <input type="text" name="tax_id" placeholder="e.g. BIN-9928123" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Email</label>
                    <input type="email" name="email" placeholder="billing@apex.com" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Phone</label>
                    <input type="text" name="phone" placeholder="+8801700112233" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Payment Terms</label>
                    <select name="payment_terms" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                        <option value="Cash">Cash in Advance</option>
                        <option value="Net 15">Net 15 Days</option>
                        <option value="Net 30" selected>Net 30 Days</option>
                        <option value="COD">Cash on Delivery (COD)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Credit Limit ($)</label>
                    <input type="number" name="credit_limit" value="5000" step="0.01" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Billing Address</label>
                <textarea name="billing_address" rows="2" placeholder="Full legal billing address" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('modal-add-customer').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-xs rounded-xl text-slate-300">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 font-bold text-xs rounded-xl text-white shadow-lg">Save Customer Account</button>
            </div>
        </form>
    </div>
</div>
@endsection
