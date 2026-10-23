@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Blog Posts</h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage and organize platform articles and blog publications.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.blog-categories.index') }}"
                   class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg shadow-sm transition-all gap-2 dark:bg-slate-900 dark:text-slate-200 dark:border-slate-700">
                    <i class="fa-solid fa-tags text-xs"></i>
                    <span>Categories</span>
                </a>
                <a href="{{ route('admin.blog.create') }}"
                   class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 rounded-lg shadow-sm transition-all gap-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Create Post</span>
                </a>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="card overflow-hidden">
            <!-- Filter Bar -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
                <form method="GET" action="{{ route('admin.blog.index') }}" class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Status:</label>
                        <select name="status" onchange="this.form.submit()"
                                class="text-xs border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg px-3 py-1.5 focus:outline-none focus:border-indigo-600 transition-colors">
                            <option value="">All Statuses</option>
                            <option value="1" @selected(request('status') === '1')>Published</option>
                            <option value="0" @selected(request('status') === '0')>Draft</option>
                        </select>
                    </div>
                    @if(request()->has('status'))
                        <a href="{{ route('admin.blog.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">Reset</a>
                    @endif
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-fixed">
                    <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-16">#</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-20">Image</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-64">Title</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-40">Category</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-44">Author</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Published</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-36">Flags</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($posts as $post)
                        <tr class="trow border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-3.5 text-xs font-bold text-primary w-16">{{ $posts->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3.5 w-20">
                                <img src="{{ $post->thumbnail_img ? asset('storage/' . $post->thumbnail_img) : asset('backend/assets/images/placeholder.jpg') }}"
                                     alt="{{ $post->title }}"
                                     class="w-12 h-12 rounded-lg object-cover border border-slate-200 dark:border-slate-700">
                            </td>
                            <td class="px-4 py-3.5 w-64 truncate">
                                <div class="font-semibold text-slate-900 dark:text-white truncate" title="{{ $post->title }}">{{ $post->title }}</div>
                                <div class="text-xs text-slate-400 truncate font-mono"><code>/blog/{{ $post->slug }}</code></div>
                            </td>
                            <td class="px-4 py-3.5 w-40 truncate">
                                @if($post->category)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ $post->category->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 w-44 truncate">
                                <div class="font-medium text-slate-900 dark:text-white truncate">{{ $post->author_name }}</div>
                                @if($post->author_role)
                                    <div class="text-xs text-slate-400 truncate">{{ $post->author_role }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-500 w-32 whitespace-nowrap">
                                {{ optional($post->published_at)->format('M j, Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 w-36">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if($post->is_featured)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200/60">Featured</span>
                                    @endif
                                    @if($post->status)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Published
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Draft
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-ink dark:text-slate-200 tabular-nums w-28">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.blog.edit', $post) }}"
                                       class="p-2 text-sky-600 hover:text-sky-700 hover:bg-sky-50 rounded-lg transition-colors"
                                       title="Edit Post">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.blog.destroy', $post) }}" method="POST"
                                          class="inline" onsubmit="return confirm('Delete this post?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                title="Delete Post">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-slate-400 py-12 text-sm">No blog posts found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($posts->hasPages())
                <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
