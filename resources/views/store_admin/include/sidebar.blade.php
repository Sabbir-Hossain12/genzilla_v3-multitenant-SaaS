@php
    $currentRoute = request()->route()?->getName() ?? '';
    $isActive = fn ($prefixes) => \Illuminate\Support\Str::startsWith($currentRoute, $prefixes);
@endphp
<div id="overlay" onclick="closeSidebar()"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-20 hidden lg:hidden"></div>
<aside id="sidebar"
       class="fixed top-0 left-0 h-full w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 z-30 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="h-16 flex items-center px-5 border-b border-slate-100 dark:border-slate-800">
        <span
            class="font-extrabold text-lg text-ink dark:text-white tracking-tight">{{ auth()->user()->tenant->store_name ?? 'Store Admin' }}</span>
    </div>
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
        <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">
            Store</p>
        @can('Store Dashboard')
            <a href="{{ route('store.admin.dashboard') }}"
               class="nav-link {{ $isActive('store.admin.dashboard') ? 'active' : '' }}">
                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                </svg>
                Dashboard
            </a>
        @endcan

        @canany(['Store Product List', 'Store Category List', 'Store Brand List', 'Store Attribute List', 'Store Inventory List'])
            <div class="nav-group">
                @php $catalogActive = $isActive(['store.admin.products.', 'store.admin.categories.', 'store.admin.brands.', 'store.admin.attributes.', 'store.admin.inventory.']); @endphp
                <button type="button"
                        class="nav-link nav-toggle w-full text-left {{ $catalogActive ? 'has-active' : '' }}"
                        aria-expanded="{{ $catalogActive ? 'true' : 'false' }}" onclick="toggleGroup(this)">
                    <i class="fa-solid fa-folder-open"></i>
                    <span>Catalog & Inventory</span>
                    <svg class="chev ml-auto h-3.5 w-3.5 shrink-0 text-slate-400 transition-transform duration-200"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
                <div class="submenu {{ $catalogActive ? 'open' : '' }}">
                    <div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                        @can('Store Category List')
                            <a href="{{ route('store.admin.categories.index') }}"
                               class="sub-link {{ $isActive('store.admin.categories.') ? 'active' : '' }}">Categories</a>
                        @endcan
                        @can('Store Brand List')
                            <a href="{{ route('store.admin.brands.index') }}"
                               class="sub-link {{ $isActive('store.admin.brands.') ? 'active' : '' }}">Brands</a>
                        @endcan
                        @can('Store Attribute List')
                            <a href="{{ route('store.admin.attributes.index') }}"
                               class="sub-link {{ $isActive('store.admin.attributes.') ? 'active' : '' }}">Attributes</a>
                        @endcan
                        @can('Store Product List')
                            <a href="{{ route('store.admin.products.index') }}"
                               class="sub-link {{ $isActive('store.admin.products.') ? 'active' : '' }}">Products</a>
                        @endcan
                        @can('Store Inventory List')
                            <a href="{{ route('store.admin.inventory.index') }}"
                               class="sub-link {{ $isActive('store.admin.inventory.') ? 'active' : '' }}">Inventory</a>
                        @endcan
                    </div>
                </div>
            </div>
        @endcanany

        <div class="!my-4 border-t border-slate-100 dark:border-slate-800"></div>
        <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">
            System</p>

        @canany(['Store Admin List', 'Store Role List', 'Store Permission List'])
            <div class="nav-group">
                @php $accessActive = $isActive(['store.admin.admins.', 'store.admin.role.', 'store.admin.permission.']); @endphp
                <button type="button"
                        class="nav-link nav-toggle w-full text-left {{ $accessActive ? 'has-active' : '' }}"
                        aria-expanded="{{ $accessActive ? 'true' : 'false' }}" onclick="toggleGroup(this)">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>Admin Security</span>
                    <svg class="chev ml-auto h-3.5 w-3.5 shrink-0 text-slate-400 transition-transform duration-200"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
                <div class="submenu {{ $accessActive ? 'open' : '' }}">
                    <div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                        @can('Store Admin List')
                            <a href="{{ route('store.admin.admins.index') }}"
                               class="sub-link {{ $isActive('store.admin.admins.') ? 'active' : '' }}">Store Admins</a>
                        @endcan
                        @can('Store Role List')
                            <a href="{{ route('store.admin.role.index') }}"
                               class="sub-link {{ $isActive('store.admin.role.') ? 'active' : '' }}">Roles</a>
                        @endcan
                        @can('Store Permission List')
                            <a href="{{ route('store.admin.permission.index') }}"
                               class="sub-link {{ $isActive('store.admin.permission.') ? 'active' : '' }}">Permissions</a>
                        @endcan
                    </div>
                </div>
            </div>
        @endcanany

        @can('Store General Setting')
            <a href="{{ route('store.admin.settings.index') }}"
               class="nav-link {{ $isActive('store.admin.settings.') ? 'active' : '' }}">
                <i class="fa-solid fa-sliders"></i> Store Settings
            </a>
        @endcan


    </nav>

    <!-- User -->
    <div class="p-3 border-t border-slate-100 dark:border-slate-800 relative" id="side-profile-menu">

        <!-- Dropdown (opens upward) -->
        <div id="side-profile-dropdown" role="menu"
             class="hidden absolute left-3 right-3 bottom-full mb-2 card shadow-lg p-1.5 z-40">
            <a href="#" role="menuitem"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-primarylt dark:hover:bg-slate-800 hover:text-primary cursor-pointer transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Profile
            </a>
            <form method="post" action="{{ route('store.admin.logout') }}">
                @csrf
                <button type="submit" role="menuitem"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 cursor-pointer transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>

        <button type="button" id="side-profile-btn" onclick="toggleSideProfile(event)"
                aria-haspopup="true" aria-expanded="false"
                class="w-full flex items-center gap-3 px-2.5 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors text-left">
            <img src="https://i.pravatar.cc/40?img=68"
                 class="w-8 h-8 rounded-full object-cover ring-2 ring-primary/30 shrink-0" alt="Alex"/>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-ink dark:text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                <p class="text-xs text-slate-400 truncate">{{ auth()->user()->getRoleNames()->implode('') }}</p>
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
