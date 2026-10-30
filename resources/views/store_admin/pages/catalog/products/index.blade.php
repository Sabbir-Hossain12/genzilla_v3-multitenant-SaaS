@extends('store_admin.layout.master')
@section('contents')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Products</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage product details, default variants, and stock.</p>
        </div>
        @can('Store Product Create')
            <a href="{{ route('store.admin.products.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition">Add product</a>
        @endcan
    </div>

    @if($errors->any())
        <div class="rounded-xl bg-rose-50 p-3 text-sm text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">{{ $errors->first() }}</div>
    @endif

    <section class="card overflow-hidden">
        <form method="GET" class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-4 dark:border-slate-800">
            <div>
                <h2 class="font-bold text-ink dark:text-white">Catalog</h2>
                <p class="text-xs text-slate-400">{{ $products->total() }} products</p>
            </div>
            <div class="flex gap-2">
                <input name="search" value="{{ request('search') }}" placeholder="Search products" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm dark:border-slate-700">Search</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                    <tr>
                        <th class="p-4">Product</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Category / Brand</th>
                        <th class="p-4">Price</th>
                        <th class="p-4">Stock</th>
                        <th class="p-4">Status</th>
                        <th class="p-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $activeVars = $product->variants->where('status', 'active');
                            $primaryVar = $activeVars->firstWhere('is_default', true) ?? $activeVars->first() ?? $product->variants->first();
                            $totalStock = (int) $activeVars->sum(fn($v) => $v->batches->sum('quantity'));
                            $tracksAny = $activeVars->contains('track_inventory', true);
                            $prices = $activeVars->pluck('price')->filter(fn($p) => $p !== null);
                            $minPrice = $prices->min();
                            $maxPrice = $prices->max();
                        @endphp
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    @if($product->thumbnail_url)
                                        <img src="{{ $product->thumbnail_url }}" class="h-10 w-10 rounded-lg object-cover" alt="{{ $product->name }}">
                                    @elseif($product->media->first())
                                        <img src="{{ asset('storage/'.$product->media->first()->path) }}" class="h-10 w-10 rounded-lg object-cover" alt="{{ $product->name }}">
                                    @else
                                        <div class="grid h-10 w-10 place-items-center rounded-lg bg-slate-100 dark:bg-slate-800">
                                            <i class="fa-solid fa-box text-slate-400"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-semibold text-slate-900 dark:text-white">{{ $product->name }}</div>
                                        <div class="text-xs font-normal text-slate-400">{{ $primaryVar?->sku ?? 'No SKU' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 capitalize">{{ $product->kind }}</td>
                            <td class="p-4">
                                {{ $product->category?->name ?? '-' }}
                                <div class="text-xs text-slate-400">{{ $product->brand?->name }}</div>
                            </td>
                            <td class="p-4 font-medium">
                                @if($minPrice !== null && $maxPrice !== null && $minPrice != $maxPrice)
                                    {{ number_format((float)$minPrice, 2) }} - {{ number_format((float)$maxPrice, 2) }}
                                @else
                                    {{ number_format((float)($primaryVar?->price ?? 0), 2) }}
                                @endif
                            </td>
                            <td class="p-4">
                                @if(!$tracksAny && $activeVars->isNotEmpty())
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300">Untracked</span>
                                @else
                                    <span class="font-semibold {{ $totalStock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $totalStock }}
                                    </span>
                                    @if($activeVars->count() > 1)
                                        <span class="block text-[11px] text-slate-400">({{ $activeVars->count() }} variants)</span>
                                    @endif
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $product->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : ($product->status === 'draft' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400') }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                @can('Store Product Edit')
                                    <a class="text-indigo-600 hover:text-indigo-700 font-medium" href="{{ route('store.admin.products.edit', $product) }}">Edit</a>
                                @endcan
                                @can('Store Product Delete')
                                    <form method="POST" action="{{ route('store.admin.products.destroy', $product) }}" class="inline" onsubmit="return confirm('Archive this product?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ml-2 text-rose-600 hover:text-rose-700 font-medium">Archive</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center text-slate-400">No products yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $products->links() }}</div>
    </section>
</div>
@endsection
