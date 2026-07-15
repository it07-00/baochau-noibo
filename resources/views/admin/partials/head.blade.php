<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">

    <title>@yield('title', 'Bảng điều khiển') | {{ config('app.name', 'Môi trường Bảo Châu') }}</title>

    <link rel="icon" href="{{ asset('assets/images/favicon-32x32.png') }}" sizes="32x32" type="image/png">
    <link rel="icon" href="{{ asset('assets/images/favicon-192x192.png') }}" sizes="192x192" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/apple-touch-icon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
        rel="stylesheet">

    <!-- Vuexy Icons and Stylesheets -->
    <link rel="stylesheet" href="{{ asset('vuexy/css/iconify-icons.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('vuexy/css/node-waves.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('vuexy/css/pickr-themes.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('vuexy/css/core.css') }}?v={{ config('app.version') }}"
        class="template-customizer-core-css">
    <link rel="stylesheet" href="{{ asset('vuexy/css/demo.css') }}?v={{ config('app.version') }}"
        class="template-customizer-theme-css">
    <link rel="stylesheet" href="{{ asset('vuexy/css/perfect-scrollbar.css') }}?v={{ config('app.version') }}">

    <!-- Existing Icons & Custom Stylesheets -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome-all.min.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/css/sweetalert2.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/animate.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/conca.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/css/dept-race-board.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/css/sales-race-board.css') }}?v={{ config('app.version') }}">

    <!-- Vuexy Helpers (Must be loaded in head) -->
    <script src="{{ asset('vuexy/js/helpers.js') }}?v={{ config('app.version') }}"></script>
    <script src="{{ asset('vuexy/js/template-customizer.js') }}?v={{ config('app.version') }}"></script>
    <script src="{{ asset('vuexy/js/config.js') }}?v={{ config('app.version') }}"></script>

    @stack('styles')
    @livewireStyles
</head>