@extends('platform_admin.layout.master')

@section('contents')

    {{-- Page heading row --}}
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl md:text-2xl font-extrabold text-ink dark:text-white">Platform Hero Section</h1>
            <nav class="flex items-center gap-1.5 mt-1 text-xs text-slate-400">
                <a href="{{ route('admin.dashboard.index') }}" class="hover:text-primary transition-colors">Admin</a>
                <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                <span class="text-slate-500 dark:text-slate-300 font-medium">Hero Section</span>
            </nav>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.hero.update') }}">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">

            {{-- Card header --}}
            <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                <div class="w-8 h-8 bg-primarylt dark:bg-primary/15 rounded-lg grid place-items-center shrink-0">
                    <svg class="w-4 h-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 6h16M4 10h16M4 14h10"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink dark:text-white">Manage Hero Banner Content</p>
                    <p class="text-xs text-slate-400 mt-0.5">Changes apply to the public storefront hero section</p>
                </div>
            </div>

            {{-- Card body --}}
            <div class="p-6 space-y-5">

                {{-- Hero Title --}}
                <div class="space-y-1.5">
                    <label for="title" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">
                        Hero Title
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title', $hero->title) }}"
                        required
                        placeholder="e.g. Build, Launch, & Scale Your Global Store"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl
                 bg-slate-50 dark:bg-slate-800
                 border border-slate-200 dark:border-slate-700
                 text-ink dark:text-slate-100 placeholder:text-slate-400
                 focus:outline-none focus:border-primary dark:focus:border-primary focus:ring-1 focus:ring-primary/20
                 transition-colors"
                    />
                </div>

                {{-- AI Badge / Subheading --}}
                <div class="space-y-1.5">
                    <label for="ai_product_desc" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">
                        AI Product Badge / Subheading
                    </label>
                    <textarea
                        id="ai_product_desc"
                        name="ai_product_desc"
                        rows="2"
                        placeholder="e.g. Generate high-converting storefronts instantly using AI."
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl resize-none
                 bg-slate-50 dark:bg-slate-800
                 border border-slate-200 dark:border-slate-700
                 text-ink dark:text-slate-100 placeholder:text-slate-400
                 focus:outline-none focus:border-primary dark:focus:border-primary focus:ring-1 focus:ring-primary/20
                 transition-colors"
                    >{{ old('ai_product_desc', $hero->ai_product_desc) }}</textarea>
                </div>

                {{-- Short Description --}}
                <div class="space-y-1.5">
                    <label for="short_desc" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">
                        Short Description
                    </label>
                    <textarea
                        id="short_desc"
                        name="short_desc"
                        rows="4"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl resize-none
                 bg-slate-50 dark:bg-slate-800
                 border border-slate-200 dark:border-slate-700
                 text-ink dark:text-slate-100 placeholder:text-slate-400
                 focus:outline-none focus:border-primary dark:focus:border-primary focus:ring-1 focus:ring-primary/20
                 transition-colors"
                    >{{ old('short_desc', $hero->short_desc) }}</textarea>
                </div>

                {{-- Divider --}}
                <div class="border-t border-slate-100 dark:border-slate-800 pt-1">
                    <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-4">Call-to-Action Buttons</p>
                </div>

                {{-- Buttons row --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Primary button --}}
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-100 dark:border-slate-800 space-y-3">
                        <p class="text-xs font-semibold text-ink dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primary inline-block"></span>
                            Primary Button
                        </p>
                        <div class="space-y-1.5">
                            <label for="btn_1_text" class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Button Text</label>
                            <input
                                type="text"
                                id="btn_1_text"
                                name="btn_1_text"
                                value="{{ old('btn_1_text', $hero->btn_1_text) }}"
                                placeholder="Start Free Trial"
                                class="w-full px-3 py-2 text-sm rounded-lg
                     bg-white dark:bg-slate-800
                     border border-slate-200 dark:border-slate-700
                     text-ink dark:text-slate-100 placeholder:text-slate-400
                     focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20
                     transition-colors"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label for="btn_1_link" class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Button Link</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                                <input
                                    type="text"
                                    id="btn_1_link"
                                    name="btn_1_link"
                                    value="{{ old('btn_1_link', $hero->btn_1_link) }}"
                                    placeholder="/register"
                                    class="w-full pl-8 pr-3 py-2 text-sm rounded-lg
                       bg-white dark:bg-slate-800
                       border border-slate-200 dark:border-slate-700
                       text-ink dark:text-slate-100 placeholder:text-slate-400
                       focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20
                       transition-colors font-mono"
                                />
                            </div>
                        </div>
                    </div>

                    {{-- Secondary button --}}
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-100 dark:border-slate-800 space-y-3">
                        <p class="text-xs font-semibold text-ink dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-400 inline-block"></span>
                            Secondary Button
                        </p>
                        <div class="space-y-1.5">
                            <label for="btn_2_text" class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Button Text</label>
                            <input
                                type="text"
                                id="btn_2_text"
                                name="btn_2_text"
                                value="{{ old('btn_2_text', $hero->btn_2_text) }}"
                                placeholder="Explore Demos"
                                class="w-full px-3 py-2 text-sm rounded-lg
                     bg-white dark:bg-slate-800
                     border border-slate-200 dark:border-slate-700
                     text-ink dark:text-slate-100 placeholder:text-slate-400
                     focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20
                     transition-colors"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label for="btn_2_link" class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Button Link</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                                <input
                                    type="text"
                                    id="btn_2_link"
                                    name="btn_2_link"
                                    value="{{ old('btn_2_link', $hero->btn_2_link) }}"
                                    placeholder="#demos"
                                    class="w-full pl-8 pr-3 py-2 text-sm rounded-lg
                       bg-white dark:bg-slate-800
                       border border-slate-200 dark:border-slate-700
                       text-ink dark:text-slate-100 placeholder:text-slate-400
                       focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20
                       transition-colors font-mono"
                                />
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Card footer --}}
            <div class="flex items-center justify-between gap-4 px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <p class="text-xs text-slate-400 hidden sm:block">
                    Changes will reflect on the public storefront immediately after saving.
                </p>
                <button
                    type="submit"
                    class="flex items-center gap-2 px-5 py-2.5
               bg-primary hover:bg-primarydark text-white
               text-sm font-semibold rounded-xl
               shadow-sm shadow-primary/30
               transition-colors shrink-0"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Save Hero Changes
                </button>
            </div>

        </div>
    </form>

@endsection
