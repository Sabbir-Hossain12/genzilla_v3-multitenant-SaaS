@extends('store_admin.layout.master')
@section('contents')
@php
    $field = 'w-full rounded-lg border-slate-300 bg-white text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white';
@endphp

<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Delivery Charges & Shipping Rates</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Configure shipping methods, rates, free shipping thresholds, and estimated delivery times.</p>
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
        {{-- Left: Delivery Options List --}}
        <section class="card min-w-0 overflow-hidden">
            <div class="border-b border-slate-100 p-4 dark:border-slate-800 flex items-center justify-between">
                <h2 class="font-bold text-slate-900 dark:text-white">Shipping Options ({{ $deliveryCharges->count() }})</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                        <tr>
                            <th class="p-4">Shipping Method</th>
                            <th class="p-4">Rate</th>
                            <th class="p-4">Free Shipping Over</th>
                            <th class="p-4">Estimated Delivery</th>
                            <th class="p-4">Sort</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($deliveryCharges as $charge)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                <td class="p-4">
                                    <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i class="fa-solid fa-truck-fast text-indigo-500"></i>
                                        {{ $charge->title }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $charge->slug }}</div>
                                    @if($charge->description)
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xs">{{ $charge->description }}</div>
                                    @endif
                                </td>
                                <td class="p-4 font-bold font-mono text-slate-900 dark:text-white">
                                    @if((float)$charge->charge_amount == 0)
                                        <span class="rounded bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 font-bold">FREE</span>
                                    @else
                                        ${{ number_format((float)$charge->charge_amount, 2) }}
                                    @endif
                                </td>
                                <td class="p-4 text-xs font-mono">
                                    {{ $charge->min_order_amount ? '$' . number_format((float)$charge->min_order_amount, 2) : '-' }}
                                </td>
                                <td class="p-4 text-xs text-slate-600 dark:text-slate-300">
                                    {{ $charge->estimated_days ?? 'Not specified' }}
                                </td>
                                <td class="p-4 text-xs font-mono text-slate-400">
                                    {{ $charge->sort_order }}
                                </td>
                                <td class="p-4">
                                    @can('Store Delivery Charge Edit')
                                        <form method="POST" action="{{ route('store.admin.delivery_charges.toggle', $charge) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold cursor-pointer transition {{ $charge->status ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400' }}">
                                                {{ $charge->status ? 'Active' : 'Disabled' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $charge->status ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $charge->status ? 'Active' : 'Disabled' }}
                                        </span>
                                    @endcan
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @can('Store Delivery Charge Edit')
                                            <details class="inline-block text-left" data-popover>
                                                <summary class="cursor-pointer text-xs font-semibold text-indigo-600 hover:underline">Edit</summary>
                                                <form method="POST" action="{{ route('store.admin.delivery_charges.update', $charge) }}" data-popover-panel class="invisible fixed z-50 grid max-h-[calc(100vh-2rem)] w-88 sm:w-96 max-w-[calc(100vw-2rem)] gap-3 overflow-y-auto rounded-xl border border-slate-200 bg-white p-4 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                                                    @csrf @method('PUT')
                                                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Edit Option: {{ $charge->title }}</h3>
                                                    <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Method Title *
                                                        <input name="title" value="{{ $charge->title }}" required class="mt-1 {{ $field }} p-2">
                                                    </label>
                                                    <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Slug (URL / identifier)
                                                        <input name="slug" value="{{ $charge->slug }}" class="mt-1 {{ $field }} font-mono p-2">
                                                    </label>
                                                    <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Description
                                                        <textarea name="description" rows="2" class="mt-1 {{ $field }} p-2">{{ $charge->description }}</textarea>
                                                    </label>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Rate / Charge ($) *
                                                            <input name="charge_amount" type="number" step="0.01" min="0" value="{{ $charge->charge_amount }}" required class="mt-1 {{ $field }} p-2">
                                                        </label>
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Free Over ($)
                                                            <input name="min_order_amount" type="number" step="0.01" min="0" value="{{ $charge->min_order_amount }}" class="mt-1 {{ $field }} p-2">
                                                        </label>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Estimated Days
                                                            <input name="estimated_days" value="{{ $charge->estimated_days }}" placeholder="e.g. 2-3 days" class="mt-1 {{ $field }} p-2">
                                                        </label>
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Sort Order
                                                            <input name="sort_order" type="number" min="0" value="{{ $charge->sort_order }}" class="mt-1 {{ $field }} p-2">
                                                        </label>
                                                    </div>
                                                    <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 pt-1">
                                                        <input type="checkbox" name="status" value="1" @checked($charge->status) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                        Active & selectable at checkout
                                                    </label>
                                                    <button type="submit" class="mt-2 rounded-lg bg-indigo-600 py-2 text-xs font-semibold text-white hover:bg-indigo-700 transition">Save Changes</button>
                                                </form>
                                            </details>
                                        @endcan

                                        @can('Store Delivery Charge Delete')
                                            <form method="POST" action="{{ route('store.admin.delivery_charges.destroy', $charge) }}" class="inline" onsubmit="return confirm('Delete this shipping option?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs text-rose-500 hover:text-rose-700 transition" title="Delete Shipping Option">
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
                                    No shipping options created yet. Use the form on the right to configure delivery methods.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Right: Add Delivery Charge Form --}}
        @can('Store Delivery Charge Create')
            <section class="card h-fit p-5 space-y-4">
                <div>
                    <h2 class="font-bold text-slate-900 dark:text-white text-base">New Shipping Option</h2>
                    <p class="text-xs text-slate-400">Add a delivery method and pricing rule.</p>
                </div>

                <form method="POST" action="{{ route('store.admin.delivery_charges.store') }}" class="space-y-3">
                    @csrf
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Method Title *
                        <input name="title" value="{{ old('title') }}" required placeholder="e.g. Standard Express Delivery" class="mt-1 {{ $field }} p-2">
                    </label>

                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Slug (optional)
                        <input name="slug" value="{{ old('slug') }}" placeholder="Auto-generated if blank" class="mt-1 {{ $field }} font-mono p-2">
                    </label>

                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Description
                        <textarea name="description" rows="2" placeholder="e.g. Delivered across all metropolitan areas within 48 hours." class="mt-1 {{ $field }} p-2">{{ old('description') }}</textarea>
                    </label>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Rate / Charge ($) *
                            <input name="charge_amount" type="number" step="0.01" min="0" value="{{ old('charge_amount', '5.00') }}" required class="mt-1 {{ $field }} p-2">
                        </label>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Free Over ($)
                            <input name="min_order_amount" type="number" step="0.01" min="0" value="{{ old('min_order_amount') }}" placeholder="e.g. 50" class="mt-1 {{ $field }} p-2">
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Estimated Days
                            <input name="estimated_days" value="{{ old('estimated_days', '2-4 business days') }}" class="mt-1 {{ $field }} p-2">
                        </label>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Sort Order
                            <input name="sort_order" type="number" min="0" value="{{ old('sort_order', 0) }}" class="mt-1 {{ $field }} p-2">
                        </label>
                    </div>

                    <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 pt-1">
                        <input type="checkbox" name="status" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Active & selectable at checkout
                    </label>

                    <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2.5 text-xs font-semibold text-white hover:bg-indigo-700 transition">
                        Create Shipping Option
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
