 @include('platform_admin.include.meta-titles')

<body class="pace-done sidebar-enable" data-sidebar-size="lg">

<!-- Begin page -->
<div id="layout-wrapper">
    @include('platform_admin.include.topbar')

    @include('platform_admin.include.sidebar')
    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                @yield('contents')
            </div>
        </div>

        @include('platform_admin.include.footer')
    </div>
</div>
    <!-- JAVASCRIPT -->
    @include('platform_admin.include.scripts')

<script>
    @if (Session::has('success'))
        toastr.options = {
        "closeButton": true,
        "progressBar": true
    }
    toastr.success("{{ session('success') }}");
    @endif

            @if (Session::has('error'))
        toastr.options = {
        "closeButton": true,
        "progressBar": true
    }
    toastr.error("{{ session('error') }}");
    @endif

            @if (Session::has('info'))
        toastr.options = {
        "closeButton": true,
        "progressBar": true
    }
    toastr.info("{{ session('info') }}");
    @endif

            @if (Session::has('warning'))
        toastr.options = {
        "closeButton": true,
        "progressBar": true
    }
    toastr.warning("{{ session('warning') }}");
    @endif
</script>
</body>
</html>
