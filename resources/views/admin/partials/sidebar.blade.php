@php
    use App\Enums\Role;
    use App\Support\SidebarMenu;

    $currentUser = auth()->user();
    $active      = SidebarMenu::resolveActive($currentUser);
    $activeGroup = $active['group'];
    $activeChild = $active['child'];
    $allMenus    = SidebarMenu::all($currentUser);

    $primaryRole = collect(Role::priorityList())
        ->first(fn ($r) => $currentUser->hasRole($r))
        ?? $currentUser->roles?->first()?->name;
@endphp

<div id="app-sidebar" class="app-sidebar overflow-hidden">
    <div class="app-sidebar-wrapper">
        <div class="app-sidebar-header d-flex align-items-center justify-content-between">
            <a href="{{ $currentUser->hasAnyRole(Role::dashboardAccessRoles()) ? route('app.dashboard') : route('app.home') }}"
                class="app-sidebar-logo text-decoration-none d-flex align-items-center gap-2">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Bảo Châu Environment"
                    class="h-40px-auto">
                <span class="fw-bolder fs-5 text-primary letter-1" >BẢO CHÂU</span>
            </a>

            <button type="button" class="app-sidebar-close-btn app-sidebar-mobile-close d-xl-none">
                <svg width="20" height="12" viewBox="0 0 20 12" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M10.6923 10.2857L6.53846 6M6.53846 6L10.6923 1.71429M6.53846 6L19 6M1 11L1 1"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </button>
        </div>

        <div id="app-sidebar-menu" class="app-sidebar-menu">
            <ul>

                {{-- ── TỔNG QUAN ─────────────────────────────────────── --}}
                <li class="app-sidebar-menu-heading">
                    <span>
                        <span class="app-sidebar-menu-heading-line"></span>
                        TỔNG QUAN
                    </span>
                </li>

                @unless ($currentUser->hasRole(Role::IT->value))
                    <li class="app-sidebar-menu-item">
                        <a href="{{ $currentUser->hasAnyRole(Role::directorRoles()) ? route('app.dashboard') : route('app.home') }}"
                            class="menu-link d-flex align-items-center {{ request()->routeIs('app.home') || request()->is('/') || ($currentUser->hasAnyRole(Role::directorRoles()) && request()->routeIs('app.dashboard')) ? 'active menu-current' : '' }}">
                            <span class="menu-icon flex-shrink-0">
                                <svg width="17" height="17" viewBox="0 0 17 17" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.5 1.5L1.5 7V15.5H6.5V11H10.5V15.5H15.5V7L8.5 1.5Z" stroke="currentColor"
                                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <span class="menu-title flex-grow-1">{{ $currentUser->hasAnyRole(Role::directorRoles()) ? 'Bảng điều khiển' : 'Bảng xếp hạng' }}</span>
                        </a>
                    </li>
                @endunless

                @unless ($currentUser->hasAnyRole([...Role::dashboardAccessRoles(), ...Role::technicalConsultingRoles()]))
                    <li class="app-sidebar-menu-item">
                        <a href="{{ route('app.dashboard') }}"
                            class="menu-link d-flex align-items-center {{ request()->routeIs('app.dashboard') ? 'active' : '' }}">
                            <span class="menu-icon flex-shrink-0">
                                <svg width="17" height="17" viewBox="0 0 17 17" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2.125 3.54166H4.25" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" />
                                    <path d="M2.125 6.37499H8.5" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" />
                                    <path d="M2.125 9.20834H11.3333" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" />
                                    <path d="M2.125 12.0417H14.1667" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" />
                                </svg>
                            </span>
                            <span class="menu-title flex-grow-1">Bảng điều khiển</span>
                        </a>
                    </li>
                @endunless

                <li class="app-sidebar-menu-item">
                    <a href="{{ route('app.work-schedules.index') }}"
                        class="menu-link d-flex align-items-center {{ request()->routeIs('app.work-schedules.*') ? 'active menu-current' : '' }}">
                        <span class="menu-icon flex-shrink-0">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor"
                                    stroke-width="1.5" />
                                <path d="M3 10H21" stroke="currentColor" stroke-width="1.5" />
                                <path d="M8 2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M16 2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M7 14H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M14 14H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M7 18H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            </svg>
                        </span>
                        <span class="menu-title flex-grow-1">Lịch công tác</span>
                    </a>
                </li>

                {{-- ── QUẢN TRỊ ─────────────────────────────────────── --}}
                @if ($currentUser->hasRole(Role::IT->value) || $currentUser->canany(['users.view', 'roles.view', 'activity-log.view']))
                    <li class="app-sidebar-menu-heading">
                        <span>
                            <span class="app-sidebar-menu-heading-line"></span>
                            QUẢN TRỊ
                        </span>
                    </li>

                    @if ($currentUser->hasRole(Role::IT->value))
                        <li class="app-sidebar-menu-item">
                            <a href="{{ route('app.it-dashboard') }}"
                                class="menu-link d-flex align-items-center {{ request()->routeIs('app.it-dashboard') ? 'active menu-current' : '' }}">
                                <span class="menu-icon flex-shrink-0">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect x="2" y="3" width="20" height="14" rx="2"
                                            stroke="currentColor" stroke-width="1.5" />
                                        <path d="M8 21h8M12 17v4" stroke="currentColor" stroke-width="1.5"
                                            stroke-linecap="round" />
                                        <path d="M7 8h2M7 11h4" stroke="currentColor" stroke-width="1.5"
                                            stroke-linecap="round" />
                                        <circle cx="16" cy="9.5" r="2.5" stroke="currentColor"
                                            stroke-width="1.5" />
                                    </svg>
                                </span>
                                <span class="menu-title flex-grow-1">Quản trị hệ thống</span>
                            </a>
                        </li>
                        <li class="app-sidebar-menu-item">
                            <a href="{{ url('log-viewer') }}" target="_blank"
                                class="menu-link d-flex align-items-center">
                                <span class="menu-icon flex-shrink-0">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"></path>
                                        <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                        <path d="M4 14l2 2l4-4"></path>
                                    </svg>
                                </span>
                                <span class="menu-title flex-grow-1">Log Hệ Thống</span>
                            </a>
                        </li>
                    @endif

                    @can('users.view')
                        <li class="app-sidebar-menu-item">
                            <a href="{{ route('app.users.index') }}"
                                class="menu-link d-flex align-items-center {{ request()->routeIs('app.users.*') ? 'active menu-current' : '' }}">
                                <span class="menu-icon flex-shrink-0">
                                    <svg width="17" height="16" viewBox="0 0 17 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M5.94234 5.9423C7.37615 5.9423 8.53849 4.77997 8.53849 3.34615C8.53849 1.91234 7.37615 0.75 5.94234 0.75C4.50853 0.75 3.34619 1.91234 3.34619 3.34615C3.34619 4.77997 4.50853 5.9423 5.94234 5.9423Z"
                                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                        <path
                                            d="M11.1346 14.5961H0.75V13.4423C0.75 12.0652 1.29704 10.7445 2.27079 9.77079C3.24453 8.79705 4.56521 8.25 5.9423 8.25C7.31938 8.25 8.64006 8.79705 9.6138 9.77079C10.5875 10.7445 11.1346 12.0652 11.1346 13.4423V14.5961Z"
                                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                                <span class="menu-title flex-grow-1">Người dùng</span>
                            </a>
                        </li>
                    @endcan

                    @can('roles.view')
                        <li class="app-sidebar-menu-item">
                            <a href="{{ route('app.roles.index') }}"
                                class="menu-link d-flex align-items-center {{ request()->routeIs('app.roles.*') ? 'active menu-current' : '' }}">
                                <span class="menu-icon flex-shrink-0">
                                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M8.5 1.5L2.5 4.5V8.5C2.5 12.5 5.5 15.5 8.5 16.5C11.5 15.5 14.5 12.5 14.5 8.5V4.5L8.5 1.5Z"
                                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </span>
                                <span class="menu-title flex-grow-1">Vai trò & quyền hạn</span>
                            </a>
                        </li>
                    @endcan

                    @can('activity-log.view')
                        <li class="app-sidebar-menu-item">
                            <a href="{{ route('app.activity-log') }}"
                                class="menu-link d-flex align-items-center {{ request()->routeIs('app.activity-log') ? 'active menu-current' : '' }}">
                                <span class="menu-icon flex-shrink-0">
                                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="8.5" cy="8.5" r="7" stroke="currentColor"
                                            stroke-width="1.5" />
                                        <path d="M8.5 4.5V8.5L11.5 11.5" stroke="currentColor" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                                <span class="menu-title flex-grow-1">Nhật ký hoạt động</span>
                            </a>
                        </li>
                    @endcan
                @endif

                {{-- ── TỔ CHỨC ──────────────────────────────────────── --}}
                @if (
                        $currentUser->canany([
                            'departments.view',
                            'handlers.view',
                            'customers.view',
                            'hr-profiles.view',
                            'cham-cong.view',
                            'cham-cong.edit',
                        ]))
                        <li class="app-sidebar-menu-heading">
                            <span>
                                <span class="app-sidebar-menu-heading-line"></span>
                                TỔ CHỨC
                            </span>
                        </li>

                        @can('departments.view')
                            <li class="app-sidebar-menu-item">
                                <a href="{{ route('app.departments.index') }}"
                                    class="menu-link d-flex align-items-center {{ request()->routeIs('app.departments.*') ? 'active menu-current' : '' }}">
                                    <span class="menu-icon flex-shrink-0">
                                        <svg width="17" height="17" viewBox="0 0 17 17" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2.5 15.5V5.5L8.5 1.5L14.5 5.5V15.5" stroke="currentColor"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M6.5 15.5V10.5H10.5V15.5" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span class="menu-title flex-grow-1">Phòng ban</span>
                                </a>
                            </li>
                        @endcan

                        @can('customers.view')
                            <li class="app-sidebar-menu-item">
                                <a href="{{ route('app.customers.index') }}"
                                    class="menu-link d-flex align-items-center {{ request()->routeIs('app.customers.*') ? 'active menu-current' : '' }}">
                                    <span class="menu-icon flex-shrink-0">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3 21V7L12 3L21 7V21" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M9 21V13H15V21" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M3 7H21" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" />
                                        </svg>
                                    </span>
                                    <span class="menu-title flex-grow-1">Khách hàng</span>
                                </a>
                            </li>
                        @endcan

                        @can('handlers.view')
                            <li class="app-sidebar-menu-item">
                                <a href="{{ route('app.handlers.index') }}"
                                    class="menu-link d-flex align-items-center {{ request()->routeIs('app.handlers.*') ? 'active menu-current' : '' }}">
                                    <span class="menu-icon flex-shrink-0">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M12 12C14.2091 12 16 10.2091 16 8C16 5.79086 14.2091 4 12 4C9.79086 4 8 5.79086 8 8C8 10.2091 9.79086 12 12 12Z"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path d="M5 20C5 16.6863 8.13401 14 12 14C15.866 14 19 16.6863 19 20"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path d="M19 6V10" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" />
                                            <path d="M21 8H17" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" />
                                        </svg>
                                    </span>
                                    <span class="menu-title flex-grow-1">Nhà thầu phụ</span>
                                </a>
                            </li>
                        @endcan

                        @can('hr-profiles.view')
                            <li class="app-sidebar-menu-item">
                                <a href="{{ route('app.hr.index') }}"
                                    class="menu-link d-flex align-items-center {{ request()->routeIs('app.hr.*') ? 'active menu-current' : '' }}">
                                    <span class="menu-icon flex-shrink-0">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" stroke="currentColor"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <circle cx="9" cy="7" r="4" stroke="currentColor"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span class="menu-title flex-grow-1">Quản lý nhân sự</span>
                                </a>
                            </li>
                        @endcan

                        @can('cham-cong.view')
                            <li class="app-sidebar-menu-item">
                                <a href="{{ route('app.attendance.index') }}"
                                    class="menu-link d-flex align-items-center {{ request()->routeIs('app.attendance.index') ? 'active menu-current' : '' }}">
                                    <span class="menu-icon flex-shrink-0">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <rect x="3" y="4" width="18" height="18" rx="2"
                                                stroke="currentColor" stroke-width="1.5" />
                                            <path d="M3 10H21" stroke="currentColor" stroke-width="1.5" />
                                            <path d="M8 2V6" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" />
                                            <path d="M16 2V6" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" />
                                            <path d="M9 15L11 17L15 13" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span class="menu-title flex-grow-1">Chấm công</span>
                                </a>
                            </li>
                        @endcan

                        @can('cham-cong.edit')
                            <li class="app-sidebar-menu-item">
                                <a href="{{ route('app.attendance.employees') }}"
                                    class="menu-link d-flex align-items-center {{ request()->routeIs('app.attendance.employees') ? 'active menu-current' : '' }}">
                                    <span class="menu-icon flex-shrink-0">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="8" r="4" stroke="currentColor"
                                                stroke-width="1.5" />
                                            <path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" />
                                        </svg>
                                    </span>
                                    <span class="menu-title flex-grow-1">NV chấm công</span>
                                </a>
                            </li>
                        @endcan
                @endif

                @if ($currentUser->hasRole(Role::IT->value))
                    @can('daily-reports.view')
                        <li class="app-sidebar-menu-heading">
                            <span>
                                <span class="app-sidebar-menu-heading-line"></span>
                                NGHIỆP VỤ
                            </span>
                        </li>
                        <li class="app-sidebar-menu-item">
                            <a href="{{ route('app.daily-reports.index') }}"
                                class="menu-link d-flex align-items-center {{ $activeGroup === 'Báo cáo ngày' ? 'active' : '' }}">
                                <span class="menu-icon flex-shrink-0">{!! SidebarMenu::icon('report') !!}</span>
                                <span class="menu-title flex-grow-1">Báo cáo ngày</span>
                            </a>
                        </li>
                    @endcan
                @endif

                @unless ($currentUser->hasRole(Role::IT->value))
                    @php $currentSection = null; @endphp
                    @foreach ($allMenus as $menu)
                        @can($menu['permission'])
                            @if ($menu['section'] !== $currentSection)
                                @php $currentSection = $menu['section']; @endphp
                                <li class="app-sidebar-menu-heading">
                                    <span>
                                        <span class="app-sidebar-menu-heading-line"></span>
                                        {{ $currentSection }}
                                    </span>
                                </li>
                            @endif
                            <li class="app-sidebar-menu-item">
                                @if (isset($menu['href']))
                                    <a href="{{ $menu['href'] }}"
                                        class="menu-link d-flex align-items-center {{ $menu['title'] === $activeGroup ? 'active' : '' }}">
                                        <span class="menu-icon flex-shrink-0">{!! SidebarMenu::icon($menu['icon']) !!}</span>
                                        <span class="menu-title flex-grow-1">{{ $menu['title'] }}</span>
                                    </a>
                                @else
                                    <a href="javascript:void(0)"
                                        class="menu-link d-flex align-items-center {{ $menu['title'] === $activeGroup ? 'active' : '' }}">
                                        <span class="menu-icon flex-shrink-0">{!! SidebarMenu::icon($menu['icon']) !!}</span>
                                        <span class="menu-title flex-grow-1">
                                            {{ $menu['title'] }}
                                            <span class="menu-arrow">{!! SidebarMenu::icon('chevron') !!}</span>
                                        </span>
                                    </a>

                                    <ul class="app-sidebar-submenu"
                                        style="display: {{ $menu['title'] === $activeGroup ? 'block' : 'none' }};">
                                        @foreach ($menu['children'] as $child)
                                            @continue($child === 'Bảng theo dõi báo giá' && !$currentUser->hasAnyRole([...Role::salesRoles(), Role::GIAM_DOC->value]))
                                            @continue($child === 'Đăng ký mục tiêu doanh số' && !$currentUser->hasAnyRole(Role::salesRoles()))

                                            @php
                                                $childActive = $menu['title'] === $activeGroup && $child === $activeChild;
                                                $childHref   = SidebarMenu::childHref($menu['title'], $child);
                                                $childLabel  = SidebarMenu::childLabel($menu['title'], $child);
                                                $childIcon   = SidebarMenu::childIcon($menu['title'], $menu['section']);
                                            @endphp
                                            <li>
                                                <a href="{{ $childHref }}"
                                                    class="menu-link d-flex align-items-center {{ $childActive ? 'menu-current active' : '' }}">
                                                    <span class="menu-icon flex-shrink-0">{!! $childIcon !!}</span>
                                                    <span class="menu-title flex-grow-1">{{ $childLabel }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endcan
                    @endforeach
                @endunless
            </ul>
        </div>

        <div class="app-sidebar-footer">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar rounded-pill">
                    <x-user-avatar :user="$currentUser" :size="36" />
                </div>
                <div>
                    <h6 class="mb-0">{{ $currentUser->name ?? 'Người dùng' }}</h6>
                    <span class="text-muted">{{ Role::tryFrom($primaryRole ?? '')?->label() ?? 'Nhân viên' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
