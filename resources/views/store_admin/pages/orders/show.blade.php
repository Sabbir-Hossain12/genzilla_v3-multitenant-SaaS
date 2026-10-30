@extends('store_admin.layout.master')
@section('contents')
@php
    $field = 'w-full rounded-xl border-slate-200 bg-white text-xs text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500 transition';

    // Calculate stepper state
    $isCancelled = $order->status === 'cancelled';
    $isReturned = $order->status === 'returned';

    $step1Done = true; // Placed
    $step2Done = in_array($order->status, ['confirmed', 'processing', 'shipped', 'delivered']);
    $step3Done = in_array($order->status, ['shipped', 'delivered']);
    $step4Done = $order->status === 'delivered';

    $progressPercent = match ($order->status) {
        'pending' => 12,
        'confirmed', 'processing' => 45,
        'shipped' => 78,
        'delivered' => 100,
        default => 0,
    };
@endphp

<div class="space-y-6">
    {{-- Top Action Header (Hidden on Print) --}}
    <div class="flex flex-wrap items-center justify-between gap-4 print:hidden">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.admin.orders.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 transition">
                    <i class="fa-solid fa-arrow-left"></i> Back to Orders
                </a>
                <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                <span class="text-xs text-slate-400">Order Details</span>
            </div>
            <div class="flex flex-wrap items-center gap-3 mt-1.5">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                    Order <span class="font-mono text-indigo-600 dark:text-indigo-400">#{{ $order->order_number }}</span>
                </h1>
                <button type="button" onclick="copyOrderNumber('{{ $order->order_number }}')" id="copy-btn" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-[11px] font-medium text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition" title="Copy Order Number">
                    <i class="fa-regular fa-copy mr-1"></i> <span id="copy-text">Copy</span>
                </button>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border {{ $order->status_badge_class }}">
                    <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>
                    {{ ucfirst($order->status) }}
                </span>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border {{ $order->payment_status_badge_class }}">
                    <i class="fa-solid fa-coins mr-1 text-[10px]"></i>
                    {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                </span>
                @if($order->prescription_status && $order->prescription_status !== 'not_required')
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border {{ $order->prescription_status === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : ($order->prescription_status === 'rejected' ? 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800' : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800') }}">
                        <i class="fa-solid fa-file-prescription mr-1 text-[10px]"></i>
                        Prescription: {{ ucfirst(str_replace('_', ' ', $order->prescription_status)) }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-400 font-mono mt-1">
                Placed on {{ $order->placed_at?->format('F j, Y \a\t g:i A') ?? $order->created_at->format('F j, Y \a\t g:i A') }} &bull; Public ID: <span class="select-all">{{ $order->public_id }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 transition">
                <i class="fa-solid fa-print"></i> Print Invoice
            </button>
            @can('Store Order Create')
                <a href="{{ route('store.admin.orders.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                    <i class="fa-solid fa-plus"></i> New Order
                </a>
            @endcan
        </div>
    </div>

    {{-- Feedback Notifications --}}
    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-3 shadow-sm print:hidden">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-300">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl bg-rose-50 p-4 text-sm text-rose-800 dark:bg-rose-950/40 dark:text-rose-200 border border-rose-200 dark:border-rose-800 shadow-sm print:hidden">
            <div class="font-semibold flex items-center gap-1.5 mb-1">
                <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400"></i> Action failed:
            </div>
            <ul class="list-inside list-disc text-xs space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Order Lifecycle Stepper / Progress Bar (Print:hidden) --}}
    <div class="print:hidden">
        @if($isCancelled)
            <div class="card p-5 border-l-4 border-l-rose-500 bg-rose-50/40 dark:bg-rose-950/20">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400">
                        <i class="fa-solid fa-ban text-lg"></i>
                    </div>
                    <div class="space-y-1">
                        <h2 class="text-sm font-bold text-rose-900 dark:text-rose-200">Order Cancelled</h2>
                        <p class="text-xs text-rose-700 dark:text-rose-300">
                            This order was cancelled on {{ $order->cancelled_at?->format('F j, Y \a\t g:i A') ?? 'N/A' }}.
                            @if($order->cancel_reason)
                                <br><span class="font-medium text-rose-800 dark:text-rose-200">Reason:</span> {{ $order->cancel_reason }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @elseif($isReturned)
            <div class="card p-5 border-l-4 border-l-purple-500 bg-purple-50/40 dark:bg-purple-950/20">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-900/40 dark:text-purple-400">
                        <i class="fa-solid fa-arrow-rotate-left text-lg"></i>
                    </div>
                    <div class="space-y-1">
                        <h2 class="text-sm font-bold text-purple-900 dark:text-purple-200">Order Returned</h2>
                        <p class="text-xs text-purple-700 dark:text-purple-300">
                            The parcel for this order has been marked as returned to inventory.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="card p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Fulfillment Progress
                    </h2>
                    <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>

                {{-- Stepper graphic --}}
                <div class="relative">
                    {{-- Progress line background --}}
                    <div class="absolute left-6 right-6 top-5 hidden sm:block h-1 bg-slate-200 dark:bg-slate-700 rounded-full" aria-hidden="true">
                        <div class="h-full bg-gradient-to-r from-indigo-500 to-emerald-500 rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%;"></div>
                    </div>

                    {{-- Steps --}}
                    <div class="relative grid grid-cols-2 gap-4 sm:grid-cols-4">
                        {{-- Step 1: Placed --}}
                        <div class="flex flex-col items-center text-center">
                            <div class="relative flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500 text-white shadow-md ring-4 ring-emerald-50 dark:ring-emerald-950/40">
                                <i class="fa-solid fa-receipt text-xs"></i>
                            </div>
                            <h3 class="mt-2 text-xs font-bold text-slate-900 dark:text-white">Placed</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                {{ $order->placed_at?->format('M j, g:i A') ?? $order->created_at->format('M j, g:i A') }}
                            </p>
                        </div>

                        {{-- Step 2: Processing --}}
                        <div class="flex flex-col items-center text-center">
                            <div class="relative flex h-10 w-10 items-center justify-center rounded-full {{ $step2Done ? 'bg-emerald-500 text-white ring-4 ring-emerald-50 dark:ring-emerald-950/40' : ($order->status === 'pending' ? 'bg-indigo-600 text-white ring-4 ring-indigo-50 dark:ring-indigo-950/40 animate-pulse' : 'bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-400') }} shadow-md">
                                <i class="fa-solid fa-boxes-packing text-xs"></i>
                            </div>
                            <h3 class="mt-2 text-xs font-bold {{ $step2Done ? 'text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-400' }}">Confirmed</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                {{ $step2Done ? 'Processing items' : 'Awaiting review' }}
                            </p>
                        </div>

                        {{-- Step 3: Shipped --}}
                        <div class="flex flex-col items-center text-center">
                            <div class="relative flex h-10 w-10 items-center justify-center rounded-full {{ $step3Done ? 'bg-emerald-500 text-white ring-4 ring-emerald-50 dark:ring-emerald-950/40' : (in_array($order->status, ['processing', 'confirmed']) ? 'bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-400' : 'bg-slate-200 text-slate-400 dark:bg-slate-800 dark:text-slate-600') }} shadow-md">
                                <i class="fa-solid fa-truck-fast text-xs"></i>
                            </div>
                            <h3 class="mt-2 text-xs font-bold {{ $step3Done ? 'text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-400' }}">Shipped</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                {{ $order->shipped_at?->format('M j, g:i A') ?? 'Awaiting dispatch' }}
                            </p>
                        </div>

                        {{-- Step 4: Delivered --}}
                        <div class="flex flex-col items-center text-center">
                            <div class="relative flex h-10 w-10 items-center justify-center rounded-full {{ $step4Done ? 'bg-emerald-500 text-white ring-4 ring-emerald-50 dark:ring-emerald-950/40' : 'bg-slate-200 text-slate-400 dark:bg-slate-800 dark:text-slate-600' }} shadow-md">
                                <i class="fa-solid fa-circle-check text-xs"></i>
                            </div>
                            <h3 class="mt-2 text-xs font-bold {{ $step4Done ? 'text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-400' }}">Delivered</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                {{ $order->delivered_at?->format('M j, g:i A') ?? 'Pending delivery' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Printable Invoice Header (Visible only when printing) --}}
    <div class="hidden print:block border-b border-slate-300 pb-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase">INVOICE</h1>
                <p class="text-xs font-mono text-slate-600 mt-1">Invoice #: {{ $order->order_number }}</p>
                <p class="text-xs text-slate-600">Date: {{ $order->placed_at?->format('F j, Y') ?? $order->created_at->format('F j, Y') }}</p>
                <p class="text-xs text-slate-600">Status: {{ ucfirst($order->status) }} | Payment: {{ ucfirst($order->payment_status) }}</p>
            </div>
            <div class="text-right">
                <h2 class="text-base font-bold text-slate-900">GENZILLA PHARMACY & STORE</h2>
                <p class="text-xs text-slate-600">Order Ref: {{ $order->public_id }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6 mt-6 pt-4 border-t border-slate-200 text-xs">
            <div>
                <h3 class="font-bold text-slate-800 uppercase text-[10px] tracking-wider mb-1">Delivered To:</h3>
                <p class="font-semibold text-slate-900">{{ $order->shipping_name }}</p>
                <p class="text-slate-700">{{ $order->shipping_phone }}</p>
                @if($order->shipping_email)<p class="text-slate-700">{{ $order->shipping_email }}</p>@endif
                <p class="text-slate-700 mt-1">
                    {{ $order->shipping_address }}<br>
                    @if($order->shipping_area){{ $order->shipping_area }}, @endif{{ $order->shipping_city }} {{ $order->shipping_postcode }}<br>
                    {{ $order->shipping_country }}
                </p>
            </div>
            <div class="text-right">
                <h3 class="font-bold text-slate-800 uppercase text-[10px] tracking-wider mb-1">Order Summary:</h3>
                <p class="text-slate-700">Payment Method: <span class="uppercase font-semibold">{{ $order->payment_method }}</span></p>
                <p class="text-slate-700">Shipping Method: {{ $order->shipping_method ?? 'Standard Delivery' }}</p>
                @if($order->coupon_code)<p class="text-slate-700">Coupon Used: {{ $order->coupon_code }}</p>@endif
            </div>
        </div>
    </div>

    {{-- Main 2-column layout --}}
    <div class="grid gap-6 lg:grid-cols-[1fr_360px] print:block">
        {{-- Left Column: Items, Breakdown & Payments --}}
        <div class="space-y-6">
            {{-- Items Card --}}
            <section class="card overflow-hidden">
                <div class="border-b border-slate-100 p-4 sm:px-6 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-900 dark:text-white text-base">Order Items</h2>
                        <p class="text-xs text-slate-400">{{ $order->products->count() }} item(s) in this order</p>
                    </div>
                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-mono font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        Total Units: {{ $order->products->sum('quantity') }}
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 dark:bg-slate-800/50 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="p-4 sm:px-6">Product Details</th>
                                <th class="p-4 text-center">SKU</th>
                                <th class="p-4 text-right">Unit Price</th>
                                <th class="p-4 text-center">Qty</th>
                                <th class="p-4 text-right">Discount</th>
                                <th class="p-4 sm:px-6 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($order->products as $item)
                                @php
                                    $product = $item->variant?->product;
                                    $thumbUrl = $product?->thumbnail_url;
                                    if (!$thumbUrl && $product?->media->isNotEmpty()) {
                                        $m = $product->media->first();
                                        $thumbUrl = \Illuminate\Support\Str::startsWith($m->path, ['http://', 'https://']) ? $m->path : asset('storage/' . $m->path);
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                    <td class="p-4 sm:px-6">
                                        <div class="flex items-center gap-3">
                                            {{-- Product Thumbnail --}}
                                            <div class="h-12 w-12 shrink-0 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center">
                                                @if($thumbUrl)
                                                    <img src="{{ $thumbUrl }}" alt="{{ $item->product_name }}" class="h-full w-full object-cover">
                                                @else
                                                    <i class="fa-solid fa-box text-slate-400 text-base"></i>
                                                @endif
                                            </div>

                                            <div>
                                                <div class="font-semibold text-slate-900 dark:text-white leading-snug">
                                                    {{ $item->product_name }}
                                                </div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ $item->variant_title }}
                                                </div>
                                                @if($item->requires_prescription)
                                                    <span class="mt-1 inline-flex items-center gap-1 rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                        <i class="fa-solid fa-file-prescription"></i> Prescription Required
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-mono text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                            {{ $item->sku ?: 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right font-mono text-slate-700 dark:text-slate-300">
                                        {{ $order->currency }} {{ number_format((float)$item->unit_price, 2) }}
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-slate-100 px-2 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                                            {{ $item->quantity }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right font-mono text-slate-500 dark:text-slate-400">
                                        @if((float)$item->discount_amount > 0)
                                            <span class="text-emerald-600 dark:text-emerald-400 font-medium">
                                                -{{ $order->currency }} {{ number_format((float)$item->discount_amount, 2) }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="p-4 sm:px-6 text-right font-bold font-mono text-slate-900 dark:text-white">
                                        {{ $order->currency }} {{ number_format((float)$item->line_total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Financial Summary Footer --}}
                <div class="border-t border-slate-100 bg-slate-50/60 p-5 sm:p-6 dark:border-slate-800 dark:bg-slate-900/40">
                    <div class="ml-auto max-w-sm space-y-2.5 text-xs">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Subtotal:</span>
                            <span class="font-mono font-medium text-slate-900 dark:text-white">{{ $order->currency }} {{ number_format((float)$order->subtotal, 2) }}</span>
                        </div>
                        @if((float)$order->discount_amount > 0)
                            <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                                <span class="flex items-center gap-1.5">
                                    Discount
                                    @if($order->coupon_code)
                                        <span class="rounded bg-emerald-100 px-1.5 py-0.5 font-mono text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <i class="fa-solid fa-tag mr-0.5"></i> {{ $order->coupon_code }}
                                        </span>
                                    @endif
                                    :
                                </span>
                                <span class="font-mono font-bold">-{{ $order->currency }} {{ number_format((float)$order->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Delivery / Shipping Charge ({{ $order->shipping_method ?? 'Standard' }}):</span>
                            <span class="font-mono font-medium text-slate-900 dark:text-white">{{ $order->currency }} {{ number_format((float)$order->shipping_charge, 2) }}</span>
                        </div>
                        @if((float)$order->tax_amount > 0)
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>Tax:</span>
                                <span class="font-mono font-medium text-slate-900 dark:text-white">{{ $order->currency }} {{ number_format((float)$order->tax_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-slate-200 pt-3 text-sm font-bold text-slate-900 dark:border-slate-700 dark:text-white">
                            <span class="text-slate-900 dark:text-white">Grand Total:</span>
                            <span class="font-mono text-base font-extrabold text-indigo-600 dark:text-indigo-400">
                                {{ $order->currency }} {{ number_format((float)$order->total, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Payments Card --}}
            <section class="card p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h2 class="font-bold text-slate-900 dark:text-white text-base">Payment Transactions</h2>
                        <p class="text-xs text-slate-400">Recorded gateway charges and transactions</p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border {{ $order->payment_status_badge_class }}">
                        {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                    </span>
                </div>

                @if($order->payments->isNotEmpty())
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($order->payments as $payment)
                            <div class="py-3 flex flex-wrap items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        @if($payment->payment_method === 'cod')
                                            <i class="fa-solid fa-hand-holding-dollar text-sm"></i>
                                        @elseif($payment->payment_method === 'stripe' || $payment->payment_method === 'card')
                                            <i class="fa-regular fa-credit-card text-sm"></i>
                                        @elseif($payment->payment_method === 'bkash' || $payment->payment_method === 'nagad')
                                            <i class="fa-solid fa-mobile-screen-button text-sm"></i>
                                        @else
                                            <i class="fa-solid fa-money-bill-transfer text-sm"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide">
                                            {{ $payment->payment_method }} &bull; <span class="capitalize text-slate-500 font-normal">{{ $payment->type }}</span>
                                        </div>
                                        <div class="text-slate-400 font-mono text-[11px] mt-0.5">
                                            TXN ID: {{ $payment->transaction_id ?? 'N/A' }}
                                        </div>
                                        @if($payment->paid_at)
                                            <div class="text-[11px] text-slate-400">Paid on {{ $payment->paid_at->format('M j, Y \a\t h:i A') }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold border {{ $payment->status === 'successful' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800' }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                    <div class="font-mono font-bold text-slate-900 dark:text-white text-sm mt-1">
                                        {{ $payment->currency }} {{ number_format((float)$payment->amount, 2) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-4 text-xs text-slate-600 dark:border-slate-800 dark:bg-slate-800/30 dark:text-slate-300 flex items-center gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                            <i class="fa-solid fa-info"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-800 dark:text-slate-200">
                                Payment Method: <strong class="uppercase text-indigo-600 dark:text-indigo-400">{{ $order->payment_method }}</strong>
                            </div>
                            <p class="text-slate-500 dark:text-slate-400 mt-0.5">
                                @if($order->payment_method === 'cod')
                                    Cash on Delivery payment will be collected from customer upon physical handover.
                                @else
                                    No external gateway transaction logged for this manual order entry.
                                @endif
                            </p>
                        </div>
                    </div>
                @endif
            </section>

            {{-- Order Notes (if available) --}}
            @if($order->order_note)
                <section class="card p-5 space-y-2">
                    <h2 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-regular fa-comment-dots text-indigo-500"></i> Customer & Order Note
                    </h2>
                    <div class="rounded-xl bg-slate-50 p-3.5 text-xs text-slate-700 dark:bg-slate-800/40 dark:text-slate-300 border border-slate-100 dark:border-slate-800 italic">
                        "{{ $order->order_note }}"
                    </div>
                </section>
            @endif
        </div>

        {{-- Right Column: Status Controls & Customer Info --}}
        <div class="space-y-6">
            {{-- Update Status Card (Print:hidden) --}}
            @can('Store Order Edit')
                <section class="card p-5 space-y-4 print:hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                        <h2 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                            <i class="fa-solid fa-sliders text-indigo-600 dark:text-indigo-400 text-sm"></i>
                            Fulfillment Control
                        </h2>
                    </div>

                    <form method="POST" action="{{ route('store.admin.orders.status', $order) }}" class="space-y-3.5">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Order Status</label>
                            <select name="status" class="{{ $field }} p-2.5">
                                <option value="pending" @selected($order->status === 'pending')>Pending</option>
                                <option value="confirmed" @selected($order->status === 'confirmed')>Confirmed</option>
                                <option value="processing" @selected($order->status === 'processing')>Processing</option>
                                <option value="shipped" @selected($order->status === 'shipped')>Shipped</option>
                                <option value="delivered" @selected($order->status === 'delivered')>Delivered</option>
                                <option value="cancelled" @selected($order->status === 'cancelled')>Cancelled</option>
                                <option value="returned" @selected($order->status === 'returned')>Returned</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Payment Status</label>
                            <select name="payment_status" class="{{ $field }} p-2.5">
                                <option value="unpaid" @selected($order->payment_status === 'unpaid')>Unpaid</option>
                                <option value="paid" @selected($order->payment_status === 'paid')>Paid</option>
                                <option value="partially_paid" @selected($order->payment_status === 'partially_paid')>Partially Paid</option>
                                <option value="refunded" @selected($order->payment_status === 'refunded')>Refunded</option>
                                <option value="failed" @selected($order->payment_status === 'failed')>Failed</option>
                            </select>
                        </div>

                        @if($order->products->contains('requires_prescription', true))
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Prescription Status</label>
                                <select name="prescription_status" class="{{ $field }} p-2.5">
                                    <option value="pending_review" @selected($order->prescription_status === 'pending_review')>Pending Review</option>
                                    <option value="approved" @selected($order->prescription_status === 'approved')>Approved</option>
                                    <option value="rejected" @selected($order->prescription_status === 'rejected')>Rejected</option>
                                </select>
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Internal Note</label>
                            <textarea name="order_note" rows="2" class="{{ $field }}" placeholder="Add internal notes or customer instruction...">{{ old('order_note', $order->order_note) }}</textarea>
                        </div>

                        <div id="cancel-reason-container" class="{{ $order->status === 'cancelled' ? '' : 'hidden' }}">
                            <label class="block text-xs font-semibold text-rose-700 dark:text-rose-400 mb-1">Cancellation Reason *</label>
                            <textarea name="cancel_reason" rows="2" class="{{ $field }} border-rose-300 dark:border-rose-700" placeholder="Specify why the order is being cancelled...">{{ old('cancel_reason', $order->cancel_reason) }}</textarea>
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-indigo-600 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-floppy-disk"></i> Save Status Changes
                        </button>
                    </form>
                </section>
            @endcan

            {{-- Customer & Shipping Card --}}
            <section class="card p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h2 class="font-bold text-slate-900 dark:text-white text-base">Customer & Shipping</h2>
                </div>

                <div class="space-y-3.5 text-xs">
                    {{-- Recipient info --}}
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 font-bold text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                            {{ strtoupper(substr($order->shipping_name, 0, 2)) }}
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Recipient Name</span>
                            <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $order->shipping_name }}</span>
                            @if($order->customer)
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300 mt-0.5">
                                    Registered User #{{ $order->customer_id }}
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-500 dark:bg-slate-800 dark:text-slate-400 mt-0.5">
                                    Guest Customer
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                            <i class="fa-solid fa-phone text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-slate-400 block text-[11px]">Contact Phone</span>
                            <a href="tel:{{ $order->shipping_phone }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ $order->shipping_phone }}
                            </a>
                        </div>
                    </div>

                    @if($order->shipping_email)
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                <i class="fa-solid fa-envelope text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-slate-400 block text-[11px]">Email Address</span>
                                <a href="mailto:{{ $order->shipping_email }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline truncate block">
                                    {{ $order->shipping_email }}
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 mt-0.5">
                                <i class="fa-solid fa-location-dot text-xs"></i>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Delivery Address</span>
                                <p class="text-slate-800 dark:text-slate-200 mt-0.5 leading-relaxed font-medium">
                                    {{ $order->shipping_address }}<br>
                                    @if($order->shipping_area){{ $order->shipping_area }}, @endif
                                    {{ $order->shipping_city }}@if($order->shipping_postcode) - {{ $order->shipping_postcode }}@endif<br>
                                    {{ $order->shipping_country }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Timeline & Audit Card --}}
            <section class="card p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h2 class="font-bold text-slate-900 dark:text-white text-base">Order Details</h2>
                </div>

                <div class="text-xs space-y-2.5">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Order Method:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200 uppercase font-mono">{{ $order->payment_method }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Shipping Option:</span>
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $order->shipping_method ?? 'Standard' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Placed:</span>
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $order->placed_at?->format('M j, Y h:i A') ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Shipped:</span>
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $order->shipped_at?->format('M j, Y h:i A') ?? 'Not shipped' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Delivered:</span>
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $order->delivered_at?->format('M j, Y h:i A') ?? 'Not delivered' }}</span>
                    </div>
                    @if($order->cancelled_at)
                        <div class="flex justify-between items-center text-rose-600">
                            <span>Cancelled:</span>
                            <span class="font-medium">{{ $order->cancelled_at->format('M j, Y h:i A') }}</span>
                        </div>
                    @endif
                    @if($order->processedBy)
                        <div class="border-t border-slate-100 pt-2.5 dark:border-slate-800 flex justify-between items-center text-slate-500">
                            <span>Handled By:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $order->processedBy->name }}</span>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>

<script>
function copyOrderNumber(num) {
    navigator.clipboard.writeText(num).then(function() {
        const copyText = document.getElementById('copy-text');
        if (copyText) {
            copyText.textContent = 'Copied!';
            setTimeout(() => { copyText.textContent = 'Copy'; }, 2000);
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const statusSelect = document.querySelector('select[name=\"status\"]');
    const cancelContainer = document.getElementById('cancel-reason-container');
    if (statusSelect && cancelContainer) {
        statusSelect.addEventListener('change', function () {
            if (this.value === 'cancelled') {
                cancelContainer.classList.remove('hidden');
            } else {
                cancelContainer.classList.add('hidden');
            }
        });
    }
});
</script>
@endsection
