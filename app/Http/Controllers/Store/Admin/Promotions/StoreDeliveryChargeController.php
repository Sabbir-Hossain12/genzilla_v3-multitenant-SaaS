<?php

namespace App\Http\Controllers\Store\Admin\Promotions;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreDeliveryChargeController extends Controller
{
    public function deliveryCharges()
    {
        $tenantId = tenantId();

        $deliveryCharges = DeliveryCharge::where('tenant_id', $tenantId)
            ->withCount('orders')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('store_admin.pages.promotions.delivery_charges', compact('deliveryCharges'));
    }

    public function storeDeliveryCharge(Request $request)
    {
        $tenantId = tenantId();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:500'],
            'charge_amount' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'estimated_days' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'boolean'],
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        $uniqueSlug = $slug;
        $counter = 1;
        while (DeliveryCharge::where('tenant_id', $tenantId)->where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = "{$slug}-{$counter}";
            $counter++;
        }

        $validated['tenant_id'] = $tenantId;
        $validated['slug'] = $uniqueSlug;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['status'] = $request->boolean('status', true);

        DeliveryCharge::create($validated);

        return back()->with('success', "Delivery option '{$validated['title']}' created successfully.");
    }

    public function updateDeliveryCharge(Request $request, int $id)
    {
        $tenantId = tenantId();
        $deliveryCharge = DeliveryCharge::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:500'],
            'charge_amount' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'estimated_days' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'boolean'],
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        $uniqueSlug = $slug;
        $counter = 1;
        while (DeliveryCharge::where('tenant_id', $tenantId)->where('slug', $uniqueSlug)->where('id', '!=', $deliveryCharge->id)->exists()) {
            $uniqueSlug = "{$slug}-{$counter}";
            $counter++;
        }

        $validated['slug'] = $uniqueSlug;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['status'] = $request->boolean('status');

        $deliveryCharge->update($validated);

        return back()->with('success', "Delivery option '{$deliveryCharge->title}' updated successfully.");
    }

    public function deleteDeliveryCharge(int $id)
    {
        $deliveryCharge = DeliveryCharge::where('tenant_id', tenantId())->findOrFail($id);
        $title = $deliveryCharge->title;
        $deliveryCharge->delete();

        return back()->with('success', "Delivery option '{$title}' deleted.");
    }

    public function toggleStatus(int $id)
    {
        $deliveryCharge = DeliveryCharge::where('tenant_id', tenantId())->findOrFail($id);
        $deliveryCharge->update(['status' => !$deliveryCharge->status]);

        $statusText = $deliveryCharge->status ? 'activated' : 'deactivated';
        return back()->with('success', "Delivery option '{$deliveryCharge->title}' {$statusText}.");
    }
}
