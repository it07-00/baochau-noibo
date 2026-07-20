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

            const desktopDensity = window.matchMedia('(min-width: 992px)');
            const resetDensity = () => {
                document.documentElement.style.zoom = '100%';
                document.documentElement.style.removeProperty('width');
                document.documentElement.style.minHeight = '100%';
                document.body?.style.removeProperty('min-height');
                document.querySelector('.page-layout')?.style.removeProperty('min-height');
                document.querySelector('.app-menubar')?.style.removeProperty('height');
            };
            const applyDensity = () => {
                if (!desktopDensity.matches) {
                    resetDensity();
                    return;
                }

                document.documentElement.style.zoom = '80%';
                document.documentElement.style.removeProperty('width');
                document.documentElement.style.minHeight = '125vh';
                document.body?.style.setProperty('min-height', '125vh');
                document.querySelector('.page-layout')?.style.setProperty('min-height', '125vh');
                document.querySelector('.app-menubar')?.style.setProperty('height', '125vh');
            };

            const syncModalViewport = (modal = document.querySelector('.modal.show')) => {
                const compactHeight = desktopDensity.matches ? '125vh' : '100vh';

                modal?.style.setProperty('height', compactHeight);
                document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
                    backdrop.style.setProperty('height', compactHeight);
                    backdrop.style.setProperty('min-height', compactHeight);
                });
            };

            document.addEventListener('DOMContentLoaded', applyDensity);
            document.addEventListener('show.bs.modal', (event) => {
                window.requestAnimationFrame(() => syncModalViewport(event.target));
            });
            document.addEventListener('shown.bs.modal', (event) => syncModalViewport(event.target));
            desktopDensity.addEventListener('change', () => {
                applyDensity();
                syncModalViewport();
            });
            window.addEventListener('beforeprint', resetDensity);
            window.addEventListener('afterprint', applyDensity);
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
