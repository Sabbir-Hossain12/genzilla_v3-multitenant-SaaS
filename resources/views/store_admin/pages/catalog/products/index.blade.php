@extends('store_admin.layout.master')
@section('contents')
@php
    $tenantObj = tenant() ?? \App\Models\Tenant::find(tenantId());
    $reachedProductLimit = $tenantObj && $tenantObj->hasReachedProductLimit();
@endphp
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Products</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage product details, default variants, and stock.</p>
        </div>
        @can('Store Product Create')
            @if($reachedProductLimit)
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800">
                        <i class="fa-solid fa-lock text-[10px]"></i> Product Limit ({{ $tenantObj->currentProductsCount() }}/{{ $tenantObj->maxProducts() }})
                    </span>
                    <a href="{{ route('store.admin.subscription.index') }}" class="rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-indigo-700 transition">
                        Upgrade Plan
                    </a>
                </div>
            @else
                <a href="{{ route('store.admin.products.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition">Add product</a>
            @endif
        @endcan
    </div>

    @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:bg-rose-950/30 dark:border-rose-900 dark:text-rose-300 flex items-center justify-between gap-3">
            <span>{{ session('error') }}</span>
            <a href="{{ route('store.admin.subscription.index') }}" class="font-bold underline text-xs shrink-0">Upgrade Plan &rarr;</a>
        </div>
    @endif

    @if($reachedProductLimit)
        <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-900 dark:bg-amber-950/30 dark:border-amber-900 dark:text-amber-200 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                <span>You have reached your catalog limit of <strong>{{ $tenantObj->maxProducts() }}</strong> products for the <strong>{{ $tenantObj->currentPlan()?->name }}</strong> plan.</span>
            </div>
            <a href="{{ route('store.admin.subscription.index') }}" class="font-bold text-indigo-700 hover:text-indigo-800 underline text-xs shrink-0">
                Upgrade Subscription &rarr;
            </a>
        </div>
    @endif

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
