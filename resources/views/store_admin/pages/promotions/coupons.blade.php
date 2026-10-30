@extends('store_admin.layout.master')
@section('contents')
@php
    $field = 'w-full rounded-lg border-slate-300 bg-white text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white';
@endphp

<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Discount Coupons</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Create promotional promo codes, percentage/fixed discounts, and usage limits.</p>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl bg-rose-50 p-3 text-sm text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        {{-- Left: Coupons Table --}}
        <section class="card min-w-0 overflow-hidden">
            <div class="border-b border-slate-100 p-4 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-bold text-slate-900 dark:text-white">Active Coupons ({{ $coupons->total() }})</h2>
                <form method="GET" action="{{ route('store.admin.coupons.index') }}" class="flex gap-2">
                    <input name="search" value="{{ request('search') }}" placeholder="Search code..." class="rounded-lg border-slate-300 bg-white text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white px-2.5 py-1.5">
                    <button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold dark:border-slate-700">Search</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                        <tr>
                            <th class="p-4">Code</th>
                            <th class="p-4">Discount</th>
                            <th class="p-4">Thresholds</th>
                            <th class="p-4">Usage</th>
                            <th class="p-4">Validity</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($coupons as $coupon)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 bg-indigo-50/70 px-2.5 py-1 font-mono text-xs font-bold text-indigo-700 dark:border-indigo-800 dark:bg-indigo-950/40 dark:text-indigo-300">
                                        <i class="fa-solid fa-ticket text-[10px]"></i>
                                        {{ $coupon->code }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-slate-900 dark:text-white">
                                        @if($coupon->type === 'percentage')
                                            {{ (float)$coupon->value }}% OFF
                                        @else
                                            ${{ number_format((float)$coupon->value, 2) }} OFF
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 capitalize">{{ $coupon->type }} discount</div>
                                </td>
                                <td class="p-4 text-xs">
                                    <div>Min: {{ $coupon->min_order_amount ? '$' . number_format((float)$coupon->min_order_amount, 2) : 'No min' }}</div>
                                    @if($coupon->max_discount_amount)
                                        <div class="text-[11px] text-slate-400">Cap: ${{ number_format((float)$coupon->max_discount_amount, 2) }}</div>
                                    @endif
                                </td>
                                <td class="p-4 text-xs font-mono">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $coupon->used_count }}</span>
                                    <span class="text-slate-400">/ {{ $coupon->usage_limit ?? '∞' }}</span>
                                </td>
                                <td class="p-4 text-xs text-slate-500 dark:text-slate-400">
                                    @if($coupon->expires_at)
                                        <div>Exp: {{ $coupon->expires_at->format('M j, Y') }}</div>
                                        <div class="text-[11px] {{ $coupon->expires_at->isPast() ? 'text-rose-500 font-semibold' : 'text-slate-400' }}">
                                            {{ $coupon->expires_at->isPast() ? 'Expired' : $coupon->expires_at->diffForHumans() }}
                                        </div>
                                    @else
                                        <span class="text-emerald-600 dark:text-emerald-400 font-medium">Never expires</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @can('Store Coupon Edit')
                                        <form method="POST" action="{{ route('store.admin.coupons.toggle', $coupon) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold cursor-pointer transition {{ $coupon->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400' }}">
                                                {{ $coupon->is_active ? 'Active' : 'Disabled' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $coupon->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $coupon->is_active ? 'Active' : 'Disabled' }}
                                        </span>
                                    @endcan
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @can('Store Coupon Edit')
                                            <details class="inline-block text-left" data-popover>
                                                <summary class="cursor-pointer text-xs font-semibold text-indigo-600 hover:underline">Edit</summary>
                                                <form method="POST" action="{{ route('store.admin.coupons.update', $coupon) }}" data-popover-panel class="invisible fixed z-50 grid max-h-[calc(100vh-2rem)] w-88 sm:w-96 max-w-[calc(100vw-2rem)] gap-3 overflow-y-auto rounded-xl border border-slate-200 bg-white p-4 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                                                    @csrf @method('PUT')
                                                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Edit Coupon: {{ $coupon->code }}</h3>
                                                    <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Coupon Code *
                                                        <input name="code" value="{{ $coupon->code }}" required class="mt-1 {{ $field }} uppercase font-mono p-2">
                                                    </label>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Type *
                                                            <select name="type" class="mt-1 {{ $field }} p-2">
                                                                <option value="percentage" @selected($coupon->type === 'percentage')>Percentage (%)</option>
                                                                <option value="fixed" @selected($coupon->type === 'fixed')>Fixed Amount ($)</option>
                                                            </select>
                                                        </label>
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Discount Value *
                                                            <input name="value" type="number" step="0.01" min="0.01" value="{{ $coupon->value }}" required class="mt-1 {{ $field }} p-2">
                                                        </label>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Min Order ($)
                                                            <input name="min_order_amount" type="number" step="0.01" min="0" value="{{ $coupon->min_order_amount }}" class="mt-1 {{ $field }} p-2">
                                                        </label>
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Max Discount ($)
                                                            <input name="max_discount_amount" type="number" step="0.01" min="0" value="{{ $coupon->max_discount_amount }}" class="mt-1 {{ $field }} p-2">
                                                        </label>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Total Usage Limit
                                                            <input name="usage_limit" type="number" min="1" value="{{ $coupon->usage_limit }}" class="mt-1 {{ $field }} p-2">
                                                        </label>
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Per Customer Limit
                                                            <input name="usage_limit_per_customer" type="number" min="1" value="{{ $coupon->usage_limit_per_customer }}" class="mt-1 {{ $field }} p-2">
                                                        </label>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Starts At
                                                            <input name="starts_at" type="datetime-local" value="{{ $coupon->starts_at?->format('Y-m-d\TH:i') }}" class="mt-1 {{ $field }} p-2">
                                                        </label>
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Expires At
                                                            <input name="expires_at" type="datetime-local" value="{{ $coupon->expires_at?->format('Y-m-d\TH:i') }}" class="mt-1 {{ $field }} p-2">
                                                        </label>
                                                    </div>
                                                    <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 pt-1">
                                                        <input type="checkbox" name="is_active" value="1" @checked($coupon->is_active) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                        Active & usable
                                                    </label>
                                                    <button type="submit" class="mt-2 rounded-lg bg-indigo-600 py-2 text-xs font-semibold text-white hover:bg-indigo-700 transition">Save Changes</button>
                                                </form>
                                            </details>
                                        @endcan

                                        @can('Store Coupon Delete')
                                            <form method="POST" action="{{ route('store.admin.coupons.destroy', $coupon) }}" class="inline" onsubmit="return confirm('Delete this coupon?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs text-rose-500 hover:text-rose-700 transition" title="Delete Coupon">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-10 text-center text-sm text-slate-400">
                                    No coupons created yet. Use the form on the right to add your first promotion.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $coupons->links() }}
            </div>
        </section>

        {{-- Right: Add Coupon Form --}}
        @can('Store Coupon Create')
            <section class="card h-fit p-5 space-y-4">
                <div>
                    <h2 class="font-bold text-slate-900 dark:text-white text-base">New Coupon</h2>
                    <p class="text-xs text-slate-400">Add a promo code for customer discounts.</p>
                </div>

                <form method="POST" action="{{ route('store.admin.coupons.store') }}" class="space-y-3">
                    @csrf
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Coupon Code *
                        <input name="code" value="{{ old('code') }}" required placeholder="e.g. FLASH25" class="mt-1 {{ $field }} uppercase font-mono p-2">
                    </label>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Type *
                            <select name="type" class="mt-1 {{ $field }} p-2">
                                <option value="percentage" @selected(old('type') === 'percentage')>Percentage (%)</option>
                                <option value="fixed" @selected(old('type') === 'fixed')>Fixed Amount ($)</option>
                            </select>
                        </label>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Value *
                            <input name="value" type="number" step="0.01" min="0.01" value="{{ old('value', '10.00') }}" required class="mt-1 {{ $field }} p-2">
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Min Order ($)
                            <input name="min_order_amount" type="number" step="0.01" min="0" value="{{ old('min_order_amount') }}" placeholder="Optional" class="mt-1 {{ $field }} p-2">
                        </label>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Max Discount ($)
                            <input name="max_discount_amount" type="number" step="0.01" min="0" value="{{ old('max_discount_amount') }}" placeholder="For % type" class="mt-1 {{ $field }} p-2">
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Total Limit
                            <input name="usage_limit" type="number" min="1" value="{{ old('usage_limit') }}" placeholder="Unlimited" class="mt-1 {{ $field }} p-2">
                        </label>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Per Customer
                            <input name="usage_limit_per_customer" type="number" min="1" value="{{ old('usage_limit_per_customer', 1) }}" class="mt-1 {{ $field }} p-2">
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Starts At
                            <input name="starts_at" type="datetime-local" value="{{ old('starts_at') }}" class="mt-1 {{ $field }} p-2">
                        </label>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Expires At
                            <input name="expires_at" type="datetime-local" value="{{ old('expires_at') }}" class="mt-1 {{ $field }} p-2">
                        </label>
                    </div>

                    <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 pt-1">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Active immediately
                    </label>

                    <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2.5 text-xs font-semibold text-white hover:bg-indigo-700 transition">
                        Create Coupon
                    </button>
                </form>
            </section>
        @endcan
    </div>
</div>

<script>
(function() {
    function positionPanel(summary, panel) {
        const r = summary.getBoundingClientRect();
        const pW = panel.offsetWidth || 384;
        const pH = panel.offsetHeight || 300;
        let top = r.bottom + 8;
        let left = r.right - pW;
        if (left < 16) left = 16;
        if (left + pW > window.innerWidth - 16) left = window.innerWidth - 16 - pW;
        if (top + pH > window.innerHeight - 16) top = Math.max(16, r.top - pH - 8);
        panel.style.top = `${top}px`;
        panel.style.left = `${left}px`;
    }

    document.querySelectorAll('[data-popover]').forEach(d => {
        const s = d.querySelector('summary');
        const p = d.querySelector('[data-popover-panel]');
        if (!s || !p) return;
        d.addEventListener('toggle', () => {
            if (d.open) {
                p.classList.remove('invisible');
                positionPanel(s, p);
            } else {
                p.classList.add('invisible');
            }
        });
    });

    document.addEventListener('click', e => {
        document.querySelectorAll('[data-popover][open]').forEach(d => {
            if (!d.contains(e.target)) d.open = false;
        });
    });
})();
</script>
@endsection
