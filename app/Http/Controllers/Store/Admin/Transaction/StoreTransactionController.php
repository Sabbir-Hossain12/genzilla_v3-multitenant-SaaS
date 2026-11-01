<?php

namespace App\Http\Controllers\Store\Admin\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StoreTransactionController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = tenantId();

        $query = OrderPayment::where('tenant_id', $tenantId)
            ->with(['order.customer', 'parent', 'refunds']);

        // 1. Status Filter
        $status = $request->input('status', 'all');
        if (in_array($status, ['successful', 'pending', 'failed', 'refunded'])) {
            $query->where('status', $status);
        }

        // 2. Payment Method Filter
        $method = $request->input('payment_method', 'all');
        if ($method !== 'all' && !empty($method)) {
            $query->where('payment_method', $method);
        }

        // 3. Type Filter (charge vs refund)
        $type = $request->input('type', 'all');
        if (in_array($type, ['charge', 'refund'])) {
            $query->where('type', $type);
        }

        // 4. Date Range Filter
        $dateRange = $request->input('date_range', 'all');
        if ($dateRange === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($dateRange === 'last_7_days') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($dateRange === 'this_month') {
            $query->where('created_at', '>=', now()->startOfMonth());
        } elseif ($dateRange === 'last_30_days') {
            $query->where('created_at', '>=', now()->subDays(30));
        } elseif ($dateRange === 'custom') {
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }
        }

        // 5. Search Filter (trx ID, order number, customer name, phone, email)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($orderQuery) use ($search) {
                      $orderQuery->where('order_number', 'like', "%{$search}%")
                                 ->orWhere('shipping_name', 'like', "%{$search}%")
                                 ->orWhere('shipping_phone', 'like', "%{$search}%")
                                 ->orWhere('shipping_email', 'like', "%{$search}%")
                                 ->orWhereHas('customer', function ($customerQuery) use ($search) {
                                     $customerQuery->where('name', 'like', "%{$search}%")
                                                   ->orWhere('phone', 'like', "%{$search}%")
                                                   ->orWhere('email', 'like', "%{$search}%");
                                 });
                  });
            });
        }

        // Financial KPIs & Summaries (scoped to tenant)
        $allPayments = OrderPayment::where('tenant_id', $tenantId)->get();

        $successfulCharges = $allPayments->where('type', 'charge')->where('status', 'successful')->sum('amount');
        $successfulRefunds = $allPayments->where('type', 'refund')->where('status', 'successful')->sum('amount');
        $netRevenue = max(0, (float) $successfulCharges - (float) $successfulRefunds);
        $pendingAmount = $allPayments->where('status', 'pending')->sum('amount');
        $refundedAmount = $allPayments->where('type', 'refund')->sum('amount');

        $statusCounts = [
            'all' => $allPayments->count(),
            'successful' => $allPayments->where('status', 'successful')->count(),
            'pending' => $allPayments->where('status', 'pending')->count(),
            'failed' => $allPayments->where('status', 'failed')->count(),
            'refunded' => $allPayments->where('status', 'refunded')->count() + $allPayments->where('type', 'refund')->count(),
        ];

        // Payment method breakdown counts
        $methodCounts = $allPayments->groupBy('payment_method')->map->count();

        // Paginated results
        $transactions = $query->orderByDesc('id')->paginate(15)->withQueryString();

        // Fetch unpaid or partially paid orders for the "Record Payment" modal
        $unpaidOrders = Order::where('tenant_id', $tenantId)
            ->whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'order_number', 'total', 'payment_status', 'shipping_name', 'payment_method']);

        return view('store_admin.pages.transactions.index', compact(
            'transactions',
            'statusCounts',
            'methodCounts',
            'netRevenue',
            'successfulCharges',
            'pendingAmount',
            'refundedAmount',
            'unpaidOrders',
            'status',
            'method',
            'type',
            'dateRange'
        ));
    }

    public function show(Request $request, int $id)
    {
        $tenantId = tenantId();

        $transaction = OrderPayment::where('tenant_id', $tenantId)
            ->with([
                'order.customer',
                'order.products',
                'parent',
                'refunds.order',
            ])
            ->findOrFail($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'id' => $transaction->id,
                'transaction_id' => $transaction->transaction_id,
                'type' => $transaction->type,
                'amount' => (float) $transaction->amount,
                'currency' => $transaction->currency,
                'payment_method' => $transaction->payment_method,
                'method_label' => $transaction->method_label,
                'method_icon' => $transaction->method_icon,
                'status' => $transaction->status,
                'status_badge_class' => $transaction->status_badge_class,
                'type_badge_class' => $transaction->type_badge_class,
                'paid_at' => $transaction->paid_at?->format('M d, Y h:i A') ?? 'Not Paid',
                'created_at' => $transaction->created_at->format('M d, Y h:i A'),
                'gateway_response' => $transaction->gateway_response,
                'remaining_refundable' => $transaction->remaining_refundable,
                'refunded_amount' => $transaction->refunded_amount,
                'order' => [
                    'id' => $transaction->order?->id,
                    'order_number' => $transaction->order?->order_number,
                    'status' => $transaction->order?->status,
                    'payment_status' => $transaction->order?->payment_status,
                    'total' => (float) ($transaction->order?->total ?? 0),
                    'shipping_name' => $transaction->order?->shipping_name,
                    'shipping_phone' => $transaction->order?->shipping_phone,
                    'shipping_email' => $transaction->order?->shipping_email,
                    'shipping_address' => $transaction->order?->shipping_address,
                ],
                'parent' => $transaction->parent ? [
                    'id' => $transaction->parent->id,
                    'transaction_id' => $transaction->parent->transaction_id,
                    'amount' => (float) $transaction->parent->amount,
                ] : null,
                'refunds' => $transaction->refunds->map(fn ($r) => [
                    'id' => $r->id,
                    'transaction_id' => $r->transaction_id,
                    'amount' => (float) $r->amount,
                    'created_at' => $r->created_at->format('M d, Y h:i A'),
                    'gateway_response' => $r->gateway_response,
                ]),
            ]);
        }

        return view('store_admin.pages.transactions.show', compact('transaction'));
    }

    public function recordManualPayment(Request $request)
    {
        $tenantId = tenantId();

        $validated = $request->validate([
            'order_id' => ['required', 'integer', Rule::exists('orders', 'id')->where('tenant_id', $tenantId)],
            'payment_method' => ['required', Rule::in(['cod', 'card', 'bkash', 'nagad', 'rocket', 'bank_transfer', 'cash'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_id' => ['nullable', 'string', 'max:150'],
            'status' => ['required', Rule::in(['successful', 'pending', 'failed'])],
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = Order::where('tenant_id', $tenantId)->findOrFail($validated['order_id']);

        $payment = DB::transaction(function () use ($tenantId, $order, $validated) {
            $trxId = !empty($validated['transaction_id']) ? $validated['transaction_id'] : 'MANUAL-' . strtoupper(Str::random(10));

            // Ensure unique trx id per tenant & method
            while (OrderPayment::where('tenant_id', $tenantId)->where('payment_method', $validated['payment_method'])->where('transaction_id', $trxId)->exists()) {
                $trxId = 'MANUAL-' . strtoupper(Str::random(10));
            }

            $gatewayPayload = [
                'source' => 'store_admin_manual_entry',
                'recorded_by' => auth()->user()?->name ?? 'Store Admin',
                'note' => $validated['note'] ?? null,
            ];

            $payment = OrderPayment::create([
                'tenant_id' => $tenantId,
                'order_id' => $order->id,
                'type' => 'charge',
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $trxId,
                'amount' => $validated['amount'],
                'currency' => $order->currency ?? 'USD',
                'status' => $validated['status'],
                'gateway_response' => $gatewayPayload,
                'paid_at' => $validated['status'] === 'successful' ? ($validated['paid_at'] ? \Carbon\Carbon::parse($validated['paid_at']) : now()) : null,
            ]);

            // Sync Order Payment Status
            $this->syncOrderPaymentStatus($order);

            return $payment;
        });

        return redirect()->route('store.admin.transactions.index')
            ->with('success', "Payment transaction #{$payment->transaction_id} recorded successfully for Order #{$order->order_number}.");
    }

    public function refund(Request $request, int $id)
    {
        $tenantId = tenantId();

        $payment = OrderPayment::where('tenant_id', $tenantId)
            ->where('type', 'charge')
            ->with('refunds')
            ->findOrFail($id);

        $remaining = $payment->remaining_refundable;

        if ($remaining <= 0) {
            return back()->with('error', 'This transaction has already been fully refunded.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', "max:{$remaining}"],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $refund = DB::transaction(function () use ($tenantId, $payment, $validated) {
            $refundTrxId = 'REF-' . strtoupper(Str::random(10));
            while (OrderPayment::where('tenant_id', $tenantId)->where('payment_method', $payment->payment_method)->where('transaction_id', $refundTrxId)->exists()) {
                $refundTrxId = 'REF-' . strtoupper(Str::random(10));
            }

            $refundPayment = OrderPayment::create([
                'tenant_id' => $tenantId,
                'order_id' => $payment->order_id,
                'type' => 'refund',
                'parent_id' => $payment->id,
                'payment_method' => $payment->payment_method,
                'transaction_id' => $refundTrxId,
                'amount' => $validated['amount'],
                'currency' => $payment->currency,
                'status' => 'successful',
                'gateway_response' => [
                    'source' => 'store_admin_refund',
                    'refund_reason' => $validated['reason'],
                    'processed_by' => auth()->user()?->name ?? 'Store Admin',
                    'parent_transaction_id' => $payment->transaction_id,
                ],
                'paid_at' => now(),
            ]);

            // If the parent payment is now fully refunded, mark parent payment status as 'refunded'
            if ($payment->remaining_refundable - (float) $validated['amount'] <= 0) {
                $payment->update(['status' => 'refunded']);
            }

            // Sync Order Payment Status
            if ($payment->order) {
                $this->syncOrderPaymentStatus($payment->order);
            }

            return $refundPayment;
        });

        return back()->with('success', "Refund of {$payment->currency} " . number_format($refund->amount, 2) . " processed successfully (Ref: #{$refund->transaction_id}).");
    }

    public function updateStatus(Request $request, int $id)
    {
        $tenantId = tenantId();

        $payment = OrderPayment::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['successful', 'pending', 'failed'])],
        ]);

        DB::transaction(function () use ($payment, $validated) {
            $updates = [
                'status' => $validated['status'],
            ];

            if ($validated['status'] === 'successful' && !$payment->paid_at) {
                $updates['paid_at'] = now();
            }

            $payment->update($updates);

            if ($payment->order) {
                $this->syncOrderPaymentStatus($payment->order);
            }
        });

        return back()->with('success', "Transaction #{$payment->transaction_id} status updated to " . ucfirst($validated['status']) . ".");
    }

    public function export(Request $request): StreamedResponse
    {
        $tenantId = tenantId();

        $query = OrderPayment::where('tenant_id', $tenantId)
            ->with(['order.customer', 'parent']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderByDesc('id')->get();

        $filename = 'transactions-export-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fputs($handle, "\xEF\xBB\xBF");

            // CSV Header
            fputcsv($handle, [
                'Transaction ID',
                'Date & Time',
                'Type',
                'Order Number',
                'Customer Name',
                'Customer Phone',
                'Customer Email',
                'Payment Method',
                'Amount',
                'Currency',
                'Status',
                'Paid At',
                'Parent Transaction ID',
                'Notes / Reason',
            ]);

            foreach ($transactions as $tx) {
                $notes = '';
                if (!empty($tx->gateway_response['refund_reason'])) {
                    $notes = 'Refund Reason: ' . $tx->gateway_response['refund_reason'];
                } elseif (!empty($tx->gateway_response['note'])) {
                    $notes = $tx->gateway_response['note'];
                }

                fputcsv($handle, [
                    $tx->transaction_id,
                    $tx->created_at->format('Y-m-d H:i:s'),
                    strtoupper($tx->type),
                    $tx->order?->order_number ?? 'N/A',
                    $tx->customer_name,
                    $tx->customer_phone ?? 'N/A',
                    $tx->customer_email ?? 'N/A',
                    $tx->method_label,
                    $tx->type === 'refund' ? '-' . number_format($tx->amount, 2, '.', '') : number_format($tx->amount, 2, '.', ''),
                    $tx->currency,
                    ucfirst($tx->status),
                    $tx->paid_at ? $tx->paid_at->format('Y-m-d H:i:s') : 'Unpaid',
                    $tx->parent?->transaction_id ?? '',
                    $notes,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function syncOrderPaymentStatus(Order $order): void
    {
        $tenantId = $order->tenant_id;

        $payments = OrderPayment::where('tenant_id', $tenantId)
            ->where('order_id', $order->id)
            ->get();

        $chargesPaid = (float) $payments->where('type', 'charge')->where('status', 'successful')->sum('amount');
        $refundsPaid = (float) $payments->where('type', 'refund')->where('status', 'successful')->sum('amount');
        $netPaid = max(0, $chargesPaid - $refundsPaid);

        if ($netPaid <= 0 && $refundsPaid > 0 && $chargesPaid > 0) {
            $order->update(['payment_status' => 'refunded']);
        } elseif ($netPaid >= (float) $order->total && (float) $order->total > 0) {
            $order->update(['payment_status' => 'paid']);
        } elseif ($netPaid > 0 && $netPaid < (float) $order->total) {
            $order->update(['payment_status' => 'partially_paid']);
        } elseif ($netPaid <= 0) {
            $order->update(['payment_status' => 'unpaid']);
        }
    }
}
