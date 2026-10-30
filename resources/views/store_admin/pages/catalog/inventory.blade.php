@extends('store_admin.layout.master')
@section('contents')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Inventory</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Manage received stock, batch expiry, and adjustments.</p>
    </div>

    @if($errors->any())
        <div class="rounded-xl bg-rose-50 p-3 text-sm text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">{{ $errors->first() }}</div>
    @endif

    <section class="card divide-y divide-slate-100 overflow-hidden dark:divide-slate-800">
        @forelse($variants as $variant)
            <article class="p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold text-slate-900 dark:text-white">{{ $variant->product->name }} - {{ $variant->title }}</h2>
                            @if(!$variant->track_inventory)
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">Untracked</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            SKU {{ $variant->sku }} |
                            <span class="font-medium text-slate-700 dark:text-slate-200">{{ $variant->batches->sum('quantity') }}</span> on hand |
                            <span class="font-medium text-slate-700 dark:text-slate-200">{{ $variant->batches->sum('reserved') }}</span> reserved |
                            <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ $variant->available_stock }}</span> available
                        </p>
                    </div>

                    @can('Store Inventory Manage')
                        <details class="relative">
                            <summary class="cursor-pointer rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700 transition">Receive stock</summary>
                            <form method="POST" action="{{ route('store.admin.inventory.receive', $variant) }}" class="absolute right-0 z-20 mt-2 grid w-64 gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                                @csrf
                                <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Batch number
                                    <input name="batch_no" value="DEFAULT" required class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                </label>
                                <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Quantity
                                    <input name="quantity" type="number" min="1" required class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                </label>
                                <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Expiry date
                                    <input name="expiry_date" type="date" class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                </label>
                                <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Cost price
                                    <input name="cost_price" type="number" min="0" step="0.01" class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                </label>
                                <button class="rounded-lg bg-indigo-600 p-2 text-xs font-semibold text-white hover:bg-indigo-700 transition">Receive</button>
                            </form>
                        </details>
                    @endcan
                </div>

                @if($variant->batches->isNotEmpty())
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[700px] text-sm">
                            <thead class="text-left text-xs text-slate-400">
                                <tr>
                                    <th class="py-2">Batch</th>
                                    <th>Expiry</th>
                                    <th>Quantity</th>
                                    <th>Reserved</th>
                                    <th>Available</th>
                                    <th>Status</th>
                                    @can('Store Inventory Manage')
                                        <th>Adjust</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($variant->batches as $batch)
                                    <tr class="border-t border-slate-100 dark:border-slate-800">
                                        <td class="py-2 font-mono text-xs">{{ $batch->batch_no }}</td>
                                        <td>{{ $batch->expiry_date?->format('M j, Y') ?? 'None' }}</td>
                                        <td class="font-medium">{{ $batch->quantity }}</td>
                                        <td class="text-slate-400">{{ $batch->reserved }}</td>
                                        <td class="font-medium {{ ($batch->quantity - $batch->reserved) > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">{{ max(0, $batch->quantity - $batch->reserved) }}</td>
                                        <td>
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $batch->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300' }}">
                                                {{ ucfirst($batch->status) }}
                                            </span>
                                        </td>
                                        @can('Store Inventory Manage')
                                            <td>
                                                <form method="POST" action="{{ route('store.admin.inventory.adjust', $batch) }}" class="flex gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="number" name="quantity_delta" placeholder="+/- qty" class="w-24 rounded-lg border-slate-300 bg-white px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                                    <input name="note" placeholder="Reason" class="w-28 rounded-lg border-slate-300 bg-white px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                                    <select name="status" class="rounded-lg border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                                        <option value="active" @selected($batch->status==='active')>Active</option>
                                                        <option value="quarantined" @selected($batch->status==='quarantined')>Quarantined</option>
                                                        <option value="recalled" @selected($batch->status==='recalled')>Recalled</option>
                                                    </select>
                                                    <button class="rounded border border-slate-300 px-2 text-xs hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">Save</button>
                                                </form>
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mt-4 text-xs text-slate-400">No batches recorded yet.</p>
                @endif
            </article>
        @empty
            <div class="p-10 text-center text-sm text-slate-400">Create a product to track stock.</div>
        @endforelse

        <div class="p-4">{{ $variants->links() }}</div>
    </section>

    <section class="card overflow-hidden">
        <div class="border-b border-slate-100 p-4 dark:border-slate-800">
            <h2 class="font-bold text-ink dark:text-white">Recent stock movements</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                    <tr>
                        <th class="p-3">Date</th>
                        <th class="p-3">Product / SKU</th>
                        <th class="p-3">Type</th>
                        <th class="p-3">Qty</th>
                        <th class="p-3">User</th>
                        <th class="p-3">Note</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="p-3 text-xs text-slate-500">{{ $movement->created_at?->format('M j, Y H:i') }}</td>
                            <td class="p-3">
                                <div class="font-medium text-slate-800 dark:text-slate-200">{{ $movement->variant?->product?->name ?? 'Archived product' }}</div>
                                <div class="text-xs text-slate-400">{{ $movement->variant?->sku ?? '-' }}</div>
                            </td>
                            <td class="p-3 capitalize">
                                <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium {{ in_array($movement->type, ['receive', 'return']) ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : (in_array($movement->type, ['adjust']) ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300') }}">
                                    {{ str_replace('_', ' ', $movement->type) }}
                                </span>
                            </td>
                            <td class="p-3 font-semibold {{ $movement->qty_delta > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($movement->qty_delta < 0 ? 'text-rose-600 dark:text-rose-400' : '') }}">
                                {{ $movement->qty_delta > 0 ? '+' : '' }}{{ $movement->qty_delta }}
                            </td>
                            <td class="p-3 text-xs text-slate-500">{{ $movement->user?->name ?? 'System' }}</td>
                            <td class="p-3 text-xs text-slate-500">{{ $movement->note ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">No movements recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
