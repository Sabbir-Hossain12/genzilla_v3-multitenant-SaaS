@php
    $page = $page ?? null;
    $input = 'w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-colors';
    $label = 'block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5';
    $sectionIcon = 'w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 grid place-items-center shrink-0';
@endphp

<div class="card overflow-hidden">
    <div class="p-6 md:p-8 space-y-6">

        {{-- Section 1: Page Information --}}
        <div>
            <div class="flex items-center gap-2.5 mb-4">
                <div class="{{ $sectionIcon }}">
                    <i class="fa-solid fa-file-lines text-sm"></i>
                </div>
                <div>
                    <h5 class="text-sm font-bold text-slate-900 dark:text-white">Page Details</h5>
                    <p class="text-xs text-slate-400">Basic identification for this storefront page</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="title" class="{{ $label }}">Page Title <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" required placeholder="e.g. Terms & Conditions, Return Policy"
                           value="{{ old('title', $page->title ?? '') }}"
                           class="{{ $input }}"/>
                </div>
                <div>
                    <label for="slug" class="{{ $label }}">URL Slug</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-mono text-slate-400">/page/</span>
                        <input type="text" id="slug" name="slug" placeholder="terms-and-conditions"
                               value="{{ old('slug', $page->slug ?? '') }}"
                               class="{{ $input }} pl-16 font-mono text-xs"/>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-400">Leave blank to automatically slugify from the page title.</p>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800"></div>

        {{-- Section 2: Visibility & Publishing --}}
        <div>
            <div class="flex items-center gap-2.5 mb-4">
                <div class="{{ $sectionIcon }}">
                    <i class="fa-solid fa-toggle-on text-sm"></i>
                </div>
                <div>
                    <h5 class="text-sm font-bold text-slate-900 dark:text-white">Publishing & Visibility</h5>
                    <p class="text-xs text-slate-400">Control storefront publication status</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" id="pageActive" value="1" class="sr-only peer" @checked(old('is_active', $page ? $page->is_active : 1))>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 dark:bg-slate-700"></div>
                </label>
                <div>
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Active & Published</span>
                    <p class="text-xs text-slate-400">When enabled, customers can view this page in the storefront footer or navigation.</p>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800"></div>

        {{-- Section 3: SEO Metadata --}}
        <div>
            <div class="flex items-center gap-2.5 mb-4">
                <div class="{{ $sectionIcon }}">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <div>
                    <h5 class="text-sm font-bold text-slate-900 dark:text-white">Search Engine Optimization (SEO)</h5>
                    <p class="text-xs text-slate-400">Meta tags for Google and social previews</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="meta_title" class="{{ $label }}">Meta Title</label>
                    <input type="text" id="meta_title" name="meta_title" placeholder="e.g. Terms of Service | MediStore"
                           value="{{ old('meta_title', $page->meta_title ?? '') }}"
                           class="{{ $input }}"/>
                </div>

                <div>
                    <label for="meta_description" class="{{ $label }}">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" rows="3" placeholder="Brief summary of this page for search results..."
                              class="{{ $input }} resize-none">{{ old('meta_description', $page->meta_description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800"></div>

        {{-- Section 4: Page Content --}}
        <div>
            <div class="flex items-center gap-2.5 mb-4">
                <div class="{{ $sectionIcon }}">
                    <i class="fa-solid fa-code text-sm"></i>
                </div>
                <div>
                    <h5 class="text-sm font-bold text-slate-900 dark:text-white">Page Content <span class="text-red-500">*</span></h5>
                    <p class="text-xs text-slate-400">HTML or rich text body for this page</p>
                </div>
            </div>

            <div>
                <textarea id="content" name="content" rows="14" required placeholder="<h1>Terms & Conditions</h1><p>Welcome to our online store...</p>"
                          class="{{ $input }} font-mono text-xs leading-relaxed">{{ old('content', $page->content ?? '') }}</textarea>
                <p class="mt-1.5 text-xs text-slate-400">Supports standard HTML markup, lists, headings, and paragraph formatting.</p>
            </div>
        </div>

    </div>

    {{-- Card Footer --}}
    <div class="px-6 md:px-8 py-4 bg-slate-50/60 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4">
        <a href="{{ route('store.admin.pages.index') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors">
            <i class="fa-solid fa-arrow-left text-xs"></i> Back to Pages
        </a>

        <div class="flex items-center gap-3">
            <a href="{{ route('store.admin.pages.index') }}"
               class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm transition">
                <i class="fa-solid fa-check"></i>
                <span>{{ $page ? 'Update Page' : 'Save & Publish Page' }}</span>
            </button>
        </div>
    </div>
</div>
