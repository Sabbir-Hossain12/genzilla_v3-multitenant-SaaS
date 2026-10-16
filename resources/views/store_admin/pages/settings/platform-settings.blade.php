@extends('platform_admin.layout.master')

@section('contents')

    {{-- Page Title --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h4 class="text-xl font-extrabold text-ink dark:text-white">Platform Settings</h4>
            <nav class="flex items-center gap-1.5 mt-1 text-xs text-slate-400">
                <a href="{{ route('admin.dashboard.index') }}" class="hover:text-primary transition-colors">Admin</a>
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                <span class="text-slate-500 dark:text-slate-300 font-medium">Platform Settings</span>
            </nav>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div id="errorAlert"
                 class="flex items-start gap-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30
                    text-red-700 dark:text-red-400 rounded-2xl px-4 py-3.5 mb-5 text-sm">
                <svg class="w-4 h-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <ul class="flex-1 space-y-0.5 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" onclick="this.closest('#errorAlert').remove()"
                        class="shrink-0 text-red-400 hover:text-red-600 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        @endif

        {{-- Main Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">

            {{-- Tab Navigation --}}
            <div class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 px-4 pt-4 overflow-x-auto">
                <div class="flex gap-1 min-w-max" role="tablist" id="settingsTabs">

                    <button type="button" role="tab" data-tab="general"
                            class="settings-tab active flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-t-xl transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        General Settings
                    </button>

                    <button type="button" role="tab" data-tab="seo"
                            class="settings-tab flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-t-xl transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        SEO & Open Graph
                    </button>

                    <button type="button" role="tab" data-tab="social"
                            class="settings-tab flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-t-xl transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        Social Links
                    </button>

                    <button type="button" role="tab" data-tab="scripts"
                            class="settings-tab flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-t-xl transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                        Analytics & Scripts
                    </button>

                    <button type="button" role="tab" data-tab="announcement"
                            class="settings-tab flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-t-xl transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        Announcement Bar
                    </button>

                </div>
            </div>

            {{-- Tab Panels --}}
            <div class="p-6 md:p-8">

                {{-- ════════════════════════════════════
                     1. GENERAL SETTINGS
                ════════════════════════════════════ --}}
                <div id="tab-general" class="tab-panel">

                    {{-- Brand & System --}}
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-7 h-7 rounded-lg bg-primarylt dark:bg-primary/15 grid place-items-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9h18M9 21V9"/></svg>
                        </div>
                        <h5 class="text-sm font-bold text-ink dark:text-white">Brand & System Information</h5>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label for="platform_name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Platform Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="platform_name" name="platform_name"
                                   value="{{ old('platform_name', $setting->platform_name ?? 'MySaaS') }}"
                                   required
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors"/>
                        </div>
                        <div>
                            <label for="tagline" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tagline</label>
                            <input type="text" id="tagline" name="tagline"
                                   value="{{ old('tagline', $setting->tagline) }}"
                                   placeholder="e.g. Next-Gen Store Builder"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors"/>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="short_desc" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Short Description</label>
                        <textarea id="short_desc" name="short_desc" rows="3"
                                  class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors resize-none">{{ old('short_desc', $setting->short_desc) }}</textarea>
                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-800 my-6"></div>

                    {{-- Media & Logos --}}
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-7 h-7 rounded-lg bg-primarylt dark:bg-primary/15 grid place-items-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                        <h5 class="text-sm font-bold text-ink dark:text-white">Media & Logos</h5>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">

                        {{-- Dark Logo --}}
                        <div class="border border-dashed border-slate-200 dark:border-slate-700 rounded-2xl p-4 hover:border-primary/50 dark:hover:border-primary/40 transition-colors">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Dark / Black Logo</label>
                            <div class="flex items-center justify-center h-16 bg-slate-50 dark:bg-slate-800 rounded-xl mb-3 overflow-hidden">
                                <img id="blackLogoPrev"
                                     src="{{ $setting->black_logo ? asset('storage/' . $setting->black_logo) : asset('backend/assets/images/logo-dark.png') }}"
                                     class="max-h-12 max-w-full object-contain" alt="Dark Logo"/>
                            </div>
                            <input class="block w-full text-xs text-slate-500 dark:text-slate-400
                                      file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                      file:text-xs file:font-semibold
                                      file:bg-primarylt file:text-primary
                                      dark:file:bg-primary/10 dark:file:text-primary
                                      hover:file:bg-primarylt/80 cursor-pointer transition-colors"
                                   type="file" id="black_logo" name="black_logo" accept="image/*"
                                   onchange="previewImg(this, 'blackLogoPrev')"/>
                        </div>

                        {{-- Light Logo --}}
                        <div class="border border-dashed border-slate-200 dark:border-slate-700 rounded-2xl p-4 hover:border-primary/50 dark:hover:border-primary/40 transition-colors">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Light Logo</label>
                            <div class="flex items-center justify-center h-16 bg-slate-800 dark:bg-slate-700 rounded-xl mb-3 overflow-hidden">
                                <img id="lightLogoPrev"
                                     src="{{ $setting->light_logo ? asset('storage/' . $setting->light_logo) : asset('backend/assets/images/logo-light.png') }}"
                                     class="max-h-12 max-w-full object-contain" alt="Light Logo"/>
                            </div>
                            <input class="block w-full text-xs text-slate-500 dark:text-slate-400
                                      file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                      file:text-xs file:font-semibold
                                      file:bg-primarylt file:text-primary
                                      dark:file:bg-primary/10 dark:file:text-primary
                                      hover:file:bg-primarylt/80 cursor-pointer transition-colors"
                                   type="file" id="light_logo" name="light_logo" accept="image/*"
                                   onchange="previewImg(this, 'lightLogoPrev')"/>
                        </div>

                        {{-- Favicon --}}
                        <div class="border border-dashed border-slate-200 dark:border-slate-700 rounded-2xl p-4 hover:border-primary/50 dark:hover:border-primary/40 transition-colors">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Favicon</label>
                            <div class="flex items-center justify-center h-16 bg-slate-50 dark:bg-slate-800 rounded-xl mb-3 overflow-hidden">
                                <img id="faviconPrev"
                                     src="{{ $setting->favicon ? asset('storage/' . $setting->favicon) : asset('backend/assets/images/favicon.ico') }}"
                                     class="max-h-10 max-w-full object-contain" alt="Favicon"/>
                            </div>
                            <input class="block w-full text-xs text-slate-500 dark:text-slate-400
                                      file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                      file:text-xs file:font-semibold
                                      file:bg-primarylt file:text-primary
                                      dark:file:bg-primary/10 dark:file:text-primary
                                      hover:file:bg-primarylt/80 cursor-pointer transition-colors"
                                   type="file" id="favicon" name="favicon" accept="image/*"
                                   onchange="previewImg(this, 'faviconPrev')"/>
                        </div>

                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-800 my-6"></div>

                    {{-- Contact & Regional --}}
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-7 h-7 rounded-lg bg-primarylt dark:bg-primary/15 grid place-items-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                        <h5 class="text-sm font-bold text-ink dark:text-white">Contact & Regional Settings</h5>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Platform Contact Email</label>
                            <input type="email" id="email" name="email"
                                   value="{{ old('email', $setting->email) }}"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors"/>
                        </div>
                        <div>
                            <label for="phone_1" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Phone Number</label>
                            <input type="text" id="phone_1" name="phone_1"
                                   value="{{ old('phone_1', $setting->phone_1) }}"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors"/>
                        </div>
                        <div>
                            <label for="working_hours" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Working Hours</label>
                            <input type="text" id="working_hours" name="working_hours"
                                   value="{{ old('working_hours', $setting->working_hours) }}"
                                   placeholder="Mon – Fri: 9am – 6pm"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors"/>
                        </div>
                        <div>
                            <label for="default_currency" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Default Currency</label>
                            <input type="text" id="default_currency" name="default_currency"
                                   value="{{ old('default_currency', $setting->default_currency ?? 'USD') }}"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors"/>
                        </div>
                        <div class="md:col-span-2">
                            <label for="company_address" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Company Address</label>
                            <textarea id="company_address" name="company_address" rows="2"
                                      class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors resize-none">{{ old('company_address', $setting->company_address) }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label for="copyright_text" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Copyright Text</label>
                            <input type="text" id="copyright_text" name="copyright_text"
                                   value="{{ old('copyright_text', $setting->copyright_text) }}"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors"/>
                        </div>
                    </div>
                </div>

                {{-- ════════════════════════════════════
                     2. SEO & OPEN GRAPH
                ════════════════════════════════════ --}}
                <div id="tab-seo" class="tab-panel hidden">

                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-7 h-7 rounded-lg bg-primarylt dark:bg-primary/15 grid place-items-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </div>
                        <h5 class="text-sm font-bold text-ink dark:text-white">Search Engine Optimization</h5>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label for="meta_title" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Meta Title</label>
                            <input type="text" id="meta_title" name="meta_title"
                                   value="{{ old('meta_title', $setting->meta_title) }}"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors"/>
                        </div>
                        <div>
                            <label for="meta_robots" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Meta Robots</label>
                            <input type="text" id="meta_robots" name="meta_robots"
                                   value="{{ old('meta_robots', $setting->meta_robots ?? 'index, follow') }}"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors"/>
                        </div>
                        <div class="md:col-span-2">
                            <label for="meta_description" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Meta Description</label>
                            <textarea id="meta_description" name="meta_description" rows="3"
                                      class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors resize-none">{{ old('meta_description', $setting->meta_description) }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label for="meta_keywords" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Meta Keywords</label>
                            <textarea id="meta_keywords" name="meta_keywords" rows="2"
                                      placeholder="ecommerce, saas, store builder"
                                      class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors resize-none">{{ old('meta_keywords', $setting->meta_keywords) }}</textarea>
                        </div>
                        <div>
                            <label for="canonical_url" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Canonical URL</label>
                            <input type="url" id="canonical_url" name="canonical_url"
                                   value="{{ old('canonical_url', $setting->canonical_url) }}"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors"/>
                        </div>
                        <div class="md:col-span-2">
                            <label for="schema_markup" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Schema Markup (JSON-LD)</label>
                            <textarea id="schema_markup" name="schema_markup" rows="4"
                                      placeholder="<script type='application/ld+json'>...</script>"
                                      class="w-full px-3.5 py-2.5 text-sm font-mono border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors resize-none">{{ old('schema_markup', $setting->schema_markup) }}</textarea>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-800 my-6"></div>

                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-7 h-7 rounded-lg bg-primarylt dark:bg-primary/15 grid place-items-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        </div>
                        <h5 class="text-sm font-bold text-ink dark:text-white">Open Graph (Social Sharing)</h5>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="og_title" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">OG Title</label>
                            <input type="text" id="og_title" name="og_title"
                                   value="{{ old('og_title', $setting->og_title) }}"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors"/>
                        </div>
                        <div>
                            <label for="og_image" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">OG Image</label>
                            <input class="block w-full text-xs text-slate-500 dark:text-slate-400
                                      file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                      file:text-xs file:font-semibold
                                      file:bg-primarylt file:text-primary
                                      dark:file:bg-primary/10 dark:file:text-primary
                                      hover:file:bg-primarylt/80 cursor-pointer transition-colors
                                      border border-slate-200 dark:border-slate-700 rounded-xl py-2"
                                   type="file" id="og_image" name="og_image" accept="image/*"
                                   onchange="previewImg(this, 'ogImgPrev')"/>
                            <div class="mt-2">
                                <img id="ogImgPrev"
                                     src="{{ $setting->og_image ? asset('storage/' . $setting->og_image) : '' }}"
                                     class="max-h-20 rounded-xl border border-slate-200 dark:border-slate-700 object-cover {{ $setting->og_image ? '' : 'hidden' }}"
                                     alt="OG Image"/>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label for="og_description" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">OG Description</label>
                            <textarea id="og_description" name="og_description" rows="3"
                                      class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors resize-none">{{ old('og_description', $setting->og_description) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ════════════════════════════════════
                     3. SOCIAL LINKS
                ════════════════════════════════════ --}}
                <div id="tab-social" class="tab-panel hidden">

                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-7 h-7 rounded-lg bg-primarylt dark:bg-primary/15 grid place-items-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        </div>
                        <h5 class="text-sm font-bold text-ink dark:text-white">Social Media Profiles</h5>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        {{-- Facebook --}}
                        <div>
                            <label for="fb_link" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded bg-[#1877F2] text-white grid place-items-center text-[10px] font-bold">f</span>
                                Facebook
                            </span>
                            </label>
                            <input type="url" id="fb_link" name="fb_link"
                                   value="{{ old('fb_link', $setting->fb_link) }}"
                                   placeholder="https://facebook.com/yourpage"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors"/>
                        </div>

                        {{-- X / Twitter --}}
                        <div>
                            <label for="x_link" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded bg-black text-white grid place-items-center font-bold" style="font-size:10px">𝕏</span>
                                X (Twitter)
                            </span>
                            </label>
                            <input type="url" id="x_link" name="x_link"
                                   value="{{ old('x_link', $setting->x_link) }}"
                                   placeholder="https://x.com/yourhandle"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors"/>
                        </div>

                        {{-- YouTube --}}
                        <div>
                            <label for="youtube_link" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded bg-[#FF0000] text-white grid place-items-center" style="font-size:9px">▶</span>
                                YouTube
                            </span>
                            </label>
                            <input type="url" id="youtube_link" name="youtube_link"
                                   value="{{ old('youtube_link', $setting->youtube_link) }}"
                                   placeholder="https://youtube.com/c/yourchannel"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors"/>
                        </div>

                        {{-- Instagram --}}
                        <div>
                            <label for="insta_link" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded bg-gradient-to-br from-[#f09433] via-[#e6683c] to-[#bc1888] text-white grid place-items-center" style="font-size:10px">✦</span>
                                Instagram
                            </span>
                            </label>
                            <input type="url" id="insta_link" name="insta_link"
                                   value="{{ old('insta_link', $setting->insta_link) }}"
                                   placeholder="https://instagram.com/yourprofile"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors"/>
                        </div>

                    </div>
                </div>

                {{-- ════════════════════════════════════
                     4. ANALYTICS & SCRIPTS
                ════════════════════════════════════ --}}
                <div id="tab-scripts" class="tab-panel hidden">

                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-7 h-7 rounded-lg bg-primarylt dark:bg-primary/15 grid place-items-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                        </div>
                        <h5 class="text-sm font-bold text-ink dark:text-white">Tracking & Custom Scripts</h5>
                    </div>

                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-5 leading-relaxed">
                        Paste your tracking code snippets below. Each script will be injected into the appropriate position in your page's HTML.
                    </p>

                    <div class="space-y-5">

                        {{-- Facebook Pixel --}}
                        <div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden">
                            <div class="flex items-center gap-2.5 px-4 py-3 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                                <span class="w-5 h-5 rounded bg-[#1877F2] text-white grid place-items-center text-[10px] font-bold shrink-0">f</span>
                                <label for="fb_pixel" class="text-sm font-semibold text-slate-700 dark:text-slate-300">Facebook Pixel Code</label>
                            </div>
                            <div class="p-4">
                            <textarea id="fb_pixel" name="fb_pixel" rows="4"
                                      placeholder="<!-- Meta Pixel Code -->"
                                      class="w-full px-3.5 py-2.5 text-xs font-mono border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors resize-none">{{ old('fb_pixel', $setting->fb_pixel) }}</textarea>
                            </div>
                        </div>

                        {{-- Google Analytics --}}
                        <div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden">
                            <div class="flex items-center gap-2.5 px-4 py-3 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                                <span class="w-5 h-5 rounded bg-[#E37400] text-white grid place-items-center text-[10px] font-bold shrink-0">G</span>
                                <label for="google_analytics" class="text-sm font-semibold text-slate-700 dark:text-slate-300">Google Analytics Script</label>
                            </div>
                            <div class="p-4">
                            <textarea id="google_analytics" name="google_analytics" rows="4"
                                      placeholder="<!-- Global site tag (gtag.js) -->"
                                      class="w-full px-3.5 py-2.5 text-xs font-mono border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors resize-none">{{ old('google_analytics', $setting->google_analytics) }}</textarea>
                            </div>
                        </div>

                        {{-- Live Chat --}}
                        <div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden">
                            <div class="flex items-center gap-2.5 px-4 py-3 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                            <span class="w-5 h-5 rounded bg-accent text-white grid place-items-center shrink-0">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            </span>
                                <label for="chatbox_script" class="text-sm font-semibold text-slate-700 dark:text-slate-300">Live Chat / Support Widget Script</label>
                            </div>
                            <div class="p-4">
                            <textarea id="chatbox_script" name="chatbox_script" rows="4"
                                      placeholder="<!-- Live Chat Script -->"
                                      class="w-full px-3.5 py-2.5 text-xs font-mono border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors resize-none">{{ old('chatbox_script', $setting->chatbox_script) }}</textarea>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ════════════════════════════════════
                     5. ANNOUNCEMENT BAR
                ════════════════════════════════════ --}}
                <div id="tab-announcement" class="tab-panel hidden">

                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-7 h-7 rounded-lg bg-primarylt dark:bg-primary/15 grid place-items-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        </div>
                        <h5 class="text-sm font-bold text-ink dark:text-white">Top Banner Announcement</h5>
                    </div>

                    <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 rounded-2xl px-4 py-3 flex items-start gap-3 mb-5 text-sm text-amber-700 dark:text-amber-400">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span>This text will be displayed in the announcement bar across all landing pages. Keep it short and impactful.</span>
                    </div>

                    <div>
                        <label for="announcement_bar_text" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Announcement Bar Text</label>
                        <textarea id="announcement_bar_text" name="announcement_bar_text" rows="4"
                                  placeholder="🎉 Special Offer: 20% off all annual plans! Use code GENZ20"
                                  class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 text-ink dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-colors resize-none">{{ old('announcement_bar_text', $setting->announcement_bar_text) }}</textarea>
                        <p class="mt-1.5 text-xs text-slate-400">Supports emoji. Keep it concise — under 120 characters recommended.</p>
                    </div>

                </div>

            </div>{{-- /tab panels --}}

            {{-- Card Footer --}}
            <div class="px-6 md:px-8 py-4 bg-slate-50/60 dark:bg-slate-800/30 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-4">
                <p class="text-xs text-slate-400 hidden sm:block">All changes are saved globally across the platform.</p>
                <button type="submit"
                        class="flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primarydark
                           text-white text-sm font-semibold rounded-xl transition-colors
                           shadow-sm shadow-primary/30 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Platform Settings
                </button>
            </div>

        </div>{{-- /card --}}
    </form>

    <style>
        .settings-tab {
            color: #64748b;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
        }
        .dark .settings-tab {
            color: #94a3b8;
        }
        .settings-tab:hover {
            color: #4f46e5;
            background-color: rgba(79, 70, 229, 0.05);
        }
        .settings-tab.active {
            color: #4f46e5;
            border-bottom-color: #4f46e5;
            background-color: rgba(79, 70, 229, 0.06);
        }
        .dark .settings-tab.active {
            color: #818cf8;
            border-bottom-color: #818cf8;
        }
    </style>

    <script>
        /* ── Tab switching ── */
        (function () {
            var tabs    = document.querySelectorAll('.settings-tab');
            var panels  = document.querySelectorAll('.tab-panel');

            tabs.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var target = btn.getAttribute('data-tab');

                    tabs.forEach(function (t) { t.classList.remove('active'); });
                    panels.forEach(function (p) { p.classList.add('hidden'); });

                    btn.classList.add('active');
                    document.getElementById('tab-' + target).classList.remove('hidden');
                });
            });
        })();

        /* ── Image preview ── */
        function previewImg(input, targetId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var el = document.getElementById(targetId);
                    el.src = e.target.result;
                    el.style.display = 'inline-block';
                    el.classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

@endsection
