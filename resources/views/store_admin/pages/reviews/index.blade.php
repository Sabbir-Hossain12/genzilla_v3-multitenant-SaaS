@extends('store_admin.layout.master')

@section('title', 'Product Reviews - ' . (auth()->user()->tenant->store_name ?? 'Store Admin'))

@section('contents')
@php
    $field = 'rounded-xl border-slate-300 bg-white text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500';
    $currentStatus = request('status', 'all');
    $currentRating = request('rating', 'all');
    $currentProduct = request('product_id', 'all');
    $currentVerified = request('verified', 'all');
    $currentDate = request('date_range', 'all');
@endphp

<div class="space-y-6 pb-12">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-1">
                <i class="fa-solid fa-star-half-stroke"></i>
                <span>Store Catalog &bull; Customer Feedback</span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-ink dark:text-white">Product Reviews &amp; Ratings</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Moderate customer feedback, verify purchases, publish genuine reviews, and post licensed pharmacist responses.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            @if($pendingReviews > 0)
                <a href="{{ route('store.admin.reviews.index', ['status' => 'pending']) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 text-xs font-bold shadow-xs hover:bg-amber-500/20 transition">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>{{ $pendingReviews }} Pending Moderation</span>
                </a>
            @endif
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

    {{-- KPI Metric Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Reviews --}}
        <div class="card p-5 border border-slate-200 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Reviews</p>
                    <h3 class="text-2xl font-black text-ink dark:text-white mt-1">
                        {{ number_format($totalReviews) }}
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-1">Across all product catalog</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl shrink-0">
                    <i class="fa-solid fa-comments"></i>
                </div>
            </div>
        </div>

        {{-- Pending Moderation --}}
        <div class="card p-5 border border-slate-200 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pending Review</p>
                    <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                        {{ number_format($pendingReviews) }}
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-1">Awaiting merchant verification</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center text-amber-600 dark:text-amber-400 text-xl shrink-0">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
        </div>

        {{-- Approved Reviews --}}
        <div class="card p-5 border border-slate-200 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Approved & Live</p>
                    <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ number_format($approvedReviews) }}
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-1">Visible on storefront</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-xl shrink-0">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>

        {{-- Average Rating --}}
        <div class="card p-5 border border-slate-200 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Average Rating</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-black text-amber-500">{{ number_format($avgRating, 1) }}</span>
                        <div class="flex items-center text-amber-400 text-xs">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa-solid fa-star {{ $i <= round($avgRating) ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700' }}"></i>
                            @endfor
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">{{ $starBreakdown[5] }} 5-star ratings</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center text-amber-500 text-xl shrink-0">
                    <i class="fa-solid fa-star"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
        <form method="GET" action="{{ route('store.admin.reviews.index') }}" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                {{-- Search --}}
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Search</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Reviewer, medicine, comment…"
                               class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border-slate-300 bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status</label>
                    <select name="status" class="w-full py-2 px-3 text-xs {{ $field }}">
                        <option value="all" @selected($currentStatus === 'all')>All Statuses</option>
                        <option value="pending" @selected($currentStatus === 'pending')>Pending Moderation</option>
                        <option value="approved" @selected($currentStatus === 'approved')>Approved</option>
                        <option value="rejected" @selected($currentStatus === 'rejected')>Rejected</option>
                    </select>
                </div>

                {{-- Rating Filter --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Rating</label>
                    <select name="rating" class="w-full py-2 px-3 text-xs {{ $field }}">
                        <option value="all" @selected($currentRating === 'all')>All Stars</option>
                        <option value="5" @selected($currentRating === '5')>5 Stars ★★★★★</option>
                        <option value="4" @selected($currentRating === '4')>4 Stars ★★★★☆</option>
                        <option value="3" @selected($currentRating === '3')>3 Stars ★★★☆☆</option>
                        <option value="2" @selected($currentRating === '2')>2 Stars ★★☆☆☆</option>
                        <option value="1" @selected($currentRating === '1')>1 Star ★☆☆☆☆</option>
                    </select>
                </div>

                {{-- Verified Purchase --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Buyer Status</label>
                    <select name="verified" class="w-full py-2 px-3 text-xs {{ $field }}">
                        <option value="all" @selected($currentVerified === 'all')>All Reviews</option>
                        <option value="1" @selected($currentVerified === '1' || $currentVerified === 'yes')>Verified Buyers Only</option>
                        <option value="0" @selected($currentVerified === '0' || $currentVerified === 'no')>Unverified Reviews</option>
                    </select>
                </div>

                {{-- Product Dropdown --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Product</label>
                    <select name="product_id" class="w-full py-2 px-3 text-xs {{ $field }}">
                        <option value="all" @selected($currentProduct === 'all')>All Products</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" @selected((string)$currentProduct === (string)$prod->id)>
                                {{ Str::limit($prod->name, 25) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Quick Date:</span>
                    <a href="{{ request()->fullUrlWithQuery(['date_range' => 'all']) }}"
                       class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $currentDate === 'all' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                        All Time
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['date_range' => 'today']) }}"
                       class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $currentDate === 'today' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                        Today
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['date_range' => 'last_7_days']) }}"
                       class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $currentDate === 'last_7_days' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                        Last 7 Days
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['date_range' => 'this_month']) }}"
                       class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $currentDate === 'this_month' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                        This Month
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    @if(request()->anyFilled(['search', 'status', 'rating', 'product_id', 'verified', 'date_range']))
                        <a href="{{ route('store.admin.reviews.index') }}"
                           class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-700 text-xs font-semibold">
                            Reset
                        </a>
                    @endif
                    <button type="submit"
                            class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs">
                        Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Bulk Action Bar (Hidden by default, shown when items are selected) --}}
    <form id="bulk-action-form" method="POST" action="{{ route('store.admin.reviews.bulk') }}" class="hidden">
        @csrf
        <div id="bulk-bar" class="p-3 bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 rounded-2xl flex flex-wrap items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-indigo-600 text-white font-bold text-xs flex items-center justify-center" id="selected-count">0</span>
                <span class="text-xs font-semibold text-indigo-950 dark:text-indigo-200">reviews selected</span>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="action" id="bulk-action-input" value="approve">
                <button type="button" onclick="submitBulk('approve')" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>Approve Selected</span>
                </button>
                <button type="button" onclick="submitBulk('reject')" class="px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-ban"></i>
                    <span>Reject Selected</span>
                </button>
                <button type="button" onclick="submitBulk('delete')" class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-trash"></i>
                    <span>Delete Selected</span>
                </button>
                <button type="button" onclick="clearSelection()" class="px-2.5 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-400 text-xs font-semibold hover:bg-white dark:hover:bg-slate-800">
                    Cancel
                </button>
            </div>
        </div>
    </form>

    {{-- Reviews Table --}}
    <div class="card overflow-hidden border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-900/75 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="py-3 px-4 w-10 text-center">
                            <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)"
                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th class="py-3 px-4">Product</th>
                        <th class="py-3 px-4">Reviewer</th>
                        <th class="py-3 px-4">Rating & Feedback</th>
                        <th class="py-3 px-4">Pharmacist Response</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($reviews as $review)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            {{-- Checkbox --}}
                            <td class="py-3.5 px-4 text-center">
                                <input type="checkbox" name="ids[]" value="{{ $review->id }}" form="bulk-action-form"
                                       onchange="updateSelectionCount()"
                                       class="review-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </td>

                            {{-- Product --}}
                            <td class="py-3.5 px-4 min-w-[200px]">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shrink-0 flex items-center justify-center">
                                        @if($review->product->thumbnail_url)
                                            <img src="{{ $review->product->thumbnail_url }}" alt="{{ $review->product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="fa-solid fa-pills text-slate-400 text-base"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('store.admin.products.edit', $review->product->id) }}"
                                           class="font-bold text-ink dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 line-clamp-1">
                                            {{ $review->product->name }}
                                        </a>
                                        @if($review->product->generic_name)
                                            <p class="text-[10px] text-slate-400 italic line-clamp-1">
                                                {{ $review->product->generic_name }}
                                            </p>
                                        @endif
                                        @if($review->product->requires_prescription)
                                            <span class="inline-flex items-center gap-1 text-[9px] font-bold text-amber-600 bg-amber-50 dark:bg-amber-950/40 px-1.5 py-0.2 rounded mt-0.5">
                                                <i class="fa-solid fa-prescription"></i> Rx Required
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Reviewer --}}
                            <td class="py-3.5 px-4 min-w-[170px]">
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $review->reviewer_avatar }}" alt="{{ $review->reviewer_display_name }}"
                                         class="w-8 h-8 rounded-full ring-1 ring-slate-200 dark:ring-slate-700 shrink-0">
                                    <div class="min-w-0">
                                        <p class="font-bold text-ink dark:text-white truncate">
                                            {{ $review->reviewer_display_name }}
                                        </p>
                                        <p class="text-[11px] text-slate-400 truncate">
                                            {{ $review->reviewer_email ?: 'Guest User' }}
                                        </p>
                                        @if($review->is_verified_purchase)
                                            <span class="inline-flex items-center gap-1 text-[9px] font-bold text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 dark:text-emerald-300 px-1.5 py-0.2 rounded mt-0.5">
                                                <i class="fa-solid fa-shield-check"></i> Verified Buyer
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-[9px] font-medium text-slate-400 mt-0.5">
                                                Unverified
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Rating & Feedback --}}
                            <td class="py-3.5 px-4 max-w-[340px]">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <div class="flex items-center text-amber-400 text-xs">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fa-solid fa-star {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">{{ $review->rating }}.0</span>
                                        <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                                        <span class="text-[10px] text-slate-400" title="{{ $review->created_at->format('Y-m-d H:i') }}">
                                            {{ $review->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    @if($review->title)
                                        <p class="font-bold text-ink dark:text-white text-xs line-clamp-1">
                                            {{ $review->title }}
                                        </p>
                                    @endif

                                    <p class="text-slate-600 dark:text-slate-300 text-xs line-clamp-2 leading-relaxed">
                                        {{ $review->comment }}
                                    </p>

                                    @if(!empty($review->images) && is_array($review->images))
                                        <div class="flex items-center gap-1.5 pt-1">
                                            @foreach(array_slice($review->images, 0, 3) as $img)
                                                <a href="{{ $img }}" target="_blank" class="w-8 h-8 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 inline-block shrink-0">
                                                    <img src="{{ $img }}" class="w-full h-full object-cover">
                                                </a>
                                            @endforeach
                                            @if(count($review->images) > 3)
                                                <span class="text-[10px] font-bold text-slate-400">+{{ count($review->images) - 3 }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Pharmacist Response --}}
                            <td class="py-3.5 px-4 min-w-[200px] max-w-[260px]">
                                @if($review->admin_reply)
                                    <div class="p-2.5 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 space-y-1">
                                        <div class="flex items-center justify-between text-[10px] font-bold text-indigo-700 dark:text-indigo-300">
                                            <span class="flex items-center gap-1">
                                                <i class="fa-solid fa-user-doctor"></i>
                                                <span>{{ $review->repliedBy?->name ?? 'Pharmacist' }}</span>
                                            </span>
                                            <span class="text-[9px] text-slate-400">{{ $review->replied_at?->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-700 dark:text-slate-300 line-clamp-2 leading-snug">
                                            {{ $review->admin_reply }}
                                        </p>
                                    </div>
                                @else
                                    <button type="button" onclick="openReplyModal({{ $review->id }}, '{{ addslashes($review->reviewer_display_name) }}', '{{ addslashes($review->product->name) }}', '')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 font-semibold text-xs transition">
                                        <i class="fa-solid fa-reply text-xs"></i>
                                        <span>Add Response</span>
                                    </button>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @php $badge = $review->status_badge; @endphp
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $badge['class'] }}">
                                    <i class="{{ $badge['icon'] }}"></i>
                                    <span>{{ $badge['label'] }}</span>
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Quick Approve / Reject --}}
                                    @can('Store Product Review Approve')
                                        @if($review->status !== 'approved')
                                            <form method="POST" action="{{ route('store.admin.reviews.status', $review->id) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" title="Approve Review"
                                                        class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 grid place-items-center transition">
                                                    <i class="fa-solid fa-check text-xs"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($review->status !== 'rejected')
                                            <form method="POST" action="{{ route('store.admin.reviews.status', $review->id) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" title="Reject Review"
                                                        class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 hover:bg-amber-100 grid place-items-center transition">
                                                    <i class="fa-solid fa-ban text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan

                                    {{-- Open Full View / Reply Modal --}}
                                    <button type="button" onclick="loadReviewDetails({{ $review->id }})" title="View Details & Reply"
                                            class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 grid place-items-center transition">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>

                                    {{-- Delete --}}
                                    @can('Store Product Review Delete')
                                        <form method="POST" action="{{ route('store.admin.reviews.destroy', $review->id) }}"
                                              onsubmit="return confirm('Are you sure you want to delete this product review?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete Review"
                                                    class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 hover:bg-rose-100 grid place-items-center transition">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 grid place-items-center text-2xl mx-auto">
                                        <i class="fa-solid fa-star-half-stroke"></i>
                                    </div>
                                    <h3 class="text-base font-extrabold text-ink dark:text-white">No Reviews Found</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        No customer product reviews match your current filters. Clear your filters or check back when customers post new feedback.
                                    </p>
                                    @if(request()->anyFilled(['search', 'status', 'rating', 'product_id', 'verified', 'date_range']))
                                        <a href="{{ route('store.admin.reviews.index') }}"
                                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-xs hover:bg-indigo-700 transition">
                                            Clear All Filters
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($reviews->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════
     MODAL: REVIEW DETAILS & PHARMACIST RESPONSE
══════════════════════════════════════════════════════ --}}
<div id="review-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="card w-full max-w-2xl max-h-[90vh] flex flex-col bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden animate-in fade-in zoom-in duration-150">
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 grid place-items-center text-sm">
                    <i class="fa-solid fa-message"></i>
                </div>
                <h3 class="font-extrabold text-base text-ink dark:text-white">Product Review &amp; Pharmacist Reply</h3>
            </div>
            <button type="button" onclick="closeReviewModal()" class="w-8 h-8 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-ink dark:hover:text-white grid place-items-center text-lg">
                &times;
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="p-6 overflow-y-auto space-y-5 flex-1 text-xs">
            {{-- Product Summary --}}
            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/60 flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-white dark:bg-slate-700 overflow-hidden border border-slate-200 dark:border-slate-600 shrink-0 flex items-center justify-center">
                    <img id="modal-product-img" src="" class="w-full h-full object-cover hidden">
                    <i id="modal-product-placeholder" class="fa-solid fa-pills text-slate-400 text-lg"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <a id="modal-product-name" href="#" target="_blank" class="font-extrabold text-sm text-ink dark:text-white hover:text-indigo-600 truncate block"></a>
                    <p id="modal-product-generic" class="text-[11px] text-slate-400 italic truncate"></p>
                </div>
                <span id="modal-verified-badge" class="px-2 py-1 rounded-lg text-[10px] font-bold text-emerald-700 bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 shrink-0">
                    <i class="fa-solid fa-shield-check"></i> Verified Buyer
                </span>
            </div>

            {{-- Review Details --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <img id="modal-reviewer-avatar" src="" class="w-9 h-9 rounded-full ring-1 ring-slate-200 dark:ring-slate-700">
                        <div>
                            <p id="modal-reviewer-name" class="font-bold text-ink dark:text-white"></p>
                            <p id="modal-reviewer-email" class="text-[11px] text-slate-400"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div id="modal-rating-stars" class="flex items-center text-amber-400 text-sm"></div>
                        <p id="modal-created-at" class="text-[10px] text-slate-400 mt-0.5"></p>
                    </div>
                </div>

                {{-- Title & Comment --}}
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 space-y-2">
                    <h4 id="modal-review-title" class="font-bold text-sm text-ink dark:text-white"></h4>
                    <p id="modal-review-comment" class="text-slate-600 dark:text-slate-300 leading-relaxed text-xs"></p>
                    <div id="modal-review-images" class="flex flex-wrap gap-2 pt-2 hidden"></div>
                </div>
            </div>

            {{-- Pharmacist Reply Form --}}
            <form id="reply-form" method="POST" action="" class="space-y-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                @csrf
                <div class="flex items-center justify-between">
                    <label class="block font-bold text-ink dark:text-white text-xs">
                        <i class="fa-solid fa-user-doctor text-indigo-600 mr-1"></i>
                        Pharmacist / Store Admin Official Response
                    </label>
                    <span id="modal-replied-meta" class="text-[10px] text-slate-400 font-medium"></span>
                </div>
                <textarea name="admin_reply" id="modal-reply-input" rows="4" required
                          placeholder="Write a professional, medically compliant response to the customer…"
                          class="w-full rounded-2xl border-slate-300 bg-white p-3 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600 dark:text-slate-300 text-xs">
                        <input type="checkbox" name="auto_approve" value="1" checked
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Also approve and publish review if currently pending</span>
                    </label>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition hover:-translate-y-0.5">
                        <i class="fa-solid fa-paper-plane mr-1"></i>
                        Submit Pharmacist Response
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
    /* ── Review Details Modal ── */
    function loadReviewDetails(reviewId) {
        fetch(`{{ url('/store/catalog/reviews') }}/${reviewId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;
            const r = data.review;

            // Product
            document.getElementById('modal-product-name').textContent = r.product.name;
            document.getElementById('modal-product-name').href = r.product.url;
            document.getElementById('modal-product-generic').textContent = r.product.generic_name || 'General Product';

            const pImg = document.getElementById('modal-product-img');
            const pPh = document.getElementById('modal-product-placeholder');
            if (r.product.thumbnail) {
                pImg.src = r.product.thumbnail;
                pImg.classList.remove('hidden');
                pPh.classList.add('hidden');
            } else {
                pImg.classList.add('hidden');
                pPh.classList.remove('hidden');
            }

            // Buyer status
            const vBadge = document.getElementById('modal-verified-badge');
            if (r.is_verified_purchase) {
                vBadge.classList.remove('hidden');
            } else {
                vBadge.classList.add('hidden');
            }

            // Reviewer
            document.getElementById('modal-reviewer-name').textContent = r.reviewer_name;
            document.getElementById('modal-reviewer-email').textContent = r.reviewer_email || 'Guest Customer';
            document.getElementById('modal-reviewer-avatar').src = r.reviewer_avatar;
            document.getElementById('modal-created-at').textContent = `${r.created_at} (${r.created_diff})`;

            // Stars
            let starsHtml = '';
            for (let i = 1; i <= 5; i++) {
                starsHtml += `<i class="fa-solid fa-star ${i <= r.rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700'}"></i>`;
            }
            document.getElementById('modal-rating-stars').innerHTML = starsHtml;

            // Title & Comment
            document.getElementById('modal-review-title').textContent = r.title || 'Product Review';
            document.getElementById('modal-review-comment').textContent = r.comment;

            // Images
            const imgBox = document.getElementById('modal-review-images');
            imgBox.innerHTML = '';
            if (Array.isArray(r.images) && r.images.length > 0) {
                imgBox.classList.remove('hidden');
                r.images.forEach(src => {
                    imgBox.innerHTML += `<a href="${src}" target="_blank" class="w-14 h-14 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700"><img src="${src}" class="w-full h-full object-cover"></a>`;
                });
            } else {
                imgBox.classList.add('hidden');
            }

            // Pharmacist Reply Form
            const replyForm = document.getElementById('reply-form');
            replyForm.action = `{{ url('/store/catalog/reviews') }}/${r.id}/reply`;
            document.getElementById('modal-reply-input').value = r.admin_reply || '';

            const replyMeta = document.getElementById('modal-replied-meta');
            if (r.admin_reply) {
                replyMeta.textContent = `Replied by ${r.replied_by || 'Pharmacist'} on ${r.replied_at}`;
            } else {
                replyMeta.textContent = 'No reply yet';
            }

            document.getElementById('review-modal').classList.remove('hidden');
        })
        .catch(err => {
            console.error(err);
            alert('Failed to load review details.');
        });
    }

    function openReplyModal(id, reviewerName, productName, existingReply) {
        loadReviewDetails(id);
    }

    function closeReviewModal() {
        document.getElementById('review-modal').classList.add('hidden');
    }

    /* ── Bulk Selection ── */
    function toggleSelectAll(master) {
        document.querySelectorAll('.review-checkbox').forEach(cb => cb.checked = master.checked);
        updateSelectionCount();
    }

    function updateSelectionCount() {
        const checked = document.querySelectorAll('.review-checkbox:checked');
        const count = checked.length;
        document.getElementById('selected-count').textContent = count;
        const bulkBar = document.getElementById('bulk-action-form');
        if (count > 0) {
            bulkBar.classList.remove('hidden');
        } else {
            bulkBar.classList.add('hidden');
            const selectAll = document.getElementById('select-all');
            if (selectAll) selectAll.checked = false;
        }
    }

    function clearSelection() {
        document.querySelectorAll('.review-checkbox').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('select-all');
        if (selectAll) selectAll.checked = false;
        updateSelectionCount();
    }

    function submitBulk(action) {
        const checked = document.querySelectorAll('.review-checkbox:checked');
        if (checked.length === 0) {
            alert('Please select at least one review.');
            return;
        }
        if (action === 'delete' && !confirm(`Are you sure you want to delete ${checked.length} selected review(s)?`)) {
            return;
        }
        document.getElementById('bulk-action-input').value = action;
        document.getElementById('bulk-action-form').submit();
    }
</script>
@endpush
@endsection
