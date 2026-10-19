<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TenantSubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = TenantSubscription::with(['tenant', 'plan', 'invoices'])->latest()->get();
        $tenants = Tenant::orderBy('store_name')->get();
        $plans = PlatformPlan::where('status', 1)->get();

        return view('platform_admin.pages.subscriptions.index', compact('subscriptions', 'tenants', 'plans'));
    }

    public function edit(TenantSubscription $subscription)
    {
        $subscription->load(['tenant', 'plan', 'invoices']);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($subscription);
        }

        $tenants = Tenant::orderBy('store_name')->get();
        $plans = PlatformPlan::where('status', 1)->get();

        return view('platform_admin.pages.subscriptions.edit', compact('subscription', 'tenants', 'plans'));
    }

    public function store(Request $request)
    {
        $tenantMode = $request->input('tenant_mode', 'new');

        $rules = [
            'tenant_mode' => 'required|in:new,existing',
            'plan_id' => 'required|exists:platform_plans,id',
            'billing_interval' => 'required|in:monthly,yearly',
            'subscription_status' => 'required|in:trialing,active,past_due,expired,cancelled,superseded',
            'trial_ends_at' => 'nullable|date',
            'current_period_starts_at' => 'nullable|date',
            'current_period_ends_at' => 'nullable|date',
            'create_invoice' => 'nullable|boolean',
            'invoice_amount' => 'nullable|numeric|min:0',
        ];

        if ($tenantMode === 'new') {
            $rules['user_name'] = 'required|string|max:255';
            $rules['user_email'] = 'required|email|max:255|unique:users,email';
            $rules['password'] = 'nullable|string|min:6';
            $rules['store_name'] = 'required|string|max:255';
            $rules['subdomain'] = 'required|string|alpha_dash|max:100|unique:tenants,subdomain';
            $rules['support_email'] = 'nullable|email|max:255';
            $rules['support_phone'] = 'nullable|string|max:50';
            $rules['currency'] = 'nullable|string|size:3';
            $rules['timezone'] = 'nullable|string|max:100';
        } else {
            $rules['tenant_id'] = 'required|exists:tenants,id';
        }

        $validated = $request->validate($rules);

        $plan = PlatformPlan::findOrFail($validated['plan_id']);
        $periodStart = $request->filled('current_period_starts_at') ? Carbon::parse($validated['current_period_starts_at']) : null;
        $periodEnd = $request->filled('current_period_ends_at') ? Carbon::parse($validated['current_period_ends_at']) : null;
        $trialEnd = $request->filled('trial_ends_at') ? Carbon::parse($validated['trial_ends_at']) : null;
        $createInvoice = $request->has('create_invoice') ? $request->boolean('create_invoice') : true;
        $invoiceAmount = $request->filled('invoice_amount') ? (float) $validated['invoice_amount'] : null;

        if ($tenantMode === 'new') {
            Tenant::provisionWithManualSubscription(
                userData: [
                    'name' => $validated['user_name'],
                    'email' => $validated['user_email'],
                    'password' => $validated['password'] ?? 'password',
                    'status' => 1,
                ],
                tenantData: [
                    'store_name' => $validated['store_name'],
                    'subdomain' => $validated['subdomain'],
                    'support_email' => $validated['support_email'] ?? $validated['user_email'],
                    'support_phone' => $validated['support_phone'] ?? null,
                    'currency' => $validated['currency'] ?? 'USD',
                    'timezone' => $validated['timezone'] ?? 'UTC',
                    'status' => 'active',
                ],
                plan: $plan,
                billingInterval: $validated['billing_interval'],
                subscriptionStatus: $validated['subscription_status'],
                periodStart: $periodStart,
                periodEnd: $periodEnd,
                trialEnd: $trialEnd,
                createInvoice: $createInvoice,
                customInvoiceAmount: $invoiceAmount
            );

            return redirect()->back()->with('success', 'Merchant Store and Subscription created successfully (User -> Tenant -> Domain -> Subscription -> Invoice -> Usage).');
        }

        $tenant = Tenant::findOrFail($validated['tenant_id']);

        $tenant->assignManualSubscription(
            plan: $plan,
            billingInterval: $validated['billing_interval'],
            subscriptionStatus: $validated['subscription_status'],
            periodStart: $periodStart,
            periodEnd: $periodEnd,
            trialEnd: $trialEnd,
            createInvoice: $createInvoice,
            customInvoiceAmount: $invoiceAmount
        );

        return redirect()->back()->with('success', 'Manual subscription assigned successfully to existing tenant.');
    }

    public function update(Request $request, TenantSubscription $subscription)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:platform_plans,id',
            'billing_interval' => 'required|in:monthly,yearly',
            'subscription_status' => 'required|in:trialing,active,past_due,expired,cancelled,superseded',
            'trial_ends_at' => 'nullable|date',
            'current_period_starts_at' => 'nullable|date',
            'current_period_ends_at' => 'nullable|date',
            'create_invoice' => 'nullable|boolean',
            'invoice_amount' => 'nullable|numeric|min:0',
        ]);

        $tenant = $subscription->tenant ?? Tenant::findOrFail($subscription->tenant_id);
        $plan = PlatformPlan::findOrFail($validated['plan_id']);

        $periodStart = $request->filled('current_period_starts_at') ? Carbon::parse($validated['current_period_starts_at']) : null;
        $periodEnd = $request->filled('current_period_ends_at') ? Carbon::parse($validated['current_period_ends_at']) : null;
        $trialEnd = $request->filled('trial_ends_at') ? Carbon::parse($validated['trial_ends_at']) : null;
        $createInvoice = $request->boolean('create_invoice');
        $invoiceAmount = $request->filled('invoice_amount') ? (float) $validated['invoice_amount'] : null;

        $tenant->updateManualSubscription(
            subscription: $subscription,
            plan: $plan,
            billingInterval: $validated['billing_interval'],
            subscriptionStatus: $validated['subscription_status'],
            periodStart: $periodStart,
            periodEnd: $periodEnd,
            trialEnd: $trialEnd,
            createInvoice: $createInvoice,
            customInvoiceAmount: $invoiceAmount
        );

        return redirect()->back()->with('success', 'Subscription updated successfully.');
    }
}
