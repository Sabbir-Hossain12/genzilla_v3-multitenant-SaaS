<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Retrieve tenant bound by IdentifyTenant middleware
        $tenant = app(Tenant::class);
        $subscription = $tenant->activeSubscription; // HasOne relationship on Tenant model

        // 1. If no subscription record exists, block store
        if (! $subscription) {
            return Inertia::render('store/StorePause', [
                'storeName' => $tenant->name,
                'isOwner' => auth()->check() && auth()->user()->id === $tenant->user_id,
//                'billingUrl' => route('merchant.billing'),
            ]);
        }

        // 2. Check Trial Status
        if ($subscription->subscription_status === 'trialing') {

            if ($subscription->trial_ends_at && now()->gt($subscription->trial_ends_at)) {
                // Trial has expired
                $subscription->update(['subscription_status' => 'expired']);
                return Inertia::render('store/StorePause', [
                    'storeName' => $tenant->name,
                    'isOwner' => auth()->check() && auth()->user()->id === $tenant->user_id,
//                'billingUrl' => route('merchant.billing'),
                ]);
            }
            return $next($request);
        }

        // 3. Check Active Paid Status
        if ($subscription->subscription_status === 'active') {
            if ($subscription->ends_at && now()->gt($subscription->ends_at)) {
                $subscription->update(['subscription_status' => 'expired']);
                return Inertia::render('store/StorePause', [
                    'storeName' => $tenant->name,
                    'isOwner' => auth()->check() && auth()->user()->id === $tenant->user_id,
//                'billingUrl' => route('merchant.billing'),
                ]);
            }
            return $next($request);
        }

        // 4. Default catch-all for 'expired', 'cancelled', or 'past_due'
        return Inertia::render('store/StorePause', [
            'storeName' => $tenant->name,
            'isOwner' => auth()->check() && auth()->user()->id === $tenant->user_id,
//                'billingUrl' => route('merchant.billing'),
        ]);
    }

}
