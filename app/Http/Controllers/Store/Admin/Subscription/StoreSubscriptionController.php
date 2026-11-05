<?php

namespace App\Http\Controllers\Store\Admin\Subscription;

use App\Http\Controllers\Controller;
use App\Models\PlatformPlan;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantSubscription;
use App\Models\TenantUsage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StoreSubscriptionController extends Controller
{
    private function tenantId(): int
    {
        return (int) (auth()->user()->tenant->id ?? 0);
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();
        $tenant = Tenant::with([
            'domains',
            'activeSubscription.plan',
            'currentSubscription.plan',
            'usage',
        ])->findOrFail($tenantId);

        // 1. Resolve current active or latest subscription
        $subscription = $tenant->activeSubscription
            ?? $tenant->currentSubscription
            ?? $tenant->subscriptions()->with('plan')->latest('id')->first();

        // Current Plan
        $currentPlan = $subscription?->plan ?? $tenant->currentSubscription?->plan;

        // 2. Billing details & Status
        $rawStatus = $subscription?->subscription_status ?? $tenant->subscription_status ?? 'trialing';
        $billingInterval = $subscription?->billing_interval ?? 'monthly';
        $periodEndsAt = $subscription?->current_period_ends_at;
        $trialEndsAt = $subscription?->trial_ends_at;
        $canceledAt = $subscription?->canceled_at;

        // Expiry calculation & status badge determination
        $now = now();
        $isExpiringSoon = false;
        $daysRemaining = null;

        if ($periodEndsAt) {
            $daysRemaining = (int) ceil($now->diffInHours($periodEndsAt, false) / 24);
            if ($daysRemaining >= 0 && $daysRemaining <= 7 && $rawStatus === 'active') {
                $isExpiringSoon = true;
            }
        } elseif ($trialEndsAt) {
            $daysRemaining = (int) ceil($now->diffInHours($trialEndsAt, false) / 24);
        }

        // Status badge label and styling color
        if ($canceledAt || $rawStatus === 'cancelled') {
            $statusLabel = 'Cancelled';
            $statusColor = 'red';
        } elseif ($rawStatus === 'past_due') {
            $statusLabel = 'Past Due';
            $statusColor = 'rose';
        } elseif ($rawStatus === 'expired') {
            $statusLabel = 'Expired';
            $statusColor = 'slate';
        } elseif ($isExpiringSoon) {
            $statusLabel = 'Expiring Soon';
            $statusColor = 'amber';
        } elseif ($rawStatus === 'trialing') {
            $statusLabel = 'Trial Active';
            $statusColor = 'blue';
        } else {
            $statusLabel = 'Active';
            $statusColor = 'emerald';
        }

        // Pricing calculation
        $currentPrice = 0.00;
        if ($currentPlan) {
            $currentPrice = $billingInterval === 'yearly'
                ? (float) $currentPlan->yearly_price
                : (float) $currentPlan->monthly_price;
        }

        // 3. Live Resource Usage & Quota meters
        // Storage Calculation (scan real uploads if present or maintain from tenant_usages)
        $storageBytes = 0;
        $tenantStoragePath = storage_path('app/public/tenants/' . $tenantId);
        if (file_exists($tenantStoragePath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tenantStoragePath, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $storageBytes += $file->getSize();
                }
            }
        }

        // Staff count
        $staffCount = User::whereHas('roles', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->count();
        if ($staffCount === 0) {
            $staffCount = 1;
        }

        // Products count
        $productsCount = Product::where('tenant_id', $tenantId)->count();

        // Update / sync usage metrics
        $usage = $tenant->usage ?: TenantUsage::firstOrCreate(['tenant_id' => $tenantId], [
            'storage_used_bytes' => $storageBytes,
            'staff_accounts_count' => $staffCount,
            'products_count' => $productsCount,
        ]);

        if ($usage->storage_used_bytes !== $storageBytes) {
            $usage->update(['storage_used_bytes' => $storageBytes]);
        }
        if ($usage->staff_accounts_count !== $staffCount) {
            $usage->update(['staff_accounts_count' => $staffCount]);
        }
        if ($usage->products_count !== $productsCount) {
            $usage->update(['products_count' => $productsCount]);
        }

        // Limits from plan
        $maxStorageMB = $currentPlan?->max_storage_mb ?? 100;
        $maxStaff = $currentPlan?->max_staff_accounts ?? 1;
        $maxProducts = $currentPlan?->max_products ?? 50;

        $storageUsedMB = round($usage->storage_used_bytes / (1024 * 1024), 2);
        $storagePct = min(100, (int) round(($storageUsedMB / max(1, $maxStorageMB)) * 100));
        $staffPct = min(100, (int) round(($staffCount / max(1, $maxStaff)) * 100));
        $productsPct = min(100, (int) round(($productsCount / max(1, $maxProducts)) * 100));

        // 4. Integrated Domain Status
        $primaryDomain = $tenant->domains()->where('is_primary', true)->first();
        if (! $primaryDomain) {
            $baseDomain = config('app.base_domain', 'genzilla.com');
            $primaryDomain = (object) [
                'domain' => "{$tenant->subdomain}.{$baseDomain}",
                'is_custom' => false,
                'is_primary' => true,
                'ssl_status' => 'active',
                'dns_verified' => true,
                'status' => 'active',
            ];
        }
        $allowsCustomDomain = $tenant->allowsCustomDomain();

        // 5. Available Platform Plans
        $availablePlans = PlatformPlan::where('status', 1)->orderBy('monthly_price')->get();

        // 6. Invoices / Billing History
        $invoices = TenantInvoice::where('tenant_id', $tenantId)
            ->with(['plan', 'subscription.plan'])
            ->latest()
            ->paginate(10);

        return view('store_admin.pages.subscription.index', compact(
            'tenant',
            'subscription',
            'currentPlan',
            'billingInterval',
            'rawStatus',
            'statusLabel',
            'statusColor',
            'periodEndsAt',
            'trialEndsAt',
            'canceledAt',
            'daysRemaining',
            'isExpiringSoon',
            'currentPrice',
            'usage',
            'storageUsedMB',
            'maxStorageMB',
            'storagePct',
            'staffCount',
            'maxStaff',
            'staffPct',
            'productsCount',
            'maxProducts',
            'productsPct',
            'primaryDomain',
            'allowsCustomDomain',
            'availablePlans',
            'invoices'
        ));
    }

    public function changePlan(Request $request)
    {
        $tenantId = $this->tenantId();
        $tenant = Tenant::findOrFail($tenantId);

        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:platform_plans,id'],
            'billing_interval' => ['required', 'string', 'in:monthly,yearly'],
        ]);

        $newPlan = PlatformPlan::where('status', 1)->findOrFail($validated['plan_id']);

        $subscription = $tenant->assignManualSubscription(
            plan: $newPlan,
            billingInterval: $validated['billing_interval'],
            subscriptionStatus: 'active',
            createInvoice: true
        );

        return redirect()->route('store.admin.subscription.index')
            ->with('success', "Your plan has been updated to {$newPlan->name} (" . ucfirst($validated['billing_interval']) . ")! Your new limits and features are active immediately.");
    }

    public function cancel(Request $request)
    {
        $tenantId = $this->tenantId();
        $tenant = Tenant::findOrFail($tenantId);

        $subscription = $tenant->activeSubscription ?? $tenant->currentSubscription;
        if (! $subscription) {
            return back()->with('error', 'No active subscription found to cancel.');
        }

        $subscription->update([
            'canceled_at' => now(),
            'subscription_status' => 'cancelled',
        ]);

        $tenant->update(['subscription_status' => 'cancelled']);

        return redirect()->route('store.admin.subscription.index')
            ->with('success', 'Your subscription auto-renewal has been cancelled. Your store services will remain accessible until the end of your billing period.');
    }

    public function resume(Request $request)
    {
        $tenantId = $this->tenantId();
        $tenant = Tenant::findOrFail($tenantId);

        $subscription = $tenant->subscriptions()->latest('id')->first();
        if (! $subscription) {
            return back()->with('error', 'No subscription found to resume.');
        }

        $subscription->update([
            'canceled_at' => null,
            'subscription_status' => 'active',
        ]);

        $tenant->update(['subscription_status' => 'active']);

        return redirect()->route('store.admin.subscription.index')
            ->with('success', 'Your subscription has been resumed successfully!');
    }

    public function showInvoice(int $id)
    {
        $tenantId = $this->tenantId();
        $invoice = TenantInvoice::where('tenant_id', $tenantId)
            ->with(['tenant', 'plan', 'subscription.plan'])
            ->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'invoice_number' => $invoice->invoice_number,
                'created_at' => $invoice->created_at ? $invoice->created_at->format('M d, Y') : '-',
                'period_start' => $invoice->billing_period_start ? $invoice->billing_period_start->format('M d, Y') : null,
                'period_end' => $invoice->billing_period_end ? $invoice->billing_period_end->format('M d, Y') : null,
                'plan_name' => $invoice->plan_title,
                'billing_interval' => ucfirst($invoice->cycle),
                'subtotal' => number_format((float) $invoice->subtotal, 2),
                'tax' => number_format((float) $invoice->tax, 2),
                'total' => number_format((float) $invoice->total, 2),
                'currency' => $invoice->currency,
                'status' => $invoice->status,
                'paid_at' => $invoice->paid_at ? $invoice->paid_at->format('M d, Y') : null,
                'store_name' => $invoice->tenant->store_name ?? 'Store',
                'subdomain' => $invoice->tenant->subdomain ?? '',
            ]);
        }

        return view('store_admin.pages.subscription.invoice', compact('invoice'));
    }
}
