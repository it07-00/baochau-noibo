<head>
    <script>
        (function () {
            const theme = localStorage.getItem('conca_theme') || 'auto';
            const resolvedTheme = theme === 'auto'
                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                : theme;
            document.documentElement.setAttribute('data-bs-theme', resolvedTheme);
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Bảng điều khiển') | {{ config('app.name', 'Môi trường Bảo Châu') }}</title>

    <link rel="icon" href="{{ asset('assets/images/favicon-32x32.png') }}" sizes="32x32" type="image/png">
    <link rel="icon" href="{{ asset('assets/images/favicon-192x192.png') }}" sizes="192x192" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/apple-touch-icon.png') }}">
    <link id="bootstrap-css" rel="stylesheet" type="text/css"
        href="{{ asset('assets/css/bootstrap.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome-all.min.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/css/perfect-scrollbar.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/conca.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/css/sweetalert2.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/animate.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/css/dept-race-board.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/css/sales-race-board.css') }}?v={{ config('app.version') }}">

    @stack('styles')
    @livewireStyles
</head>