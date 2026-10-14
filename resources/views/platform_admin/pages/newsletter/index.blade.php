@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Newsletter Subscribers</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Manage email subscriptions and subscriber statuses.</p>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                    <tr class="bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        <th class="py-3.5 px-4 w-16">#</th>
                        <th class="py-3.5 px-4">Email Address</th>
                        <th class="py-3.5 px-4 w-44">Subscription Status</th>
                        <th class="py-3.5 px-4 w-48">Subscribed Date</th>
                        <th class="py-3.5 px-4 w-36 text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-sm text-slate-700 dark:text-slate-300">
                    @forelse($subscribers as $sub)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-4 font-medium text-slate-500 dark:text-slate-400">
                                {{ $loop->iteration }}
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-900 dark:text-white">
                                <a href="mailto:{{ $sub->email }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                    {{ $sub->email }}
                                </a>
                            </td>
                            <td class="py-4 px-4">
                                @if($sub->is_subscribed)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-900/50">
                                    <i class="fa-solid fa-check text-[10px]"></i>
                                    Subscribed
                                </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    <i class="fa-solid fa-ban text-[10px]"></i>
                                    Unsubscribed
                                </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-500 dark:text-slate-400">
                                {{ $sub->subscribed_at ? $sub->subscribed_at->format('M d, Y H:i') : ($sub->created_at ? $sub->created_at->format('M d, Y H:i') : '-') }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <form action="{{ route('admin.newsletter.toggle', $sub->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 text-amber-600 dark:text-amber-400 hover:text-amber-700 hover:bg-amber-50 dark:hover:bg-amber-950/50 rounded-lg transition-colors cursor-pointer" title="Toggle Subscription Status">
                                            <i class="fa-solid fa-rotate text-xs"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.newsletter.destroy', $sub->id) }}" method="POST" class="inline" onsubmit="return confirm('Remove subscriber?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-rose-600 dark:text-rose-400 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-colors cursor-pointer" title="Remove Subscriber">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-slate-400 dark:text-slate-500 py-12 text-sm">No newsletter subscribers found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
