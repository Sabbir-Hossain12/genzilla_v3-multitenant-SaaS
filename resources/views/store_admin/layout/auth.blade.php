<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Log In — {{ $basic_info->platform_name ?? '' }}</title>
    @vite(['resources/css/platform_admin.css'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
          rel="stylesheet">
    @vite(['resources/css/platform_admin.css','resources/js/platform_admin.js'])

    @stack('css')
</head>

<body class="bg-white text-ink font-sans antialiased">
<div class="min-h-screen grid lg:grid-cols-2">
    @yield('contents')
</div>

<script>
    function togglePw(id, btn) {
        const input = document.getElementById(id);
        const isPw = input.type === 'password';
        input.type = isPw ? 'text' : 'password';
        btn.classList.toggle('text-primary', isPw);
    }
</script>

{{-- Toastr--}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if (Session::has('success'))
            toastr.options = {"closeButton": true, "progressBar": true};
        toastr.success("{{ session('success') }}");
        @endif

            @if (Session::has('error'))
            toastr.options = {"closeButton": true, "progressBar": true};
        toastr.error("{{ session('error') }}");
        @endif

            @if (Session::has('info'))
            toastr.options = {"closeButton": true, "progressBar": true};
        toastr.info("{{ session('info') }}");
        @endif

            @if (Session::has('warning'))
            toastr.options = {"closeButton": true, "progressBar": true};
        toastr.warning("{{ session('warning') }}");
        @endif
    });
</script>

@stack('js')
</body>
</html>
