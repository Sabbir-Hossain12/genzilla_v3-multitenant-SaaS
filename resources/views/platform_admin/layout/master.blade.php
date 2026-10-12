<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
    <title>@yield('title','Admin')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300..800&display=swap"
          rel="stylesheet"/>
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
        document.getElementById('icon-moon').classList.toggle('hidden', isDark);
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
