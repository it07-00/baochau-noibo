@php
    $currentUser = auth()->user();

    $primaryRole = collect(\App\Enums\Role::priorityList())
        ->first(fn ($r) => $currentUser?->hasRole($r));
    if (!$primaryRole) {
        $primaryRole = $currentUser?->roles?->first()?->name;
    }

    $roleEnum  = \App\Enums\Role::tryFrom($primaryRole ?? '');
    $roleLabel = $roleEnum?->label() ?? 'Nhân viên';
    $roleColor = $roleEnum?->color() ?? '#64748b';

    $weekdays = ['Chủ nhật','Thứ hai','Thứ ba','Thứ tư','Thứ năm','Thứ sáu','Thứ bảy'];
    $todayLabel = $weekdays[now()->dayOfWeek] . ', ' . now()->format('d/m/Y');
@endphp

<div class="app-header app-header-redesign">
    {{-- LEFT: Hamburger + Greeting --}}
    <div class="app-header-left-group">
        <button type="button" class="app-header-bar-btn app-sidebar-open-btn d-none d-xl-inline-flex align-items-center justify-content-center flex-shrink-0" >
            <span></span><span></span><span></span>
        </button>
        <button type="button" class="app-header-bar-btn app-sidebar-mobile-open d-xl-none d-inline-flex align-items-center justify-content-center flex-shrink-0" >
            <span></span><span></span><span></span>
        </button>

        @php
            $hour = now()->hour;
            $wish = match(true) {
                $hour >= 5  && $hour < 11 => 'Chúc bạn buổi sáng làm việc hiệu quả! ☀️',
                $hour >= 11 && $hour < 13 => 'Chúc bạn buổi trưa vui vẻ! 🌤️',
                $hour >= 13 && $hour < 18 => 'Chúc bạn buổi chiều làm việc tốt lành! 🌿',
                $hour >= 18 && $hour < 22 => 'Chúc bạn buổi tối thư giãn! 🌙',
                default                   => 'Chúc bạn một ngày tốt lành! ✨',
            };
        @endphp
        <div class="app-header-greeting d-none d-sm-flex">
            <span class="app-header-greeting-name">{{ $currentUser?->name ?? 'Người dùng' }}</span>
            <span class="app-header-greeting-sub">{{ $wish }}</span>
        </div>

        <span class="app-header-role-badge d-none d-md-inline-flex"
              style="--role-c:{{ $roleColor }};">
            <svg width="9" height="9" viewBox="0 0 9 9" fill="currentColor"><circle cx="4.5" cy="4.5" r="4.5"/></svg>
            {{ $roleLabel }}
        </span>
    </div>

    {{-- CENTER: Date --}}
    <div class="app-header-date d-none d-lg-flex">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="1.5" y="2.5" width="13" height="12" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
            <path d="M5 1v3M11 1v3M1.5 6.5h13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        {{ $todayLabel }}
    </div>

    {{-- RIGHT: Actions --}}
    <div class="app-header-right-group">
        <ul class="navbar-nav flex-row align-items-center gap-1">
                <li class="header-nav-item header-style-switcher me-2">
                    <a class="header-nav-link" href="javascript:void(0);" data-bs-toggle="dropdown" title="Giao diện">
                        <span class="d-flex align-items-center justify-content-center theme-icon light-icon">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.75 0.75V1.98077M8.75 15.5192V16.75M16.75 8.75H15.5192M1.98077 8.75H0.75M14.4115 3.08846L13.5377 3.96231M3.96231 13.5377L3.08846 14.4115M14.4115 14.4115L13.5377 13.5377M3.96231 3.96231L3.08846 3.08846M12.75 8.75C12.75 10.9591 10.9591 12.75 8.75 12.75C6.54086 12.75 4.75 10.9591 4.75 8.75C4.75 6.54086 6.54086 4.75 8.75 4.75C10.9591 4.75 12.75 6.54086 12.75 8.75Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                        <span class="d-flex align-items-center justify-content-center theme-icon dark-icon">
                            <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.7075 10.73C10.5458 10.7229 9.40692 10.407 8.4076 9.81463C7.40829 9.22228 6.58449 8.37482 6.02067 7.35913C5.45685 6.34345 5.17331 5.19609 5.1991 4.03469C5.22489 2.8733 5.5591 1.73965 6.16745 0.75C4.56463 1.03138 3.12395 1.89934 2.12605 3.1848C1.12815 4.47026 0.644493 6.08116 0.769304 7.7037C0.894115 9.32624 1.61845 10.8442 2.80121 11.9619C3.98397 13.0797 5.54045 13.7171 7.16745 13.75C8.28058 13.7528 9.37555 13.468 10.3462 12.9231C11.3168 12.3782 12.1302 11.5918 12.7075 10.64C12.3768 10.6946 12.0425 10.7247 11.7075 10.73V10.73Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                        <span class="d-flex align-items-center justify-content-center theme-icon auto-icon">
                            <svg width="15" height="13" viewBox="0 0 15 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.25 9.75L5.25 12.25M8.25 9.75L9.25 12.25M4.25 12.25H10.25M1.25 0.75H13.25C13.5261 0.75 13.75 0.973858 13.75 1.25V9.25C13.75 9.52614 13.5261 9.75 13.25 9.75H1.25C0.973858 9.75 0.75 9.52614 0.75 9.25V1.25C0.75 0.973858 0.973858 0.75 1.25 0.75Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0);" data-bs-theme-value="light">Sáng</a></li>
                        <li><a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0);" data-bs-theme-value="dark">Tối</a></li>
                        <li><a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0);" data-bs-theme-value="auto">Hệ thống</a></li>
                    </ul>
                </li>

                <li class="header-nav-item me-4 position-relative">
                    <livewire:admin.notification-bell />
                </li>

                <li class="header-nav-item header-user me-0">
                    <a class="header-nav-link" href="javascript:void(0);" data-bs-toggle="dropdown">
                        <x-user-avatar :user="$currentUser" :size="34" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-lg py-0">
                        <div class="dropdown-header d-flex align-items-center border-bottom py-4">
                            <div class="me-3 flex-shrink-0">
                                <x-user-avatar :user="$currentUser" :size="48" />
                            </div>
                            <div class="flex-grow-1 text-start overflow-hidden">
                                <h6 class="mb-0 text-truncate max-w-180px"  title="{{ $currentUser?->name }}">{{ $currentUser?->name ?? 'Người dùng' }}</h6>
                                <span class="text-muted">{{ $roleLabel }}</span>
                            </div>
                        </div>
                        <div class="dropdown-body py-2">
                            <a class="dropdown-item" href="{{ route('app.profile.index') }}">Hồ sơ của tôi</a>
                            <a class="dropdown-item" href="{{ route('app.password.index') }}">Đổi mật khẩu</a>
                            @can(\App\Enums\Permission::SETTINGS_VIEW->value)
                            <a class="dropdown-item" href="{{ route('app.settings.index') }}">Cài đặt hệ thống</a>
                            @endcan
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Đăng xuất</button>
                            </form>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
</div>
