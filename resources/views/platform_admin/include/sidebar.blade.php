<!-- Mobile overlay -->
<div id="overlay" onclick="closeSidebar()"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-20 hidden lg:hidden"></div>

<!-- ══════════════════════════════════════════════
     SIDEBAR
══════════════════════════════════════════════ -->
<aside id="sidebar"
       class="fixed top-0 left-0 h-full w-64 bg-white dark:bg-slate-900
         border-r border-slate-200 dark:border-slate-800
         z-30 flex flex-col
         -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">

    <!-- Logo -->
    <div class="h-16 flex items-center px-5 border-b border-slate-100 dark:border-slate-800 flex-shrink-0">
        <div class="flex items-center gap-2.5">
            @if(isset($basic_info->black_logo))
                <div class="w-auto h-auto p-3">
                    <img src="{{ asset('/storage/'. $basic_info->black_logo) }}" alt="">
                </div>
            @else
                <span
                    class="font-extrabold text-lg text-ink dark:text-white tracking-tight">{{ $basic_info->platform_name ?? 'Genzilla' }}</span>
                <span
                    class="text-[9px] font-bold px-1.5 py-0.5 bg-primarylt dark:bg-primary/15 text-primary rounded-full"> {{ auth()->user()->getRoleNames()->implode(', ') }}</span>
            @endif
        </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500 px-3 pb-2">
            Platform Core</p>

        <a href="{{ route('admin.dashboard.index') }}" class="nav-link active">
            <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.8">
                <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                <rect x="14" y="14" width="7" height="7" rx="1.5"/>
            </svg>
            Dashboard
        </a>
        <!-- Landing Page CMS -->
        @canany(['Manage Hero', 'Manage Features', 'Manage Steps', 'Manage Use Cases', 'Manage Store Demos', 'Manage Testimonials', 'Manage Faqs', 'Manage Matrix'])
            <div class="nav-group">
                <button type="button" class="nav-link nav-toggle w-full text-left" aria-expanded="false"
                        onclick="toggleGroup(this)">
                    <i class="fa-solid fa-laptop-code"></i>
                    <span>Landing Page CMS</span>
                    <svg class="chev w-3.5 h-3.5 ml-auto shrink-0 text-slate-400 transition-transform duration-200"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>

                <div class="submenu">
                    <div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                        @can('Manage Hero')
                            <a href="{{ route('admin.hero.index') }}" class="sub-link">Hero Section</a>
                        @endcan

                        @can('Manage Matrix')
                            <a href="{{ route('admin.matrix.index') }}" class="sub-link">Platform Metrics</a>
                        @endcan

                        @can('Manage Faqs')
                            <a href="{{ route('admin.faqs.index') }}" class="sub-link"> FAQs</a>
                        @endcan

                        @can('Manage Testimonials')
                            <a href="{{ route('admin.testimonials.index') }}" class="sub-link">Testimonials</a>
                        @endcan

                        @can('Manage Store Demos')
                            <a href="{{ route('admin.store-demos.index') }}" class="sub-link">Store Demos</a>
                        @endcan

                        @can('Manage Use Cases')
                            <a href="{{ route('admin.use-cases.index') }}" class="sub-link">Use Cases</a>
                        @endcan

                        @can('Manage Steps')
                            <a href="{{ route('admin.steps.index') }}" class="sub-link">How It Works</a>
                        @endcan

                        @can('Manage Features')
                            <a href="{{ route('admin.features.index') }}" class="sub-link">Features</a>
                        @endcan
                    </div>
                </div>
            </div>
        @endcanany

        <!-- Plans & Subscriptions -->
        @canany(['Manage Plans', 'Manage Subscriptions', 'Manage Invoices'])
            <div class="nav-group">
                <button type="button" class="nav-link nav-toggle w-full text-left" aria-expanded="false"
                        onclick="toggleGroup(this)">
                    <i class="fa-solid fa-credit-card"></i>
                    <span>Plans & Billing</span>
                    <svg class="chev w-3.5 h-3.5 ml-auto shrink-0 text-slate-400 transition-transform duration-200"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
                <div class="submenu">
                    <div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                        @can('Manage Plans')
                            <a href="{{ route('admin.plans.index') }}" class="sub-link">Subscription Plans</a>
                        @endcan

                        @can('Manage Subscriptions')
                            <a href="{{ route('admin.subscriptions.index') }}" class="sub-link">Merchant
                                Subscriptions</a>
                        @endcan

                        @can('Manage Invoices')
                            <a href="{{ route('admin.invoices.index') }}" class="sub-link">Invoices</a>
                        @endcan
                    </div>
                </div>
            </div>
        @endcanany

        <!-- Tenant Management -->
        @canany(['Manage Domains', 'Manage Usages'])
            <div class="nav-group">
                <button type="button" class="nav-link nav-toggle w-full text-left" aria-expanded="false"
                        onclick="toggleGroup(this)">
                    <i class="fa-solid fa-store"></i>
                    <span>Tenant Operations</span>
                    <svg class="chev w-3.5 h-3.5 ml-auto shrink-0 text-slate-400 transition-transform duration-200"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
                <div class="submenu">
                    <div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                        @can('Manage Domains')
                            <a href="{{ route('admin.domains.index') }}" class="sub-link">Domains & SSL</a>
                        @endcan

                        @can('Manage Usages')
                            <a href="{{ route('admin.usages.index') }}" class="sub-link">Resource Usage</a>
                        @endcan
                    </div>
                </div>
            </div>
        @endcanany

        <!-- Content & Support -->
        @canany(['Manage Pages', 'Manage Blog', 'Manage Blog Categories', 'Manage Contacts', 'Manage Newsletter'])
            <div class="nav-group">
                <button type="button" class="nav-link nav-toggle w-full text-left" aria-expanded="false"
                        onclick="toggleGroup(this)">
                    <i class="fa-solid fa-folder-open"></i>
                    <span>Content & Support</span>
                    <svg class="chev w-3.5 h-3.5 ml-auto shrink-0 text-slate-400 transition-transform duration-200"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
                <div class="submenu">
                    <div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                        @can('Manage Pages')
                            <a href="{{ route('admin.pages.index') }}" class="sub-link">Custom Pages</a>
                        @endcan

                        @can('Manage Blog')
                            <a href="{{ route('admin.blog.index') }}" class="sub-link">Blog Posts</a>
                        @endcan

                        @can('Manage Blog Categories')
                            <a href="{{ route('admin.blog-categories.index') }}" class="sub-link"> Blog Categories</a>
                        @endcan

                        @can('Manage Contacts')
                            <a href="{{ route('admin.contacts.index') }}" class="sub-link">Contact Messages</a>
                        @endcan

                        @can('Manage Newsletter')
                            <a href="{{ route('admin.newsletter.index') }}" class="sub-link">Newsletter Subs</a>
                        @endcan
                    </div>
                </div>
            </div>
        @endcanany


        <div class="!my-4 border-t border-slate-100 dark:border-slate-800"></div>

        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500 px-3 pb-2">
            System</p>
        @canany(['Admin List', 'Role List', 'Permission List'])
            <div class="nav-group">
                <button type="button" class="nav-link nav-toggle w-full text-left" aria-expanded="false"
                        onclick="toggleGroup(this)">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>Admin Security</span>
                    <svg class="chev w-3.5 h-3.5 ml-auto shrink-0 text-slate-400 transition-transform duration-200"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
                <div class="submenu">
                    <div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                        @can('Admin List')
                            <a href="{{ route('admin.admins.index') }}" class="sub-link">Admin Users</a>
                        @endcan

                        @can('Role List')
                            <a href="{{ route('admin.role.index') }}" class="sub-link">Roles</a>
                        @endcan

                        @can('Permission List')
                            <a href="{{ route('admin.permission.index') }}" class="sub-link">Permissions</a>
                        @endcan
                    </div>
                </div>
            </div>
        @endcanany

        @can('General Setting')
            <a href="{{ route('admin.settings.index') }}" class="nav-link">
                <i class="fa-solid fa-sliders"></i>
                Settings
            </a>
        @endcan


    </nav>

    <!-- User -->
    <div class="p-3 border-t border-slate-100 dark:border-slate-800 relative" id="side-profile-menu">

        <!-- Dropdown (opens upward) -->
        <div id="side-profile-dropdown" role="menu"
             class="hidden absolute left-3 right-3 bottom-full mb-2 card shadow-lg p-1.5 z-40">
            <a role="menuitem"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-primarylt dark:hover:bg-slate-800 hover:text-primary cursor-pointer transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Profile
            </a>
            <a role="menuitem"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 cursor-pointer transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Logout
            </a>
        </div>

        <button type="button" id="side-profile-btn" onclick="toggleSideProfile(event)"
                aria-haspopup="true" aria-expanded="false"
                class="w-full flex items-center gap-3 px-2.5 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors text-left">
            <img src="https://i.pravatar.cc/40?img=68"
                 class="w-8 h-8 rounded-full object-cover ring-2 ring-primary/30 shrink-0" alt="Alex"/>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-ink dark:text-white truncate">Alex Johnson</p>
                <p class="text-xs text-slate-400 truncate">Super Admin</p>
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
