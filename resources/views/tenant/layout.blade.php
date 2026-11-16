<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — {{ $tenant->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Instant Theme Detection Script (Zero FOUC) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'light') {
                document.documentElement.classList.add('light');
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>

    <style>
        /* Modern Base Typography & Smooth Transition */
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        /* Custom Scrollbars */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.4); }
        ::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.35); border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.7); }

        /* Dark Mode Aesthetics: Ambient Glow Background */
        html.dark body {
            background: radial-gradient(circle at 10% 15%, rgba(99, 102, 241, 0.08) 0%, transparent 45%),
                        radial-gradient(circle at 90% 85%, rgba(168, 85, 247, 0.07) 0%, transparent 45%),
                        #070b14 !important;
            color: #f1f5f9 !important;
        }

        /* ======================================================== */
        /* LIGHT MODE DESIGN SYSTEM (Clean, Modern, Stripe/Linear style) */
        /* ======================================================== */
        html.light,
        html.light body {
            background: radial-gradient(circle at 10% 15%, rgba(99, 102, 241, 0.05) 0%, transparent 45%),
                        radial-gradient(circle at 90% 85%, rgba(236, 72, 153, 0.04) 0%, transparent 45%),
                        #f8fafc !important;
            color: #0f172a !important;
        }

        /* Light Mode Scrollbars */
        html.light ::-webkit-scrollbar-track { background: #f1f5f9; }
        html.light ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        html.light ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Structural Backgrounds */
        html.light header,
        html.light #main-sidebar,
        html.light aside#main-sidebar,
        html.light .bg-\[\#0b101d\],
        html.light .bg-\[\#070b14\] {
            background-color: #ffffff !important;
        }

        html.light .bg-slate-950,
        html.light .bg-slate-950\/90,
        html.light .bg-slate-950\/80,
        html.light .bg-slate-950\/70,
        html.light .bg-slate-950\/60,
        html.light .bg-slate-950\/50,
        html.light .bg-slate-950\/40,
        html.light .crm-root {
            background-color: #f8fafc !important;
        }

        html.light .bg-slate-900,
        html.light .bg-slate-900\/95,
        html.light .bg-slate-900\/90,
        html.light .bg-slate-900\/80,
        html.light .bg-slate-900\/70,
        html.light .bg-slate-900\/60,
        html.light .bg-slate-900\/50,
        html.light .bg-slate-900\/40,
        html.light .bg-slate-900\/30,
        html.light .bg-slate-900\/20 {
            background-color: #ffffff !important;
        }

        html.light .bg-slate-800,
        html.light .bg-slate-800\/80,
        html.light .bg-slate-800\/60,
        html.light .bg-slate-800\/50,
        html.light .bg-slate-800\/40,
        html.light .bg-slate-800\/30,
        html.light .bg-slate-800\/20 {
            background-color: #f1f5f9 !important;
        }

        html.light .hover\:bg-slate-800\/40:hover,
        html.light .hover\:bg-slate-800\/50:hover,
        html.light .hover\:bg-slate-800\/30:hover,
        html.light .hover\:bg-slate-700:hover,
        html.light .hover\:bg-slate-800:hover {
            background-color: #e2e8f0 !important;
        }

        /* Borders in Light Mode */
        html.light .border-slate-800,
        html.light .border-slate-800\/80,
        html.light .border-slate-800\/60,
        html.light .border-slate-800\/50,
        html.light .border-slate-800\/40,
        html.light .border-slate-800\/30,
        html.light .border-slate-800\/20,
        html.light .border-slate-700,
        html.light .border-slate-700\/60 {
            border-color: #e2e8f0 !important;
        }

        html.light .divide-slate-800\/40 > :not([hidden]) ~ :not([hidden]),
        html.light .divide-slate-800\/60 > :not([hidden]) ~ :not([hidden]),
        html.light .divide-slate-800 > :not([hidden]) ~ :not([hidden]) {
            border-color: #e2e8f0 !important;
        }

        /* Typography in Light Mode */
        html.light .text-white,
        html.light .text-slate-100,
        html.light .text-slate-200 {
            color: #0f172a !important;
        }

        html.light .text-slate-300,
        html.light .text-slate-400 {
            color: #334155 !important;
        }

        html.light .text-slate-500 {
            color: #64748b !important;
        }

        html.light .text-slate-600 {
            color: #94a3b8 !important;
        }

        /* Form Inputs & Textareas in Light Mode */
        html.light input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="range"]),
        html.light textarea,
        html.light select {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }

        html.light input::placeholder,
        html.light textarea::placeholder {
            color: #94a3b8 !important;
        }

        html.light input:focus,
        html.light textarea:focus,
        html.light select:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15) !important;
        }

        /* Tables in Light Mode */
        html.light thead tr,
        html.light thead th {
            background-color: #f8fafc !important;
            color: #475569 !important;
            border-color: #e2e8f0 !important;
        }

        html.light tbody tr:hover {
            background-color: #f1f5f9 !important;
        }

        /* Cards & Shadows in Light Mode */
        html.light .rounded-2xl,
        html.light .rounded-3xl,
        html.light .rounded-xl {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.03);
        }

        html.light .shadow-xl,
        html.light .shadow-2xl {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.06), 0 8px 10px -6px rgba(0, 0, 0, 0.03) !important;
        }

        /* Live Inbox in Light Mode */
        html.light #messages-container {
            background-color: #f8fafc !important;
        }

        html.light .conv-item.bg-indigo-950\/30 {
            background-color: #eef2ff !important;
            border-left-color: #6366f1 !important;
        }

        html.light #composer-normal-box {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
        }

        html.light #popover-emoji,
        html.light #popover-gif {
            background-color: rgba(255, 255, 255, 0.98) !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.15) !important;
        }

        /* Backdrop in Light Mode */
        html.light #sidebar-overlay,
        html.light #crm-drawer-backdrop {
            background-color: rgba(15, 23, 42, 0.4) !important;
        }
    </style>
    @stack('head')
</head>
<body class="h-full flex flex-col overflow-hidden select-none">

    <!-- Super Admin Impersonation Banner (if active) -->
    @if(session('impersonator_id'))
        <div class="h-9 bg-amber-500 text-slate-950 px-6 flex items-center justify-between text-xs font-bold shrink-0">
            <div class="flex items-center gap-2">
                <span>⚠️ Super Admin Mode:</span>
                <span>You are currently managing <strong>{{ $tenant->name }}</strong> ({{ $tenant->company_email }}).</span>
            </div>
            <a href="{{ route('admin.stop_impersonating') }}" class="px-2.5 py-0.5 rounded bg-slate-950 text-amber-400 hover:text-white transition-colors">
                Return to Super Admin Hub &rarr;
            </a>
        </div>
    @endif

    <!-- Mobile Top Navigation Bar -->
    <header class="h-14 bg-[#0b101d] border-b border-slate-800 px-4 flex items-center justify-between md:hidden shrink-0 z-30">
        <div class="flex items-center gap-3">
            <button type="button" onclick="toggleMobileSidebar()" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white transition-colors" title="Toggle Navigation Menu">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-indigo-600 to-pink-500 flex items-center justify-center font-extrabold text-white text-xs">
                    {{ strtoupper(substr($tenant->name, 0, 1)) }}
                </div>
                <span class="text-xs font-bold text-white truncate max-w-[130px]">{{ $tenant->name }}</span>
            </div>
        </div>

        <div class="flex items-center gap-1.5 sm:gap-2">
            <!-- Mobile Sound Toggle Button -->
            <button type="button" onclick="toggleSoundChime()" id="btn-mobile-sound" class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 text-slate-300 hover:text-white flex items-center justify-center transition-colors shadow-sm" title="Toggle Sound Chime">
                <span id="mobile-sound-icon" class="text-xs">🔊</span>
            </button>
            <!-- Mobile Dark/Light Theme Toggle -->
            <button type="button" onclick="toggleTheme()" class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 text-slate-300 hover:text-white flex items-center justify-center transition-colors shadow-sm" title="Toggle Dark/Light Mode">
                <span id="theme-mobile-icon" class="text-xs">🌙</span>
            </button>
            <a href="{{ route('tenant.inbox') }}" class="px-2.5 py-1.5 rounded-lg bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 text-xs font-semibold flex items-center gap-1.5" title="Live Inbox">
                <span>💬</span>
                <span class="hidden sm:inline">Inbox</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            </a>
            <div class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center text-[11px] font-bold text-slate-200">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
        </div>
    </header>

    <!-- Mobile Backdrop Overlay -->
    <div id="sidebar-overlay" onclick="closeMobileSidebar()" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-40 hidden md:hidden transition-opacity"></div>

    <div class="flex-1 flex overflow-hidden relative">
        <!-- REVE Chat Inspired Dark Left Navigation Sidebar (Responsive Mobile Drawer + Desktop Fixed) -->
        <aside id="main-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 border-r border-slate-800 bg-[#0b101d] flex flex-col shrink-0 transform -translate-x-full md:translate-x-0 md:relative transition-transform duration-300 ease-in-out shadow-2xl md:shadow-none">
            <!-- Brand & Organization Header -->
            <div class="h-16 border-b border-slate-800/80 px-4 flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-violet-600 to-pink-500 flex items-center justify-center font-extrabold text-white text-base shadow-lg shadow-indigo-500/20 shrink-0">
                        {{ strtoupper(substr($tenant->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-xs font-bold text-white truncate">{{ $tenant->name }}</h2>
                        <span class="text-[10px] px-1.5 py-0.2 rounded font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            {{ $tenant->plan_name }} Plan
                        </span>
                    </div>
                </div>
                <button type="button" onclick="closeMobileSidebar()" class="md:hidden p-1.5 text-slate-400 hover:text-white rounded-lg text-lg" title="Close Menu">
                    &times;
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 p-3 space-y-4 overflow-y-auto">
                <!-- Section: Communication -->
                <div class="space-y-1">
                    <span class="block px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Communication</span>

                    <!-- Dashboard -->
                    <a href="{{ route('tenant.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('tenant.dashboard') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Omnichannel Live Inbox -->
                    <a href="{{ route('tenant.inbox') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('tenant.inbox') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <span>Live Inbox</span>
                        </div>
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    </a>
                </div>

                <!-- Section: CRM -->
                <div class="space-y-1">
                    <span class="block px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">CRM</span>

                    <!-- CRM Hub (Unified Entry) -->
                    <a href="{{ route('tenant.crm.hub') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('tenant.crm.hub') ? 'bg-gradient-to-r from-indigo-600 to-purple-700 text-white shadow-md shadow-indigo-500/20' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span class="flex-1">CRM Hub</span>
                        <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 font-bold border border-indigo-500/20">All-in-One</span>
                    </a>

                    <!-- Quick Tab Links (visible always, subtle) -->
                    <div class="pl-6 space-y-0.5">
                        <a href="{{ route('tenant.crm.hub') }}?tab=leads" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-medium text-slate-500 hover:text-slate-300 hover:bg-slate-800/40 transition-all">
                            <span>⊕</span> Leads
                        </a>
                        <a href="{{ route('tenant.crm.hub') }}?tab=deals" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-medium text-slate-500 hover:text-slate-300 hover:bg-slate-800/40 transition-all">
                            <span>⊞</span> Pipeline
                        </a>
                        <a href="{{ route('tenant.crm.hub') }}?tab=activities" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-medium text-slate-500 hover:text-slate-300 hover:bg-slate-800/40 transition-all">
                            <span>⊙</span> Activities
                        </a>
                        <a href="{{ route('tenant.crm.hub') }}?tab=analytics" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-medium text-slate-500 hover:text-slate-300 hover:bg-slate-800/40 transition-all">
                            <span>⊵</span> Analytics
                        </a>
                    </div>
                </div>

                <!-- Section: Channels & AI -->
                <div class="space-y-1">
                    <span class="block px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Setup & AI</span>

                    <!-- AI Chatbot (Only if included in plan) -->
                    @if($tenant->ai_enabled)
                        <a href="{{ route('tenant.ai') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('tenant.ai') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>
                                <span>AI Chatbot</span>
                            </div>
                            <span class="text-[9px] px-1 py-0.2 rounded bg-indigo-500/20 text-indigo-300 font-bold">PRO</span>
                        </a>
                    @endif

                    <!-- Connected Channels -->
                    <a href="{{ route('tenant.channels') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('tenant.channels') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        <span>Channels</span>
                    </a>

                    <!-- Team Agents -->
                    <a href="{{ route('tenant.agents') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('tenant.agents') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Team Agents</span>
                    </a>

                    <!-- Settings & Widget -->
                    <a href="{{ route('tenant.settings') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('tenant.settings') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        <span>Widget & Setup</span>
                    </a>
                </div>
            </nav>

            <!-- Dark / Light Theme Toggle Switcher -->
            <div class="px-3 py-1.5 border-t border-slate-800/80">
                <div class="flex items-center justify-between p-1.5 rounded-xl bg-slate-900/80 border border-slate-800/60 shadow-sm">
                    <span class="text-[11px] font-semibold text-slate-400 flex items-center gap-1.5 px-2">
                        <span id="theme-label-icon">🌙</span>
                        <span id="theme-label-text">Dark Mode</span>
                    </span>
                    <button type="button" onclick="toggleTheme()" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none bg-indigo-600" id="theme-toggle-btn" role="switch" aria-checked="true" title="Toggle Light / Dark Mode">
                        <span class="sr-only">Toggle theme</span>
                        <span id="theme-toggle-thumb" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out translate-x-5 flex items-center justify-center text-[10px]">
                            🌙
                        </span>
                    </button>
                </div>
            </div>

            <!-- Audio Chime & Push Notification Controls -->
            <div class="px-3 py-1.5 border-t border-slate-800/80">
                <div class="flex items-center justify-between p-1.5 rounded-xl bg-slate-900/80 border border-slate-800/60 shadow-sm">
                    <button type="button" onclick="toggleSoundChime()" id="btn-sound-toggle" class="flex items-center gap-1.5 px-2 py-1 rounded-lg text-[11px] font-semibold text-slate-400 hover:text-white transition-colors" title="Toggle incoming message sound">
                        <span id="sound-icon">🔊</span>
                        <span id="sound-text">Sound</span>
                    </button>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="playDingChime()" class="p-1 text-slate-400 hover:text-indigo-400 text-xs rounded transition-transform active:scale-90" title="Play Test Ding Sound">
                            🎵
                        </button>
                        <button type="button" onclick="requestNotificationPermission()" id="btn-desktop-notif" class="px-2 py-1 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 text-[10px] font-bold border border-indigo-500/30 transition-all flex items-center gap-1" title="Enable Desktop Push Notifications">
                            <span id="notif-badge-icon">🔔</span>
                            <span id="notif-badge-text">Push</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Current User & Logout Footer -->
            <div class="p-3 border-t border-slate-800 bg-slate-950/60 flex items-center justify-between">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-200 shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-slate-500 uppercase">{{ auth()->user()->role }}</div>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-rose-400 rounded-lg transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Workspace Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            @yield('content')
        </div>
    </div>

    @yield('scripts')
    @stack('scripts')

    <!-- SweetAlert2 Toast & Alerts Global Config -->
    <script>
        window.Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            background: '#0b1120',
            color: '#f8fafc',
            iconColor: '#38bdf8',
            customClass: {
                popup: 'border border-slate-800 shadow-2xl rounded-2xl text-xs backdrop-blur-md'
            },
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
    </script>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Toast.fire({ icon: 'success', title: '{{ addslashes(session('success')) }}', iconColor: '#10b981' });
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Toast.fire({ icon: 'error', title: '{{ addslashes(session('error')) }}', iconColor: '#f43f5e' });
            });
        </script>
    @endif

    @if(session('info'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Toast.fire({ icon: 'info', title: '{{ addslashes(session('info')) }}', iconColor: '#6366f1' });
            });
        </script>
    @endif

    <script>
        function toggleTheme() {
            const isLight = document.documentElement.classList.contains('light');
            if (isLight) {
                document.documentElement.classList.remove('light');
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
            }
            updateThemeUI();
        }

        function updateThemeUI() {
            const isLight = document.documentElement.classList.contains('light');
            const thumb = document.getElementById('theme-toggle-thumb');
            const labelText = document.getElementById('theme-label-text');
            const labelIcon = document.getElementById('theme-label-icon');
            const mobileIcon = document.getElementById('theme-mobile-icon');
            const toggleBtn = document.getElementById('theme-toggle-btn');

            if (isLight) {
                if (thumb) {
                    thumb.classList.remove('translate-x-5');
                    thumb.classList.add('translate-x-0');
                    thumb.innerText = '☀️';
                }
                if (toggleBtn) {
                    toggleBtn.classList.remove('bg-indigo-600');
                    toggleBtn.classList.add('bg-amber-400');
                }
                if (labelText) labelText.innerText = 'Light Mode';
                if (labelIcon) labelIcon.innerText = '☀️';
                if (mobileIcon) mobileIcon.innerText = '☀️';
            } else {
                if (thumb) {
                    thumb.classList.remove('translate-x-0');
                    thumb.classList.add('translate-x-5');
                    thumb.innerText = '🌙';
                }
                if (toggleBtn) {
                    toggleBtn.classList.remove('bg-amber-400');
                    toggleBtn.classList.add('bg-indigo-600');
                }
                if (labelText) labelText.innerText = 'Dark Mode';
                if (labelIcon) labelIcon.innerText = '🌙';
                if (mobileIcon) mobileIcon.innerText = '🌙';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateThemeUI();
            updateNotificationAndSoundUI();
        });

        /* ======================================================== */
        /* AUDIO CHIME SYSTEM (Pleasant Web Audio Ding Synthesizer) */
        /* ======================================================== */
        window.playDingChime = function() {
            if (localStorage.getItem('notification_sound_enabled') === 'false') {
                return;
            }
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const ctx = new AudioContext();

                if (ctx.state === 'suspended') {
                    ctx.resume();
                }

                const now = ctx.currentTime;

                // Tone 1: 587.33 Hz (D5) - soft lead chime
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, now);
                gain1.gain.setValueAtTime(0, now);
                gain1.gain.linearRampToValueAtTime(0.2, now + 0.02);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.3);

                // Tone 2: 880 Hz (A5) - clear bell chime
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880, now + 0.07);
                gain2.gain.setValueAtTime(0, now + 0.07);
                gain2.gain.linearRampToValueAtTime(0.3, now + 0.09);
                gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.75);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.07);
                osc2.stop(now + 0.75);
            } catch (e) {
                console.warn('Audio chime playback note:', e);
            }
        };

        window.toggleSoundChime = function() {
            const isCurrentlyEnabled = localStorage.getItem('notification_sound_enabled') !== 'false';
            const newState = !isCurrentlyEnabled;
            localStorage.setItem('notification_sound_enabled', newState ? 'true' : 'false');
            updateNotificationAndSoundUI();
            if (newState) {
                window.playDingChime();
                Toast.fire({ icon: 'success', title: '🔊 Message Ding Sound Enabled', iconColor: '#10b981' });
            } else {
                Toast.fire({ icon: 'info', title: '🔇 Message Sound Muted', iconColor: '#94a3b8' });
            }
        };

        /* ======================================================== */
        /* WEB DESKTOP PUSH NOTIFICATION SYSTEM                     */
        /* ======================================================== */
        window.requestNotificationPermission = async function() {
            if (!('Notification' in window)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Not Supported',
                    text: 'Your browser does not support desktop notifications.',
                    background: document.documentElement.classList.contains('light') ? '#ffffff' : '#0b1120',
                    color: document.documentElement.classList.contains('light') ? '#0f172a' : '#f8fafc'
                });
                return false;
            }

            try {
                const permission = await Notification.requestPermission();
                updateNotificationAndSoundUI();
                if (permission === 'granted') {
                    Toast.fire({
                        icon: 'success',
                        title: '🔔 Desktop Notifications Active!',
                        iconColor: '#10b981'
                    });
                    window.playDingChime();
                    window.showDesktopNotification('Antigravity OmniChannel', '🎉 Desktop notifications are now active! You will receive instant notifications even when working in other tabs or minimized.', null);
                    return true;
                } else if (permission === 'denied') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Notifications Blocked',
                        text: 'Notifications are blocked in your browser settings. Please click the lock/settings icon in your browser address bar to allow notifications.',
                        background: document.documentElement.classList.contains('light') ? '#ffffff' : '#0b1120',
                        color: document.documentElement.classList.contains('light') ? '#0f172a' : '#f8fafc'
                    });
                    return false;
                }
            } catch (e) {
                console.error(e);
                return false;
            }
        };

        window.showDesktopNotification = function(title, body, convId) {
            if (!('Notification' in window) || Notification.permission !== 'granted') return;

            try {
                const options = {
                    body: body || 'New message received from customer',
                    tag: convId ? `conv-${convId}` : 'general-inbound',
                    renotify: true
                };

                const notif = new Notification(title, options);

                // Auto close after 5 seconds (5000ms)
                setTimeout(() => {
                    try {
                        notif.close();
                    } catch (e) {}
                }, 5000);

                notif.onclick = function() {
                    window.focus();
                    if (convId) {
                        if (window.location.pathname.includes('/tenant/inbox')) {
                            if (typeof selectConversation === 'function') {
                                selectConversation(convId);
                            }
                        } else {
                            window.location.href = `/tenant/inbox?conv=${convId}`;
                        }
                    }
                    notif.close();
                };
            } catch (e) {
                console.warn('Desktop notification spawn note:', e);
            }
        };

        function updateNotificationAndSoundUI() {
            const soundEnabled = localStorage.getItem('notification_sound_enabled') !== 'false';
            const soundIcon = document.getElementById('sound-icon');
            const soundText = document.getElementById('sound-text');
            const mobileSoundIcon = document.getElementById('mobile-sound-icon');

            if (soundIcon) soundIcon.innerText = soundEnabled ? '🔊' : '🔇';
            if (soundText) soundText.innerText = soundEnabled ? 'Sound' : 'Muted';
            if (mobileSoundIcon) mobileSoundIcon.innerText = soundEnabled ? '🔊' : '🔇';

            const notifBtn = document.getElementById('btn-desktop-notif');
            const notifIcon = document.getElementById('notif-badge-icon');
            const notifText = document.getElementById('notif-badge-text');

            if (notifBtn && 'Notification' in window) {
                if (Notification.permission === 'granted') {
                    notifBtn.className = 'px-2 py-1 rounded-lg bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-400 text-[10px] font-bold border border-emerald-500/30 transition-all flex items-center gap-1';
                    if (notifIcon) notifIcon.innerText = '✓';
                    if (notifText) notifText.innerText = 'Push';
                    notifBtn.title = 'Desktop Notifications are active! Click to send a test notification.';
                    notifBtn.onclick = () => {
                        window.playDingChime();
                        window.showDesktopNotification('Antigravity OmniChannel Test', '🔔 Test notification working perfectly! (Sound + Desktop Push)', null);
                    };
                } else if (Notification.permission === 'denied') {
                    notifBtn.className = 'px-2 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-[10px] font-bold border border-rose-500/30 transition-all flex items-center gap-1';
                    if (notifIcon) notifIcon.innerText = '✕';
                    if (notifText) notifText.innerText = 'Push';
                    notifBtn.title = 'Desktop Notifications blocked. Click for help to allow.';
                    notifBtn.onclick = requestNotificationPermission;
                } else {
                    notifBtn.className = 'px-2 py-1 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 text-[10px] font-bold border border-indigo-500/30 transition-all flex items-center gap-1';
                    if (notifIcon) notifIcon.innerText = '🔔';
                    if (notifText) notifText.innerText = 'Push';
                    notifBtn.title = 'Click to enable Desktop Push Notifications!';
                    notifBtn.onclick = requestNotificationPermission;
                }
            }
        }

        /* ======================================================== */
        /* DYNAMIC BROWSER TAB TITLE FLASHING                       */
        /* ======================================================== */
        let originalPageTitle = document.title;
        let titleFlashTimer = null;

        window.flashDocumentTitle = function(alertTitle) {
            if (!document.hidden) return; // Only flash if tab is in background
            if (titleFlashTimer) clearInterval(titleFlashTimer);
            let state = false;
            titleFlashTimer = setInterval(() => {
                document.title = state ? alertTitle : originalPageTitle;
                state = !state;
            }, 1000);
        };

        window.addEventListener('focus', () => {
            if (titleFlashTimer) {
                clearInterval(titleFlashTimer);
                titleFlashTimer = null;
            }
            document.title = originalPageTitle;
        });

        /* ======================================================== */
        /* GLOBAL BACKGROUND MESSAGE DETECTOR                       */
        /* (Plays chime and pushes notification outside inbox page) */
        /* ======================================================== */
        if (!window.location.pathname.includes('/tenant/inbox')) {
            let lastKnownLatestMessageId = null;

            setInterval(async () => {
                try {
                    const res = await fetch('/tenant/inbox/poll', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await res.json();
                    if (data.success && data.conversations && data.conversations.length > 0) {
                        const latestConv = data.conversations[0];
                        if (latestConv && latestConv.latest_message) {
                            const msg = latestConv.latest_message;
                            if (lastKnownLatestMessageId === null) {
                                lastKnownLatestMessageId = msg.id;
                            } else if (msg.id > lastKnownLatestMessageId && msg.direction === 'inbound') {
                                lastKnownLatestMessageId = msg.id;
                                window.playDingChime();
                                const senderName = latestConv.contact?.name || 'Customer';
                                const platform = (latestConv.channel?.platform || 'Social').toUpperCase();
                                window.showDesktopNotification(`💬 ${senderName} (${platform})`, msg.content || 'Sent a new message', latestConv.id);
                                window.flashDocumentTitle(`(1) 💬 ${senderName}`);
                                Toast.fire({
                                    icon: 'info',
                                    title: `💬 New message from ${senderName} (${platform}): "${(msg.content || 'Sent attachment').slice(0, 45)}"`,
                                    timer: 5000
                                });
                            }
                        }
                    }
                } catch (e) {
                    // silent catch
                }
            }, 8000);
        }

        function toggleMobileSidebar() {
            const sidebar = document.getElementById('main-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (!sidebar || !overlay) return;
            const isClosed = sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        function closeMobileSidebar() {
            const sidebar = document.getElementById('main-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar) sidebar.classList.add('-translate-x-full');
            if (overlay) overlay.classList.add('hidden');
        }
    </script>
</body>
</html>
