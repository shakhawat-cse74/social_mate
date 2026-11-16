@extends('tenant.layout')

@section('title', 'Social Media Channels')

@section('content')
<div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 max-w-6xl mx-auto w-full space-y-6">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-white tracking-tight flex items-center gap-2 flex-wrap">
                <span>Social Media & Communication Channels</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-400 font-semibold border border-indigo-500/30">
                    {{ $channels->count() }} Connected
                </span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Connect, verify, and manage your Facebook Pages, WhatsApp Business, Telegram Bots, Webchat, and any custom social media channels.</p>
        </div>

        <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
            <button onclick="openNewPlatformModal()" class="px-3 sm:px-3.5 py-2 sm:py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs rounded-xl border border-slate-700 flex items-center gap-1.5 transition-all">
                <span>➕ Add Platform</span>
            </button>
            <button onclick="openConnectModal('telegram', 'Telegram Bot', 'TG', '#229ED9')" class="px-3.5 sm:px-4 py-2 sm:py-2.5 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all transform active:scale-95">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Connect Channel</span>
            </button>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>❌</span>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white">&times;</button>
        </div>
    @endif

    <!-- Live AJAX Toast Container -->
    <div id="ajax-toast" class="hidden p-4 rounded-xl text-xs flex items-center justify-between transition-all">
        <div id="ajax-toast-content" class="flex items-center gap-2"></div>
        <button onclick="document.getElementById('ajax-toast').classList.add('hidden')" class="hover:text-white">&times;</button>
    </div>

    <!-- ============================================== -->
    <!-- QUICK CONNECT AVAILABLE PLATFORMS (DYNAMIC LIST) -->
    <!-- ============================================== -->
    @php
        $connectedPlatforms = $channels->pluck('platform')->toArray();
    @endphp

    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Quick Connect Available Platforms</h2>
                <p class="text-[11px] text-slate-500 mt-0.5">Click any platform to connect, or add new social platforms dynamically</p>
            </div>
            <button type="button" onclick="openNewPlatformModal()" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1">
                <span>+ Add New Platform</span>
            </button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            @foreach($platforms as $p)
                @php 
                    $connectedChannel = $channels->where('platform', $p->slug)->first();
                    $isConfigured = !is_null($connectedChannel);
                    $isActive = $isConfigured && $connectedChannel->is_active;
                @endphp
                <div class="relative group">
                    <div class="w-full p-3.5 rounded-xl border flex flex-col items-center gap-2.5 transition-all text-center h-full justify-between
                        {{ $isActive 
                            ? 'bg-indigo-950/25 border-indigo-500/40 text-indigo-200 shadow-lg shadow-indigo-950/40' 
                            : ($isConfigured 
                                ? 'bg-amber-950/15 border-amber-500/30 text-slate-300' 
                                : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:border-slate-700') }}">
                        
                        <!-- Top Icon & Name (Clickable to open details/connect if not configured) -->
                        <div onclick="{{ $isConfigured ? "openConnectModal('{$p->slug}', '".addslashes($p->name)."', '{$p->short_code}', '{$p->color}', ".($p->is_builtin ? 'false' : 'true').")" : "openConnectModal('{$p->slug}', '".addslashes($p->name)."', '{$p->short_code}', '{$p->color}', ".($p->is_builtin ? 'false' : 'true').")" }}" class="cursor-pointer flex flex-col items-center gap-2 w-full group-hover:opacity-95" title="{{ $isConfigured ? 'Click to view/edit settings' : 'Click to configure' }}">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center font-extrabold text-white text-xs shadow-md transition-transform hover:scale-105" style="background-color: {{ $p->color }};">
                                {{ $p->short_code }}
                            </div>

                            <div class="w-full">
                                <div class="text-[11px] font-bold truncate max-w-full text-slate-200">{{ $p->name }}</div>
                            </div>
                        </div>

                        <!-- Interactive Switch Toggle (ON = Active, OFF = Paused / Not Connected) -->
                        <div class="pt-1 w-full flex items-center justify-center">
                            @if($isActive)
                                <!-- STATE 1: ACTIVE (Green ON -> Click to Pause/Turn Off) -->
                                <button type="button" onclick="toggleChannelActive({{ $connectedChannel->id }}, '{{ addslashes($p->name) }}', false)" 
                                    class="group/toggle flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/15 hover:bg-rose-500/20 border border-emerald-500/30 hover:border-rose-500/40 transition-all cursor-pointer"
                                    title="Active & Connected. Click to temporarily turn off / pause">
                                    <div class="w-7 h-3.5 bg-emerald-500 group-hover/toggle:bg-rose-500 rounded-full transition-colors relative flex items-center px-0.5">
                                        <div class="w-2.5 h-2.5 bg-white rounded-full transition-all translate-x-3.5 group-hover/toggle:translate-x-0 shadow-sm"></div>
                                    </div>
                                    <span class="text-[10px] font-bold text-emerald-400 group-hover/toggle:text-rose-400 transition-colors">On</span>
                                </button>
                            @elseif($isConfigured)
                                <!-- STATE 2: SAVED & PAUSED (Amber OFF -> Click to immediately Reconnect/Turn ON) -->
                                <button type="button" onclick="toggleChannelActive({{ $connectedChannel->id }}, '{{ addslashes($p->name) }}', true)" 
                                    class="group/toggle flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 hover:bg-emerald-500/20 border border-amber-500/30 hover:border-emerald-500/40 transition-all cursor-pointer"
                                    title="Configured & Saved. Click to reconnect / turn on without re-entering token">
                                    <div class="w-7 h-3.5 bg-slate-800 group-hover/toggle:bg-emerald-500 rounded-full transition-colors relative flex items-center px-0.5">
                                        <div class="w-2.5 h-2.5 bg-amber-400 group-hover/toggle:bg-white rounded-full transition-all translate-x-0 group-hover/toggle:translate-x-3.5 shadow-sm"></div>
                                    </div>
                                    <span class="text-[10px] font-semibold text-amber-400 group-hover/toggle:text-emerald-400 transition-colors">Off</span>
                                </button>
                            @else
                                <!-- STATE 3: NOT CONFIGURED YET (Slate OFF -> Click to Configure for first time) -->
                                <button type="button" onclick="openConnectModal('{{ $p->slug }}', '{{ addslashes($p->name) }}', '{{ $p->short_code }}', '{{ $p->color }}', {{ $p->is_builtin ? 'false' : 'true' }})" 
                                    class="group/toggle flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-900 hover:bg-indigo-600/20 border border-slate-800 hover:border-indigo-500/40 transition-all cursor-pointer"
                                    title="Not configured yet. Click to enter token and connect">
                                    <div class="w-7 h-3.5 bg-slate-800 group-hover/toggle:bg-indigo-600 rounded-full transition-colors relative flex items-center px-0.5">
                                        <div class="w-2.5 h-2.5 bg-slate-500 group-hover/toggle:bg-white rounded-full transition-all translate-x-0 shadow-sm"></div>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-500 group-hover/toggle:text-indigo-300 transition-colors">Off</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Delete button for custom added platforms -->
                    @if(!$p->is_builtin)
                        <button type="button" onclick="confirmDeletePlatform({{ $p->id }}, '{{ addslashes($p->name) }}')" class="absolute top-1.5 right-1.5 opacity-0 group-hover:opacity-100 transition-opacity z-10 w-5 h-5 rounded-full bg-slate-800 hover:bg-rose-600 text-slate-400 hover:text-white text-[10px] flex items-center justify-center transition-colors shadow" title="Remove platform">
                            &times;
                        </button>
                    @endif
                </div>
            @endforeach

            <!-- + Add Platform Card (Dashed) -->
            <button type="button" onclick="openNewPlatformModal()" class="p-3.5 rounded-xl border border-dashed border-slate-700 bg-slate-950/40 hover:bg-slate-900/80 hover:border-indigo-500 text-slate-400 hover:text-indigo-300 flex flex-col items-center justify-center gap-2 transition-all text-center group min-h-[105px]">
                <div class="w-9 h-9 rounded-lg bg-slate-800 group-hover:bg-indigo-600/30 flex items-center justify-center font-bold text-slate-300 group-hover:text-indigo-300 text-base transition-colors">
                    +
                </div>
                <div class="text-[11px] font-bold">Add Platform</div>
                <span class="text-[10px] text-indigo-400 font-semibold">+ Custom</span>
            </button>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- ACTIVE CONNECTED CHANNELS GRID                 -->
    <!-- ============================================== -->
    <div>
        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Connected Channels & Integrations</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
            @forelse($channels as $ch)
                @php
                    $platformDef = $platforms->where('slug', $ch->platform)->first();
                    $badgeColor = $platformDef->color ?? '#6366f1';
                    $badgeCode = $platformDef->short_code ?? strtoupper(substr($ch->platform, 0, 3));
                @endphp
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 space-y-4 shadow-xl transition-all hover:border-slate-700 flex flex-col justify-between">
                    <div class="space-y-4">
                        <!-- Card Top -->
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center font-extrabold text-sm text-white shadow-lg" style="background-color: {{ $badgeColor }};">
                                    {{ $badgeCode }}
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white">{{ $ch->name }}</h3>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[11px] text-slate-400 capitalize font-medium">{{ $platformDef->name ?? $ch->platform }}</span>
                                        @if($ch->platform_account_id)
                                            <span class="text-[11px] text-slate-500">• {{ $ch->platform_account_id }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="flex items-center gap-2">
                                <form action="{{ route('tenant.channels.toggle_status', $ch->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="Click to toggle status" class="px-2.5 py-1 text-[10px] font-bold rounded-full transition-all {{ $ch->is_active ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30 hover:bg-rose-500/30' }}">
                                        {{ $ch->is_active ? '● Active' : '○ Paused' }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Details Box -->
                        <div class="p-3.5 bg-slate-950/80 rounded-xl border border-slate-800/80 space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Webhook URL:</span>
                                <div class="flex items-center gap-1.5">
                                    <code class="text-indigo-300 font-mono text-[10px] bg-slate-900 px-2 py-0.5 rounded border border-slate-800 select-all">/api/webhooks/{{ in_array($ch->platform, ['facebook', 'telegram', 'whatsapp']) ? ($ch->platform === 'facebook' ? 'meta' : $ch->platform) : 'custom' }}/{{ $ch->id }}</code>
                                    <button type="button" onclick="copyText('{{ url('/api/webhooks/' . (in_array($ch->platform, ['facebook', 'telegram', 'whatsapp']) ? ($ch->platform === 'facebook' ? 'meta' : $ch->platform) : 'custom') . '/' . $ch->id) }}')" class="text-slate-400 hover:text-white" title="Copy full URL">📋</button>
                                </div>
                            </div>
                            @if($ch->verify_token)
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Verify Token:</span>
                                    <div class="flex items-center gap-1.5">
                                        <code class="text-slate-300 font-mono text-[10px] bg-slate-900 px-2 py-0.5 rounded border border-slate-800">{{ $ch->verify_token }}</code>
                                        <button type="button" onclick="copyText('{{ $ch->verify_token }}')" class="text-slate-400 hover:text-white" title="Copy token">📋</button>
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-1 border-t border-slate-900">
                                <span class="text-slate-400">AI Auto-Reply Assistant:</span>
                                <form action="{{ route('tenant.channels.toggle_ai', $ch->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-[11px] font-bold px-2 py-0.5 rounded {{ $ch->ai_enabled ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-slate-800 text-slate-500' }}">
                                        {{ $ch->ai_enabled ? '🤖 AI Active' : 'Off' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <!-- Live Connection Test Button -->
                            <button type="button" onclick="testChannelConnection({{ $ch->id }}, this)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition-all">
                                <span>⚡ Test Ping</span>
                            </button>

                            @if(in_array($ch->platform, ['telegram', 'facebook']))
                                <button type="button" onclick="syncTelegramChannel({{ $ch->id }}, this)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg {{ $ch->platform === 'facebook' ? 'bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 border border-blue-500/40' : 'bg-sky-500/20 hover:bg-sky-500/30 text-sky-300 border border-sky-500/40' }} text-xs font-semibold transition-all" title="Pull latest messages from {{ ucfirst($ch->platform) }}">
                                    <span>🔄 Sync</span>
                                </button>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if($ch->platform === 'webchat')
                                <button type="button" onclick="showEmbedModal({{ $ch->id }})" class="px-2.5 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 font-semibold text-xs transition-all">
                                    💻 Embed Code
                                </button>
                            @endif

                            <!-- Safe Pause / Turn Off / Turn On Button -->
                            @if($ch->is_active)
                                <button type="button" onclick="toggleChannelActive({{ $ch->id }}, '{{ addslashes($ch->name) }}', false)" class="px-2.5 py-1.5 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-semibold flex items-center gap-1 transition-all" title="Temporarily Pause (Chat history and bot token will be safely preserved)">
                                    <span>⏸️ Turn Off</span>
                                </button>
                            @else
                                <button type="button" onclick="toggleChannelActive({{ $ch->id }}, '{{ addslashes($ch->name) }}', true)" class="px-2.5 py-1.5 rounded-lg bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-400 border border-emerald-500/30 text-xs font-semibold flex items-center gap-1 transition-all" title="Turn Channel Back On">
                                    <span>▶️ Turn On</span>
                                </button>
                            @endif

                            <!-- Unlink Credentials (Soft Delete - preserves all customer chat history) -->
                            <button type="button" onclick="confirmDeleteChannel({{ $ch->id }}, '{{ addslashes($ch->name) }}')" class="p-1.5 rounded-lg hover:bg-rose-500/20 text-slate-500 hover:text-rose-400 transition-colors" title="Unlink Bot Credentials (Chat history will be preserved)">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 p-12 text-center bg-slate-900/40 rounded-2xl border border-slate-800 space-y-3">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center text-xl">
                        🔌
                    </div>
                    <h3 class="text-sm font-bold text-white">No Channels Connected Yet</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Click any platform above to connect your account and start receiving customer messages.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 1: Add New Platform to List (DYNAMIC NO-CODE BUILDER) -->
<!-- ======================================================== -->
<div id="modal-add-platform" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 max-h-[90vh] flex flex-col">
        <!-- Top Header -->
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-white text-base">
                    ✨
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Add New Platform to Available List</h3>
                    <p class="text-[11px] text-slate-400">Add LinkedIn, Discord, Slack or any custom channel</p>
                </div>
            </div>
            <button onclick="closeNewPlatformModal()" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <form action="{{ route('tenant.platforms.store') }}" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
            @csrf

            <!-- 1-Click Popular Presets -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">1-Click Popular Presets:</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <button type="button" onclick="applyPreset('linkedin')" class="p-2 rounded-xl bg-slate-950 border border-slate-800 hover:border-blue-500 hover:bg-slate-800/60 text-xs flex flex-col items-center gap-1 transition-all">
                        <span class="w-5 h-5 rounded bg-[#0A66C2] flex items-center justify-center font-bold text-white text-[9px]">IN</span>
                        <span class="text-[10px] font-semibold text-slate-200">LinkedIn</span>
                    </button>
                    <button type="button" onclick="applyPreset('discord')" class="p-2 rounded-xl bg-slate-950 border border-slate-800 hover:border-indigo-500 hover:bg-slate-800/60 text-xs flex flex-col items-center gap-1 transition-all">
                        <span class="w-5 h-5 rounded bg-[#5865F2] flex items-center justify-center font-bold text-white text-[9px]">DC</span>
                        <span class="text-[10px] font-semibold text-slate-200">Discord</span>
                    </button>
                    <button type="button" onclick="applyPreset('slack')" class="p-2 rounded-xl bg-slate-950 border border-slate-800 hover:border-purple-500 hover:bg-slate-800/60 text-xs flex flex-col items-center gap-1 transition-all">
                        <span class="w-5 h-5 rounded bg-[#4A154B] flex items-center justify-center font-bold text-white text-[9px]">SL</span>
                        <span class="text-[10px] font-semibold text-slate-200">Slack</span>
                    </button>
                    <button type="button" onclick="applyPreset('twitter')" class="p-2 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-400 hover:bg-slate-800/60 text-xs flex flex-col items-center gap-1 transition-all">
                        <span class="w-5 h-5 rounded bg-black flex items-center justify-center font-bold text-white text-[9px]">X</span>
                        <span class="text-[10px] font-semibold text-slate-200">X (Twitter)</span>
                    </button>
                    <button type="button" onclick="applyPreset('viber')" class="p-2 rounded-xl bg-slate-950 border border-slate-800 hover:border-purple-500 hover:bg-slate-800/60 text-xs flex flex-col items-center gap-1 transition-all">
                        <span class="w-5 h-5 rounded bg-[#7360F2] flex items-center justify-center font-bold text-white text-[9px]">VB</span>
                        <span class="text-[10px] font-semibold text-slate-200">Viber</span>
                    </button>
                    <button type="button" onclick="applyPreset('twilio')" class="p-2 rounded-xl bg-slate-950 border border-slate-800 hover:border-rose-500 hover:bg-slate-800/60 text-xs flex flex-col items-center gap-1 transition-all">
                        <span class="w-5 h-5 rounded bg-[#F22F46] flex items-center justify-center font-bold text-white text-[9px]">SMS</span>
                        <span class="text-[10px] font-semibold text-slate-200">Twilio SMS</span>
                    </button>
                    <button type="button" onclick="applyPreset('custom')" class="p-2 rounded-xl bg-slate-950 border border-slate-800 hover:border-emerald-500 hover:bg-slate-800/60 text-xs flex flex-col items-center gap-1 transition-all col-span-2">
                        <span class="w-5 h-5 rounded bg-emerald-600 flex items-center justify-center font-bold text-white text-[9px]">API</span>
                        <span class="text-[10px] font-semibold text-slate-200">Custom Webhook API</span>
                    </button>
                </div>
            </div>

            <!-- Platform Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Platform Name <span class="text-rose-400">*</span></label>
                    <input type="text" name="name" id="new-platform-name" required placeholder="e.g. LinkedIn" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Short Code (2-4 chars)</label>
                    <input type="text" name="short_code" id="new-platform-code" maxlength="6" placeholder="e.g. IN" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white uppercase focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Platform Slug / Key</label>
                    <input type="text" name="slug" id="new-platform-slug" placeholder="e.g. linkedin" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Brand Theme Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="color" id="new-platform-color" value="#0A66C2" class="w-10 h-9 bg-slate-950 border border-slate-800 rounded-lg p-0.5 cursor-pointer">
                        <input type="text" id="new-platform-color-text" value="#0A66C2" class="w-24 bg-slate-950 border border-slate-800 rounded-xl p-2 text-xs text-slate-300 font-mono">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Outbound API URL (Optional)</label>
                <input type="url" name="outbound_api_url" id="new-platform-url" placeholder="https://api.linkedin.com/v2/messages" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                <span class="text-[10px] text-slate-500 mt-1 block">Endpoint to deliver outbound replies to from your inbox.</span>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                <button type="button" onclick="closeNewPlatformModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 font-bold text-xs rounded-xl text-white shadow-lg shadow-indigo-600/30 transition-all">
                    ✓ Add to Available Platforms
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 2: Connect Social Media Channel In-App             -->
<!-- ======================================================== -->
<div id="modal-connect" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-xl w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 max-h-[90vh] flex flex-col">
        <!-- Modal Top Bar -->
        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div id="modal-platform-icon" class="w-10 h-10 rounded-xl flex items-center justify-center font-extrabold text-white text-sm shadow-lg">
                    TG
                </div>
                <div>
                    <h3 id="modal-title" class="text-sm font-bold text-white">Connect Social Media Channel</h3>
                    <p class="text-[11px] text-slate-400">Direct in-app verification & automated setup</p>
                </div>
            </div>
            <button onclick="closeConnectModal()" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <!-- Connection Form -->
        <form id="form-connect-channel" onsubmit="submitConnectForm(event)" class="p-6 space-y-4 overflow-y-auto flex-1">
            @csrf
            <input type="hidden" name="platform" id="input-platform" value="telegram">

            <!-- Modal Dynamic Alert -->
            <div id="modal-alert" class="hidden p-3.5 rounded-xl text-xs flex items-start gap-2"></div>

            <!-- Platform Fields: Telegram -->
            <div id="fields-telegram" class="platform-field-group space-y-3 hidden">
                <div class="p-3 bg-sky-950/20 border border-sky-800/30 rounded-xl text-[11px] text-sky-300 space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <span>ℹ️</span> <span>How to connect your Telegram Bot:</span>
                    </div>
                    <ol class="list-decimal pl-4 space-y-0.5 text-slate-400">
                        <li>Open Telegram and search for <strong class="text-sky-300">@BotFather</strong>.</li>
                        <li>Type <code class="text-white bg-slate-900 px-1 rounded">/newbot</code>, give it a name and username.</li>
                        <li>Copy the HTTP API token BotFather gives you and paste it below.</li>
                    </ol>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Telegram Bot API Token <span class="text-rose-400">*</span></label>
                    <input type="password" name="bot_token" id="input-tg-token" placeholder="e.g. 7123456789:AAFlkj34jkl234_your_token_here" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Bot Display Label (Optional)</label>
                    <input type="text" name="name" id="input-tg-name" placeholder="e.g. Official Customer Support Bot" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-sky-500">
                </div>
            </div>

            <!-- Platform Fields: Facebook -->
            <div id="fields-facebook" class="platform-field-group space-y-3 hidden">
                <div class="p-3 bg-blue-950/20 border border-blue-800/30 rounded-xl text-[11px] text-blue-300 space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <span>ℹ️</span> <span>Facebook Page & Messenger Connection:</span>
                    </div>
                    <p class="text-slate-400">Provide your Facebook Page Access Token. Our backend will automatically query Meta Graph API to verify the page and subscribe to incoming messages.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Page Access Token <span class="text-rose-400">*</span></label>
                    <div class="relative">
                        <input type="password" name="access_token" id="input-fb-token" placeholder="EAAxxxxxx... (Page Access Token from Meta Developer App)" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 pr-9 text-xs text-white focus:outline-none focus:border-blue-500 font-mono">
                        <button type="button" onclick="togglePasswordVisibility('input-fb-token', this)" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-white text-xs" title="Show/Hide Token">👁️</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Facebook Page ID (Optional)</label>
                        <input type="text" name="page_id" id="input-fb-pageid" placeholder="Leave blank to auto-detect" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Page Display Name</label>
                        <input type="text" name="name" id="input-fb-name" placeholder="e.g. My Business Shop" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Platform Fields: WhatsApp -->
            <div id="fields-whatsapp" class="platform-field-group space-y-3 hidden">
                <div class="p-3 bg-emerald-950/20 border border-emerald-800/30 rounded-xl text-[11px] text-emerald-300 space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <span>ℹ️</span> <span>WhatsApp Cloud API Connection:</span>
                    </div>
                    <p class="text-slate-400">Enter your Phone Number ID and Permanent System User Access Token from your Meta Developer WhatsApp Account.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Phone Number ID <span class="text-rose-400">*</span></label>
                    <input type="text" name="phone_number_id" id="input-wa-phoneid" placeholder="e.g. 10492837461928" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Permanent Access Token <span class="text-rose-400">*</span></label>
                    <input type="password" name="access_token" id="input-wa-token" placeholder="EAAxxxxxx..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Display Phone Number (Optional)</label>
                    <input type="text" name="phone_number" id="input-wa-phone" placeholder="e.g. +1 (555) 234-5678" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <!-- Platform Fields: Instagram -->
            <div id="fields-instagram" class="platform-field-group space-y-3 hidden">
                <div class="p-3 bg-pink-950/20 border border-pink-800/30 rounded-xl text-[11px] text-pink-300 space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <span>ℹ️</span> <span>Instagram Direct Business Connection:</span>
                    </div>
                    <p class="text-slate-400">Connect your Instagram Professional/Business account via Page Token or Instagram Graph Token.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Instagram Access Token <span class="text-rose-400">*</span></label>
                    <input type="password" name="access_token" id="input-ig-token" placeholder="EAAxxxxxx..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-pink-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Instagram Account Username / ID (Optional)</label>
                    <input type="text" name="username" id="input-ig-user" placeholder="e.g. @myshop.official" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-pink-500">
                </div>
            </div>

            <!-- Platform Fields: Web Widget -->
            <div id="fields-webchat" class="platform-field-group space-y-3 hidden">
                <div class="p-3 bg-indigo-950/20 border border-indigo-800/30 rounded-xl text-[11px] text-indigo-300 space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <span>💬</span> <span>Website Live Chat Widget:</span>
                    </div>
                    <p class="text-slate-400">Embed this chat widget on your website to receive live visitor inquiries directly into your unified inbox.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Widget Title</label>
                        <input type="text" name="name" value="{{ $tenant->name }} Live Support" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Brand Theme Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="primary_color" value="#6366f1" class="w-10 h-9 bg-slate-950 border border-slate-800 rounded-lg p-0.5 cursor-pointer">
                            <span class="text-xs text-slate-400">#6366f1</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Welcome Greeting Message</label>
                    <textarea name="welcome_message" rows="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">Hello! How can our team assist you today?</textarea>
                </div>
            </div>

            <!-- Platform Fields: Custom Dynamic Platforms (LinkedIn, Discord, Slack, etc.) -->
            <div id="fields-custom" class="platform-field-group space-y-3 hidden">
                <div class="p-3 bg-purple-950/20 border border-purple-800/30 rounded-xl text-[11px] text-purple-300 space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <span>✨</span> <span id="custom-platform-heading">Custom Channel Configuration:</span>
                    </div>
                    <p class="text-slate-400">Connect this channel by providing its API Token or webhook settings. Any incoming payload will be mapped and delivered directly into your unified inbox.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Channel Display Name <span class="text-rose-400">*</span></label>
                    <input type="text" name="name" id="input-custom-name" placeholder="e.g. Official LinkedIn Company Page" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Account ID / Channel ID</label>
                        <input type="text" name="platform_account_id" id="input-custom-accountid" placeholder="e.g. org_10928374" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Access Token / API Key</label>
                        <input type="password" name="access_token" id="input-custom-token" placeholder="Bearer Token or API Key" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Outbound Reply API URL (Optional)</label>
                    <input type="url" name="outbound_api_url" id="input-custom-outbound" placeholder="https://api.linkedin.com/v2/messages" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <!-- Quick Sandbox / Simulator Notice -->
            <div class="p-2.5 bg-slate-950 rounded-xl border border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                <div class="flex items-center gap-1.5">
                    <span>💡</span>
                    <span>Test mode: Use token <code class="text-indigo-400 bg-slate-900 px-1.5 py-0.5 rounded border border-slate-800 cursor-pointer" onclick="useDemoToken()">demo_token</code> to test instantly.</span>
                </div>
            </div>

            <!-- Form Buttons -->
            <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" onclick="closeConnectModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-connect" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all">
                    <span id="btn-submit-spinner" class="hidden animate-spin">⏳</span>
                    <span id="btn-submit-text">⚡ Verify & Connect Channel</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 3: Web Widget Embed Snippet                        -->
<!-- ======================================================== -->
<div id="modal-embed" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span>💻</span>
                <span>Website Chat Widget Embed Snippet</span>
            </h3>
            <button onclick="document.getElementById('modal-embed').classList.add('hidden')" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <p class="text-xs text-slate-400">Paste this script tag right before the closing <code class="text-indigo-300 bg-slate-950 px-1 rounded">&lt;/body&gt;</code> tag on any website (WordPress, Laravel, Shopify, React, HTML).</p>

        <div class="relative">
            <pre class="bg-slate-950 border border-slate-800 rounded-xl p-3 text-[11px] text-indigo-300 font-mono overflow-x-auto select-all"><code id="embed-code-snippet">&lt;!-- SocialMate Live Chat --&gt;
&lt;script src="{{ url('/js/omni-widget.js') }}" data-tenant="{{ $tenant->slug }}" data-channel-id="1" async&gt;&lt;/script&gt;</code></pre>
            <button onclick="copySnippet()" class="absolute top-2 right-2 px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 border border-slate-700">
                Copy
            </button>
        </div>

        <div class="flex justify-end pt-2">
            <button onclick="document.getElementById('modal-embed').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-xs font-semibold rounded-xl text-slate-300">Close</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let activePlatform = 'telegram';

    function openNewPlatformModal() {
        document.getElementById('modal-add-platform').classList.remove('hidden');
    }

    function closeNewPlatformModal() {
        document.getElementById('modal-add-platform').classList.add('hidden');
    }

    function applyPreset(type) {
        const presets = {
            linkedin: { name: 'LinkedIn Messaging', slug: 'linkedin', code: 'IN', color: '#0A66C2', url: 'https://api.linkedin.com/v2/messages' },
            discord: { name: 'Discord Bot', slug: 'discord', code: 'DC', color: '#5865F2', url: 'https://discord.com/api/v10/channels' },
            slack: { name: 'Slack Bot', slug: 'slack', code: 'SL', color: '#4A154B', url: 'https://slack.com/api/chat.postMessage' },
            twitter: { name: 'X (Twitter) DM', slug: 'twitter', code: 'X', color: '#000000', url: 'https://api.twitter.com/2/dm_conversations/with_participant_id/messages' },
            viber: { name: 'Viber Business', slug: 'viber', code: 'VB', color: '#7360F2', url: 'https://chatapi.viber.com/pa/send_message' },
            twilio: { name: 'Twilio SMS', slug: 'twilio_sms', code: 'SMS', color: '#F22F46', url: 'https://api.twilio.com/2010-04-01/Accounts' },
            custom: { name: 'Custom Webhook API', slug: 'custom_api', code: 'API', color: '#10B981', url: '' }
        };

        const p = presets[type];
        if (p) {
            document.getElementById('new-platform-name').value = p.name;
            document.getElementById('new-platform-slug').value = p.slug;
            document.getElementById('new-platform-code').value = p.code;
            document.getElementById('new-platform-color').value = p.color;
            document.getElementById('new-platform-color-text').value = p.color;
            document.getElementById('new-platform-url').value = p.url;
        }
    }

    document.getElementById('new-platform-color')?.addEventListener('input', function(e) {
        document.getElementById('new-platform-color-text').value = e.target.value;
    });

    function openConnectModal(platformSlug, platformName = '', shortCode = '', color = '#6366f1', isCustom = false) {
        activePlatform = platformSlug;
        document.getElementById('input-platform').value = platformSlug;

        const iconEl = document.getElementById('modal-platform-icon');
        const titleEl = document.getElementById('modal-title');
        const submitText = document.getElementById('btn-submit-text');

        iconEl.style.backgroundColor = color;
        iconEl.innerText = shortCode || platformSlug.substring(0, 3).toUpperCase();
        titleEl.innerText = `Connect ${platformName || platformSlug}`;

        // Disable all inputs across all field groups to avoid field name collisions (e.g. access_token, name)
        document.querySelectorAll('.platform-field-group').forEach(el => {
            el.classList.add('hidden');
            el.querySelectorAll('input, select, textarea').forEach(inp => inp.disabled = true);
        });

        let target = null;
        if (['facebook', 'whatsapp', 'telegram', 'instagram', 'webchat'].includes(platformSlug)) {
            target = document.getElementById(`fields-${platformSlug}`);
            submitText.innerText = `⚡ Verify & Connect ${platformName}`;
        } else {
            // Dynamic custom platform
            target = document.getElementById('fields-custom');
            document.getElementById('custom-platform-heading').innerText = `${platformName} Configuration:`;
            document.getElementById('input-custom-name').value = `${platformName} Channel`;
            submitText.innerText = `⚡ Connect ${platformName}`;
        }

        if (target) {
            target.classList.remove('hidden');
            target.querySelectorAll('input, select, textarea').forEach(inp => inp.disabled = false);
        }

        document.getElementById('modal-alert').classList.add('hidden');
        document.getElementById('modal-connect').classList.remove('hidden');
    }

    function togglePasswordVisibility(inputId, btn) {
        const inp = document.getElementById(inputId);
        if (!inp) return;
        if (inp.type === 'password') {
            inp.type = 'text';
            btn.innerText = '🙈';
        } else {
            inp.type = 'password';
            btn.innerText = '👁️';
        }
    }

    function closeConnectModal() {
        document.getElementById('modal-connect').classList.add('hidden');
    }

    function useDemoToken() {
        if (activePlatform === 'telegram') {
            document.getElementById('input-tg-token').value = 'demo_telegram_bot_token_123456';
            document.getElementById('input-tg-name').value = 'Demo Support Bot';
        } else if (activePlatform === 'facebook') {
            document.getElementById('input-fb-token').value = 'demo_facebook_page_token_abc';
            document.getElementById('input-fb-name').value = 'Demo Fashion Store Page';
            document.getElementById('input-fb-pageid').value = '1092837465';
        } else if (activePlatform === 'whatsapp') {
            document.getElementById('input-wa-token').value = 'demo_whatsapp_token_xyz';
            document.getElementById('input-wa-phoneid').value = '109283746519283';
            document.getElementById('input-wa-phone').value = '+880 1712-345678';
        } else if (activePlatform === 'instagram') {
            document.getElementById('input-ig-token').value = 'demo_instagram_token_xyz';
            document.getElementById('input-ig-user').value = '@mybrand.official';
        } else {
            document.getElementById('input-custom-token').value = 'demo_token_secret_123';
            document.getElementById('input-custom-accountid').value = `${activePlatform}_account_101`;
        }
    }

    async function submitConnectForm(e) {
        e.preventDefault();

        const form = document.getElementById('form-connect-channel');
        const btn = document.getElementById('btn-submit-connect');
        const spinner = document.getElementById('btn-submit-spinner');
        const text = document.getElementById('btn-submit-text');
        const alertEl = document.getElementById('modal-alert');

        const originalText = text.innerText;
        btn.disabled = true;
        spinner.classList.remove('hidden');
        text.innerText = 'Verifying with Platform API...';
        alertEl.classList.add('hidden');

        // Build clean FormData only containing active platform fields
        const formData = new FormData();
        formData.append('_token', form.querySelector('input[name="_token"]').value);
        formData.append('platform', activePlatform);

        const activeGroup = ['facebook', 'whatsapp', 'telegram', 'instagram', 'webchat'].includes(activePlatform)
            ? document.getElementById(`fields-${activePlatform}`)
            : document.getElementById('fields-custom');

        if (activeGroup) {
            activeGroup.querySelectorAll('input, select, textarea').forEach(input => {
                if (input.name && !input.disabled) {
                    formData.append(input.name, input.value.trim());
                }
            });
        }

        try {
            const response = await fetch("{{ route('tenant.channels.connect') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alertEl.className = 'p-3.5 rounded-xl text-xs flex items-start gap-2 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400';
                alertEl.innerHTML = `<span>✅</span> <div><strong>Connected!</strong> ${data.message} Reloading...</div>`;
                alertEl.classList.remove('hidden');

                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            } else {
                alertEl.className = 'p-3.5 rounded-xl text-xs flex items-start gap-2 bg-rose-500/10 border border-rose-500/20 text-rose-400';
                alertEl.innerHTML = `<span>❌</span> <div><strong>Connection Failed:</strong> ${data.message}</div>`;
                alertEl.classList.remove('hidden');
            }
        } catch (err) {
            alertEl.className = 'p-3.5 rounded-xl text-xs flex items-start gap-2 bg-rose-500/10 border border-rose-500/20 text-rose-400';
            alertEl.innerHTML = `<span>❌</span> <div><strong>Error:</strong> Network timeout or communication issue. Please try again.</div>`;
            alertEl.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            spinner.classList.add('hidden');
            text.innerText = originalText;
        }
    }

    async function testChannelConnection(channelId, btn) {
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="animate-spin">⏳</span> Ping...`;

        try {
            const response = await fetch(`/tenant/channels/${channelId}/test`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (data.success) {
                Toast.fire({ icon: 'success', title: data.message, iconColor: '#10b981' });
            } else {
                Toast.fire({ icon: 'warning', title: data.message, iconColor: '#f59e0b' });
            }
        } catch (err) {
            Toast.fire({ icon: 'error', title: 'Error checking channel health.', iconColor: '#f43f5e' });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    async function syncTelegramChannel(channelId, btn) {
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="animate-spin">⏳</span> Syncing...`;

        try {
            const response = await fetch(`/tenant/channels/${channelId}/sync`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            Toast.fire({ icon: 'info', title: data.message, iconColor: '#38bdf8' });

            if (data.count > 0) {
                setTimeout(() => window.location.href = "{{ route('tenant.inbox') }}", 1500);
            }
        } catch (err) {
            Toast.fire({ icon: 'error', title: 'Error syncing with Telegram.', iconColor: '#f43f5e' });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    function toggleChannelActive(channelId, platformName, turnOn) {
        if (turnOn) {
            submitToggleStatus(channelId);
            return;
        }

        Swal.fire({
            title: `Disconnect ${platformName}?`,
            html: '<div class="space-y-2 text-left"><p class="text-xs text-slate-300">Incoming messages from this channel will temporarily stop syncing.</p><div class="p-2.5 rounded-xl bg-emerald-950/40 border border-emerald-500/30 text-[11px] text-emerald-300 flex items-center gap-2"><span>🛡️</span><span>Your bot token, credentials, and <b>all previous chat history</b> will remain 100% safe.</span></div></div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#334155',
            confirmButtonText: 'Yes, Turn Off',
            cancelButtonText: 'Cancel',
            background: '#0b1120',
            color: '#f8fafc',
            iconColor: '#f43f5e',
            customClass: {
                popup: 'border border-slate-800 rounded-3xl p-6 shadow-2xl backdrop-blur-xl',
                title: 'text-base font-bold text-white',
                htmlContainer: 'text-xs text-slate-400',
                confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg shadow-rose-600/30',
                cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-xs text-slate-300'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                submitToggleStatus(channelId);
            }
        });
    }

    function submitToggleStatus(channelId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/tenant/channels/${channelId}/toggle-status`;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';

        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'PATCH';

        form.appendChild(csrf);
        form.appendChild(method);
        document.body.appendChild(form);
        form.submit();
    }

    function confirmDeleteChannel(channelId, platformName) {
        Swal.fire({
            title: `Unlink ${platformName}?`,
            html: '<div class="space-y-2 text-left"><p class="text-xs text-slate-300">Are you sure you want to remove credentials for <b>' + platformName + '</b>?</p><div class="p-2.5 rounded-xl bg-sky-950/40 border border-sky-500/30 text-[11px] text-sky-300 flex items-center gap-2"><span>💬</span><span>All past customer chat history and conversations will remain preserved.</span></div></div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#334155',
            confirmButtonText: 'Yes, Unlink Channel',
            cancelButtonText: 'Cancel',
            background: '#0b1120',
            color: '#f8fafc',
            iconColor: '#f43f5e',
            customClass: {
                popup: 'border border-slate-800 rounded-3xl p-6 shadow-2xl backdrop-blur-xl',
                title: 'text-base font-bold text-white',
                htmlContainer: 'text-xs text-slate-400',
                confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg shadow-rose-600/30',
                cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-xs text-slate-300'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/tenant/channels/${channelId}`;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';

                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';

                form.appendChild(csrf);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function confirmDeletePlatform(platformId, platformName) {
        Swal.fire({
            title: `Remove ${platformName}?`,
            text: 'This custom platform will be removed from your available list.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#334155',
            confirmButtonText: 'Yes, Remove',
            cancelButtonText: 'Cancel',
            background: '#0b1120',
            color: '#f8fafc',
            iconColor: '#f43f5e',
            customClass: {
                popup: 'border border-slate-800 rounded-3xl p-6 shadow-2xl backdrop-blur-xl',
                title: 'text-base font-bold text-white',
                htmlContainer: 'text-xs text-slate-400',
                confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg shadow-rose-600/30',
                cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-xs text-slate-300'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/tenant/platforms/${platformId}`;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';

                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';

                form.appendChild(csrf);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function showEmbedModal(channelId) {
        const snippet = `<!-- SocialMate Live Chat -->\n<script src="${window.location.origin}/js/omni-widget.js" data-tenant="{{ $tenant->slug }}" data-channel-id="${channelId}" async><\/script>`;
        document.getElementById('embed-code-snippet').innerText = snippet;
        document.getElementById('modal-embed').classList.remove('hidden');
    }

    function copySnippet() {
        const code = document.getElementById('embed-code-snippet').innerText;
        navigator.clipboard.writeText(code);
        Toast.fire({ icon: 'success', title: 'Embed script copied to clipboard!', iconColor: '#10b981' });
    }

    function copyText(str) {
        navigator.clipboard.writeText(str);
        Toast.fire({ icon: 'success', title: 'Copied to clipboard!', iconColor: '#10b981' });
    }
</script>
@endsection
