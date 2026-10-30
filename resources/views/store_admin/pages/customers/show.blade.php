@extends('store_admin.layout.master')
@section('contents')
@php
    $field = 'w-full rounded-xl border-slate-200 bg-white text-xs text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500 p-2.5 transition';
@endphp

<div class="space-y-6">
    {{-- Top Action Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.admin.customers.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 font-semibold flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left"></i> Back to Customers
                </a>
            </div>
            <div class="flex flex-wrap items-center gap-3 mt-1.5">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                    {{ $customer->full_name }}
                </h1>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border {{ $customer->status_badge_class }}">
                    <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>
                    {{ ucfirst($customer->status) }}
                </span>
            </div>
            <p class="text-xs text-slate-400 font-mono mt-0.5">
                Customer ID: #{{ $customer->id }} &bull; Joined {{ $customer->created_at->format('F j, Y \a\t g:i A') }}
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            @can('Store Customer Edit')
                <form method="POST" action="{{ route('store.admin.customers.toggle', $customer) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold {{ $customer->status === 'active' ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50' }} shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 transition">
                        <i class="fa-solid {{ $customer->status === 'active' ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                        {{ $customer->status === 'active' ? 'Suspend Account' : 'Activate Account' }}
                    </button>
                </form>

                <a href="{{ route('store.admin.customers.edit', $customer) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 transition">
                    <i class="fa-regular fa-pen-to-square"></i> Edit Profile
                </a>
            @endcan

            @can('Store Order Create')
                <a href="{{ route('store.admin.orders.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                    <i class="fa-solid fa-cart-plus"></i> Create Order
                </a>
            @endcan
        </div>
    </div>

    {{-- Feedback Messages --}}
    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-300">&times;</button>
        </div>
    @endif

    {{-- Top Metrics Row --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                <i class="fa-solid fa-box-archive text-lg"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Total Orders</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $customer->orders->count() }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                <i class="fa-solid fa-hand-holding-dollar text-lg"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Total Paid Spend</span>
                <span class="text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400">${{ number_format((float)$totalSpent, 2) }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400">
                <i class="fa-solid fa-location-dot text-lg"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Saved Addresses</span>
                <span class="text-xl font-bold font-mono text-purple-600 dark:text-purple-400">{{ $customer->addresses->count() }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400">
                <i class="fa-regular fa-heart text-lg"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Wishlist Items</span>
                <span class="text-xl font-bold font-mono text-rose-600 dark:text-rose-400">{{ $customer->wishlists->count() }}</span>
            </div>
        </div>
    </div>

    {{-- Main 2-column layout --}}
    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        {{-- Left Column: Addresses & Orders History --}}
        <div class="space-y-6">
            {{-- Saved Addresses Section --}}
            <section class="card p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h2 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                            <i class="fa-solid fa-map-location-dot text-indigo-600 dark:text-indigo-400"></i>
                            Saved Addresses ({{ $customer->addresses->count() }})
                        </h2>
                    </div>
                </div>

                @if($customer->addresses->isNotEmpty())
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($customer->addresses as $address)
                            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/40 relative flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                                {{ $address->title }}
                                            </span>
                                            @if($address->is_default)
                                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                    Default
                                                </span>
                                            @endif
                                        </div>

                                        @can('Store Customer Edit')
                                            <form method="POST" action="{{ route('store.admin.customers.addresses.destroy', [$customer, $address]) }}" onsubmit="return confirm('Remove this address?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-slate-400 hover:text-rose-600 transition text-xs p-1" title="Delete Address">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>

                                    <div class="text-xs text-slate-800 dark:text-slate-200 leading-relaxed">
                                        <div class="font-medium text-slate-900 dark:text-white">{{ $address->full_name }}</div>
                                        <div>{{ $address->address_1 }}</div>
                                        @if($address->address_2)<div>{{ $address->address_2 }}</div>@endif
                                        <div>{{ $address->state_district }}@if($address->zip) - {{ $address->zip }}@endif</div>
                                        <div class="text-slate-500">{{ $address->country }}</div>
                                        @if($address->phone)
                                            <div class="mt-1 font-mono text-[11px] text-slate-500">
                                                <i class="fa-solid fa-phone text-[10px] mr-1"></i> {{ $address->phone }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center text-xs text-slate-500 dark:border-slate-800 dark:bg-slate-800/30 dark:text-slate-400">
                        No addresses saved for this customer yet. Use the form on the right to add a delivery address.
                    </div>
                @endif
            </section>

            {{-- Recent Orders History Card --}}
            <section class="card overflow-hidden">
                <div class="border-b border-slate-100 p-4 sm:px-6 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                            <i class="fa-solid fa-receipt text-indigo-600 dark:text-indigo-400"></i>
                            Order History ({{ $customer->orders->count() }})
                        </h2>
                    </div>
                </div>

                @if($customer->orders->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 dark:bg-slate-800/50 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                                <tr>
                                    <th class="p-4 sm:px-6">Order</th>
                                    <th class="p-4">Fulfillment</th>
                                    <th class="p-4">Payment</th>
                                    <th class="p-4 text-center">Items</th>
                                    <th class="p-4 text-right">Total</th>
                                    <th class="p-4 sm:px-6 text-right">View</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                                @foreach($customer->orders as $order)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                        <td class="p-4 sm:px-6">
                                            <a href="{{ route('store.admin.orders.show', $order) }}" class="font-bold font-mono text-indigo-600 dark:text-indigo-400 hover:underline">
                                                #{{ $order->order_number }}
                                            </a>
                                            <div class="text-[11px] text-slate-400">
                                                {{ $order->placed_at?->format('M j, Y') ?? $order->created_at->format('M j, Y') }}
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold border {{ $order->status_badge_class }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="p-4">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold border {{ $order->payment_status_badge_class }}">
                                                {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-center font-bold">
                                            {{ $order->products->sum('quantity') }}
                                        </td>
                                        <td class="p-4 text-right font-bold font-mono text-slate-900 dark:text-white">
                                            {{ $order->currency }} {{ number_format((float)$order->total, 2) }}
                                        </td>
                                        <td class="p-4 sm:px-6 text-right">
                                            <a href="{{ route('store.admin.orders.show', $order) }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800 transition">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center text-xs text-slate-500 dark:text-slate-400">
                        No orders recorded for this customer yet.
                    </div>
                @endif
            </section>
        </div>

        {{-- Right Column: Profile Overview & Add Address Form --}}
        <div class="space-y-6">
            {{-- Profile Card --}}
            <section class="card p-5 space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-base font-bold text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                        {{ $customer->initials }}
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-900 dark:text-white text-base">{{ $customer->full_name }}</h2>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-semibold border {{ $customer->status_badge_class }} mt-0.5">
                            {{ ucfirst($customer->status) }}
                        </span>
                    </div>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Email Address</span>
                        <a href="mailto:{{ $customer->email }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ $customer->email }}
                        </a>
                    </div>
                    @if($customer->phone)
                        <div>
                            <span class="text-slate-400 block text-[11px]">Phone</span>
                            <a href="tel:{{ $customer->phone }}" class="font-semibold text-slate-800 dark:text-slate-200 hover:underline">
                                {{ $customer->phone }}
                            </a>
                        </div>
                    @endif
                    <div class="border-t border-slate-100 pt-2.5 dark:border-slate-800">
                        <span class="text-slate-400 block text-[11px]">Account Created</span>
                        <span class="font-medium text-slate-700 dark:text-slate-300 font-mono">
                            {{ $customer->created_at->format('M j, Y h:i A') }}
                        </span>
                    </div>
                </div>
            </section>

            {{-- Add New Address Card --}}
            @can('Store Customer Edit')
                <section class="card p-5 space-y-4">
                    <h2 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2 border-b border-slate-100 pb-3 dark:border-slate-800">
                        <i class="fa-solid fa-plus text-indigo-600 dark:text-indigo-400 text-xs"></i>
                        Add New Address
                    </h2>

                    <form method="POST" action="{{ route('store.admin.customers.addresses.store', $customer) }}" class="space-y-3">
                        @csrf

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Title *</label>
                                <input type="text" name="title" required placeholder="e.g. Office" class="{{ $field }}">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Country</label>
                                <input type="text" name="country" value="Bangladesh" class="{{ $field }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Recipient First Name *</label>
                                <input type="text" name="first_name" required value="{{ $customer->first_name }}" class="{{ $field }}">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Last Name</label>
                                <input type="text" name="last_name" value="{{ $customer->last_name }}" class="{{ $field }}">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone</label>
                            <input type="text" name="phone" value="{{ $customer->phone }}" class="{{ $field }}">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Address line 1 *</label>
                            <input type="text" name="address_1" required placeholder="Street address, house no." class="{{ $field }}">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Address line 2</label>
                            <input type="text" name="address_2" placeholder="Suite, floor, landmark" class="{{ $field }}">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">State / District</label>
                                <input type="text" name="state_district" placeholder="e.g. Dhaka" class="{{ $field }}">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Zip Code</label>
                                <input type="text" name="zip" placeholder="e.g. 1212" class="{{ $field }}">
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <input type="checkbox" name="is_default" value="1" id="is_default_check" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800">
                            <label for="is_default_check" class="text-xs text-slate-700 dark:text-slate-300 font-medium">Set as default address</label>
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-indigo-600 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition flex items-center justify-center gap-1.5 cursor-pointer mt-2">
                            <i class="fa-solid fa-plus"></i> Save Address
                        </button>
                    </form>
                </section>
            @endcan
        </div>
    </div>
</div>
@endsection
