<?php

namespace App\Http\Controllers\Store\Admin\Promotions;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreCouponController extends Controller
{
    public function coupons(Request $request)
    {
        $tenantId = tenantId();

        $coupons = Coupon::where('tenant_id', $tenantId)
            ->withCount('orders')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('code', 'like', '%' . trim($request->search) . '%');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('store_admin.pages.promotions.coupons', compact('coupons'));
    }

    public function storeCoupon(Request $request)
    {
        $tenantId = tenantId();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->where('tenant_id', $tenantId)],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_customer' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);

        Coupon::create($validated);

        return back()->with('success', "Coupon '{$validated['code']}' created successfully.");
    }

    public function updateCoupon(Request $request, int $id)
    {
        $tenantId = tenantId();
        $coupon = Coupon::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->where('tenant_id', $tenantId)->ignore($coupon->id)],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_customer' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active');

        $coupon->update($validated);

        return back()->with('success', "Coupon '{$coupon->code}' updated successfully.");
    }

    public function deleteCoupon(int $id)
    {
        $coupon = Coupon::where('tenant_id', tenantId())->findOrFail($id);
        $code = $coupon->code;
        $coupon->delete();

        return back()->with('success', "Coupon '{$code}' deleted.");
    }

    public function toggleStatus(int $id)
    {
        $coupon = Coupon::where('tenant_id', tenantId())->findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);

        $statusText = $coupon->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Coupon '{$coupon->code}' {$statusText}.");
    }
}
