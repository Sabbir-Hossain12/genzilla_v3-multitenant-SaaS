@extends('store_admin.layout.master')

@section('contents')
<div class="space-y-6">
    {{-- Top Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.admin.prescriptions.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 transition">
                    <i class="fa-solid fa-arrow-left"></i> Back to Prescriptions
                </a>
                <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                <span class="text-xs text-slate-400 font-mono">{{ $prescription->prescription_number }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-3 mt-1.5">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                    Prescription <span class="font-mono text-indigo-600 dark:text-indigo-400">#{{ $prescription->prescription_number }}</span>
                </h1>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border {{ $prescription->status_badge_class }}">
                    @if($prescription->status === 'pending_review')
                        <i class="fa-regular fa-clock mr-1.5 text-[10px]"></i> Pending Review
                    @elseif($prescription->status === 'converted_to_order')
                        <i class="fa-solid fa-check mr-1.5 text-[10px]"></i> Converted to Order
                    @else
                        <i class="fa-solid fa-ban mr-1.5 text-[10px]"></i> Rejected
                    @endif
                </span>
                @if($prescription->order)
                    <a href="{{ route('store.admin.orders.show', $prescription->order) }}" class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 border border-indigo-200 px-3 py-0.5 text-xs font-mono font-bold text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:border-indigo-800 dark:text-indigo-300 transition">
                        <i class="fa-solid fa-cart-shopping text-[10px]"></i> Order #{{ $prescription->order->order_number }}
                    </a>
                @endif
            </div>
            <p class="text-xs text-slate-400 font-mono mt-1">
                Submitted on {{ $prescription->created_at->format('F j, Y \\a\\t g:i A') }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if($prescription->status === 'pending_review')
                <button type="button" onclick="openRejectModal()" class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-white px-3.5 py-2 text-xs font-semibold text-rose-700 shadow-sm hover:bg-rose-50 dark:border-rose-900 dark:bg-slate-800 dark:text-rose-400 dark:hover:bg-rose-950/40 transition">
                    <i class="fa-solid fa-ban"></i> Reject Prescription
                </button>
            @endif
        </div>
    </div>

    {{-- Notification Alerts --}}
    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-300">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl bg-rose-50 p-4 text-sm text-rose-800 dark:bg-rose-950/40 dark:text-rose-200 border border-rose-200 dark:border-rose-800 shadow-sm">
            <div class="font-semibold flex items-center gap-1.5 mb-1">
                <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400"></i> Action could not be completed:
            </div>
            <ul class="list-inside list-disc text-xs space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Converted or Rejected Status Cards --}}
    @if($prescription->status === 'converted_to_order' && $prescription->order)
        <div class="card p-5 border-l-4 border-l-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-200">Prescription Converted to Order</h3>
                    <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5">
                        This prescription was reviewed and converted into Order <span class="font-mono font-bold">#{{ $prescription->order->order_number }}</span>
                        @if($prescription->reviewedBy)
                            by <strong>{{ $prescription->reviewedBy->name }}</strong>
                        @endif
                        on {{ $prescription->reviewed_at?->format('F j, Y \\a\\t g:i A') ?? $prescription->updated_at->format('F j, Y \\a\\t g:i A') }}.
                    </p>
                    @if($prescription->pharmacist_notes)
                        <div class="mt-2 text-xs bg-white/70 dark:bg-slate-800/70 p-2.5 rounded-lg border border-emerald-200 dark:border-emerald-800">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Pharmacist Notes:</span>
                            <span class="text-slate-600 dark:text-slate-400">{{ $prescription->pharmacist_notes }}</span>
                        </div>
                    @endif
                </div>
            </div>
            <a href="{{ route('store.admin.orders.show', $prescription->order) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 transition">
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> View Active Order
            </a>
        </div>
    @elseif($prescription->status === 'rejected')
        <div class="card p-5 border-l-4 border-l-rose-500 bg-rose-50/40 dark:bg-rose-950/20">
            <div class="flex items-start gap-3.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400">
                    <i class="fa-solid fa-ban text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-rose-900 dark:text-rose-200">Prescription Rejected</h3>
                    <p class="text-xs text-rose-700 dark:text-rose-300 mt-0.5">
                        Rejected on {{ $prescription->reviewed_at?->format('F j, Y \\a\\t g:i A') ?? $prescription->updated_at->format('F j, Y \\a\\t g:i A') }}
                        @if($prescription->reviewedBy) by <strong>{{ $prescription->reviewedBy->name }}</strong> @endif.
                    </p>
                    <div class="mt-2 text-xs bg-white/80 dark:bg-slate-800/80 p-3 rounded-lg border border-rose-200 dark:border-rose-900">
                        <span class="font-bold text-rose-800 dark:text-rose-300">Rejection Reason:</span>
                        <p class="text-slate-700 dark:text-slate-300 mt-0.5 font-medium">{{ $prescription->rejection_reason }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Main 2-Column Split Workspace --}}
    <div class="grid gap-6 lg:grid-cols-12 items-start">
        {{-- LEFT COLUMN: Sticky Prescription Photo & Patient Details (5 cols) --}}
        <div class="lg:col-span-5 space-y-6">
            {{-- Image Viewer Card --}}
            <div class="card overflow-hidden">
                <div class="border-b border-slate-100 p-3.5 sm:px-5 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-800/50">
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                        <i class="fa-solid fa-file-image text-indigo-500"></i> Doctor's Slip
                    </span>
                    {{-- Viewer Controls --}}
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="zoomIn()" class="p-1.5 text-xs text-slate-600 hover:text-indigo-600 hover:bg-slate-100 rounded-lg dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700 transition" title="Zoom In">
                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                        </button>
                        <button type="button" onclick="zoomOut()" class="p-1.5 text-xs text-slate-600 hover:text-indigo-600 hover:bg-slate-100 rounded-lg dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700 transition" title="Zoom Out">
                            <i class="fa-solid fa-magnifying-glass-minus"></i>
                        </button>
                        <button type="button" onclick="rotateImage()" class="p-1.5 text-xs text-slate-600 hover:text-indigo-600 hover:bg-slate-100 rounded-lg dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700 transition" title="Rotate 90°">
                            <i class="fa-solid fa-rotate-right"></i>
                        </button>
                        <button type="button" onclick="resetViewer()" class="p-1.5 text-xs text-slate-600 hover:text-indigo-600 hover:bg-slate-100 rounded-lg dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700 transition" title="Reset">
                            <i class="fa-solid fa-arrow-rotate-left"></i>
                        </button>
                        @if($prescription->image_url)
                            <a href="{{ $prescription->image_url }}" target="_blank" class="p-1.5 text-xs text-slate-600 hover:text-indigo-600 hover:bg-slate-100 rounded-lg dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700 transition" title="Open Fullscreen">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Interactive Viewport --}}
                <div class="relative bg-slate-950 h-[480px] flex items-center justify-center overflow-hidden cursor-grab active:cursor-grabbing select-none" id="imageViewport">
                    @if($prescription->image_url)
                        <img id="prescriptionViewerImg"
                             src="{{ $prescription->image_url }}"
                             alt="Prescription Slip"
                             class="max-h-full max-w-full object-contain transition-transform duration-150 origin-center">
                    @else
                        <div class="text-center p-8 text-slate-500">
                            <i class="fa-regular fa-image text-4xl mb-2"></i>
                            <p class="text-xs">No prescription photo attached</p>
                        </div>
                    @endif
                    <div class="absolute bottom-2 right-2 rounded-lg bg-black/60 px-2 py-0.5 text-[10px] text-white/80 font-mono">
                        <span id="zoomLevelText">100%</span> | <span id="rotationText">0°</span>
                    </div>
                </div>
            </div>

            {{-- Patient & Delivery Card --}}
            <div class="card p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-user-injured text-indigo-500"></i> Patient & Delivery Details
                    </h3>
                    @if($prescription->customer)
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            Registered Customer
                        </span>
                    @else
                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                            Guest Order
                        </span>
                    @endif
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Customer Name</span>
                        <div class="text-slate-900 dark:text-white font-bold text-sm mt-0.5">{{ $prescription->customer_name }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Phone Number</span>
                            <a href="tel:{{ $prescription->customer_phone }}" class="text-indigo-600 hover:underline dark:text-indigo-400 font-mono font-bold mt-0.5 inline-block">
                                <i class="fa-solid fa-phone mr-1 text-[10px]"></i> {{ $prescription->customer_phone }}
                            </a>
                        </div>
                        <div>
                            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Email</span>
                            <div class="text-slate-700 dark:text-slate-300 truncate mt-0.5">
                                {{ $prescription->customer_email ?? 'Not provided' }}
                            </div>
                        </div>
                    </div>

                    <div class="pt-1">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Delivery Address</span>
                        <div class="text-slate-800 dark:text-slate-200 font-medium leading-relaxed mt-0.5 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                            {{ $prescription->delivery_address }}
                            @if($prescription->delivery_city)
                                <div class="mt-1 font-semibold text-indigo-600 dark:text-indigo-400 text-[11px]">
                                    <i class="fa-solid fa-location-dot mr-1"></i> {{ $prescription->delivery_city }} @if($prescription->delivery_area)({{ $prescription->delivery_area }})@endif
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($prescription->patient_notes)
                        <div class="pt-1">
                            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Patient Request Note</span>
                            <div class="mt-0.5 p-3 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/70 dark:border-amber-900 text-amber-900 dark:text-amber-200 italic leading-relaxed">
                                <i class="fa-solid fa-quote-left mr-1.5 opacity-60"></i>
                                {{ $prescription->patient_notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Pharmacist Interactive Order Builder (7 cols) --}}
        <div class="lg:col-span-7 space-y-6">
            @if($prescription->status === 'pending_review')
                {{-- Order Creation Form --}}
                <form method="POST" action="{{ route('store.admin.prescriptions.convert', $prescription) }}" id="orderBuilderForm" class="space-y-6">
                    @csrf

                    {{-- Customer & Shipping Hidden/Prefilled inputs --}}
                    <input type="hidden" name="shipping_name" value="{{ $prescription->customer_name }}">
                    <input type="hidden" name="shipping_phone" value="{{ $prescription->customer_phone }}">
                    <input type="hidden" name="shipping_email" value="{{ $prescription->customer_email }}">
                    <input type="hidden" name="shipping_address" value="{{ $prescription->delivery_address }}">
                    <input type="hidden" name="shipping_city" value="{{ $prescription->delivery_city ?? 'Dhaka' }}">
                    <input type="hidden" name="shipping_area" value="{{ $prescription->delivery_area }}">

                    <div class="card overflow-hidden">
                        {{-- Builder Header --}}
                        <div class="border-b border-slate-100 p-4 sm:px-6 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-800/40">
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="fa-solid fa-cart-flatbed text-indigo-600"></i>
                                    Prescription Order Builder
                                </h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    Search pharmacy catalog and add medicines specified in the doctor's handwriting.
                                </p>
                            </div>
                            <span class="rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 px-2.5 py-1 text-xs font-mono font-bold">
                                Step 2: Build Cart
                            </span>
                        </div>

                        {{-- Catalog Autocomplete Search Bar --}}
                        <div class="p-4 sm:p-6 border-b border-slate-100 dark:border-slate-800 space-y-4">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Search Medicine Catalog
                            </label>
                            <div class="relative">
                                <input type="text"
                                       id="catalogSearchInput"
                                       placeholder="Type brand name, generic name, or SKU (e.g. Napa, Seclo, Amoxicillin)..."
                                       autocomplete="off"
                                       class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-xs text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500 transition">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>

                                {{-- Search Results Dropdown --}}
                                <div id="catalogSearchResults" class="absolute z-30 left-0 right-0 mt-1 max-h-72 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-800 hidden divide-y divide-slate-100 dark:divide-slate-700">
                                </div>
                            </div>
                        </div>

                        {{-- Order Line Items Table --}}
                        <div class="p-4 sm:p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Selected Prescription Items
                                </span>
                                <span id="itemCountBadge" class="text-xs font-mono text-slate-400">0 items</span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs" id="itemsTable">
                                    <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-800/40 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                                        <tr>
                                            <th class="py-2.5 px-3">Medicine & Dosage Instructions</th>
                                            <th class="py-2.5 px-2 text-center w-24">Qty</th>
                                            <th class="py-2.5 px-2 text-right w-24">Unit Price</th>
                                            <th class="py-2.5 px-3 text-right w-24">Total</th>
                                            <th class="py-2.5 px-2 text-center w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody" class="divide-y divide-slate-100 dark:divide-slate-800">
                                        <tr id="emptyItemsRow">
                                            <td colspan="5" class="py-8 text-center text-slate-400 italic">
                                                No medicines added yet. Use the search bar above to look up products.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Pricing & Delivery Section --}}
                        <div class="border-t border-slate-100 p-4 sm:p-6 bg-slate-50/50 dark:border-slate-800 dark:bg-slate-800/20 space-y-5">
                            <div class="grid sm:grid-cols-2 gap-4">
                                {{-- Delivery Charge Selection --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Shipping / Delivery Zone
                                    </label>
                                    <select name="delivery_charge_id" id="deliveryChargeSelect" onchange="recalculateTotals()" class="w-full rounded-xl border border-slate-200 bg-white py-2 px-3 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                        <option value="" data-amount="0">Standard Delivery (Free / Included)</option>
                                        @foreach($deliveryCharges as $charge)
                                            <option value="{{ $charge->id }}" data-amount="{{ $charge->charge_amount }}" @selected(str_contains(strtolower($prescription->delivery_city ?? ''), strtolower($charge->title)))>
                                                {{ $charge->title }} - ${{ number_format($charge->charge_amount, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Custom Shipping Override --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Custom Shipping Charge ($)
                                    </label>
                                    <input type="number" step="0.01" min="0" name="custom_shipping_charge" id="customShippingInput" oninput="recalculateTotals()" placeholder="Override shipping amount" class="w-full rounded-xl border border-slate-200 bg-white py-2 px-3 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                </div>

                                {{-- Payment Method --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Payment Method
                                    </label>
                                    <select name="payment_method" class="w-full rounded-xl border border-slate-200 bg-white py-2 px-3 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                        <option value="cod" selected>Cash on Delivery (COD)</option>
                                        <option value="card">Credit / Debit Card</option>
                                        <option value="bkash">bKash</option>
                                        <option value="nagad">Nagad</option>
                                        <option value="rocket">Rocket</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="cash">Cash in Hand</option>
                                    </select>
                                </div>

                                {{-- Payment Status --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Payment Status
                                    </label>
                                    <select name="payment_status" class="w-full rounded-xl border border-slate-200 bg-white py-2 px-3 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                        <option value="unpaid" selected>Unpaid (Pending COD)</option>
                                        <option value="paid">Paid</option>
                                        <option value="partially_paid">Partially Paid</option>
                                    </select>
                                </div>

                                {{-- Pharmacist Notes / Instructions --}}
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Pharmacist Notes & Verification Comments
                                    </label>
                                    <textarea name="pharmacist_notes" rows="2" placeholder="e.g. Verified with patient via phone. Dosage instructions verified against prescription pad." class="w-full rounded-xl border border-slate-200 bg-white py-2 px-3 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                                </div>
                            </div>

                            {{-- Financial Calculation Box --}}
                            <div class="rounded-2xl bg-white p-4 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2">
                                <div class="flex justify-between text-xs text-slate-600 dark:text-slate-400">
                                    <span>Medicines Subtotal</span>
                                    <span class="font-mono font-semibold text-slate-900 dark:text-white" id="subtotalDisplay">$0.00</span>
                                </div>
                                <div class="flex justify-between text-xs text-slate-600 dark:text-slate-400">
                                    <span>Delivery Charge</span>
                                    <span class="font-mono font-semibold text-slate-900 dark:text-white" id="shippingDisplay">$0.00</span>
                                </div>
                                <div class="border-t border-slate-100 pt-2 dark:border-slate-800 flex justify-between text-sm font-extrabold text-slate-900 dark:text-white">
                                    <span>Total Payable Bill</span>
                                    <span class="font-mono text-indigo-600 dark:text-indigo-400 text-base" id="grandTotalDisplay">$0.00</span>
                                </div>
                            </div>
                        </div>

                        {{-- Convert Action Bar --}}
                        <div class="border-t border-slate-100 p-4 sm:px-6 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/40 flex items-center justify-between">
                            <span class="text-xs text-slate-400">
                                Creates order and locks prescription.
                            </span>
                            <button type="submit" id="convertOrderBtn" disabled class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                <i class="fa-solid fa-check"></i> Convert to Order & Dispatch
                            </button>
                        </div>
                    </div>
                </form>
            @elseif($prescription->status === 'converted_to_order' && $prescription->order)
                {{-- Order Summary Details --}}
                <div class="card p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Created Order Items</h3>
                            <p class="text-xs text-slate-400 font-mono">Order #{{ $prescription->order->order_number }}</p>
                        </div>
                        <a href="{{ route('store.admin.orders.show', $prescription->order) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 transition">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Open in Orders
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800/50 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                                <tr>
                                    <th class="py-2.5 px-3">Item Details</th>
                                    <th class="py-2.5 px-2 text-center">Qty</th>
                                    <th class="py-2.5 px-2 text-right">Price</th>
                                    <th class="py-2.5 px-3 text-right">Line Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($prescription->order->products as $item)
                                    <tr>
                                        <td class="py-3 px-3">
                                            <div class="font-bold text-slate-900 dark:text-white">{{ $item->product_name }}</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">{{ $item->variant_title }} &bull; SKU: {{ $item->sku }}</div>
                                            @if($item->dosage_instructions)
                                                <div class="mt-1 text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded inline-block">
                                                    <i class="fa-solid fa-prescription mr-1"></i> Dosage: {{ $item->dosage_instructions }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-2 text-center font-bold font-mono">{{ $item->quantity }}</td>
                                        <td class="py-3 px-2 text-right font-mono">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="py-3 px-3 text-right font-bold font-mono text-slate-900 dark:text-white">${{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-slate-100 pt-4 dark:border-slate-800 flex justify-between items-center text-sm">
                        <span class="text-slate-500">Order Grand Total:</span>
                        <span class="font-extrabold text-base font-mono text-indigo-600 dark:text-indigo-400">${{ number_format($prescription->order->total, 2) }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Reject Prescription Modal --}}
<div id="rejectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
    <div class="relative max-w-md w-full bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <form method="POST" action="{{ route('store.admin.prescriptions.reject', $prescription) }}">
            @csrf
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-500"></i> Reject Prescription
                </h3>
                <button type="button" onclick="closeRejectModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="p-5 space-y-4 text-xs">
                <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                    Please specify why this prescription cannot be fulfilled. The reason will be logged and communicated to the patient.
                </p>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Quick Select Preset Reason</label>
                    <select onchange="if(this.value) document.getElementById('rejectionReasonInput').value = this.value" class="w-full rounded-xl border border-slate-200 bg-white py-2 px-3 text-xs text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <option value="">-- Choose common rejection reason --</option>
                        <option value="Image is too blurry or illegible. Please re-upload a clear photograph of the doctor's slip.">Image is too blurry / illegible</option>
                        <option value="Doctor's name, BMDC registration number, or official seal is missing from the slip.">Doctor's signature or seal missing</option>
                        <option value="Prescription has expired or exceeded the valid re-fill duration.">Expired prescription</option>
                        <option value="Prescribed medicines are restricted controlled substances requiring in-person hospital dispensing.">Restricted / controlled substances</option>
                        <option value="Prescribed medicines are currently out of stock with our suppliers.">Items out of stock</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Detailed Reason <span class="text-rose-500">*</span></label>
                    <textarea name="rejection_reason" id="rejectionReasonInput" required rows="3" placeholder="Explain the issue clearly..." class="w-full rounded-xl border border-slate-200 bg-white py-2 px-3 text-xs text-slate-800 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                </div>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
                <button type="button" onclick="closeRejectModal()" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white hover:bg-rose-700 transition">
                    Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Catalog Data & Client-Side Scripts --}}
<script>
    // 1. Viewer state
    let zoomLevel = 1.0;
    let rotationAngle = 0;
    const viewerImg = document.getElementById('prescriptionViewerImg');
    const zoomText = document.getElementById('zoomLevelText');
    const rotText = document.getElementById('rotationText');

    function applyTransforms() {
        if (!viewerImg) return;
        viewerImg.style.transform = `scale(${zoomLevel}) rotate(${rotationAngle}deg)`;
        if (zoomText) zoomText.innerText = Math.round(zoomLevel * 100) + '%';
        if (rotText) rotText.innerText = rotationAngle + '°';
    }

    function zoomIn() {
        if (zoomLevel < 3.5) {
            zoomLevel += 0.25;
            applyTransforms();
        }
    }

    function zoomOut() {
        if (zoomLevel > 0.5) {
            zoomLevel -= 0.25;
            applyTransforms();
        }
    }

    function rotateImage() {
        rotationAngle = (rotationAngle + 90) % 360;
        applyTransforms();
    }

    function resetViewer() {
        zoomLevel = 1.0;
        rotationAngle = 0;
        applyTransforms();
    }

    // Modal controls
    function openRejectModal() {
        document.getElementById('rejectModal').classList.remove('hidden');
        document.getElementById('rejectModal').classList.add('flex');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.getElementById('rejectModal').classList.remove('flex');
    }

    // 2. Catalog Search & Order Items Builder
    const catalogProducts = @json($products);
    let selectedItems = [];

    const searchInput = document.getElementById('catalogSearchInput');
    const resultsContainer = document.getElementById('catalogSearchResults');
    const itemsTableBody = document.getElementById('itemsTableBody');
    const emptyRow = document.getElementById('emptyItemsRow');
    const convertBtn = document.getElementById('convertOrderBtn');
    const itemCountBadge = document.getElementById('itemCountBadge');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            if (query.length < 1) {
                resultsContainer.classList.add('hidden');
                resultsContainer.innerHTML = '';
                return;
            }

            let matches = [];
            catalogProducts.forEach(prod => {
                prod.active_variants.forEach(variant => {
                    const searchStr = `${prod.name} ${variant.title} ${variant.sku} ${prod.generic_name || ''} ${prod.brand?.name || ''}`.toLowerCase();
                    if (searchStr.includes(query)) {
                        matches.push({
                            product: prod,
                            variant: variant
                        });
                    }
                });
            });

            if (matches.length === 0) {
                resultsContainer.innerHTML = `<div class="p-3 text-center text-xs text-slate-400">No matching medicines found in catalog.</div>`;
            } else {
                let html = '';
                matches.slice(0, 10).forEach(m => {
                    const price = parseFloat(m.variant.selling_price || m.variant.mrp || 0).toFixed(2);
                    html += `
                        <div class="p-2.5 hover:bg-indigo-50/60 dark:hover:bg-slate-700/60 cursor-pointer flex items-center justify-between gap-3 transition"
                             onclick="addItem(${m.variant.id}, '${escapeJs(m.product.name)}', '${escapeJs(m.variant.title)}', '${escapeJs(m.variant.sku)}', ${price}, ${m.product.requires_prescription ? 'true' : 'false'})">
                            <div>
                                <div class="font-bold text-slate-800 dark:text-slate-100 text-xs">
                                    ${escapeHtml(m.product.name)}
                                    ${m.product.requires_prescription ? '<span class="ml-1 text-[9px] bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 px-1 py-0.2 rounded font-bold">Rx</span>' : ''}
                                </div>
                                <div class="text-[10px] text-slate-500 font-mono">
                                    ${escapeHtml(m.variant.title)} &bull; SKU: ${escapeHtml(m.variant.sku)}
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-mono font-bold text-slate-900 dark:text-white">$${price}</span>
                                <span class="block text-[10px] text-indigo-600 font-semibold">+ Add</span>
                            </div>
                        </div>
                    `;
                });
                resultsContainer.innerHTML = html;
            }
            resultsContainer.classList.remove('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.classList.add('hidden');
            }
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    function escapeJs(str) {
        if (!str) return '';
        return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    function addItem(variantId, productName, variantTitle, sku, unitPrice, isRx) {
        // If already in list, increase quantity
        const existing = selectedItems.find(i => i.variantId === variantId);
        if (existing) {
            existing.quantity += 1;
        } else {
            selectedItems.push({
                variantId: variantId,
                productName: productName,
                variantTitle: variantTitle,
                sku: sku,
                unitPrice: parseFloat(unitPrice),
                quantity: 1,
                dosage: '',
                isRx: isRx
            });
        }

        searchInput.value = '';
        resultsContainer.classList.add('hidden');
        renderItems();
    }

    function removeItem(index) {
        selectedItems.splice(index, 1);
        renderItems();
    }

    function updateQty(index, newQty) {
        const qty = parseInt(newQty);
        if (qty > 0) {
            selectedItems[index].quantity = qty;
        }
        renderItems();
    }

    function updatePrice(index, newPrice) {
        const p = parseFloat(newPrice);
        if (!isNaN(p) && p >= 0) {
            selectedItems[index].unitPrice = p;
        }
        recalculateTotals();
    }

    function updateDosage(index, val) {
        selectedItems[index].dosage = val;
    }

    function renderItems() {
        if (!itemsTableBody) return;

        if (selectedItems.length === 0) {
            itemsTableBody.innerHTML = `
                <tr id="emptyItemsRow">
                    <td colspan="5" class="py-8 text-center text-slate-400 italic">
                        No medicines added yet. Use the search bar above to look up products.
                    </td>
                </tr>
            `;
            if (convertBtn) convertBtn.disabled = true;
            if (itemCountBadge) itemCountBadge.innerText = '0 items';
            recalculateTotals();
            return;
        }

        let html = '';
        selectedItems.forEach((item, idx) => {
            const lineTotal = (item.quantity * item.unitPrice).toFixed(2);
            html += `
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                    <td class="py-3 px-3">
                        <input type="hidden" name="items[${idx}][variant_id]" value="${item.variantId}">
                        <div class="font-bold text-slate-900 dark:text-white">
                            ${escapeHtml(item.productName)}
                            ${item.isRx ? '<span class="ml-1 text-[9px] bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 px-1 py-0.2 rounded font-bold">Rx</span>' : ''}
                        </div>
                        <div class="text-[10px] text-slate-500 font-mono">${escapeHtml(item.variantTitle)} &bull; SKU: ${escapeHtml(item.sku)}</div>
                        <div class="mt-1.5">
                            <input type="text"
                                   name="items[${idx}][dosage_instructions]"
                                   value="${escapeHtml(item.dosage)}"
                                   oninput="updateDosage(${idx}, this.value)"
                                   placeholder="Dosage instruction (e.g. 1+0+1 after meal for 7 days)"
                                   class="w-full rounded-lg border border-slate-200 bg-white py-1 px-2 text-[11px] text-slate-800 focus:border-indigo-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        </div>
                    </td>
                    <td class="py-3 px-2 text-center">
                        <input type="number"
                               name="items[${idx}][quantity]"
                               value="${item.quantity}"
                               min="1"
                               oninput="updateQty(${idx}, this.value)"
                               class="w-16 text-center rounded-lg border border-slate-200 bg-white py-1 px-1 font-mono font-bold text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    </td>
                    <td class="py-3 px-2 text-right">
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="items[${idx}][unit_price]"
                               value="${item.unitPrice.toFixed(2)}"
                               oninput="updatePrice(${idx}, this.value)"
                               class="w-20 text-right rounded-lg border border-slate-200 bg-white py-1 px-1.5 font-mono text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    </td>
                    <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 dark:text-white" id="lineTotal_${idx}">
                        $${lineTotal}
                    </td>
                    <td class="py-3 px-2 text-center">
                        <button type="button" onclick="removeItem(${idx})" class="text-slate-400 hover:text-rose-600 transition p-1" title="Remove item">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        itemsTableBody.innerHTML = html;
        if (convertBtn) convertBtn.disabled = false;
        if (itemCountBadge) itemCountBadge.innerText = `${selectedItems.length} item(s)`;
        recalculateTotals();
    }

    function recalculateTotals() {
        let subtotal = 0;
        selectedItems.forEach((item, idx) => {
            const line = item.quantity * item.unitPrice;
            subtotal += line;
            const lineElem = document.getElementById(`lineTotal_${idx}`);
            if (lineElem) lineElem.innerText = `$${line.toFixed(2)}`;
        });

        const deliverySelect = document.getElementById('deliveryChargeSelect');
        const customShippingInput = document.getElementById('customShippingInput');

        let shipping = 0;
        if (customShippingInput && customShippingInput.value !== '' && !isNaN(parseFloat(customShippingInput.value))) {
            shipping = parseFloat(customShippingInput.value);
        } else if (deliverySelect && deliverySelect.selectedOptions[0]) {
            shipping = parseFloat(deliverySelect.selectedOptions[0].getAttribute('data-amount') || 0);
        }

        const grandTotal = subtotal + shipping;

        const subElem = document.getElementById('subtotalDisplay');
        const shipElem = document.getElementById('shippingDisplay');
        const grandElem = document.getElementById('grandTotalDisplay');

        if (subElem) subElem.innerText = `$${subtotal.toFixed(2)}`;
        if (shipElem) shipElem.innerText = `$${shipping.toFixed(2)}`;
        if (grandElem) grandElem.innerText = `$${grandTotal.toFixed(2)}`;
    }
</script>
@endsection
