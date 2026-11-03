<?php

namespace App\Http\Controllers\Store\Admin\Report;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Tenant;
use App\Models\TenantPrescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StoreReportController extends Controller
{
    /**
     * Report index and interactive preview
     */
    public function index(Request $request)
    {
        $tenantId = tenantId();
        $type = $request->input('type', 'sales');

        $reportData = $this->buildReportData($request, $type, 30);

        // Overall stats for report overview cards
        $stats = [
            'total_sales' => (float) Order::where('tenant_id', $tenantId)->whereNotIn('status', ['cancelled'])->sum('total'),
            'total_orders' => Order::where('tenant_id', $tenantId)->count(),
            'total_skus' => ProductVariant::where('tenant_id', $tenantId)->count(),
            'total_prescriptions' => TenantPrescription::where('tenant_id', $tenantId)->count(),
            'total_abandoned' => Cart::where('tenant_id', $tenantId)->has('items')->count(),
            'total_reviews' => ProductReview::where('tenant_id', $tenantId)->count(),
        ];

        return view('store_admin.pages.reports.index', compact('reportData', 'type', 'stats'));
    }

    /**
     * Export report data as Excel-compatible CSV with UTF-8 BOM
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $type = $request->input('type', 'sales');
        $reportData = $this->buildReportData($request, $type, null); // null limit = all records

        $filename = 'report-' . $type . '-' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($reportData) {
            $handle = fopen('php://output', 'w');

            // Write UTF-8 BOM for Microsoft Excel
            fputs($handle, "\xEF\xBB\xBF");

            // Meta banner
            fputcsv($handle, ['REPORT TYPE', $reportData['title']]);
            fputcsv($handle, ['GENERATED AT', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, ['DATE RANGE', $reportData['filters']['start_date'] . ' to ' . $reportData['filters']['end_date']]);
            fputcsv($handle, []);

            // Summary metrics
            fputcsv($handle, ['SUMMARY METRICS']);
            foreach ($reportData['summary'] as $label => $value) {
                fputcsv($handle, [ucwords(str_replace('_', ' ', $label)), $value]);
            }
            fputcsv($handle, []);

            // Table headers
            fputcsv($handle, $reportData['headers']);

            // Table rows
            foreach ($reportData['rows'] as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export report data as PDF document
     */
    public function exportPdf(Request $request)
    {
        $tenantId = tenantId();
        $type = $request->input('type', 'sales');
        $reportData = $this->buildReportData($request, $type, 150); // limit for crisp PDF layout

        $tenant = Tenant::find($tenantId);

        $pdf = Pdf::loadView('store_admin.pages.reports.pdf', [
            'reportData' => $reportData,
            'tenant' => $tenant,
            'type' => $type,
        ]);

        $pdf->setPaper('a4', 'landscape');

        $filename = 'report-' . $type . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Unified Report Data Builder
     */
    private function buildReportData(Request $request, string $type, ?int $limit = null): array
    {
        $tenantId = tenantId();

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfDay();

        switch ($type) {
            case 'inventory':
                return $this->buildInventoryReport($tenantId, $request, $limit);

            case 'prescriptions':
                return $this->buildPrescriptionReport($tenantId, $request, $startDate, $endDate, $limit);

            case 'transactions':
                return $this->buildTransactionReport($tenantId, $request, $startDate, $endDate, $limit);

            case 'abandoned_carts':
                return $this->buildAbandonedCartReport($tenantId, $request, $startDate, $endDate, $limit);

            case 'reviews':
                return $this->buildReviewsReport($tenantId, $request, $startDate, $endDate, $limit);

            case 'sales':
            default:
                return $this->buildSalesReport($tenantId, $request, $startDate, $endDate, $limit);
        }
    }

    /**
     * 1. Sales & Orders Report
     */
    private function buildSalesReport($tenantId, Request $request, $startDate, $endDate, ?int $limit): array
    {
        $query = Order::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['customer']);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_status') && $request->input('payment_status') !== 'all') {
            $query->where('payment_status', $request->input('payment_status'));
        }

        $allMatching = (clone $query)->get();

        $summary = [
            'total_orders' => $allMatching->count(),
            'total_gross_revenue' => '৳ ' . number_format($allMatching->whereNotIn('status', ['cancelled'])->sum('total'), 2),
            'total_subtotal' => '৳ ' . number_format($allMatching->sum('subtotal'), 2),
            'total_discount' => '৳ ' . number_format($allMatching->sum('discount_amount'), 2),
            'total_shipping' => '৳ ' . number_format($allMatching->sum('shipping_charge'), 2),
            'paid_orders' => $allMatching->where('payment_status', 'paid')->count(),
            'pending_orders' => $allMatching->where('status', 'pending')->count(),
            'cancelled_orders' => $allMatching->where('status', 'cancelled')->count(),
        ];

        $headers = [
            'Order #',
            'Date',
            'Customer Name',
            'Phone',
            'Order Status',
            'Payment Method',
            'Payment Status',
            'Subtotal (৳)',
            'Discount (৳)',
            'Delivery (৳)',
            'Total (৳)',
        ];

        $rowsQuery = $query->latest('id');
        if ($limit) {
            $rowsQuery->limit($limit);
        }
        $orders = $rowsQuery->get();

        $rows = [];
        foreach ($orders as $o) {
            $customerName = $o->shipping_name ?? $o->customer?->name ?? 'Guest Customer';
            $customerPhone = $o->shipping_phone ?? $o->customer?->phone ?? '-';

            $rows[] = [
                $o->order_number,
                $o->created_at->format('Y-m-d H:i'),
                $customerName,
                $customerPhone,
                ucfirst($o->status),
                strtoupper($o->payment_method ?? 'COD'),
                ucfirst($o->payment_status ?? 'unpaid'),
                number_format($o->subtotal, 2),
                number_format($o->discount_amount, 2),
                number_format($o->shipping_charge, 2),
                number_format($o->total, 2),
            ];
        }

        return [
            'title' => 'Sales & Orders Performance Report',
            'description' => 'Detailed ledger of customer orders, revenue, discount redemptions, and settlement status.',
            'headers' => $headers,
            'rows' => $rows,
            'summary' => $summary,
            'total_count' => $allMatching->count(),
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'status' => $request->input('status', 'all'),
                'payment_status' => $request->input('payment_status', 'all'),
            ],
        ];
    }

    /**
     * 2. Inventory Valuation & Stock Health Report
     */
    private function buildInventoryReport($tenantId, Request $request, ?int $limit): array
    {
        $query = ProductVariant::where('tenant_id', $tenantId)
            ->with(['product.category', 'product.brand', 'batches']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $allVariants = $query->get();

        // Calculate metrics
        $totalValuation = 0;
        $totalUnits = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;

        foreach ($allVariants as $var) {
            $available = $var->available_stock;
            $sellingPrice = (float) $var->selling_price;
            $totalValuation += ($available * $sellingPrice);
            $totalUnits += $available;

            if ($available === 0) {
                $outOfStockCount++;
            } elseif ($available <= 10) {
                $lowStockCount++;
            }
        }

        // Apply stock status filter if specified
        if ($request->filled('stock_status') && $request->input('stock_status') !== 'all') {
            $status = $request->input('stock_status');
            $allVariants = $allVariants->filter(function ($var) use ($status) {
                $avail = $var->available_stock;
                if ($status === 'out_of_stock') return $avail === 0;
                if ($status === 'low_stock') return $avail > 0 && $avail <= 10;
                if ($status === 'in_stock') return $avail > 10;
                return true;
            });
        }

        $summary = [
            'total_variants' => $allVariants->count(),
            'total_units_in_stock' => number_format($totalUnits),
            'total_inventory_valuation' => '৳ ' . number_format($totalValuation, 2),
            'low_stock_items' => $lowStockCount,
            'out_of_stock_items' => $outOfStockCount,
        ];

        $headers = [
            'Product Name',
            'SKU',
            'Category',
            'Brand',
            'Unit Price (৳)',
            'Available Stock',
            'Reserved Stock',
            'Total On Hand',
            'Stock Valuation (৳)',
            'Stock Status',
        ];

        $displayed = $limit ? $allVariants->take($limit) : $allVariants;

        $rows = [];
        foreach ($displayed as $var) {
            $available = $var->available_stock;
            $reserved = $var->reserved_stock;
            $onHand = $var->stock_on_hand;
            $price = (float) $var->selling_price;
            $val = $available * $price;

            $statusText = $available === 0 ? 'Out of Stock' : ($available <= 10 ? 'Low Stock' : 'In Stock');

            $rows[] = [
                $var->product?->name ?? 'N/A',
                $var->sku,
                $var->product?->category?->name ?? 'Uncategorized',
                $var->product?->brand?->name ?? 'N/A',
                number_format($price, 2),
                $available,
                $reserved,
                $onHand,
                number_format($val, 2),
                $statusText,
            ];
        }

        return [
            'title' => 'Inventory Stock Valuation & Health Report',
            'description' => 'Real-time catalog inventory levels, batch allocations, stock reorder thresholds, and current financial valuation.',
            'headers' => $headers,
            'rows' => $rows,
            'summary' => $summary,
            'total_count' => $allVariants->count(),
            'filters' => [
                'stock_status' => $request->input('stock_status', 'all'),
                'search' => $request->input('search', ''),
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ],
        ];
    }

    /**
     * 3. Prescriptions Compliance Report
     */
    private function buildPrescriptionReport($tenantId, Request $request, $startDate, $endDate, ?int $limit): array
    {
        $query = TenantPrescription::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['customer', 'order', 'reviewedBy']);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $statusVal = $request->input('status');
            if ($statusVal === 'pending') {
                $query->whereIn('status', ['pending', 'pending_review']);
            } elseif ($statusVal === 'converted') {
                $query->whereIn('status', ['converted', 'converted_to_order']);
            } else {
                $query->where('status', $statusVal);
            }
        }

        $allPrescriptions = (clone $query)->get();

        $summary = [
            'total_prescriptions' => $allPrescriptions->count(),
            'pending_review' => $allPrescriptions->filter(fn($rx) => in_array($rx->status, ['pending', 'pending_review']))->count(),
            'converted_to_order' => $allPrescriptions->filter(fn($rx) => in_array($rx->status, ['converted', 'converted_to_order']))->count(),
            'rejected' => $allPrescriptions->where('status', 'rejected')->count(),
        ];

        $headers = [
            'Prescription #',
            'Date Uploaded',
            'Customer Name',
            'Phone',
            'Status',
            'Linked Order #',
            'Reviewed By',
            'Reviewed At',
            'Customer Notes',
        ];

        $rowsQuery = $query->latest('id');
        if ($limit) {
            $rowsQuery->limit($limit);
        }
        $prescriptions = $rowsQuery->get();

        $rows = [];
        foreach ($prescriptions as $rx) {
            $rows[] = [
                $rx->prescription_number,
                $rx->created_at->format('Y-m-d H:i'),
                $rx->customer?->name ?? 'Guest Patient',
                $rx->customer?->phone ?? '-',
                ucfirst($rx->status),
                $rx->order?->order_number ?? 'None',
                $rx->reviewedBy?->name ?? 'Unassigned',
                $rx->reviewed_at ? $rx->reviewed_at->format('Y-m-d H:i') : '-',
                $rx->customer_notes ?? '-',
            ];
        }

        return [
            'title' => 'Prescriptions Compliance & Verification Report',
            'description' => 'Audit log of patient-uploaded prescriptions, pharmacist review activities, and conversion metrics.',
            'headers' => $headers,
            'rows' => $rows,
            'summary' => $summary,
            'total_count' => $allPrescriptions->count(),
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'status' => $request->input('status', 'all'),
            ],
        ];
    }

    /**
     * 4. Financial Ledger & Transactions Report
     */
    private function buildTransactionReport($tenantId, Request $request, $startDate, $endDate, ?int $limit): array
    {
        $query = OrderPayment::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['order.customer']);

        $txType = $request->input('type') ?? $request->input('type_filter');
        if (!empty($txType) && $txType !== 'all') {
            $query->where('type', $txType);
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_method') && $request->input('payment_method') !== 'all') {
            $query->where('payment_method', $request->input('payment_method'));
        }

        $allTx = (clone $query)->get();

        $grossCharges = (float) $allTx->where('type', 'charge')->where('status', 'successful')->sum('amount');
        $refunds = (float) $allTx->where('type', 'refund')->where('status', 'successful')->sum('amount');
        $netCaptured = max(0, $grossCharges - $refunds);

        $summary = [
            'total_transactions' => $allTx->count(),
            'gross_collected' => '৳ ' . number_format($grossCharges, 2),
            'refunded_amount' => '৳ ' . number_format($refunds, 2),
            'net_settled_revenue' => '৳ ' . number_format($netCaptured, 2),
            'successful_count' => $allTx->where('status', 'successful')->count(),
            'pending_count' => $allTx->where('status', 'pending')->count(),
        ];

        $headers = [
            'Transaction ID',
            'Date',
            'Type',
            'Order #',
            'Customer Name',
            'Payment Gateway',
            'Status',
            'Paid At',
            'Amount (৳)',
        ];

        $rowsQuery = $query->latest('id');
        if ($limit) {
            $rowsQuery->limit($limit);
        }
        $transactions = $rowsQuery->get();

        $rows = [];
        foreach ($transactions as $tx) {
            $amountFormatted = ($tx->type === 'refund' ? '-' : '') . number_format($tx->amount, 2);

            $rows[] = [
                $tx->transaction_id,
                $tx->created_at->format('Y-m-d H:i'),
                strtoupper($tx->type),
                $tx->order?->order_number ?? 'N/A',
                $tx->customer_name,
                strtoupper($tx->payment_method),
                ucfirst($tx->status),
                $tx->paid_at ? $tx->paid_at->format('Y-m-d H:i') : 'Unpaid',
                $amountFormatted,
            ];
        }

        return [
            'title' => 'Financial Ledger & Customer Payments Report',
            'description' => 'Comprehensive accounting of customer payment transactions, gateway settlements, and refunds.',
            'headers' => $headers,
            'rows' => $rows,
            'summary' => $summary,
            'total_count' => $allTx->count(),
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'type' => $txType ?: 'all',
                'status' => $request->input('status', 'all'),
                'payment_method' => $request->input('payment_method', 'all'),
            ],
        ];
    }

    /**
     * 5. Abandoned Checkouts Report
     */
    private function buildAbandonedCartReport($tenantId, Request $request, $startDate, $endDate, ?int $limit): array
    {
        $query = Cart::where('tenant_id', $tenantId)
            ->has('items')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->with(['customer', 'items.product', 'items.productVariant']);

        if ($request->input('contact_filter') === 'contacted') {
            $query->whereNotNull('recovery_contacted_at');
        } elseif ($request->input('contact_filter') === 'uncontacted') {
            $query->whereNull('recovery_contacted_at');
        } elseif ($request->input('contact_filter') === 'registered_only') {
            $query->whereNotNull('customer_id');
        }

        $allCarts = (clone $query)->get();

        $totalLostValue = 0;
        foreach ($allCarts as $c) {
            $totalLostValue += $c->items->sum(function ($item) {
                $price = $item->productVariant ? (float) $item->productVariant->selling_price : 0;
                return $price * $item->quantity;
            });
        }

        $summary = [
            'total_abandoned_carts' => $allCarts->count(),
            'recoverable_carts' => $allCarts->whereNotNull('customer_id')->count(),
            'total_abandoned_value' => '৳ ' . number_format($totalLostValue, 2),
            'contacted_by_staff' => $allCarts->whereNotNull('recovery_contacted_at')->count(),
            'uncontacted_count' => $allCarts->whereNull('recovery_contacted_at')->count(),
        ];

        $headers = [
            'Cart ID',
            'Customer Identity',
            'Customer Name',
            'Phone',
            'Email',
            'Items Count',
            'Cart Value (৳)',
            'Abandoned Date',
            'Recovery Status',
            'Staff Note',
        ];

        $rowsQuery = $query->latest('updated_at');
        if ($limit) {
            $rowsQuery->limit($limit);
        }
        $carts = $rowsQuery->get();

        $rows = [];
        foreach ($carts as $c) {
            $cartVal = $c->items->sum(function ($item) {
                $price = $item->productVariant ? (float) $item->productVariant->selling_price : 0;
                return $price * $item->quantity;
            });

            $recoveryStatus = $c->recovery_contacted_at
                ? 'Contacted (' . $c->recovery_contacted_at->format('M d') . ')'
                : 'Pending Follow-up';

            $rows[] = [
                'CART-' . $c->id,
                $c->customer_id ? 'Registered User' : 'Guest Visitor',
                $c->customer?->name ?? 'Guest',
                $c->customer?->phone ?? '-',
                $c->customer?->email ?? '-',
                $c->items->count() . ' items (' . $c->items->sum('quantity') . ' units)',
                number_format($cartVal, 2),
                $c->updated_at->format('Y-m-d H:i'),
                $recoveryStatus,
                $c->recovery_note ?? '-',
            ];
        }

        return [
            'title' => 'Abandoned Checkouts & Recovery Report',
            'description' => 'Tele-sales recovery leads, lost cart valuations, customer outreach status, and conversion blockers.',
            'headers' => $headers,
            'rows' => $rows,
            'summary' => $summary,
            'total_count' => $allCarts->count(),
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'contact_filter' => $request->input('contact_filter', 'all'),
            ],
        ];
    }

    /**
     * 6. Product Reviews & Customer Satisfaction Report
     */
    private function buildReviewsReport($tenantId, Request $request, $startDate, $endDate, ?int $limit): array
    {
        $query = ProductReview::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['product', 'customer']);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('rating') && $request->input('rating') !== 'all') {
            $query->where('rating', (int) $request->input('rating'));
        }

        $allReviews = (clone $query)->get();

        $avgRating = $allReviews->count() > 0 ? round($allReviews->avg('rating'), 1) : 0;

        $summary = [
            'total_reviews' => $allReviews->count(),
            'average_rating' => $avgRating . ' / 5.0 ★',
            'approved_reviews' => $allReviews->where('status', 'approved')->count(),
            'pending_reviews' => $allReviews->where('status', 'pending')->count(),
            'replied_count' => $allReviews->filter(fn($r) => !empty($r->admin_reply))->count(),
        ];

        $headers = [
            'Review ID',
            'Date',
            'Product Name',
            'Customer Name',
            'Rating',
            'Review Title',
            'Comment',
            'Status',
            'Admin Replied',
        ];

        $rowsQuery = $query->latest('id');
        if ($limit) {
            $rowsQuery->limit($limit);
        }
        $reviews = $rowsQuery->get();

        $rows = [];
        foreach ($reviews as $rev) {
            $rows[] = [
                '#REV-' . $rev->id,
                $rev->created_at->format('Y-m-d H:i'),
                $rev->product?->name ?? 'N/A',
                $rev->customer_name,
                $rev->rating . ' Stars',
                $rev->title ?? '-',
                $rev->comment,
                ucfirst($rev->status),
                !empty($rev->admin_reply) ? 'Yes' : 'No',
            ];
        }

        return [
            'title' => 'Product Reviews & Customer Feedback Report',
            'description' => 'Customer ratings, product reviews, moderation status, and pharmacy response tracking.',
            'headers' => $headers,
            'rows' => $rows,
            'summary' => $summary,
            'total_count' => $allReviews->count(),
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'status' => $request->input('status', 'all'),
                'rating' => $request->input('rating', 'all'),
            ],
        ];
    }
}
