<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Hub — SocialMate Platform Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex bg-[#070b14] overflow-hidden">

    <!-- Super Admin Sidebar -->
    <aside class="w-64 border-r border-slate-800 bg-slate-900/60 flex flex-col shrink-0">
        <div class="h-16 border-b border-slate-800 flex items-center gap-3 px-5">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 via-orange-500 to-rose-600 flex items-center justify-center font-bold text-white shadow-lg shadow-orange-500/20">
                👑
            </div>
            <div>
                <h1 class="text-xs font-bold uppercase tracking-wider text-white">Super Admin</h1>
                <span class="text-[10px] text-amber-400 font-medium">Platform Management</span>
            </div>
        </div>

        <nav class="flex-1 p-3 space-y-1">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                <span>Subscribed Clients</span>
            </a>
            <a href="#" onclick="alert('System logs & API monitoring')" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/40 text-xs font-medium">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 20v-6M6 20V10M18 20V4"/></svg>
                <span>System Analytics</span>
            </a>
        </nav>

        <!-- Current Admin Profile -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/60 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-200">
                    SA
                </div>
                <div>
                    <div class="text-xs font-bold text-white">{{ auth()->user()->name }}</div>
                    <div class="text-[10px] text-slate-500">Super Administrator</div>
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

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col overflow-y-auto">
        <!-- Top Stats Banner -->
        <header class="p-8 border-b border-slate-800/80 bg-slate-900/30">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-white tracking-tight">Client Organizations & Subscriptions</h2>
                    <p class="text-xs text-slate-400">Sell omnichannel CRM access, configure social media channels, and assign role permissions</p>
                </div>
                <button onclick="document.getElementById('modal-new-tenant').classList.remove('hidden')" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all">
                    <span>+ Add New Client Account</span>
                </button>
            </div>

            <!-- Metric Cards -->
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-2xl">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Active Client Companies</div>
                    <div class="text-2xl font-extrabold text-white mt-1">{{ $stats['active_tenants'] }} <span class="text-xs font-normal text-slate-500">/ {{ $stats['total_tenants'] }} total</span></div>
                </div>

                <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-2xl">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Monthly Recurring Revenue</div>
                    <div class="text-2xl font-extrabold text-emerald-400 mt-1">${{ number_format($stats['monthly_revenue'], 2) }}</div>
                </div>

                <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-2xl">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total CRM Leads Managed</div>
                    <div class="text-2xl font-extrabold text-purple-400 mt-1">{{ $stats['total_contacts'] }}</div>
                </div>

                <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-2xl">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Processed Messages</div>
                    <div class="text-2xl font-extrabold text-blue-400 mt-1">{{ $stats['total_messages'] }}</div>
                </div>
            </div>
        </header>

        <!-- Feedback Messages -->
        @if(session('success'))
            <div class="m-8 mb-0 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2">
                <span>✓</span> {{ session('success') }}
            </div>
        @endif

        <!-- Client Tenants Table -->
        <section class="p-8">
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300">Subscribed Customer Accounts</h3>
                    <span class="text-xs text-slate-500">{{ count($tenants) }} Organizations Registered</span>
                </div>

                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 bg-slate-950/50 text-[11px] text-slate-400 uppercase">
                            <th class="p-4 font-semibold">Client Company</th>
                            <th class="p-4 font-semibold">Subscription Plan</th>
                            <th class="p-4 font-semibold">Allowed Channels</th>
                            <th class="p-4 font-semibold">AI Agent</th>
                            <th class="p-4 font-semibold">Expires At</th>
                            <th class="p-4 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($tenants as $tenant)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-white text-sm">{{ $tenant->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $tenant->company_email }} • {{ $tenant->company_phone ?? 'No phone' }}</div>
                                </td>

                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] 
                                        @if($tenant->plan_name === 'Enterprise') bg-purple-500/20 text-purple-300 border border-purple-500/30
                                        @elseif($tenant->plan_name === 'Growth') bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                                        @else bg-slate-800 text-slate-300 border border-slate-700 @endif">
                                        {{ $tenant->plan_name }} Plan
                                    </span>
                                </td>

                                <td class="p-4">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @foreach($tenant->allowed_channels ?? [] as $ch)
                                            <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold
                                                @if($ch === 'facebook') bg-blue-500/20 text-blue-400
                                                @elseif($ch === 'whatsapp') bg-emerald-500/20 text-emerald-400
                                                @elseif($ch === 'telegram') bg-sky-500/20 text-sky-400
                                                @elseif($ch === 'instagram') bg-pink-500/20 text-pink-400
                                                @else bg-purple-500/20 text-purple-400 @endif">
                                                {{ $ch }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="p-4">
                                    @if($tenant->ai_enabled)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                            <span>🤖 Enabled</span>
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-500">Disabled</span>
                                    @endif
                                </td>

                                <td class="p-4 text-slate-400 text-[11px]">
                                    {{ $tenant->subscription_expires_at ? $tenant->subscription_expires_at->format('M d, Y') : 'Lifetime' }}
                                </td>

                                <td class="p-4 text-right space-x-2">
                                    <!-- Impersonate Login -->
                                    @php $owner = $tenant->users->where('role', 'tenant_admin')->first(); @endphp
                                    @if($owner)
                                        <a href="{{ route('admin.impersonate', $owner->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-semibold transition-all">
                                            <span>👁️ Login as Client</span>
                                        </a>
                                    @endif

                                    <!-- Setup Channels Button -->
                                    <button onclick="openChannelSetupModal({{ $tenant->id }}, '{{ addslashes($tenant->name) }}')" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold transition-all">
                                        ⚙️ Setup Channels
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Modal: Add New Client / Tenant Account -->
    <div id="modal-new-tenant" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white">Create New Client Organization</h3>
                <button onclick="document.getElementById('modal-new-tenant').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form action="{{ route('admin.tenants.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Company / Client Name</label>
                        <input type="text" name="name" required placeholder="e.g. Star Tech" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Company Email</label>
                        <input type="email" name="company_email" required placeholder="info@startech.com" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Subscription Plan</label>
                        <select name="plan_name" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                            <option value="Starter">Starter ($29/mo - 2 Channels)</option>
                            <option value="Growth" selected>Growth ($79/mo - 10 Channels + AI)</option>
                            <option value="Enterprise">Enterprise ($199/mo - Unlimited + Dedicated)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Duration (Months)</label>
                        <input type="number" name="subscription_months" value="12" min="1" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <!-- Allowed Channels Checkboxes -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2">Enable Social Media Channels for this Client:</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center gap-2 p-2 bg-slate-950 border border-slate-800 rounded-lg text-xs cursor-pointer">
                            <input type="checkbox" name="allowed_channels[]" value="facebook" checked class="rounded text-indigo-600">
                            <span>Facebook</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 bg-slate-950 border border-slate-800 rounded-lg text-xs cursor-pointer">
                            <input type="checkbox" name="allowed_channels[]" value="whatsapp" checked class="rounded text-indigo-600">
                            <span>WhatsApp</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 bg-slate-950 border border-slate-800 rounded-lg text-xs cursor-pointer">
                            <input type="checkbox" name="allowed_channels[]" value="telegram" checked class="rounded text-indigo-600">
                            <span>Telegram</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 bg-slate-950 border border-slate-800 rounded-lg text-xs cursor-pointer">
                            <input type="checkbox" name="allowed_channels[]" value="instagram" class="rounded text-indigo-600">
                            <span>Instagram</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 bg-slate-950 border border-slate-800 rounded-lg text-xs cursor-pointer">
                            <input type="checkbox" name="allowed_channels[]" value="webchat" checked class="rounded text-indigo-600">
                            <span>Webchat</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 bg-slate-950 border border-slate-800 rounded-lg text-xs cursor-pointer">
                            <input type="checkbox" name="ai_enabled" value="1" checked class="rounded text-indigo-600">
                            <span class="text-indigo-400 font-bold">🤖 AI Agent</span>
                        </label>
                    </div>
                </div>

                <!-- Client Owner Admin Credentials -->
                <div class="pt-3 border-t border-slate-800 space-y-3">
                    <span class="block text-xs font-bold text-amber-400 uppercase tracking-wider">Client Primary Login (Tenant Admin)</span>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <input type="text" name="admin_name" required placeholder="Owner Name" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2 text-xs text-white">
                        </div>
                        <div>
                            <input type="email" name="admin_email" required placeholder="owner@client.com" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2 text-xs text-white">
                        </div>
                        <div>
                            <input type="password" name="admin_password" required placeholder="Password" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2 text-xs text-white">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('modal-new-tenant').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-xs rounded-xl text-slate-300">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 font-bold text-xs rounded-xl text-white shadow-lg">Save Client Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Setup Social Media Channel for Client -->
    <div id="modal-setup-channel" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white">Configure Social Channel for <span id="modal-channel-tenant-name" class="text-indigo-400"></span></h3>
                <button onclick="document.getElementById('modal-setup-channel').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form id="form-setup-channel" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Platform</label>
                    <select name="platform" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                        <option value="facebook">Facebook Messenger / Page</option>
                        <option value="whatsapp">WhatsApp Business Cloud API</option>
                        <option value="telegram">Telegram Bot</option>
                        <option value="instagram">Instagram Direct</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Channel Display Name</label>
                    <input type="text" name="name" required placeholder="e.g. Official FB Page" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Page ID / Phone Number ID / Bot Username</label>
                    <input type="text" name="platform_account_id" placeholder="e.g. 1092837465" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Access Token / Bot Token</label>
                    <input type="password" name="access_token" placeholder="Paste access token from Meta or BotFather" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('modal-setup-channel').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-xs rounded-xl text-slate-300">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 font-bold text-xs rounded-xl text-white shadow-lg">Save Channel Configuration</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openChannelSetupModal(tenantId, tenantName) {
            document.getElementById('modal-channel-tenant-name').innerText = tenantName;
            document.getElementById('form-setup-channel').action = `/admin/tenants/${tenantId}/channels`;
            document.getElementById('modal-setup-channel').classList.remove('hidden');
        }

        window.Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
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
</body>
</html>
