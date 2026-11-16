@extends('tenant.layout')

@section('title', 'Team Support Agents')

@section('content')
<div class="flex-1 overflow-y-auto p-8 max-w-4xl mx-auto w-full space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Team Support Agents</h1>
            <p class="text-xs text-slate-400">Manage support agents and their access permissions (Limit: {{ count($agents) }} / {{ $tenant->max_agents }} agents)</p>
        </div>

        @if(count($agents) < $tenant->max_agents)
            <button onclick="document.getElementById('modal-add-agent').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-xl text-xs font-bold text-white shadow-lg transition-all">
                + Add Support Agent
            </button>
        @else
            <span class="text-xs text-amber-400 font-semibold">Agent Limit Reached</span>
        @endif
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs flex items-center gap-2">
            <span>⚠️</span> {{ session('error') }}
        </div>
    @endif

    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-800 bg-slate-950/50 text-[11px] text-slate-400 uppercase">
                    <th class="p-4 font-semibold">Agent Name</th>
                    <th class="p-4 font-semibold">Email</th>
                    <th class="p-4 font-semibold">Role</th>
                    <th class="p-4 font-semibold">Permissions</th>
                    <th class="p-4 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach($agents as $agent)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="p-4 font-bold text-white flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center text-xs">
                                {{ strtoupper(substr($agent->name, 0, 1)) }}
                            </div>
                            <span>{{ $agent->name }}</span>
                        </td>
                        <td class="p-4 text-slate-300">{{ $agent->email }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold capitalize 
                                {{ $agent->role === 'tenant_admin' ? 'bg-indigo-500/20 text-indigo-300' : 'bg-slate-800 text-slate-300' }}">
                                {{ str_replace('_', ' ', $agent->role) }}
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="flex flex-wrap gap-1">
                                @if($agent->role === 'tenant_admin')
                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-purple-500/20 text-purple-300">All Permissions</span>
                                @else
                                    @foreach($agent->permissions ?? [] as $perm)
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-400">{{ $perm }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $agent->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                                {{ $agent->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Agent -->
<div id="modal-add-agent" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white">Add Team Support Agent</h3>
            <button onclick="document.getElementById('modal-add-agent').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ route('tenant.agents.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Full Name</label>
                <input type="text" name="name" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Password</label>
                <input type="password" name="password" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Role</label>
                <select name="role" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                    <option value="agent">Support Agent (Can chat & manage assigned leads)</option>
                    <option value="tenant_admin">Company Supervisor / Co-Admin</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">Permissions</label>
                <div class="space-y-1.5 text-xs">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="permissions[]" value="can_reply" checked class="rounded text-indigo-600">
                        <span>Can reply to customer inquiries</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="permissions[]" value="can_manage_leads" checked class="rounded text-indigo-600">
                        <span>Can update lead stages & deal values</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('modal-add-agent').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-xs rounded-xl text-slate-300">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 font-bold text-xs rounded-xl text-white shadow-lg">Save Agent</button>
            </div>
        </form>
    </div>
</div>
@endsection
