<?php

namespace App\Http\Controllers\Store\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\TenantPrescription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StoreAdminDashboardController extends Controller
{
    public function index()
    {
        $tenantId = tenantId();
        $today = now()->startOfDay();

        // 1. Primary Operational KPIs
        $totalRevenue = (float) Order::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');

        $todayRevenue = (float) Order::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $today)
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');

        $totalOrders = Order::where('tenant_id', $tenantId)->count();
        $todayOrders = Order::where('tenant_id', $tenantId)->where('created_at', '>=', $today)->count();
        $pendingOrders = Order::where('tenant_id', $tenantId)->where('status', 'pending')->count();
        $processingOrders = Order::where('tenant_id', $tenantId)->whereIn('status', ['processing', 'confirmed'])->count();

        // Prescriptions queue (pending direct uploads)
        $pendingPrescriptionsCount = TenantPrescription::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'pending_review'])
            ->count();

        // Abandoned recoverable carts
        $recoverableCarts = Cart::where('tenant_id', $tenantId)
            ->whereNotNull('customer_id')
            ->has('items')
            ->where('updated_at', '<=', now()->subHours(2))
            ->whereNull('recovery_contacted_at')
            ->count();

        // Product reviews pending moderation
        $pendingReviewsCount = ProductReview::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        // Total registered customers
        $totalCustomers = Customer::where('tenant_id', $tenantId)->count();

        // 2. Trend Analytics (Last 14 Days)
        $chartLabels = [];
        $chartRevenue = [];
        $chartOrders = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayStart = (clone $date)->startOfDay();
            $dayEnd = (clone $date)->endOfDay();
            $label = $date->format('M d');

            $chartLabels[] = $label;

            $rev = (float) Order::where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->whereNotIn('status', ['cancelled'])
                ->sum('total');
            $chartRevenue[] = round($rev, 2);

            $ordCount = Order::where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->count();
            $chartOrders[] = $ordCount;
        }

        // 3. Orders by Payment Method (Doughnut)
        $paymentMethodsRaw = Order::where('tenant_id', $tenantId)
            ->selectRaw('payment_method, COUNT(*) as count')
            ->groupBy('payment_method')
            ->pluck('count', 'payment_method')
            ->toArray();

        $paymentMethodLabels = [];
        $paymentMethodCounts = [];
        foreach ($paymentMethodsRaw as $method => $count) {
            $paymentMethodLabels[] = strtoupper($method ?: 'COD');
            $paymentMethodCounts[] = $count;
        }

        if (empty($paymentMethodLabels)) {
            $paymentMethodLabels = ['COD', 'bKash', 'Nagad'];
            $paymentMethodCounts = [0, 0, 0];
        }

        // 4. Order Status Distribution (Doughnut)
        $orderStatusesRaw = Order::where('tenant_id', $tenantId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusLabels = ['Delivered', 'Processing', 'Pending', 'Shipped', 'Cancelled'];
        $statusCounts = [
            $orderStatusesRaw['delivered'] ?? 0,
            ($orderStatusesRaw['processing'] ?? 0) + ($orderStatusesRaw['confirmed'] ?? 0),
            $orderStatusesRaw['pending'] ?? 0,
            $orderStatusesRaw['shipped'] ?? 0,
            $orderStatusesRaw['cancelled'] ?? 0,
        ];

        // 5. Recent Orders Feed (latest 6)
        $recentOrders = Order::where('tenant_id', $tenantId)
            ->with(['customer'])
            ->latest('id')
            ->limit(6)
            ->get();

        // 6. Pending Prescriptions Needing Pharmacist Action (latest 4)
        $recentPrescriptions = TenantPrescription::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'pending_review'])
            ->with(['customer'])
            ->latest('id')
            ->limit(4)
            ->get();

        // 7. Critical Inventory Reorder Watchlist (available stock <= 10)
        $variants = ProductVariant::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('track_inventory', true)
            ->whereHas('product', fn($q) => $q->whereNull('deleted_at')->where('status', 'active'))
            ->with(['product.category', 'batches'])
            ->get();

        $allLowStockVariants = $variants->filter(function ($variant) {
            return $variant->available_stock <= 10;
        })->sortBy('available_stock');

        $lowStockCount = $allLowStockVariants->count();
        $lowStockVariants = $allLowStockVariants->take(5);

        // 8. Pending Customer Reviews (latest 4)
        $recentReviews = ProductReview::where('tenant_id', $tenantId)
            ->with(['product', 'customer'])
            ->latest('id')
            ->limit(4)
            ->get();

        return view('store_admin.pages.dashboard.dashboard', compact(
            'totalRevenue',
            'todayRevenue',
            'totalOrders',
            'todayOrders',
            'pendingOrders',
            'processingOrders',
            'pendingPrescriptionsCount',
            'recoverableCarts',
            'pendingReviewsCount',
            'totalCustomers',
            'chartLabels',
            'chartRevenue',
            'chartOrders',
            'paymentMethodLabels',
            'paymentMethodCounts',
            'statusLabels',
            'statusCounts',
            'recentOrders',
            'recentPrescriptions',
            'lowStockCount',
            'lowStockVariants',
            'recentReviews'
        ));
    }

    public function dashboard()
    {
        return $this->index();
    }
}
