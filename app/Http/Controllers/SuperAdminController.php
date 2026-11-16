<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with(['users', 'channels'])->withCount(['contacts', 'channels'])->latest()->get();

        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'total_messages' => Message::count(),
            'total_contacts' => Contact::count(),
            'monthly_revenue' => $tenants->sum(function ($t) {
                return match($t->plan_name) {
                    'Enterprise' => 199,
                    'Growth' => 79,
                    default => 29,
                };
            }),
        ];

        return view('admin.dashboard', compact('tenants', 'stats'));
    }

    public function storeTenant(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_email' => 'required|email|unique:tenants,company_email',
            'company_phone' => 'nullable|string',
            'plan_name' => 'required|string|in:Starter,Growth,Enterprise',
            'allowed_channels' => 'nullable|array',
            'ai_enabled' => 'nullable|boolean',
            'max_agents' => 'nullable|integer|min:1',
            'subscription_months' => 'nullable|integer|min:1',
            // Admin user details
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:6',
        ]);

        $months = (int) ($validated['subscription_months'] ?? 12);

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . rand(100, 999),
            'company_email' => $validated['company_email'],
            'company_phone' => $validated['company_phone'] ?? null,
            'plan_name' => $validated['plan_name'],
            'status' => 'active',
            'allowed_channels' => $validated['allowed_channels'] ?? ['facebook', 'webchat'],
            'ai_enabled' => $request->boolean('ai_enabled'),
            'max_agents' => (int) ($validated['max_agents'] ?? 5),
            'subscription_starts_at' => now(),
            'subscription_expires_at' => now()->addMonths($months),
        ]);

        // Create Default Webchat channel
        Channel::create([
            'tenant_id' => $tenant->id,
            'name' => $tenant->name . ' Web Widget',
            'platform' => 'webchat',
            'is_active' => true,
            'ai_enabled' => $tenant->ai_enabled,
        ]);

        // Create Tenant Admin User
        User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'role' => 'tenant_admin',
            'password' => Hash::make($validated['admin_password']),
            'is_active' => true,
        ]);

        return redirect()->route('admin.dashboard')->with('success', "Tenant account '{$tenant->name}' created successfully!");
    }

    public function updateTenant(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plan_name' => 'required|string|in:Starter,Growth,Enterprise',
            'status' => 'required|string|in:active,suspended,expired',
            'allowed_channels' => 'nullable|array',
            'ai_enabled' => 'nullable|boolean',
            'max_agents' => 'nullable|integer',
        ]);

        $tenant->update([
            'name' => $validated['name'],
            'plan_name' => $validated['plan_name'],
            'status' => $validated['status'],
            'allowed_channels' => $validated['allowed_channels'] ?? [],
            'ai_enabled' => $request->boolean('ai_enabled'),
            'max_agents' => (int) ($validated['max_agents'] ?? 5),
        ]);

        return redirect()->route('admin.dashboard')->with('success', "Tenant '{$tenant->name}' updated successfully.");
    }

    public function setupChannel(Request $request, Tenant $tenant, \App\Services\Omnichannel\ChannelConnectionService $connectionService)
    {
        $platform = $request->input('platform');

        // Normalize field names if submitted from simple admin form
        $data = $request->all();
        if ($platform === 'telegram' && empty($data['bot_token']) && !empty($data['access_token'])) {
            $data['bot_token'] = $data['access_token'];
        }
        if ($platform === 'whatsapp' && empty($data['phone_number_id']) && !empty($data['platform_account_id'])) {
            $data['phone_number_id'] = $data['platform_account_id'];
        }
        if ($platform === 'facebook' && empty($data['page_id']) && !empty($data['platform_account_id'])) {
            $data['page_id'] = $data['platform_account_id'];
        }
        if ($platform === 'instagram' && empty($data['instagram_id']) && !empty($data['platform_account_id'])) {
            $data['instagram_id'] = $data['platform_account_id'];
        }

        $result = $connectionService->connect($tenant, $platform, $data);

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        return redirect()->route('admin.dashboard')->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function testChannel(Channel $channel, \App\Services\Omnichannel\ChannelConnectionService $connectionService)
    {
        $result = $connectionService->testConnection($channel);
        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function destroyChannel(Channel $channel)
    {
        $name = $channel->name;
        $channel->delete();
        return back()->with('success', "Channel '{$name}' removed.");
    }

    public function destroyTenant(Tenant $tenant)
    {
        $name = $tenant->name;
        $tenant->delete();

        return redirect()->route('admin.dashboard')->with('success', "Tenant '{$name}' deleted.");
    }
}
