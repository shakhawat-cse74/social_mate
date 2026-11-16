<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialMate CRM — Omnichannel Inbox & AI Agent Hub</title>
    <meta name="description" content="Unified social media connector, omnichannel live inbox, CRM pipeline, and intelligent AI auto-reply agent.">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.6); }
        ::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.3); border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.6); }
        .glass-panel {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glow-indigo {
            box-shadow: 0 0 25px -5px rgba(99, 102, 241, 0.35);
        }
    </style>
</head>
<body class="h-full flex flex-col bg-[#070b14] overflow-hidden select-none">

    <!-- Top Navigation Bar -->
    <header class="h-16 border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-lg flex items-center justify-between px-6 z-20 shrink-0">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-violet-600 to-pink-500 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base font-bold tracking-tight bg-gradient-to-r from-white via-slate-200 to-indigo-200 bg-clip-text text-transparent">SocialMate CRM</h1>
                    <span class="px-2 py-0.5 text-[10px] font-semibold tracking-wider uppercase rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Modular Add-on Hub</span>
                </div>
                <p class="text-xs text-slate-400">Social Media Aggregator & AI Agent Engine</p>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <nav class="flex items-center bg-slate-950/60 p-1 rounded-xl border border-slate-800">
            <button onclick="switchTab('inbox')" id="tab-btn-inbox" class="tab-btn active px-4 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-2 bg-indigo-600 text-white shadow-sm">
                <span>💬</span> Unified Inbox
            </button>
            <button onclick="switchTab('pipeline')" id="tab-btn-pipeline" class="tab-btn px-4 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-2 text-slate-400 hover:text-slate-200">
                <span>📊</span> CRM Pipeline
            </button>
            <button onclick="switchTab('channels')" id="tab-btn-channels" class="tab-btn px-4 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-2 text-slate-400 hover:text-slate-200">
                <span>🌐</span> Channels & Webhooks
            </button>
            <button onclick="switchTab('ai')" id="tab-btn-ai" class="tab-btn px-4 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-2 text-slate-400 hover:text-slate-200">
                <span>🤖</span> AI Agent Studio
            </button>
            <button onclick="switchTab('addon')" id="tab-btn-addon" class="tab-btn px-4 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-2 text-slate-400 hover:text-slate-200">
                <span>🔌</span> External Add-on Docs
            </button>
        </nav>

        <!-- Right Quick Status -->
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>AI Agent: Online</span>
            </div>
            <div class="text-xs text-slate-400 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                <span>{{ $stats['active_channels'] }} Channels Connected</span>
            </div>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="flex-1 flex overflow-hidden relative">

        <!-- ========================================== -->
        <!-- TAB 1: UNIFIED OMNICHANNEL INBOX -->
        <!-- ========================================== -->
        <div id="view-inbox" class="tab-view flex-1 flex overflow-hidden">
            <!-- Left Conversations Sidebar -->
            <aside class="w-80 border-r border-slate-800/80 bg-slate-900/40 flex flex-col shrink-0">
                <!-- Search & Channel Filter -->
                <div class="p-3 border-b border-slate-800/80 space-y-2.5">
                    <div class="relative">
                        <input type="text" id="conv-search" oninput="filterConversations()" placeholder="Search contacts or messages..." class="w-full bg-slate-950/70 border border-slate-800 rounded-lg px-3 py-1.5 pl-8 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                        <svg class="w-3.5 h-3.5 text-slate-500 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    </div>

                    <!-- Channel Filter Chips -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-[11px]" id="channel-filter-chips">
                        <button onclick="setPlatformFilter('all')" class="platform-chip active px-2.5 py-1 rounded-md bg-indigo-600 text-white font-medium shrink-0">All ({{ count($conversations) }})</button>
                        <button onclick="setPlatformFilter('facebook')" class="platform-chip px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 hover:bg-slate-700 shrink-0 flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span> FB</button>
                        <button onclick="setPlatformFilter('whatsapp')" class="platform-chip px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 hover:bg-slate-700 shrink-0 flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> WA</button>
                        <button onclick="setPlatformFilter('telegram')" class="platform-chip px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 hover:bg-slate-700 shrink-0 flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-sky-500"></span> TG</button>
                        <button onclick="setPlatformFilter('instagram')" class="platform-chip px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 hover:bg-slate-700 shrink-0 flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-pink-500"></span> IG</button>
                        <button onclick="setPlatformFilter('webchat')" class="platform-chip px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 hover:bg-slate-700 shrink-0 flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-purple-500"></span> Web</button>
                    </div>
                </div>

                <!-- Conversation List -->
                <div class="flex-1 overflow-y-auto divide-y divide-slate-800/40" id="conversation-list">
                    @forelse($conversations as $conv)
                        <div onclick="selectConversation({{ $conv->id }})" id="conv-item-{{ $conv->id }}" data-platform="{{ $conv->channel->platform ?? 'webchat' }}" class="conv-item p-3 hover:bg-slate-800/40 cursor-pointer transition-colors relative flex items-start gap-3 {{ $loop->first ? 'bg-indigo-950/30 border-l-2 border-indigo-500' : '' }}">
                            <div class="relative shrink-0">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-slate-700 to-slate-800 flex items-center justify-center font-bold text-slate-200 text-sm">
                                    {{ strtoupper(substr($conv->contact->name ?? 'U', 0, 1)) }}
                                </div>
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
                                    <h4 class="text-xs font-semibold text-slate-200 truncate">{{ $conv->contact->name ?? 'Guest User' }}</h4>
                                    <span class="text-[10px] text-slate-500">{{ $conv->last_message_at ? $conv->last_message_at->diffForHumans(null, true) : 'New' }}</span>
                                </div>
                                <p class="text-[11px] text-slate-400 truncate mt-0.5">
                                    {{ $conv->latestMessage->content ?? 'No messages yet' }}
                                </p>
                                <div class="flex items-center gap-1.5 mt-1.5">
                                    <span class="text-[9px] px-1.5 py-0.2 rounded font-medium 
                                        @if($conv->ai_mode === 'active') bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                                        @elseif($conv->ai_mode === 'human_takeover') bg-amber-500/20 text-amber-300 border border-amber-500/30
                                        @else bg-slate-800 text-slate-400 @endif">
                                        @if($conv->ai_mode === 'active') 🤖 AI Active
                                        @elseif($conv->ai_mode === 'human_takeover') 👤 Human Mode
                                        @else ⏸️ Paused @endif
                                    </span>
                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-400">
                                        {{ ucfirst($conv->contact->pipeline_stage ?? 'new') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-500">
                            No conversations found.
                        </div>
                    @endforelse
                </div>
            </aside>

            <!-- Center Chat Workspace -->
            <section class="flex-1 flex flex-col bg-slate-950/60 overflow-hidden relative">
                <!-- Chat Header -->
                <div class="h-16 border-b border-slate-800/80 px-6 flex items-center justify-between bg-slate-900/30">
                    <div class="flex items-center gap-3">
                        <div id="chat-header-avatar" class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center font-bold text-white text-xs">
                            {{ strtoupper(substr($conversations->first()?->contact?->name ?? 'T', 0, 1)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 id="chat-header-name" class="text-sm font-bold text-white">{{ $conversations->first()?->contact?->name ?? 'Select a conversation' }}</h3>
                                <span id="chat-header-platform" class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                    {{ ucfirst($conversations->first()?->channel?->platform ?? 'Facebook') }}
                                </span>
                            </div>
                            <p id="chat-header-sender-id" class="text-[11px] text-slate-400">{{ $conversations->first()?->contact?->platform_sender_id ?? '' }}</p>
                        </div>
                    </div>

                    <!-- Action Controls: AI Switch & Status -->
                    <div class="flex items-center gap-2.5">
                        <div class="flex items-center bg-slate-900 border border-slate-800 rounded-lg p-0.5 text-xs">
                            <button onclick="setAiMode('active')" id="btn-ai-active" class="px-2.5 py-1 rounded text-[11px] font-medium transition-all bg-indigo-600 text-white">🤖 AI Auto-Reply</button>
                            <button onclick="setAiMode('human_takeover')" id="btn-ai-human" class="px-2.5 py-1 rounded text-[11px] font-medium transition-all text-slate-400 hover:text-slate-200">👤 Human Takeover</button>
                            <button onclick="setAiMode('paused')" id="btn-ai-paused" class="px-2.5 py-1 rounded text-[11px] font-medium transition-all text-slate-400 hover:text-slate-200">⏸️ Mute</button>
                        </div>
                        <button onclick="markResolved()" class="px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-semibold transition-all">
                            ✓ Mark Resolved
                        </button>
                    </div>
                </div>

                <!-- Messages Stream -->
                <div id="messages-container" class="flex-1 overflow-y-auto p-6 space-y-4">
                    <!-- Dynamic Messages will load here via JS -->
                </div>

                <!-- AI Co-Pilot Recommendation Bar -->
                <div class="px-6 py-2 bg-slate-900/60 border-t border-slate-800/80 flex items-center gap-2 overflow-x-auto">
                    <span class="text-[11px] font-medium text-slate-400 shrink-0 flex items-center gap-1">✨ AI Co-pilot:</span>
                    <button onclick="insertQuickReply('Thank you for reaching out! Our Growth plan is $79/mo and supports up to 10 social channels.')" class="px-2.5 py-1 rounded-md bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-[11px] shrink-0 border border-slate-700/60 transition-colors">
                        "Send Pricing Info"
                    </button>
                    <button onclick="insertQuickReply('I would be delighted to schedule a live walkthrough demo for you! What time works best?')" class="px-2.5 py-1 rounded-md bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-[11px] shrink-0 border border-slate-700/60 transition-colors">
                        "Schedule Demo"
                    </button>
                    <button onclick="insertQuickReply('Our team is checking your request and will provide an update within a few minutes.')" class="px-2.5 py-1 rounded-md bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-[11px] shrink-0 border border-slate-700/60 transition-colors">
                        "Team Checking Request"
                    </button>
                </div>

                <!-- Reply Composer -->
                <div class="p-4 bg-slate-900/90 border-t border-slate-800 flex items-end gap-3">
                    <div class="flex-1 bg-slate-950/80 rounded-xl border border-slate-800 focus-within:border-indigo-500 transition-colors p-2">
                        <textarea id="reply-input" rows="2" placeholder="Write your reply to customer... (Press Enter to send, Shift+Enter for new line)" class="w-full bg-transparent text-xs text-slate-100 placeholder-slate-500 focus:outline-none resize-none"></textarea>
                    </div>
                    <button onclick="sendReply()" class="h-10 px-5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition-all flex items-center gap-1.5 shadow-lg shadow-indigo-600/30">
                        <span>Send Reply</span>
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </section>

            <!-- Right Lead Details & Simulator Sidebar -->
            <aside class="w-80 border-l border-slate-800/80 bg-slate-900/40 flex flex-col shrink-0 overflow-y-auto">
                <!-- Contact Profile -->
                <div class="p-5 border-b border-slate-800/80 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white text-xl mx-auto shadow-md shadow-indigo-500/20" id="crm-avatar">
                        T
                    </div>
                    <h4 class="text-sm font-bold text-white mt-2" id="crm-name">Tanvir Ahmed</h4>
                    <p class="text-xs text-slate-400" id="crm-phone">+8801711223344</p>
                    <p class="text-xs text-slate-400" id="crm-email">tanvir@gmail.com</p>
                </div>

                <!-- CRM Lead Management Form -->
                <div class="p-5 space-y-4 border-b border-slate-800/80">
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
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Estimated Deal Value ($)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1.5 text-slate-500 text-xs">$</span>
                            <input type="number" id="crm-deal-value" onblur="updateContactDeal()" class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-7 pr-3 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-indigo-500" value="79.00">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Tags</label>
                        <div class="flex flex-wrap gap-1.5" id="crm-tags-list">
                            <span class="text-[10px] px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">hot_lead</span>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">facebook</span>
                        </div>
                        <div class="flex gap-1.5 mt-2">
                            <input type="text" id="new-tag-input" placeholder="Add tag..." class="flex-1 bg-slate-950 border border-slate-800 rounded px-2 py-1 text-xs text-slate-200">
                            <button onclick="addTag()" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-xs text-white rounded">+</button>
                        </div>
                    </div>
                </div>

                <!-- Inbound Message Simulator (Interactive Testing) -->
                <div class="p-5 bg-indigo-950/20">
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="text-xs">⚡</span>
                        <h5 class="text-xs font-bold text-indigo-300 uppercase tracking-wider">Live Simulator</h5>
                    </div>
                    <p class="text-[11px] text-slate-400 mb-3">Simulate an incoming message from the customer to see how the AI and Webhooks respond instantly:</p>

                    <div class="space-y-2">
                        <textarea id="sim-message-text" rows="2" placeholder="e.g. 'Can I speak with a human agent?' or 'What is your price?'" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 resize-none"></textarea>
                        <button onclick="simulateIncomingMessage()" class="w-full py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs rounded-lg transition-all shadow-md">
                            Simulate Customer Message
                        </button>
                    </div>
                </div>
            </aside>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: CRM PIPELINE (KANBAN BOARD) -->
        <!-- ========================================== -->
        <div id="view-pipeline" class="tab-view flex-1 overflow-x-auto p-6 hidden">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-bold text-white">CRM Deals & Pipeline</h2>
                    <p class="text-xs text-slate-400">Track and advance social media leads across conversion stages</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-400">Total Pipeline Value:</span>
                    <span class="text-sm font-bold text-emerald-400">${{ number_format($stats['total_deal_value'], 2) }}</span>
                </div>
            </div>

            <div class="grid grid-cols-5 gap-4 min-w-[1000px] h-[calc(100vh-180px)]">
                <!-- Stage: New -->
                <div class="bg-slate-900/50 border border-slate-800 rounded-xl p-3 flex flex-col">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-xs font-bold text-slate-300">1. New Leads</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-400" id="count-stage-new">1</span>
                    </div>
                    <div class="flex-1 overflow-y-auto space-y-3 pt-3" id="kanban-stage-new"></div>
                </div>

                <!-- Stage: Contacted -->
                <div class="bg-slate-900/50 border border-slate-800 rounded-xl p-3 flex flex-col">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-xs font-bold text-blue-400">2. Contacted</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300" id="count-stage-contacted">0</span>
                    </div>
                    <div class="flex-1 overflow-y-auto space-y-3 pt-3" id="kanban-stage-contacted"></div>
                </div>

                <!-- Stage: Interested -->
                <div class="bg-slate-900/50 border border-slate-800 rounded-xl p-3 flex flex-col">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-xs font-bold text-purple-400">3. Interested / Qualified</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300" id="count-stage-interested">1</span>
                    </div>
                    <div class="flex-1 overflow-y-auto space-y-3 pt-3" id="kanban-stage-interested"></div>
                </div>

                <!-- Stage: Won -->
                <div class="bg-slate-900/50 border border-slate-800 rounded-xl p-3 flex flex-col">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-xs font-bold text-emerald-400">4. Deal Won 🎉</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300" id="count-stage-won">1</span>
                    </div>
                    <div class="flex-1 overflow-y-auto space-y-3 pt-3" id="kanban-stage-won"></div>
                </div>

                <!-- Stage: Lost -->
                <div class="bg-slate-900/50 border border-slate-800 rounded-xl p-3 flex flex-col">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-xs font-bold text-rose-400">5. Closed Lost</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-300" id="count-stage-lost">0</span>
                    </div>
                    <div class="flex-1 overflow-y-auto space-y-3 pt-3" id="kanban-stage-lost"></div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: CHANNELS & WEBHOOKS -->
        <!-- ========================================== -->
        <div id="view-channels" class="tab-view flex-1 overflow-y-auto p-8 hidden max-w-5xl mx-auto w-full">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-xl font-bold text-white">Social Media Channels & Webhooks</h2>
                    <p class="text-xs text-slate-400">Configure Meta Graph API, Telegram Bots, and WhatsApp Cloud Webhooks</p>
                </div>
                <button onclick="alert('In production, click here to authenticate with Facebook Login OAuth!')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-lg text-xs font-semibold text-white shadow-lg transition-all">
                    + Connect New Channel
                </button>
            </div>

            <div class="grid grid-cols-2 gap-6">
                @foreach($channels as $ch)
                    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 relative overflow-hidden">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-lg text-white
                                    @if($ch->platform === 'facebook') bg-blue-600
                                    @elseif($ch->platform === 'whatsapp') bg-emerald-600
                                    @elseif($ch->platform === 'telegram') bg-sky-500
                                    @elseif($ch->platform === 'instagram') bg-gradient-to-tr from-yellow-500 via-pink-600 to-purple-600
                                    @else bg-purple-600 @endif">
                                    @if($ch->platform === 'facebook') FB
                                    @elseif($ch->platform === 'whatsapp') WA
                                    @elseif($ch->platform === 'telegram') TG
                                    @elseif($ch->platform === 'instagram') IG
                                    @else WEB @endif
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white">{{ $ch->name }}</h3>
                                    <span class="text-xs text-slate-400">{{ ucfirst($ch->platform) }} Connector</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $ch->is_active ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400' }}">
                                {{ $ch->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <!-- Webhook Details -->
                        <div class="mt-4 p-3 bg-slate-950 rounded-xl border border-slate-800/80 space-y-2">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-400">Webhook URL:</span>
                                <code class="text-indigo-300 font-mono text-[10px] select-all">/api/webhooks/{{ $ch->platform === 'facebook' ? 'meta' : $ch->platform }}/{{ $ch->id }}</code>
                            </div>
                            @if($ch->verify_token)
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-slate-400">Verify Token:</span>
                                    <code class="text-slate-300 font-mono text-[10px]">{{ $ch->verify_token }}</code>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 flex items-center justify-between pt-3 border-t border-slate-800/80 text-xs">
                            <span class="text-slate-400">AI Auto-Reply:</span>
                            <span class="font-semibold {{ $ch->ai_enabled ? 'text-indigo-400' : 'text-slate-500' }}">
                                {{ $ch->ai_enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 4: AI AGENT STUDIO -->
        <!-- ========================================== -->
        <div id="view-ai" class="tab-view flex-1 overflow-y-auto p-8 hidden max-w-4xl mx-auto w-full">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-white">AI Agent Studio & Knowledge Base</h2>
                <p class="text-xs text-slate-400">Train and customize your intelligent customer support bot with business rules and FAQs</p>
            </div>

            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-2">AI Provider</label>
                        <select id="ai-provider" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-indigo-500">
                            <option value="gemini" {{ ($aiConfig->provider ?? '') === 'gemini' ? 'selected' : '' }}>Google Gemini (Recommended - Ultra Fast)</option>
                            <option value="openai" {{ ($aiConfig->provider ?? '') === 'openai' ? 'selected' : '' }}>OpenAI GPT-4o-mini</option>
                            <option value="mock" {{ ($aiConfig->provider ?? '') === 'mock' ? 'selected' : '' }}>Built-in Smart Local Engine (No API key needed)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-2">API Key (Optional for Built-in)</label>
                        <input type="password" id="ai-api-key" placeholder="Enter Gemini or OpenAI API Key..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2">System Instructions / Persona</label>
                    <textarea id="ai-system-prompt" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono">{{ $aiConfig->system_prompt ?? '' }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2">Business Knowledge Base (FAQs, Products, Refund Policy, Pricing)</label>
                    <textarea id="ai-knowledge-base" rows="6" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono">{{ $aiConfig->knowledge_base ?? '' }}</textarea>
                    <p class="text-[11px] text-slate-500 mt-1">The AI will use this knowledge base to answer customer questions automatically on Facebook, WhatsApp, Telegram, and your web store.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2">Human Handover Trigger Keywords (Comma separated)</label>
                    <input type="text" id="ai-handover-keywords" value="{{ implode(', ', $aiConfig->handover_keywords ?? ['human', 'agent', 'support', 'manush', 'kotha bolbo']) }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-800">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="ai-auto-reply" {{ ($aiConfig->auto_reply_enabled ?? true) ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-0 w-4 h-4 bg-slate-950 border-slate-800">
                        <label for="ai-auto-reply" class="text-xs text-slate-300">Enable Automated AI Replies for new inbound messages</label>
                    </div>
                    <button onclick="saveAiConfig()" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 rounded-xl text-xs font-bold text-white shadow-lg shadow-indigo-600/30 transition-all">
                        Save AI Configuration
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 5: EXTERNAL ADD-ON & SDK DOCS -->
        <!-- ========================================== -->
        <div id="view-addon" class="tab-view flex-1 overflow-y-auto p-8 hidden max-w-4xl mx-auto w-full space-y-8">
            <div>
                <h2 class="text-xl font-bold text-white">Connecting SocialMate as an Add-on in other Projects</h2>
                <p class="text-xs text-slate-400">Seamlessly plug this CRM, Inbox, and AI into WooCommerce, Laravel apps, or React websites</p>
            </div>

            <!-- Integration Option 1: Floating Webchat Widget -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xs">1</span>
                    <h3 class="text-sm font-bold text-white">Embeddable Live Chat Widget (Any Website)</h3>
                </div>
                <p class="text-xs text-slate-400 mb-3">Copy and paste this single script tag right before the closing <code>&lt;/body&gt;</code> tag on any website:</p>

                <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 relative font-mono text-xs text-indigo-300 overflow-x-auto">
                    <code>&lt;script src="{{ url('/widget.js') }}" data-channel="5"&gt;&lt;/script&gt;</code>
                </div>
            </div>

            <!-- Integration Option 2: Webhooks (Bi-directional Event Dispatch) -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-6 h-6 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold text-xs">2</span>
                    <h3 class="text-sm font-bold text-white">Outbound Webhooks (Notify External Apps)</h3>
                </div>
                <p class="text-xs text-slate-400 mb-3">Whenever a message arrives or a lead converts, SocialMate fires an HTTP POST webhook to your app with <code>X-Connector-Signature</code>.</p>

                <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-2 text-xs">
                    <div class="text-slate-300 font-semibold">Active Subscribed Endpoints:</div>
                    @forelse($webhooks as $wh)
                        <div class="flex items-center justify-between p-2 rounded bg-slate-900 border border-slate-800">
                            <div>
                                <span class="font-bold text-white">{{ $wh->name }}</span>
                                <p class="text-[11px] text-indigo-300 font-mono">{{ $wh->url }}</p>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300">Active</span>
                        </div>
                    @empty
                        <div class="text-slate-500 text-xs">No external webhooks configured yet.</div>
                    @endforelse
                </div>
            </div>

            <!-- Integration Option 3: REST API Endpoints -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">3</span>
                    <h3 class="text-sm font-bold text-white">REST API Endpoints for External Projects</h3>
                </div>
                <p class="text-xs text-slate-400 mb-3">Use these endpoints with your API Key to create leads, fetch inbox threads, or send messages from your project:</p>

                <div class="space-y-2 font-mono text-xs">
                    <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800 flex items-center justify-between">
                        <span class="text-emerald-400 font-bold">GET</span>
                        <span class="text-slate-300">/api/conversations</span>
                        <span class="text-slate-500 text-[11px]">List all conversation threads</span>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800 flex items-center justify-between">
                        <span class="text-indigo-400 font-bold">POST</span>
                        <span class="text-slate-300">/api/conversations/{id}/reply</span>
                        <span class="text-slate-500 text-[11px]">Send reply via FB/WhatsApp/TG</span>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800 flex items-center justify-between">
                        <span class="text-emerald-400 font-bold">GET</span>
                        <span class="text-slate-300">/api/contacts</span>
                        <span class="text-slate-500 text-[11px]">Fetch CRM Leads & Deal stages</span>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800 flex items-center justify-between">
                        <span class="text-indigo-400 font-bold">POST</span>
                        <span class="text-slate-300">/api/contacts</span>
                        <span class="text-slate-500 text-[11px]">Sync customer from external store</span>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Built-in Floating Webchat Demo -->
    <script src="/widget.js"></script>

    <!-- Client-side Controller & Real-time State Management -->
    <script>
        let currentConversationId = {{ $conversations->first()?->id ?? 'null' }};
        let activePlatformFilter = 'all';

        // Initial Load
        document.addEventListener('DOMContentLoaded', () => {
            if (currentConversationId) {
                loadConversationDetails(currentConversationId);
            }
            loadKanbanBoard();
        });

        // Tab Navigation
        function switchTab(tab) {
            document.querySelectorAll('.tab-view').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('bg-indigo-600', 'text-white');
                el.classList.add('text-slate-400');
            });

            document.getElementById(`view-${tab}`).classList.remove('hidden');
            const btn = document.getElementById(`tab-btn-${tab}`);
            btn.classList.add('bg-indigo-600', 'text-white');
            btn.classList.remove('text-slate-400');

            if (tab === 'pipeline') {
                loadKanbanBoard();
            }
        }

        // Platform Filter Chips
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

                if (matchPlatform && matchText) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Select Conversation
        async function selectConversation(id) {
            currentConversationId = id;

            document.querySelectorAll('.conv-item').forEach(el => {
                el.classList.remove('bg-indigo-950/30', 'border-l-2', 'border-indigo-500');
            });
            const selectedItem = document.getElementById(`conv-item-${id}`);
            if (selectedItem) {
                selectedItem.classList.add('bg-indigo-950/30', 'border-l-2', 'border-indigo-500');
            }

            await loadConversationDetails(id);
        }

        async function loadConversationDetails(id) {
            try {
                const res = await fetch(`/api/conversations/${id}`);
                const data = await res.json();

                // Update Header
                document.getElementById('chat-header-name').innerText = data.contact?.name || 'Customer';
                document.getElementById('chat-header-avatar').innerText = (data.contact?.name || 'C').charAt(0).toUpperCase();
                document.getElementById('chat-header-platform').innerText = (data.channel?.platform || 'Social').toUpperCase();
                document.getElementById('chat-header-sender-id').innerText = data.contact?.platform_sender_id || '';

                // Update AI Buttons
                updateAiModeButtons(data.ai_mode);

                // Update CRM Sidebar
                document.getElementById('crm-name').innerText = data.contact?.name || 'Customer';
                document.getElementById('crm-avatar').innerText = (data.contact?.name || 'C').charAt(0).toUpperCase();
                document.getElementById('crm-phone').innerText = data.contact?.phone || 'No phone recorded';
                document.getElementById('crm-email').innerText = data.contact?.email || 'No email recorded';
                document.getElementById('crm-stage').value = data.contact?.pipeline_stage || 'new';
                document.getElementById('crm-deal-value').value = data.contact?.deal_value || 0;

                // Render Tags
                const tagsList = document.getElementById('crm-tags-list');
                tagsList.innerHTML = '';
                (data.contact?.tags || []).forEach(tag => {
                    const pill = document.createElement('span');
                    pill.className = 'text-[10px] px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30';
                    pill.innerText = tag;
                    tagsList.appendChild(pill);
                });

                // Render Messages
                renderMessages(data.messages || []);
            } catch (err) {
                console.error(err);
            }
        }

        function renderMessages(messages) {
            const container = document.getElementById('messages-container');
            container.innerHTML = '';

            messages.forEach(msg => {
                const isInbound = msg.direction === 'inbound';
                const isAi = msg.sender_type === 'ai_agent';
                const isSystem = msg.sender_type === 'system';

                const wrapper = document.createElement('div');
                wrapper.className = `flex ${isInbound ? 'justify-start' : 'justify-end'}`;

                let badge = '';
                if (isAi) {
                    badge = `<span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.2 rounded bg-indigo-400/20 text-indigo-300 border border-indigo-400/30 mb-1">🤖 AI Agent</span>`;
                } else if (isSystem) {
                    badge = `<span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.2 rounded bg-amber-400/20 text-amber-300 mb-1">⚠️ System Notification</span>`;
                }

                wrapper.innerHTML = `
                    <div class="max-w-[70%]">
                        ${badge ? `<div>${badge}</div>` : ''}
                        <div class="p-3.5 rounded-2xl text-xs leading-relaxed shadow-sm ${
                            isInbound 
                                ? 'bg-slate-900 border border-slate-800 text-slate-100 rounded-bl-sm' 
                                : (isAi ? 'bg-indigo-950/80 border border-indigo-500/40 text-indigo-100 rounded-br-sm' : 'bg-indigo-600 text-white rounded-br-sm')
                        }">
                            ${msg.content}
                        </div>
                        <div class="text-[10px] text-slate-500 mt-1 ${isInbound ? 'text-left' : 'text-right'}">
                            ${new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} • ${msg.status}
                        </div>
                    </div>
                `;

                container.appendChild(wrapper);
            });

            container.scrollTop = container.scrollHeight;
        }

        // Send Outbound Reply
        async function sendReply() {
            const input = document.getElementById('reply-input');
            const text = input.value.trim();
            if (!text || !currentConversationId) return;

            input.value = '';

            try {
                const res = await fetch(`/api/conversations/${currentConversationId}/reply`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ content: text, sender_type: 'human_agent' })
                });

                if (res.ok) {
                    await loadConversationDetails(currentConversationId);
                }
            } catch (err) {
                console.error(err);
            }
        }

        // Enter key to send reply
        document.getElementById('reply-input').addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendReply();
            }
        });

        function insertQuickReply(text) {
            const input = document.getElementById('reply-input');
            input.value = text;
            input.focus();
        }

        // Toggle AI Mode
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

        async function markResolved() {
            if (!currentConversationId) return;
            await fetch(`/api/conversations/${currentConversationId}/status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: 'resolved' })
            });
            alert('Conversation marked as resolved!');
        }

        // Simulator: Test Customer Message
        async function simulateIncomingMessage() {
            const text = document.getElementById('sim-message-text').value.trim();
            if (!text) {
                alert('Please type a message to simulate.');
                return;
            }

            try {
                const res = await fetch('/simulate-inbound', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        channel_id: 1, // Facebook page
                        sender_id: 'fb_user_4492',
                        sender_name: 'Tanvir Ahmed',
                        content: text
                    })
                });

                document.getElementById('sim-message-text').value = '';
                setTimeout(() => {
                    loadConversationDetails(currentConversationId);
                }, 400);
            } catch (err) {
                console.error(err);
            }
        }

        // CRM Updates
        async function updateContactStage() {
            const stage = document.getElementById('crm-stage').value;
            // Find current contact
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

        // Kanban Board Loader
        async function loadKanbanBoard() {
            try {
                const res = await fetch('/api/contacts?per_page=50');
                const result = await res.json();
                const contacts = result.data || [];

                const stages = ['new', 'contacted', 'interested', 'won', 'lost'];
                stages.forEach(s => {
                    const col = document.getElementById(`kanban-stage-${s}`);
                    if (col) col.innerHTML = '';
                    const countEl = document.getElementById(`count-stage-${s}`);
                    if (countEl) countEl.innerText = '0';
                });

                const counts = { new: 0, contacted: 0, interested: 0, won: 0, lost: 0 };

                contacts.forEach(c => {
                    const stage = c.pipeline_stage || 'new';
                    if (counts[stage] !== undefined) counts[stage]++;

                    const col = document.getElementById(`kanban-stage-${stage}`);
                    if (col) {
                        const card = document.createElement('div');
                        card.className = 'p-3 bg-slate-950/80 rounded-xl border border-slate-800/80 shadow-sm space-y-2';
                        card.innerHTML = `
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-white">${c.name}</span>
                                <span class="text-[10px] text-emerald-400 font-bold">$${c.deal_value || 0}</span>
                            </div>
                            <div class="text-[11px] text-slate-400">${c.platform || 'web'} • ${c.phone || c.email || 'No contact info'}</div>
                            <div class="flex flex-wrap gap-1">
                                ${(c.tags || []).map(t => `<span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-300">${t}</span>`).join('')}
                            </div>
                        `;
                        col.appendChild(card);
                    }
                });

                stages.forEach(s => {
                    const countEl = document.getElementById(`count-stage-${s}`);
                    if (countEl) countEl.innerText = counts[s];
                });
            } catch (err) {
                console.error(err);
            }
        }

        // Save AI Configuration
        async function saveAiConfig() {
            const provider = document.getElementById('ai-provider').value;
            const apiKey = document.getElementById('ai-api-key').value;
            const systemPrompt = document.getElementById('ai-system-prompt').value;
            const knowledgeBase = document.getElementById('ai-knowledge-base').value;
            const handoverStr = document.getElementById('ai-handover-keywords').value;
            const autoReply = document.getElementById('ai-auto-reply').checked;

            const handoverKeywords = handoverStr.split(',').map(s => s.trim()).filter(Boolean);

            try {
                const res = await fetch('/api/ai-config', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        provider,
                        api_key: apiKey || null,
                        system_prompt: systemPrompt,
                        knowledge_base: knowledgeBase,
                        handover_keywords: handoverKeywords,
                        auto_reply_enabled: autoReply,
                    })
                });

                if (res.ok) {
                    alert('✨ AI Configuration and Knowledge Base saved successfully!');
                }
            } catch (err) {
                alert('Failed to save configuration');
            }
        }
    </script>
</body>
</html>
