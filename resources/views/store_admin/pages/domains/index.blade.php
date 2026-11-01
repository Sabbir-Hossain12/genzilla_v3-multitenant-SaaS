@extends('store_admin.layout.master')

@section('contents')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Custom Domains & Branding</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Connect your own custom branded domain (e.g., www.greenpharma.com) so customers shop under your brand identity.</p>
        </div>
        <div>
            @if($allowsCustomDomain)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs font-semibold border border-emerald-200 dark:border-emerald-800">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span>Custom Domains Active on {{ $currentPlan->name ?? 'Current' }} Plan</span>
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 text-xs font-semibold border border-amber-200 dark:border-amber-800">
                    <i class="fa-solid fa-lock text-amber-500"></i>
                    <span>Feature Restricted on {{ $currentPlan->name ?? 'Basic' }} Plan</span>
                </span>
            @endif
        </div>
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-300">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl bg-red-50 p-4 text-sm font-medium text-red-800 dark:bg-red-950/40 dark:text-red-200 border border-red-200 dark:border-red-800 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-red-600 dark:text-red-400"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 dark:text-red-300">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 rounded-2xl px-4 py-3.5 text-sm shadow-sm">
            <i class="fa-solid fa-circle-exclamation text-base mt-0.5 shrink-0"></i>
            <ul class="flex-1 space-y-0.5 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Plan Gatekeeping Banner if Disallowed --}}
    @if(!$allowsCustomDomain)
        <div class="rounded-3xl bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 p-6 md:p-8 text-white shadow-lg relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative z-10 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-indigo-200 text-xs font-semibold mb-3">
                    <i class="fa-solid fa-sparkles"></i> Pro Tier Feature
                </div>
                <h2 class="text-xl md:text-2xl font-extrabold text-white">Upgrade to Connect Your Custom Domain</h2>
                <p class="text-sm text-indigo-100/90 mt-2 leading-relaxed">
                    Custom domain connection (e.g., <code class="bg-black/30 px-1.5 py-0.5 rounded text-amber-300">yourpharmacy.com</code>) is available on our Pro and Enterprise plans. Your store is currently running on the free system subdomain.
                </p>
                <div class="mt-5 flex items-center gap-3">
                    <a href="{{ route('store.admin.subscription.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-indigo-900 hover:bg-indigo-50 font-bold text-xs shadow-md transition">
                        <span>View Plans & Upgrade</span>
                        <i class="fa-solid fa-arrow-right text-[10px] cursor-pointer"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- DNS Setup Instructions Card --}}
    <div class="card overflow-hidden">
        <div class="p-6 md:p-7 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-bold text-xs uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-network-wired"></i>
                        <span>DNS Configuration Guide</span>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Point Your Domain to Genzilla Servers</h2>
                    <p class="text-xs text-slate-400 mt-1 max-w-2xl">
                        Log into your domain registrar (Namecheap, GoDaddy, Cloudflare, Hostinger, etc.) and add the following DNS records:
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                {{-- A Record --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 p-4 relative">
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">
                            A Record (Root Domain)
                        </span>
                        <button type="button" onclick="copyText('{{ $serverIp }}', 'A Record IP copied!')"
                                class="text-xs text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1 font-medium transition">
                            <i class="fa-solid fa-copy"></i> Copy
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs font-mono mt-3">
                        <div>
                            <span class="text-slate-400 block text-[10px] font-sans">Host / Name</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">@ (or leave empty)</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] font-sans">Required IP Address</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 font-mono">{{ $serverIp }}</span>
                        </div>
                    </div>
                </div>

                {{-- CNAME Record --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 p-4 relative">
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-cyan-100 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300">
                            CNAME Record (Subdomain / WWW)
                        </span>
                        <button type="button" onclick="copyText('{{ $cnameTarget }}', 'CNAME Target copied!')"
                                class="text-xs text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1 font-medium transition">
                            <i class="fa-solid fa-copy"></i> Copy
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs font-mono mt-3">
                        <div>
                            <span class="text-slate-400 block text-[10px] font-sans">Host / Subdomain</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">www</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] font-sans">Points to Target</span>
                            <span class="font-bold text-cyan-600 dark:text-cyan-400 font-mono">{{ $cnameTarget }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Add Domain Form --}}
        <div class="p-6 md:p-7 bg-slate-50/50 dark:bg-slate-800/20">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-1">Add a Custom Domain</h3>
            <p class="text-xs text-slate-400 mb-4">Enter your registered domain name to associate it with this store.</p>

            <form action="{{ route('store.admin.domains.store') }}" method="POST" class="max-w-xl">
                @csrf
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                            <i class="fa-solid fa-globe"></i>
                        </div>
                        <input type="text" name="domain" required placeholder="e.g. greenpharma.com or shop.brand.com"
                               {{ !$allowsCustomDomain ? 'disabled' : '' }}
                               value="{{ old('domain') }}"
                               class="w-full pl-9 pr-3.5 py-2.5 text-sm font-mono border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 {{ !$allowsCustomDomain ? 'opacity-60 cursor-not-allowed' : '' }}">
                    </div>
                    <button type="submit"
                            {{ !$allowsCustomDomain ? 'disabled' : '' }}
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition {{ !$allowsCustomDomain ? 'opacity-50 cursor-not-allowed' : '' }}">
                        <i class="fa-solid fa-plus text-[11px]"></i>
                        <span>Connect Domain</span>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 mt-2">Enter your domain without <code class="text-indigo-600">https://</code> or trailing slashes.</p>
            </form>
        </div>
    </div>

    {{-- Connected Domains List --}}
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-list-check text-slate-400"></i>
                <h2 class="font-bold text-slate-900 dark:text-white">Store Domains</h2>
                <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 text-xs font-semibold text-slate-600 dark:text-slate-400">
                    {{ $domains->count() }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Domain Name</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-36">Type</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-44">DNS Status</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Primary Routing</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-48">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($domains as $domain)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition-colors">
                        {{-- Domain Name & SSL --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-slate-900 dark:text-white text-sm">{{ $domain->domain }}</span>
                                <a href="https://{{ $domain->domain }}" target="_blank" class="text-slate-400 hover:text-indigo-600 transition" title="Open storefront">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                                </a>
                            </div>
                            <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400">
                                <span>SSL:</span>
                                <span class="font-medium {{ $domain->ssl_status === 'active' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-500' }}">
                                    <i class="fa-solid fa-lock text-[9px] mr-0.5"></i> {{ ucfirst($domain->ssl_status) }}
                                </span>
                            </div>
                        </td>

                        {{-- Domain Type --}}
                        <td class="px-5 py-4">
                            @if($domain->is_custom)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300">
                                    <i class="fa-solid fa-globe text-[10px]"></i> Custom
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    <i class="fa-solid fa-server text-[10px]"></i> System Default
                                </span>
                            @endif
                        </td>

                        {{-- DNS Verification Status --}}
                        <td class="px-5 py-4 text-center">
                            @if(!$domain->is_custom)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>System Active</span>
                                </span>
                            @elseif($domain->dns_verified)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Connected & Active</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    <span>Pending DNS Setup</span>
                                </span>
                            @endif
                        </td>

                        {{-- Primary Flag --}}
                        <td class="px-5 py-4 text-center">
                            @if($domain->is_primary)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-600 text-white shadow-sm">
                                    <i class="fa-solid fa-star text-[9px]"></i> Primary
                                </span>
                            @else
                                <span class="text-xs text-slate-400 font-medium">Secondary</span>
                            @endif
                        </td>

                        {{-- Action Buttons --}}
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- Verify DNS button --}}
                                @if($domain->is_custom && !$domain->dns_verified)
                                    @can('Store Domain Edit')
                                        <form action="{{ route('store.admin.domains.verify', $domain) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition"
                                                    title="Check DNS records propagation">
                                                <i class="fa-solid fa-rotate text-[10px]"></i>
                                                <span>Verify DNS</span>
                                            </button>
                                        </form>
                                    @endcan
                                @endif

                                {{-- Make Primary Button --}}
                                @if(!$domain->is_primary && (!$domain->is_custom || $domain->dns_verified))
                                    @can('Store Domain Edit')
                                        <form action="{{ route('store.admin.domains.primary', $domain) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition"
                                                    title="Set as main primary storefront address">
                                                <i class="fa-regular fa-star text-[10px]"></i>
                                                <span>Set Primary</span>
                                            </button>
                                        </form>
                                    @endcan
                                @endif

                                {{-- Delete Domain (Custom only) --}}
                                @if($domain->is_custom)
                                    @can('Store Domain Delete')
                                        <form action="{{ route('store.admin.domains.destroy', $domain) }}" method="POST"
                                              onsubmit="return confirm('Are you sure you want to disconnect domain {{ $domain->domain }}?')"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="h-7 w-7 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 transition"
                                                    title="Remove Domain">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function copyText(text, message) {
        navigator.clipboard.writeText(text).then(function() {
            alert(message);
        });
    }
</script>
@endsection
