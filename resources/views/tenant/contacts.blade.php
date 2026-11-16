@extends('tenant.layout')

@section('title', 'CRM Contacts & Leads')

@section('content')
<div class="flex-1 overflow-y-auto p-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">CRM Contacts & Leads</h1>
            <p class="text-xs text-slate-400">All customer profiles automatically gathered from social media inquiries</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-400">Total Contacts: <strong class="text-white">{{ $contacts->total() }}</strong></span>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-800 bg-slate-950/50 text-[11px] text-slate-400 uppercase">
                    <th class="p-4 font-semibold">Contact / Customer</th>
                    <th class="p-4 font-semibold">Platform</th>
                    <th class="p-4 font-semibold">Stage</th>
                    <th class="p-4 font-semibold">Deal Value</th>
                    <th class="p-4 font-semibold">Tags</th>
                    <th class="p-4 font-semibold">Last Active</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse($contacts as $contact)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center font-bold text-white text-xs">
                                    {{ strtoupper(substr($contact->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-white">{{ $contact->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $contact->email ?: ($contact->phone ?: $contact->platform_sender_id) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                @if($contact->platform === 'facebook') bg-blue-500/20 text-blue-400
                                @elseif($contact->platform === 'whatsapp') bg-emerald-500/20 text-emerald-400
                                @elseif($contact->platform === 'telegram') bg-sky-500/20 text-sky-400
                                @else bg-purple-500/20 text-purple-400 @endif">
                                {{ $contact->platform }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold capitalize bg-slate-800 text-slate-300">
                                {{ $contact->pipeline_stage }}
                            </span>
                        </td>
                        <td class="p-4 font-bold text-emerald-400">
                            ${{ number_format($contact->deal_value, 2) }}
                        </td>
                        <td class="p-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($contact->tags ?? [] as $t)
                                    <span class="px-1.5 py-0.2 rounded text-[9px] bg-indigo-500/20 text-indigo-300">{{ $t }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="p-4 text-slate-400 text-[11px]">
                            {{ $contact->last_active_at ? $contact->last_active_at->diffForHumans() : 'Never' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-500">No contacts recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t border-slate-800">
            {{ $contacts->links() }}
        </div>
    </div>
</div>
@endsection
