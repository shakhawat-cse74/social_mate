@extends('tenant.layout')

@section('title', 'Omnichannel Live Inbox')

@section('content')
<div class="flex-1 flex overflow-hidden">
    <!-- COLUMN 1: Threads Sidebar -->
    <aside id="inbox-col-threads" class="w-full lg:w-80 border-r border-slate-800 bg-slate-900/40 flex flex-col shrink-0">
        <!-- Search & Filter Bar -->
        <div class="p-3 border-b border-slate-800 space-y-2.5">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input type="text" id="conv-search" oninput="filterConversations()" placeholder="Search contacts..." class="w-full bg-slate-950/70 border border-slate-800 rounded-lg px-3 py-1.5 pl-8 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                    <svg class="w-3.5 h-3.5 text-slate-500 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                </div>
                <button type="button" onclick="window.location.reload()" class="px-2 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-all text-xs font-semibold flex items-center gap-1 shrink-0" title="Refresh and sync new messages">
                    <span>🔄</span>
                </button>
            </div>

            <!-- Platform Filters -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-[11px]" id="channel-filter-chips">
                <button onclick="setPlatformFilter('all')" class="platform-chip active px-2.5 py-1 rounded-md bg-indigo-600 text-white font-medium shrink-0">All ({{ count($conversations) }})</button>
                <button onclick="setPlatformFilter('facebook')" class="platform-chip px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 hover:bg-slate-700 shrink-0 flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span> FB</button>
                <button onclick="setPlatformFilter('whatsapp')" class="platform-chip px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 hover:bg-slate-700 shrink-0 flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> WA</button>
                <button onclick="setPlatformFilter('telegram')" class="platform-chip px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 hover:bg-slate-700 shrink-0 flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-sky-500"></span> TG</button>
                <button onclick="setPlatformFilter('webchat')" class="platform-chip px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 hover:bg-slate-700 shrink-0 flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-purple-500"></span> Web</button>
            </div>
        </div>

        <!-- Conversation Threads List -->
        <div class="flex-1 overflow-y-auto divide-y divide-slate-800/40" id="conversation-list">
            @forelse($conversations as $conv)
                @php
                    $lastTime = $conv->contact?->last_active_at ?? $conv->last_message_at;
                    $isCustomerOnline = $lastTime && $lastTime->gt(now()->subMinutes(10));
                    $isUnread = ($conv->unread_count ?? 0) > 0 && !$loop->first;
                @endphp
                <div onclick="selectConversation({{ $conv->id }})" id="conv-item-{{ $conv->id }}" data-platform="{{ $conv->channel->platform ?? 'webchat' }}" class="conv-item p-3 hover:bg-slate-800/40 cursor-pointer transition-all relative flex items-start gap-3 {{ $loop->first ? 'bg-indigo-950/30 border-l-2 border-indigo-500' : ($isUnread ? 'bg-indigo-950/20 border-l-2 border-indigo-400' : '') }}">
                    <div class="relative shrink-0">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-slate-700 to-slate-800 flex items-center justify-center font-bold text-slate-200 text-sm">
                            {{ strtoupper(substr($conv->contact->name ?? 'U', 0, 1)) }}
                        </div>

                        <!-- Real-time Online/Active status dot on avatar -->
                        @if($isCustomerOnline)
                            <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-slate-900 rounded-full animate-pulse shadow-sm" title="Customer Active Now (Online)"></span>
                        @else
                            <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-slate-600 border-2 border-slate-900 rounded-full opacity-60" title="Customer Offline"></span>
                        @endif

                        <!-- Platform Icon badge at bottom right -->
                        <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full flex items-center justify-center text-[9px] text-white
                            @if(($conv->channel->platform ?? '') === 'facebook') bg-blue-600
                            @elseif(($conv->channel->platform ?? '') === 'whatsapp') bg-emerald-600
                            @elseif(($conv->channel->platform ?? '') === 'telegram') bg-sky-500
                            @elseif(($conv->channel->platform ?? '') === 'instagram') bg-pink-600
                            @else bg-purple-600 @endif">
                            @if(($conv->channel->platform ?? '') === 'facebook') f
                            @elseif(($conv->channel->platform ?? '') === 'whatsapp') w
                            @elseif(($conv->channel->platform ?? '') === 'telegram') t
                            @elseif(($conv->channel->platform ?? '') === 'instagram') i
                            @else c @endif
                        </span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 truncate">
                                <h4 class="conv-contact-name text-xs {{ $isUnread ? 'font-bold text-white' : 'font-semibold text-slate-200' }} truncate">{{ $conv->contact->name ?? 'Guest User' }}</h4>
                                @if($isCustomerOnline)
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0" title="Online"></span>
                                @endif
                                <span class="conv-unread-dot w-2 h-2 rounded-full bg-indigo-500 shrink-0 {{ $isUnread ? '' : 'hidden' }} animate-pulse" title="Unread message"></span>
                            </div>
                            <span class="conv-time-label text-[10px] {{ $isUnread ? 'text-indigo-400 font-bold' : 'text-slate-500' }}">{{ $conv->last_message_at ? $conv->last_message_at->diffForHumans(null, true) : 'New' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-1.5 mt-0.5">
                            <p class="conv-preview-text text-[11px] truncate flex-1 {{ $isUnread ? 'text-slate-100 font-semibold' : 'text-slate-400' }}">
                                {{ $conv->latestMessage->content ?? 'No messages yet' }}
                            </p>
                            <span class="conv-unread-badge shrink-0 {{ $isUnread ? '' : 'hidden' }} min-w-[18px] h-[18px] px-1.5 rounded-full bg-indigo-600 text-white text-[10px] font-bold flex items-center justify-center shadow-md shadow-indigo-600/40">
                                {{ $conv->unread_count ?? 1 }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                            <!-- AI Mode Label (Clear & unambiguous) -->
                            <span class="text-[9px] px-1.5 py-0.2 rounded font-medium 
                                @if($conv->ai_mode === 'active') bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                                @elseif($conv->ai_mode === 'human_takeover') bg-amber-500/20 text-amber-300 border border-amber-500/30
                                @else bg-slate-800 text-slate-400 border border-slate-700/50 @endif" title="AI Bot Setting for this customer">
                                @if($conv->ai_mode === 'active') 🤖 Bot: On
                                @elseif($conv->ai_mode === 'human_takeover') 👤 Agent Mode
                                @else ⏸️ Bot: Off @endif
                            </span>

                            <!-- Real Customer Activity Status Badge -->
                            @if($isCustomerOnline)
                                <span class="text-[9px] px-1.5 py-0.2 rounded font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Active Now
                                </span>
                            @else
                                <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-900 text-slate-500 border border-slate-800">
                                    Offline
                                </span>
                            @endif

                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-400">
                                {{ ucfirst($conv->contact->pipeline_stage ?? 'new') }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-slate-500">
                    No active conversations found.
                </div>
            @endforelse
        </div>
    </aside>

    <!-- COLUMN 2: Active Chat Area -->
    <section id="inbox-col-chat" class="hidden lg:flex flex-1 flex-col bg-slate-950/60 overflow-hidden relative">
        <!-- Chat Header -->
        <div class="min-h-16 py-2.5 border-b border-slate-800 px-3 sm:px-6 flex flex-wrap items-center justify-between gap-2.5 bg-slate-900/30">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <!-- Back to list button on mobile -->
                <button type="button" onclick="showMobileThreadsList()" class="lg:hidden p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center shrink-0" title="Back to conversations">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </button>

                <div id="chat-header-avatar" class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center font-bold text-white text-xs shrink-0">
                    {{ strtoupper(substr($conversations->first()?->contact?->name ?? 'T', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                        <h3 id="chat-header-name" class="text-xs sm:text-sm font-bold text-white truncate max-w-[120px] sm:max-w-[200px]">{{ $conversations->first()?->contact?->name ?? 'Select a conversation' }}</h3>
                        <span id="chat-header-platform" class="text-[9px] sm:text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30">
                            {{ ucfirst($conversations->first()?->channel?->platform ?? 'Facebook') }}
                        </span>
                        <span id="chat-header-online-status" class="inline-flex items-center gap-1 text-[9px] sm:text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Active Now
                        </span>
                    </div>
                    <p id="chat-header-sender-id" class="text-[10px] sm:text-[11px] text-slate-400 truncate max-w-[140px] sm:max-w-xs">{{ $conversations->first()?->contact?->platform_sender_id ?? '' }}</p>
                </div>
            </div>

            <!-- Action Controls: AI Switch, Status, CRM button -->
            <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                <div class="flex items-center bg-slate-900 border border-slate-800 rounded-lg p-0.5 text-xs">
                    <button onclick="setAiMode('active')" id="btn-ai-active" class="px-2 sm:px-2.5 py-1 rounded text-[10px] sm:text-[11px] font-medium transition-all bg-indigo-600 text-white" title="AI Bot Active">🤖 <span class="hidden sm:inline">AI Active</span></button>
                    <button onclick="setAiMode('human_takeover')" id="btn-ai-human" class="px-2 sm:px-2.5 py-1 rounded text-[10px] sm:text-[11px] font-medium transition-all text-slate-400 hover:text-slate-200" title="Human Takeover">👤 <span class="hidden sm:inline">Human</span></button>
                    <button onclick="setAiMode('paused')" id="btn-ai-paused" class="px-2 sm:px-2.5 py-1 rounded text-[10px] sm:text-[11px] font-medium transition-all text-slate-400 hover:text-slate-200" title="Mute AI Bot">⏸️ <span class="hidden sm:inline">Mute</span></button>
                </div>
                <button onclick="toggleResolved()" id="btn-resolve-toggle" class="px-2.5 sm:px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-[11px] sm:text-xs font-semibold transition-all flex items-center gap-1">
                    <span id="resolve-icon">✓</span>
                    <span id="resolve-text" class="hidden sm:inline">Resolved</span>
                </button>
                <button onclick="manualSync()" id="btn-manual-sync" class="px-2.5 sm:px-3 py-1.5 rounded-lg bg-sky-500/10 hover:bg-sky-500/20 border border-sky-500/30 text-sky-300 text-[11px] sm:text-xs font-semibold transition-all flex items-center gap-1" title="Sync messages">
                    <span id="sync-icon">🔄</span>
                    <span class="hidden sm:inline">Sync</span>
                </button>
                <!-- Mobile/Tablet CRM info drawer toggle button -->
                <button type="button" onclick="toggleMobileCrmDrawer()" class="xl:hidden px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-semibold flex items-center gap-1 border border-slate-700" title="View Customer CRM Details">
                    <span>👤</span>
                    <span class="hidden md:inline">CRM</span>
                </button>
            </div>
        </div>

        <!-- Messages Stream -->
        <div id="messages-container" class="flex-1 overflow-y-auto p-6 space-y-4">
            <!-- Dynamic Messages will load here via JS -->
        </div>

        <!-- Canned Responses / Quick Replies Recommendation Bar -->
        <div class="px-3 sm:px-6 py-2 bg-slate-900/60 border-t border-slate-800 flex items-center justify-between gap-2 overflow-x-auto" id="quick-replies-bar">
            <div class="flex items-center gap-2 overflow-x-auto min-w-0 flex-1 py-0.5" id="quick-replies-list">
                <span class="text-[11px] font-semibold text-slate-400 shrink-0 flex items-center gap-1">⚡ Quick Replies:</span>
                <!-- Dynamic Chips will render here -->
            </div>
            <button type="button" onclick="openQuickRepliesModal()" class="px-2.5 py-1 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 hover:text-white text-[11px] font-semibold shrink-0 border border-indigo-500/30 transition-all flex items-center gap-1 ml-auto shadow-sm" title="Create, edit, or manage quick replies">
                <span>⚙️ Manage / ➕ Add</span>
            </button>
        </div>

        <!-- Channel Disconnected/Inactive Notice Banner -->
        <div id="channel-inactive-banner" class="hidden px-5 py-2.5 bg-amber-500/10 border-t border-amber-500/20 text-amber-300 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span id="channel-inactive-text">This channel is currently disconnected / paused. Messages cannot be sent or received.</span>
            </div>
            <a href="{{ route('tenant.channels') }}" class="px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-200 text-[11px] font-bold transition-colors">
                Turn On Channel &rarr;
            </a>
        </div>

        <!-- Attachment preview bar (shows chosen image/document/audio) -->
        <div id="attachment-preview-bar" class="hidden px-5 py-2.5 bg-slate-950 border-t border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div id="attachment-thumb" class="w-10 h-10 rounded-xl overflow-hidden bg-slate-900 border border-slate-800 flex items-center justify-center text-base shrink-0">
                    📎
                </div>
                <div class="min-w-0">
                    <p id="attachment-name" class="text-xs font-semibold text-slate-200 truncate max-w-xs">file.jpg</p>
                    <span id="attachment-size" class="text-[10px] text-slate-500">120 KB</span>
                </div>
            </div>
            <button type="button" onclick="clearAttachment()" class="text-slate-400 hover:text-rose-400 text-lg px-2" title="Remove attachment">&times;</button>
        </div>

        <!-- Popover: Emoji Picker (Telegram style with live search & tabs) -->
        <div id="popover-emoji" class="hidden absolute bottom-24 left-2 sm:left-6 z-30 bg-slate-900/95 border border-slate-800 rounded-2xl shadow-2xl p-3 w-[calc(100vw-1rem)] sm:w-96 max-w-sm backdrop-blur-xl animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between pb-2 border-b border-slate-800 mb-2">
                <span class="text-xs font-bold text-white flex items-center gap-1.5">
                    <span>😀</span> Telegram Emojis
                </span>
                <button type="button" onclick="toggleEmojiPicker()" class="text-slate-400 hover:text-white text-base">&times;</button>
            </div>
            <!-- Search bar -->
            <div class="relative mb-2">
                <input type="text" id="emoji-search-input" oninput="filterEmojis(this.value)" placeholder="Search emojis (smile, love, fire, thumbs)..." class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-7 pr-2.5 py-1.5 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                <span class="absolute left-2.5 top-1.5 text-xs text-slate-500">🔍</span>
            </div>
            <!-- Categories tabs -->
            <div class="flex items-center gap-1 overflow-x-auto pb-1.5 mb-1.5 border-b border-slate-800/60 no-scrollbar">
                <button type="button" onclick="switchEmojiTab('smileys')" class="emoji-tab-btn text-xs px-2 py-0.5 rounded shrink-0 bg-indigo-600 text-white font-bold" data-tab="smileys" title="Smileys & Emotion">😊</button>
                <button type="button" onclick="switchEmojiTab('gestures')" class="emoji-tab-btn text-xs px-2 py-0.5 rounded shrink-0 text-slate-400 hover:text-white" data-tab="gestures" title="People & Body">👋</button>
                <button type="button" onclick="switchEmojiTab('animals')" class="emoji-tab-btn text-xs px-2 py-0.5 rounded shrink-0 text-slate-400 hover:text-white" data-tab="animals" title="Animals & Nature">🐶</button>
                <button type="button" onclick="switchEmojiTab('food')" class="emoji-tab-btn text-xs px-2 py-0.5 rounded shrink-0 text-slate-400 hover:text-white" data-tab="food" title="Food & Drink">🍔</button>
                <button type="button" onclick="switchEmojiTab('travel')" class="emoji-tab-btn text-xs px-2 py-0.5 rounded shrink-0 text-slate-400 hover:text-white" data-tab="travel" title="Travel & Places">✈️</button>
                <button type="button" onclick="switchEmojiTab('activities')" class="emoji-tab-btn text-xs px-2 py-0.5 rounded shrink-0 text-slate-400 hover:text-white" data-tab="activities" title="Activities & Objects">⚽</button>
                <button type="button" onclick="switchEmojiTab('symbols')" class="emoji-tab-btn text-xs px-2 py-0.5 rounded shrink-0 text-slate-400 hover:text-white" data-tab="symbols" title="Symbols & Flags">❤️</button>
            </div>
            <div id="emoji-grid" class="grid grid-cols-8 gap-1.5 max-h-52 overflow-y-auto p-1 text-lg">
                <!-- Injected via JS -->
            </div>
        </div>

        <!-- Popover: GIF Stickers (Telegram style with Live Search & Tags) -->
        <div id="popover-gif" class="hidden absolute bottom-24 left-2 sm:left-16 z-30 bg-slate-900/95 border border-slate-800 rounded-2xl shadow-2xl p-3 w-[calc(100vw-1rem)] sm:w-96 max-w-sm backdrop-blur-xl animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between pb-2 border-b border-slate-800 mb-2">
                <span class="text-xs font-bold text-white flex items-center gap-1.5"><span>🎞️</span> Telegram GIFs & Stickers</span>
                <button type="button" onclick="toggleGifPicker()" class="text-slate-400 hover:text-white text-base">&times;</button>
            </div>
            <!-- Search bar -->
            <div class="relative mb-2">
                <input type="text" id="gif-search-input" onkeydown="if(event.key==='Enter') searchGifs(this.value)" placeholder="Search GIFs (e.g. cat, dance, thanks) & press Enter..." class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-7 pr-7 py-1.5 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                <span class="absolute left-2.5 top-1.5 text-xs text-slate-500">🔍</span>
                <button type="button" onclick="searchGifs(document.getElementById('gif-search-input').value)" class="absolute right-2 top-1.5 text-xs text-slate-400 hover:text-indigo-400">↵</button>
            </div>
            <!-- Quick Category Tags -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1.5 mb-2 no-scrollbar text-[11px]">
                <button type="button" onclick="searchGifs('trending')" class="px-2 py-0.5 rounded-full bg-indigo-600/30 text-indigo-300 hover:bg-indigo-600 hover:text-white shrink-0 font-medium">🔥 Trending</button>
                <button type="button" onclick="searchGifs('hello')" class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 hover:bg-indigo-600 hover:text-white shrink-0 font-medium">👋 Hello</button>
                <button type="button" onclick="searchGifs('thank you')" class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 hover:bg-indigo-600 hover:text-white shrink-0 font-medium">🙏 Thanks</button>
                <button type="button" onclick="searchGifs('thumbs up')" class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 hover:bg-indigo-600 hover:text-white shrink-0 font-medium">👍 Great</button>
                <button type="button" onclick="searchGifs('laugh')" class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 hover:bg-indigo-600 hover:text-white shrink-0 font-medium">😂 Laugh</button>
                <button type="button" onclick="searchGifs('celebrate')" class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 hover:bg-indigo-600 hover:text-white shrink-0 font-medium">🎉 Party</button>
                <button type="button" onclick="searchGifs('love')" class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 hover:bg-indigo-600 hover:text-white shrink-0 font-medium">❤️ Love</button>
                <button type="button" onclick="searchGifs('bye')" class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 hover:bg-indigo-600 hover:text-white shrink-0 font-medium">👋 Bye</button>
            </div>
            <div class="grid grid-cols-2 gap-2 max-h-56 overflow-y-auto p-1" id="gif-grid">
                <!-- Injected via JS -->
            </div>
        </div>

        <!-- Reply Composer -->
        <div class="p-2 sm:p-3 bg-slate-900/95 border-t border-slate-800 space-y-2 relative">
            <!-- Normal Text & Action Input Box -->
            <div id="composer-normal-box" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-2 bg-slate-950/80 rounded-2xl border border-slate-800 focus-within:border-indigo-500 transition-colors p-2 sm:p-2.5">
                <textarea id="reply-input" rows="2" placeholder="Type reply to customer... (Press Enter to send, Shift+Enter for new line)" class="w-full bg-transparent text-xs text-slate-100 placeholder-slate-500 focus:outline-none resize-none disabled:opacity-50 disabled:cursor-not-allowed"></textarea>
                
                <div class="flex items-center justify-between sm:justify-end gap-1 shrink-0 pb-0.5 border-t sm:border-t-0 border-slate-800/60 pt-1 sm:pt-0">
                    <div class="flex items-center gap-1">
                        <!-- Attachment button -->
                        <button type="button" onclick="document.getElementById('attachment-file-input').click()" class="w-8 h-8 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-indigo-300 flex items-center justify-center transition-colors text-sm" title="Attach Image, Audio or Document">
                            📎
                        </button>
                        <!-- Emoji button -->
                        <button type="button" onclick="toggleEmojiPicker()" class="w-8 h-8 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-amber-300 flex items-center justify-center transition-colors text-sm" title="Telegram Emojis">
                            😊
                        </button>
                        <!-- GIF button -->
                        <button type="button" onclick="toggleGifPicker()" class="h-8 px-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-pink-300 flex items-center justify-center transition-colors text-[10px] font-black tracking-wider" title="Telegram GIFs">
                            GIF
                        </button>
                        <!-- Voice Message Button -->
                        <button type="button" onclick="startVoiceRecording()" class="w-8 h-8 rounded-xl bg-slate-900 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 flex items-center justify-center transition-colors text-sm" title="Record Voice Message (Like Telegram)">
                            🎙️
                        </button>
                    </div>
                    <!-- Send button -->
                    <button id="btn-send-reply" onclick="sendReply()" class="h-8 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition-all flex items-center gap-1.5 shadow-lg shadow-indigo-600/30 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span>Send</span>
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </div>

            <!-- Voice Recording Live Bar (Hidden initially) -->
            <div id="composer-recording-box" class="hidden items-center justify-between bg-rose-950/40 border border-rose-500/40 rounded-2xl p-3 animate-in fade-in duration-200">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-rose-500 animate-ping"></span>
                    <span class="text-xs font-bold text-rose-400 flex items-center gap-1.5">
                        <span>🎙️ Recording Voice Note:</span>
                        <span id="voice-record-timer" class="font-mono text-white text-sm">00:00</span>
                    </span>
                    <div class="flex items-center gap-1 ml-2">
                        <span class="w-1 h-3 bg-rose-500 rounded-full animate-pulse"></span>
                        <span class="w-1 h-5 bg-rose-400 rounded-full animate-pulse delay-75"></span>
                        <span class="w-1 h-2 bg-rose-500 rounded-full animate-pulse delay-150"></span>
                        <span class="w-1 h-4 bg-rose-400 rounded-full animate-pulse delay-100"></span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="cancelVoiceRecording()" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold transition-colors flex items-center gap-1">
                        <span>❌ Cancel</span>
                    </button>
                    <button type="button" onclick="stopAndSendVoiceRecording()" class="px-4 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition-all shadow-lg shadow-rose-600/30 flex items-center gap-1.5">
                        <span>🚀 Send Voice Note</span>
                    </button>
                </div>
            </div>

            <!-- Hidden File Input -->
            <input type="file" id="attachment-file-input" onchange="handleFileSelect(event)" class="hidden" accept="image/*,audio/*,application/pdf,.doc,.docx,.xls,.xlsx,.zip,.txt">
        </div>
    </section>

    <!-- Mobile CRM Backdrop Overlay -->
    <div id="crm-drawer-backdrop" onclick="toggleMobileCrmDrawer()" class="hidden fixed inset-0 bg-slate-950/80 z-40 backdrop-blur-sm xl:hidden"></div>

    <!-- COLUMN 3: Customer CRM Profile & Simulator -->
    <aside id="inbox-col-crm" class="fixed inset-y-0 right-0 z-50 w-80 max-w-[85vw] bg-slate-900 border-l border-slate-800 shadow-2xl transform translate-x-full transition-transform duration-300 xl:translate-x-0 xl:static xl:flex xl:w-80 xl:bg-slate-900/40 flex-col shrink-0 overflow-y-auto">
        <!-- Close button on mobile/tablet -->
        <div class="p-3 border-b border-slate-800 flex items-center justify-between xl:hidden">
            <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                <span>👤</span> Customer CRM Profile
            </span>
            <button type="button" onclick="toggleMobileCrmDrawer()" class="w-8 h-8 rounded-lg bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center text-lg">&times;</button>
        </div>
        <!-- Contact Profile -->
        <div class="p-5 border-b border-slate-800 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white text-xl mx-auto shadow-md shadow-indigo-500/20" id="crm-avatar">
                T
            </div>
            <div class="flex items-center justify-center gap-1.5 mt-2">
                <h4 class="text-sm font-bold text-white truncate max-w-[180px]" id="crm-name">Tanvir Ahmed</h4>
                <button type="button" onclick="editContactName()" class="text-slate-400 hover:text-indigo-400 text-xs p-1 transition-colors" title="Edit Contact Name">✏️</button>
            </div>
            <p class="text-xs text-slate-400" id="crm-phone">+8801711223344</p>
            <p class="text-xs text-slate-400" id="crm-email">tanvir@gmail.com</p>
        </div>

        <!-- CRM Lead Controls -->
        <div class="p-5 space-y-4 border-b border-slate-800">
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Pipeline Stage</label>
                <select id="crm-stage" onchange="updateContactStage()" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="new">New Lead</option>
                    <option value="contacted">Contacted</option>
                    <option value="interested">Interested / Qualified</option>
                    <option value="won">Deal Won (Customer)</option>
                    <option value="lost">Lost</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Deal Value ($)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1.5 text-slate-500 text-xs">$</span>
                    <input type="number" id="crm-deal-value" onblur="updateContactDeal()" class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-7 pr-3 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-indigo-500" value="850.00">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Tags</label>
                <div class="flex flex-wrap gap-1.5" id="crm-tags-list">
                    <span class="text-[10px] px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">hot_lead</span>
                </div>
                <div class="flex gap-1.5 mt-2">
                    <input type="text" id="new-tag-input" placeholder="Add tag..." class="flex-1 bg-slate-950 border border-slate-800 rounded px-2 py-1 text-xs text-slate-200">
                    <button onclick="addTag()" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-xs text-white rounded">+</button>
                </div>
            <div class="pt-2">
                <button onclick="convertToErpLead()" class="w-full py-2 px-3 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-1.5">
                    <span>⚡ Convert to ERP Lead</span>
                </button>
            </div>
        </div>

        <!-- Live Inbound Message Simulator -->
        <div class="p-5 bg-indigo-950/20">
            <div class="flex items-center gap-1.5 mb-1.5">
                <span class="text-xs">⚡</span>
                <h5 class="text-xs font-bold text-indigo-300 uppercase tracking-wider">Live Simulator</h5>
            </div>
            <p class="text-[11px] text-slate-400 mb-3">Simulate an incoming message from the customer:</p>

            <div class="space-y-2">
                <textarea id="sim-message-text" rows="2" placeholder="e.g. 'Can I speak with human agent?' or 'What is warranty policy?'" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 resize-none"></textarea>
                <button onclick="simulateIncomingMessage()" class="w-full py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs rounded-lg transition-all shadow-md">
                    Simulate Inbound Message
                </button>
            </div>
        </div>
    </aside>
</div>

<!-- Image Lightbox Modal -->
<div id="modal-lightbox" class="fixed inset-0 bg-black/90 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden" onclick="closeLightbox()">
    <div class="relative max-h-[90vh] max-w-[90vw]" onclick="event.stopPropagation()">
        <img id="lightbox-img" src="" class="max-h-[85vh] max-w-[85vw] rounded-2xl object-contain shadow-2xl border border-slate-800">
        <button onclick="closeLightbox()" class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-slate-800 hover:bg-rose-600 text-white font-bold flex items-center justify-center text-sm shadow-xl transition-colors">&times;</button>
    </div>
</div>

<!-- Quick Replies Management Modal -->
<div id="modal-quick-replies" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden" onclick="if(event.target === this) closeQuickRepliesModal()">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
        <!-- Modal Header -->
        <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/40">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-sm text-indigo-400 font-bold">
                    ⚡
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Manage Quick Replies (Canned Responses)</h3>
                    <p class="text-[11px] text-slate-400">Add, edit, or toggle ON to display directly above your chat box</p>
                </div>
            </div>
            <button onclick="closeQuickRepliesModal()" class="w-8 h-8 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center text-sm transition-colors">
                &times;
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-5 overflow-y-auto space-y-6 flex-1 text-xs">
            <!-- Add / Edit Form -->
            <form id="qr-form" onsubmit="saveQuickReply(event)" class="bg-slate-950/60 p-4 rounded-xl border border-slate-800/80 space-y-3">
                <input type="hidden" id="qr-input-id" value="">
                
                <div class="flex items-center justify-between">
                    <h4 id="qr-form-heading" class="text-xs font-bold text-indigo-300 uppercase tracking-wider flex items-center gap-1.5">
                        <span>➕</span> Add New Quick Reply
                    </h4>
                    <button type="button" id="qr-cancel-edit-btn" onclick="resetQuickReplyForm()" class="hidden text-[10px] text-slate-400 hover:text-rose-400 underline">
                        Cancel Edit
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Reply Title / Button Label <span class="text-rose-400">*</span></label>
                        <input type="text" id="qr-input-title" required placeholder="e.g. Bkash Payment, Delivery Address" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-lg text-slate-200 placeholder-slate-500 text-xs focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Shortcut / Tag <span class="text-slate-500">(Optional)</span></label>
                        <input type="text" id="qr-input-shortcut" placeholder="e.g. /bkash or /stock" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-lg text-slate-200 placeholder-slate-500 text-xs focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Full Message Template <span class="text-rose-400">*</span></label>
                    <textarea id="qr-input-message" required rows="3" placeholder="Enter the exact message text to send to customer..." class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-lg text-slate-200 placeholder-slate-500 text-xs focus:outline-none focus:border-indigo-500 resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="submit" id="qr-form-submit-btn" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs rounded-lg transition-all shadow-md flex items-center gap-1.5">
                        <span>💾</span> Save Quick Reply
                    </button>
                </div>
            </form>

            <!-- List of Saved Quick Replies -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                        <span>📋</span> Saved Quick Replies (<span id="qr-count">0</span>)
                    </h4>
                    <span class="text-[10px] text-slate-500">Click any reply to insert into active chat</span>
                </div>

                <div id="qr-modal-list" class="space-y-2.5">
                    <!-- Dynamic List of Cards -->
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-5 py-3 border-t border-slate-800 flex items-center justify-between bg-slate-950/40 text-[11px] text-slate-400">
            <span>💡 Tip: Quick replies save valuable time during high-volume customer inquiries.</span>
            <button onclick="closeQuickRepliesModal()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors">
                Close
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentConversationId = {{ $conversations->first()?->id ?? 'null' }};
    let currentContactId = null;
    let activePlatformFilter = 'all';
    let isPolling = false;
    let lastMessageCount = 0;

    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const queryConv = urlParams.get('conv');
        if (queryConv) {
            const parsed = parseInt(queryConv);
            if (!isNaN(parsed) && document.getElementById(`conv-item-${parsed}`)) {
                selectConversation(parsed);
                return;
            }
        }
        if (currentConversationId) {
            loadConversationDetails(currentConversationId);
        }
        renderQuickRepliesBar();
    });

    function setPlatformFilter(platform) {
        activePlatformFilter = platform;
        document.querySelectorAll('.platform-chip').forEach(c => {
            c.classList.remove('bg-indigo-600', 'text-white');
            c.classList.add('bg-slate-800', 'text-slate-300');
        });
        event.target.closest('button').classList.add('bg-indigo-600', 'text-white');
        event.target.closest('button').classList.remove('bg-slate-800', 'text-slate-300');

        filterConversations();
    }

    function filterConversations() {
        const query = document.getElementById('conv-search').value.toLowerCase();
        document.querySelectorAll('.conv-item').forEach(item => {
            const matchPlatform = activePlatformFilter === 'all' || item.getAttribute('data-platform') === activePlatformFilter;
            const matchText = item.innerText.toLowerCase().includes(query);

            item.style.display = (matchPlatform && matchText) ? 'flex' : 'none';
        });
    }

    function formatRelativeTime(dateStr) {
        if (!dateStr) return 'New';
        const date = new Date(dateStr);
        const now = new Date();
        const diffSec = Math.max(0, Math.floor((now - date) / 1000));
        if (diffSec < 60) return 'Just now';
        const diffMin = Math.floor(diffSec / 60);
        if (diffMin < 60) return diffMin + 'm';
        const diffHours = Math.floor(diffMin / 60);
        if (diffHours < 24) return diffHours + 'h';
        const diffDays = Math.floor(diffHours / 24);
        return diffDays + 'd';
    }

    window.moveConversationToTop = function(convId, previewText, timeStr = 'Just now') {
        if (!convId) return;
        const list = document.getElementById('conversation-list');
        const item = document.getElementById(`conv-item-${convId}`);
        if (!list || !item) return;

        if (previewText) {
            const previewEl = item.querySelector('.conv-preview-text') || item.querySelector('p');
            if (previewEl) previewEl.innerText = previewText;
        }

        if (timeStr) {
            const timeEl = item.querySelector('.conv-time-label');
            if (timeEl) timeEl.innerText = timeStr;
        }

        if (list.firstElementChild !== item) {
            item.style.transition = 'all 0.3s ease';
            list.prepend(item);
        }
    };

    window.markConversationUnread = function(convId, previewText, count) {
        if (!convId) return;
        const item = document.getElementById(`conv-item-${convId}`);
        if (!item) return;

        // If user is actively focused on this conversation right now in the browser, don't mark as unread
        if (convId === currentConversationId && !document.hidden && document.hasFocus()) {
            return;
        }

        // Add visual unread styling
        item.classList.add('bg-indigo-950/20', 'border-l-2', 'border-indigo-400');

        const nameEl = item.querySelector('.conv-contact-name');
        if (nameEl) {
            nameEl.classList.add('text-white', 'font-bold');
            nameEl.classList.remove('text-slate-200');
        }

        const previewEl = item.querySelector('.conv-preview-text');
        if (previewEl) {
            previewEl.classList.add('text-slate-100', 'font-semibold');
            previewEl.classList.remove('text-slate-400');
            if (previewText) previewEl.innerText = previewText;
        }

        const timeEl = item.querySelector('.conv-time-label');
        if (timeEl) {
            timeEl.classList.add('text-indigo-400', 'font-bold');
            timeEl.classList.remove('text-slate-500');
            timeEl.innerText = 'Just now';
        }

        const dot = item.querySelector('.conv-unread-dot');
        if (dot) dot.classList.remove('hidden');

        const badge = item.querySelector('.conv-unread-badge');
        if (badge) {
            badge.classList.remove('hidden');
            const currentBadgeVal = parseInt(badge.innerText) || 0;
            badge.innerText = count !== undefined ? count : (currentBadgeVal + 1);
        }

        // Jump to top of list
        window.moveConversationToTop(convId, previewText, 'Just now');
    };

    window.markConversationAsRead = function(convId) {
        if (!convId) return;
        const item = document.getElementById(`conv-item-${convId}`);
        if (!item) return;

        // Remove unread highlight
        item.classList.remove('bg-indigo-950/20', 'border-indigo-400');

        const nameEl = item.querySelector('.conv-contact-name');
        if (nameEl) {
            nameEl.classList.remove('text-white', 'font-bold');
            nameEl.classList.add('text-slate-200');
        }

        const previewEl = item.querySelector('.conv-preview-text');
        if (previewEl) {
            previewEl.classList.remove('text-slate-100', 'font-semibold');
            previewEl.classList.add('text-slate-400');
        }

        const timeEl = item.querySelector('.conv-time-label');
        if (timeEl) {
            timeEl.classList.remove('text-indigo-400', 'font-bold');
            timeEl.classList.add('text-slate-500');
        }

        const dot = item.querySelector('.conv-unread-dot');
        if (dot) dot.classList.add('hidden');

        const badge = item.querySelector('.conv-unread-badge');
        if (badge) {
            badge.innerText = '0';
            badge.classList.add('hidden');
        }
    };

    window.addEventListener('focus', () => {
        if (currentConversationId) {
            window.markConversationAsRead(currentConversationId);
        }
    });

    function syncConversationListDOM(conversations) {
        const list = document.getElementById('conversation-list');
        if (!list || !Array.isArray(conversations)) return;

        const searchInput = document.getElementById('conv-search');
        const isSearching = searchInput && searchInput.value.trim().length > 0;

        conversations.forEach(conv => {
            const item = document.getElementById(`conv-item-${conv.id}`);
            if (item) {
                // Update preview text
                if (conv.latest_message) {
                    const previewEl = item.querySelector('.conv-preview-text') || item.querySelector('p');
                    if (previewEl) {
                        const text = conv.latest_message.content || (conv.latest_message.attachments ? '[Attachment]' : '');
                        if (text) previewEl.innerText = text;
                    }
                }

                // Update timestamp
                const timeEl = item.querySelector('.conv-time-label');
                if (timeEl && conv.last_message_at) {
                    timeEl.innerText = formatRelativeTime(conv.last_message_at);
                }

                // Sync unread visual state
                if (conv.id !== currentConversationId) {
                    if (conv.unread_count > 0) {
                        window.markConversationUnread(conv.id, conv.latest_message?.content, conv.unread_count);
                    } else {
                        window.markConversationAsRead(conv.id);
                    }
                }

                // If user is not searching, append in sorted order
                if (!isSearching) {
                    list.appendChild(item);
                }
            }
        });

        if (isSearching) {
            filterConversations();
        }
    }

    async function selectConversation(id) {
        currentConversationId = id;
        document.querySelectorAll('.conv-item').forEach(el => el.classList.remove('bg-indigo-950/30', 'border-l-2', 'border-indigo-500'));
        const selected = document.getElementById(`conv-item-${id}`);
        if (selected) selected.classList.add('bg-indigo-950/30', 'border-l-2', 'border-indigo-500');

        // Mark as seen and clear unread badge
        window.markConversationAsRead(id);

        // On mobile/tablet (< lg), switch seamlessly to chat view
        if (window.innerWidth < 1024) {
            document.getElementById('inbox-col-threads')?.classList.add('hidden');
            document.getElementById('inbox-col-threads')?.classList.remove('flex');
            document.getElementById('inbox-col-chat')?.classList.remove('hidden');
            document.getElementById('inbox-col-chat')?.classList.add('flex');
        }

        await loadConversationDetails(id);
    }

    function showMobileThreadsList() {
        document.getElementById('inbox-col-chat')?.classList.add('hidden');
        document.getElementById('inbox-col-chat')?.classList.remove('flex');
        document.getElementById('inbox-col-threads')?.classList.remove('hidden');
        document.getElementById('inbox-col-threads')?.classList.add('flex');
    }

    let isCrmDrawerOpen = false;
    function toggleMobileCrmDrawer() {
        const drawer = document.getElementById('inbox-col-crm');
        const backdrop = document.getElementById('crm-drawer-backdrop');
        if (!drawer) return;
        isCrmDrawerOpen = !isCrmDrawerOpen;
        if (isCrmDrawerOpen) {
            drawer.classList.remove('translate-x-full');
            backdrop?.classList.remove('hidden');
        } else {
            drawer.classList.add('translate-x-full');
            backdrop?.classList.add('hidden');
        }
    }

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            document.getElementById('inbox-col-threads')?.classList.remove('hidden');
            document.getElementById('inbox-col-threads')?.classList.add('flex');
            document.getElementById('inbox-col-chat')?.classList.remove('hidden');
            document.getElementById('inbox-col-chat')?.classList.add('flex');
        }
        if (window.innerWidth >= 1280) {
            document.getElementById('crm-drawer-backdrop')?.classList.add('hidden');
            isCrmDrawerOpen = false;
        }
    });

    async function loadConversationDetails(id) {
        try {
            const res = await fetch(`/api/conversations/${id}`);
            const data = await res.json();

            currentContactId = data.contact?.id || null;

            document.getElementById('chat-header-name').innerText = data.contact?.name || 'Customer';
            document.getElementById('chat-header-avatar').innerText = (data.contact?.name || 'C').charAt(0).toUpperCase();
            document.getElementById('chat-header-platform').innerText = (data.channel?.platform || 'Social').toUpperCase();
            document.getElementById('chat-header-sender-id').innerText = data.contact?.platform_sender_id || '';

            // Check channel active status
            const isChannelActive = data.channel && data.channel.is_active;
            const banner = document.getElementById('channel-inactive-banner');
            const replyInput = document.getElementById('reply-input');
            const sendBtn = document.getElementById('btn-send-reply');

            if (banner) {
                if (!isChannelActive) {
                    banner.classList.remove('hidden');
                    document.getElementById('channel-inactive-text').innerText = `Channel '${data.channel?.name || 'Social'}' is currently disconnected / paused.`;
                    replyInput.disabled = true;
                    replyInput.placeholder = "Channel is disconnected / paused. Turn it On to send and receive messages.";
                    if (sendBtn) sendBtn.disabled = true;
                } else {
                    banner.classList.add('hidden');
                    replyInput.disabled = false;
                    replyInput.placeholder = "Type reply to customer... (Press Enter to send, Shift+Enter for new line)";
                    if (sendBtn) sendBtn.disabled = false;
                }
            }

            updateAiModeButtons(data.ai_mode);
            updateResolvedButton(data.status || 'open');

            // Update Customer Online/Active status in header
            const lastActive = data.contact?.last_active_at || data.last_message_at;
            const statusBadge = document.getElementById('chat-header-online-status');
            if (statusBadge) {
                if (lastActive) {
                    const diffMinutes = (new Date() - new Date(lastActive)) / (1000 * 60);
                    if (diffMinutes <= 10) {
                        statusBadge.className = 'inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30';
                        statusBadge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Active Now';
                    } else if (diffMinutes < 60) {
                        statusBadge.className = 'inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20';
                        statusBadge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Active ${Math.round(diffMinutes)}m ago`;
                    } else {
                        statusBadge.className = 'inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 border border-slate-700/60';
                        statusBadge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Offline';
                    }
                } else {
                    statusBadge.className = 'inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 border border-slate-700/60';
                    statusBadge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Offline';
                }
            }

            // Update CRM Sidebar
            document.getElementById('crm-name').innerText = data.contact?.name || 'Customer';
            document.getElementById('crm-avatar').innerText = (data.contact?.name || 'C').charAt(0).toUpperCase();
            document.getElementById('crm-phone').innerText = data.contact?.phone || 'No phone recorded';
            document.getElementById('crm-email').innerText = data.contact?.email || 'No email recorded';
            document.getElementById('crm-stage').value = data.contact?.pipeline_stage || 'new';
            document.getElementById('crm-deal-value').value = data.contact?.deal_value || 0;

            const tagsList = document.getElementById('crm-tags-list');
            tagsList.innerHTML = '';
            (data.contact?.tags || []).forEach(tag => {
                const pill = document.createElement('span');
                pill.className = 'text-[10px] px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30';
                pill.innerText = tag;
                tagsList.appendChild(pill);
            });

            renderMessages(data.messages || []);
        } catch (err) {
            console.error(err);
        }
    }

    async function editContactName() {
        if (!currentContactId) return;
        const currentName = document.getElementById('crm-name').innerText;
        const { value: newName } = await Swal.fire({
            title: '✏️ Edit Customer Name',
            input: 'text',
            inputValue: currentName === 'Facebook user' ? '' : currentName,
            inputPlaceholder: 'Enter customer real name...',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#334155',
            confirmButtonText: 'Save Name',
            cancelButtonText: 'Cancel'
        });

        if (newName && newName.trim()) {
            try {
                const res = await fetch(`/api/contacts/${currentContactId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ name: newName.trim() })
                });
                if (res.ok) {
                    const clean = newName.trim();
                    document.getElementById('crm-name').innerText = clean;
                    document.getElementById('chat-header-name').innerText = clean;
                    document.getElementById('crm-avatar').innerText = clean.charAt(0).toUpperCase();
                    document.getElementById('chat-header-avatar').innerText = clean.charAt(0).toUpperCase();

                    const activeConvItem = document.querySelector(`.conv-item[onclick*="${currentConversationId}"] h4`);
                    if (activeConvItem) activeConvItem.innerText = clean;

                    Toast.fire({ icon: 'success', title: 'Customer name updated!' });
                }
            } catch (e) {
                console.error(e);
            }
        }
    }

    let selectedAttachmentFile = null;

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function renderMessages(messages) {
        const container = document.getElementById('messages-container');
        container.innerHTML = '';

        messages.forEach(msg => {
            const isInbound = msg.direction === 'inbound';
            const isAi = msg.sender_type === 'ai_agent';
            const isSystem = msg.sender_type === 'system';

            const wrapper = document.createElement('div');
            wrapper.className = `flex ${isInbound ? 'justify-start' : 'justify-end'} group relative my-1`;

            let badge = '';
            if (isAi) {
                badge = `<span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.2 rounded bg-indigo-400/20 text-indigo-300 border border-indigo-400/30 mb-1">🤖 AI Agent</span>`;
            } else if (isSystem) {
                badge = `<span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.2 rounded bg-amber-400/20 text-amber-300 mb-1">⚠️ System Notification</span>`;
            }

            // Render Attachments
            let attachmentsHtml = '';
            if (msg.attachments && Array.isArray(msg.attachments) && msg.attachments.length > 0) {
                msg.attachments.forEach(att => {
                    const isImg = att.type === 'image' || (att.url && att.url.match(/\.(jpeg|jpg|gif|png|webp|svg)($|\?)/i));
                    const isAudio = att.type === 'audio' || (att.url && att.url.match(/\.(mp3|ogg|wav|m4a|oga)($|\?)/i));

                    if (isImg) {
                        attachmentsHtml += `
                            <div class="mb-2">
                                <img src="${att.url}" alt="${escapeHtml(att.name || 'Image')}" 
                                     onclick="openLightbox('${att.url}')"
                                     class="max-w-xs max-h-64 rounded-xl object-cover cursor-pointer hover:opacity-90 transition-opacity border border-white/10 shadow-md">
                            </div>
                        `;
                    } else if (isAudio) {
                        attachmentsHtml += `
                            <div class="flex items-center gap-2 p-2 bg-slate-950/70 rounded-xl mb-2 border border-white/10">
                                <span class="text-base">🎙️</span>
                                <audio controls src="${att.url}" class="h-8 max-w-[220px]"></audio>
                            </div>
                        `;
                    } else {
                        attachmentsHtml += `
                            <a href="${att.url}" target="_blank" download="${escapeHtml(att.name || 'document')}" 
                               class="flex items-center gap-2.5 p-2.5 bg-slate-950/70 hover:bg-slate-950 rounded-xl mb-2 border border-white/10 transition-colors group/att">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-300 flex items-center justify-center font-bold text-sm">📄</div>
                                <div class="min-w-0 text-left">
                                    <p class="text-xs font-semibold text-slate-100 truncate max-w-[160px] group-hover/att:text-indigo-300">${escapeHtml(att.name || 'Attachment File')}</p>
                                    <span class="text-[10px] text-slate-400">Click to download</span>
                                </div>
                                <span class="text-xs text-slate-400 ml-auto">⬇️</span>
                            </a>
                        `;
                    }
                });
            }

            // Render Message Text (if not empty or placeholder)
            let contentHtml = '';
            if (msg.content && msg.content.trim()) {
                contentHtml = `<div class="break-words leading-relaxed whitespace-pre-wrap">${escapeHtml(msg.content)}</div>`;
            }

            // Render existing Reactions
            let reactionsHtml = '';
            if (msg.reactions && typeof msg.reactions === 'object' && Object.keys(msg.reactions).length > 0) {
                reactionsHtml = '<div class="flex flex-wrap gap-1 mt-1.5">';
                for (const [emoji, count] of Object.entries(msg.reactions)) {
                    if (count > 0) {
                        reactionsHtml += `
                            <button type="button" onclick="reactToMessage(${msg.id}, '${emoji}')" 
                                    class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full bg-slate-800/90 hover:bg-slate-700 border border-slate-700 text-slate-200 shadow-sm transition-transform active:scale-95" 
                                    title="Reaction ${emoji}">
                                <span>${emoji}</span>
                                <span class="font-bold text-[10px] text-slate-400">${count}</span>
                            </button>
                        `;
                    }
                }
                reactionsHtml += '</div>';
            }

            // Hover quick-reaction bar
            const quickReactionsBar = `
                <div class="absolute -top-3.5 ${isInbound ? 'left-2' : 'right-2'} hidden group-hover:flex items-center gap-0.5 bg-slate-900 border border-slate-700/80 rounded-full px-2 py-0.5 shadow-xl z-20 backdrop-blur-md animate-in fade-in duration-100">
                    <button type="button" onclick="reactToMessage(${msg.id}, '👍')" class="hover:scale-125 transition-transform p-0.5 text-xs" title="Like">👍</button>
                    <button type="button" onclick="reactToMessage(${msg.id}, '❤️')" class="hover:scale-125 transition-transform p-0.5 text-xs" title="Love">❤️</button>
                    <button type="button" onclick="reactToMessage(${msg.id}, '😂')" class="hover:scale-125 transition-transform p-0.5 text-xs" title="Haha">😂</button>
                    <button type="button" onclick="reactToMessage(${msg.id}, '😮')" class="hover:scale-125 transition-transform p-0.5 text-xs" title="Wow">😮</button>
                    <button type="button" onclick="reactToMessage(${msg.id}, '🙏')" class="hover:scale-125 transition-transform p-0.5 text-xs" title="Thanks">🙏</button>
                    <button type="button" onclick="reactToMessage(${msg.id}, '🔥')" class="hover:scale-125 transition-transform p-0.5 text-xs" title="Fire">🔥</button>
                </div>
            `;

            wrapper.innerHTML = `
                <div class="max-w-[75%] relative">
                    ${quickReactionsBar}
                    ${badge ? `<div>${badge}</div>` : ''}
                    <div class="p-3.5 rounded-2xl text-xs shadow-sm ${
                        isInbound 
                            ? 'bg-slate-900 border border-slate-800 text-slate-100 rounded-bl-sm inbound-bubble' 
                            : (isAi ? 'bg-indigo-950/80 border border-indigo-500/40 text-indigo-100 rounded-br-sm' : 'bg-indigo-600 text-white rounded-br-sm outbound-bubble')
                    }">
                        ${attachmentsHtml}
                        ${contentHtml}
                    </div>
                    ${reactionsHtml}
                    <div class="text-[10px] text-slate-500 mt-1 ${isInbound ? 'text-left' : 'text-right'}">
                        ${new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} • ${msg.status}
                    </div>
                </div>
            `;
            container.appendChild(wrapper);
        });

        container.scrollTop = container.scrollHeight;
        lastMessageCount = messages.length;
    }

    // Auto-poll active social channels and current chat every 3.5 seconds
    let knownLatestMessageMap = {};

    setInterval(async () => {
        if (isPolling || !currentConversationId) return;
        isPolling = true;
        try {
            const res = await fetch('/tenant/inbox/poll', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ conversation_id: currentConversationId })
            });
            const data = await res.json();
            if (data.success) {
                // Check if current conversation has new messages
                if (data.messages && data.messages.length > 0) {
                    if (data.messages.length !== lastMessageCount) {
                        if (lastMessageCount > 0) {
                            const newMsgs = data.messages.slice(lastMessageCount);
                            const hasInbound = newMsgs.some(m => m.direction === 'inbound');
                            if (hasInbound) {
                                if (typeof window.playDingChime === 'function') window.playDingChime();
                                const lastInbound = newMsgs.filter(m => m.direction === 'inbound').pop();
                                const contactName = document.getElementById('chat-header-name')?.innerText || 'Customer';
                                const platform = document.getElementById('chat-header-platform')?.innerText || 'Social';
                                if (typeof window.showDesktopNotification === 'function') {
                                    window.showDesktopNotification(`💬 ${contactName} (${platform})`, lastInbound.content || 'Sent an attachment', currentConversationId);
                                }
                                if (typeof window.flashDocumentTitle === 'function') {
                                    window.flashDocumentTitle(`(1) 💬 ${contactName}`);
                                }
                                if (document.hidden || !document.hasFocus()) {
                                    window.markConversationUnread(currentConversationId, lastInbound.content || '[Attachment]');
                                } else {
                                    window.moveConversationToTop(currentConversationId, lastInbound.content || '[Attachment]', 'Just now');
                                }
                            }
                        }
                        renderMessages(data.messages);
                    }
                }

                // Sync and reorder all conversations in the sidebar list
                if (data.conversations && Array.isArray(data.conversations)) {
                    syncConversationListDOM(data.conversations);

                    data.conversations.forEach(c => {
                        if (c.latest_message) {
                            const prevId = knownLatestMessageMap[c.id];
                            if (prevId && c.latest_message.id > prevId && c.latest_message.direction === 'inbound' && c.id !== currentConversationId) {
                                if (typeof window.playDingChime === 'function') window.playDingChime();
                                const senderName = c.contact?.name || 'Customer';
                                const plat = (c.channel?.platform || 'Social').toUpperCase();
                                if (typeof window.showDesktopNotification === 'function') {
                                    window.showDesktopNotification(`💬 ${senderName} (${plat})`, c.latest_message.content || 'New inquiry', c.id);
                                }
                                if (typeof window.flashDocumentTitle === 'function') {
                                    window.flashDocumentTitle(`(1) 💬 ${senderName}`);
                                }
                                Toast.fire({
                                    icon: 'info',
                                    title: `💬 New message from ${senderName} (${plat}): "${(c.latest_message.content || 'Sent attachment').slice(0, 40)}"`,
                                    timer: 5000
                                });
                                window.markConversationUnread(c.id, c.latest_message.content || '[Attachment]');
                            }
                            knownLatestMessageMap[c.id] = c.latest_message.id;
                        }
                    });
                }

                if (data.synced_count > 0) {
                    Toast.fire({
                        icon: 'info',
                        title: `⚡ Received ${data.synced_count} new message${data.synced_count > 1 ? 's' : ''}!`,
                        iconColor: '#38bdf8'
                    });
                }
            }
        } catch (e) {
            // silent catch
        } finally {
            isPolling = false;
        }
    }, 3500);

    async function manualSync() {
        const btn = document.getElementById('btn-manual-sync');
        const icon = document.getElementById('sync-icon');
        icon.classList.add('animate-spin');
        btn.disabled = true;

        try {
            const res = await fetch('/tenant/inbox/poll', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ conversation_id: currentConversationId })
            });
            const data = await res.json();
            if (data.synced_count > 0) {
                Toast.fire({ icon: 'success', title: `Synced ${data.synced_count} new messages from Telegram!`, iconColor: '#10b981' });
            } else {
                Toast.fire({ icon: 'info', title: 'Inbox is up to date (no pending messages in queue)', iconColor: '#38bdf8' });
            }
            if (currentConversationId) {
                await loadConversationDetails(currentConversationId);
            }
        } catch (e) {
            Toast.fire({ icon: 'error', title: 'Failed to sync with Telegram.', iconColor: '#f43f5e' });
        } finally {
            icon.classList.remove('animate-spin');
            btn.disabled = false;
        }
    }

    // Attachment File Handler
    function handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        selectedAttachmentFile = file;
        const previewBar = document.getElementById('attachment-preview-bar');
        const thumb = document.getElementById('attachment-thumb');
        const nameEl = document.getElementById('attachment-name');
        const sizeEl = document.getElementById('attachment-size');

        nameEl.innerText = file.name;
        const kb = Math.round(file.size / 1024);
        sizeEl.innerText = kb > 1024 ? (kb / 1024).toFixed(1) + ' MB' : kb + ' KB';

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                thumb.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
            };
            reader.readAsDataURL(file);
        } else {
            thumb.innerHTML = `<span class="text-sm">📄</span>`;
        }

        previewBar.classList.remove('hidden');
        previewBar.classList.add('flex');
    }

    function clearAttachment() {
        selectedAttachmentFile = null;
        document.getElementById('attachment-file-input').value = '';
        const previewBar = document.getElementById('attachment-preview-bar');
        previewBar.classList.add('hidden');
        previewBar.classList.remove('flex');
    }

    // Lightbox modal functions
    function openLightbox(url) {
        const modal = document.getElementById('modal-lightbox');
        const img = document.getElementById('lightbox-img');
        img.src = url;
        modal.classList.remove('hidden');
    }

    function closeLightbox() {
        const modal = document.getElementById('modal-lightbox');
        modal.classList.add('hidden');
        document.getElementById('lightbox-img').src = '';
    }

    // Reactions
    async function reactToMessage(messageId, emoji) {
        try {
            const res = await fetch(`/api/messages/${messageId}/react`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ emoji: emoji })
            });
            const data = await res.json();
            if (res.ok) {
                await loadConversationDetails(currentConversationId);
                Toast.fire({ icon: 'success', title: `Reacted ${emoji}!`, timer: 1200, iconColor: '#38bdf8' });
            }
        } catch (err) {
            console.error(err);
        }
    }

    // ==========================================
    // 1. TELEGRAM-STYLE EMOJIS WITH REALTIME SEARCH
    // ==========================================
    let currentEmojiTab = 'smileys';
    const emojiData = {
        smileys: [
            '😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃','😉','😊','😇','🥰','😍','🤩','😘','😗','😚','😋','😛','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😒','🙄','😬','🤥','😌','😔','😪','🤤','😴','😷','🤒','🤕','🤢','🤮','🤧','🥵','🥶','🥴','😵','🤯','🤠','🥳','😎','🤓','🧐','☹️','🙁','😮','😯','😲','😳','🥺','😦','😧','😨','😰','😥','😢','😭','😱','😖','😣','😞','😓','😩','😫','🥱','😤','😡','😠','🤬','😈','👿','💀','☠️','💩','🤡','👹','👺','👻','👽','👾','🤖'
        ],
        gestures: [
            '👋','🤚','🖐️','✋','🖖','🤙','👈','👉','👆','🖕','👇','☝️','👍','👎','✊','👊','🤛','🤜','👏','🙌','👐','🤲','🤝','🙏','✍️','💅','🤳','💪','🦾','🦿','🦵','🦶','👂','🦻','👃','🧠','🫀','🫁','🦷','🦴','👀','👁️','👅','👄','👶','🧒','👦','👧','🧑','👱','👨','🧔','👩','🧓','👴','👵','🙍','🙎','🙅','🙆','💁','🙋','🧏','🙇','🤦','🤷'
        ],
        animals: [
            '🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼','🐨','🐯','🦁','🐮','🐷','🐸','🐵','🐔','🐧','🐦','🐤','🦆','🦅','🦉','🦇','🐺','🐗','🐴','🦄','🐝','🐛','🦋','🐌','🐞','🐜','🦟','🦗','🕷️','🦂','🐢','🐍','🦎','🦖','🐙','🦑','🦐','🦞','🦀','🐡','🐠','🐟','🐬','🐳','🦈','🐊','🐅','🐆','🦓','🦍','🦧','🐘','🦛','🦏','🐪','🐫','🦒','🦘','🌸','🌹','🌺','🌻','🌼','🌷','🌱','🌲','🌳','🌴','🌵','🌾','🌿','🍀','🍁','🍂','🍃'
        ],
        food: [
            '🍏','🍎','🍐','🍊','🍋','🍌','🍉','🍇','🍓','🍈','🍒','🍑','🥭','🍍','🥥','🥝','🍅','🍆','🥑','🥦','🥬','🥒','🌶️','🌽','🥕','🧄','🧅','🥔','🍠','🥐','🥯','🍞','🥖','🥨','🧀','🥚','🍳','🧈','🥞','🧇','🥓','🥩','🍗','🍖','🦴','🌭','🍔','🍟','🍕','🥪','🥙','🌮','🌯','🥗','🥘','🥫','🍝','🍜','🍲','🍛','🍣','🍱','🥟','🦪','🍤','🍙','🍚','🍘','🍥','🍦','🍧','🍨','🍩','🍪','🎂','🍰','🧁','🍫','🍬','🍭','☕','🍵','🧃','🥤','🧋','🍶','🍺','🍻','🥂','🍷','🥃','🍸'
        ],
        travel: [
            '🚗','🚕','🚙','🚌','🚎','🏎️','🚓','🚑','🚒','🚐','🛻','🚚','🚛','🚜','🛵','🏍️','🛺','🚲','🛴','🛹','🛼','🚨','🚥','🚦','🛑','🚢','✈️','🛫','🛬','🚀','🛸','🚁','🚂','🚆','🚇','🚉','🌍','🌎','🌏','🗺️','🏖️','🏝️','🏜️','🌋','⛰️','🏔️','🏕️','🏠','🏡','🏢','🏣','🏥','🏦','🏨','🏩','🏪','🏫','🏬','🏭','🏰','🗼','🗽','🕌','🛕','⛩️'
        ],
        activities: [
            '⚽','🏀','🏈','⚾','🥎','🎾','🏐','🏉','🥏','🎱','🪀','🏓','🏸','🏒','🏑','🥍','🏏','🥅','⛳','🪁','🏹','🎣','🤿','🥊','🥋','🎽','🛹','🛼','🛷','⛸️','🥌','🎿','⛷️','🏂','🏆','🥇','🥈','🥉','🏅','🎖️','🏵️','🎗️','🎫','🎟️','🎪','🎭','🎨','🎬','🎤','🎧','🎼','🎹','🥁','🎷','🎺','🎸','🪕','🎻','🎲','♟️','🎯','🎳','🎮','🎰','📱','💻','🖥️','🖨️','⌨️','🖱️','💾','💿','📼','📷','📸','📹','🎥','📽️','📞','☎️','📟','📠','📺','📻','🎙️','⏰','⏱️','⏳','💡','🔦','🕯️','💵','💴','💶','💷','💰','💳','💎','⚖️','🔧','🔨','⛏️','🛠️','🔩','⚙️','🧲','🔫','💣','🧨','🔪','🗡️','🛡️','🚬','⚰️','🔮','💊','💉','🩸','🩹','🩺','🚪','🛏️','🛋️','🚿','🛁','🧴','🧷','🧹','🧺','🧻','🧼','🧽','🧯','🛒','🎁','🎈','🎏','🎀','🪄','🎊','🎉','📦','🏷️','✉️','📩','📧','📨'
        ],
        symbols: [
            '❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❣️','💕','💞','💓','💗','💖','💘','💝','💟','☮️','✝️','☪️','🕉️','☸️','✡️','🔯','🕎','☯️','☦️','🛐','⛎','♈','♉','♊','♋','♌','♍','♎','♏','♐','♑','♒','♓','🆔','⚛️','🉑','☢️','☣️','📴','📳','🈶','🈚','🈸','🈺','🈷️','✴️','🆚','💮','🉐','㊙️','㊗️','🈴','🈵','🈹','🈲','🅰️','🅱️','🆎','🆑','🅾️','🆘','❌','⭕','🛑','⛔','📛','🚫','💯','💢','♨️','🚷','🚯','🚳','🚱','🔞','📵','🚭','❗','❕','❓','❔','‼️','⁉️','🔅','🔆','〽️','⚠️','🚸','🔱','⚜️','🔰','♻️','✅','🈯','💹','❇️','✳️','❎','🌐','💠','Ⓜ️','🌀','💤','🏧','🚾','♿','🅿️','🛗','🈳','🈂️','🛂','🛃','🛄','🛅','🚹','🚺','🚼','⚧️','🚻','🚮','🎦','📶','🈁','🆖','🆗','🆙','🆒','🆕','🆓','0️⃣','1️⃣','2️⃣','3️⃣','4️⃣','5️⃣','6️⃣','7️⃣','8️⃣','9️⃣','🔟','🏁','🚩','🎌','🏴','🏳️','🏳️‍🌈','🏳️‍⚧️','🏴‍☠️','🇧🇩','🇺🇸','🇬🇧','🇮🇳','🇨🇦','🇦🇺','🇩🇪','🇫🇷','🇯🇵','🇨🇳'
        ]
    };

    function initEmojiPicker() {
        switchEmojiTab('smileys');
        initGifPicker();
    }

    function switchEmojiTab(tab) {
        currentEmojiTab = tab;
        document.querySelectorAll('.emoji-tab-btn').forEach(btn => {
            if (btn.getAttribute('data-tab') === tab) {
                btn.className = 'emoji-tab-btn text-xs px-2 py-0.5 rounded shrink-0 bg-indigo-600 text-white font-bold';
            } else {
                btn.className = 'emoji-tab-btn text-xs px-2 py-0.5 rounded shrink-0 text-slate-400 hover:text-white';
            }
        });

        const searchVal = document.getElementById('emoji-search-input')?.value.trim();
        if (searchVal) {
            filterEmojis(searchVal);
        } else {
            renderEmojiList(emojiData[tab] || []);
        }
    }

    function renderEmojiList(list) {
        const grid = document.getElementById('emoji-grid');
        grid.innerHTML = '';
        if (!list || list.length === 0) {
            grid.innerHTML = '<div class="col-span-8 py-6 text-center text-xs text-slate-500">No matching emojis found</div>';
            return;
        }
        list.forEach(emoji => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-7 h-7 rounded hover:bg-slate-800 flex items-center justify-center transition-transform hover:scale-125 text-base';
            btn.innerText = emoji;
            btn.onclick = () => insertEmoji(emoji);
            grid.appendChild(btn);
        });
    }

    function filterEmojis(query) {
        const q = query.trim().toLowerCase();
        if (!q) {
            renderEmojiList(emojiData[currentEmojiTab] || []);
            return;
        }

        // Search across all categories
        const results = [];
        for (const cat in emojiData) {
            emojiData[cat].forEach(e => {
                if (!results.includes(e)) {
                    // Match character or simple keywords
                    results.push(e);
                }
            });
        }
        // Filter sublist based on query
        const matched = results.filter(e => e.includes(q) || matchesEmojiKeyword(e, q));
        renderEmojiList(matched.slice(0, 80));
    }

    function matchesEmojiKeyword(emoji, q) {
        const keywords = {
            'smile': ['😀','😃','😄','😁','😆','😅','😊','😇','🙂'],
            'laugh': ['🤣','😂','😆'],
            'love': ['🥰','😍','😘','❤️','🧡','💛','💚','💙','💜','💕','💓','💗','💖'],
            'heart': ['❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❣️','💕'],
            'fire': ['🔥'],
            'thumbs': ['👍','👎'],
            'party': ['🥳','🎉','🎊'],
            'sad': ['😢','😭','😞','😔','🙁','☹️'],
            'cry': ['😢','😭'],
            'dog': ['🐶'],
            'cat': ['🐱'],
            'car': ['🚗','🚕','🚙'],
            'money': ['💵','💰','💳','🤑'],
            'food': ['🍔','🍕','🍟','🌭','🌮','🥪','🥗'],
            'flag': ['🇧🇩','🇺🇸','🇬🇧','🇮🇳']
        };
        for (const [key, emojis] of Object.entries(keywords)) {
            if (key.includes(q) && emojis.includes(emoji)) return true;
        }
        return false;
    }

    function insertEmoji(emoji) {
        const input = document.getElementById('reply-input');
        const start = input.selectionStart || 0;
        const end = input.selectionEnd || 0;
        const val = input.value;
        input.value = val.substring(0, start) + emoji + val.substring(end);
        input.focus();
        input.setSelectionRange(start + emoji.length, start + emoji.length);
        document.getElementById('popover-emoji').classList.add('hidden');
    }

    function toggleEmojiPicker() {
        const p = document.getElementById('popover-emoji');
        const isHidden = p.classList.contains('hidden');
        p.classList.toggle('hidden');
        document.getElementById('popover-gif').classList.add('hidden');
        if (isHidden) {
            initEmojiPicker();
            setTimeout(() => document.getElementById('emoji-search-input')?.focus(), 50);
        }
    }

    // ==========================================
    // 2. TELEGRAM-STYLE GIFS WITH SEARCH
    // ==========================================
    const curatedGifs = [
        { title: 'Welcome / Hello', url: 'https://media.giphy.com/media/l0FF564424uPow3ey3/giphy.gif', tags: 'hello welcome hi waving' },
        { title: 'Thank You!', url: 'https://media.giphy.com/media/osAcIGExETniE/giphy.gif', tags: 'thank you thanks grateful appreciated' },
        { title: 'Thumbs Up', url: 'https://media.giphy.com/media/111ebonMs90YLu/giphy.gif', tags: 'thumbs up ok great good awesome' },
        { title: 'Celebration', url: 'https://media.giphy.com/media/artj92V8o75VPL7AeQ/giphy.gif', tags: 'celebrate celebration party happy yay' },
        { title: 'Working On It', url: 'https://media.giphy.com/media/ule4vhcY1xEKQ/giphy.gif', tags: 'working coding typing specialist review' },
        { title: 'High Five', url: 'https://media.giphy.com/media/3oEjHV0z8S7WM4MwnK/giphy.gif', tags: 'high five team support yes' },
        { title: 'Laughing Cat', url: 'https://media.giphy.com/media/mlvseq9yvZhba/giphy.gif', tags: 'cat laugh laughing haha funny' },
        { title: 'Mind Blown', url: 'https://media.giphy.com/media/xT0xeJpnrWC4XWblEk/giphy.gif', tags: 'mind blown wow shocked unbelievable' },
        { title: 'Love / Hearts', url: 'https://media.giphy.com/media/M90mJvfWfd5mbUuULX/giphy.gif', tags: 'love hearts kiss customer care happy' },
        { title: 'Dancing Baby', url: 'https://media.giphy.com/media/blSTtZehjAZ8I/giphy.gif', tags: 'dance dancing baby happy weekend' },
        { title: 'Bye / See you', url: 'https://media.giphy.com/media/kxxgt4GvrBzDG/giphy.gif', tags: 'bye goodbye see you later wave' },
        { title: 'Yes / Agree', url: 'https://media.giphy.com/media/3o7abKhOpu0NwenH3O/giphy.gif', tags: 'yes agree nod correct confirmed' }
    ];

    function initGifPicker() {
        searchGifs('trending');
    }

    async function searchGifs(query) {
        const q = (query || 'trending').trim().toLowerCase();
        const grid = document.getElementById('gif-grid');
        grid.innerHTML = '<div class="col-span-2 py-6 text-center text-xs text-slate-400">Loading GIFs...</div>';

        // Try Giphy Public Search first if online
        try {
            const apiRes = await fetch(`https://api.giphy.com/v1/gifs/search?api_key=dc6zaTOxFJmzC&limit=16&q=${encodeURIComponent(q === 'trending' ? 'reaction' : q)}`);
            if (apiRes.ok) {
                const apiData = await apiRes.json();
                if (apiData.data && apiData.data.length > 0) {
                    renderGifResults(apiData.data.map(item => ({
                        title: item.title || 'GIF',
                        url: item.images?.fixed_height?.url || item.images?.original?.url
                    })));
                    return;
                }
            }
        } catch (e) {
            // Fallback to local curated catalogue
        }

        // Local curated catalogue matching
        let filtered = curatedGifs;
        if (q !== 'trending') {
            filtered = curatedGifs.filter(g => g.title.toLowerCase().includes(q) || g.tags.toLowerCase().includes(q));
            if (filtered.length === 0) filtered = curatedGifs;
        }
        renderGifResults(filtered);
    }

    function renderGifResults(gifs) {
        const grid = document.getElementById('gif-grid');
        grid.innerHTML = '';
        gifs.forEach(g => {
            const item = document.createElement('div');
            item.className = 'rounded-xl overflow-hidden bg-slate-950 border border-slate-800 hover:border-indigo-500 cursor-pointer transition-all group relative';
            item.onclick = () => sendGif(g.url, g.title);
            item.innerHTML = `
                <img src="${g.url}" alt="${escapeHtml(g.title)}" class="w-full h-20 object-cover group-hover:scale-105 transition-transform" loading="lazy">
                <div class="p-1 text-[10px] font-semibold text-slate-300 text-center truncate bg-slate-900/90">${escapeHtml(g.title)}</div>
            `;
            grid.appendChild(item);
        });
    }

    function toggleGifPicker() {
        const p = document.getElementById('popover-gif');
        const isHidden = p.classList.contains('hidden');
        p.classList.toggle('hidden');
        document.getElementById('popover-emoji').classList.add('hidden');
        if (isHidden) {
            initGifPicker();
            setTimeout(() => document.getElementById('gif-search-input')?.focus(), 50);
        }
    }

    async function sendGif(url, title) {
        if (!currentConversationId) return;
        document.getElementById('popover-gif').classList.add('hidden');

        try {
            const res = await fetch(`/api/conversations/${currentConversationId}/reply`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    content: title ? `[GIF: ${title}]` : '[GIF]',
                    attachment_url: url,
                    attachment_type: 'image',
                    sender_type: 'human_agent'
                })
            });
            const data = await res.json();
            if (res.ok) {
                window.moveConversationToTop(currentConversationId, title ? `[GIF: ${title}]` : '[GIF]', 'Just now');
                await loadConversationDetails(currentConversationId);
                Toast.fire({ icon: 'success', title: 'GIF sent to chat!', iconColor: '#10b981' });
            } else {
                Toast.fire({ icon: 'warning', title: data.message || 'Cannot send GIF', iconColor: '#f59e0b' });
            }
        } catch (err) {
            console.error(err);
            Toast.fire({ icon: 'error', title: 'Network error sending GIF', iconColor: '#f43f5e' });
        }
    }

    // ==========================================
    // 3. VOICE MESSAGE RECORDER (TELEGRAM STYLE)
    // ==========================================
    let mediaRecorder = null;
    let voiceChunks = [];
    let voiceTimerInterval = null;
    let voiceSeconds = 0;
    let voiceAudioStream = null;

    async function startVoiceRecording() {
        if (!currentConversationId) {
            Toast.fire({ icon: 'warning', title: 'Select a conversation first', iconColor: '#f59e0b' });
            return;
        }

        // Check if browser is blocked by Insecure Context (HTTP instead of HTTPS)
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            Swal.fire({
                title: '🎙️ মাইক্রোফোন চালু করার উপায়',
                html: `
                    <p class="text-xs text-slate-300 mb-2">ক্রোম ব্রাউজার লোকাল <code>.test</code> ডোমেইনে নিরাপত্তার জন্য মাইক্রোফোন অ্যাক্সেস ব্লক করে রাখে। এটি চালু করার সহজ ২টি উপায়:</p>
                    <div class="text-left text-xs bg-slate-900 border border-slate-800 rounded-xl p-3 my-2 text-slate-300 space-y-2 font-sans">
                        <div>
                            <p class="font-bold text-indigo-400">উপায় ১: Chrome Flag অন করা (স্থায়ী সমাধান)</p>
                            <p class="text-[11px] text-slate-400">১. ক্রোমে নতুন ট্যাব খুলে যান: <code class="text-amber-300 select-all">chrome://flags/#unsafely-treat-insecure-origin-as-secure</code></p>
                            <p class="text-[11px] text-slate-400">২. অপশনটি <b>Enabled</b> করুন এবং বক্সে লিখুন: <code class="text-amber-300 select-all">http://socialmate.test</code></p>
                            <p class="text-[11px] text-slate-400">৩. নিচে <b>Relaunch</b> বাটনে ক্লিক করুন।</p>
                        </div>
                        <div class="border-t border-slate-800 pt-2">
                            <p class="font-bold text-emerald-400">উপায় ২: রেকর্ড করা ফাইল আপলোড (📎)</p>
                            <p class="text-[11px] text-slate-400">ইনবক্সের নিচে 📎 বাটনে ক্লিক করে সরাসরি যেকোনো অডিও বা ভয়েস ফাইল পাঠিয়ে দিতে পারেন।</p>
                        </div>
                    </div>
                `,
                icon: 'info',
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'বুঝেছি'
            });
            return;
        }

        try {
            voiceAudioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            voiceChunks = [];
            voiceSeconds = 0;

            const mimeType = MediaRecorder.isTypeSupported('audio/webm;codecs=opus')
                ? 'audio/webm;codecs=opus'
                : (MediaRecorder.isTypeSupported('audio/ogg;codecs=opus') ? 'audio/ogg;codecs=opus' : '');

            mediaRecorder = mimeType ? new MediaRecorder(voiceAudioStream, { mimeType }) : new MediaRecorder(voiceAudioStream);

            mediaRecorder.ondataavailable = (e) => {
                if (e.data && e.data.size > 0) voiceChunks.push(e.data);
            };

            mediaRecorder.start(250);

            // Switch Composer UI to Recording Bar
            document.getElementById('composer-normal-box').classList.add('hidden');
            const recBox = document.getElementById('composer-recording-box');
            recBox.classList.remove('hidden');
            recBox.classList.add('flex');

            // Start timer
            const timerEl = document.getElementById('voice-record-timer');
            timerEl.innerText = '00:00';
            clearInterval(voiceTimerInterval);
            voiceTimerInterval = setInterval(() => {
                voiceSeconds++;
                const mins = String(Math.floor(voiceSeconds / 60)).padStart(2, '0');
                const secs = String(voiceSeconds % 60).padStart(2, '0');
                timerEl.innerText = `${mins}:${secs}`;
            }, 1000);

        } catch (err) {
            console.error(err);
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                Swal.fire({
                    title: '🎙️ মাইক্রোফোন পারমিশন ব্লকড',
                    html: `
                        <p class="text-sm text-slate-300">ব্রাউজারে মাইক্রোফোন পারমিশন ব্লক করা আছে। চালু করতে:</p>
                        <div class="text-left text-xs bg-slate-900 border border-slate-800 rounded-xl p-3 my-3 text-slate-300 space-y-1.5 font-sans">
                            <p>১. ব্রাউজারের অ্যাড্রেস বারের বাম পাশে (URL এর বামে) <b>Site Settings / Tune 🎚️ আইকনে</b> চাপ দিন।</p>
                            <p>২. <b>Microphone</b> অপশনটি <b>Allow</b> করুন।</p>
                            <p>৩. এরপর পেজটি রিফ্রেশ দিন।</p>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'বুঝেছি'
                });
            } else {
                Toast.fire({ icon: 'error', title: 'Microphone permission denied or unavailable.', iconColor: '#f43f5e' });
            }
        }
    }

    function cancelVoiceRecording() {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
        }
        if (voiceAudioStream) {
            voiceAudioStream.getTracks().forEach(t => t.stop());
            voiceAudioStream = null;
        }
        clearInterval(voiceTimerInterval);
        voiceChunks = [];

        // Restore Composer UI
        document.getElementById('composer-recording-box').classList.add('hidden');
        document.getElementById('composer-recording-box').classList.remove('flex');
        document.getElementById('composer-normal-box').classList.remove('hidden');
    }

    async function stopAndSendVoiceRecording() {
        if (!mediaRecorder || mediaRecorder.state === 'inactive') return;

        clearInterval(voiceTimerInterval);

        mediaRecorder.onstop = async () => {
            if (voiceAudioStream) {
                voiceAudioStream.getTracks().forEach(t => t.stop());
                voiceAudioStream = null;
            }

            const audioBlob = new Blob(voiceChunks, { type: mediaRecorder.mimeType || 'audio/ogg' });
            const ext = mediaRecorder.mimeType && mediaRecorder.mimeType.includes('webm') ? 'webm' : 'ogg';
            const audioFile = new File([audioBlob], `voice_note_${Date.now()}.${ext}`, { type: audioBlob.type });

            // Restore Composer UI
            document.getElementById('composer-recording-box').classList.add('hidden');
            document.getElementById('composer-recording-box').classList.remove('flex');
            document.getElementById('composer-normal-box').classList.remove('hidden');

            // Send to backend
            const formData = new FormData();
            formData.append('attachment', audioFile);
            formData.append('message_type', 'audio');
            formData.append('content', '🎙️ Voice Message');
            formData.append('sender_type', 'human_agent');

            try {
                Toast.fire({ icon: 'info', title: 'Sending voice message...', timer: 1500, iconColor: '#38bdf8' });
                const res = await fetch(`/api/conversations/${currentConversationId}/reply`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (res.ok) {
                    window.moveConversationToTop(currentConversationId, '🎙️ Voice Message', 'Just now');
                    await loadConversationDetails(currentConversationId);
                    Toast.fire({ icon: 'success', title: 'Voice note delivered!', iconColor: '#10b981' });
                } else {
                    Toast.fire({ icon: 'warning', title: data.message || 'Failed to send voice note', iconColor: '#f59e0b' });
                }
            } catch (err) {
                console.error(err);
                Toast.fire({ icon: 'error', title: 'Network error sending voice note', iconColor: '#f43f5e' });
            }
        };

        mediaRecorder.stop();
    }



    // Close popovers when clicking outside
    document.addEventListener('click', (e) => {
        const popEmoji = document.getElementById('popover-emoji');
        const popGif = document.getElementById('popover-gif');
        if (popEmoji && !popEmoji.classList.contains('hidden')) {
            if (!popEmoji.contains(e.target) && !e.target.closest('button[onclick*="toggleEmojiPicker"]')) {
                popEmoji.classList.add('hidden');
            }
        }
        if (popGif && !popGif.classList.contains('hidden')) {
            if (!popGif.contains(e.target) && !e.target.closest('button[onclick*="toggleGifPicker"]')) {
                popGif.classList.add('hidden');
            }
        }
    });

    async function sendReply() {
        const input = document.getElementById('reply-input');
        const text = input.value.trim();
        if ((!text && !selectedAttachmentFile) || !currentConversationId) return;

        const sendBtn = document.getElementById('btn-send-reply');
        sendBtn.disabled = true;

        try {
            let res;
            if (selectedAttachmentFile) {
                const formData = new FormData();
                if (text) formData.append('content', text);
                formData.append('sender_type', 'human_agent');
                formData.append('attachment', selectedAttachmentFile);

                res = await fetch(`/api/conversations/${currentConversationId}/reply`, {
                    method: 'POST',
                    body: formData
                });
            } else {
                res = await fetch(`/api/conversations/${currentConversationId}/reply`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ content: text, sender_type: 'human_agent' })
                });
            }

            const data = await res.json();
            if (res.ok) {
                input.value = '';
                clearAttachment();
                window.moveConversationToTop(currentConversationId, text || '[Attachment]', 'Just now');
                await loadConversationDetails(currentConversationId);
            } else {
                Toast.fire({
                    icon: 'warning',
                    title: data.message || 'Cannot send: Channel is disconnected/paused.',
                    iconColor: '#f59e0b'
                });
            }
        } catch (err) {
            console.error(err);
            Toast.fire({ icon: 'error', title: 'Network error sending reply.', iconColor: '#f43f5e' });
        } finally {
            sendBtn.disabled = false;
        }
    }

    document.getElementById('reply-input').addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendReply();
        }
    });

    // ==========================================
    // QUICK REPLIES (CANNED RESPONSES) SYSTEM
    // ==========================================
    let quickRepliesData = @json($quickReplies ?? []);

    function insertQuickReply(text) {
        const input = document.getElementById('reply-input');
        if (input) {
            input.value = text;
            input.focus();
        }
    }

    function insertQuickReplyAndClose(text) {
        insertQuickReply(text);
        closeQuickRepliesModal();
    }

    function renderQuickRepliesBar() {
        const container = document.getElementById('quick-replies-list');
        if (!container) return;

        const activeReplies = (quickRepliesData || []).filter(qr => qr.is_active !== false);

        if (activeReplies.length === 0) {
            container.innerHTML = '<span class="text-[11px] font-semibold text-slate-400 shrink-0 flex items-center gap-1">⚡ Quick Replies:</span><span class="text-[11px] text-slate-500 italic">No active replies. Click ⚙️ to turn ON!</span>';
            return;
        }

        let html = '<span class="text-[11px] font-semibold text-slate-400 shrink-0 flex items-center gap-1">⚡ Quick Replies:</span>';
        activeReplies.forEach(qr => {
            const safeMsg = escapeHtml(qr.message);
            const safeTitle = escapeHtml(qr.title);
            html += `
                <div class="inline-flex items-center rounded-md bg-slate-800/80 hover:bg-slate-700 border border-slate-700/60 group shrink-0 transition-colors">
                    <button type="button" onclick="insertQuickReply(${JSON.stringify(qr.message)})" class="px-2.5 py-1 text-slate-300 hover:text-white text-[11px] font-medium truncate max-w-[150px] sm:max-w-xs" title="${safeMsg}">
                        "${safeTitle}"
                    </button>
                    <button type="button" onclick="editQuickReplyModal(${qr.id})" class="px-1.5 py-1 text-slate-500 hover:text-amber-400 text-[10px] opacity-0 group-hover:opacity-100 transition-opacity border-l border-slate-700/40" title="Edit this reply">
                        ✏️
                    </button>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    function renderQuickRepliesModalList() {
        const container = document.getElementById('qr-modal-list');
        const countEl = document.getElementById('qr-count');
        if (!container) return;

        if (countEl) countEl.innerText = quickRepliesData.length;

        if (!Array.isArray(quickRepliesData) || quickRepliesData.length === 0) {
            container.innerHTML = '<div class="p-6 text-center text-xs text-slate-500 border border-dashed border-slate-800 rounded-xl">No quick replies created yet. Use the form above to create your first reply!</div>';
            return;
        }

        let html = '';
        quickRepliesData.forEach(qr => {
            const safeTitle = escapeHtml(qr.title);
            const safeMsg = escapeHtml(qr.message);
            const safeShortcut = qr.shortcut ? escapeHtml(qr.shortcut) : '';
            const isActive = qr.is_active !== false;
            html += `
                <div class="p-3.5 rounded-xl bg-slate-950 border ${isActive ? 'border-slate-800/80 hover:border-slate-700' : 'border-slate-900 opacity-60'} flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 transition-all">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h5 class="text-xs font-bold ${isActive ? 'text-white' : 'text-slate-400'}">${safeTitle}</h5>
                            ${safeShortcut ? `<span class="px-1.5 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-mono text-[10px] border border-indigo-500/30">${safeShortcut}</span>` : ''}
                            ${isActive ? '<span class="text-[9px] px-1.5 py-0.2 rounded bg-emerald-500/15 text-emerald-400 font-semibold border border-emerald-500/30">Active on Chat</span>' : '<span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-500 font-medium">Hidden</span>'}
                        </div>
                        <p class="text-[11px] text-slate-400 whitespace-pre-wrap line-clamp-2">${safeMsg}</p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                        <!-- Toggle Switch Button -->
                        <div class="flex items-center gap-1.5 bg-slate-900/80 px-2 py-1 rounded-lg border border-slate-800" title="Toggle to show/hide on chat bar">
                            <span class="text-[10px] font-semibold text-slate-400">Chat Bar:</span>
                            <button type="button" onclick="toggleQuickReplyStatus(${qr.id})" class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none ${isActive ? 'bg-emerald-500' : 'bg-slate-700'}">
                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out ${isActive ? 'translate-x-4' : 'translate-x-0'}"></span>
                            </button>
                            <span class="text-[10px] font-bold ${isActive ? 'text-emerald-400' : 'text-slate-500'} w-6">
                                ${isActive ? 'ON' : 'OFF'}
                            </span>
                        </div>

                        <!-- Edit & Delete Buttons -->
                        <div class="flex items-center gap-1">
                            <button type="button" onclick="editQuickReplyModal(${qr.id})" class="px-2.5 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 text-[11px] font-semibold border border-amber-500/20 transition-colors" title="Edit this reply">
                                ✏️ Edit
                            </button>
                            <button type="button" onclick="deleteQuickReply(${qr.id})" class="px-2 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-[11px] font-semibold border border-rose-500/20 transition-colors" title="Delete this reply">
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    async function toggleQuickReplyStatus(id) {
        const qr = quickRepliesData.find(item => item.id == id);
        if (!qr) return;

        const originalState = qr.is_active !== false;
        qr.is_active = !originalState; // optimistic update

        renderQuickRepliesBar();
        renderQuickRepliesModalList();

        try {
            const res = await fetch(`/tenant/quick-replies/${id}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                qr.is_active = data.data.is_active;
                renderQuickRepliesBar();
                renderQuickRepliesModalList();

                Toast.fire({
                    icon: qr.is_active ? 'success' : 'info',
                    title: `"${qr.title}" is now ${qr.is_active ? 'visible (ON)' : 'hidden (OFF)'} on chat bar!`,
                    iconColor: qr.is_active ? '#10b981' : '#94a3b8'
                });
            } else {
                qr.is_active = originalState; // revert on failure
                renderQuickRepliesBar();
                renderQuickRepliesModalList();
                Toast.fire({ icon: 'error', title: data.message || 'Cannot update status.', iconColor: '#f43f5e' });
            }
        } catch (e) {
            qr.is_active = originalState; // revert on error
            renderQuickRepliesBar();
            renderQuickRepliesModalList();
            Toast.fire({ icon: 'error', title: 'Network error updating status.', iconColor: '#f43f5e' });
        }
    }

    function openQuickRepliesModal() {
        renderQuickRepliesModalList();
        document.getElementById('modal-quick-replies')?.classList.remove('hidden');
    }

    function closeQuickRepliesModal() {
        document.getElementById('modal-quick-replies')?.classList.add('hidden');
        resetQuickReplyForm();
    }

    function resetQuickReplyForm() {
        document.getElementById('qr-input-id').value = '';
        document.getElementById('qr-input-title').value = '';
        document.getElementById('qr-input-message').value = '';
        document.getElementById('qr-input-shortcut').value = '';
        document.getElementById('qr-form-heading').innerHTML = '<span>➕</span> Add New Quick Reply';
        document.getElementById('qr-form-submit-btn').innerHTML = '<span>💾</span> Save Quick Reply';
        document.getElementById('qr-cancel-edit-btn').classList.add('hidden');
    }

    function editQuickReplyModal(id) {
        const qr = quickRepliesData.find(item => item.id == id);
        if (!qr) return;

        openQuickRepliesModal();

        document.getElementById('qr-input-id').value = qr.id;
        document.getElementById('qr-input-title').value = qr.title;
        document.getElementById('qr-input-message').value = qr.message;
        document.getElementById('qr-input-shortcut').value = qr.shortcut || '';
        document.getElementById('qr-form-heading').innerHTML = '<span>✏️</span> Edit Quick Reply';
        document.getElementById('qr-form-submit-btn').innerHTML = '<span>✓</span> Update Quick Reply';
        document.getElementById('qr-cancel-edit-btn').classList.remove('hidden');

        document.getElementById('qr-input-title').focus();
    }

    async function saveQuickReply(e) {
        e.preventDefault();
        const id = document.getElementById('qr-input-id').value;
        const title = document.getElementById('qr-input-title').value.trim();
        const message = document.getElementById('qr-input-message').value.trim();
        const shortcut = document.getElementById('qr-input-shortcut').value.trim();

        if (!title || !message) return;

        const submitBtn = document.getElementById('qr-form-submit-btn');
        submitBtn.disabled = true;

        try {
            const url = id ? `/tenant/quick-replies/${id}` : '/tenant/quick-replies';
            const method = id ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ title, message, shortcut })
            });

            const data = await res.json();
            if (data.success) {
                if (id) {
                    const idx = quickRepliesData.findIndex(item => item.id == id);
                    if (idx !== -1) quickRepliesData[idx] = data.data;
                } else {
                    quickRepliesData.push(data.data);
                }

                renderQuickRepliesBar();
                renderQuickRepliesModalList();
                resetQuickReplyForm();

                Toast.fire({
                    icon: 'success',
                    title: data.message || 'Quick reply saved successfully!',
                    iconColor: '#10b981'
                });
            } else {
                Toast.fire({
                    icon: 'error',
                    title: data.message || 'Failed to save quick reply.',
                    iconColor: '#f43f5e'
                });
            }
        } catch (err) {
            console.error(err);
            Toast.fire({ icon: 'error', title: 'Network error saving quick reply.', iconColor: '#f43f5e' });
        } finally {
            submitBtn.disabled = false;
        }
    }

    async function deleteQuickReply(id) {
        const qr = quickRepliesData.find(item => item.id == id);
        const title = qr ? qr.title : 'this reply';

        const result = await Swal.fire({
            title: 'Delete Quick Reply?',
            text: `Are you sure you want to delete "${title}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f43f5e',
            cancelButtonColor: '#334155',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            background: document.documentElement.classList.contains('light') ? '#ffffff' : '#0b1120',
            color: document.documentElement.classList.contains('light') ? '#0f172a' : '#f8fafc'
        });

        if (result.isConfirmed) {
            try {
                const res = await fetch(`/tenant/quick-replies/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    quickRepliesData = quickRepliesData.filter(item => item.id != id);
                    renderQuickRepliesBar();
                    renderQuickRepliesModalList();
                    Toast.fire({
                        icon: 'success',
                        title: 'Quick reply deleted.',
                        iconColor: '#10b981'
                    });
                }
            } catch (err) {
                console.error(err);
                Toast.fire({ icon: 'error', title: 'Network error deleting quick reply.', iconColor: '#f43f5e' });
            }
        }
    }

    async function setAiMode(mode) {
        if (!currentConversationId) return;
        try {
            await fetch(`/api/conversations/${currentConversationId}/ai-mode`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ai_mode: mode })
            });
            updateAiModeButtons(mode);
        } catch (err) {
            console.error(err);
        }
    }

    function updateAiModeButtons(mode) {
        ['active', 'human_takeover', 'paused'].forEach(m => {
            const btn = document.getElementById(`btn-ai-${m === 'human_takeover' ? 'human' : m}`);
            if (btn) {
                if (m === mode) {
                    btn.className = 'px-2.5 py-1 rounded text-[11px] font-medium transition-all bg-indigo-600 text-white';
                } else {
                    btn.className = 'px-2.5 py-1 rounded text-[11px] font-medium transition-all text-slate-400 hover:text-slate-200';
                }
            }
        });
    }

    let currentConversationStatus = 'open';

    async function toggleResolved() {
        if (!currentConversationId) return;
        const newStatus = currentConversationStatus === 'resolved' ? 'open' : 'resolved';
        try {
            await fetch(`/api/conversations/${currentConversationId}/status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: newStatus })
            });
            updateResolvedButton(newStatus);
            Toast.fire({ 
                icon: 'success', 
                title: newStatus === 'resolved' ? 'Conversation marked as Resolved!' : 'Conversation Re-opened!', 
                iconColor: '#10b981' 
            });
        } catch (err) {
            console.error(err);
        }
    }

    function updateResolvedButton(status) {
        currentConversationStatus = status;
        const btn = document.getElementById('btn-resolve-toggle');
        const icon = document.getElementById('resolve-icon');
        const text = document.getElementById('resolve-text');
        if (!btn) return;
        if (status === 'resolved') {
            btn.className = 'px-3 py-1.5 rounded-lg bg-indigo-500/15 hover:bg-indigo-500/25 border border-indigo-500/30 text-indigo-300 text-xs font-semibold transition-all flex items-center gap-1';
            if (icon) icon.innerText = '🔄';
            if (text) text.innerText = 'Reopen';
            btn.title = 'Conversation is resolved. Click to Reopen.';
        } else {
            btn.className = 'px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-semibold transition-all flex items-center gap-1';
            if (icon) icon.innerText = '✓';
            if (text) text.innerText = 'Resolved';
            btn.title = 'Click to mark conversation as Resolved.';
        }
    }

    async function simulateIncomingMessage() {
        const text = document.getElementById('sim-message-text').value.trim();
        if (!text) return;

        const customerName = document.getElementById('chat-header-name')?.innerText || 'Customer';
        const platform = document.getElementById('chat-header-platform')?.innerText || 'Social';

        try {
            const payload = { content: text };
            if (currentConversationId) {
                payload.conversation_id = currentConversationId;
            } else {
                payload.channel_id = {{ $channels->first()?->id ?? 1 }};
                payload.sender_id = 'fb_apex_user_1';
                payload.sender_name = 'Tanvir Ahmed';
            }

            const res = await fetch('/simulate-inbound', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            const targetConvId = data?.data?.conversation_id || currentConversationId;

            // Play the pleasant Ding Chime and trigger OS Desktop Push Notification
            if (typeof window.playDingChime === 'function') {
                window.playDingChime();
            }
            if (typeof window.showDesktopNotification === 'function') {
                window.showDesktopNotification(`💬 ${customerName} (${platform})`, text, targetConvId);
            }

            if (targetConvId) {
                if (targetConvId !== currentConversationId || document.hidden || !document.hasFocus()) {
                    window.markConversationUnread(targetConvId, text);
                } else {
                    window.moveConversationToTop(targetConvId, text, 'Just now');
                }
            }

            document.getElementById('sim-message-text').value = '';
            setTimeout(() => {
                if (targetConvId) loadConversationDetails(targetConvId);
            }, 300);
        } catch (err) {
            console.error(err);
        }
    }

    async function updateContactStage() {
        const stage = document.getElementById('crm-stage').value;
        const res = await fetch(`/api/conversations/${currentConversationId}`);
        const data = await res.json();
        if (data.contact_id) {
            await fetch(`/api/contacts/${data.contact_id}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ pipeline_stage: stage })
            });
        }
    }

    async function updateContactDeal() {
        const val = document.getElementById('crm-deal-value').value;
        const res = await fetch(`/api/conversations/${currentConversationId}`);
        const data = await res.json();
        if (data.contact_id) {
            await fetch(`/api/contacts/${data.contact_id}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ deal_value: val })
            });
        }
    }

    async function addTag() {
        const tagInput = document.getElementById('new-tag-input');
        const newTag = tagInput.value.trim();
        if (!newTag) return;

        const res = await fetch(`/api/conversations/${currentConversationId}`);
        const data = await res.json();
        if (data.contact) {
            const currentTags = data.contact.tags || [];
            if (!currentTags.includes(newTag)) {
                currentTags.push(newTag);
                await fetch(`/api/contacts/${data.contact.id}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tags: currentTags })
                });
                tagInput.value = '';
                loadConversationDetails(currentConversationId);
            }
        }
    }

    async function convertToErpLead() {
        if (!currentConversationId) {
            Toast.fire({ icon: 'warning', title: 'Please select a conversation first.', iconColor: '#f59e0b' });
            return;
        }

        const btn = event?.currentTarget;
        const originalText = btn ? btn.innerHTML : '';
        if (btn) {
            btn.innerHTML = '<span>⏳ Converting...</span>';
            btn.disabled = true;
        }

        try {
            const res = await fetch(`/tenant/crm/convert-social/${currentConversationId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                Toast.fire({ icon: 'success', title: `🎉 ${data.message} - Lead tracked in ERP!`, iconColor: '#10b981' });
            } else {
                Toast.fire({ icon: 'error', title: 'Failed to convert: ' + (data.message || 'Unknown error'), iconColor: '#f43f5e' });
            }
        } catch (err) {
            console.error(err);
            Toast.fire({ icon: 'error', title: 'Error converting conversation to ERP lead.', iconColor: '#f43f5e' });
        } finally {
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    }
</script>
@endsection
