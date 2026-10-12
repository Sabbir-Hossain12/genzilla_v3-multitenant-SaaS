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
            <div class="w-8 h-8 bg-primary rounded-lg grid place-items-center shadow-sm shadow-primary/30">
                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
            </div>
            <span class="font-extrabold text-lg text-ink dark:text-white tracking-tight">Nexus</span>
            <span class="text-[9px] font-bold px-1.5 py-0.5 bg-primarylt dark:bg-primary/15 text-primary rounded-full">ADMIN</span>
        </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500 px-3 pb-2">Overview</p>

        <a class="nav-link active">
            <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            Dashboard
        </a>

        <div class="nav-group">
            <button type="button" class="nav-link nav-toggle w-full text-left" aria-expanded="false" onclick="toggleGroup(this)">
                <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Users</span>
                <span class="ml-auto text-[10px] font-bold bg-primary/10 text-primary px-1.5 py-0.5 rounded-full">2.4k</span>
                <svg class="chev w-3.5 h-3.5 ml-2 shrink-0 text-slate-400 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
            <div class="submenu"><div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                    <a class="sub-link">All Users</a>
                    <div class="nav-group">
                        <button type="button" class="sub-link nav-toggle w-full text-left" aria-expanded="false" onclick="toggleGroup(this)">
                            <span>Roles &amp; Permissions</span>
                            <svg class="chev w-3.5 h-3.5 ml-2 shrink-0 text-slate-400 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="submenu"><div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                                <a class="sub-link">Admins</a>
                                <a class="sub-link">Editors</a>
                                <a class="sub-link">Viewers</a>
                            </div></div>
                    </div>
                    <a class="sub-link">Invitations</a>
                </div></div>
        </div>

        <div class="nav-group">
            <button type="button" class="nav-link nav-toggle w-full text-left" aria-expanded="false" onclick="toggleGroup(this)">
                <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span>Orders</span>
                <span class="ml-auto w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                <svg class="chev w-3.5 h-3.5 ml-2 shrink-0 text-slate-400 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
            <div class="submenu"><div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                    <a class="sub-link">All Orders</a>
                    <a class="sub-link">Pending</a>
                    <a class="sub-link">Refunds</a>
                </div></div>
        </div>

        <div class="nav-group">
            <button type="button" class="nav-link nav-toggle w-full text-left" aria-expanded="false" onclick="toggleGroup(this)">
                <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <span>Analytics</span>
                <svg class="chev w-3.5 h-3.5 ml-auto shrink-0 text-slate-400 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
            <div class="submenu"><div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                    <a class="sub-link">Traffic</a>
                    <a class="sub-link">Sales</a>
                    <div class="nav-group">
                        <button type="button" class="sub-link nav-toggle w-full text-left" aria-expanded="false" onclick="toggleGroup(this)">
                            <span>Audience</span>
                            <svg class="chev w-3.5 h-3.5 ml-auto shrink-0 text-slate-400 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="submenu"><div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                                <a class="sub-link">Demographics</a>
                                <a class="sub-link">Behavior</a>
                            </div></div>
                    </div>
                </div></div>
        </div>

        <div class="nav-group">
            <button type="button" class="nav-link nav-toggle w-full text-left" aria-expanded="false" onclick="toggleGroup(this)">
                <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                <span>Payments</span>
                <svg class="chev w-3.5 h-3.5 ml-auto shrink-0 text-slate-400 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
            <div class="submenu"><div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                    <a class="sub-link">Transactions</a>
                    <a class="sub-link">Invoices</a>
                    <a class="sub-link">Payouts</a>
                </div></div>
        </div>

        <a class="nav-link">
            <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Reports
        </a>

        <div class="!my-4 border-t border-slate-100 dark:border-slate-800"></div>

        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500 px-3 pb-2">System</p>

        <div class="nav-group">
            <button type="button" class="nav-link nav-toggle w-full text-left" aria-expanded="false" onclick="toggleGroup(this)">
                <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                <span>Settings</span>
                <svg class="chev w-3.5 h-3.5 ml-auto shrink-0 text-slate-400 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
            <div class="submenu"><div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                    <a class="sub-link">General</a>
                    <a class="sub-link">Security</a>
                    <div class="nav-group">
                        <button type="button" class="sub-link nav-toggle w-full text-left" aria-expanded="false" onclick="toggleGroup(this)">
                            <span>Notifications</span>
                            <svg class="chev w-3.5 h-3.5 ml-auto shrink-0 text-slate-400 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="submenu"><div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                                <a class="sub-link">Email</a>
                                <a class="sub-link">Push</a>
                                <a class="sub-link">SMS</a>
                            </div></div>
                    </div>
                </div></div>
        </div>

        <div class="nav-group">
            <button type="button" class="nav-link nav-toggle w-full text-left" aria-expanded="false" onclick="toggleGroup(this)">
                <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/></svg>
                <span>Integrations</span>
                <svg class="chev w-3.5 h-3.5 ml-auto shrink-0 text-slate-400 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
            <div class="submenu"><div class="submenu-inner space-y-0.5 pt-0.5 pb-1">
                    <a class="sub-link">API Keys</a>
                    <a class="sub-link">Webhooks</a>
                    <a class="sub-link">Connected Apps</a>
                </div></div>
        </div>
    </nav>

    <!-- User -->
    <div class="p-3 border-t border-slate-100 dark:border-slate-800 relative" id="side-profile-menu">

        <!-- Dropdown (opens upward) -->
        <div id="side-profile-dropdown" role="menu"
             class="hidden absolute left-3 right-3 bottom-full mb-2 card shadow-lg p-1.5 z-40">
            <a role="menuitem" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-primarylt dark:hover:bg-slate-800 hover:text-primary cursor-pointer transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Profile
            </a>
            <a role="menuitem" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 cursor-pointer transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Logout
            </a>
        </div>

        <button type="button" id="side-profile-btn" onclick="toggleSideProfile(event)"
                aria-haspopup="true" aria-expanded="false"
                class="w-full flex items-center gap-3 px-2.5 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors text-left">
            <img src="https://i.pravatar.cc/40?img=68" class="w-8 h-8 rounded-full object-cover ring-2 ring-primary/30 shrink-0" alt="Alex"/>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-ink dark:text-white truncate">Alex Johnson</p>
                <p class="text-xs text-slate-400 truncate">Super Admin</p>
            </div>
            <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
        </button>
    </div>
</aside>
