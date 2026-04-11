<!DOCTYPE html>
<html lang="vi" dir="ltr">
@include('admin.partials.head')
<body class="theme-auto">
    <div class="app-main">
        <div id="app-wrapper" class="app-wrapper d-flex flex-column align-items-stretch min-vh-100">
            @include('admin.partials.sidebar')

            @include('admin.partials.header')

            <div class="app-content-wrapper {{ $fullWidth ?? false ? 'pt-4 pb-0 px-0' : 'pt-13 pb-13 px-5' }}">
                <div class="{{ $fullWidth ?? false ? 'w-100 p-0' : 'container-fluid' }}">
                    <style>
                        .page-header > h1,
                        .page-header > h2,
                        .page-header > h3,
                        .page-header > h4,
                        .page-header > h5,
                        .page-header > h6,
                        .page-header nav[aria-label="breadcrumb"],
                        .page-header .breadcrumb {
                            display: none !important;
                        }

                        .page-header > div:has(> h1),
                        .page-header > div:has(> h2),
                        .page-header > div:has(> h3),
                        .page-header > div:has(> h4),
                        .page-header > div:has(> h5),
                        .page-header > div:has(> h6) {
                            display: none !important;
                        }
                    </style>

                    <div class="page-content">
                        @yield('content')
                        {{ $slot ?? '' }}
                    </div>
                </div>
            </div>

            @include('admin.partials.footer')
            <div class="app-backdrop"></div>
        </div>
    </div>

    @include('admin.partials.scripts')
    @livewireScripts
    @stack('scripts')
</body>
</html>
