@extends('store_admin.layout.master')

@section('title', 'Cart Snapshot #CART-' . $cart->id . ' - ' . (auth()->user()->tenant->store_name ?? 'Store Admin'))

@section('contents')
@php
    $customer = $cart->customer;
    $cleanPhone = preg_replace('/[^0-9]/', '', (string) ($customer?->phone ?? ''));
    $whatsappText = rawurlencode("Hello " . ($customer?->first_name ?? 'there') . ", we noticed you left some items in your cart at " . (auth()->user()->tenant->store_name ?? 'our pharmacy') . ". Can we help you complete your order?");
@endphp

<div class="max-w-4xl mx-auto space-y-6 pb-12">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('store.admin.abandoned_checkouts.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-rose-600 transition mb-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Abandoned Checkouts</span>
            </a>
            <h1 class="text-2xl font-black text-ink dark:text-white flex items-center gap-2.5">
                <span>Cart Snapshot #CART-{{ $cart->id }}</span>
                @if($cart->is_contacted)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $cart->contacted_badge_class }}">
                        Contacted {{ $cart->recovery_contacted_at->diffForHumans() }}
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $cart->contacted_badge_class }}">
                        Not Contacted
                    </span>
                @endif
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Last updated {{ $cart->updated_at->format('M d, Y \a\t h:i A') }} ({{ $cart->idle_time }})
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if(!empty($cleanPhone))
                <a href="https://wa.me/{{ $cleanPhone }}?text={{ $whatsappText }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-xs transition">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>WhatsApp</span>
                </a>
            @endif

            @if(!empty($customer?->phone))
                <a href="tel:{{ $customer->phone }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition">
                    <i class="fa-solid fa-phone text-xs"></i>
                    <span>Call {{ $customer->phone }}</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Customer Lead Card --}}
    <div class="card p-6 border border-slate-200 dark:border-slate-800">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Customer Information</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-slate-400">Customer Name</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">{{ $customer?->full_name ?? 'Guest Buyer' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Phone Number</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">{{ $customer?->phone ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Email Address</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">{{ $customer?->email ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    {{-- Items Card --}}
    <div class="card overflow-hidden border border-slate-200 dark:border-slate-800">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-ink dark:text-white">Items Left in Cart ({{ $cart->total_items_count }})</h3>
            <span class="text-sm font-extrabold text-rose-600 dark:text-rose-400">Total: ৳{{ number_format($cart->subtotal, 2) }}</span>
        </div>
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800/40 text-[11px] font-bold text-slate-400 uppercase">
                <tr>
                    <th class="py-3 px-5">Item</th>
                    <th class="py-3 px-4 text-center">Quantity</th>
                    <th class="py-3 px-4 text-right">Unit Price</th>
                    <th class="py-3 px-5 text-right">Line Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($cart->items as $item)
                    <tr>
                        <td class="py-3.5 px-5">
                            <p class="font-bold text-slate-900 dark:text-white">{{ $item->product?->name }}</p>
                            <p class="text-[11px] text-slate-400">
                                SKU: {{ $item->productVariant?->sku ?? 'N/A' }} &bull; {{ $item->productVariant?->title ?? 'Default' }}
                            </p>
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold text-slate-700 dark:text-slate-300">{{ $item->quantity }}</td>
                        <td class="py-3.5 px-4 text-right text-slate-600 dark:text-slate-400">৳{{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-3.5 px-5 text-right font-extrabold text-slate-900 dark:text-white">৳{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tele-Sales Follow-up Log --}}
    <div class="card p-6 border border-slate-200 dark:border-slate-800">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Tele-Sales Follow-Up Note</h3>
        <form method="POST" action="{{ route('store.admin.abandoned_checkouts.note', $cart->id) }}" class="space-y-4">
            @csrf
            @method('PATCH')
            <textarea name="recovery_note" rows="4"
                      placeholder="Add notes from phone call or WhatsApp conversation..."
                      class="w-full rounded-xl border border-slate-300 bg-white p-3 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">{{ old('recovery_note', $cart->recovery_note) }}</textarea>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="mark_contacted" value="1" {{ $cart->is_contacted ? 'checked' : '' }} class="rounded text-rose-600 focus:ring-rose-500">
                    <span>Stamp as Contacted</span>
                </label>

                <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold text-xs hover:bg-slate-800 transition">
                    Save Tele-Sales Log
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
