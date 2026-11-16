<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — SocialMate CRM & AI Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .mesh-gradient {
            background: radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                        radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.15) 0px, transparent 50%),
                        radial-gradient(at 50% 50%, rgba(14, 165, 233, 0.1) 0px, transparent 50%);
        }
    </style>
</head>
<body class="h-full flex bg-[#060913] text-slate-100 overflow-hidden">

    <!-- Split Screen: Left Brand Showcase -->
    <div class="hidden lg:flex lg:w-1/2 relative flex-col justify-between p-12 bg-[#090e1c] border-r border-slate-800/80 overflow-hidden">
        <!-- Background Glows -->
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Brand Header -->
        <div class="relative z-10 flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 via-violet-600 to-pink-500 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div>
                <span class="text-lg font-extrabold tracking-tight bg-gradient-to-r from-white via-slate-200 to-indigo-200 bg-clip-text text-transparent">SocialMate</span>
                <span class="block text-[10px] uppercase font-bold tracking-widest text-indigo-400">Enterprise AI Hub</span>
            </div>
        </div>

        <!-- Hero Content & Highlights -->
        <div class="relative z-10 my-auto py-8 space-y-6">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Omnichannel Social Media CRM + 24/7 AI Agent
            </div>

            <h1 class="text-4xl font-extrabold tracking-tight leading-tight text-white">
                Connect all social chats in one place & close deals <span class="bg-gradient-to-r from-indigo-400 via-violet-400 to-pink-400 bg-clip-text text-transparent">faster with AI.</span>
            </h1>

            <p class="text-sm text-slate-400 leading-relaxed max-w-lg">
                Stop jumping between Facebook Messenger, WhatsApp, Instagram, and Telegram. Respond instantly, manage leads in a visual sales pipeline, and let your AI co-pilot handle routine customer queries.
            </p>

            <!-- Feature Pills -->
            <div class="grid grid-cols-2 gap-3 pt-4">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <span class="text-xl">💬</span>
                    <div>
                        <div class="text-xs font-bold text-white">Unified Live Inbox</div>
                        <div class="text-[11px] text-slate-400">FB, WA, TG, Web in 1 screen</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <span class="text-xl">🤖</span>
                    <div>
                        <div class="text-xs font-bold text-white">Intelligent AI Agent</div>
                        <div class="text-[11px] text-slate-400">Auto-reply & knowledge base</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <span class="text-xl">📊</span>
                    <div>
                        <div class="text-xs font-bold text-white">CRM Deals Kanban</div>
                        <div class="text-[11px] text-slate-400">Stage tracking & deal values</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <span class="text-xl">⚡</span>
                    <div>
                        <div class="text-xs font-bold text-white">Human Handover</div>
                        <div class="text-[11px] text-slate-400">Seamless takeover switch</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Proof Footer -->
        <div class="relative z-10 pt-6 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
            <span>Trusted by digital agencies, eCommerce stores & support teams</span>
            <div class="flex items-center gap-1 text-amber-400 font-bold">
                ★★★★★ <span class="text-slate-300 ml-1">4.9/5</span>
            </div>
        </div>
    </div>

    <!-- Split Screen: Right Login Form -->
    <div class="w-full lg:w-1/2 flex flex-col justify-between p-8 sm:p-12 overflow-y-auto mesh-gradient relative">
        <div class="flex justify-end">
            <span class="text-xs text-slate-400 flex items-center gap-2">
                Don't have an account?
                <a href="{{ route('register') }}" class="px-3 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 font-bold transition-colors">
                    Start Free Trial &rarr;
                </a>
            </span>
        </div>

        <!-- Login Card -->
        <div class="max-w-md w-full mx-auto my-auto py-6 space-y-6">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-white">Welcome back</h2>
                <p class="text-xs text-slate-400 mt-1">Sign in to your organization workspace to access your inbox</p>
            </div>

            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs flex items-center gap-2">
                    <span>⚠️</span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </span>
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email', 'admin@admin.com') }}" class="w-full bg-slate-900/90 border border-slate-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all" placeholder="name@company.com">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-slate-300">Password</label>
                        <a href="#" onclick="alert('Password reset link has been dispatched in test environment!')" class="text-[11px] text-indigo-400 hover:text-indigo-300">Forgot password?</a>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input id="password" name="password" type="password" autocomplete="current-password" required value="12345678" class="w-full bg-slate-900/90 border border-slate-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all" placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input id="remember" name="remember" type="checkbox" checked class="h-4 w-4 rounded bg-slate-900 border-slate-800 text-indigo-600 focus:ring-0">
                        <span class="text-xs text-slate-400">Keep me logged in</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition-all transform active:scale-[0.99] flex items-center justify-center gap-2">
                    <span>Sign In to Dashboard</span>
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </form>

            <!-- 1-Click Quick Demo Login Shortcuts -->
            <div class="pt-6 border-t border-slate-800/80">
                <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center mb-3">1-Click Instant Demo Login</span>

                <div class="space-y-2">
                    <button onclick="fillLogin('admin@admin.com', '12345678')" class="w-full flex items-center justify-between p-2.5 rounded-xl bg-slate-900/60 hover:bg-slate-800/80 border border-slate-800 text-left transition-all group">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-300 flex items-center justify-center font-bold text-xs">👑</span>
                            <div>
                                <div class="text-xs font-bold text-white group-hover:text-amber-300 transition-colors">Super Admin: admin@admin.com</div>
                                <div class="text-[10px] text-slate-400">Manage clients, subscriptions & setup channels</div>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500 group-hover:text-white font-mono">&rarr;</span>
                    </button>

                    <button onclick="fillLogin('owner@apex.test', 'password123')" class="w-full flex items-center justify-between p-2.5 rounded-xl bg-slate-900/60 hover:bg-slate-800/80 border border-slate-800 text-left transition-all group">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center font-bold text-xs">🏢</span>
                            <div>
                                <div class="text-xs font-bold text-white group-hover:text-emerald-300 transition-colors">Client: Apex Electronics (Growth Plan)</div>
                                <div class="text-[10px] text-slate-400">FB + WhatsApp + Telegram + AI Bot Enabled</div>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500 group-hover:text-white font-mono">&rarr;</span>
                    </button>

                    <button onclick="fillLogin('owner@boutique.test', 'password123')" class="w-full flex items-center justify-between p-2.5 rounded-xl bg-slate-900/60 hover:bg-slate-800/80 border border-slate-800 text-left transition-all group">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-pink-500/20 text-pink-300 flex items-center justify-center font-bold text-xs">👗</span>
                            <div>
                                <div class="text-xs font-bold text-white group-hover:text-pink-300 transition-colors">Client: Dhaka Boutique (Starter Plan)</div>
                                <div class="text-[10px] text-slate-400">Only Facebook & Web Widget (No AI Plan)</div>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500 group-hover:text-white font-mono">&rarr;</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} SocialMate SaaS Platform. All rights reserved.
        </div>
    </div>

    <script>
        function fillLogin(email, pass) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = pass;
        }
    </script>
</body>
</html>
