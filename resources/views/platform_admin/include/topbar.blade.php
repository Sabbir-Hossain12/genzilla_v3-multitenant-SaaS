
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
        <div class="flex items-center gap-2 cursor-pointer group">
            <img src="https://i.pravatar.cc/40?img=68"
                 class="w-8 h-8 rounded-full object-cover ring-2 ring-primary/40" alt="Alex"/>
            <div class="hidden sm:block">
                <p class="text-xs font-semibold text-ink dark:text-white leading-none">Alex J.</p>
                <p class="text-[10px] text-slate-400">Super Admin</p>
            </div>
        </div>
    </div>
</header>
