@extends('store_admin.layout.master')

@section('title', 'Transactions & Payments - ' . (auth()->user()->tenant->store_name ?? 'Store Admin'))

@section('contents')
@php
    $field = 'rounded-xl border-slate-300 bg-white text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500';
    $currentStatus = request('status', 'all');
    $currentMethod = request('payment_method', 'all');
    $currentType = request('type', 'all');
    $currentDate = request('date_range', 'all');
@endphp

<div class="space-y-6 pb-12">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-1">
                <i class="fa-solid fa-money-bill-transfer"></i>
                <span>Store Operations &bull; Financial Ledger</span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-ink dark:text-white">Transactions & Payments</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Monitor customer payments across all payment gateways (bKash, Nagad, Cards, COD), track collections, and manage refunds.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('store.admin.transactions.export', request()->query()) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-xs shadow-xs transition">
                <i class="fa-solid fa-file-arrow-down text-indigo-600 dark:text-indigo-400"></i>
                <span>Export CSV</span>
            </a>

            @can('Store Transaction Create')
                <button type="button" onclick="openRecordPaymentModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition-all hover:-translate-y-0.5 cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Record Manual Payment</span>
                </button>
            @endcan
        </div>
    </div>

    {{-- Flash Alerts --}}
    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 p-4 text-sm font-medium text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-base shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-300 font-bold text-lg">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl bg-rose-50 dark:bg-rose-950/40 p-4 text-sm font-medium text-rose-800 dark:text-rose-200 border border-rose-200 dark:border-rose-800 flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400 text-base shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800 dark:text-rose-300 font-bold text-lg">&times;</button>
        </div>
    @endif

    {{-- KPI Metric Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Net Revenue --}}
        <div class="card p-5 border border-slate-200 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Net Revenue Collected</p>
                    <h3 class="text-2xl font-black text-ink dark:text-white mt-1">
                        ৳{{ number_format($netRevenue, 2) }}
                    </h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-xl shrink-0">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-3 flex items-center gap-1.5">
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                <span>Charges minus completed refunds</span>
            </p>
        </div>

        {{-- Total Charges --}}
        <div class="card p-5 border border-slate-200 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Charges</p>
                    <h3 class="text-2xl font-black text-ink dark:text-white mt-1">
                        ৳{{ number_format($successfulCharges, 2) }}
                    </h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-950/50 flex items-center justify-center text-sky-600 dark:text-sky-400 text-xl shrink-0">
                    <i class="fa-solid fa-circle-arrow-down"></i>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-3 flex items-center gap-1.5">
                <i class="fa-solid fa-receipt text-sky-500"></i>
                <span>{{ $statusCounts['successful'] }} successful transactions</span>
            </p>
        </div>

        {{-- Pending Collections --}}
        <div class="card p-5 border border-slate-200 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pending Collections</p>
                    <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                        ৳{{ number_format($pendingAmount, 2) }}
                    </h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center text-amber-600 dark:text-amber-400 text-xl shrink-0">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-3 flex items-center gap-1.5">
                <i class="fa-solid fa-truck text-amber-500"></i>
                <span>{{ $statusCounts['pending'] }} COD or pending clearances</span>
            </p>
        </div>

        {{-- Total Refunds --}}
        <div class="card p-5 border border-slate-200 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Refunded</p>
                    <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                        ৳{{ number_format($refundedAmount, 2) }}
                    </h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/50 flex items-center justify-center text-rose-600 dark:text-rose-400 text-xl shrink-0">
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-3 flex items-center gap-1.5">
                <i class="fa-solid fa-shield-halved text-rose-500"></i>
                <span>{{ $statusCounts['refunded'] }} reversed records</span>
            </p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        {{-- Status Tabs --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-3">
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                @php
                    $tabs = [
                        'all' => ['label' => 'All Transactions', 'count' => $statusCounts['all']],
                        'successful' => ['label' => 'Successful', 'count' => $statusCounts['successful']],
                        'pending' => ['label' => 'Pending', 'count' => $statusCounts['pending']],
                        'refunded' => ['label' => 'Refunded', 'count' => $statusCounts['refunded']],
                        'failed' => ['label' => 'Failed', 'count' => $statusCounts['failed']],
                    ];
                @endphp
                @foreach($tabs as $tabKey => $tabInfo)
                    <a href="{{ route('store.admin.transactions.index', array_merge(request()->query(), ['status' => $tabKey, 'page' => 1])) }}"
                       class="px-3 py-1.5 rounded-lg font-medium transition-all {{ $currentStatus === $tabKey ? 'bg-indigo-600 text-white shadow-xs font-bold' : 'bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        {{ $tabInfo['label'] }}
                        <span class="ml-1 opacity-75 font-semibold text-[11px]">({{ $tabInfo['count'] }})</span>
                    </a>
                @endforeach
            </div>

            <div class="text-xs text-slate-400 dark:text-slate-500">
                Showing <strong>{{ $transactions->total() }}</strong> total records
            </div>
        </div>

        {{-- Dropdown Filters & Search --}}
        <form method="GET" action="{{ route('store.admin.transactions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="hidden" name="status" value="{{ $currentStatus }}">

            {{-- Method Filter --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Payment Method</label>
                <select name="payment_method" onchange="this.form.submit()" class="w-full {{ $field }}">
                    <option value="all" {{ $currentMethod === 'all' ? 'selected' : '' }}>All Methods</option>
                    <option value="bkash" {{ $currentMethod === 'bkash' ? 'selected' : '' }}>bKash</option>
                    <option value="nagad" {{ $currentMethod === 'nagad' ? 'selected' : '' }}>Nagad</option>
                    <option value="rocket" {{ $currentMethod === 'rocket' ? 'selected' : '' }}>Rocket</option>
                    <option value="card" {{ $currentMethod === 'card' ? 'selected' : '' }}>Debit / Credit Card</option>
                    <option value="cod" {{ $currentMethod === 'cod' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                    <option value="bank_transfer" {{ $currentMethod === 'bank_transfer' ? 'selected' : '' }}>Bank Wire Transfer</option>
                    <option value="cash" {{ $currentMethod === 'cash' ? 'selected' : '' }}>Cash in Hand</option>
                </select>
            </div>

            {{-- Type Filter --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Type</label>
                <select name="type" onchange="this.form.submit()" class="w-full {{ $field }}">
                    <option value="all" {{ $currentType === 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="charge" {{ $currentType === 'charge' ? 'selected' : '' }}>Charge (Incoming)</option>
                    <option value="refund" {{ $currentType === 'refund' ? 'selected' : '' }}>Refund (Outgoing)</option>
                </select>
            </div>

            {{-- Date Range --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Date Range</label>
                <select name="date_range" onchange="this.form.submit()" class="w-full {{ $field }}">
                    <option value="all" {{ $currentDate === 'all' ? 'selected' : '' }}>All Time</option>
                    <option value="today" {{ $currentDate === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="last_7_days" {{ $currentDate === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="this_month" {{ $currentDate === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_30_days" {{ $currentDate === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                </select>
            </div>

            {{-- Search Bar --}}
            <div class="lg:col-span-2 flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Search Reference</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Trx ID, Order #, Customer..."
                               class="w-full pl-9 {{ $field }}">
                    </div>
                </div>
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold text-xs hover:opacity-90 transition">
                    Search
                </button>
                @if(request()->anyFilled(['search', 'payment_method', 'type', 'date_range']) || $currentStatus !== 'all')
                    <a href="{{ route('store.admin.transactions.index') }}" title="Clear filters"
                       class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold transition">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Transactions Table --}}
    <div class="card overflow-hidden border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Transaction ID</th>
                        <th class="px-4 py-3.5">Date &amp; Time</th>
                        <th class="px-4 py-3.5">Order</th>
                        <th class="px-4 py-3.5">Customer</th>
                        <th class="px-4 py-3.5">Method</th>
                        <th class="px-4 py-3.5 text-right">Amount</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-slate-700 dark:text-slate-300">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                            {{-- Transaction ID & Type --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-ink dark:text-white select-all">{{ $tx->transaction_id }}</span>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $tx->transaction_id }}'); alert('Transaction ID copied!');"
                                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition" title="Copy Trx ID">
                                        <i class="fa-regular fa-copy text-[11px]"></i>
                                    </button>
                                </div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wide border {{ $tx->type_badge_class }}">
                                        {{ $tx->type }}
                                    </span>
                                    @if($tx->parent_id)
                                        <span class="text-[10px] text-slate-400" title="Refund for {{ $tx->parent?->transaction_id }}">
                                            for #{{ Str::limit($tx->parent?->transaction_id, 10) }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Date & Time --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-900 dark:text-white">{{ $tx->created_at->format('M d, Y') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $tx->created_at->format('h:i A') }}</div>
                            </td>

                            {{-- Linked Order --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($tx->order)
                                    <a href="{{ route('store.admin.orders.show', $tx->order->id) }}"
                                       class="inline-flex items-center gap-1.5 font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        <i class="fa-solid fa-cart-shopping text-[10px]"></i>
                                        <span>#{{ $tx->order->order_number }}</span>
                                    </a>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        Order Total: ৳{{ number_format($tx->order->total, 2) }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">No Order Attached</span>
                                @endif
                            </td>

                            {{-- Customer --}}
                            <td class="px-4 py-4">
                                <div class="font-semibold text-slate-900 dark:text-white truncate max-w-[160px]">
                                    {{ $tx->customer_name }}
                                </div>
                                <div class="text-[11px] text-slate-400 truncate max-w-[160px]">
                                    {{ $tx->customer_phone ?? $tx->customer_email ?? 'No contact info' }}
                                </div>
                            </td>

                            {{-- Method --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200">
                                    <i class="fa-solid {{ $tx->method_icon }} text-slate-400 text-xs"></i>
                                    <span>{{ $tx->method_label }}</span>
                                </div>
                            </td>

                            {{-- Amount --}}
                            <td class="px-4 py-4 text-right whitespace-nowrap">
                                @if($tx->type === 'refund')
                                    <span class="font-bold text-rose-600 dark:text-rose-400 text-sm">
                                        -৳{{ number_format($tx->amount, 2) }}
                                    </span>
                                @else
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">
                                        +৳{{ number_format($tx->amount, 2) }}
                                    </span>
                                @endif
                                @if($tx->refunds->count() > 0)
                                    <div class="text-[10px] text-purple-600 dark:text-purple-400 font-semibold mt-0.5">
                                        ৳{{ number_format($tx->refunded_amount, 2) }} refunded
                                    </div>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border capitalize {{ $tx->status_badge_class }}">
                                    @if($tx->status === 'successful')
                                        <i class="fa-solid fa-circle-check text-[9px]"></i>
                                    @elseif($tx->status === 'pending')
                                        <i class="fa-solid fa-clock text-[9px]"></i>
                                    @elseif($tx->status === 'refunded')
                                        <i class="fa-solid fa-arrow-rotate-left text-[9px]"></i>
                                    @else
                                        <i class="fa-solid fa-circle-xmark text-[9px]"></i>
                                    @endif
                                    {{ $tx->status }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    {{-- Quick Details Modal View --}}
                                    <button type="button" onclick="openDetailsModal({{ $tx->id }})"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                                            title="View Transaction Details">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>

                                    {{-- Mark Pending as Paid --}}
                                    @if($tx->status === 'pending')
                                        <form method="POST" action="{{ route('store.admin.transactions.status', $tx->id) }}" class="inline"
                                              onsubmit="return confirm('Mark transaction #{{ $tx->transaction_id }} as Successful / Paid?');">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="successful">
                                            <button type="submit"
                                                    class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition cursor-pointer"
                                                    title="Mark as Paid">
                                                <i class="fa-solid fa-circle-check text-xs"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Refund Action --}}
                                    @can('Store Transaction Refund')
                                        @if($tx->type === 'charge' && $tx->status === 'successful' && $tx->remaining_refundable > 0)
                                            <button type="button" onclick="openRefundModal({{ $tx->id }}, '{{ $tx->transaction_id }}', {{ $tx->remaining_refundable }}, '{{ $tx->order?->order_number }}')"
                                                    class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer"
                                                    title="Issue Refund">
                                                <i class="fa-solid fa-arrow-rotate-left text-xs"></i>
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400 dark:text-slate-500">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto text-slate-400 text-xl mb-3">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">No transactions found</p>
                                <p class="text-xs mt-1">Try adjusting your filters or record a new customer payment.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($transactions->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

</div>

{{-- MODAL 1: Transaction Details Modal --}}
<div id="details-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="card w-full max-w-xl max-h-[90vh] flex flex-col border border-slate-200 dark:border-slate-800 shadow-2xl rounded-2xl overflow-hidden bg-white dark:bg-slate-900">
        {{-- Modal Header --}}
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-ink dark:text-white">Transaction Details</h3>
                    <p id="modal-trx-subtitle" class="text-xs text-slate-400 font-mono">Loading...</p>
                </div>
            </div>
            <button type="button" onclick="closeDetailsModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl font-bold">&times;</button>
        </div>

        {{-- Modal Content (Dynamic) --}}
        <div id="modal-trx-body" class="p-5 overflow-y-auto space-y-5 text-xs flex-1">
            <div class="flex items-center justify-center py-8 text-slate-400">
                <i class="fa-solid fa-spinner fa-spin text-xl mr-2"></i> Loading transaction data...
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2 bg-slate-50/50 dark:bg-slate-800/30">
            <button type="button" onclick="closeDetailsModal()"
                    class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs transition cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>

{{-- MODAL 2: Record Manual Payment Modal --}}
@can('Store Transaction Create')
<div id="record-payment-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="card w-full max-w-lg border border-slate-200 dark:border-slate-800 shadow-2xl rounded-2xl overflow-hidden bg-white dark:bg-slate-900">
        <form method="POST" action="{{ route('store.admin.transactions.store') }}">
            @csrf
            {{-- Header --}}
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-ink dark:text-white">Record Customer Payment</h3>
                        <p class="text-xs text-slate-400">Log an offline, cash or manual bank transfer payment for an order</p>
                    </div>
                </div>
                <button type="button" onclick="closeRecordPaymentModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl font-bold">&times;</button>
            </div>

            {{-- Body --}}
            <div class="p-5 space-y-4 text-xs">
                {{-- Order Select --}}
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Select Order <span class="text-rose-500">*</span></label>
                    <select name="order_id" required class="w-full {{ $field }}" onchange="updateSuggestedAmount(this)">
                        <option value="">-- Choose an Unpaid / Partially Paid Order --</option>
                        @foreach($unpaidOrders as $ord)
                            <option value="{{ $ord->id }}" data-total="{{ $ord->total }}" data-method="{{ $ord->payment_method }}">
                                #{{ $ord->order_number }} - {{ $ord->shipping_name }} (Total: ৳{{ number_format($ord->total, 2) }}, Status: {{ ucfirst($ord->payment_status) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Amount & Payment Method --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Amount (৳) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="record-payment-amount" required placeholder="0.00" class="w-full {{ $field }}">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Method <span class="text-rose-500">*</span></label>
                        <select name="payment_method" id="record-payment-method" required class="w-full {{ $field }}">
                            <option value="cod">Cash on Delivery (COD)</option>
                            <option value="bkash">bKash Mobile Banking</option>
                            <option value="nagad">Nagad Mobile Banking</option>
                            <option value="rocket">Rocket Mobile Banking</option>
                            <option value="card">Card Payment</option>
                            <option value="bank_transfer">Bank Wire Transfer</option>
                            <option value="cash">Cash in Hand</option>
                        </select>
                    </div>
                </div>

                {{-- Transaction / Reference ID & Status --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Transaction / Reference ID</label>
                        <input type="text" name="transaction_id" placeholder="Optional e.g. BKASH-9912A" class="w-full {{ $field }}">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Status <span class="text-rose-500">*</span></label>
                        <select name="status" required class="w-full {{ $field }}">
                            <option value="successful">Successful (Money Received)</option>
                            <option value="pending">Pending (Awaiting Clearance)</option>
                        </select>
                    </div>
                </div>

                {{-- Paid Date --}}
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Date &amp; Time</label>
                    <input type="datetime-local" name="paid_at" value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full {{ $field }}">
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Internal Notes / Payment Proof</label>
                    <textarea name="note" rows="2" placeholder="e.g. Received cash from delivery courier / bKash sender number: 017XXXXXXXX" class="w-full {{ $field }}"></textarea>
                </div>
            </div>

            {{-- Footer --}}
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5 bg-slate-50/50 dark:bg-slate-800/30">
                <button type="button" onclick="closeRecordPaymentModal()" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs hover:bg-slate-50 transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition cursor-pointer">
                    Record Payment
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- MODAL 3: Issue Refund Modal --}}
@can('Store Transaction Refund')
<div id="refund-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="card w-full max-w-md border border-slate-200 dark:border-slate-800 shadow-2xl rounded-2xl overflow-hidden bg-white dark:bg-slate-900">
        <form id="refund-form" method="POST" action="">
            @csrf
            {{-- Header --}}
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-ink dark:text-white">Issue Transaction Refund</h3>
                        <p id="refund-modal-subtitle" class="text-xs text-slate-400 font-mono"></p>
                    </div>
                </div>
                <button type="button" onclick="closeRefundModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl font-bold">&times;</button>
            </div>

            {{-- Body --}}
            <div class="p-5 space-y-4 text-xs">
                <div class="rounded-xl bg-amber-50 dark:bg-amber-950/30 p-3 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200">
                    <p class="font-bold flex items-center gap-1.5">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>Refund Policy Notice</span>
                    </p>
                    <p class="mt-1 text-[11px] opacity-90">
                        This action records a refund ledger against the selected customer transaction and adjusts the order payment status.
                    </p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Refund Amount (৳) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="refund-amount-input" required class="w-full {{ $field }}">
                    <p class="text-[11px] text-slate-400 mt-1">Maximum refundable: <strong id="refund-max-text">৳0.00</strong></p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Reason for Refund <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="reason" rows="3" required placeholder="e.g. Customer returned damaged medicine box / cancelled Rx items" class="w-full {{ $field }}"></textarea>
                </div>
            </div>

            {{-- Footer --}}
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5 bg-slate-50/50 dark:bg-slate-800/30">
                <button type="button" onclick="closeRefundModal()" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs hover:bg-slate-50 transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition cursor-pointer">
                    Confirm Refund
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

@endsection

@push('scripts')
<script>
    // 1. Details Modal
    function openDetailsModal(id) {
        const modal = document.getElementById('details-modal');
        const subtitle = document.getElementById('modal-trx-subtitle');
        const body = document.getElementById('modal-trx-body');

        modal.classList.remove('hidden');
        subtitle.textContent = 'Loading...';
        body.innerHTML = '<div class="flex items-center justify-center py-8 text-slate-400"><i class="fa-solid fa-spinner fa-spin text-xl mr-2"></i> Fetching transaction receipt...</div>';

        fetch(`/admin/transactions/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            subtitle.textContent = data.transaction_id;

            let html = `
                <div class="grid grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-800/40 p-4 rounded-xl border border-slate-100 dark:border-slate-800">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Amount</span>
                        <span class="text-lg font-black ${data.type === 'refund' ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'}">
                            ${data.type === 'refund' ? '-' : '+'}৳${parseFloat(data.amount).toFixed(2)}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Status</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border capitalize ${data.status_badge_class} mt-0.5">
                            ${data.status}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Payment Method</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200 mt-0.5 block flex items-center gap-1.5">
                            <i class="fa-solid ${data.method_icon} text-slate-400"></i> ${data.method_label}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Paid Timestamp</span>
                        <span class="font-medium text-slate-700 dark:text-slate-300 mt-0.5 block">${data.paid_at}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <h4 class="font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-[11px]">Linked Order & Customer</h4>
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-3.5 space-y-2 bg-white dark:bg-slate-900">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Order Number:</span>
                            <a href="/admin/orders/${data.order.id}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                #${data.order.order_number}
                            </a>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Customer Name:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">${data.order.shipping_name || 'Guest'}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Phone:</span>
                            <span class="font-mono text-slate-700 dark:text-slate-300">${data.order.shipping_phone || 'N/A'}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Shipping Address:</span>
                            <span class="text-slate-700 dark:text-slate-300 text-right truncate max-w-[220px]">${data.order.shipping_address || 'N/A'}</span>
                        </div>
                    </div>
                </div>
            `;

            if (data.gateway_response) {
                html += `
                    <div class="space-y-1.5">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-[11px]">Gateway & Audit Metadata</h4>
                        <pre class="bg-slate-900 text-emerald-400 p-3 rounded-xl font-mono text-[11px] overflow-x-auto">${JSON.stringify(data.gateway_response, null, 2)}</pre>
                    </div>
                `;
            }

            if (data.refunds && data.refunds.length > 0) {
                html += `
                    <div class="space-y-2">
                        <h4 class="font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider text-[11px]">Refunds Issued Against This Transaction</h4>
                        <div class="divide-y divide-slate-100 dark:divide-slate-800 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                `;
                data.refunds.forEach(r => {
                    html += `
                        <div class="p-2.5 flex items-center justify-between bg-rose-50/30 dark:bg-rose-950/10">
                            <div>
                                <span class="font-mono font-bold text-rose-700 dark:text-rose-400">#${r.transaction_id}</span>
                                <span class="text-[10px] text-slate-400 block">${r.created_at}</span>
                            </div>
                            <span class="font-bold text-rose-600 dark:text-rose-400 text-sm">-৳${r.amount.toFixed(2)}</span>
                        </div>
                    `;
                });
                html += `</div></div>`;
            }

            body.innerHTML = html;
        })
        .catch(err => {
            body.innerHTML = '<div class="p-4 text-center text-rose-500">Failed to load transaction details. Please try again.</div>';
        });
    }

    function closeDetailsModal() {
        document.getElementById('details-modal').classList.add('hidden');
    }

    // 2. Record Payment Modal
    function openRecordPaymentModal() {
        document.getElementById('record-payment-modal').classList.remove('hidden');
    }

    function closeRecordPaymentModal() {
        document.getElementById('record-payment-modal').classList.add('hidden');
    }

    function updateSuggestedAmount(select) {
        const option = select.options[select.selectedIndex];
        if (option && option.dataset.total) {
            document.getElementById('record-payment-amount').value = parseFloat(option.dataset.total).toFixed(2);
            if (option.dataset.method) {
                document.getElementById('record-payment-method').value = option.dataset.method;
            }
        }
    }

    // 3. Issue Refund Modal
    function openRefundModal(id, trxId, maxRefund, orderNumber) {
        const modal = document.getElementById('refund-modal');
        const form = document.getElementById('refund-form');
        const subtitle = document.getElementById('refund-modal-subtitle');
        const amountInput = document.getElementById('refund-amount-input');
        const maxText = document.getElementById('refund-max-text');

        form.action = `/admin/transactions/${id}/refund`;
        subtitle.textContent = `${trxId} (Order #${orderNumber})`;
        amountInput.max = maxRefund;
        amountInput.value = maxRefund.toFixed(2);
        maxText.textContent = `৳${maxRefund.toFixed(2)}`;

        modal.classList.remove('hidden');
    }

    function closeRefundModal() {
        document.getElementById('refund-modal').classList.add('hidden');
    }
</script>
@endpush
