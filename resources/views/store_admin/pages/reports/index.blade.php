@extends('store_admin.layout.master')

@push('title')
    Reports & Business Analytics
@endpush

@section('contents')
<div class="space-y-6">

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-primary/10 text-primary dark:bg-primary/20">
                    <i class="fa-solid fa-chart-pie text-base"></i>
                </span>
                <h1 class="text-xl md:text-2xl font-bold text-ink dark:text-white">Reports & Business Analytics</h1>
            </div>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Generate, preview, and download compliance and financial reports in Excel or PDF format.
            </p>
        </div>

        <!-- Export Buttons -->
        <div class="flex items-center gap-2.5">
            @can('Store Report Export')
                <a href="{{ route('store.admin.reports.export.csv', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm shadow-emerald-600/20 transition-colors">
                    <i class="fa-solid fa-file-excel text-sm"></i>
                    <span>Export Excel (CSV)</span>
                </a>
                <a href="{{ route('store.admin.reports.export.pdf', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-primary hover:bg-primarydark text-white shadow-sm shadow-primary/25 transition-colors">
                    <i class="fa-solid fa-file-pdf text-sm"></i>
                    <span>Download PDF</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- Report Type Selector Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        @php
            $types = [
                'sales' => ['label' => 'Sales & Orders', 'icon' => 'fa-cart-shopping', 'badge' => $stats['total_orders'] . ' Orders', 'color' => 'blue'],
                'inventory' => ['label' => 'Stock Valuation', 'icon' => 'fa-boxes-stacked', 'badge' => $stats['total_skus'] . ' SKUs', 'color' => 'emerald'],
                'prescriptions' => ['label' => 'Prescriptions', 'icon' => 'fa-file-prescription', 'badge' => $stats['total_prescriptions'] . ' Rx', 'color' => 'purple'],
                'transactions' => ['label' => 'Financial Ledger', 'icon' => 'fa-money-bill-transfer', 'badge' => '৳ Revenue', 'color' => 'amber'],
                'abandoned_carts' => ['label' => 'Abandoned Carts', 'icon' => 'fa-cart-arrow-down', 'badge' => $stats['total_abandoned'] . ' Carts', 'color' => 'rose'],
                'reviews' => ['label' => 'Customer Reviews', 'icon' => 'fa-star-half-stroke', 'badge' => $stats['total_reviews'] . ' Reviews', 'color' => 'indigo'],
            ];
        @endphp

        @foreach($types as $key => $meta)
            <a href="{{ route('store.admin.reports.index', array_merge(request()->except(['page', 'type']), ['type' => $key])) }}"
               class="group relative flex flex-col justify-between p-3.5 rounded-2xl border transition-all duration-200 {{ $type === $key ? 'bg-primary/5 dark:bg-primary/10 border-primary ring-2 ring-primary/20 shadow-sm' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center {{ $type === $key ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 group-hover:bg-primary/10 group-hover:text-primary transition-colors' }}">
                        <i class="fa-solid {{ $meta['icon'] }} text-xs"></i>
                    </span>
                    <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500">
                        {{ $meta['badge'] }}
                    </span>
                </div>
                <div>
                    <h3 class="text-xs font-bold {{ $type === $key ? 'text-primary' : 'text-ink dark:text-white' }} truncate">
                        {{ $meta['label'] }}
                    </h3>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Filter & Parameters Toolbar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 shadow-xs">
        <form method="GET" action="{{ route('store.admin.reports.index') }}" class="space-y-4">
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-filter text-xs text-slate-400"></i>
                    <span class="text-xs font-bold text-ink dark:text-white uppercase tracking-wider">Report Parameters</span>
                </div>

                <!-- Quick Date Presets -->
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <span class="text-slate-400 text-[11px] mr-1">Quick:</span>
                    <button type="button" onclick="setPreset('today')" class="px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 transition-colors">Today</button>
                    <button type="button" onclick="setPreset('7days')" class="px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 transition-colors">Last 7 Days</button>
                    <button type="button" onclick="setPreset('30days')" class="px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 transition-colors">Last 30 Days</button>
                    <button type="button" onclick="setPreset('thisMonth')" class="px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 transition-colors">This Month</button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3.5 items-end">
                <!-- Start Date -->
                <div>
                    <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">From Date</label>
                    <input type="date" id="start_date" name="start_date"
                           value="{{ $reportData['filters']['start_date'] ?? '' }}"
                           class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20">
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">To Date</label>
                    <input type="date" id="end_date" name="end_date"
                           value="{{ $reportData['filters']['end_date'] ?? '' }}"
                           class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20">
                </div>

                <!-- Conditional Filter per Report Type -->
                @if($type === 'sales')
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Order Status</label>
                        <select name="status" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20">
                            <option value="all">All Statuses</option>
                            <option value="pending" {{ ($reportData['filters']['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ ($reportData['filters']['status'] ?? '') === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="shipped" {{ ($reportData['filters']['status'] ?? '') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ ($reportData['filters']['status'] ?? '') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ ($reportData['filters']['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Payment Status</label>
                        <select name="payment_status" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20">
                            <option value="all">All Payment Statuses</option>
                            <option value="paid" {{ ($reportData['filters']['payment_status'] ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="unpaid" {{ ($reportData['filters']['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="refunded" {{ ($reportData['filters']['payment_status'] ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>
                @elseif($type === 'inventory')
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Stock Status</label>
                        <select name="stock_status" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20">
                            <option value="all">All Inventory</option>
                            <option value="in_stock" {{ ($reportData['filters']['stock_status'] ?? '') === 'in_stock' ? 'selected' : '' }}>In Stock (> 10)</option>
                            <option value="low_stock" {{ ($reportData['filters']['stock_status'] ?? '') === 'low_stock' ? 'selected' : '' }}>Low Stock (1 - 10)</option>
                            <option value="out_of_stock" {{ ($reportData['filters']['stock_status'] ?? '') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock (0)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Search Product / SKU</label>
                        <input type="text" name="search" value="{{ $reportData['filters']['search'] ?? '' }}" placeholder="Name or SKU..."
                               class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20">
                    </div>
                @elseif($type === 'prescriptions')
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Verification Status</label>
                        <select name="status" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20">
                            <option value="all">All Statuses</option>
                            <option value="pending" {{ ($reportData['filters']['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                            <option value="converted" {{ ($reportData['filters']['status'] ?? '') === 'converted' ? 'selected' : '' }}>Converted to Order</option>
                            <option value="rejected" {{ ($reportData['filters']['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                @elseif($type === 'transactions')
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Transaction Type</label>
                        <select name="type_filter" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20">
                            <option value="all">All (Charges & Refunds)</option>
                            <option value="charge" {{ ($reportData['filters']['type'] ?? '') === 'charge' ? 'selected' : '' }}>Charges Only</option>
                            <option value="refund" {{ ($reportData['filters']['type'] ?? '') === 'refund' ? 'selected' : '' }}>Refunds Only</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Status</label>
                        <select name="status" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20">
                            <option value="all">All Statuses</option>
                            <option value="successful" {{ ($reportData['filters']['status'] ?? '') === 'successful' ? 'selected' : '' }}>Successful</option>
                            <option value="pending" {{ ($reportData['filters']['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="failed" {{ ($reportData['filters']['status'] ?? '') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                @elseif($type === 'abandoned_carts')
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Tele-Sales Contact Status</label>
                        <select name="contact_filter" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20">
                            <option value="all">All Carts</option>
                            <option value="uncontacted" {{ ($reportData['filters']['contact_filter'] ?? '') === 'uncontacted' ? 'selected' : '' }}>Pending Outreach</option>
                            <option value="contacted" {{ ($reportData['filters']['contact_filter'] ?? '') === 'contacted' ? 'selected' : '' }}>Contacted by Staff</option>
                            <option value="registered_only" {{ ($reportData['filters']['contact_filter'] ?? '') === 'registered_only' ? 'selected' : '' }}>Registered Users Only</option>
                        </select>
                    </div>
                @elseif($type === 'reviews')
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Review Status</label>
                        <select name="status" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20">
                            <option value="all">All Statuses</option>
                            <option value="pending" {{ ($reportData['filters']['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending Moderation</option>
                            <option value="approved" {{ ($reportData['filters']['status'] ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="spam" {{ ($reportData['filters']['status'] ?? '') === 'spam' ? 'selected' : '' }}>Spam</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Star Rating</label>
                        <select name="rating" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20">
                            <option value="all">All Ratings</option>
                            <option value="5" {{ ($reportData['filters']['rating'] ?? '') == '5' ? 'selected' : '' }}>5 Stars ★★★★★</option>
                            <option value="4" {{ ($reportData['filters']['rating'] ?? '') == '4' ? 'selected' : '' }}>4 Stars ★★★★☆</option>
                            <option value="3" {{ ($reportData['filters']['rating'] ?? '') == '3' ? 'selected' : '' }}>3 Stars ★★★☆☆</option>
                            <option value="2" {{ ($reportData['filters']['rating'] ?? '') == '2' ? 'selected' : '' }}>2 Stars ★★☆☆☆</option>
                            <option value="1" {{ ($reportData['filters']['rating'] ?? '') == '1' ? 'selected' : '' }}>1 Star ★☆☆☆☆</option>
                        </select>
                    </div>
                @endif

                <!-- Filter Actions -->
                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-primary hover:bg-primarydark text-white transition-colors">
                        <i class="fa-solid fa-arrows-rotate text-xs"></i>
                        <span>Apply</span>
                    </button>
                    <a href="{{ route('store.admin.reports.index', ['type' => $type]) }}"
                       class="inline-flex items-center justify-center p-2 rounded-xl text-xs font-medium text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Active Report Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5">
        @foreach($reportData['summary'] as $label => $value)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs">
                <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1">
                    {{ ucwords(str_replace('_', ' ', $label)) }}
                </span>
                <span class="text-base sm:text-lg font-bold text-ink dark:text-white">
                    {{ $value }}
                </span>
            </div>
        @endforeach
    </div>

    <!-- Report Preview Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 gap-2">
            <div>
                <h2 class="text-sm sm:text-base font-bold text-ink dark:text-white">
                    {{ $reportData['title'] }}
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ $reportData['description'] }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    Showing top {{ count($reportData['rows']) }} of {{ $reportData['total_count'] }} records
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider text-[10px] border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        @foreach($reportData['headers'] as $header)
                            <th class="px-4 py-3 whitespace-nowrap">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($reportData['rows'] as $row)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            @foreach($row as $colIdx => $col)
                                <td class="px-4 py-3 whitespace-nowrap {{ $colIdx === 0 ? 'font-semibold text-ink dark:text-white' : '' }}">
                                    @if(is_string($col) && in_array(strtolower($col), ['paid', 'successful', 'approved', 'delivered', 'in stock']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            {{ $col }}
                                        </span>
                                    @elseif(is_string($col) && in_array(strtolower($col), ['pending', 'unpaid', 'low stock', 'pending follow-up', 'pending review']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            {{ $col }}
                                        </span>
                                    @elseif(is_string($col) && in_array(strtolower($col), ['cancelled', 'failed', 'refunded', 'rejected', 'out of stock', 'spam']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            {{ $col }}
                                        </span>
                                    @else
                                        {{ $col }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($reportData['headers']) }}" class="px-4 py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto">
                                    <i class="fa-solid fa-inbox text-3xl text-slate-300 dark:text-slate-600 mb-2"></i>
                                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">No records found matching the selected filters.</p>
                                    <p class="text-[11px] text-slate-400 mt-1">Try expanding the date range or clearing status filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reportData['total_count'] > count($reportData['rows']))
            <div class="p-3 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 text-center text-xs text-slate-500">
                <span>Displaying preview of first {{ count($reportData['rows']) }} entries. Click <strong>"Export Excel (CSV)"</strong> or <strong>"Download PDF"</strong> above to retrieve all {{ $reportData['total_count'] }} records.</span>
            </div>
        @endif
    </div>

</div>

@push('js')
<script>
    function setPreset(preset) {
        const startInput = document.getElementById('start_date');
        const endInput = document.getElementById('end_date');
        const today = new Date();

        function formatDate(d) {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        endInput.value = formatDate(today);

        if (preset === 'today') {
            startInput.value = formatDate(today);
        } else if (preset === '7days') {
            const past7 = new Date();
            past7.setDate(today.getDate() - 7);
            startInput.value = formatDate(past7);
        } else if (preset === '30days') {
            const past30 = new Date();
            past30.setDate(today.getDate() - 30);
            startInput.value = formatDate(past30);
        } else if (preset === 'thisMonth') {
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            startInput.value = formatDate(firstDay);
        }

        // Auto submit form
        startInput.closest('form').submit();
    }
</script>
@endpush
@endsection
