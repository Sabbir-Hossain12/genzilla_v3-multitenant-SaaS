@extends('store_admin.layout.master')

@section('contents')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Payment Methods & Gateways</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Configure Cash on Delivery, manual MFS (bKash/Nagad/Rocket), and automated API gateways (SSLCommerz, Stripe).</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs font-semibold border border-emerald-200 dark:border-emerald-800">
                <i class="fa-solid fa-shield-halved text-emerald-500"></i>
                <span>Credentials Encrypted via AES-256</span>
            </span>
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

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                <i class="fa-solid fa-wallet text-base"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Total Methods</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats['total'] ?? 0 }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                <i class="fa-solid fa-toggle-on text-base"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Active Online</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats['active'] ?? 0 }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400">
                <i class="fa-solid fa-mobile-screen-button text-base"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Active MFS / Manual</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats['manual'] ?? 0 }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 dark:bg-cyan-950/50 dark:text-cyan-400">
                <i class="fa-solid fa-bolt text-base"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Active Gateways</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats['automated'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    {{-- Tier Filter Pills --}}
    @php
        $currentType = request('type');
    @endphp
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
        <a href="{{ route('store.admin.payments.index') }}"
           class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ empty($currentType) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300' }}">
            All Options ({{ $methods->count() }})
        </a>
        <a href="{{ route('store.admin.payments.index', ['type' => 'cash']) }}"
           class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ $currentType === 'cash' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300' }}">
            Tier 1: Cash on Delivery
        </a>
        <a href="{{ route('store.admin.payments.index', ['type' => 'manual']) }}"
           class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ $currentType === 'manual' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300' }}">
            Tier 2: Manual / MFS
        </a>
        <a href="{{ route('store.admin.payments.index', ['type' => 'automated']) }}"
           class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ $currentType === 'automated' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300' }}">
            Tier 3: Automated Gateways
        </a>
    </div>

    {{-- Gateways Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($methods as $method)
            @php
                $meta = $supported[$method->provider] ?? null;
                $masked = $method->masked_credentials;
            @endphp
            <div class="card p-5 flex flex-col justify-between border {{ $method->is_active ? 'border-indigo-200/80 dark:border-indigo-900/50 shadow-sm' : 'border-slate-200 dark:border-slate-800 opacity-90' }} transition hover:shadow-md">
                <div>
                    {{-- Card Header: Type Badge & Toggle Switch --}}
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold {{ $method->type_badge_class }}">
                            @if($method->type === 'cash')
                                <i class="fa-solid fa-money-bill-wave"></i> Cash on Delivery
                            @elseif($method->type === 'manual')
                                <i class="fa-solid fa-mobile-screen"></i> Manual MFS
                            @else
                                <i class="fa-solid fa-bolt"></i> Automated API
                            @endif
                        </span>

                        {{-- Status Toggle Form --}}
                        @can('Store Payment Method Edit')
                            <form action="{{ route('store.admin.payments.toggle', $method) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        title="{{ $method->is_active ? 'Click to disable' : 'Click to enable' }}"
                                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $method->is_active ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700' }}">
                                    <span class="sr-only">Toggle payment method</span>
                                    <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $method->is_active ? 'translate-x-4' : 'translate-x-0' }}"></span>
                                </button>
                            </form>
                        @endcan
                    </div>

                    {{-- Provider Title & Info --}}
                    <div class="flex items-start gap-3 mb-3">
                        <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 border border-slate-200/60 dark:border-slate-700">
                            @if(str_contains($method->provider, 'bkash'))
                                <span class="font-bold text-pink-600 text-xs">bKash</span>
                            @elseif(str_contains($method->provider, 'nagad'))
                                <span class="font-bold text-orange-600 text-xs">Nagad</span>
                            @elseif(str_contains($method->provider, 'rocket'))
                                <span class="font-bold text-purple-600 text-xs">Rocket</span>
                            @elseif($method->provider === 'stripe')
                                <i class="fa-brands fa-stripe text-indigo-500 text-xl"></i>
                            @elseif($method->provider === 'sslcommerz')
                                <i class="fa-solid fa-credit-card text-emerald-600 text-base"></i>
                            @elseif($method->provider === 'bank_transfer')
                                <i class="fa-solid fa-building-columns text-slate-600 text-base"></i>
                            @else
                                <i class="fa-solid fa-truck-fast text-blue-600 text-base"></i>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base leading-tight">{{ $method->title }}</h3>
                            <p class="text-xs text-slate-400 mt-1 line-clamp-2">
                                {{ $meta['description'] ?? 'Customer payment gateway option.' }}
                            </p>
                        </div>
                    </div>

                    {{-- Credentials / Config Snapshot --}}
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3 space-y-1.5 text-xs text-slate-600 dark:text-slate-300 font-mono border border-slate-100 dark:border-slate-800">
                        @if($method->type === 'cash')
                            <div class="flex justify-between items-center text-[11px]">
                                <span class="text-slate-400 font-sans">Payment Flow:</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200 font-sans">Cash at doorstep</span>
                            </div>
                            @if(!empty($method->credentials['max_order_amount']))
                                <div class="flex justify-between items-center text-[11px]">
                                    <span class="text-slate-400 font-sans">Max Order Limit:</span>
                                    <span class="text-indigo-600 font-bold font-mono">{{ $method->credentials['max_order_amount'] }}</span>
                                </div>
                            @endif
                        @elseif($method->type === 'manual')
                            <div class="flex justify-between items-center text-[11px]">
                                <span class="text-slate-400 font-sans">Wallet / Account:</span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $masked['wallet_number'] ?? ($masked['account_number'] ?? 'Not set') }}
                                </span>
                            </div>
                            @if(!empty($masked['account_type']))
                                <div class="flex justify-between items-center text-[11px]">
                                    <span class="text-slate-400 font-sans">Account Type:</span>
                                    <span class="capitalize font-sans text-slate-700 dark:text-slate-300">{{ $masked['account_type'] }}</span>
                                </div>
                            @endif
                        @else
                            <div class="flex justify-between items-center text-[11px]">
                                <span class="text-slate-400 font-sans">Environment:</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $method->is_sandbox ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                                    {{ $method->is_sandbox ? 'Sandbox (Test)' : 'Live Mode' }}
                                </span>
                            </div>
                            @if(!empty($masked['store_id']))
                                <div class="flex justify-between items-center text-[11px]">
                                    <span class="text-slate-400 font-sans">Store ID:</span>
                                    <span>{{ $masked['store_id'] }}</span>
                                </div>
                            @elseif(!empty($masked['app_key']))
                                <div class="flex justify-between items-center text-[11px]">
                                    <span class="text-slate-400 font-sans">App Key:</span>
                                    <span class="truncate max-w-[140px]">{{ $masked['app_key'] }}</span>
                                </div>
                            @elseif(!empty($masked['publishable_key']))
                                <div class="flex justify-between items-center text-[11px]">
                                    <span class="text-slate-400 font-sans">Publishable:</span>
                                    <span class="truncate max-w-[140px]">{{ $masked['publishable_key'] }}</span>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Action Footer --}}
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-[11px] font-medium {{ $method->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                        <i class="fa-solid fa-circle text-[7px] mr-1"></i>
                        {{ $method->is_active ? 'Visible at Checkout' : 'Disabled' }}
                    </span>

                    @can('Store Payment Method Edit')
                        <button type="button"
                                onclick='openPaymentModal(@json($method), @json($meta), @json($masked))'
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-950/50 dark:hover:text-indigo-400 text-slate-700 dark:text-slate-200 text-xs font-semibold transition">
                            <i class="fa-solid fa-gear text-[11px]"></i>
                            <span>Configure</span>
                        </button>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Payment Method Configuration Modal --}}
<div id="paymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-4 sm:p-6 md:p-12 flex items-center justify-center">
    <div class="relative w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden my-8">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 id="modalMethodTitle" class="text-lg font-bold text-slate-900 dark:text-white">Configure Payment Method</h3>
                <p id="modalMethodSub" class="text-xs text-slate-400">Manage credentials, instructions, and checkout status</p>
            </div>
            <button type="button" onclick="closePaymentModal()"
                    class="h-8 w-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white grid place-items-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Modal Form --}}
        <form id="paymentForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            {{-- Checkout Display Title & Sort Order --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Checkout Display Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="pmTitle" required
                           class="w-full px-3.5 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Sort Order</label>
                    <input type="number" name="sort_order" id="pmSortOrder" min="0" max="999"
                           class="w-full px-3.5 py-2 text-sm font-mono border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            {{-- Status & Sandbox Toggles --}}
            <div class="flex flex-wrap items-center gap-6 p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" id="pmActive" value="1" class="sr-only peer">
                        <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600 dark:bg-slate-700"></div>
                    </label>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Active at Checkout</span>
                </div>

                <div id="sandboxToggleWrapper" class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_sandbox" id="pmSandbox" value="1" class="sr-only peer">
                        <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-600 dark:bg-slate-700"></div>
                    </label>
                    <div>
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Sandbox / Test Mode</span>
                        <p class="text-[10px] text-slate-400">Simulate transactions without charging real money</p>
                    </div>
                </div>
            </div>

            {{-- Dynamic Credentials Fields Container --}}
            <div id="dynamicCredsFields" class="space-y-3 pt-1">
                {{-- Populated via JavaScript based on gateway --}}
            </div>

            {{-- Instructions --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Checkout Instructions for Customer</label>
                <textarea name="instructions" id="pmInstructions" rows="3" placeholder="e.g. Please send money to the personal number above, then submit your bKash phone number and TrxID."
                          class="w-full px-3.5 py-2 text-xs border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500 resize-none"></textarea>
                <p class="text-[11px] text-slate-400 mt-1">Displayed to the customer upon selecting this method on checkout.</p>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closePaymentModal()"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">
                    Cancel
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition">
                    <i class="fa-solid fa-check"></i>
                    <span>Save Configuration</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const pModal = document.getElementById('paymentModal');
    const pForm = document.getElementById('paymentForm');
    const pmTitle = document.getElementById('pmTitle');
    const pmSortOrder = document.getElementById('pmSortOrder');
    const pmActive = document.getElementById('pmActive');
    const pmSandbox = document.getElementById('pmSandbox');
    const pmInstructions = document.getElementById('pmInstructions');
    const dynamicCredsFields = document.getElementById('dynamicCredsFields');
    const sandboxToggleWrapper = document.getElementById('sandboxToggleWrapper');
    const modalMethodTitle = document.getElementById('modalMethodTitle');
    const modalMethodSub = document.getElementById('modalMethodSub');

    function openPaymentModal(method, meta, masked) {
        pForm.action = "{{ url('store/payments/methods') }}/" + method.id;
        modalMethodTitle.textContent = "Configure " + method.title;
        modalMethodSub.textContent = meta ? meta.category : "Payment Gateway Settings";

        pmTitle.value = method.title || '';
        pmSortOrder.value = method.sort_order ?? 0;
        pmActive.checked = Boolean(method.is_active);
        pmSandbox.checked = Boolean(method.is_sandbox);
        pmInstructions.value = method.instructions || '';

        // Show/hide sandbox toggle
        if (method.type === 'automated') {
            sandboxToggleWrapper.classList.remove('hidden');
        } else {
            sandboxToggleWrapper.classList.add('hidden');
        }

        // Render dynamic fields
        dynamicCredsFields.innerHTML = '';
        if (meta && meta.fields && meta.fields.length > 0) {
            const heading = document.createElement('h4');
            heading.className = "text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider text-slate-400";
            heading.textContent = method.type === 'automated' ? "Gateway API Credentials" : "Account & Payment Details";
            dynamicCredsFields.appendChild(heading);

            meta.fields.forEach(f => {
                const wrap = document.createElement('div');
                wrap.className = "space-y-1";

                const label = document.createElement('label');
                label.className = "block text-xs font-semibold text-slate-700 dark:text-slate-300";
                label.innerHTML = f.label + (f.required ? ' <span class="text-red-500">*</span>' : '');
                wrap.appendChild(label);

                const currentVal = (masked && masked[f.name]) ? masked[f.name] : '';

                if (f.type === 'select') {
                    const select = document.createElement('select');
                    select.name = `credentials[${f.name}]`;
                    select.className = "w-full px-3.5 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500";
                    for (const [k, text] of Object.entries(f.options || {})) {
                        const opt = document.createElement('option');
                        opt.value = k;
                        opt.textContent = text;
                        if (currentVal === k) opt.selected = true;
                        select.appendChild(opt);
                    }
                    wrap.appendChild(select);
                } else {
                    const input = document.createElement('input');
                    input.type = f.type || 'text';
                    input.name = `credentials[${f.name}]`;
                    input.value = currentVal;
                    input.placeholder = f.placeholder || '';
                    if (f.required && !currentVal) input.required = true;
                    input.className = "w-full px-3.5 py-2 text-sm font-mono border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500";
                    wrap.appendChild(input);
                }

                dynamicCredsFields.appendChild(wrap);
            });
        }

        pModal.classList.remove('hidden');
    }

    function closePaymentModal() {
        pModal.classList.add('hidden');
    }

    // Close on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !pModal.classList.contains('hidden')) {
            closePaymentModal();
        }
    });
</script>
@endsection
