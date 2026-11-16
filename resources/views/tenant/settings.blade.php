@extends('tenant.layout')

@section('title', 'Widget & Setup')

@section('content')
<div class="flex-1 overflow-y-auto p-8 max-w-4xl mx-auto w-full space-y-6">
    <div>
        <h1 class="text-xl font-bold text-white tracking-tight">Website Chat Widget & Integrations</h1>
        <p class="text-xs text-slate-400">Embed live chat on your website or connect with external applications</p>
    </div>

    <!-- Web Widget Embed Code -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-4">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xs">
                🌐
            </div>
            <div>
                <h3 class="text-sm font-bold text-white">Embed Live Chat on your Website</h3>
                <p class="text-xs text-slate-400">Add this one line of script before the <code>&lt;/body&gt;</code> tag on any HTML, WordPress, or Laravel site:</p>
            </div>
        </div>

        <div class="p-4 bg-slate-950 rounded-xl border border-slate-800 font-mono text-xs text-indigo-300 relative">
            <code>&lt;script src="{{ url('/widget.js') }}" data-channel="{{ $webWidgetChannel->id ?? 1 }}"&gt;&lt;/script&gt;</code>
        </div>
    </div>

    <!-- Webhook Integration -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-4">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold text-xs">
                ⚡
            </div>
            <div>
                <h3 class="text-sm font-bold text-white">Outbound Webhooks</h3>
                <p class="text-xs text-slate-400">Automatically push message events to your company CRM or ERP</p>
            </div>
        </div>

        <div class="p-4 bg-slate-950 rounded-xl border border-slate-800 text-xs text-slate-400 space-y-2">
            <div class="flex items-center justify-between">
                <span>Webhook Delivery:</span>
                <span class="text-emerald-400 font-bold">Active</span>
            </div>
            <div class="flex items-center justify-between">
                <span>Signature Header:</span>
                <code class="text-slate-300 font-mono text-[11px]">X-Connector-Signature (HMAC SHA-256)</code>
            </div>
        </div>
    </div>
</div>
@endsection
