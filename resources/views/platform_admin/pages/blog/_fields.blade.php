@php
    $blogPost = $blogPost ?? null;
    $categories = $categories ?? collect();
@endphp

    <!-- Basic Information Card -->
<div class="card bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-6 md:p-8 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Title</label>
                <input type="text" name="title" value="{{ old('title', $blogPost->title ?? '') }}" required
                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $blogPost->slug ?? '') }}" placeholder="auto-generated from title"
                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Category</label>
                <select name="blog_category_id"
                        class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                    <option value="">&mdash; None &mdash;</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('blog_category_id', $blogPost->blog_category_id ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Read Time (minutes)</label>
                <input type="number" name="read_time" min="1" max="120" value="{{ old('read_time', $blogPost->read_time ?? 5) }}"
                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Publish Date</label>
                <input type="date" name="published_at" value="{{ old('published_at', optional($blogPost->published_at ?? null)->format('Y-m-d')) }}"
                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Excerpt</label>
            <textarea name="excerpt" rows="2" required
                      class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">{{ old('excerpt', $blogPost->excerpt ?? '') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Author Name</label>
                <input type="text" name="author_name" value="{{ old('author_name', $blogPost->author_name ?? '') }}" required
                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Author Role</label>
                <input type="text" name="author_role" value="{{ old('author_role', $blogPost->author_role ?? '') }}"
                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Author Initials</label>
                <input type="text" name="author_initials" maxlength="5" value="{{ old('author_initials', $blogPost->author_initials ?? '') }}"
                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Cover Gradient Class</label>
                <input type="text" name="cover_class" value="{{ old('cover_class', $blogPost->cover_class ?? '') }}" placeholder="bg-gradient-to-br from-indigo-50 to-indigo-100"
                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-mono">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Emoji</label>
                <input type="text" name="emoji" maxlength="16" value="{{ old('emoji', $blogPost->emoji ?? '') }}" placeholder="🚀"
                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $blogPost->sort_order ?? 0) }}"
                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
            </div>
        </div>

        <div class="flex items-center gap-6 pt-2">
            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="status" id="blogStatus" value="1" class="sr-only peer" @checked(old('status', $blogPost->status ?? 1))>
                    <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
                <label for="blogStatus" class="text-sm font-medium text-slate-700 cursor-pointer select-none">Published Status</label>
            </div>

            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_featured" id="blogFeatured" value="1" class="sr-only peer" @checked(old('is_featured', $blogPost->is_featured ?? false))>
                    <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
                <label for="blogFeatured" class="text-sm font-medium text-slate-700 cursor-pointer select-none">Featured Post</label>
            </div>
        </div>
    </div>
</div>

<!-- Article Body Card -->
<div class="card bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Article Body</h3>
    </div>
    <div class="p-6 md:p-8 space-y-2">
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Long Description</label>
        <textarea name="long_desc" id="blog-long-desc" rows="12" placeholder="Write the full article here..."
                  class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-mono">{{ old('long_desc', $blogPost->long_desc ?? '') }}</textarea>
        <p class="text-xs text-slate-400 mt-1">Supports headings, bold, lists, and quotes. Stored as HTML and rendered on the public blog.</p>
    </div>
</div>

<!-- SEO Card -->
<div class="card bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">SEO & Metadata</h3>
    </div>
    <div class="p-6 md:p-8 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Meta Title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $blogPost->meta_title ?? '') }}"
                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Meta Keywords</label>
                <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $blogPost->meta_keywords ?? '') }}"
                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Meta Description</label>
            <textarea name="meta_description" rows="2"
                      class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">{{ old('meta_description', $blogPost->meta_description ?? '') }}</textarea>
        </div>
    </div>
    <div class="flex items-center justify-end px-6 py-4 bg-slate-50 border-t border-slate-100">
        <button type="submit"
                class="inline-flex items-center gap-1.5 px-5 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
            <i class="fa-solid fa-floppy-disk text-xs"></i>
            <span>Save Post</span>
        </button>
    </div>
</div>

@push('backendJs')
    @include('platform_admin.pages.blog._scripts')
@endpush
