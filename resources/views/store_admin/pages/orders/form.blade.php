@extends('store_admin.layout.master')
@section('contents')
@php
    $field = 'w-full rounded-lg border-slate-300 bg-white text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white p-2.5';
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.admin.orders.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 font-semibold flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left"></i> Back to Orders
                </a>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Create New Order</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Place a manual or in-store order, select catalog products, and specify delivery details.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-xl bg-rose-50 p-4 text-sm text-rose-700 dark:bg-rose-950/30 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
            <div class="font-semibold mb-1 flex items-center gap-1.5">
                <i class="fa-solid fa-triangle-exclamation"></i> Please resolve the following errors:
            </div>
            <ul class="list-inside list-disc text-xs space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('store.admin.orders.store') }}" id="order-create-form" class="space-y-6">
        @csrf

        <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
            {{-- Left Column: Line Items & Pricing --}}
            <div class="space-y-6">
                {{-- Order Items Card --}}
                <section class="card p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                        <div>
                            <h2 class="font-bold text-slate-900 dark:text-white text-base">Order Items</h2>
                            <p class="text-xs text-slate-400">Add products and variants to this order.</p>
                        </div>
                        <button type="button" id="btn-add-item" class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:text-indigo-300 dark:hover:bg-indigo-900/50 transition flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid fa-plus"></i> Add Item
                        </button>
                    </div>

                    <div id="order-items-container" class="space-y-3">
                        {{-- Initial Line Item --}}
                        <div class="item-row card rounded-xl border border-slate-200 dark:border-slate-800 p-4 bg-slate-50/50 dark:bg-slate-900/40 relative" data-row-index="0">
                            <div class="grid gap-3 sm:grid-cols-[1fr_120px_100px_110px_40px] items-end">
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Product & Variant *</label>
                                    <select name="items[0][variant_id]" required class="variant-select {{ $field }}">
                                        <option value="">Select a product variant...</option>
                                        @foreach($products as $prod)
                                            <optgroup label="{{ $prod->name }} ({{ ucfirst($prod->kind) }})">
                                                @foreach($prod->activeVariants as $variant)
                                                    <option value="{{ $variant->id }}"
                                                            data-price="{{ $variant->price }}"
                                                            data-sku="{{ $variant->sku }}"
                                                            data-stock="{{ $variant->available_stock }}"
                                                            data-track="{{ $variant->track_inventory ? '1' : '0' }}">
                                                        {{ $prod->name }} - {{ $variant->title }} (SKU: {{ $variant->sku }}) - ${{ number_format((float)$variant->price, 2) }}
                                                        @if($variant->track_inventory)
                                                            [{{ $variant->available_stock }} in stock]
                                                        @else
                                                            [Untracked]
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Unit Price ($)</label>
                                    <input type="number" step="0.01" min="0" name="items[0][unit_price]" value="0.00" class="unit-price-input {{ $field }} font-mono">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Qty *</label>
                                    <input type="number" min="1" name="items[0][quantity]\" value="1" required class="quantity-input {{ $field }} font-mono">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Line Total</label>
                                    <div class="line-total-display h-9 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold font-mono text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white flex items-center justify-end">
                                        $0.00
                                    </div>
                                </div>

                                <div class="flex justify-center pb-1">
                                    <button type="button" class="btn-remove-row text-slate-400 hover:text-rose-600 transition p-1.5" title="Remove item">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Shipping & Promotion Options --}}
                <section class="card p-5 space-y-4">
                    <h2 class="font-bold text-slate-900 dark:text-white text-base">Shipping & Promotions</h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Delivery Method</label>
                            <select name="delivery_charge_id" id="delivery-charge-select" class="{{ $field }}">
                                <option value="" data-charge="0">Custom / No delivery fee ($0.00)</option>
                                @foreach($deliveryCharges as $delivery)
                                    <option value="{{ $delivery->id }}"
                                            data-charge="{{ $delivery->charge_amount }}"
                                            data-free-over="{{ $delivery->min_order_amount ?? 0 }}">
                                        {{ $delivery->title }} - ${{ number_format((float)$delivery->charge_amount, 2) }}
                                        @if($delivery->min_order_amount)
                                            (Free over ${{ number_format((float)$delivery->min_order_amount, 2) }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Custom Shipping Fee ($)</label>
                            <input type="number" step="0.01" min="0" name="custom_shipping_charge" id="custom-shipping-input" value="0.00" placeholder="0.00" class="{{ $field }} font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Coupon Code</label>
                            <select name="coupon_id" id="coupon-select" class="{{ $field }}">
                                <option value="" data-type="" data-val="0" data-min="0" data-max="0">No coupon applied</option>
                                @foreach($coupons as $coupon)
                                    <option value="{{ $coupon->id }}"
                                            data-type="{{ $coupon->type }}"
                                            data-val="{{ $coupon->value }}"
                                            data-min="{{ $coupon->min_order_amount ?? 0 }}"
                                            data-max="{{ $coupon->max_discount_amount ?? 0 }}">
                                        {{ $coupon->code }} ({{ $coupon->type === 'percentage' ? (float)$coupon->value . '%' : '$' . number_format((float)$coupon->value, 2) }} OFF)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Method *</label>
                            <select name="payment_method" required class="{{ $field }}">
                                <option value="cod">Cash on Delivery (COD)</option>
                                <option value="card">Credit / Debit Card</option>
                                <option value="bkash">bKash</option>
                                <option value="nagad">Nagad</option>
                                <option value="rocket">Rocket</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash">In-Store Cash</option>
                            </select>
                        </div>
                    </div>
                </section>

                {{-- Live Financial Breakdown Card --}}
                <section class="card p-5 bg-gradient-to-br from-white to-slate-50 dark:from-slate-900 dark:to-slate-800/60 border border-slate-200 dark:border-slate-800">
                    <div class="max-w-md ml-auto space-y-2 text-xs">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Items Subtotal:</span>
                            <span id="summary-subtotal" class="font-mono font-medium text-slate-900 dark:text-white">$0.00</span>
                        </div>
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                            <span>Coupon Discount:</span>
                            <span id="summary-discount" class="font-mono font-medium">-$0.00</span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Shipping Charge:</span>
                            <span id="summary-shipping" class="font-mono font-medium text-slate-900 dark:text-white">$0.00</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 dark:border-slate-700 pt-2 text-base font-bold text-slate-900 dark:text-white">
                            <span>Grand Total:</span>
                            <span id="summary-total" class="font-mono text-indigo-600 dark:text-indigo-400 text-lg">$0.00</span>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Right Column: Status & Customer Details --}}
            <div class="space-y-6">
                {{-- Fulfillment State Card --}}
                <section class="card p-5 space-y-4">
                    <h2 class="font-bold text-slate-900 dark:text-white text-base">Fulfillment & Status</h2>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Order Status *</label>
                            <select name="status" required class="{{ $field }}">
                                <option value="pending">Pending</option>
                                <option value="confirmed" selected>Confirmed</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Status *</label>
                            <select name="payment_status" required class="{{ $field }}">
                                <option value="unpaid">Unpaid</option>
                                <option value="paid">Paid</option>
                                <option value="partially_paid">Partially Paid</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Prescription Status (if applicable)</label>
                            <select name="prescription_status" class="{{ $field }}">
                                <option value="not_required">Not Required</option>
                                <option value="pending_review">Pending Review</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Order Note / Instructions</label>
                            <textarea name="order_note" rows="2" placeholder="e.g. Call before delivery, handle with care." class="{{ $field }}"></textarea>
                        </div>
                    </div>
                </section>

                {{-- Customer Information Card --}}
                <section class="card p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                        <div>
                            <h2 class="font-bold text-slate-900 dark:text-white text-base">Customer & Shipping</h2>
                            <p class="text-xs text-slate-400">Select an existing customer or enter manually.</p>
                        </div>
                        <a href="{{ route('store.admin.customers.create') }}" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1 font-semibold">
                            <i class="fa-solid fa-user-plus"></i> New Customer
                        </a>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Registered Customer (Optional)</label>
                            <select name="customer_id" id="customer-select" class="{{ $field }}">
                                <option value="" data-name="" data-phone="" data-email="" data-address="" data-area="" data-city="" data-postcode="" data-country="">
                                    -- Walk-in / Guest Order --
                                </option>
                                @foreach($customers as $c)
                                    @php
                                        $addr = $c->defaultAddress;
                                    @endphp
                                    <option value="{{ $c->id }}"
                                            {{ old('customer_id') == $c->id ? 'selected' : '' }}
                                            data-name="{{ $c->full_name }}"
                                            data-phone="{{ $c->phone ?? '' }}"
                                            data-email="{{ $c->email }}"
                                            data-address="{{ $addr?->address_1 ?? '' }}"
                                            data-area="{{ $addr?->address_2 ?? '' }}"
                                            data-city="{{ $addr?->state_district ?? '' }}"
                                            data-postcode="{{ $addr?->zip ?? '' }}"
                                            data-country="{{ $addr?->country ?? 'United States' }}">
                                        {{ $c->full_name }} ({{ $c->phone ?: $c->email }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Select an account to autofill recipient and default address.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Recipient Name *</label>
                            <input name="shipping_name" id="shipping_name" required value="{{ old('shipping_name') }}" placeholder="Full recipient name" class="{{ $field }}">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Phone Number *</label>
                            <input name="shipping_phone" id="shipping_phone" required value="{{ old('shipping_phone') }}" placeholder="+1 (555) 000-0000" class="{{ $field }}">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Email (Optional)</label>
                            <input name="shipping_email" id="shipping_email" type="email" value="{{ old('shipping_email') }}" placeholder="customer@example.com" class="{{ $field }}">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Street Address *</label>
                            <textarea name="shipping_address" id="shipping_address" required rows="2" placeholder="House / Building, Street name" class="{{ $field }}">{{ old('shipping_address') }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Area / Suburb</label>
                                <input name="shipping_area" id="shipping_area" value="{{ old('shipping_area') }}" placeholder="e.g. Downtown" class="{{ $field }}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">City *</label>
                                <input name="shipping_city" id="shipping_city" required value="{{ old('shipping_city', 'New York') }}" placeholder="City name" class="{{ $field }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Postal Code</label>
                                <input name="shipping_postcode" id="shipping_postcode" value="{{ old('shipping_postcode') }}" placeholder="e.g. 10001" class="{{ $field }}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Country</label>
                                <input name="shipping_country" id="shipping_country" value="{{ old('shipping_country', 'United States') }}" class="{{ $field }}">
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Action Submit --}}
                <div class="space-y-2">
                    <button type="submit" class="w-full rounded-xl bg-indigo-600 py-3 text-sm font-bold text-white hover:bg-indigo-700 transition shadow-md flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-check"></i> Place & Create Order
                    </button>
                    <a href="{{ route('store.admin.orders.index') }}" class="w-full block text-center rounded-xl border border-slate-300 bg-white py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 transition">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Template for dynamic line item rows --}}
<template id="item-row-template">
    <div class="item-row card rounded-xl border border-slate-200 dark:border-slate-800 p-4 bg-slate-50/50 dark:bg-slate-900/40 relative" data-row-index="__INDEX__">
        <div class="grid gap-3 sm:grid-cols-[1fr_120px_100px_110px_40px] items-end">
            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Product & Variant *</label>
                <select name="items[__INDEX__][variant_id]" required class="variant-select {{ $field }}">
                    <option value="">Select a product variant...</option>
                    @foreach($products as $prod)
                        <optgroup label="{{ $prod->name }} ({{ ucfirst($prod->kind) }})">
                            @foreach($prod->activeVariants as $variant)
                                <option value="{{ $variant->id }}"
                                        data-price="{{ $variant->price }}"
                                        data-sku="{{ $variant->sku }}"
                                        data-stock="{{ $variant->available_stock }}"
                                        data-track="{{ $variant->track_inventory ? '1' : '0' }}">
                                    {{ $prod->name }} - {{ $variant->title }} (SKU: {{ $variant->sku }}) - ${{ number_format((float)$variant->price, 2) }}
                                    @if($variant->track_inventory)
                                        [{{ $variant->available_stock }} in stock]
                                    @else
                                        [Untracked]
                                    @endif
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Unit Price ($)</label>
                <input type="number" step="0.01" min="0" name="items[__INDEX__][unit_price]" value="0.00" class="unit-price-input {{ $field }} font-mono">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Qty *</label>
                <input type="number" min="1" name="items[__INDEX__][quantity]" value="1" required class="quantity-input {{ $field }} font-mono">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Line Total</label>
                <div class="line-total-display h-9 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold font-mono text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white flex items-center justify-end">
                    $0.00
                </div>
            </div>

            <div class="flex justify-center pb-1">
                <button type="button" class="btn-remove-row text-slate-400 hover:text-rose-600 transition p-1.5" title="Remove item">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('order-items-container');
    const btnAdd = document.getElementById('btn-add-item');
    const template = document.getElementById('item-row-template');
    let rowIndex = 1;

    function recalculateRow(row) {
        const select = row.querySelector('.variant-select');
        const priceInput = row.querySelector('.unit-price-input');
        const qtyInput = row.querySelector('.quantity-input');
        const lineTotalDisplay = row.querySelector('.line-total-display');

        const price = parseFloat(priceInput.value) || 0;
        const qty = parseInt(qtyInput.value) || 0;
        const total = price * qty;

        lineTotalDisplay.textContent = '$' + total.toFixed(2);
        recalculateOrderSummary();
    }

    function recalculateOrderSummary() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const price = parseFloat(row.querySelector('.unit-price-input').value) || 0;
            const qty = parseInt(row.querySelector('.quantity-input').value) || 0;
            subtotal += (price * qty);
        });

        // Delivery Charge calculation
        const deliverySelect = document.getElementById('delivery-charge-select');
        const customShippingInput = document.getElementById('custom-shipping-input');
        let shipping = 0;

        if (deliverySelect.value) {
            const opt = deliverySelect.options[deliverySelect.selectedIndex];
            const baseCharge = parseFloat(opt.dataset.charge) || 0;
            const freeOver = parseFloat(opt.dataset.freeOver) || 0;
            if (freeOver > 0 && subtotal >= freeOver) {
                shipping = 0;
            } else {
                shipping = baseCharge;
            }
        } else {
            shipping = parseFloat(customShippingInput.value) || 0;
        }

        // Coupon Discount calculation
        const couponSelect = document.getElementById('coupon-select');
        let discount = 0;
        if (couponSelect.value) {
            const opt = couponSelect.options[couponSelect.selectedIndex];
            const type = opt.dataset.type;
            const val = parseFloat(opt.dataset.val) || 0;
            const min = parseFloat(opt.dataset.min) || 0;
            const max = parseFloat(opt.dataset.max) || 0;

            if (subtotal >= min) {
                if (type === 'percentage') {
                    discount = (subtotal * (val / 100));
                } else {
                    discount = val;
                }
                if (max > 0 && discount > max) {
                    discount = max;
                }
                discount = Math.min(discount, subtotal);
            }
        }

        const grandTotal = Math.max(0, subtotal - discount + shipping);

        document.getElementById('summary-subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('summary-discount').textContent = '-$' + discount.toFixed(2);
        document.getElementById('summary-shipping').textContent = '$' + shipping.toFixed(2);
        document.getElementById('summary-total').textContent = '$' + grandTotal.toFixed(2);
    }

    function bindRowEvents(row) {
        const select = row.querySelector('.variant-select');
        const priceInput = row.querySelector('.unit-price-input');
        const qtyInput = row.querySelector('.quantity-input');
        const btnRemove = row.querySelector('.btn-remove-row');

        select.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.dataset.price) {
                priceInput.value = parseFloat(opt.dataset.price).toFixed(2);
            }
            recalculateRow(row);
        });

        priceInput.addEventListener('input', () => recalculateRow(row));
        qtyInput.addEventListener('input', () => recalculateRow(row));

        btnRemove.addEventListener('click', function () {
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                recalculateOrderSummary();
            } else {
                alert('An order must contain at least one item.');
            }
        });
    }

    // Bind initial row
    document.querySelectorAll('.item-row').forEach(bindRowEvents);

    // Add new row
    btnAdd.addEventListener('click', function () {
        const html = template.innerHTML.replace(/__INDEX__/g, rowIndex);
        const div = document.createElement('div');
        div.innerHTML = html.trim();
        const newRow = div.firstChild;
        container.appendChild(newRow);
        bindRowEvents(newRow);
        rowIndex++;
    });

    // Re-bind summary listeners
    document.getElementById('delivery-charge-select').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (this.value) {
            document.getElementById('custom-shipping-input').value = parseFloat(opt.dataset.charge).toFixed(2);
        }
        recalculateOrderSummary();
    });

    document.getElementById('custom-shipping-input').addEventListener('input', recalculateOrderSummary);
    document.getElementById('coupon-select').addEventListener('change', recalculateOrderSummary);

    // Customer Select Auto-fill
    const customerSelect = document.getElementById('customer-select');
    if (customerSelect) {
        customerSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (!opt) return;

            if (opt.value) {
                if (opt.dataset.name) document.getElementById('shipping_name').value = opt.dataset.name;
                if (opt.dataset.phone) document.getElementById('shipping_phone').value = opt.dataset.phone;
                if (opt.dataset.email) document.getElementById('shipping_email').value = opt.dataset.email;
                if (opt.dataset.address) document.getElementById('shipping_address').value = opt.dataset.address;
                if (opt.dataset.area) document.getElementById('shipping_area').value = opt.dataset.area;
                if (opt.dataset.city) document.getElementById('shipping_city').value = opt.dataset.city;
                if (opt.dataset.postcode) document.getElementById('shipping_postcode').value = opt.dataset.postcode;
                if (opt.dataset.country) document.getElementById('shipping_country').value = opt.dataset.country;
            }
        });
    }
});
</script>
@endsection
