<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            return $user->isSuperAdmin() ? redirect()->route('admin.dashboard') : redirect()->route('tenant.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account has been deactivated. Please contact support.']);
            }

            if ($user->tenant && !$user->tenant->isSubscriptionActive()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your company subscription has expired or is suspended.']);
            }

            return $user->isSuperAdmin()
                ? redirect()->intended(route('admin.dashboard'))
                : redirect()->intended(route('tenant.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            $user = Auth::user();
            return $user->isSuperAdmin() ? redirect()->route('admin.dashboard') : redirect()->route('tenant.dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $tenant = \App\Models\Tenant::create([
            'name' => $validated['company_name'],
            'slug' => \Illuminate\Support\Str::slug($validated['company_name']) . '-' . rand(100, 999),
            'company_email' => $validated['email'],
            'plan_name' => 'Growth',
            'status' => 'active',
            'allowed_channels' => ['facebook', 'whatsapp', 'telegram', 'webchat'],
            'ai_enabled' => true,
            'max_agents' => 5,
            'subscription_starts_at' => now(),
            'subscription_expires_at' => now()->addDays(14),
        ]);

        // Default webchat channel
        \App\Models\Channel::create([
            'tenant_id' => $tenant->id,
            'name' => $tenant->name . ' Live Webchat',
            'platform' => 'webchat',
            'is_active' => true,
            'ai_enabled' => true,
        ]);

        // Tenant Admin user
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => 'tenant_admin',
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('tenant.dashboard')->with('success', 'Welcome to SocialMate! Your 14-day Growth trial with AI and Omnichannel inbox is ready.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Super Admin quick impersonation of a tenant customer.
     */
    public function impersonate(Request $request, User $user)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }

        session(['impersonator_id' => Auth::id()]);
        Auth::login($user);

        return redirect()->route('tenant.dashboard')->with('success', "Logged in as {$user->name} ({$user->tenant?->name})");
    }

    /**
     * Stop impersonating and return to Super Admin.
     */
    public function stopImpersonation()
    {
        $impersonatorId = session('impersonator_id');
        if ($impersonatorId) {
            $superAdmin = User::find($impersonatorId);
            if ($superAdmin && $superAdmin->isSuperAdmin()) {
                session()->forget('impersonator_id');
                Auth::login($superAdmin);
                return redirect()->route('admin.dashboard');
            }
        }

        return redirect()->route('login');
    }
}
