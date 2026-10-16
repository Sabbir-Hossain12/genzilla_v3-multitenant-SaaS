@php
    $page = $page ?? null;
    $input = 'w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors';
    $label = 'block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5';
    $sectionIcon = 'w-7 h-7 rounded-lg bg-primarylt dark:bg-primary/15 grid place-items-center shrink-0';
    $icon = 'w-3.5 h-3.5 text-primary';
@endphp

<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    <div class="p-6 md:p-8">

        {{-- ════════════════════════════════════
             PAGE INFORMATION
        ════════════════════════════════════ --}}
        <div class="flex items-center gap-2 mb-5">
            <div class="{{ $sectionIcon }}">
                <svg class="{{ $icon }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <h5 class="text-sm font-bold text-ink dark:text-white">Page Information</h5>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label for="title" class="{{ $label }}">Page Title <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" required placeholder="Terms of Service"
                       value="{{ old('title', $page->title ?? '') }}"
                       class="{{ $input }}"/>
            </div>
            <div>
                <label for="slug" class="{{ $label }}">Page Slug</label>
                <input type="text" id="slug" name="slug" placeholder="terms-of-service"
                       value="{{ old('slug', $page->slug ?? '') }}"
                       class="{{ $input }}"/>
                <p class="mt-1.5 text-xs text-slate-400">Leave blank to auto-generate from the title.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
            <div>
                <label for="meta_title" class="{{ $label }}">Meta Title</label>
                <input type="text" id="meta_title" name="meta_title" placeholder="Terms &amp; Conditions | Genzilla"
                       value="{{ old('meta_title', $page->meta_title ?? '') }}"
                       class="{{ $input }}"/>
            </div>
            <div>
                <label for="pageStatus" class="{{ $label }}">Status</label>
                <div class="flex items-center gap-3 pt-1.5">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="status" id="pageStatus" value="1" class="sr-only peer" @checked(old('status', $page->status ?? 1))>
                        <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300">Published (visible on the site)</span>
                </div>
            </div>
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

        <div class="mb-6">
            <label for="meta_description" class="{{ $label }}">Meta Description</label>
            <textarea id="meta_description" name="meta_description" rows="3" placeholder="Read our terms and conditions..."
                      class="{{ $input }} resize-none">{{ old('meta_description', $page->meta_description ?? '') }}</textarea>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800 my-6"></div>

        {{-- ════════════════════════════════════
             PAGE CONTENT
        ════════════════════════════════════ --}}
        <div class="flex items-center gap-2 mb-5">
            <div class="{{ $sectionIcon }}">
                <svg class="{{ $icon }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
            </div>
            <h5 class="text-sm font-bold text-ink dark:text-white">Page Content</h5>
        </div>

        <div>
            <label for="content" class="{{ $label }}">Content (HTML / Text) <span class="text-red-500">*</span></label>
            <textarea id="content" name="content" rows="12" required placeholder="Write or paste your HTML content here..."
                      class="{{ $input }} font-mono">{{ old('content', $page->content ?? '') }}</textarea>
            <p class="mt-1.5 text-xs text-slate-400">Supports raw HTML. This content is rendered on the public page.</p>
        </div>

    </div>{{-- /body --}}

    {{-- Card Footer --}}
    <div class="px-6 md:px-8 py-4 bg-slate-50/60 dark:bg-slate-800/30 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-4">
        <a href="{{ route('admin.pages.index') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-primary dark:text-slate-400 dark:hover:text-primary transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back to Pages
        </a>
        <button type="submit"
                class="flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primarydark text-white text-sm font-semibold rounded-xl transition-colors shadow-sm shadow-primary/30 focus:outline-none focus:ring-2 focus:ring-primary/30 cursor-pointer">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            {{ $page ? 'Update Page' : 'Save Page' }}
        </button>
    </div>
</div>

@push('js')
    @include('platform_admin.pages.pages._scripts')
@endpush
