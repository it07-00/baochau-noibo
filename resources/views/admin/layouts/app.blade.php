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
                    @if(!($fullWidth ?? false))
                    <div class="page-header pb-7">
                        <h2 class="fw-semibold fs-7">@yield('page_title', 'Bảng điều khiển')</h2>
                        @include('admin.partials.breadcrumb')
                    </div>
                    @endif

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
