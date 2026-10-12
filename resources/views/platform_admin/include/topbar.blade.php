
<!-- TOP BAR -->
<header class="sticky top-0 z-10 h-16 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md
                 border-b border-slate-200 dark:border-slate-800
                 flex items-center px-4 md:px-6 gap-3">

    <!-- Hamburger -->
    <button onclick="openSidebar()"
            class="lg:hidden w-9 h-9 grid place-items-center rounded-xl
             hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shrink-0">
        <svg class="w-5 h-5 text-slate-500 dark:text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    <!-- Title -->
    <div class="hidden md:block">
        <p class="text-base font-bold text-ink dark:text-white leading-none">Dashboard</p>
        <p class="text-xs text-slate-400 mt-0.5">Monday, 12 Oct 2026</p>
    </div>

    <!-- Search -->
    <div class="flex-1 max-w-xs mx-auto md:mx-0 md:ml-6">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" placeholder="Search anything…"
                   class="w-full pl-9 pr-4 py-2 text-sm rounded-xl
                 bg-slate-100 dark:bg-slate-800
                 border border-transparent focus:border-primary dark:focus:border-primary
                 text-ink dark:text-slate-100 placeholder:text-slate-400
                 focus:outline-none transition-colors"/>
        </div>
    </div>

    <!-- Right actions -->
    <div class="flex items-center gap-1 ml-auto">

        <!-- Dark toggle -->
        <button onclick="toggleDark()" title="Toggle dark mode"
                class="w-9 h-9 grid place-items-center rounded-xl
               hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors
               text-slate-500 dark:text-slate-400">
            <svg id="icon-moon" class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
            <svg id="icon-sun" class="w-[18px] h-[18px] hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
        </button>

        <!-- Notifications -->
        <button class="relative w-9 h-9 grid place-items-center rounded-xl
                     hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors
                     text-slate-500 dark:text-slate-400">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-[1.5px] border-white dark:border-slate-900"></span>
        </button>

        <!-- Divider -->
        <div class="w-px h-6 bg-slate-200 dark:bg-slate-700 mx-1"></div>

        <!-- Avatar -->
{{--        <div class="flex items-center gap-2 cursor-pointer group">--}}
{{--            <img src="https://i.pravatar.cc/40?img=68"--}}
{{--                 class="w-8 h-8 rounded-full object-cover ring-2 ring-primary/40" alt="Alex"/>--}}
{{--            <div class="hidden sm:block">--}}
{{--                <p class="text-xs font-semibold text-ink dark:text-white leading-none">Alex J.</p>--}}
{{--                <p class="text-[10px] text-slate-400">Super Admin</p>--}}
{{--            </div>--}}
{{--        </div>--}}

        <!-- Avatar + dropdown -->
        <div class="relative" id="profile-menu">
            <button type="button" id="profile-btn" onclick="toggleProfileMenu(event)"
                    aria-haspopup="true" aria-expanded="false"
                    class="flex items-center gap-2 rounded-xl p-1 pr-2 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <img src="https://i.pravatar.cc/40?img=68"
                     class="w-8 h-8 rounded-full object-cover ring-2 ring-primary/40" alt="Alex"/>
                <div class="hidden sm:block text-left">
                    <p class="text-xs font-semibold text-ink dark:text-white leading-none">Alex J.</p>
                    <p class="text-[10px] text-slate-400">Super Admin</p>
                </div>
                <svg id="profile-chev" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>

            <div id="profile-dropdown" role="menu"
                 class="hidden absolute right-0 top-full mt-2 w-56 card shadow-lg p-1.5 z-40">
                <div class="px-3 py-2.5 border-b border-slate-100 dark:border-slate-800 mb-1">
                    <p class="text-sm font-semibold text-ink dark:text-white truncate">Alex Johnson</p>
                    <p class="text-xs text-slate-400 truncate">alex@nexus.com</p>
                </div>
                <a role="menuitem" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-primarylt dark:hover:bg-slate-800 hover:text-primary cursor-pointer transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Profile
                </a>
                <a role="menuitem" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 cursor-pointer transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Logout
                </a>
            </div>
        </div>
    </div>
</header>
