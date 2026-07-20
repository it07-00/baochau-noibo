<aside class="app-menubar" id="appMenubar">
    <!-- Brand -->
    <div class="app-navbar-brand">
        <!-- Logo Icon (Always visible, fits mini sidebar) -->
        <a href="{{ auth()->user()->hasRole(\App\Enums\Role::THUC_TAP->value) ? route('app.daily-reports.index') : (auth()->user()->hasAnyRole(\App\Enums\Role::dashboardAccessRoles()) ? route('app.dashboard') : route('app.home')) }}"
            class="navbar-brand-logo text-decoration-none">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Bảo Châu" style="height: 40px; width: auto;">
        </a>

        <!-- Logo Text (Only visible when sidebar is full) -->
        <a class="navbar-brand-mini text-decoration-none ms-2" href="{{ auth()->user()->hasRole(\App\Enums\Role::THUC_TAP->value) ? route('app.daily-reports.index') : (auth()->user()->hasAnyRole(\App\Enums\Role::dashboardAccessRoles()) ? route('app.dashboard') : route('app.home')) }}">
            <span class="fw-bolder fs-5 text-primary letter-1">BẢO CHÂU</span>
        </a>
    </div>

    <!-- Navigation Menu -->
    <nav class="app-navbar" data-simplebar>
        <ul class="menubar">
            @if (auth()->user()->hasRole(\App\Enums\Role::THUC_TAP->value))
                {{-- ── NGHIỆP VỤ (ONLY FOR INTERNS) ─────────────────────────────────── --}}
                <li class="menu-heading">
                    <span class="menu-label">NGHIỆP VỤ</span>
                </li>
                <li class="menu-item">
                    <a href="{{ route('app.daily-reports.index') }}" class="menu-link">
                        <i class="fi fi-rr-chart-pie-alt"></i>
                        <span class="menu-label">Báo cáo ngày</span>
                    </a>
                </li>
            @else

                {{-- ── TỔNG QUAN ─────────────────────────────────────── --}}
                <li class="menu-heading">
                    <span class="menu-label">TỔNG QUAN</span>
                </li>

                @unless (auth()->user()->hasAnyRole([\App\Enums\Role::IT->value, \App\Enums\Role::MARKETING->value]))
                    <li class="menu-item">
                        <a href="{{ auth()->user()->hasAnyRole(\App\Enums\Role::directorRoles()) ? route('app.dashboard') : route('app.home') }}"
                            class="menu-link">
                            <i class="fi fi-rr-apps"></i>
                            <span class="menu-label">{{ auth()->user()->hasAnyRole(\App\Enums\Role::directorRoles()) ? 'Bảng điều khiển' : 'Bảng xếp hạng' }}</span>
                        </a>
                    </li>
                @endunless

                @unless (auth()->user()->hasAnyRole(\App\Enums\Role::dashboardAccessRoles()) || auth()->user()->hasRole(\App\Enums\Role::MARKETING->value))
                    <li class="menu-item">
                        <a href="{{ route('app.dashboard') }}" class="menu-link">
                            <i class="fi fi-rr-chart-pie-alt"></i>
                            <span class="menu-label">Thống kê</span>
                        </a>
                    </li>
                @endunless

                <li class="menu-item">
                    <a href="{{ route('app.work-schedules.index') }}" class="menu-link">
                        <i class="fi fi-rr-calendar"></i>
                        <span class="menu-label">Lịch công tác</span>
                    </a>
                </li>

                {{-- ── QUẢN TRỊ ─────────────────────────────────────── --}}
                @if (auth()->user()->hasRole(\App\Enums\Role::IT->value) || auth()->user()->canany(['users.view', 'roles.view', 'activity-log.view']))
                    <li class="menu-heading">
                        <span class="menu-label">QUẢN TRỊ</span>
                    </li>

                    @if (auth()->user()->hasRole(\App\Enums\Role::IT->value))
                        <li class="menu-item menu-arrow">
                            <a href="javascript:void(0)" class="menu-link" role="button">
                                <i class="fi fi-rr-settings"></i>
                                <span class="menu-label">Hệ thống</span>
                            </a>
                            <ul class="menu-inner">
                                <li class="menu-item">
                                    <a href="{{ route('app.it-dashboard') }}" class="menu-link">
                                        <i class="fi fi-rr-settings"></i>
                                        <span class="menu-label">Quản trị hệ thống</span>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="{{ url('log-viewer') }}" target="_blank" class="menu-link">
                                        <i class="fi fi-rr-file"></i>
                                        <span class="menu-label">Log Hệ Thống</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if (auth()->user()->canAny(['users.view', 'roles.view', 'activity-log.view']))
                        <li class="menu-item menu-arrow">
                            <a href="javascript:void(0)" class="menu-link" role="button">
                                <i class="fi fi-rr-user-key"></i>
                                <span class="menu-label">Phân quyền</span>
                            </a>
                            <ul class="menu-inner">
                                @can('users.view')
                                    <li class="menu-item">
                                        <a href="{{ route('app.users.index') }}" class="menu-link">
                                            <i class="fi fi-rr-users"></i>
                                            <span class="menu-label">Người dùng</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('roles.view')
                                    <li class="menu-item">
                                        <a href="{{ route('app.roles.index') }}" class="menu-link">
                                            <i class="fi fi-rr-user-key"></i>
                                            <span class="menu-label">Vai trò & quyền hạn</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('activity-log.view')
                                    <li class="menu-item">
                                        <a href="{{ route('app.activity-log') }}" class="menu-link">
                                            <i class="fi fi-rr-file"></i>
                                            <span class="menu-label">Nhật ký hoạt động</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif

                    @if (auth()->user()->hasRole(\App\Enums\Role::IT->value))
                        <li class="menu-item">
                            <a href="{{ route('app.internal-notifications.index') }}" class="menu-link">
                                <i class="fi fi-rr-bell"></i>
                                <span class="menu-label">Thông báo nội bộ</span>
                            </a>
                        </li>
                    @endif
                @endif

                {{-- ── TỔ CHỨC ──────────────────────────────────────── --}}
                @if (auth()->user()->canany(['departments.view', 'handlers.view', 'customers.view', 'hr-profiles.view', 'cham-cong.view', 'cham-cong.edit']))
                    <li class="menu-heading">
                        <span class="menu-label">TỔ CHỨC</span>
                    </li>

                    @can('departments.view')
                        <li class="menu-item">
                            <a href="{{ route('app.departments.index') }}" class="menu-link">
                                <i class="fi fi-rr-briefcase"></i>
                                <span class="menu-label">Phòng ban</span>
                            </a>
                        </li>
                    @endcan

                    @if (auth()->user()->canAny(['customers.view', 'handlers.view']))
                        <li class="menu-item menu-arrow">
                            <a href="javascript:void(0)" class="menu-link" role="button">
                                <i class="fi fi-rr-folder-open"></i>
                                <span class="menu-label">Đối tác</span>
                            </a>
                            <ul class="menu-inner">
                                @can('customers.view')
                                    <li class="menu-item">
                                        <a href="{{ route('app.customers.index') }}" class="menu-link">
                                            <i class="fi fi-rr-users"></i>
                                            <span class="menu-label">Khách hàng</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('handlers.view')
                                    <li class="menu-item">
                                        <a href="{{ route('app.handlers.index') }}" class="menu-link">
                                            <i class="fi fi-rr-briefcase"></i>
                                            <span class="menu-label">Nhà thầu phụ</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif

                    @if (auth()->user()->canAny(['hr-profiles.view', 'cham-cong.view', 'cham-cong.edit']))
                        <li class="menu-item menu-arrow">
                            <a href="javascript:void(0)" class="menu-link" role="button">
                                <i class="fi fi-rr-users"></i>
                                <span class="menu-label">Nhân sự</span>
                            </a>
                            <ul class="menu-inner">
                                @can('hr-profiles.view')
                                    <li class="menu-item">
                                        <a href="{{ route('app.hr.index') }}" class="menu-link">
                                            <i class="fi fi-rr-users"></i>
                                            <span class="menu-label">Quản lý nhân sự</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('cham-cong.view')
                                    <li class="menu-item">
                                        <a href="{{ route('app.attendance.index') }}" class="menu-link">
                                            <i class="fi fi-rr-calendar"></i>
                                            <span class="menu-label">Chấm công</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('cham-cong.edit')
                                    <li class="menu-item">
                                        <a href="{{ route('app.attendance.employees') }}" class="menu-link">
                                            <i class="fi fi-rr-user-key"></i>
                                            <span class="menu-label">NV chấm công</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif
                @endif

                @if (auth()->user()->hasRole(\App\Enums\Role::IT->value))
                    @can('daily-reports.view')
                        <li class="menu-heading">
                            <span class="menu-label">NGHIỆP VỤ</span>
                        </li>
                        <li class="menu-item">
                            <a href="{{ route('app.daily-reports.index') }}" class="menu-link">
                                <i class="fi fi-rr-chart-pie-alt"></i>
                                <span class="menu-label">Báo cáo ngày</span>
                            </a>
                        </li>
                    @endcan
                @endif

                {{-- ── DYNAMIC MENUS (GROUPED BY SECTION) ─────────────────────────── --}}
                @unless (auth()->user()->hasRole(\App\Enums\Role::IT->value))
                    @foreach (\App\Support\SidebarMenu::groupedBySection(auth()->user()) as $section => $sectionMenus)
                        <li class="menu-heading">
                            <span class="menu-label">{{ $section }}</span>
                        </li>

                        @foreach ($sectionMenus as $menu)
                            @can($menu['permission'])
                                @php
                                    $isActiveGroup = \App\Support\SidebarMenu::activeGroup(auth()->user()) === $menu['title'];
                                @endphp
                                @if (isset($menu['href']))
                                    <li class="menu-item">
                                        <a href="{{ $menu['href'] }}"
                                            class="menu-link d-flex align-items-center gap-2 rounded-3 {{ $isActiveGroup ? 'active bg-primary text-white shadow-sm fw-semibold' : '' }}">
                                            {!! \App\Support\SidebarMenu::icon($menu['icon']) !!}
                                            <span class="menu-label">{{ $menu['title'] }}</span>
                                        </a>
                                    </li>
                                @else
                                    <li class="menu-item menu-arrow {{ $isActiveGroup ? 'open' : '' }}">
                                        <a href="javascript:void(0)"
                                            class="menu-link d-flex align-items-center gap-2 rounded-3 fw-semibold {{ $isActiveGroup ? 'open bg-primary text-white shadow-sm' : '' }}"
                                            role="button" aria-expanded="{{ $isActiveGroup ? 'true' : 'false' }}">
                                            {!! \App\Support\SidebarMenu::icon($menu['icon']) !!}
                                            <span class="menu-label">{{ $menu['title'] }}</span>
                                        </a>

                                        <ul class="menu-inner ps-2 ms-3 mt-2">
                                            @foreach ($menu['children'] as $child)
                                                @continue($child === 'Bảng theo dõi báo giá' && !auth()->user()->hasAnyRole([...\App\Enums\Role::salesRoles(), \App\Enums\Role::GIAM_DOC->value]))
                                                @continue($child === 'Tạo báo giá' && !auth()->user()->hasAnyRole([...\App\Enums\Role::salesRoles()]))
                                                @continue($child === 'Đăng ký mục tiêu doanh số' && !auth()->user()->hasAnyRole(\App\Enums\Role::salesRoles()))

                                                @php
                                                    $isActiveChild = \App\Support\SidebarMenu::activeChild(auth()->user()) === $child;
                                                @endphp

                                                <li class="menu-item">
                                                    <a href="{{ \App\Support\SidebarMenu::childHref($menu['title'], $child) }}"
                                                        class="menu-link d-flex align-items-center gap-2 rounded-3 px-2 py-2 mb-1 {{ $isActiveChild ? 'active bg-primary-subtle text-primary fw-semibold' : 'text-body' }}"
                                                        @if ($isActiveChild) aria-current="page" @endif>
                                                        <span class="d-inline-flex align-items-center justify-content-center flex-shrink-0 rounded-2 bg-body-tertiary text-secondary p-2">
                                                            {!! \App\Support\SidebarMenu::childIcon($menu['title'], $section, $child) !!}
                                                        </span>
                                                        <span class="menu-label flex-grow-1 text-wrap lh-sm">{{ \App\Support\SidebarMenu::childLabel($menu['title'], $child) }}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endif
                            @endcan
                        @endforeach
                    @endforeach
                @endunless
            @endif
        </ul>
    </nav>
</aside>
