@extends('store_admin.layout.master')

@section('contents')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-ink dark:text-white">Store Settings</h1>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Manage your storefront profile, search metadata, pharmacy rules, and integrations.</p>
        </div>
        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $tenant->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' }}">{{ ucfirst($tenant->status) }}</span>
    </div>

    @if ($errors->any())
        <div id="storeSettingsErrors" class="mb-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
            <ul class="flex-1 list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" onclick="this.closest('#storeSettingsErrors').remove()\" class="text-red-400 transition-colors hover:text-red-600" aria-label="Dismiss errors">&times;</button>
        </div>
    @endif

    <form method="POST" action="{{ route('store.admin.settings.update') }}" enctype="multipart/form-data" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @csrf
        @method('PUT')

        <div class="overflow-x-auto border-b border-slate-200 bg-slate-50/60 px-4 pt-4 dark:border-slate-800 dark:bg-slate-800/40">
            <div class="flex min-w-max gap-1" role="tablist" aria-label="Store settings sections">
                <button type="button" role="tab" aria-selected="true" data-tab="general" class="store-settings-tab active rounded-t-xl border-b-2 border-transparent px-4 py-2.5 text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-store mr-2"></i>General Settings
                </button>
                <button type="button" role="tab" aria-selected="false" data-tab="pharmacy" class="store-settings-tab rounded-t-xl border-b-2 border-transparent px-4 py-2.5 text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-file-prescription mr-2"></i>Pharmacy & Prescriptions
                </button>
                <button type="button" role="tab" aria-selected="false" data-tab="seo" class="store-settings-tab rounded-t-xl border-b-2 border-transparent px-4 py-2.5 text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-magnifying-glass mr-2"></i>SEO & Metadata
                </button>
                <button type="button" role="tab" aria-selected="false" data-tab="integrations" class="store-settings-tab rounded-t-xl border-b-2 border-transparent px-4 py-2.5 text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-plug mr-2"></i>Analytics & Scripts
                </button>
            </div>
        </div>

        <div class="p-6 md:p-8">
            <section id="store-tab-general" class="store-settings-panel space-y-8" role="tabpanel">
                <div>
                    <div class="mb-5 flex items-center gap-2">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300"><i class="fa-solid fa-id-card text-xs"></i></span>
                        <div><h2 class="text-sm font-bold text-ink dark:text-white">Store profile</h2><p class="text-xs text-slate-500 dark:text-slate-400">Public name and customer support contact details.</p></div>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 md:col-span-2">Store name
                            <input name="store_name" required maxlength="255" value="{{ old('store_name', $tenant->store_name) }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:placeholder:text-slate-400">
                        </label>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Support email
                            <input type="email" name="support_email" value="{{ old('support_email', $tenant->support_email) }}" placeholder="help@example.com" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:placeholder:text-slate-400">
                        </label>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Support phone
                            <input name="support_phone" value="{{ old('support_phone', $tenant->support_phone) }}" placeholder="+1 555 0100" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:placeholder:text-slate-400">
                        </label>
                        <div class="md:col-span-2">
                            <label for="logo" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Store logo</label>
                            <div class="flex flex-wrap items-center gap-4">
                                @if ($tenant->logo_path)
                                    <img src="{{ asset('storage/'.$tenant->logo_path) }}" alt="Current store logo" class="h-16 max-w-48 rounded-lg border border-slate-200 bg-white object-contain p-2 dark:border-slate-700 dark:bg-slate-800">
                                @else
                                    <div class="grid h-16 w-24 place-items-center rounded-lg border border-dashed border-slate-300 text-slate-400 dark:border-slate-700"><i class="fa-regular fa-image"></i></div>
                                @endif
                                <div class="min-w-56 flex-1">
                                    <input id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 dark:text-slate-400 dark:file:bg-indigo-500/15 dark:file:text-indigo-300">
                                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">PNG, JPG, or WebP, up to 2 MB.</p>
                                    @if ($tenant->logo_path)
                                        <label class="mt-2 inline-flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400"><input type="checkbox" name="remove_logo" value="1"> Remove current logo</label>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-7 dark:border-slate-800">
                    <div class="mb-5 flex items-center gap-2">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300"><i class="fa-solid fa-globe text-xs"></i></span>
                        <div><h2 class="text-sm font-bold text-ink dark:text-white">Localization</h2><p class="text-xs text-slate-500 dark:text-slate-400">Currency and regional time zone preferences.</p></div>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Currency code
                            <input name="currency" required maxlength="3" value="{{ old('currency', $tenant->currency) }}" placeholder="USD" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm uppercase text-ink placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:placeholder:text-slate-400">
                            <span class="mt-1 block text-xs font-normal text-slate-400">3-letter ISO code (e.g. USD, BDT, EUR).</span>
                        </label>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Timezone
                            <select name="timezone" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-ink focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200">
                                @foreach (timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}" @selected(old('timezone', $tenant->timezone) === $tz)>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
            </section>

            {{-- Pharmacy & Prescriptions Panel --}}
            <section id="store-tab-pharmacy" class="store-settings-panel hidden space-y-8" role="tabpanel" aria-hidden="true">
                <div>
                    <div class="mb-5 flex items-center gap-2">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300"><i class="fa-solid fa-file-prescription text-xs"></i></span>
                        <div>
                            <h2 class="text-sm font-bold text-ink dark:text-white">Rx Compliance & Checkout Rules</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Configure regulatory compliance strictness and prescription order workflows.</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <span class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Cart Checkout Rx Policy</span>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="relative flex cursor-pointer flex-col rounded-2xl border p-4 transition-all focus:outline-none {{ old('rx_compliance_mode', $tenant->rx_compliance_mode ?? 'flexible') === 'flexible' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-500/20 dark:border-indigo-500 dark:bg-indigo-950/20' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600' }}">
                                    <input type="radio" name="rx_compliance_mode" value="flexible" class="sr-only" @checked(old('rx_compliance_mode', $tenant->rx_compliance_mode ?? 'flexible') === 'flexible')>
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="grid h-6 w-6 place-items-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300"><i class="fa-solid fa-headset text-xs"></i></span>
                                            <span class="font-bold text-sm text-slate-800 dark:text-slate-200">Flexible / Tele-Sales Mode</span>
                                        </div>
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Standard</span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                        Customers can place orders even if they haven't uploaded an Rx photo. The order is automatically tagged with <strong>Rx Missing</strong>, allowing your call center or pharmacist team to verify via phone or WhatsApp before dispatch.
                                    </p>
                                </label>

                                <label class="relative flex cursor-pointer flex-col rounded-2xl border p-4 transition-all focus:outline-none {{ old('rx_compliance_mode', $tenant->rx_compliance_mode) === 'strict' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-500/20 dark:border-indigo-500 dark:bg-indigo-950/20' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600' }}">
                                    <input type="radio" name="rx_compliance_mode" value="strict" class="sr-only" @checked(old('rx_compliance_mode', $tenant->rx_compliance_mode) === 'strict')>
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="grid h-6 w-6 place-items-center rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300"><i class="fa-solid fa-shield-halved text-xs"></i></span>
                                            <span class="font-bold text-sm text-slate-800 dark:text-slate-200">Strict Compliance Mode</span>
                                        </div>
                                        <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">Strict</span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                        If any cart item is flagged as <em>Prescription Required</em>, the checkout page completely blocks "Place Order" until the customer attaches a valid prescription photo.
                                    </p>
                                </label>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-6 dark:border-slate-800">
                            <span class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Direct Upload Feature</span>
                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600 cursor-pointer">
                                <input type="checkbox" name="enable_direct_prescriptions" value="1" @checked(old('enable_direct_prescriptions', $tenant->enable_direct_prescriptions ?? true)) class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800">
                                <div class="text-xs">
                                    <span class="font-bold text-slate-800 dark:text-slate-200 block text-sm">Enable Direct Prescription Uploads (Order Request Inbox)</span>
                                    <span class="text-slate-500 dark:text-slate-400 mt-0.5 block leading-relaxed">
                                        Allows storefront visitors to upload an image of doctor's handwritten slip with delivery details without manually selecting products. Requests land in your Store Admin under <strong>Prescriptions (Pending Review)</strong> for pharmacists to assemble the cart and create orders.
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </section>

            <section id="store-tab-seo" class="store-settings-panel hidden space-y-6" role="tabpanel" aria-hidden="true">
                <div class="mb-5 flex items-center gap-2">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300"><i class="fa-solid fa-magnifying-glass text-xs"></i></span>
                    <div><h2 class="text-sm font-bold text-ink dark:text-white">Search engine optimization</h2><p class="text-xs text-slate-500 dark:text-slate-400">Metadata and instructions for search engines crawling your storefront.</p></div>
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 md:col-span-2">Meta title
                        <input name="meta_title" maxlength="255" value="{{ old('meta_title', $tenant->meta_title) }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:placeholder:text-slate-400">
                    </label>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 md:col-span-2">Meta description
                        <textarea name="meta_description" rows="3" class="mt-1.5 w-full resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:placeholder:text-slate-400">{{ old('meta_description', $tenant->meta_description) }}</textarea>
                    </label>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 md:col-span-2">Meta keywords
                        <textarea name="meta_keywords" rows="2" placeholder="Separate keywords with commas" class="mt-1.5 w-full resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:placeholder:text-slate-400">{{ old('meta_keywords', $tenant->meta_keywords) }}</textarea>
                    </label>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Robots directive
                        <select name="meta_robots" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-ink focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200">
                            @foreach (['index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow'] as $robots)
                                <option value="{{ $robots }}" @selected(old('meta_robots', $tenant->meta_robots) === $robots)>{{ $robots }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Canonical URL
                        <input type="url" name="canonical_url" value="{{ old('canonical_url', $tenant->canonical_url) }}" placeholder="https://example.com" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:placeholder:text-slate-400">
                    </label>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 md:col-span-2">Structured data (JSON)
                        <textarea name="schema_markup" rows="5" placeholder='{"@@context":"https://schema.org","@@type":"Store"}' class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 font-mono text-xs text-ink placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:placeholder:text-slate-400">{{ old('schema_markup', $tenant->schema_markup) }}</textarea>
                    </label>
                </div>
            </section>

            <section id="store-tab-integrations" class="store-settings-panel hidden space-y-6" role="tabpanel" aria-hidden="true">
                <div class="mb-5 flex items-center gap-2">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300"><i class="fa-solid fa-plug text-xs"></i></span>
                    <div><h2 class="text-sm font-bold text-ink dark:text-white">Analytics and integrations</h2><p class="text-xs text-slate-500 dark:text-slate-400">Connect your storefront to analytics services.</p></div>
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Meta Pixel ID
                        <input name="fb_pixel_id" value="{{ old('fb_pixel_id', $tenant->fb_pixel_id) }}" placeholder="123456789012345" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:placeholder:text-slate-400">
                    </label>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Google Analytics ID
                        <input name="google_analytics_id" value="{{ old('google_analytics_id', $tenant->google_analytics_id) }}" placeholder="G-XXXXXXXXXX" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:placeholder:text-slate-400">
                    </label>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 md:col-span-2">Google Tag Manager ID
                        <input name="gtm_id" value="{{ old('gtm_id', $tenant->gtm_id) }}" placeholder="GTM-XXXXXXX" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:placeholder:text-slate-400">
                    </label>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 md:col-span-2">Custom head script
                        <textarea name="custom_head_script" rows="6" placeholder="Paste trusted provider scripts here" class="mt-1.5 w-full resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 font-mono text-xs text-ink placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:placeholder:text-slate-400">{{ old('custom_head_script', $tenant->custom_head_script) }}</textarea>
                        <span class="mt-1 block text-xs font-normal text-amber-700 dark:text-amber-300">Only add scripts from providers you trust.</span>
                    </label>
                </div>
            </section>
        </div>

        <div class="flex items-center justify-between gap-4 border-t border-slate-200 bg-slate-50/60 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/30 md:px-8">
            <p class="hidden text-xs text-slate-400 dark:text-slate-500 sm:block">Settings apply to this storefront.</p>
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                <i class="fa-solid fa-floppy-disk text-xs"></i>Save Store Settings
            </button>
        </div>
    </form>

    <style>
        .store-settings-tab { color: #64748b; margin-bottom: -1px; }
        .dark .store-settings-tab { color: #94a3b8; }
        .store-settings-tab:hover { color: #4f46e5; background-color: rgba(79, 70, 229, .05); }
        .store-settings-tab.active { color: #4f46e5; border-bottom-color: #4f46e5; background-color: rgba(79, 70, 229, .06); }
        .dark .store-settings-tab.active { color: #818cf8; border-bottom-color: #818cf8; }
    </style>
    <script>
        (function () {
            var tabs = document.querySelectorAll('.store-settings-tab');
            var panels = document.querySelectorAll('.store-settings-panel');
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var target = tab.getAttribute('data-tab');
                    tabs.forEach(function (item) {
                        var selected = item === tab;
                        item.classList.toggle('active', selected);
                        item.setAttribute('aria-selected', selected ? 'true' : 'false');
                    });
                    panels.forEach(function (panel) {
                        var selected = panel.id === 'store-tab-' + target;
                        panel.classList.toggle('hidden', !selected);
                        panel.setAttribute('aria-hidden', selected ? 'false' : 'true');
                    });
                });
            });
        })();
    </script>
@endsection
