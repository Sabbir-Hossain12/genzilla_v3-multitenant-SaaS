@extends('store_admin.layout.master')

@push('title')
    Store Admin Dashboard & Analytics
@endpush

@push('css')
<style>
    .chart-container {
        position: relative;
        height: 280px;
        width: 100%;
    }
</style>
@endpush

@section('contents')
<div class="space-y-6">

    <!-- Top Greeting & Quick Actions Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-black text-ink dark:text-white flex items-center gap-2">
                Pharmacy Overview <span>💊</span>
            </h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Store performance metrics, live queues, and business analytics &bull; {{ now()->format('l, j F Y') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @can('Store Report List')
                <a href="{{ route('store.admin.reports.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-xs">
                    <i class="fa-solid fa-chart-line text-xs text-primary"></i>
                    <span>Export Reports</span>
                </a>
            @endcan

            @can('Store Order Create')
                <a href="{{ route('store.admin.orders.create') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-xs">
                    <i class="fa-solid fa-plus text-xs text-emerald-600"></i>
                    <span>New Order</span>
                </a>
            @endcan

            @can('Store Product Create')
                <a href="{{ route('store.admin.products.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-primary hover:bg-primarydark text-white transition-colors shadow-sm shadow-primary/30">
                    <i class="fa-solid fa-box-open text-xs"></i>
                    <span>Add Product</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- Operational KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Revenue Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Gross Sales</span>
                <span class="w-9 h-9 rounded-xl flex items-center justify-center bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">
                    <i class="fa-solid fa-bangladeshi-taka-sign text-sm"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-ink dark:text-white">
                    ৳ {{ number_format($totalRevenue, 2) }}
                </div>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        +৳ {{ number_format($todayRevenue, 2) }} today
                    </span>
                    <span class="text-[11px] text-slate-400">net valid orders</span>
                </div>
            </div>
        </div>

        <!-- 2. Orders Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Orders</span>
                <span class="w-9 h-9 rounded-xl flex items-center justify-center bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400">
                    <i class="fa-solid fa-cart-shopping text-sm"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-ink dark:text-white">
                    {{ number_format($totalOrders) }}
                </div>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                        {{ $todayOrders }} placed today
                    </span>
                    <span class="text-[11px] text-slate-400">&bull; {{ $pendingOrders }} pending</span>
                </div>
            </div>
        </div>

        <!-- 3. Prescriptions Queue Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Rx Pending Review</span>
                <span class="w-9 h-9 rounded-xl flex items-center justify-center bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                    <i class="fa-solid fa-file-prescription text-sm"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-ink dark:text-white">
                    {{ number_format($pendingPrescriptionsCount) }}
                </div>
                <div class="flex items-center gap-1.5 mt-2">
                    @if($pendingPrescriptionsCount > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white">
                            Action Needed
                        </span>
                        <a href="{{ route('store.admin.prescriptions.index') }}" class="text-[11px] text-primary hover:underline font-medium">Open Queue &rarr;</a>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            All Caught Up
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- 4. Abandoned Carts / Leads Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Recoverable Carts</span>
                <span class="w-9 h-9 rounded-xl flex items-center justify-center bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400">
                    <i class="fa-solid fa-cart-arrow-down text-sm"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-ink dark:text-white">
                    {{ number_format($recoverableCarts) }}
                </div>
                <div class="flex items-center gap-1.5 mt-2">
                    @if($recoverableCarts > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500 text-white">
                            Tele-sales Leads
                        </span>
                        <a href="{{ route('store.admin.abandoned_checkouts.index') }}" class="text-[11px] text-primary hover:underline font-medium">Recover &rarr;</a>
                    @else
                        <span class="text-[11px] text-slate-400">0 carts awaiting follow-up</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Graphs Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 14-Day Sales & Order Volume Chart (Span 2 cols) -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h2 class="text-sm font-bold text-ink dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-chart-area text-primary text-xs"></i>
                        Sales & Order Trends (Last 14 Days)
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Daily gross revenue and order frequency across store checkout</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center gap-1.5 text-slate-500">
                        <span class="w-3 h-3 rounded-sm bg-primary inline-block"></span> Revenue (৳)
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-slate-500">
                        <span class="w-3 h-3 rounded-sm bg-emerald-500 inline-block"></span> Orders
                    </span>
                </div>
            </div>

            <div class="chart-container">
                <canvas id="salesTrendsChart"></canvas>
            </div>
        </div>

        <!-- Distribution Breakdown: Payment Methods & Order Pipeline (Span 1 col) -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
            <div class="pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-ink dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-amber-500 text-xs"></i>
                        Distribution
                    </h2>
                    <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-0.5 rounded-lg text-[11px]">
                        <button type="button" id="tabPaymentBtn" onclick="switchDistributionTab('payment')"
                                class="px-2.5 py-1 rounded-md font-semibold text-primary bg-white dark:bg-slate-700 shadow-xs transition-all cursor-pointer">
                            Payment
                        </button>
                        <button type="button" id="tabStatusBtn" onclick="switchDistributionTab('status')"
                                class="px-2.5 py-1 rounded-md font-medium text-slate-500 hover:text-ink dark:hover:text-white transition-all cursor-pointer">
                            Pipeline
                        </button>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-1" id="distributionSubtitle">Customer checkout payment preference</p>
            </div>

            <div class="chart-container my-auto flex items-center justify-center relative">
                <canvas id="paymentMethodsChart"></canvas>
                <canvas id="orderStatusesChart" style="display: none;"></canvas>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-around text-center text-xs">
                <div>
                    <span class="block text-[10px] text-slate-400 uppercase font-semibold">Customers</span>
                    <span class="font-bold text-ink dark:text-white">{{ $totalCustomers }}</span>
                </div>
                <div class="border-l border-slate-100 dark:border-slate-800 pl-4">
                    <span class="block text-[10px] text-slate-400 uppercase font-semibold">Reviews</span>
                    <span class="font-bold text-ink dark:text-white">{{ $pendingReviewsCount }} Pending</span>
                </div>
                <div class="border-l border-slate-100 dark:border-slate-800 pl-4">
                    <span class="block text-[10px] text-slate-400 uppercase font-semibold">Stock Alerts</span>
                    <span class="font-bold text-rose-600">{{ $lowStockCount ?? count($lowStockVariants) }} Items</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Feeds: Orders, Prescriptions, Low Stock -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- 1. Recent Orders Feed -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="flex items-center justify-between p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </span>
                    <h2 class="text-sm font-bold text-ink dark:text-white">Recent Orders</h2>
                </div>
                <a href="{{ route('store.admin.orders.index') }}" class="text-xs text-primary font-semibold hover:underline">
                    View All &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-400 text-[10px] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="px-4 py-2.5">Order</th>
                            <th class="px-4 py-2.5">Customer</th>
                            <th class="px-4 py-2.5">Total</th>
                            <th class="px-4 py-2.5">Status</th>
                            <th class="px-4 py-2.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-ink dark:text-white block">{{ $order->order_number }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $order->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    {{ $order->shipping_name ?? $order->customer?->name ?? 'Guest' }}
                                </td>
                                <td class="px-4 py-3 font-bold text-ink dark:text-white">
                                    ৳ {{ number_format($order->total, 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $order->status_badge_class }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('store.admin.orders.show', $order) }}"
                                       class="inline-flex items-center p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-800" title="View Order">
                                        <i class="fa-solid fa-arrow-right text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">
                                    No orders placed yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Prescriptions Needing Pharmacist Attention -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="flex items-center justify-between p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-file-prescription"></i>
                    </span>
                    <h2 class="text-sm font-bold text-ink dark:text-white">Prescriptions Inbox</h2>
                </div>
                <a href="{{ route('store.admin.prescriptions.index') }}" class="text-xs text-primary font-semibold hover:underline">
                    Review Queue &rarr;
                </a>
            </div>

            <div class="p-4 space-y-3">
                @forelse($recentPrescriptions as $rx)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            @if($rx->image_url)
                                <img src="{{ $rx->image_url }}" alt="Rx" class="w-10 h-10 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-file-image"></i>
                                </div>
                            @endif
                            <div>
                                <span class="font-bold text-xs text-ink dark:text-white block">{{ $rx->prescription_number }}</span>
                                <span class="text-[11px] text-slate-400">
                                    {{ $rx->customer_name ?? $rx->customer?->name ?? 'Patient' }} &bull; {{ $rx->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <a href="{{ route('store.admin.prescriptions.show', $rx) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold bg-primary hover:bg-primarydark text-white transition-colors">
                            <span>Process</span>
                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </a>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-circle-check text-2xl text-emerald-500 mb-2"></i>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300">All prescription uploads reviewed!</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">No patients currently waiting for quotes.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Inventory Low Stock Watchlist & Reviews Alert -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Low Stock Alerts -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="flex items-center justify-between p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </span>
                    <h2 class="text-sm font-bold text-ink dark:text-white">Low Stock Watchlist (&le; 10 Units)</h2>
                </div>
                <a href="{{ route('store.admin.inventory.index') }}" class="text-xs text-primary font-semibold hover:underline">
                    Manage Inventory &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-400 text-[10px] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="px-4 py-2.5">Product & SKU</th>
                            <th class="px-4 py-2.5">Available</th>
                            <th class="px-4 py-2.5">Reserved</th>
                            <th class="px-4 py-2.5">Threshold</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($lowStockVariants as $var)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-2.5">
                                    <span class="font-semibold text-ink dark:text-white block">{{ $var->product?->name ?? 'Product' }}</span>
                                    <span class="text-[10px] text-slate-400">SKU: {{ $var->sku }}</span>
                                </td>
                                <td class="px-4 py-2.5 font-bold {{ $var->available_stock === 0 ? 'text-rose-600' : 'text-amber-600' }}">
                                    {{ $var->available_stock }} units
                                </td>
                                <td class="px-4 py-2.5 text-slate-500">
                                    {{ $var->reserved_stock }}
                                </td>
                                <td class="px-4 py-2.5">
                                    @if($var->available_stock === 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            Out of Stock
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            Reorder Soon
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-400 text-xs">
                                    All inventory batches are well stocked!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Customer Reviews -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="flex items-center justify-between p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-star-half-stroke"></i>
                    </span>
                    <h2 class="text-sm font-bold text-ink dark:text-white">Customer Reviews Moderation</h2>
                </div>
                <a href="{{ route('store.admin.reviews.index') }}" class="text-xs text-primary font-semibold hover:underline">
                    View All &rarr;
                </a>
            </div>

            <div class="p-4 space-y-3">
                @forelse($recentReviews as $rev)
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-xs text-ink dark:text-white truncate max-w-[200px]">
                                {{ $rev->product?->name ?? 'Product' }}
                            </span>
                            <div class="flex items-center text-amber-400 text-xs">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-solid fa-star {{ $i <= $rev->rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700' }} text-[10px]"></i>
                                @endfor
                            </div>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 line-clamp-2">
                            "{{ $rev->comment }}"
                        </p>
                        <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-200/50 dark:border-slate-700/50 text-[10px] text-slate-400">
                            <span>By {{ $rev->customer_name }} &bull; {{ $rev->created_at->diffForHumans() }}</span>
                            <span class="font-semibold uppercase tracking-wider {{ $rev->status === 'approved' ? 'text-emerald-600' : ($rev->status === 'pending' ? 'text-amber-600' : 'text-rose-600') }}">
                                {{ $rev->status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-400 text-xs">
                        No customer reviews submitted yet.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

@push('js')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    function switchDistributionTab(tab) {
        const paymentCanvas = document.getElementById('paymentMethodsChart');
        const statusCanvas = document.getElementById('orderStatusesChart');
        const paymentBtn = document.getElementById('tabPaymentBtn');
        const statusBtn = document.getElementById('tabStatusBtn');
        const subtitle = document.getElementById('distributionSubtitle');

        if (tab === 'payment') {
            paymentCanvas.style.display = 'block';
            statusCanvas.style.display = 'none';
            paymentBtn.className = 'px-2.5 py-1 rounded-md font-semibold text-primary bg-white dark:bg-slate-700 shadow-xs transition-all cursor-pointer';
            statusBtn.className = 'px-2.5 py-1 rounded-md font-medium text-slate-500 hover:text-ink dark:hover:text-white transition-all cursor-pointer';
            subtitle.innerText = 'Customer checkout payment preference';
        } else {
            paymentCanvas.style.display = 'none';
            statusCanvas.style.display = 'block';
            statusBtn.className = 'px-2.5 py-1 rounded-md font-semibold text-primary bg-white dark:bg-slate-700 shadow-xs transition-all cursor-pointer';
            paymentBtn.className = 'px-2.5 py-1 rounded-md font-medium text-slate-500 hover:text-ink dark:hover:text-white transition-all cursor-pointer';
            subtitle.innerText = 'Order lifecycle & fulfillment pipeline distribution';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // 1. Dual-Axis Sales & Orders Trend Line/Bar Chart
        const trendCtx = document.getElementById('salesTrendsChart');
        if (trendCtx) {
            new Chart(trendCtx, {
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [
                        {
                            type: 'line',
                            label: 'Revenue (৳)',
                            data: {!! json_encode($chartRevenue) !!},
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            yAxisID: 'yRev',
                            pointRadius: 3,
                            pointHoverRadius: 5
                        },
                        {
                            type: 'bar',
                            label: 'Orders Placed',
                            data: {!! json_encode($chartOrders) !!},
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderRadius: 6,
                            barThickness: 14,
                            yAxisID: 'yOrders'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (context.dataset.label.includes('Revenue')) {
                                        return 'Revenue: ৳ ' + Number(context.parsed.y).toLocaleString();
                                    }
                                    return 'Orders: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false }
                        },
                        yRev: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            ticks: {
                                callback: function(val) {
                                    return '৳' + (val >= 1000 ? (val/1000).toFixed(0) + 'k' : val);
                                }
                            },
                            grid: { color: 'rgba(226, 232, 240, 0.4)' }
                        },
                        yOrders: {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            grid: { display: false },
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        // 2. Orders by Payment Method Doughnut
        const paymentCtx = document.getElementById('paymentMethodsChart');
        if (paymentCtx) {
            new Chart(paymentCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($paymentMethodLabels) !!},
                    datasets: [{
                        data: {!! json_encode($paymentMethodCounts) !!},
                        backgroundColor: [
                            '#2563eb', // primary blue
                            '#e11d48', // bKash rose
                            '#f59e0b', // Nagad amber
                            '#10b981', // emerald
                            '#8b5cf6'  // purple
                        ],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 12,
                                font: { size: 11 }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }

        // 3. Order Status Pipeline Doughnut
        const statusCtx = document.getElementById('orderStatusesChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($statusLabels) !!},
                    datasets: [{
                        data: {!! json_encode($statusCounts) !!},
                        backgroundColor: [
                            '#10b981', // Delivered (emerald)
                            '#3b82f6', // Processing (blue)
                            '#f59e0b', // Pending (amber)
                            '#6366f1', // Shipped (indigo)
                            '#f43f5e'  // Cancelled (rose)
                        ],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 12,
                                font: { size: 11 }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    });
</script>
@endpush
