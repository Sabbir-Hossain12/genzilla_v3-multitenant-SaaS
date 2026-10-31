<?php

namespace App\Http\Controllers\Store\Admin\Payment;

use App\Http\Controllers\Controller;
use App\Models\TenantPaymentMethod;
use Illuminate\Http\Request;

class StorePaymentMethodController extends Controller
{
    public function paymentMethods(Request $request)
    {
        $tenantId = tenantId();
        $supported = TenantPaymentMethod::supportedGateways();

        // Ensure default records exist for this tenant so they can immediately toggle/configure
        foreach ($supported as $key => $meta) {
            TenantPaymentMethod::firstOrCreate(
                ['tenant_id' => $tenantId, 'provider' => $key],
                [
                    'type' => $meta['type'],
                    'title' => $meta['title'],
                    'instructions' => $meta['type'] === 'manual'
                        ? "Please complete payment to our {$meta['title']} account and enter your sender number and Transaction ID (TrxID) below."
                        : ($key === 'cod' ? 'Cash will be collected by the delivery rider upon medicine package delivery.' : null),
                    'credentials' => [],
                    'is_sandbox' => true,
                    'is_active' => $key === 'cod', // COD on by default, others off
                    'sort_order' => match ($meta['type']) {
                        'cash' => 1,
                        'manual' => 2,
                        'automated' => 3,
                        default => 4,
                    },
                ]
            );
        }

        $query = TenantPaymentMethod::where('tenant_id', $tenantId)->orderBy('sort_order')->orderBy('id');

        if ($request->filled('type') && in_array($request->type, ['cash', 'manual', 'automated'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $methods = $query->get();

        $stats = [
            'total' => TenantPaymentMethod::where('tenant_id', $tenantId)->count(),
            'active' => TenantPaymentMethod::where('tenant_id', $tenantId)->where('is_active', true)->count(),
            'cash' => TenantPaymentMethod::where('tenant_id', $tenantId)->where('type', 'cash')->where('is_active', true)->count(),
            'manual' => TenantPaymentMethod::where('tenant_id', $tenantId)->where('type', 'manual')->where('is_active', true)->count(),
            'automated' => TenantPaymentMethod::where('tenant_id', $tenantId)->where('type', 'automated')->where('is_active', true)->count(),
        ];

        return view('store_admin.pages.payments.index', compact('methods', 'stats', 'supported'));
    }

    public function updatePaymentMethod(Request $request, int $id)
    {
        $tenantId = tenantId();
        $method = TenantPaymentMethod::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'instructions' => ['nullable', 'string', 'max:3000'],
            'is_sandbox' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'credentials' => ['nullable', 'array'],
        ]);

        $submittedCreds = $request->input('credentials', []);
        $existingCreds = $method->credentials ?? [];

        // Preserve sensitive credentials if the user submitted masked asterisks
        $cleanCreds = [];
        if (is_array($submittedCreds)) {
            foreach ($submittedCreds as $key => $val) {
                if (is_string($val) && (str_contains($val, '••••') || str_contains($val, '****'))) {
                    // Retain old value
                    $cleanCreds[$key] = $existingCreds[$key] ?? '';
                } else {
                    $cleanCreds[$key] = is_string($val) ? trim($val) : $val;
                }
            }
        }

        $isActive = $request->has('is_active') ? $request->boolean('is_active') : $method->is_active;
        $isSandbox = $request->has('is_sandbox') ? $request->boolean('is_sandbox') : $method->is_sandbox;

        $method->update([
            'title' => $validated['title'],
            'instructions' => $validated['instructions'] ?? null,
            'credentials' => $cleanCreds,
            'is_sandbox' => $isSandbox,
            'is_active' => $isActive,
            'sort_order' => $validated['sort_order'] ?? $method->sort_order,
        ]);

        return redirect()->route('store.admin.payments.index')->with('success', "Payment method '{$method->title}' updated successfully.");
    }

    public function toggleStatus(int $id)
    {
        $tenantId = tenantId();
        $method = TenantPaymentMethod::where('tenant_id', $tenantId)->findOrFail($id);

        $method->is_active = !$method->is_active;
        $method->save();

        $status = $method->is_active ? 'enabled' : 'disabled';
        return redirect()->route('store.admin.payments.index')->with('success', "Payment method '{$method->title}' {$status} successfully.");
    }
}
