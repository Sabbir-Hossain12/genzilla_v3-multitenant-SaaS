@php
    $currentRoute = request()->route()?->getName() ?? '';
    $isActive = fn ($prefixes) => \Illuminate\Support\Str::startsWith($currentRoute, $prefixes);
@endphp
<div id="overlay" onclick="closeSidebar()" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-20 hidden lg:hidden"></div>
<aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 z-30 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="h-16 flex items-center px-5 border-b border-slate-100 dark:border-slate-800">
        <span class="font-extrabold text-lg text-ink dark:text-white tracking-tight">{{ auth()->user()->tenant->store_name ?? 'Store Admin' }}</span>
    </div>
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        @can('Store Dashboard')
            <a href="{{ route('store.admin.dashboard') }}" class="nav-link {{ $isActive('store.admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
        @endcan
        @can('Store Role List')
            <a href="{{ route('store.admin.role.index') }}" class="nav-link {{ $isActive('store.admin.role.') ? 'active' : '' }}">
                <i class="fa-solid fa-user-shield"></i> Roles & Permissions
            </a>
        @endcan
    </nav>
    <div class="p-3 border-t border-slate-100 dark:border-slate-800">
        <div class="px-2.5 py-2">
            <p class="text-sm font-semibold text-ink dark:text-white truncate">{{ auth()->user()->name }}</p>
            <p class="text-xs text-slate-400 truncate">{{ auth()->user()->getRoleNames()->implode(', ') }}</p>
        </div>
        <form method="post" action="{{ route('store.admin.logout') }}">
            @csrf
            <button type="submit" class="nav-link w-full text-left text-red-500"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
        </form>
    </div>
</aside>
