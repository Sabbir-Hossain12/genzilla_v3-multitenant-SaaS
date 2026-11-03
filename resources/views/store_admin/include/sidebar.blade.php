@php
    $currentRoute = request()->route()?->getName() ?? '';
    $isActive = fn ($prefixes) => \Illuminate\Support\Str::startsWith($currentRoute, $prefixes);
@endphp
<div id="overlay" onclick="closeSidebar()"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-20 hidden lg:hidden"></div>
<aside id="sidebar"
       class="fixed top-0 left-0 h-full w-72 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 z-30 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="h-16 flex items-center px-5 border-b border-slate-100 dark:border-slate-800">
        <span
            class="font-extrabold text-lg text-ink dark:text-white tracking-tight truncate">{{ auth()->user()->tenant->store_name ?? 'Store Admin' }}</span>
    </div>
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
        <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500 whitespace-nowrap">
            Store</p>
        @can('Store Dashboard')
            <a href="{{ route('store.admin.dashboard') }}"
               class="nav-link whitespace-nowrap {{ $isActive('store.admin.dashboard') ? 'active' : '' }}">
                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                </svg>
                <span class="truncate">Dashboard</span>
            </a>
        @endcan

        @canany(['Store Product List', 'Store Category List', 'Store Brand List', 'Store Attribute List', 'Store Inventory List', 'Store Product Review List'])
            <div class="nav-group">
                @php $catalogActive = $isActive(['store.admin.products.', 'store.admin.categories.', 'store.admin.brands.', 'store.admin.attributes.', 'store.admin.inventory.']); @endphp
                <button type="button"
                        class="nav-link nav-toggle w-full text-left whitespace-nowrap {{ $catalogActive ? 'has-active' : '' }}"
                        aria-expanded="{{ $catalogActive ? 'true' : 'false' }}" onclick="toggleGroup(this)">
                    <i class="fa-solid fa-folder-open w-4 text-center shrink-0"></i>
                    <span class="truncate">Catalog & Inventory</span>
                    <svg class="chev ml-auto h-3.5 w-3.5 shrink-0 text-slate-400 transition-transform duration-200"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
                <div class="submenu {{ $catalogActive ? 'open' : '' }}">
                    <div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                        @can('Store Category List')
                            <a href="{{ route('store.admin.categories.index') }}"
                                class="sub-link whitespace-nowrap {{ $isActive('store.admin.categories.') ? 'active' : '' }}"><span class="truncate">Categories</span></a>
                        @endcan
                        @can('Store Brand List')
                            <a href="{{ route('store.admin.brands.index') }}"
                                class="sub-link whitespace-nowrap {{ $isActive('store.admin.brands.') ? 'active' : '' }}"><span class="truncate">Brands</span></a>
                        @endcan
                        @can('Store Attribute List')
                            <a href="{{ route('store.admin.attributes.index') }}"
                                class="sub-link whitespace-nowrap {{ $isActive('store.admin.attributes.') ? 'active' : '' }}"><span class="truncate">Attributes</span></a>
                        @endcan
                        @can('Store Product List')
                            <a href="{{ route('store.admin.products.index') }}"
                                class="sub-link whitespace-nowrap {{ $isActive('store.admin.products.') ? 'active' : '' }}"><span class="truncate">Products</span></a>
                        @endcan
                        @can('Store Product Review List')
                            <a href="{{ route('store.admin.reviews.index') }}"
                                class="sub-link whitespace-nowrap {{ $isActive('store.admin.reviews.') ? 'active' : '' }}"><span class="truncate">Reviews Moderation</span></a>
                        @endcan
                        @can('Store Inventory List')
                            <a href="{{ route('store.admin.inventory.index') }}"
                                class="sub-link whitespace-nowrap {{ $isActive('store.admin.inventory.') ? 'active' : '' }}"><span class="truncate">Inventory</span></a>
                        @endcan
                    </div>
                </div>
            </div>
        @endcanany

        @can('Store Prescription List')
            @php
                $pendingRxCount = \App\Models\TenantPrescription::where('tenant_id', tenantId())->where('status', 'pending_review')->count();
            @endphp
            <a href="{{ route('store.admin.prescriptions.index') }}"
               class="nav-link whitespace-nowrap {{ $isActive('store.admin.prescriptions.') ? 'active' : '' }}">
                <i class="fa-solid fa-file-prescription w-4 text-center shrink-0"></i>
                <span class="truncate flex-1">Prescriptions</span>
                @if($pendingRxCount > 0)
                    <span class="ml-auto inline-flex shrink-0 items-center justify-center rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold text-white shadow-xs">
                        {{ $pendingRxCount }}
                    </span>
                @endif
            </a>
        @endcan

        @can('Store Order List')
            <a href="{{ route('store.admin.orders.index') }}"
               class="nav-link whitespace-nowrap {{ $isActive('store.admin.orders.') ? 'active' : '' }}">
                <i class="fa-solid fa-cart-shopping w-4 text-center shrink-0"></i>
                <span class="truncate">Orders</span>
            </a>
        @endcan

        @can('Store Abandoned Checkout List')
            @php
                $uncontactedCartsCount = \App\Models\Cart::where('tenant_id', tenantId())
                    ->whereNotNull('customer_id')
                    ->has('items')
                    ->where('updated_at', '<=', now()->subHours(2))
                    ->whereNull('recovery_contacted_at')
                    ->count();
            @endphp
            <a href="{{ route('store.admin.abandoned_checkouts.index') }}"
               class="nav-link whitespace-nowrap {{ $isActive('store.admin.abandoned_checkouts.') ? 'active' : '' }}">
                <i class="fa-solid fa-cart-arrow-down w-4 text-center shrink-0"></i>
                <span class="truncate flex-1">Abandoned Checkouts</span>
                @if($uncontactedCartsCount > 0)
                    <span class="ml-auto inline-flex shrink-0 items-center justify-center rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold text-white shadow-xs" title="{{ $uncontactedCartsCount }} uncontacted recoverable carts">
                        {{ $uncontactedCartsCount }}
                    </span>
                @endif
            </a>
        @endcan

        @can('Store Product Review List')
            @php
                $pendingReviewsCount = \App\Models\ProductReview::where('tenant_id', tenantId())->where('status', 'pending')->count();
            @endphp
            <a href="{{ route('store.admin.reviews.index') }}"
               class="nav-link whitespace-nowrap {{ $isActive('store.admin.reviews.') ? 'active' : '' }}">
                <i class="fa-solid fa-star-half-stroke w-4 text-center shrink-0"></i>
                <span class="truncate flex-1">Product Reviews</span>
                @if($pendingReviewsCount > 0)
                    <span class="ml-auto inline-flex shrink-0 items-center justify-center rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold text-white shadow-xs" title="{{ $pendingReviewsCount }} pending reviews">
                        {{ $pendingReviewsCount }}
                    </span>
                @endif
            </a>
        @endcan

        @can('Store Transaction List')
            <a href="{{ route('store.admin.transactions.index') }}"
               class="nav-link whitespace-nowrap {{ $isActive('store.admin.transactions.') ? 'active' : '' }}">
                <i class="fa-solid fa-money-bill-transfer w-4 text-center shrink-0"></i>
                <span class="truncate">Transactions & Payments</span>
            </a>
        @endcan

        @can('Store Customer List')
            <a href="{{ route('store.admin.customers.index') }}"
               class="nav-link whitespace-nowrap {{ $isActive('store.admin.customers.') ? 'active' : '' }}">
                <i class="fa-solid fa-users w-4 text-center shrink-0"></i>
                <span class="truncate">Customers</span>
            </a>
        @endcan

        @canany(['Store Coupon List', 'Store Delivery Charge List'])
            <div class="nav-group">
                @php $promotionsActive = $isActive(['store.admin.coupons.', 'store.admin.delivery_charges.']); @endphp
                <button type="button"
                        class="nav-link nav-toggle w-full text-left whitespace-nowrap {{ $promotionsActive ? 'has-active' : '' }}"
                        aria-expanded="{{ $promotionsActive ? 'true' : 'false' }}" onclick="toggleGroup(this)">
                    <i class="fa-solid fa-tags w-4 text-center shrink-0"></i>
                    <span class="truncate">Promotions & Delivery</span>
                    <svg class="chev ml-auto h-3.5 w-3.5 shrink-0 text-slate-400 transition-transform duration-200"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
                <div class="submenu {{ $promotionsActive ? 'open' : '' }}">
                    <div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                        @can('Store Coupon List')
                            <a href="{{ route('store.admin.coupons.index') }}"
                                class="sub-link whitespace-nowrap {{ $isActive('store.admin.coupons.') ? 'active' : '' }}"><span class="truncate">Coupons</span></a>
                        @endcan
                        @can('Store Delivery Charge List')
                            <a href="{{ route('store.admin.delivery_charges.index') }}"
                                class="sub-link whitespace-nowrap {{ $isActive('store.admin.delivery_charges.') ? 'active' : '' }}"><span class="truncate">Delivery Charges</span></a>
                        @endcan
                    </div>
                </div>
            </div>
        @endcanany

        @canany(['Store Slider List', 'Store Banner List', 'Store Page List'])
            <div class="nav-group">
                @php $cmsActive = $isActive(['store.admin.sliders.', 'store.admin.banners.', 'store.admin.pages.']); @endphp
                <button type="button"
                        class="nav-link nav-toggle w-full text-left whitespace-nowrap {{ $cmsActive ? 'has-active' : '' }}"
                        aria-expanded="{{ $cmsActive ? 'true' : 'false' }}" onclick="toggleGroup(this)">
                    <i class="fa-solid fa-layer-group w-4 text-center shrink-0"></i>
                    <span class="truncate">Storefront & CMS</span>
                    <svg class="chev ml-auto h-3.5 w-3.5 shrink-0 text-slate-400 transition-transform duration-200"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
                <div class="submenu {{ $cmsActive ? 'open' : '' }}">
                    <div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                        @can('Store Slider List')
                            <a href="{{ route('store.admin.sliders.index') }}"
                                class="sub-link whitespace-nowrap {{ $isActive('store.admin.sliders.') ? 'active' : '' }}"><span class="truncate">Hero Sliders</span></a>
                        @endcan
                        @can('Store Banner List')
                            <a href="{{ route('store.admin.banners.index') }}"
                                class="sub-link whitespace-nowrap {{ $isActive('store.admin.banners.') ? 'active' : '' }}"><span class="truncate">Promo Banners</span></a>
                        @endcan
                        @can('Store Page List')
                            <a href="{{ route('store.admin.pages.index') }}"
                                class="sub-link whitespace-nowrap {{ $isActive('store.admin.pages.') ? 'active' : '' }}"><span class="truncate">Custom Pages</span></a>
                        @endcan
                    </div>
                </div>
            </div>
        @endcanany

        <div class="!my-4 border-t border-slate-100 dark:border-slate-800"></div>
        <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500 whitespace-nowrap">
            Settings & Security</p>

        @canany(['Store Admin List', 'Store Role List', 'Store Permission List'])
            <div class="nav-group">
                @php $accessActive = $isActive(['store.admin.admins.', 'store.admin.role.', 'store.admin.permission.']); @endphp
                <button type="button"
                        class="nav-link nav-toggle w-full text-left whitespace-nowrap {{ $accessActive ? 'has-active' : '' }}"
                        aria-expanded="{{ $accessActive ? 'true' : 'false' }}" onclick="toggleGroup(this)">
                    <i class="fa-solid fa-user-shield w-4 text-center shrink-0"></i>
                    <span class="truncate">Admin Security</span>
                    <svg class="chev ml-auto h-3.5 w-3.5 shrink-0 text-slate-400 transition-transform duration-200"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
                <div class="submenu {{ $accessActive ? 'open' : '' }}">
                    <div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                        @can('Store Admin List')
                            <a href="{{ route('store.admin.admins.index') }}"
                                class="sub-link whitespace-nowrap {{ $isActive('store.admin.admins.') ? 'active' : '' }}"><span class="truncate">Store Admins</span></a>
                        @endcan
                        @can('Store Role List')
                            <a href="{{ route('store.admin.role.index') }}"
                                class="sub-link whitespace-nowrap {{ $isActive('store.admin.role.') ? 'active' : '' }}"><span class="truncate">Roles</span></a>
                        @endcan
                        @can('Store Permission List')
                            <a href="{{ route('store.admin.permission.index') }}"
                                class="sub-link whitespace-nowrap {{ $isActive('store.admin.permission.') ? 'active' : '' }}"><span class="truncate">Permissions</span></a>
                        @endcan
                    </div>
                </div>
            </div>
        @endcanany

        @can('Store Payment Method List')
            <a href="{{ route('store.admin.payments.index') }}"
               class="nav-link whitespace-nowrap {{ $isActive('store.admin.payments.') ? 'active' : '' }}">
                <i class="fa-solid fa-credit-card w-4 text-center shrink-0"></i>
                <span class="truncate">Payment Gateways</span>
            </a>
        @endcan

        @can('Store Domain List')
            <a href="{{ route('store.admin.domains.index') }}"
               class="nav-link whitespace-nowrap {{ $isActive('store.admin.domains.') ? 'active' : '' }}">
                <i class="fa-solid fa-globe w-4 text-center shrink-0"></i>
                <span class="truncate">Custom Domain</span>
            </a>
        @endcan

        @can('Store Subscription View')
            <a href="{{ route('store.admin.subscription.index') }}"
               class="nav-link whitespace-nowrap {{ $isActive('store.admin.subscription.') ? 'active' : '' }}">
                <i class="fa-solid fa-receipt w-4 text-center shrink-0"></i>
                <span class="truncate">Store Subscription & Billing</span>
            </a>
        @endcan

        @can('Store General Setting')
            <a href="{{ route('store.admin.settings.index') }}"
               class="nav-link whitespace-nowrap {{ $isActive('store.admin.settings.') ? 'active' : '' }}">
                <i class="fa-solid fa-sliders w-4 text-center shrink-0"></i>
                <span class="truncate">Store Settings</span>
            </a>
        @endcan

    </nav>

    <!-- User -->
    <div class="p-3 border-t border-slate-100 dark:border-slate-800 relative" id="side-profile-menu">

        <!-- Dropdown (opens upward) -->
        <div id="side-profile-dropdown" role="menu"
             class="hidden absolute left-3 right-3 bottom-full mb-2 card shadow-lg p-1.5 z-40">
            <a href="#" role="menuitem"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-primarylt dark:hover:bg-slate-800 hover:text-primary cursor-pointer transition-colors whitespace-nowrap">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span class="truncate">Profile</span>
            </a>
            <form method="post" action="{{ route('store.admin.logout') }}">
                @csrf
                <button type="submit" role="menuitem"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 cursor-pointer transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span class="truncate">Logout</span>
                </button>
            </form>
        </div>

        <button type="button" id="side-profile-btn" onclick="toggleSideProfile(event)"
                aria-haspopup="true" aria-expanded="false"
                class="w-full flex items-center gap-3 px-2.5 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors text-left whitespace-nowrap">
            <img src="https://i.pravatar.cc/40?img=68"
                 class="w-8 h-8 rounded-full object-cover ring-2 ring-primary/30 shrink-0" alt="Alex"/>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-ink dark:text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                <p class="text-xs text-slate-400 truncate">{{ auth()->user()->getRoleNames()->implode(', ') }}</p>
            </div>
            <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2">
                <circle cx="12" cy="12" r="1"/>
                <circle cx="19" cy="12" r="1"/>
                <circle cx="5" cy="12" r="1"/>
            </svg>
        </button>
    </div>
</aside>
