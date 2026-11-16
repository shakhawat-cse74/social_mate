<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // If Super Admin is logged in, they can view tenant pages too
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if (!$user->tenant) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'No active organization found for your account.']);
        }

        if (!$user->tenant->isSubscriptionActive()) {
            return response()->view('errors.subscription_expired', ['tenant' => $user->tenant], 403);
        }

        return $next($request);
    }
}
