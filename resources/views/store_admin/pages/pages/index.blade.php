@extends('store_admin.layout.master')

@section('contents')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Custom Pages</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Create and publish static informational content like Terms, Privacy Policy, FAQs, and About Us.</p>
        </div>
        @can('Store Page Create')
            <a href="{{ route('store.admin.pages.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Create New Page</span>
            </a>
        @endcan
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-300">&times;</button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                <i class="fa-solid fa-file-lines text-base"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Total Pages</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats['total'] ?? 0 }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                <i class="fa-solid fa-circle-check text-base"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Published</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats['active'] ?? 0 }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                <i class="fa-solid fa-eye-slash text-base"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Draft / Hidden</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats['inactive'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-slate-400"></i>
                <h2 class="font-bold text-slate-900 dark:text-white">Storefront Pages</h2>
                <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 text-xs font-semibold text-slate-600 dark:text-slate-400">
                    {{ $pages->total() }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-16">#</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Page Title</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Slug & Route</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">SEO Meta Title</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Status</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($pages as $page)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-4 py-3.5 text-xs font-mono text-slate-400">{{ $loop->iteration + ($pages->currentPage() - 1) * $pages->perPage() }}</td>
                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-slate-900 dark:text-white">{{ $page->title }}</div>
                            <div class="text-xs text-slate-400 line-clamp-1 mt-0.5 max-w-md">
                                {{ Str::limit(strip_tags($page->content), 80) }}
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-mono text-slate-600 dark:text-slate-300">
                                <i class="fa-solid fa-link text-[10px] text-slate-400"></i>
                                <span>/page/{{ $page->slug }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-300">
                            {{ $page->meta_title ?: '-' }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @can('Store Page Edit')
                                <form action="{{ route('store.admin.pages.toggle', $page) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            title="Click to toggle status"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium cursor-pointer transition {{ $page->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $page->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $page->is_active ? 'Active' : 'Draft' }}
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $page->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $page->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $page->is_active ? 'Active' : 'Draft' }}
                                </span>
                            @endcan
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <div class="inline-flex items-center gap-1">
                                @can('Store Page Edit')
                                    <a href="{{ route('store.admin.pages.edit', $page) }}"
                                       title="Edit Page"
                                       class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>
                                @endcan

                                @can('Store Page Delete')
                                    <form action="{{ route('store.admin.pages.destroy', $page) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to permanently delete this page?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Delete Page"
                                                class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 transition">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3">
                                    <i class="fa-solid fa-file-circle-xmark text-xl"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No Custom Pages Found</p>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm">Create informational pages like Terms of Service, Privacy Policy, and Return Guidelines.</p>
                                @can('Store Page Create')
                                    <a href="{{ route('store.admin.pages.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                                        <i class="fa-solid fa-plus text-xs"></i> Add Page
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($pages->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $pages->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
