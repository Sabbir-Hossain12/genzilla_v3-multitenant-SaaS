@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Custom Pages</h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage and organize dynamic platform pages and SEO meta tags.</p>
            </div>
            <a href="{{ route('admin.pages.create') }}"
               class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 rounded-lg shadow-sm transition-all gap-2">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Create New Page</span>
            </a>
        </div>

        <!-- Main Card Container -->
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
                <div>
                    <h2 class="font-bold text-ink dark:text-white">Pages</h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm table-fixed">
                    <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-16">#</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-64">Title</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-48">Slug</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Meta Title</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Status</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($pages as $page)
                        <tr class="trow border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-3.5 text-xs font-bold text-primary w-16">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3.5 w-64 truncate font-semibold text-slate-900 dark:text-white" title="{{ $page->title }}">{{ $page->title }}</td>
                            <td class="px-4 py-3.5 w-48 truncate font-mono text-xs text-slate-500">
                                <code>/{{ $page->slug }}</code>
                            </td>
                            <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 truncate" title="{{ $page->meta_title ?? '-' }}">
                                {{ $page->meta_title ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center w-28">
                                @if($page->status)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-ink dark:text-slate-200 tabular-nums w-28">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.pages.edit', $page->id) }}"
                                       class="p-2 text-sky-600 hover:text-sky-700 hover:bg-sky-50 rounded-lg transition-colors"
                                       title="Edit Page">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST"
                                          class="inline" onsubmit="return confirm('Delete this page?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                title="Delete Page">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-slate-400 py-12 text-sm">No custom pages found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
