<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Free Trial — SocialMate CRM & AI Hub</title>
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
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs font-semibold">
                <span>🎁</span> 14-Day Full Feature Free Trial
            </div>

            <h1 class="text-4xl font-extrabold tracking-tight leading-tight text-white">
                Launch your AI customer service & social inbox <span class="bg-gradient-to-r from-indigo-400 via-violet-400 to-pink-400 bg-clip-text text-transparent">in 2 minutes.</span>
            </h1>

            <p class="text-sm text-slate-400 leading-relaxed max-w-lg">
                Create your company workspace today. Connect your Facebook pages, WhatsApp number, and Telegram bots to centralize inquiries and automate conversations.
            </p>

            <!-- What's included in Trial -->
            <div class="space-y-3 pt-2">
                <div class="flex items-center gap-3 text-xs text-slate-300">
                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                    <span><strong>Omnichannel Hub:</strong> Facebook Messenger, WhatsApp Cloud, Telegram & Website</span>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-300">
                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                    <span><strong>AI Smart Assistant:</strong> Train with your business FAQ, pricing, and refund policies</span>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-300">
                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                    <span><strong>Visual Sales CRM:</strong> Drag and drop lead management with deals pipeline</span>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-300">
                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                    <span><strong>Team Support:</strong> Invite up to 5 support agents with custom permissions</span>
                </div>
            </div>
        </div>

        <!-- Social Proof Footer -->
        <div class="relative z-10 pt-6 border-t border-slate-800/80 text-xs text-slate-400">
            No credit card required • Instant automated setup • Cancel anytime
        </div>
    </div>

    <!-- Split Screen: Right Registration Form -->
    <div class="w-full lg:w-1/2 flex flex-col justify-between p-8 sm:p-12 overflow-y-auto mesh-gradient relative">
        <div class="flex justify-end">
            <span class="text-xs text-slate-400 flex items-center gap-2">
                Already have an account?
                <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 font-bold transition-colors">
                    Sign In &rarr;
                </a>
            </span>
        </div>

        <!-- Registration Card -->
        <div class="max-w-md w-full mx-auto my-auto py-6 space-y-6">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-white">Start your 14-day free trial</h2>
                <p class="text-xs text-slate-400 mt-1">Get started with your custom business workspace in seconds</p>
            </div>

            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs flex items-center gap-2">
                    <span>⚠️</span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="company_name" class="block text-xs font-semibold text-slate-300 mb-1.5">Company / Brand Name</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><line x1="9" x2="15" y1="6" y2="6"/><line x1="9" x2="15" y1="10" y2="10"/><line x1="9" x2="15" y1="14" y2="14"/></svg>
                        </span>
                        <input id="company_name" name="company_name" type="text" required value="{{ old('company_name') }}" class="w-full bg-slate-900/90 border border-slate-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all" placeholder="e.g. Apex Gadgets">
                    </div>
                </div>

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">Your Full Name</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <input id="name" name="name" type="text" required value="{{ old('name') }}" class="w-full bg-slate-900/90 border border-slate-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all" placeholder="e.g. Rahim Chowdhury">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">Work Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </span>
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" class="w-full bg-slate-900/90 border border-slate-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all" placeholder="rahim@apex.com">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-xs">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input id="password" name="password" type="password" required class="w-full bg-slate-900/90 border border-slate-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all" placeholder="Min. 6 chars">
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 mb-1.5">Confirm Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-xs">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full bg-slate-900/90 border border-slate-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all" placeholder="Repeat password">
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition-all transform active:scale-[0.99] flex items-center justify-center gap-2">
                        <span>Create Workspace & Start Free Trial</span>
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                </div>
            </form>

            <p class="text-center text-[11px] text-slate-500">
                By creating an account, you agree to our Terms of Service & Privacy Policy.
            </p>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} SocialMate SaaS Platform. All rights reserved.
        </div>
    </div>
</body>
</html>
