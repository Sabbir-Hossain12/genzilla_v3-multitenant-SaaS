<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Http\Request;

class TenantSubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = TenantSubscription::with(['tenant', 'plan'])->latest()->get();
        $tenants = Tenant::all();
        $plans = PlatformPlan::where('status', 1)->get();

        return view('platform_admin.pages.subscriptions.index', compact('subscriptions', 'tenants', 'plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'plan_id' => 'required|exists:platform_plans,id',
            'billing_interval' => 'required|in:monthly,yearly',
            'subscription_status' => 'required|in:trialing,active,past_due,expired,cancelled,superseded',
            'trial_ends_at' => 'nullable|date',
            'current_period_starts_at' => 'nullable|date',
            'current_period_ends_at' => 'nullable|date',
        ]);

        TenantSubscription::create($validated);

        return redirect()->back()->with('success', 'Subscription assigned successfully.');
    }

    public function update(Request $request, TenantSubscription $subscription)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:platform_plans,id',
            'billing_interval' => 'required|in:monthly,yearly',
            'subscription_status' => 'required|in:trialing,active,past_due,expired,cancelled,superseded',
            'current_period_ends_at' => 'nullable|date',
        ]);

        $subscription->update($validated);

        return redirect()->back()->with('success', 'Subscription updated successfully.');
    }
}
