@extends('platform_admin.layout.auth')

@section('contents')
    <!-- Left branded panel -->
    <div class="hidden lg:flex flex-col justify-between bg-ink relative overflow-hidden p-10 xl:p-14">
        <div class="absolute -right-24 -top-24 w-80 h-80 rounded-full bg-primary/20"></div>
        <div class="absolute -left-16 bottom-0 w-64 h-64 rounded-full bg-accent/10"></div>

        <a href="{{ url('/') }}" class="flex items-center gap-2 relative z-10">
            <div class="w-9 h-9 rounded-lg bg-primary flex items-center justify-center">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M3 12c3-4 6 4 9 0s6-4 9 0M3 18c3-4 6 4 9 0s6-4 9 0" stroke="white" stroke-width="2"
                          stroke-linecap="round"/>
                </svg>
            </div>
            <span class="text-white text-xl font-extrabold">Shop<span class="text-accent">wave</span></span>
        </a>

        <div class="relative z-10 max-w-md">
            <h2 class="text-white text-[28px] xl:text-[32px] font-extrabold leading-tight">Run your store from
                anywhere.</h2>
            <p class="text-gray-400 text-[15px] mt-3 leading-relaxed">Log in to manage orders, products, and payments —
                all from one clean dashboard.</p>
        </div>

        <div class="relative z-10 bg-white/5 border border-white/10 rounded-2xl p-5 max-w-md">
            <div class="flex text-amber-400 text-[13px] mb-2.5">★★★★★</div>
            <p class="text-gray-300 text-[13.5px] leading-relaxed mb-4">"We migrated from a patchwork of plugins to
                Shopwave in a weekend. Checkout conversion went up 18% in the first month alone."</p>
            <div class="flex items-center gap-3">
                <div
                    class="w-9 h-9 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold text-[12.5px]">
                    RA
                </div>
                <div><p class="text-white text-[13px] font-bold">Rima Akter</p>
                    <p class="text-gray-500 text-[11.5px]">Founder, Verdant Co.</p></div>
            </div>
        </div>
    </div>

    <!-- Right form panel -->
    <div class="flex flex-col items-center justify-center p-6 sm:p-10">
        <div class="w-full max-w-sm">
            <a href="{{ url('/') }}" class="flex lg:hidden items-center gap-2 justify-center mb-8">
                <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M3 12c3-4 6 4 9 0s6-4 9 0M3 18c3-4 6 4 9 0s6-4 9 0" stroke="white" stroke-width="2"
                              stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="text-ink text-lg font-extrabold">Shop<span class="text-accent">wave</span></span>
            </a>
            <h1 class="text-[26px] font-extrabold text-ink">Welcome back</h1>
            <p class="text-[14px] text-gray-500 mt-1.5 mb-7">Log in to manage your Platform.</p>

            <form id="login-form" method="POST" action="{{ route('store.admin.login.store')}}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Email Address</label>
                    <input required id="li-email" type="email" name="email"  placeholder="you@example.com"
                           class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-[14px] focus:outline-none focus:border-primary transition-colors"/>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-[13px] font-semibold text-gray-700">Password</label>
                    </div>

                    <div class="relative">
                        <input id="li-password" type="password" name="password"  placeholder="Enter your password"
                               class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 pr-10 text-[14px] focus:outline-none focus:border-primary transition-colors"/>
                        <button type="button" onclick="togglePw('li-password', this)"
                                class="cursor-pointer absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" stroke="currentColor"
                                      stroke-width="2"/>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </button>
                    </div>
                    <div>
                        @if ($errors->any())
                            @foreach ($errors->all() as $error)
                                <span class="text-xs text-red-800 fw-bold py-1">{{ $error }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" checked class="w-4 h-4 rounded accent-primary cursor-pointer"/>
                    <span class="text-[13px] text-gray-600">Remember me for 30 days</span>
                </label>
                <p id="li-error" class="hidden text-[12.5px] text-red-500">Please enter a valid email and password.</p>
                <button type="submit"
                        class="cursor-pointer w-full bg-primary hover:bg-primarydark text-white font-bold text-[14.5px] py-3 rounded-xl transition-colors">
                    Log In
                </button>
            </form>
        </div>
    </div>
@endsection

@push('js')

@endpush
