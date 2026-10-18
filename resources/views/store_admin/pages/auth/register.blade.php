@extends('platform_admin.layout.auth')

@section('contents')
    <!-- Left branded panel -->
    <div class="hidden lg:flex flex-col justify-between bg-ink relative overflow-hidden p-10 xl:p-14">
        <div class="absolute -right-24 -top-24 w-80 h-80 rounded-full bg-primary/20"></div>
        <div class="absolute -left-16 bottom-0 w-64 h-64 rounded-full bg-accent/10"></div>

        <!-- Brand Logo -->
        <a href="{{ url('/') }}" class="flex items-center gap-2 relative z-10">
            <div class="w-9 h-9 rounded-lg bg-primary flex items-center justify-center">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M3 12c3-4 6 4 9 0s6-4 9 0M3 18c3-4 6 4 9 0s6-4 9 0" stroke="white" stroke-width="2"
                          stroke-linecap="round"/>
                </svg>
            </div>
            <span class="text-white text-xl font-extrabold">Gen<span class="text-accent">zilla</span></span>
        </a>

        <!-- Middle Hero Info -->
        <div class="relative z-10 max-w-md">
            <h2 class="text-white text-[28px] xl:text-[32px] font-extrabold leading-tight">Launch your pharmacy store in seconds.</h2>
            <p class="text-gray-400 text-[15px] mt-3 leading-relaxed">Start your 14-day full-access free trial today. Manage prescription uploads, multi-currency checkout, and order fulfillment from one unified platform.</p>
        </div>

        <!-- Selected Subscription Plan Summary Card -->
        <div class="relative z-10 bg-white/5 border border-white/10 rounded-2xl p-5 max-w-md">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400 bg-emerald-400/10 px-2.5 py-1 rounded-full">
                    Selected Plan
                </span>
                <span class="text-gray-400 text-[12px]">14-Day Free Trial</span>
            </div>

            <div class="flex items-baseline justify-between">
                <h3 class="text-white text-xl font-bold">{{ $selectedPlan->name }} Plan</h3>
                <div class="text-right">
                    <span class="text-white text-2xl font-extrabold">
                        ${{ $selectedInterval === 'yearly' ? $selectedPlan->yearly_price : $selectedPlan->monthly_price }}
                    </span>
                    <span class="text-gray-400 text-[12px]">/{{ $selectedInterval === 'yearly' ? 'yr' : 'mo' }}</span>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-white/10 space-y-2 text-[12.5px] text-gray-300">
                <div class="flex items-center gap-2">
                    <span class="text-emerald-400 font-bold">&#10003;</span> Up to {{ $selectedPlan->max_products }} Product SKUs
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-emerald-400 font-bold">&#10003;</span> {{ $selectedPlan->max_staff_accounts }} Staff Account(s)
                </div>
                @if($selectedPlan->allow_custom_domain)
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-400 font-bold">&#10003;</span> Custom Domain & Automated SSL
                    </div>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-white/10 flex justify-between text-[11.5px] text-gray-400">
                <span>Due Today: <strong class="text-white">$0.00</strong></span>
                <span>Cancel anytime in dashboard</span>
            </div>
        </div>
    </div>

    <!-- Right form panel -->
    <div class="flex flex-col items-center justify-center p-6 sm:p-8 xl:p-12">
        <div class="w-full max-w-xl xl:max-w-2xl py-4 sm:py-6">
            <!-- Mobile Brand Header -->
            <a href="{{ url('/') }}" class="flex lg:hidden items-center gap-2 justify-center mb-6">
                <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M3 12c3-4 6 4 9 0s6-4 9 0M3 18c3-4 6 4 9 0s6-4 9 0" stroke="white" stroke-width="2"
                              stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="text-ink text-lg font-extrabold">Gen<span class="text-accent">zilla</span></span>
            </a>

            <h1 class="text-[24px] sm:text-[26px] font-extrabold text-ink">Start 14-Day Free Trial</h1>
            <p class="text-[13.5px] text-gray-500 mt-1 mb-5">Create your account and setup your multi-tenant store.</p>

            <form id="register-form" method="POST" action="{{ route('store.admin.register.store') }}" class="space-y-4">
                @csrf

                <!-- Hidden Plan Attributes -->
                <input type="hidden" name="plan_slug" value="{{ $selectedPlan->slug }}">
                <input type="hidden" name="billing_interval" value="{{ $selectedInterval }}">

                <!-- Global Error Messages -->
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 text-[13px] rounded-lg p-3">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Two-Column Fields Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 items-start">
                    <!-- COLUMN 1: ACCOUNT IDENTITY -->
                    <div class="space-y-3.5">
                        <div class="flex items-center gap-2 pb-1 border-b border-gray-100">
                            <span class="w-5 h-5 rounded-full bg-primary/10 text-primary text-[11px] font-bold flex items-center justify-center">1</span>
                            <span class="text-[11.5px] font-bold uppercase tracking-wider text-primary">Account Identity</span>
                        </div>

                        <div>
                            <label class="block text-[13px] font-semibold text-gray-700 mb-1">Full Name</label>
                            <input required type="text" name="name" value="{{ old('name') }}" placeholder="Dr. John Doe"
                                   class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-[14px] focus:outline-none focus:border-primary transition-colors"/>
                        </div>

                        <div>
                            <label class="block text-[13px] font-semibold text-gray-700 mb-1">Email Address</label>
                            <input required type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com"
                                   class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-[14px] focus:outline-none focus:border-primary transition-colors"/>
                        </div>

                        <div>
                            <label class="block text-[13px] font-semibold text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <input id="reg-password" type="password" name="password" required placeholder="Create a password"
                                       class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 pr-10 text-[14px] focus:outline-none focus:border-primary transition-colors"/>
                                <button type="button" onclick="togglePw('reg-password', this)"
                                        class="cursor-pointer absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" stroke="currentColor" stroke-width="2"/>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMN 2: STORE SETUP -->
                    <div class="space-y-3.5">
                        <div class="flex items-center gap-2 pb-1 border-b border-gray-100">
                            <span class="w-5 h-5 rounded-full bg-primary/10 text-primary text-[11px] font-bold flex items-center justify-center">2</span>
                            <span class="text-[11.5px] font-bold uppercase tracking-wider text-primary">Store Setup</span>
                        </div>

                        <div>
                            <label class="block text-[13px] font-semibold text-gray-700 mb-1">Store Name</label>
                            <input required type="text" name="store_name" value="{{ old('store_name') }}" placeholder="MediCare Pharmacy"
                                   class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-[14px] focus:outline-none focus:border-primary transition-colors"/>
                        </div>

                        <div>
                            <label class="block text-[13px] font-semibold text-gray-700 mb-1">Store Subdomain</label>
                            <div class="flex rounded-lg border border-gray-200 overflow-hidden focus-within:border-primary transition-colors">
                                <input required type="text" name="subdomain" value="{{ old('subdomain') }}" placeholder="medicare"
                                       class="w-full min-w-0 px-3.5 py-2.5 text-[14px] focus:outline-none"/>
                                <span class="bg-gray-50 border-l border-gray-200 px-3 py-2.5 text-[12.5px] text-gray-500 font-medium flex items-center shrink-0">
                                    .genzilla.com
                                </span>
                            </div>
                        </div>

                        <!-- Trial summary note to balance height -->
                        <div class="rounded-lg bg-emerald-50/70 border border-emerald-100 p-3 flex items-start gap-2.5">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/15 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="text-[12px] text-gray-600 leading-snug">
                                <span class="font-bold text-gray-800">14-day trial on {{ $selectedPlan->name }} plan.</span>
                                <span class="block text-gray-500 text-[11px] mt-0.5">Instant activation &bull; No card required</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms & Conditions Checkbox -->
                <div class="pt-1">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" required checked class="w-4 h-4 rounded accent-primary cursor-pointer mt-0.5"/>
                        <span class="text-[12.5px] text-gray-600 leading-snug">
                            I agree to the <a href="" target="_blank" class="text-primary underline">Terms of Service</a> and <a href="" target="_blank" class="text-primary underline">Privacy Policy</a>.
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="cursor-pointer w-full bg-primary hover:bg-primarydark text-white font-bold text-[14.5px] py-3 rounded-xl transition-colors shadow-sm mt-1">
                    Create Store & Start 14-Day Free Trial
                </button>

                <!-- Login Redirect Link -->
                <p class="text-center text-[13px] text-gray-500 mt-3">
                    Already have a store?
                    <a href="{{ route('admin.login') }}" class="font-bold text-primary hover:underline">Log in here</a>
                </p>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script>
        function togglePw(inputId, btn) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
            } else {
                input.type = "password";
            }
        }
    </script>
@endpush
