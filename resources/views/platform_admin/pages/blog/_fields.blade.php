@php
    $blogPost = $blogPost ?? null;
    $categories = $categories ?? collect();
    $input = 'w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors';
    $label = 'block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5';
    $sectionIcon = 'w-7 h-7 rounded-lg bg-primarylt dark:bg-primary/15 grid place-items-center shrink-0';
    $icon = 'w-3.5 h-3.5 text-primary';
@endphp

<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    <div class="p-6 md:p-8">

        {{-- ════════════════════════════════════
             POST DETAILS
        ════════════════════════════════════ --}}
        <div class="flex items-center gap-2 mb-5">
            <div class="{{ $sectionIcon }}">
                <svg class="{{ $icon }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <h5 class="text-sm font-bold text-ink dark:text-white">Post Details</h5>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
            <div class="md:col-span-2">
                <label for="title" class="{{ $label }}">Title <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" required value="{{ old('title', $blogPost->title ?? '') }}"
                       class="{{ $input }}"/>
            </div>
            <div>
                <label for="slug" class="{{ $label }}">Slug</label>
                <input type="text" id="slug" name="slug" placeholder="auto-generated from title"
                       value="{{ old('slug', $blogPost->slug ?? '') }}"
                       class="{{ $input }}"/>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
            <div>
                <label for="blog_category_id" class="{{ $label }}">Category</label>
                <select id="blog_category_id" name="blog_category_id" class="{{ $input }}">
                    <option value="">&mdash; None &mdash;</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('blog_category_id', $blogPost->blog_category_id ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="read_time" class="{{ $label }}">Read Time (minutes)</label>
                <input type="number" id="read_time" name="read_time" min="1" max="120" value="{{ old('read_time', $blogPost->read_time ?? 5) }}"
                       class="{{ $input }}"/>
            </div>
            <div>
                <label for="published_at" class="{{ $label }}">Publish Date</label>
                <input type="date" id="published_at" name="published_at" value="{{ old('published_at', optional($blogPost->published_at ?? null)->format('Y-m-d')) }}"
                       class="{{ $input }}"/>
            </div>
        </div>

        <div class="mb-5">
            <label for="excerpt" class="{{ $label }}">Excerpt <span class="text-red-500">*</span></label>
            <textarea id="excerpt" name="excerpt" rows="3" required placeholder="A short summary shown on the blog listing..."
                      class="{{ $input }} resize-none">{{ old('excerpt', $blogPost->excerpt ?? '') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
            <div>
                <label for="author_name" class="{{ $label }}">Author Name <span class="text-red-500">*</span></label>
                <input type="text" id="author_name" name="author_name" required value="{{ old('author_name', $blogPost->author_name ?? '') }}"
                       class="{{ $input }}"/>
            </div>
            <div>
                <label for="author_role" class="{{ $label }}">Author Role</label>
                <input type="text" id="author_role" name="author_role" value="{{ old('author_role', $blogPost->author_role ?? '') }}"
                       class="{{ $input }}"/>
            </div>
            <div>
                <label for="author_initials" class="{{ $label }}">Author Initials</label>
                <input type="text" id="author_initials" name="author_initials" maxlength="5" value="{{ old('author_initials', $blogPost->author_initials ?? '') }}"
                       class="{{ $input }}"/>
            </div>
        </div>

        <div class="mb-5">
            <label for="sort_order" class="{{ $label }}">Sort Order</label>
            <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $blogPost->sort_order ?? 0) }}"
                   class="{{ $input }}"/>
        </div>

        {{-- Cover & Thumbnail Images --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label class="{{ $label }}">Cover Image</label>
                <div class="relative flex flex-col items-center justify-center w-full h-44 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl bg-white/70 dark:bg-slate-800/40 hover:border-primary/40 transition cursor-pointer"
                     onclick="document.getElementById('cover_img').click()">
                    <input id="cover_img" type="file" name="cover_img" accept="image/*" class="hidden"
                           onchange="previewBlogImage(this, 'blogCoverPrev')">
                    <img id="blogCoverPrev"
                         src="{{ $blogPost && $blogPost->cover_img ? asset('storage/' . $blogPost->cover_img) : '' }}"
                         alt="Cover image preview"
                         class="w-full h-full object-cover rounded-2xl {{ $blogPost && $blogPost->cover_img ? '' : 'hidden' }}">
                    <div id="blogCoverPrevPlaceholder" class="flex flex-col items-center text-center px-4 {{ $blogPost && $blogPost->cover_img ? 'hidden' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                             class="text-slate-400">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                            <circle cx="9" cy="9" r="2"/>
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                        </svg>
                        <p class="mt-2 text-sm font-medium text-slate-600 dark:text-slate-300">Upload cover image</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">JPG, PNG, GIF, WEBP up to 5MB</p>
                    </div>
                </div>
                @error('cover_img')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $label }}">Thumbnail Image</label>
                <div class="relative flex flex-col items-center justify-center w-full h-44 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl bg-white/70 dark:bg-slate-800/40 hover:border-primary/40 transition cursor-pointer"
                     onclick="document.getElementById('thumbnail_img').click()">
                    <input id="thumbnail_img" type="file" name="thumbnail_img" accept="image/*" class="hidden"
                           onchange="previewBlogImage(this, 'blogThumbPrev')">
                    <img id="blogThumbPrev"
                         src="{{ $blogPost && $blogPost->thumbnail_img ? asset('storage/' . $blogPost->thumbnail_img) : '' }}"
                         alt="Thumbnail preview"
                         class="w-full h-full object-cover rounded-2xl {{ $blogPost && $blogPost->thumbnail_img ? '' : 'hidden' }}">
                    <div id="blogThumbPrevPlaceholder" class="flex flex-col items-center text-center px-4 {{ $blogPost && $blogPost->thumbnail_img ? 'hidden' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                             class="text-slate-400">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                            <circle cx="9" cy="9" r="2"/>
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                        </svg>
                        <p class="mt-2 text-sm font-medium text-slate-600 dark:text-slate-300">Upload thumbnail</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">JPG, PNG, GIF, WEBP up to 5MB</p>
                    </div>
                </div>
                @error('thumbnail_img')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex items-center gap-6 pt-2">
            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="status" id="blogStatus" value="1" class="sr-only peer" @checked(old('status', $blogPost->status ?? 1))>
                    <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                </label>
                <label for="blogStatus" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none">Published Status</label>
            </div>

            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_featured" id="blogFeatured" value="1" class="sr-only peer" @checked(old('is_featured', $blogPost->is_featured ?? false))>
                    <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                </label>
                <label for="blogFeatured" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none">Featured Post</label>
            </div>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800 my-6"></div>

        {{-- ════════════════════════════════════
             ARTICLE BODY
        ════════════════════════════════════ --}}
        <div class="flex items-center gap-2 mb-5">
            <div class="{{ $sectionIcon }}">
                <svg class="{{ $icon }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
            </div>
            <h5 class="text-sm font-bold text-ink dark:text-white">Article Body</h5>
        </div>

        <div class="mb-6">
            <label for="blog-long-desc" class="{{ $label }}">Long Description</label>
            <textarea name="long_desc" id="blog-long-desc" rows="12" placeholder="Write the full article here..."
                      class="{{ $input }} font-mono">{{ old('long_desc', $blogPost->long_desc ?? '') }}</textarea>
{{--            <p class="mt-1.5 text-xs text-slate-400">Supports headings, bold, lists, and quotes. Stored as HTML and rendered on the public blog.</p>--}}
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800 my-6"></div>

        {{-- ════════════════════════════════════
             SEO & METADATA
        ════════════════════════════════════ --}}
        <div class="flex items-center gap-2 mb-5">
            <div class="{{ $sectionIcon }}">
                <svg class="{{ $icon }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
            <h5 class="text-sm font-bold text-ink dark:text-white">SEO &amp; Metadata</h5>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label for="meta_title" class="{{ $label }}">Meta Title</label>
                <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title', $blogPost->meta_title ?? '') }}"
                       class="{{ $input }}"/>
            </div>
            <div>
                <label for="meta_keywords" class="{{ $label }}">Meta Keywords</label>
                <input type="text" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $blogPost->meta_keywords ?? '') }}"
                       class="{{ $input }}"/>
            </div>
        </div>
        <div>
            <label for="meta_description" class="{{ $label }}">Meta Description</label>
            <textarea id="meta_description" name="meta_description" rows="3"
                      class="{{ $input }} resize-none">{{ old('meta_description', $blogPost->meta_description ?? '') }}</textarea>
        </div>

    </div>{{-- /body --}}

    {{-- Card Footer --}}
    <div class="px-6 md:px-8 py-4 bg-slate-50/60 dark:bg-slate-800/30 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-4">
        <a href="{{ route('admin.blog.index') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-primary dark:text-slate-400 dark:hover:text-primary transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back to Blog
        </a>
        <button type="submit"
                class="flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primarydark text-white text-sm font-semibold rounded-xl transition-colors shadow-sm shadow-primary/30 focus:outline-none focus:ring-2 focus:ring-primary/30 cursor-pointer">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            {{ $blogPost ? 'Update Post' : 'Save Post' }}
        </button>
    </div>
</div>

@push('js')
    @include('platform_admin.pages.blog._scripts')
    <script>
        function previewBlogImage(input, targetId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var el = document.getElementById(targetId);
                    el.src = e.target.result;
                    el.classList.remove('hidden');
                    var ph = document.getElementById(targetId + 'Placeholder');
                    if (ph) ph.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush
