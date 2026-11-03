@extends('store_admin.layout.master')

@section('title', 'Review #' . $review->id . ' - ' . (auth()->user()->tenant->store_name ?? 'Store Admin'))

@section('contents')
<div class="max-w-4xl mx-auto space-y-6 pb-12">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('store.admin.reviews.index') }}"
               class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 grid place-items-center text-slate-500 hover:text-ink dark:hover:text-white transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl font-extrabold text-ink dark:text-white">Review #{{ $review->id }} Details</h1>
                <p class="text-xs text-slate-400">Created {{ $review->created_at->format('M d, Y h:i A') }} ({{ $review->created_at->diffForHumans() }})</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @php $badge = $review->status_badge; @endphp
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border {{ $badge['class'] }}">
                <i class="{{ $badge['icon'] }}"></i>
                <span>{{ $badge['label'] }}</span>
            </span>
        </div>
    </div>

    {{-- Product & Review Card --}}
    <div class="card p-6 border border-slate-200 dark:border-slate-800 space-y-5">
        {{-- Product Summary --}}
        <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60">
            <div class="w-14 h-14 rounded-xl bg-white dark:bg-slate-700 overflow-hidden border border-slate-200 dark:border-slate-600 shrink-0 flex items-center justify-center">
                @if($review->product->thumbnail_url)
                    <img src="{{ $review->product->thumbnail_url }}" class="w-full h-full object-cover">
                @else
                    <i class="fa-solid fa-pills text-slate-400 text-xl"></i>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <a href="{{ route('store.admin.products.edit', $review->product->id) }}" class="font-extrabold text-base text-ink dark:text-white hover:text-indigo-600 truncate block">
                    {{ $review->product->name }}
                </a>
                <p class="text-xs text-slate-400 italic">{{ $review->product->generic_name ?? 'General Product' }}</p>
            </div>
            @if($review->is_verified_purchase)
                <span class="px-3 py-1 rounded-xl text-xs font-bold text-emerald-700 bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 shrink-0">
                    <i class="fa-solid fa-shield-check"></i> Verified Buyer
                </span>
            @endif
        </div>

        {{-- Reviewer & Star Rating --}}
        <div class="flex items-center justify-between pt-2">
            <div class="flex items-center gap-3">
                <img src="{{ $review->reviewer_avatar }}" class="w-10 h-10 rounded-full ring-1 ring-slate-200 dark:ring-slate-700">
                <div>
                    <h3 class="font-bold text-sm text-ink dark:text-white">{{ $review->reviewer_display_name }}</h3>
                    <p class="text-xs text-slate-400">{{ $review->reviewer_email ?: 'Guest Shopper' }}</p>
                </div>
            </div>
            <div class="text-right">
                <div class="flex items-center text-amber-400 text-base">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-solid fa-star {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700' }}"></i>
                    @endfor
                </div>
                <span class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ $review->rating }}.0 out of 5</span>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 space-y-2">
            @if($review->title)
                <h4 class="font-extrabold text-base text-ink dark:text-white">{{ $review->title }}</h4>
            @endif
            <p class="text-slate-700 dark:text-slate-200 leading-relaxed text-sm">{{ $review->comment }}</p>

            @if(!empty($review->images) && is_array($review->images))
                <div class="flex flex-wrap gap-2.5 pt-3">
                    @foreach($review->images as $img)
                        <a href="{{ $img }}" target="_blank" class="w-20 h-20 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 block">
                            <img src="{{ $img }}" class="w-full h-full object-cover">
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Quick Moderation Status Form --}}
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500">Moderation Status:</span>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('store.admin.reviews.status', $review->id) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $review->status === 'approved' ? 'bg-emerald-600 text-white' : 'border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-emerald-50' }}">
                        Approve
                    </button>
                </form>
                <form method="POST" action="{{ route('store.admin.reviews.status', $review->id) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $review->status === 'rejected' ? 'bg-rose-600 text-white' : 'border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-rose-50' }}">
                        Reject
                    </button>
                </form>
                <form method="POST" action="{{ route('store.admin.reviews.status', $review->id) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="pending">
                    <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $review->status === 'pending' ? 'bg-amber-500 text-white' : 'border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-amber-50' }}">
                        Pending
                    </button>
                </form>
            </div>
        </div>

        {{-- Pharmacist Response Form --}}
        <form method="POST" action="{{ route('store.admin.reviews.reply', $review->id) }}" class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            @csrf
            <div class="flex items-center justify-between">
                <label class="block font-bold text-ink dark:text-white text-xs">
                    <i class="fa-solid fa-user-doctor text-indigo-600 mr-1"></i>
                    Official Pharmacist Response
                </label>
                @if($review->admin_reply)
                    <span class="text-[11px] text-slate-400">Replied {{ $review->replied_at?->diffForHumans() }} by {{ $review->repliedBy?->name ?? 'Pharmacist' }}</span>
                @endif
            </div>
            <textarea name="admin_reply" rows="4" required placeholder="Write a verified pharmacist response…"
                      class="w-full rounded-2xl border-slate-300 bg-white p-3 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">{{ old('admin_reply', $review->admin_reply) }}</textarea>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer text-slate-600 dark:text-slate-300 text-xs">
                    <input type="checkbox" name="auto_approve" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span>Also approve review</span>
                </label>
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs">
                    Post Pharmacist Response
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
