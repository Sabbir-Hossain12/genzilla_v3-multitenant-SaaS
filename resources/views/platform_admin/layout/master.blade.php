<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
    <title>@yield('title','Admin')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300..800&display=swap"
          rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css" />
    @vite(['resources/css/platform_admin.css'])
    @stack('css')
</head>

<body class="bg-slate-50 dark:bg-slate-950 text-ink dark:text-slate-100 antialiased">

@include('platform_admin.include.sidebar')

<!-- ══════════════════════════════════════════════
     MAIN WRAPPER
══════════════════════════════════════════════ -->
<div class="lg:pl-64 flex flex-col min-h-screen">
    @include('platform_admin.include.topbar')

    <!-- ══════ PAGE CONTENT ══════ -->
    <main class="flex-1 p-4 md:p-6 xl:p-8 space-y-6">
        @yield('contents')
    </main>

    @include('platform_admin.include.footer')

</div><!-- /main wrapper -->

<script>
    /* ── Dark mode ── */
    function toggleDark() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('nexus-theme', isDark ? 'dark' : 'light');
        document.getElementById('icon-moon').classList.toggle('hidden',  isDark);
        document.getElementById('icon-sun').classList.toggle('hidden', !isDark);
    }

    (function initTheme() {
        const saved = localStorage.getItem('nexus-theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (saved === 'dark' || (!saved && prefersDark)) {
            document.documentElement.classList.add('dark');
            document.getElementById('icon-moon').classList.add('hidden');
            document.getElementById('icon-sun').classList.remove('hidden');
        }
    })();


    /* ── Multi-level menu ── */
    function setOpen(btn, open) {
        btn.setAttribute('aria-expanded', open);
        btn.nextElementSibling.classList.toggle('open', open);
    }
    function toggleGroup(btn) {
        const open = btn.getAttribute('aria-expanded') !== 'true';
        // accordion: close sibling groups at the same level
        if (open) {
            btn.closest('.nav-group').parentElement.querySelectorAll(':scope > .nav-group > .nav-toggle')
                .forEach(b => { if (b !== btn) setOpen(b, false); });
        }
        setOpen(btn, open);
    }
    function activate(el) {
        const nav = document.querySelector('#sidebar nav');
        nav.querySelectorAll('.active').forEach(a => a.classList.remove('active'));
        nav.querySelectorAll('.has-active').forEach(a => a.classList.remove('has-active'));
        el.classList.add('active');
        let g = el.closest('.nav-group');
        while (g) {
            const t = g.querySelector(':scope > .nav-toggle');
            t.classList.add('has-active');
            setOpen(t, true);
            g = g.parentElement.closest('.nav-group');
        }
    }
    document.querySelectorAll('#sidebar nav a').forEach(a =>
        a.addEventListener('click', () => {
            activate(a);
            if (window.innerWidth < 1024 && a.classList.contains('sub-link')) closeSidebar();
        })
    );


    /* ── Profile dropdown ── */
    function setProfileMenu(open) {
        document.getElementById('profile-dropdown').classList.toggle('hidden', !open);
        document.getElementById('profile-btn').setAttribute('aria-expanded', open);
        document.getElementById('profile-chev').style.transform = open ? 'rotate(180deg)' : '';
    }
    function toggleProfileMenu(e) {
        e.stopPropagation();
        setSideProfile(false);
        setProfileMenu(document.getElementById('profile-dropdown').classList.contains('hidden'));
    }
    /* sidebar profile dropdown */
    function setSideProfile(open) {
        document.getElementById('side-profile-dropdown').classList.toggle('hidden', !open);
        document.getElementById('side-profile-btn').setAttribute('aria-expanded', open);
    }
    function toggleSideProfile(e) {
        e.stopPropagation();
        const open = document.getElementById('side-profile-dropdown').classList.contains('hidden');
        if (open) setProfileMenu(false);
        setSideProfile(open);
    }
    document.addEventListener('click', e => {
        if (!document.getElementById('profile-menu').contains(e.target)) setProfileMenu(false);
        if (!document.getElementById('side-profile-menu').contains(e.target)) setSideProfile(false);
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { setProfileMenu(false); setSideProfile(false); }
    });

    /* ── Sidebar (mobile) ── */
    function openSidebar() {
        document.getElementById('sidebar').classList.remove('-translate-x-full');
        document.getElementById('overlay').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.add('-translate-x-full');
        document.getElementById('overlay').classList.add('hidden');
        document.body.style.overflow = '';
    }
</script>
@stack('js')
</body>
</html>
