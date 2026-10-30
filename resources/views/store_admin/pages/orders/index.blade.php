@extends('store_admin.layout.master')
@section('contents')
@php
    $field = 'rounded-lg border-slate-300 bg-white text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white';
    $currentStatus = request('status', 'all');
    $currentPayment = request('payment_status', 'all');
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Orders</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage customer orders, payments, shipments, and order fulfillment.</p>
        </div>
        @can('Store Order Create')
            <a href="{{ route('store.admin.orders.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-indigo-700 transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-plus"></i> Create Order
            </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl bg-rose-50 p-3 text-sm text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Status Count Filter Tabs --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3 dark:border-slate-800 text-xs">
        <a href="{{ route('store.admin.orders.index', ['payment_status' => $currentPayment]) }}"
           class="rounded-lg px-3 py-1.5 font-medium transition {{ $currentStatus === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700' }}">
            All <span class="ml-1 opacity-80 font-bold">({{ $statusCounts['all'] }})</span>
        </a>
        <a href="{{ route('store.admin.orders.index', ['status' => 'pending', 'payment_status' => $currentPayment]) }}"
           class="rounded-lg px-3 py-1.5 font-medium transition {{ $currentStatus === 'pending' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700' }}">
            Pending <span class="ml-1 opacity-80 font-bold">({{ $statusCounts['pending'] }})</span>
        </a>
        <a href="{{ route('store.admin.orders.index', ['status' => 'processing', 'payment_status' => $currentPayment]) }}"
           class="rounded-lg px-3 py-1.5 font-medium transition {{ $currentStatus === 'processing' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700' }}">
            Processing <span class="ml-1 opacity-80 font-bold">({{ $statusCounts['processing'] }})</span>
        </a>
        <a href="{{ route('store.admin.orders.index', ['status' => 'shipped', 'payment_status' => $currentPayment]) }}"
           class="rounded-lg px-3 py-1.5 font-medium transition {{ $currentStatus === 'shipped' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700' }}">
            Shipped <span class="ml-1 opacity-80 font-bold">({{ $statusCounts['shipped'] }})</span>
        </a>
        <a href="{{ route('store.admin.orders.index', ['status' => 'delivered', 'payment_status' => $currentPayment]) }}"
           class="rounded-lg px-3 py-1.5 font-medium transition {{ $currentStatus === 'delivered' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700' }}">
            Delivered <span class="ml-1 opacity-80 font-bold">({{ $statusCounts['delivered'] }})</span>
        </a>
        <a href="{{ route('store.admin.orders.index', ['status' => 'cancelled', 'payment_status' => $currentPayment]) }}"
           class="rounded-lg px-3 py-1.5 font-medium transition {{ $currentStatus === 'cancelled' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700' }}">
            Cancelled <span class="ml-1 opacity-80 font-bold">({{ $statusCounts['cancelled'] }})</span>
        </a>
    </div>

    {{-- Filter & Search Bar --}}
    <section class="card overflow-hidden">
        <form method="GET" action="{{ route('store.admin.orders.index') }}" class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-4 dark:border-slate-800">
            <input type="hidden" name="status" value="{{ $currentStatus }}">
            <div class="flex flex-wrap items-center gap-3">
                <input name="search" value="{{ request('search') }}" placeholder="Search by order #, customer, phone..." class="w-64 sm:w-80 {{ $field }} px-3 py-2 text-sm">
                <select name="payment_status" class="{{ $field }} px-3 py-2 text-sm" onchange="this.form.submit()">
                    <option value="all" @selected($currentPayment === 'all')>All Payment Statuses</option>
                    <option value="unpaid" @selected($currentPayment === 'unpaid')>Unpaid</option>
                    <option value="paid" @selected($currentPayment === 'paid')>Paid</option>
                    <option value="refunded" @selected($currentPayment === 'refunded')>Refunded</option>
                    <option value="failed" @selected($currentPayment === 'failed')>Failed</option>
                </select>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition">Filter</button>
                @if(request()->filled('search') || request()->filled('status') || request()->filled('payment_status'))
                    <a href="{{ route('store.admin.orders.index') }}" class="text-xs text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 underline">Reset</a>
                @endif
            </div>
            <div class="text-xs text-slate-400">
                Showing {{ $orders->firstItem() ?? 0 }}-{{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                    <tr>
                        <th class="p-4">Order</th>
                        <th class="p-4">Date</th>
                        <th class="p-4">Customer & Destination</th>
                        <th class="p-4">Items</th>
                        <th class="p-4">Payment</th>
                        <th class="p-4">Order Status</th>
                        <th class="p-4">Total</th>
                        <th class="p-4 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                            <td class="p-4">
                                <a href="{{ route('store.admin.orders.show', $order) }}" class="font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-mono">
                                    {{ $order->order_number }}
                                </a>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5 truncate max-w-[140px]" title="{{ $order->public_id }}">
                                    {{ substr($order->public_id, 0, 8) }}...
                                </div>
                            </td>
                            <td class="p-4 text-xs text-slate-600 dark:text-slate-300">
                                {{ $order->placed_at?->format('M j, Y') ?? $order->created_at->format('M j, Y') }}
                                <div class="text-[11px] text-slate-400">{{ $order->placed_at?->format('h:i A') ?? $order->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $order->shipping_name }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $order->shipping_phone }}</div>
                                <div class="text-[11px] text-slate-400 truncate max-w-[200px]" title="{{ $order->shipping_city }}, {{ $order->shipping_country }}">{{ $order->shipping_city }}, {{ $order->shipping_country }}</div>
                            </td>
                            <td class="p-4 text-xs">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $order->products->sum('quantity') }}</span> item(s)
                                <div class="text-[11px] text-slate-400">({{ $order->products->count() }} SKU)</div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold border {{ $order->payment_status_badge_class }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                </span>
                                <div class="text-[11px] text-slate-400 uppercase mt-1">{{ $order->payment_method }}</div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border {{ $order->status_badge_class }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                                @if($order->prescription_status && $order->prescription_status !== 'not_required')
                                    <div class="text-[10px] text-amber-600 dark:text-amber-400 mt-1 font-medium">
                                        <i class="fa-solid fa-file-prescription"></i> {{ ucfirst(str_replace('_', ' ', $order->prescription_status)) }}
                                    </div>
                                @endif
                            </td>
                            <td class="p-4 font-bold text-slate-900 dark:text-white font-mono">
                                {{ $order->currency }} {{ number_format((float)$order->total, 2) }}
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('Store Order View')
                                        <a href="{{ route('store.admin.orders.show', $order) }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400 dark:hover:bg-slate-700 transition">
                                            View
                                        </a>
                                    @endcan
                                    @can('Store Order Delete')
                                        <form method="POST" action="{{ route('store.admin.orders.destroy', $order) }}" class="inline" onsubmit="return confirm('Delete this order?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-xs text-rose-500 hover:text-rose-700 transition" title="Delete Order">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-12 text-center text-sm text-slate-400">
                                <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                <div>No orders found matching the filter criteria.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $orders->links() }}
        </div>
    </section>
</div>
@endsection
