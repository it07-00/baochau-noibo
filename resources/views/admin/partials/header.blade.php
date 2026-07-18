@inject('headerView', 'App\Support\HeaderViewData')
@php($isIntern = auth()->user()->hasRole(\App\Enums\Role::THUC_TAP->value))

<header class="app-header">
    <div class="app-header-inner align-items-center">
        <!-- Sidebar Toggler -->
        <button class="app-toggler me-2" type="button" aria-label="app toggler">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <!-- Header Start (Date) -->
        <div class="app-header-start d-none d-md-flex align-items-center ms-2">
            <div class="d-flex align-items-center text-muted fs-7">
                <i class="fi fi-rr-calendar me-2"></i>
                {{ $headerView->todayLabel() }}
            </div>
        </div>

        <!-- Header End (Actions & Settings) -->
        <div class="app-header-end">

            <!-- Light/Dark Mode Switcher -->
            <div class="px-lg-3 px-2 ps-0 d-flex align-items-center">
                <div class="dropdown">
                    <button class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light position-relative" id="ld-theme" type="button" data-bs-auto-close="outside" aria-expanded="false" data-bs-toggle="dropdown">
                        <i class="fi fi-rr-brightness scale-1x theme-icon-active"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button type="button" class="dropdown-item d-flex gap-2 align-items-center" data-bs-theme-value="light" aria-pressed="false">
                                <i class="fi fi-rr-brightness scale-1x" data-theme="light"></i> Sáng
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item d-flex gap-2 align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                                <i class="fi fi-rr-moon scale-1x" data-theme="dark"></i> Tối
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item d-flex gap-2 align-items-center" data-bs-theme-value="auto" aria-pressed="true">
                                <i class="fi fi-br-circle-half-stroke scale-1x" data-theme="auto"></i> Hệ thống
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="vr my-3"></div>

            <!-- Notifications & Work Calendar Shortcut -->
            <div class="d-flex align-items-center gap-sm-2 gap-0 px-lg-4 px-sm-2 px-1">
                @unless($isIntern)
                    <livewire:admin.notification-bell />
                @endunless

                <a href="{{ route('app.work-schedules.index') }}" class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light" title="Lịch công tác">
                    <i class="fi fi-rr-calendar"></i>
                </a>
            </div>

            <div class="vr my-3"></div>

            <!-- User Profile Dropdown -->
            <div class="dropdown text-end ms-sm-3 ms-2 ms-lg-4">
                <a href="#" class="d-flex align-items-center py-2 text-decoration-none" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="true">
                    <div class="text-end me-2 d-none d-lg-inline-block">
                        <div class="fw-bold text-dark">{{ $headerView->displayName(auth()->user()) }}</div>
                        <small class="text-body d-block lh-sm">
                            <i class="fi fi-rr-angle-down text-3xs me-1"></i> {{ $headerView->roleLabel(auth()->user()) }}
                        </small>
                    </div>
                    <div class="avatar avatar-sm rounded-circle border border-2 border-primary border-opacity-10">
                        <x-user-avatar :user="auth()->user()" :size="32" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end w-225px mt-1">
                    <li class="d-flex align-items-center p-2">
                        <div class="avatar avatar-sm rounded-circle me-2">
                            <x-user-avatar :user="auth()->user()" :size="32" />
                        </div>
                        <div class="ms-1 overflow-hidden">
                            <div class="fw-bold text-dark text-truncate" style="max-width: 150px;">{{ $headerView->displayName(auth()->user()) }}</div>
                            <small class="text-body d-block lh-sm text-truncate" style="max-width: 150px;">{{ auth()->user()->email }}</small>
                        </div>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    @unless($isIntern)
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('app.profile.index') }}">
                                <i class="fi fi-rr-user scale-1x"></i> Hồ sơ của tôi
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('app.password.index') }}">
                                <i class="fi fi-rr-lock scale-1x"></i> Đổi mật khẩu
                            </a>
                        </li>
                        @can(\App\Enums\Permission::SETTINGS_VIEW->value)
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('app.settings.index') }}">
                                    <i class="fi fi-rr-settings scale-1x"></i> Cài đặt hệ thống
                                </a>
                            </li>
                        @endcan
                    @endunless
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" id="header-logout-form" class="d-none">
                            @csrf
                        </form>
                        <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('header-logout-form').submit();">
                            <i class="fi fi-sr-exit scale-1x"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
