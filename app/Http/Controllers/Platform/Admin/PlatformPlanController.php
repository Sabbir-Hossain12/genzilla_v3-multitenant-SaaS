<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlatformPlanController extends Controller
{
    public function index()
    {
        $plans = PlatformPlan::withCount('subscriptions')->get();
        return view('platform_admin.pages.plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:platform_plans,slug',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'stripe_monthly_price_id' => 'nullable|string|max:255',
            'stripe_yearly_price_id' => 'nullable|string|max:255',
            'max_products' => 'required|integer|min:1',
            'max_staff_accounts' => 'required|integer|min:1',
            'max_storage_mb' => 'required|integer|min:1',
            'max_digital_file_size_mb' => 'required|integer|min:1',
            'allow_custom_domain' => 'nullable|boolean',
            'has_multi_currency' => 'nullable|boolean',
            'max_currencies_supported' => 'required|integer|min:1',
            'has_advanced_reporting' => 'nullable|boolean',
            'has_custom_rbac' => 'nullable|boolean',
            'has_audit_logs' => 'nullable|boolean',
            'status' => 'nullable|integer',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['allow_custom_domain'] = $request->has('allow_custom_domain');
        $validated['has_multi_currency'] = $request->has('has_multi_currency');
        $validated['has_advanced_reporting'] = $request->has('has_advanced_reporting');
        $validated['has_custom_rbac'] = $request->has('has_custom_rbac');
        $validated['has_audit_logs'] = $request->has('has_audit_logs');
        $validated['status'] = $request->has('status') ? 1 : 0;

        PlatformPlan::create($validated);

        return redirect()->back()->with('success', 'Platform plan created successfully.');
    }

    public function update(Request $request, PlatformPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:platform_plans,slug,' . $plan->id,
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'stripe_monthly_price_id' => 'nullable|string|max:255',
            'stripe_yearly_price_id' => 'nullable|string|max:255',
            'max_products' => 'required|integer|min:1',
            'max_staff_accounts' => 'required|integer|min:1',
            'max_storage_mb' => 'required|integer|min:1',
            'max_digital_file_size_mb' => 'required|integer|min:1',
            'allow_custom_domain' => 'nullable|boolean',
            'has_multi_currency' => 'nullable|boolean',
            'max_currencies_supported' => 'required|integer|min:1',
            'has_advanced_reporting' => 'nullable|boolean',
            'has_custom_rbac' => 'nullable|boolean',
            'has_audit_logs' => 'nullable|boolean',
            'status' => 'nullable|integer',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['allow_custom_domain'] = $request->has('allow_custom_domain');
        $validated['has_multi_currency'] = $request->has('has_multi_currency');
        $validated['has_advanced_reporting'] = $request->has('has_advanced_reporting');
        $validated['has_custom_rbac'] = $request->has('has_custom_rbac');
        $validated['has_audit_logs'] = $request->has('has_audit_logs');
        $validated['status'] = $request->has('status') ? 1 : 0;

        $plan->update($validated);

        return redirect()->back()->with('success', 'Platform plan updated successfully.');
    }

    public function destroy(PlatformPlan $plan)
    {
        if ($plan->subscriptions()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete plan with active tenant subscriptions.');
        }

        $plan->delete();
        return redirect()->back()->with('success', 'Platform plan deleted successfully.');
    }
}
