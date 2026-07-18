<head>
    <script>
        (function () {
            const getCookie = (name) => {
                const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
                return match ? match[2] : null;
            };
            const theme = getCookie('theme') || 'auto';
            const resolvedTheme = theme === 'auto'
                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                : theme;
            document.documentElement.setAttribute('data-bs-theme', resolvedTheme);
            document.documentElement.setAttribute('data-app-sidebar', 'full');
            document.documentElement.setAttribute('data-color-theme', 'blue');
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Bảng điều khiển') | {{ config('app.name', 'Môi trường Bảo Châu') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

    <!-- Theme Required CSS -->
    <link rel="stylesheet" href="{{ asset('assets/libs/flaticon/css/all/all.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/lucide/lucide.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/fontawesome/css/all.min.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/simplebar/simplebar.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/node-waves/waves.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-select/css/bootstrap-select.min.css') }}?v={{ config('app.version') }}">

    <!-- Theme CSS Stylesheet -->
    <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/datatables/datatables.min.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dept-race-board.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/sales-race-board.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/sweetalert2.css') }}?v={{ config('app.version') }}">

    @stack('styles')
    @livewireStyles
</head>
