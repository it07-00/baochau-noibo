<!DOCTYPE html>
<html lang="vi" dir="ltr">
@include('admin.partials.head')
<body>
    <div class="page-layout">
        @include('admin.partials.header')

        @include('admin.partials.sidebar')

        <main class="app-wrapper">
            <div class="{{ $fullWidth ?? false ? 'w-100 p-0' : 'container-fluid py-4' }}">
                <div class="page-content">
                    @yield('content')
                    {{ $slot ?? '' }}
                </div>
            </div>
        </main>

        @include('admin.partials.footer')
    </div>

    @include('admin.partials.scripts')
    @livewireScripts
    @stack('scripts')
</body>
</html>
