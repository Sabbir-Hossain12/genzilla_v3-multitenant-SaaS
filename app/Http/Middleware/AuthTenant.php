<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. If the user is not logged in, redirect to your custom store login route
        if (! $user) {
            return redirect()->route('store.admin.login')
                ->with('error', 'Please log in to access the admin panel.');
        }

        // 2. If the user is logged in, but does not have a tenant row
        if (! $user->tenant) {
            // Optional: Log them out so their invalid session doesn't linger
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('store.admin.login')
                ->with('error', 'This account does not have an associated store.');
        }

        return $next($request);
    }
}
