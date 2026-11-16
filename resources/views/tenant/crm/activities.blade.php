@extends('tenant.layout')

@section('title', 'Sales Activities & Follow-ups')

@section('content')
<div class="flex-1 overflow-y-auto p-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Sales Activities & Follow-ups</h1>
            <p class="text-xs text-slate-400">Plan and track customer phone calls, product meetings, tasks, and quotation deadlines</p>
        </div>
        <button onclick="document.getElementById('modal-add-activity').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-xl text-xs font-bold text-white shadow-lg transition-all flex items-center gap-1.5">
            <span>+ Schedule Sales Action</span>
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    <!-- Activities Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-800 bg-slate-950/50 text-[11px] text-slate-400 uppercase">
                    <th class="p-4 font-semibold">Type</th>
                    <th class="p-4 font-semibold">Subject / Objective</th>
                    <th class="p-4 font-semibold">Related Customer / Deal</th>
                    <th class="p-4 font-semibold">Due Date & Time</th>
                    <th class="p-4 font-semibold">Assigned Agent</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse($activities as $act)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                @if($act->type === 'call') bg-blue-500/20 text-blue-400
                                @elseif($act->type === 'meeting') bg-purple-500/20 text-purple-400
                                @elseif($act->type === 'follow_up') bg-amber-500/20 text-amber-400
                                @else bg-slate-800 text-slate-300 @endif">
                                {{ str_replace('_', ' ', $act->type) }}
                            </span>
                        </td>

                        <td class="p-4">
                            <div class="font-bold text-white text-xs">{{ $act->subject }}</div>
                            <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $act->description }}</div>
                        </td>

                        <td class="p-4 text-slate-300">
                            {{ $act->customer?->name ?? ($act->deal?->title ?? 'General Inquiry') }}
                        </td>

                        <td class="p-4 text-slate-300 text-[11px]">
                            {{ $act->due_date ? $act->due_date->format('M d, Y h:i A') : 'No due date' }}
                        </td>

                        <td class="p-4 text-slate-300">
                            {{ $act->user->name }}
                        </td>

                        <td class="p-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $act->status === 'completed' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300' }}">
                                {{ $act->status }}
                            </span>
                        </td>

                        <td class="p-4 text-right">
                            @if($act->status !== 'completed')
                                <form action="{{ route('tenant.crm.activities.complete', $act->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-xs text-white font-semibold rounded-lg border border-slate-700 transition-colors">
                                        ✓ Mark Done
                                    </button>
                                </form>
                            @else
                                <span class="text-[11px] text-emerald-400 font-semibold">Done ✓</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500">No scheduled activities found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t border-slate-800">
            {{ $activities->links() }}
        </div>
    </div>
</div>

<!-- Modal: Schedule Activity -->
<div id="modal-add-activity" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white">Schedule Sales Activity</h3>
            <button onclick="document.getElementById('modal-add-activity').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ route('tenant.crm.activities.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Related Customer</label>
                <select name="customer_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                    <option value="">-- Optional / None --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Related Deal</label>
                <select name="deal_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                    <option value="">-- Optional / None --</option>
                    @foreach($deals as $d)
                        <option value="{{ $d->id }}">{{ $d->title }} (${{ $d->amount }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Activity Type</label>
                    <select name="type" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                        <option value="call">Phone Call</option>
                        <option value="meeting">Meeting (Zoom / Physical)</option>
                        <option value="follow_up">Quotation Follow-up</option>
                        <option value="task">General Task</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Due Date & Time *</label>
                    <input type="datetime-local" name="due_date" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Subject *</label>
                <input type="text" name="subject" required placeholder="e.g. Call to finalize order contract" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Description / Notes</label>
                <textarea name="description" rows="2" placeholder="Key agenda items..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('modal-add-activity').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-xs rounded-xl text-slate-300">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 font-bold text-xs rounded-xl text-white shadow-lg">Save Activity</button>
            </div>
        </form>
    </div>
</div>
@endsection
