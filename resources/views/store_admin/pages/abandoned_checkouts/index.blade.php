@extends('store_admin.layout.master')

@section('title', 'Abandoned Checkouts - ' . (auth()->user()->tenant->store_name ?? 'Store Admin'))

@section('contents')
@php
    $field = 'rounded-xl border-slate-300 bg-white text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500';
    $currentTab = $tab ?? 'recoverable';
@endphp

<div class="space-y-6 pb-12">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-1">
                <i class="fa-solid fa-cart-arrow-down"></i>
                <span>Sales Recovery & Merchandising Analytics</span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-ink dark:text-white">Abandoned Checkouts</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Recover potential sales by contacting customers who left items behind, and discover catalog friction points.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold">
                <i class="fa-solid fa-clock-rotate-left text-slate-400"></i>
                <span>Idle Threshold: <strong>2+ Hours</strong></span>
            </span>
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

    {{-- Financial & Operational KPI Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Recoverable Carts --}}
        <div class="card p-5 border border-slate-200/80 dark:border-slate-800 relative overflow-hidden bg-white dark:bg-slate-900 shadow-xs">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Recoverable Carts</p>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1.5">{{ number_format($kpis['total_recoverable_carts']) }}</h3>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-user-check text-emerald-500"></i>
                        <span>Known registered buyers</span>
                    </p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-cart-arrow-down"></i>
                </div>
            </div>
        </div>

        {{-- Card 2: Potential Recoverable Revenue --}}
        <div class="card p-5 border border-slate-200/80 dark:border-slate-800 relative overflow-hidden bg-white dark:bg-slate-900 shadow-xs">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Potential Revenue</p>
                    <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1.5">৳{{ number_format($kpis['recoverable_revenue'], 2) }}</h3>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-coins text-emerald-500"></i>
                        <span>In recoverable cart values</span>
                    </p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-bangladeshi-taka-sign"></i>
                </div>
            </div>
        </div>

        {{-- Card 3: Uncontacted Leads (Follow-Up Needed) --}}
        <div class="card p-5 border border-slate-200/80 dark:border-slate-800 relative overflow-hidden bg-white dark:bg-slate-900 shadow-xs">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Follow-up Needed</p>
                    <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1.5">{{ number_format($kpis['uncontacted_carts']) }}</h3>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-phone-volume text-amber-500"></i>
                        <span>Awaiting staff phone/WhatsApp</span>
                    </p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-headset"></i>
                </div>
            </div>
        </div>

        {{-- Card 4: Top Abandoned Products --}}
        <div class="card p-5 border border-slate-200/80 dark:border-slate-800 relative overflow-hidden bg-white dark:bg-slate-900 shadow-xs">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Stuck Catalog Items</p>
                    <h3 class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1.5">{{ number_format($kpis['total_abandoned_products']) }}</h3>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-chart-line text-indigo-500"></i>
                        <span>৳{{ number_format($kpis['total_lost_revenue'], 0) }} total unpurchased</span>
                    </p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Tabs Navigation --}}
    <div class="card overflow-hidden">
        <div class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 p-3 sm:px-6 sm:py-3.5 flex flex-wrap items-center justify-between gap-4">
            {{-- Tabs --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('store.admin.abandoned_checkouts.index', ['tab' => 'recoverable']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition {{ $currentTab === 'recoverable' ? 'bg-rose-600 text-white shadow-sm shadow-rose-600/30' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700' }}">
                    <i class="fa-solid fa-phone-flip"></i>
                    <span>Recoverable Carts (Tele-Sales)</span>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ $currentTab === 'recoverable' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300' }}">
                        {{ $kpis['total_recoverable_carts'] }}
                    </span>
                    @if($kpis['uncontacted_carts'] > 0)
                        <span class="w-2 h-2 rounded-full bg-amber-400 ring-2 ring-white dark:ring-slate-900 animate-pulse" title="{{ $kpis['uncontacted_carts'] }} need contact"></span>
                    @endif
                </a>

                <a href="{{ route('store.admin.abandoned_checkouts.index', ['tab' => 'products']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition {{ $currentTab === 'products' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700' }}">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Top Abandoned Products (Catalog Analytics)</span>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ $currentTab === 'products' ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300' }}">
                        {{ $kpis['total_abandoned_products'] }}
                    </span>
                </a>
            </div>

            {{-- Filter / Search Area depending on active tab --}}
            <form method="GET" action="{{ route('store.admin.abandoned_checkouts.index') }}" class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                <input type="hidden" name="tab" value="{{ $currentTab }}">

                @if($currentTab === 'recoverable')
                    {{-- Contact Status Filter --}}
                    <select name="contact_status" onchange="this.form.submit()" class="{{ $field }} py-1.5 px-3">
                        <option value="all" {{ ($contactStatus ?? 'all') === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="uncontacted" {{ ($contactStatus ?? '') === 'uncontacted' ? 'selected' : '' }}>⏳ Uncontacted Only ({{ $kpis['uncontacted_carts'] }})</option>
                        <option value="contacted" {{ ($contactStatus ?? '') === 'contacted' ? 'selected' : '' }}>✓ Contacted Only ({{ $kpis['contacted_carts'] }})</option>
                    </select>

                    {{-- Rx Filter --}}
                    <select name="rx_status" onchange="this.form.submit()" class="{{ $field }} py-1.5 px-3">
                        <option value="all" {{ ($rxStatus ?? 'all') === 'all' ? 'selected' : '' }}>All Carts</option>
                        <option value="required" {{ ($rxStatus ?? '') === 'required' ? 'selected' : '' }}>Rx Prescriptions Required</option>
                        <option value="not_required" {{ ($rxStatus ?? '') === 'not_required' ? 'selected' : '' }}>OTC / No Rx Needed</option>
                    </select>
                @endif

                {{-- Text Search --}}
                <div class="relative flex-1 sm:w-64">
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                           placeholder="{{ $currentTab === 'recoverable' ? 'Search customer, phone, item...' : 'Search medicine, SKU...' }}"
                           class="{{ $field }} w-full pl-9 pr-3 py-1.5">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                </div>

                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-xs font-bold hover:bg-slate-800 transition">
                    Filter
                </button>

                @if(!empty($search) || ($contactStatus ?? 'all') !== 'all' || ($rxStatus ?? 'all') !== 'all')
                    <a href="{{ route('store.admin.abandoned_checkouts.index', ['tab' => $currentTab]) }}"
                       class="px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 dark:hover:text-white text-xs font-semibold transition" title="Clear Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </form>
        </div>

        {{-- ============================================================== --}}
        {{-- TAB 1 CONTENT: RECOVERABLE CARTS (Tele-Sales Queue)           --}}
        {{-- ============================================================== --}}
        @if($currentTab === 'recoverable')
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/40 dark:bg-slate-800/20">
                            <th class="py-3.5 px-4 sm:px-6">Customer (Buyer Lead)</th>
                            <th class="py-3.5 px-4">Cart Contents & Value</th>
                            <th class="py-3.5 px-4">Idle Duration</th>
                            <th class="py-3.5 px-4">Follow-up Status & Notes</th>
                            <th class="py-3.5 px-4 sm:px-6 text-right">Quick Contact Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                        @forelse($recoverableCarts as $cart)
                            @php
                                $customer = $cart->customer;
                                $cleanPhone = preg_replace('/[^0-9]/', '', (string) ($customer?->phone ?? ''));
                                $whatsappText = rawurlencode("Hello " . ($customer?->first_name ?? 'there') . ", we noticed you left some medicines in your cart at " . (auth()->user()->tenant->store_name ?? 'our pharmacy') . ". Can we help you complete your order?");
                                $hasRx = $cart->has_prescription_items;
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                {{-- 1. Customer Column --}}
                                <td class="py-4 px-4 sm:px-6 align-top">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 font-bold flex items-center justify-center shrink-0 text-xs border border-rose-200 dark:border-rose-800">
                                            {{ $customer?->initials ?? 'G' }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-ink dark:text-white truncate">
                                                {{ $customer?->full_name ?? 'Registered Customer' }}
                                            </p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate flex items-center gap-1.5 mt-0.5">
                                                <i class="fa-regular fa-envelope text-slate-400 text-[10px]"></i>
                                                <span>{{ $customer?->email ?? 'No email' }}</span>
                                            </p>
                                            <p class="text-[11px] font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5 mt-0.5">
                                                <i class="fa-solid fa-phone text-slate-400 text-[10px]"></i>
                                                <a href="tel:{{ $customer?->phone }}" class="hover:text-rose-600 dark:hover:text-rose-400 hover:underline">
                                                    {{ $customer?->phone ?? 'No phone' }}
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- 2. Cart Contents & Value --}}
                                <td class="py-4 px-4 align-top">
                                    <div>
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <span class="text-sm font-black text-ink dark:text-white">
                                                ৳{{ number_format($cart->subtotal, 2) }}
                                            </span>
                                            <span class="text-[11px] text-slate-400">
                                                ({{ $cart->total_items_count }} {{ Str::plural('item', $cart->total_items_count) }})
                                            </span>
                                            @if($hasRx)
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-[10px] font-bold" title="Contains restricted prescription items">
                                                    <i class="fa-solid fa-file-prescription text-[9px]"></i>
                                                    <span>Rx Required</span>
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Items Preview Pill list --}}
                                        <div class="space-y-1">
                                            @foreach($cart->items->take(2) as $item)
                                                <div class="text-[11px] text-slate-600 dark:text-slate-400 truncate max-w-xs flex items-center gap-1">
                                                    <span class="font-bold text-slate-700 dark:text-slate-200">{{ $item->quantity }}x</span>
                                                    <span class="truncate">{{ $item->product?->name }}</span>
                                                    @if($item->productVariant && $item->productVariant->title !== 'Default')
                                                        <span class="text-slate-400">({{ $item->productVariant->title }})</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if($cart->items->count() > 2)
                                                <button type="button" onclick="viewCartDetails({{ $cart->id }})" class="text-[10px] font-bold text-rose-600 dark:text-rose-400 hover:underline">
                                                    +{{ $cart->items->count() - 2 }} more items...
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- 3. Idle Duration --}}
                                <td class="py-4 px-4 align-top whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-[11px]">
                                        <i class="fa-regular fa-clock text-slate-400"></i>
                                        <span>{{ $cart->idle_time }}</span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">
                                        Last active: {{ $cart->updated_at->format('h:i A') }}
                                    </p>
                                </td>

                                {{-- 4. Follow-up Status & Tele-sales Notes --}}
                                <td class="py-4 px-4 align-top max-w-xs">
                                    <div class="space-y-1.5">
                                        @if($cart->is_contacted)
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $cart->contacted_badge_class }}">
                                                <i class="fa-solid fa-check text-[9px]"></i>
                                                <span>Contacted {{ $cart->recovery_contacted_at->diffForHumans() }}</span>
                                            </div>
                                        @else
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $cart->contacted_badge_class }}">
                                                <i class="fa-solid fa-hourglass-start text-[9px] animate-pulse"></i>
                                                <span>Not Contacted</span>
                                            </div>
                                        @endif

                                        @if(!empty($cart->recovery_note))
                                            <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 text-[11px] text-slate-700 dark:text-slate-300 line-clamp-2">
                                                <span class="font-bold text-slate-900 dark:text-white">Note:</span> {{ $cart->recovery_note }}
                                            </div>
                                        @endif

                                        <button type="button" onclick="openNoteModal({{ $cart->id }}, @js($cart->recovery_note), {{ $cart->is_contacted ? 'true' : 'false' }})"
                                                class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white">
                                            <i class="fa-regular fa-pen-to-square text-[10px]"></i>
                                            <span>{{ empty($cart->recovery_note) ? 'Add Note' : 'Edit Note' }}</span>
                                        </button>
                                    </div>
                                </td>

                                {{-- 5. Actions --}}
                                <td class="py-4 px-4 sm:px-6 align-top text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- WhatsApp Link --}}
                                        @if(!empty($cleanPhone))
                                            <a href="https://wa.me/{{ $cleanPhone }}?text={{ $whatsappText }}"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="w-8 h-8 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center text-xs shadow-xs transition hover:scale-105"
                                               title="Chat on WhatsApp with pre-filled message">
                                                <i class="fa-brands fa-whatsapp text-sm"></i>
                                            </a>
                                        @endif

                                        {{-- Phone Call Link --}}
                                        @if(!empty($customer?->phone))
                                            <a href="tel:{{ $customer->phone }}"
                                               class="w-8 h-8 rounded-xl bg-blue-500 hover:bg-blue-600 text-white flex items-center justify-center text-xs shadow-xs transition hover:scale-105"
                                               title="Call Customer Phone">
                                                <i class="fa-solid fa-phone text-xs"></i>
                                            </a>
                                        @endif

                                        {{-- Quick Mark Contacted --}}
                                        @if(!$cart->is_contacted)
                                            <button type="button" onclick="quickMarkContacted({{ $cart->id }})"
                                                    class="h-8 px-2.5 rounded-xl bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 dark:bg-slate-800 dark:hover:bg-emerald-950/40 dark:hover:text-emerald-300 text-slate-700 dark:text-slate-300 font-bold text-[11px] border border-slate-200 dark:border-slate-700 transition flex items-center gap-1"
                                                    title="Mark as contacted">
                                                <i class="fa-solid fa-check text-[10px]"></i>
                                                <span class="hidden xl:inline">Contacted</span>
                                            </button>
                                        @endif

                                        {{-- View Cart Details --}}
                                        <button type="button" onclick="viewCartDetails({{ $cart->id }})"
                                                class="w-8 h-8 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center text-xs shadow-xs transition"
                                                title="View Cart Details & Items">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>

                                        {{-- Delete Cart --}}
                                        <form method="POST" action="{{ route('store.admin.abandoned_checkouts.destroy', $cart->id) }}"
                                              onsubmit="return confirm('Are you sure you want to remove this abandoned cart?')"
                                              class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="w-8 h-8 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 dark:hover:text-rose-400 text-slate-400 flex items-center justify-center text-xs shadow-xs transition"
                                                    title="Purge Cart">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center">
                                    <div class="max-w-sm mx-auto text-center space-y-3">
                                        <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto text-xl">
                                            <i class="fa-solid fa-cart-shopping"></i>
                                        </div>
                                        <h4 class="text-base font-bold text-ink dark:text-white">No Recoverable Carts Found</h4>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            No registered customers have abandoned items idle for 2+ hours matching your current filter criteria.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Tab 1 Pagination --}}
            @if($recoverableCarts->hasPages())
                <div class="p-4 sm:p-5 border-t border-slate-100 dark:border-slate-800">
                    {{ $recoverableCarts->appends(request()->query())->links() }}
                </div>
            @endif

        {{-- ============================================================== --}}
        {{-- TAB 2 CONTENT: TOP ABANDONED PRODUCTS (Catalog Analytics)     --}}
        {{-- ============================================================== --}}
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/40 dark:bg-slate-800/20">
                            <th class="py-3.5 px-4 sm:px-6">Product & Variant</th>
                            <th class="py-3.5 px-4">Abandoned Carts</th>
                            <th class="py-3.5 px-4">Stuck Units</th>
                            <th class="py-3.5 px-4">Selling Price</th>
                            <th class="py-3.5 px-4">Lost Revenue Potential</th>
                            <th class="py-3.5 px-4 sm:px-6 text-right">Catalog Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                        @forelse($topAbandonedProducts as $item)
                            @php
                                $product = $item->product;
                                $variant = $item->productVariant;
                                $unitPrice = (float) ($variant?->price ?? $product?->defaultVariant?->price ?? 0);
                                $potentialLoss = (float) ($item->lost_revenue ?? ($unitPrice * $item->total_abandoned_qty));
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                {{-- 1. Product Info --}}
                                <td class="py-4 px-4 sm:px-6 align-middle">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shrink-0 flex items-center justify-center">
                                            @if($product?->thumbnail_url)
                                                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                            @else
                                                <i class="fa-solid fa-pills text-slate-400 text-lg"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-ink dark:text-white truncate max-w-sm">
                                                {{ $product?->name ?? 'Unknown Medicine' }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[11px] font-mono font-medium text-slate-500 dark:text-slate-400">
                                                    SKU: {{ $variant?->sku ?? 'N/A' }}
                                                </span>
                                                @if($variant && $variant->title !== 'Default')
                                                    <span class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-[10px] font-bold text-slate-600 dark:text-slate-300">
                                                        {{ $variant->title }}
                                                    </span>
                                                @endif
                                                @if($product?->requires_prescription)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 text-[10px] font-bold">
                                                        <i class="fa-solid fa-file-prescription text-[8px]"></i>
                                                        <span>Rx Required</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- 2. Abandoned Carts Count --}}
                                <td class="py-4 px-4 align-middle">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800 font-bold text-xs">
                                        <i class="fa-solid fa-users-slash text-[10px]"></i>
                                        <span>{{ number_format($item->abandoned_carts_count) }} Shoppers</span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">Unique guest & customer carts</p>
                                </td>

                                {{-- 3. Total Stuck Quantity --}}
                                <td class="py-4 px-4 align-middle">
                                    <span class="text-sm font-extrabold text-slate-900 dark:text-white">
                                        {{ number_format($item->total_abandoned_qty) }}
                                    </span>
                                    <span class="text-[11px] text-slate-400">units</span>
                                </td>

                                {{-- 4. Unit Selling Price --}}
                                <td class="py-4 px-4 align-middle">
                                    <span class="font-bold text-slate-700 dark:text-slate-300">
                                        ৳{{ number_format($unitPrice, 2) }}
                                    </span>
                                </td>

                                {{-- 5. Lost Revenue Potential --}}
                                <td class="py-4 px-4 align-middle">
                                    <span class="text-sm font-black text-rose-600 dark:text-rose-400">
                                        ৳{{ number_format($potentialLoss, 2) }}
                                    </span>
                                    <p class="text-[10px] text-slate-400">Stuck gross GMV</p>
                                </td>

                                {{-- 6. Actions --}}
                                <td class="py-4 px-4 sm:px-6 align-middle text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($product)
                                            <a href="{{ route('store.admin.products.edit', $product->id) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-xs transition"
                                               title="Edit Product & Adjust Pricing">
                                                <i class="fa-solid fa-tags text-indigo-500 text-[11px]"></i>
                                                <span>Adjust Price</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="max-w-sm mx-auto text-center space-y-3">
                                        <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto text-xl">
                                            <i class="fa-solid fa-boxes-packing"></i>
                                        </div>
                                        <h4 class="text-base font-bold text-ink dark:text-white">No Abandoned Products</h4>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            No cart items have sat idle for 2+ hours across customer or guest traffic.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Tab 2 Pagination --}}
            @if($topAbandonedProducts->hasPages())
                <div class="p-4 sm:p-5 border-t border-slate-100 dark:border-slate-800">
                    {{ $topAbandonedProducts->appends(request()->query())->links() }}
                </div>
            @endif
        @endif
    </div>

</div>

{{-- ============================================================== --}}
{{-- MODAL 1: INTERACTIVE CART DETAILS SNAPSHOT                     --}}
{{-- ============================================================== --}}
<div id="cartDetailsModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden my-8">
        {{-- Modal Header --}}
        <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-800/40">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-basket-shopping"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-ink dark:text-white flex items-center gap-2">
                        <span>Cart Snapshot</span>
                        <span id="modalCartIdBadge" class="text-xs font-mono font-bold text-slate-400"></span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Detailed contents left by customer before leaving the checkout.
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeCartDetailsModal()" class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center justify-center text-lg font-bold">
                &times;
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="p-5 sm:p-6 space-y-6 max-h-[75vh] overflow-y-auto">
            {{-- Customer Contact Card --}}
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Lead Customer</p>
                    <h4 id="modalCustomerName" class="text-base font-extrabold text-ink dark:text-white mt-0.5"></h4>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600 dark:text-slate-400 mt-1">
                        <span id="modalCustomerPhone" class="font-semibold text-slate-900 dark:text-white"></span>
                        <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                        <span id="modalCustomerEmail"></span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a id="modalWhatsAppBtn" href="#" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-xs transition">
                        <i class="fa-brands fa-whatsapp text-sm"></i>
                        <span>WhatsApp</span>
                    </a>
                    <a id="modalPhoneBtn" href="#"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition">
                        <i class="fa-solid fa-phone text-xs"></i>
                        <span>Call</span>
                    </a>
                </div>
            </div>

            {{-- Cart Items Table --}}
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2.5">Items in Cart</h4>
                <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <tr>
                                <th class="py-2.5 px-3.5">Item</th>
                                <th class="py-2.5 px-3 text-center">Qty</th>
                                <th class="py-2.5 px-3 text-right">Price</th>
                                <th class="py-2.5 px-3.5 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody id="modalItemsBody" class="divide-y divide-slate-100 dark:divide-slate-800">
                            {{-- Injected dynamically --}}
                        </tbody>
                        <tfoot class="bg-slate-50/70 dark:bg-slate-800/40 border-t border-slate-200 dark:border-slate-800 font-bold">
                            <tr>
                                <td colspan="3" class="py-3 px-3.5 text-right text-slate-600 dark:text-slate-300">Total Cart Value:</td>
                                <td id="modalSubtotal" class="py-3 px-3.5 text-right text-sm font-black text-rose-600 dark:text-rose-400"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Tele-sales Follow-up & Note Form --}}
            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-clipboard-check text-rose-500"></i>
                        <span>Tele-Sales Log & Note</span>
                    </h4>
                    <span id="modalContactedBadge" class="text-[10px] font-bold px-2 py-0.5 rounded-full border"></span>
                </div>

                <form id="modalNoteForm" onsubmit="submitModalNote(event)">
                    <input type="hidden" id="modalFormCartId" value="">
                    <textarea id="modalRecoveryNote" rows="3"
                              placeholder="e.g. Called patient, customer asked to request prescription slip on WhatsApp..."
                              class="w-full rounded-xl border border-slate-300 bg-slate-50 p-3 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>

                    <div class="mt-2.5 flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <input type="checkbox" id="modalMarkContactedCheckbox" class="rounded text-rose-600 focus:ring-rose-500">
                            <span>Mark as Contacted</span>
                        </label>

                        <button type="submit" id="modalSaveNoteBtn"
                                class="px-4 py-2 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold text-xs hover:bg-slate-800 transition shadow-xs">
                            Save Note
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================== --}}
{{-- MODAL 2: QUICK NOTE / FOLLOW-UP MODAL                          --}}
{{-- ============================================================== --}}
<div id="contactNoteModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-800/40">
            <h3 class="text-base font-bold text-ink dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-rose-500"></i>
                <span>Tele-Sales Note</span>
            </h3>
            <button type="button" onclick="closeNoteModal()" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-lg font-bold">&times;</button>
        </div>

        <form id="quickNoteForm" onsubmit="submitQuickNote(event)" class="p-5 space-y-4">
            <input type="hidden" id="quickNoteCartId" value="">
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Staff Follow-up Note</label>
                <textarea id="quickNoteText" rows="4"
                          placeholder="e.g. Waiting for prescription upload on WhatsApp..."
                          class="w-full rounded-xl border border-slate-300 bg-white p-3 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
            </div>

            <div class="flex items-center justify-between pt-2">
                <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" id="quickMarkContactedCheck" class="rounded text-rose-600 focus:ring-rose-500">
                    <span>Stamp Contacted</span>
                </label>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeNoteModal()" class="px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition">
                        Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // ==========================================
    // MODAL HANDLERS & AJAX ACTIONS
    // ==========================================

    function viewCartDetails(cartId) {
        const modal = document.getElementById('cartDetailsModal');
        const itemsBody = document.getElementById('modalItemsBody');
        itemsBody.innerHTML = '<tr><td colspan="4" class="py-6 text-center text-slate-400"><i class="fa-solid fa-spinner fa-spin text-lg"></i></td></tr>';
        modal.classList.remove('hidden');

        fetch(`{{ url('/store/abandoned-checkouts') }}/${cartId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalCartIdBadge').textContent = `#CART-${data.id}`;
            document.getElementById('modalFormCartId').value = data.id;
            document.getElementById('modalCustomerName').textContent = data.customer.name;
            document.getElementById('modalCustomerPhone').textContent = data.customer.phone || 'No phone provided';
            document.getElementById('modalCustomerEmail').textContent = data.customer.email || 'No email';

            // WhatsApp & Phone Buttons
            const waBtn = document.getElementById('modalWhatsAppBtn');
            const phoneBtn = document.getElementById('modalPhoneBtn');
            if (data.customer.whatsapp_url) {
                waBtn.href = data.customer.whatsapp_url;
                waBtn.style.display = 'inline-flex';
            } else {
                waBtn.style.display = 'none';
            }

            if (data.customer.phone) {
                phoneBtn.href = `tel:${data.customer.phone}`;
                phoneBtn.style.display = 'inline-flex';
            } else {
                phoneBtn.style.display = 'none';
            }

            // Items table
            let rowsHtml = '';
            data.items.forEach(item => {
                rowsHtml += `
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                        <td class="py-2.5 px-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shrink-0 flex items-center justify-center">
                                    ${item.thumbnail_url ? `<img src="${item.thumbnail_url}" class="w-full h-full object-cover">` : '<i class="fa-solid fa-pills text-slate-400 text-xs"></i>'}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white">${item.product_name}</p>
                                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                                        <span>SKU: ${item.sku}</span>
                                        ${item.requires_prescription ? '<span class="text-amber-600 font-bold">Rx Required</span>' : ''}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-center font-bold text-slate-700 dark:text-slate-300">${item.quantity}</td>
                        <td class="py-2.5 px-3 text-right font-medium text-slate-600 dark:text-slate-400">৳${Number(item.price).toFixed(2)}</td>
                        <td class="py-2.5 px-3.5 text-right font-bold text-slate-900 dark:text-white">৳${Number(item.line_total).toFixed(2)}</td>
                    </tr>
                `;
            });
            itemsBody.innerHTML = rowsHtml;
            document.getElementById('modalSubtotal').textContent = `৳${Number(data.subtotal).toFixed(2)}`;

            // Tele-sales Status & Note
            const badge = document.getElementById('modalContactedBadge');
            if (data.is_contacted) {
                badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full border bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
                badge.textContent = `Contacted ${data.recovery_contacted_at}`;
            } else {
                badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full border bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200 dark:border-amber-800';
                badge.textContent = 'Not Contacted';
            }

            document.getElementById('modalRecoveryNote').value = data.recovery_note || '';
            document.getElementById('modalMarkContactedCheckbox').checked = data.is_contacted;
        })
        .catch(err => {
            itemsBody.innerHTML = '<tr><td colspan="4" class="py-4 text-center text-rose-500">Failed to load cart details.</td></tr>';
        });
    }

    function closeCartDetailsModal() {
        document.getElementById('cartDetailsModal').classList.add('hidden');
    }

    function submitModalNote(e) {
        e.preventDefault();
        const cartId = document.getElementById('modalFormCartId').value;
        const note = document.getElementById('modalRecoveryNote').value;
        const markContacted = document.getElementById('modalMarkContactedCheckbox').checked;

        fetch(`{{ url('/store/abandoned-checkouts') }}/${cartId}/note`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                recovery_note: note,
                mark_contacted: markContacted
            })
        })
        .then(res => res.json())
        .then(data => {
            alert('Tele-sales note saved successfully!');
            location.reload();
        })
        .catch(err => alert('Failed to save note.'));
    }

    function quickMarkContacted(cartId) {
        if (!confirm('Mark this customer cart as contacted?')) return;

        fetch(`{{ url('/store/abandoned-checkouts') }}/${cartId}/contacted`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            location.reload();
        })
        .catch(err => alert('Failed to mark cart as contacted.'));
    }

    function openNoteModal(cartId, currentNote, isContacted) {
        document.getElementById('quickNoteCartId').value = cartId;
        document.getElementById('quickNoteText').value = currentNote || '';
        document.getElementById('quickMarkContactedCheck').checked = isContacted;
        document.getElementById('contactNoteModal').classList.remove('hidden');
    }

    function closeNoteModal() {
        document.getElementById('contactNoteModal').classList.add('hidden');
    }

    function submitQuickNote(e) {
        e.preventDefault();
        const cartId = document.getElementById('quickNoteCartId').value;
        const note = document.getElementById('quickNoteText').value;
        const markContacted = document.getElementById('quickMarkContactedCheck').checked;

        fetch(`{{ url('/store/abandoned-checkouts') }}/${cartId}/note`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                recovery_note: note,
                mark_contacted: markContacted
            })
        })
        .then(res => res.json())
        .then(data => {
            location.reload();
        })
        .catch(err => alert('Failed to save note.'));
    }

    // Close modals on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCartDetailsModal();
            closeNoteModal();
        }
    });
</script>
@endsection
