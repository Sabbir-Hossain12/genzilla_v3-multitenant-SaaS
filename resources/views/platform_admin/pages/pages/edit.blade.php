@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Edit Custom Page</h1>
                <p class="text-sm text-slate-500 mt-0.5">Update page details, meta descriptions, and content.</p>
            </div>
            <a href="{{ route('admin.pages.index') }}"
               class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg shadow-sm transition-all gap-2">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Back to Pages</span>
            </a>
        </div>

        <form method="POST" action="{{ route('admin.pages.update', $page->id) }}">
            @csrf
            @method('PUT')
            <div class="card overflow-hidden bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm">
                <div class="p-6 md:p-8 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Page Title</label>
                            <input type="text" name="title" value="{{ old('title', $page->title) }}" required
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Page Slug</label>
                            <input type="text" name="slug" value="{{ old('slug', $page->slug) }}"
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Meta Title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Status</label>
                            <select name="status"
                                    class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                <option value="1" {{ $page->status == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $page->status == 0 ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Meta Description</label>
                        <textarea name="meta_description" rows="2"
                                  class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">{{ old('meta_description', $page->meta_description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Page Content (HTML / Text)</label>
                        <textarea name="content" rows="12" required
                                  class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-mono">{{ old('content', $page->content) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Update Page</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
