@php
    $currentUser = auth()->user();
@endphp

<div id="app-sidebar" class="app-sidebar overflow-hidden">
    <div class="app-sidebar-wrapper">
        <div class="app-sidebar-header d-flex align-items-center justify-content-between">
            <a href="{{ $currentUser->hasAnyRole(['it', 'giam-doc', 'admin', 'quan-ly']) ? route('app.dashboard') : route('app.home') }}" class="app-sidebar-logo text-decoration-none d-flex align-items-center gap-2">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Bảo Châu Environment" style="height: 40px; width: auto;">
                <span class="fw-bolder fs-5 text-primary" style="letter-spacing: 1px;">BẢO CHÂU</span>
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

                <li class="app-sidebar-menu-heading">
                    <span>
                        <span class="app-sidebar-menu-heading-line"></span>
                        QUẢN LÝ
                    </span>
                </li>

                @if($currentUser->hasRole('it'))
                    <li class="app-sidebar-menu-item">
                        <a href="{{ route('app.dashboard') }}"
                            class="menu-link d-flex align-items-center {{ request()->routeIs('app.dashboard') ? 'active menu-current' : '' }}">
                            <span class="menu-icon flex-shrink-0">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2" y="3" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8 21h8M12 17v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M7 8h2M7 11h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <circle cx="16" cy="9.5" r="2.5" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                            </span>
                            <span class="menu-title flex-grow-1">Quản trị hệ thống</span>
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
                            <span class="menu-title flex-grow-1">Quản lý vai trò quyền hạn</span>
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
                                    <circle cx="8.5" cy="8.5" r="7" stroke="currentColor" stroke-width="1.5" />
                                    <path d="M8.5 4.5V8.5L11.5 11.5" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <span class="menu-title flex-grow-1">Nhật ký hoạt động</span>
                        </a>
                    </li>
                @endcan

                @can('departments.view')
                    <li class="app-sidebar-menu-item">
                        <a href="{{ route('app.departments.index') }}"
                            class="menu-link d-flex align-items-center {{ request()->routeIs('app.departments.*') ? 'active menu-current' : '' }}">
                            <span class="menu-icon flex-shrink-0">
                                <svg width="17" height="17" viewBox="0 0 17 17" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2.5 15.5V5.5L8.5 1.5L14.5 5.5V15.5" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M6.5 15.5V10.5H10.5V15.5" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <span class="menu-title flex-grow-1">Phòng ban</span>
                        </a>
                    </li>
                @endcan

                @can('handlers.view')
                    @unless ($currentUser->hasRole('it'))
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
                                        <path d="M19 6V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                        <path d="M21 8H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                    </svg>
                                </span>
                                <span class="menu-title flex-grow-1">Nhà thầu phụ</span>
                            </a>
                        </li>
                    @endunless
                @endcan

                @can('customers.view')
                    @unless ($currentUser->hasRole('it'))
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
                                        <path d="M3 7H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                    </svg>
                                </span>
                                <span class="menu-title flex-grow-1">Khách hàng</span>
                            </a>
                        </li>
                    @endunless
                @endcan

                @can('cham-cong.view')
                    <li class="app-sidebar-menu-item">
                        <a href="{{ route('app.attendance.index') }}"
                            class="menu-link d-flex align-items-center {{ request()->routeIs('app.attendance.index') ? 'active menu-current' : '' }}">
                            <span class="menu-icon flex-shrink-0">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M3 10H21" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8 2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M16 2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M9 15L11 17L15 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
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
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="menu-title flex-grow-1">NV chấm công</span>
                        </a>
                    </li>
                @endcan

                @can('hr-profiles.view')
                    <li class="app-sidebar-menu-item">
                        <a href="{{ route('app.hr.index') }}"
                            class="menu-link d-flex align-items-center {{ request()->routeIs('app.hr.*') ? 'active menu-current' : '' }}">
                            <span class="menu-icon flex-shrink-0">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="menu-title flex-grow-1">Quản lý nhân sự</span>
                        </a>
                    </li>
                @endcan

                <li class="app-sidebar-menu-heading">
                    <span>
                        <span class="app-sidebar-menu-heading-line"></span>
                        HẠNG MỤC
                    </span>
                </li>

                @php
                    $stackIcon = <<<'SVG'
                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.49996 1.75L1.75 5.125L8.49996 8.5L15.25 5.125L8.49996 1.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M1.75 8.5L8.49996 11.875L15.25 8.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1.75 11.875L8.49996 15.25L15.25 11.875" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    SVG;

                    $usersIcon = <<<'SVG'
                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6.02084 6.02083C7.38905 6.02083 8.49813 4.91175 8.49813 3.54354C8.49813 2.17533 7.38905 1.06625 6.02084 1.06625C4.65263 1.06625 3.54355 2.17533 3.54355 3.54354C3.54355 4.91175 4.65263 6.02083 6.02084 6.02083Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12.0417 6.72917C13.019 6.72917 13.8115 5.93667 13.8115 4.95938C13.8115 3.98208 13.019 3.18958 12.0417 3.18958" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1.7746 12.7492C1.7746 11.2507 2.98204 10.0433 4.48056 10.0433H7.56023C9.05875 10.0433 10.2662 11.2507 10.2662 12.7492" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11.3334 10.75H12.0417C13.2148 10.75 14.1667 11.7019 14.1667 12.875" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    SVG;

                    $docIcon = <<<'SVG'
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.3333 14H4.66667C3.93029 14 3.33333 13.403 3.33333 12.6667V3.33333C3.33333 2.59695 3.93029 2 4.66667 2H11.3333C12.0697 2 12.6667 2.59695 12.6667 3.33333V12.6667C12.6667 13.403 12.0697 14 11.3333 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6 6H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M6 9.33333H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    SVG;

                    $reportNodeIcon = <<<'SVG'
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="2.66667" y="2.66667" width="10.6667" height="10.6667" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M6.66667 5.66667L10.6667 8L6.66667 10.3333V5.66667Z" fill="currentColor"/>
                    </svg>
                    SVG;

                    $fileIcon = <<<'SVG'
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2.66667H5.33333C4.59695 2.66667 4 3.26362 4 4V12C4 12.7364 4.59695 13.3333 5.33333 13.3333H10.6667C11.403 13.3333 12 12.7364 12 12V4.66667L10 2.66667Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 2.66667V4.66667H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.66667 8H9.33333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    SVG;

                    $chevronIcon = <<<'SVG'
                    <svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.5 1.5L5.5 6L1.5 10.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    SVG;

                    // permission: chỉ những role được cấp permission tương ứng mới thấy menu
                    $groupMenus = [
                        [
                            'title' => 'Quản lý hợp đồng',
                            'icon' => $stackIcon,
                            'permission' => 'payment-schedules.view',
                            'children' => [
                                'HĐ Chất thải & Tiếng ồn',
                                'HĐ Pháp lý & Hồ sơ MT',
                                'HĐ Kỹ thuật & Ứng phó SC',
                                'HĐ NC & CĐ Công nghệ',
                                'HĐ TV & BC PTBV',
                                'HĐ Phát thải & Năng lượng',
                            ],
                        ],
                        [
                            'title' => 'Quản lý hóa đơn',
                            'icon' => $stackIcon,
                            'permission' => 'invoices.view',
                            'children' => ['Hóa đơn Bảo Châu', 'Hóa đơn nhà thầu phụ'],
                        ],
                        [
                            'title' => 'Chuyển phát thư',
                            'icon' => $stackIcon,
                            'permission' => 'mail-delivery-admin.view',
                            'children' => ['Quản lý chuyển phát'],
                        ],
                        [
                            'title' => 'Bộ phận kinh doanh',
                            'icon' => $stackIcon,
                            'permission' => 'sales-renewal.view',
                            'children' => ['Doanh số tái ký', 'Doanh số theo tiến độ', 'Bảng theo dõi báo giá', 'Đăng ký mục tiêu doanh số'],
                        ],
                        [
                            'title' => 'Bộ phận tư vấn',
                            'icon' => $stackIcon,
                            'permission' => 'consulting-requests.view',

                            'children' => [
                                'HĐ Chất thải & Tiếng ồn',
                                'HĐ Pháp lý & Hồ sơ MT',
                                'HĐ Kỹ thuật & Ứng phó SC',
                                'HĐ NC & CĐ Công nghệ',
                                'HĐ TV & BC PTBV',
                                'HĐ Phát thải & Năng lượng',
                            ],
                        ],
                        [
                            'title' => 'Bộ phận kế toán',
                            'icon' => $stackIcon,
                            'permission' => 'commissions.view',
                            'children' => ['Yêu cầu chi hoa hồng'],
                        ],
                        [
                            'title' => 'Bộ phận kỹ thuật',
                            'icon' => $stackIcon,
                            'permission' => 'technical-requests.view',
                            'children' => [
                                'HĐ Pháp lý & Hồ sơ MT',
                            ],
                        ],
                        [
                            'title' => 'Bộ phận Marketing',
                            'icon' => $usersIcon,
                            'permission' => 'marketing-reports.view',
                            'children' => ['Báo cáo hàng ngày'],
                        ],
                        [
                            'title' => 'Báo cáo Kinh doanh',
                            'icon' => $usersIcon,
                            'permission' => 'reports-sales.view',
                            'children' => [
                                'Bảng tổng kết doanh số',
                                'Bảng doanh số cam kết',
                                'Bảng tổng kết',
                                'Bảng doanh số cá nhân',
                                'Bảng theo dõi tái ký cá nhân',
                                'Bảng theo dõi doanh số',
                                'Doanh số thực thu',
                            ],
                        ],
                        [
                            'title' => 'Báo cáo Tư vấn',
                            'icon' => $usersIcon,
                            'permission' => 'reports-consulting.view',
                            'children' => [
                                'Chất thải & Tiếng ồn',
                                'Pháp lý & Hồ sơ MT',
                                'Kỹ thuật & Ứng phó SC',
                                'NC & CĐ Công nghệ',
                                'TV & BC PTBV',
                                'Phát thải & Năng lượng',
                            ],
                        ],
                        [
                            'title' => 'Báo cáo Kỹ thuật',
                            'icon' => $usersIcon,
                            'permission' => 'reports-technical.view',
                            'children' => ['Hồ sơ môi trường'],
                        ],
                        [
                            'title' => 'Nội bộ',
                            'icon' => $usersIcon,
                            'permission' => 'internal-docs.view',
                            'children' => ['Quy định'],
                        ],
                        [
                            'title' => 'Báo cáo ngày',
                            'icon' => $reportNodeIcon,
                            'permission' => 'daily-reports.view',
                            'href' => route('app.daily-reports.index'),
                        ],
                    ];

                    $activeGroup = null;
                    $activeChild = null;

                    if (request()->routeIs('app.internal-docs.*')) {
                        $activeGroup = 'Nội bộ';
                        $activeChild = 'Quy định';
                    } elseif (request()->routeIs('app.contracts.waste.*')) {
                        $activeGroup = 'Quản lý hợp đồng';
                        $activeChild = 'HĐ Chất thải & Tiếng ồn';
                    } elseif (request()->routeIs('app.contracts.consulting.*')) {
                        $activeGroup = 'Quản lý hợp đồng';
                        $activeChild = 'HĐ Pháp lý & Hồ sơ MT';
                    } elseif (request()->routeIs('app.contracts.project.*')) {
                        $activeGroup = 'Quản lý hợp đồng';
                        $activeChild = 'HĐ Kỹ thuật & Ứng phó SC';
                    } elseif (request()->routeIs('app.contracts.commercial.*')) {
                        $activeGroup = 'Quản lý hợp đồng';
                        $activeChild = 'HĐ NC & CĐ Công nghệ';
                    } elseif (request()->routeIs('app.contracts.sustainability.*')) {
                        $activeGroup = 'Quản lý hợp đồng';
                        $activeChild = 'HĐ TV & BC PTBV';
                    } elseif (request()->routeIs('app.contracts.energy.*')) {
                        $activeGroup = 'Quản lý hợp đồng';
                        $activeChild = 'HĐ Phát thải & Năng lượng';
                    } elseif (request()->routeIs('app.marketing.daily-report.*')) {
                        $activeGroup = 'Bộ phận Marketing';
                        $activeChild = 'Báo cáo hàng ngày';
                    } elseif (request()->routeIs('app.daily-reports.*')) {
                        $activeGroup = 'Báo cáo ngày';
                        $activeChild = 'Báo cáo ngày';
                    } elseif (request()->routeIs('app.commissions.*')) {
                        $activeGroup = 'Bộ phận kế toán';
                        $activeChild = 'Yêu cầu chi hoa hồng';
                    } elseif (request()->routeIs('app.sales.renewal.*')) {
                        $activeGroup = 'Bộ phận kinh doanh';
                        $activeChild = 'Doanh số tái ký';
                    } elseif (request()->routeIs('app.sales.progressive.*')) {
                        $activeGroup = 'Bộ phận kinh doanh';
                        $activeChild = 'Doanh số theo tiến độ';
                    } elseif (request()->routeIs('app.postal-deliveries.*')) {
                        $activeGroup = 'Chuyển phát thư';
                        $activeChild = 'Quản lý chuyển phát';
                    } elseif (request()->routeIs('app.quotation-tracking.*')) {
                        $activeGroup = 'Bộ phận kinh doanh';
                        $activeChild = 'Bảng theo dõi báo giá';
                    } elseif (request()->routeIs('app.reports.sales.summary')) {
                        $activeGroup = 'Báo cáo Kinh doanh';
                        $activeChild = 'Bảng tổng kết doanh số';
                    } elseif (request()->routeIs('app.sales.target-registration')) {
                        $activeGroup = 'Bộ phận kinh doanh';
                        $activeChild = 'Đăng ký mục tiêu doanh số';
                    } elseif (request()->routeIs('app.reports.sales.target')) {
                        $activeGroup = 'Báo cáo Kinh doanh';
                        $activeChild = 'Bảng doanh số cam kết';
                    } elseif (request()->routeIs('app.reports.sales.overview')) {
                        $activeGroup = 'Báo cáo Kinh doanh';
                        $activeChild = 'Bảng tổng kết';
                    } elseif (request()->routeIs('app.reports.sales.personal')) {
                        $activeGroup = 'Báo cáo Kinh doanh';
                        $activeChild = 'Bảng doanh số cá nhân';
                    } elseif (request()->routeIs('app.reports.sales.renewal-personal')) {
                        $activeGroup = 'Báo cáo Kinh doanh';
                        $activeChild = 'Bảng theo dõi tái ký cá nhân';
                    } elseif (request()->routeIs('app.reports.sales.tracking')) {
                        $activeGroup = 'Báo cáo Kinh doanh';
                        $activeChild = 'Bảng theo dõi doanh số';
                    } elseif (request()->routeIs('app.reports.sales.revenue')) {
                        $activeGroup = 'Báo cáo Kinh doanh';
                        $activeChild = 'Doanh số thực thu';
                    } elseif (request()->routeIs('app.reports.consulting-work.*')) {
                        $activeGroup = 'Báo cáo Tư vấn';
                        $activeChild = match (true) {
                            request()->routeIs('app.reports.consulting-work.waste') => 'Chất thải & Tiếng ồn',
                            request()->routeIs('app.reports.consulting-work.consulting') => 'Pháp lý & Hồ sơ MT',
                            request()->routeIs('app.reports.consulting-work.project') => 'Kỹ thuật & Ứng phó SC',
                            request()->routeIs('app.reports.consulting-work.commercial') => 'NC & CĐ Công nghệ',
                            request()->routeIs('app.reports.consulting-work.sustainability') => 'TV & BC PTBV',
                            request()->routeIs('app.reports.consulting-work.energy') => 'Phát thải & Năng lượng',
                            default => 'Chất thải & Tiếng ồn',
                        };
                    } elseif (request()->routeIs('app.reports.technical.*')) {
                        $activeGroup = 'Báo cáo Kỹ thuật';
                        $activeChild = match (true) {
                            request()->routeIs('app.reports.technical.waste') => 'BC Chất thải & Tiếng ồn',
                            request()->routeIs('app.reports.technical.consulting') => 'Hồ sơ môi trường',
                            request()->routeIs('app.reports.technical.project') => 'BC Kỹ thuật & Ứng phó SC',
                            request()->routeIs('app.reports.technical.commercial') => 'BC NC & CĐ Công nghệ',
                            request()->routeIs('app.reports.technical.sustainability') => 'BC TV & BC PTBV',
                            request()->routeIs('app.reports.technical.energy') => 'BC Phát thải & Năng lượng',
                            default => 'BC Chất thải & Tiếng ồn',
                        };
                    } elseif (request()->routeIs('app.invoices.bao-chau')) {
                        $activeGroup = 'Quản lý hóa đơn';
                        $activeChild = 'Hóa đơn Bảo Châu';
                    } elseif (request()->routeIs('app.invoices.handlers')) {
                        $activeGroup = 'Quản lý hóa đơn';
                        $activeChild = 'Hóa đơn nhà thầu phụ';
                    }

                    // TV/KT: redirect activeGroup to their own section when on contract pages
                    if ($activeGroup === 'Quản lý hợp đồng' && $currentUser->hasAnyRole(['tu-van', 'ky-thuat'])) {
                        $activeGroup = $currentUser->hasRole('tu-van') ? 'Bộ phận tư vấn' : 'Bộ phận kỹ thuật';
                    }
                @endphp

                @unless ($currentUser->hasRole('it'))
                    <li class="app-sidebar-menu-item">
                        <a href="{{ $currentUser->hasAnyRole(['giam-doc', 'admin', 'quan-ly']) ? route('app.dashboard') : route('app.home') }}"
                            class="menu-link d-flex align-items-center {{ request()->routeIs('app.home') || request()->is('/') || ($currentUser->hasAnyRole(['giam-doc', 'admin', 'quan-ly']) && request()->routeIs('app.dashboard')) ? 'active menu-current' : '' }}">
                            <span class="menu-icon flex-shrink-0">
                                <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.5 1.5L1.5 7V15.5H6.5V11H10.5V15.5H15.5V7L8.5 1.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="menu-title flex-grow-1">{{ $currentUser->hasAnyRole(['giam-doc', 'admin', 'quan-ly']) ? 'Bảng điều khiển' : 'Bảng xếp hạng' }}</span>
                        </a>
                    </li>
                @endunless

                @unless ($currentUser->hasAnyRole(['it', 'tu-van', 'ky-thuat', 'giam-doc', 'admin', 'quan-ly']))
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
                            {{-- /bang-dieu-khien --}}
                        </a>
                    </li>
                @endunless

                @foreach ($groupMenus as $menu)
                    @can($menu['permission'])
                        @if (!isset($menu['allow_roles']) || $currentUser->hasAnyRole($menu['allow_roles']))
                            <li class="app-sidebar-menu-item">
                                @if(isset($menu['href']))
                                <a href="{{ $menu['href'] }}"
                                    class="menu-link d-flex align-items-center {{ $menu['title'] === $activeGroup ? 'active' : '' }}">
                                    <span class="menu-icon flex-shrink-0">{!! $menu['icon'] !!}</span>
                                    <span class="menu-title flex-grow-1">{{ $menu['title'] }}</span>
                                </a>
                                @else
                                <a href="javascript:void(0)"
                                    class="menu-link d-flex align-items-center {{ $menu['title'] === $activeGroup ? 'active' : '' }}">
                                    <span class="menu-icon flex-shrink-0">{!! $menu['icon'] !!}</span>
                                    <span class="menu-title flex-grow-1">
                                        {{ $menu['title'] }}
                                        <span class="menu-arrow">{!! $chevronIcon !!}</span>
                                    </span>
                                </a>

                                <ul class="app-sidebar-submenu"
                                    style="display: {{ $menu['title'] === $activeGroup ? 'block' : 'none' }};">
                                    @foreach ($menu['children'] as $child)
                                        @continue(
                                            $child === 'Bảng theo dõi báo giá' &&
                                            !$currentUser->hasAnyRole(['kinh-doanh', 'tp-kinh-doanh', 'giam-doc'])
                                        )
                                        @continue(
                                            $child === 'Đăng ký mục tiêu doanh số' &&
                                            !$currentUser->hasAnyRole(['kinh-doanh', 'tp-kinh-doanh'])
                                        )

                                        @php
                                            $childActive = $menu['title'] === $activeGroup && $child === $activeChild;
                                            $childIcon =
                                                $menu['title'] === 'Nội bộ'
                                                    ? $fileIcon
                                                    : (str_starts_with($menu['title'], 'Báo cáo')
                                                        ? $reportNodeIcon
                                                        : $docIcon);
                                            $childLabel = $child;

                                            if ($menu['title'] === 'Quản lý hợp đồng') {
                                                $childLabel = match ($child) {
                                                    'HĐ Chất thải & Tiếng ồn' => 'Chất thải & Tiếng ồn',
                                                    'HĐ Pháp lý & Hồ sơ MT' => 'Hồ sơ môi trường',
                                                    'HĐ Kỹ thuật & Ứng phó SC' => 'Kỹ thuật & Ứng phó SC',
                                                    'HĐ NC & CĐ Công nghệ' => 'NC & CĐ Công nghệ',
                                                    'HĐ TV & BC PTBV' => 'TV & BC PTBV',
                                                    'HĐ Phát thải & Năng lượng' => 'Phát thải & Năng lượng',
                                                    default => $child,
                                                };
                                            }
                                        @endphp
                                        @php
                                            $href = 'javascript:void(0)';
                                            if ($menu['title'] === 'Nội bộ' && $child === 'Quy định') {
                                                $href = route('app.internal-docs.index');
                                            } elseif ($child === 'HĐ Chất thải & Tiếng ồn') {
                                                $href = route('app.contracts.waste.index');
                                            } elseif ($child === 'HĐ Pháp lý & Hồ sơ MT') {
                                                $href = route('app.contracts.consulting.index');
                                            } elseif ($child === 'HĐ Kỹ thuật & Ứng phó SC') {
                                                $href = route('app.contracts.project.index');
                                            } elseif ($child === 'HĐ NC & CĐ Công nghệ') {
                                                $href = route('app.contracts.commercial.index');
                                            } elseif ($child === 'HĐ TV & BC PTBV') {
                                                $href = route('app.contracts.sustainability.index');
                                            } elseif ($child === 'HĐ Phát thải & Năng lượng') {
                                                $href = route('app.contracts.energy.index');
                                            } elseif ($menu['title'] === 'Báo cáo ngày' && $child === 'Báo cáo ngày') {
                                                $href = route('app.daily-reports.index');
                                            } elseif (
                                                $menu['title'] === 'Bộ phận kế toán' &&
                                                $child === 'Yêu cầu chi hoa hồng'
                                            ) {
                                                $href = route('app.commissions.index');
                                            } elseif (
                                                $menu['title'] === 'Bộ phận kinh doanh' &&
                                                $child === 'Doanh số tái ký'
                                            ) {
                                                $href = route('app.sales.renewal.index');
                                            } elseif (
                                                $menu['title'] === 'Bộ phận kinh doanh' &&
                                                $child === 'Doanh số theo tiến độ'
                                            ) {
                                                $href = route('app.sales.progressive.index');
                                            } elseif (
                                                $menu['title'] === 'Bộ phận kinh doanh' &&
                                                $child === 'Đăng ký mục tiêu doanh số'
                                            ) {
                                                $href = route('app.sales.target-registration');
                                            } elseif (
                                                $menu['title'] === 'Chuyển phát thư' &&
                                                $child === 'Quản lý chuyển phát'
                                            ) {
                                                $href = route('app.postal-deliveries.index');
                                            } elseif ($child === 'Bảng theo dõi báo giá') {
                                                $href = route('app.quotation-tracking.index');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Kinh doanh' &&
                                                $child === 'Bảng tổng kết doanh số'
                                            ) {
                                                $href = route('app.reports.sales.summary');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Kinh doanh' &&
                                                $child === 'Bảng doanh số cam kết'
                                            ) {
                                                $href = route('app.reports.sales.target');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Kinh doanh' &&
                                                $child === 'Bảng tổng kết'
                                            ) {
                                                $href = route('app.reports.sales.overview');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Kinh doanh' &&
                                                $child === 'Bảng doanh số cá nhân'
                                            ) {
                                                $href = route('app.reports.sales.personal');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Kinh doanh' &&
                                                $child === 'Bảng theo dõi tái ký cá nhân'
                                            ) {
                                                $href = route('app.reports.sales.renewal-personal');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Kinh doanh' &&
                                                $child === 'Bảng theo dõi doanh số'
                                            ) {
                                                $href = route('app.reports.sales.tracking');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Kinh doanh' &&
                                                $child === 'Doanh số thực thu'
                                            ) {
                                                $href = route('app.reports.sales.revenue');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Tư vấn' &&
                                                $child === 'Chất thải & Tiếng ồn'
                                            ) {
                                                $href = route('app.reports.consulting-work.waste');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Tư vấn' &&
                                                $child === 'Pháp lý & Hồ sơ MT'
                                            ) {
                                                $href = route('app.reports.consulting-work.consulting');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Tư vấn' &&
                                                $child === 'Kỹ thuật & Ứng phó SC'
                                            ) {
                                                $href = route('app.reports.consulting-work.project');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Tư vấn' &&
                                                $child === 'NC & CĐ Công nghệ'
                                            ) {
                                                $href = route('app.reports.consulting-work.commercial');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Tư vấn' &&
                                                $child === 'TV & BC PTBV'
                                            ) {
                                                $href = route('app.reports.consulting-work.sustainability');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Tư vấn' &&
                                                $child === 'Phát thải & Năng lượng'
                                            ) {
                                                $href = route('app.reports.consulting-work.energy');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Kỹ thuật' &&
                                                $child === 'BC Chất thải & Tiếng ồn'
                                            ) {
                                                $href = route('app.reports.technical.waste');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Kỹ thuật' &&
                                                $child === 'Hồ sơ môi trường'
                                            ) {
                                                $href = route('app.reports.technical.consulting');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Kỹ thuật' &&
                                                $child === 'BC Kỹ thuật & Ứng phó SC'
                                            ) {
                                                $href = route('app.reports.technical.project');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Kỹ thuật' &&
                                                $child === 'BC NC & CĐ Công nghệ'
                                            ) {
                                                $href = route('app.reports.technical.commercial');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Kỹ thuật' &&
                                                $child === 'BC TV & BC PTBV'
                                            ) {
                                                $href = route('app.reports.technical.sustainability');
                                            } elseif (
                                                $menu['title'] === 'Báo cáo Kỹ thuật' &&
                                                $child === 'BC Phát thải & Năng lượng'
                                            ) {
                                                $href = route('app.reports.technical.energy');
                                            } elseif (
                                                $menu['title'] === 'Bộ phận Marketing' &&
                                                $child === 'Báo cáo hàng ngày'
                                            ) {
                                                $href = route('app.marketing.daily-report.index');
                                            } elseif (
                                                $menu['title'] === 'Quản lý hóa đơn' &&
                                                $child === 'Hóa đơn Bảo Châu'
                                            ) {
                                                $href = route('app.invoices.bao-chau');
                                            } elseif (
                                                $menu['title'] === 'Quản lý hóa đơn' &&
                                                $child === 'Hóa đơn nhà thầu phụ'
                                            ) {
                                                $href = route('app.invoices.handlers');
                                            }
                                        @endphp
                                        <li>
                                            <a href="{{ $href }}"
                                                class="menu-link d-flex align-items-center {{ $childActive ? 'menu-current active' : '' }}">
                                                <span class="menu-icon flex-shrink-0">{!! $childIcon !!}</span>
                                                <span class="menu-title flex-grow-1">{{ $childLabel }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                @endif
                            </li>
                        @endif
                    @endcan
                @endforeach

                {{-- Lịch công tác — standalone, visible to all --}}
                <li class="app-sidebar-menu-item">
                    <a href="{{ route('app.work-schedules.index') }}"
                        class="menu-link d-flex align-items-center {{ request()->routeIs('app.work-schedules.*') ? 'active menu-current' : '' }}">
                        <span class="menu-icon flex-shrink-0">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M3 10H21" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M16 2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M7 14H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M14 14H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M7 18H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="menu-title flex-grow-1">Lịch công tác</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="app-sidebar-footer">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar rounded-pill">
                    <x-user-avatar :user="$currentUser" :size="36" />
                </div>
                <div>
                    <h6 class="mb-0">{{ $currentUser?->name ?? 'Người dùng' }}</h6>
                    @php
                        $roleLabels = [
                            'it' => 'IT / Quản trị',
                            'giam-doc' => 'Giám đốc',
                            'tp-kinh-doanh' => 'Trưởng phòng KD',
                            'kinh-doanh' => 'Nhân viên KD',
                            'tu-van' => 'Tư vấn',
                            'ky-thuat' => 'Kỹ thuật',
                            'marketing' => 'Marketing',
                            'ke-toan' => 'Kế toán',
                            'quan-ly' => 'Quản lý',
                            'hcns' => 'Hành chính NS',
                        ];

                        $rolePriority = ['it', 'giam-doc', 'quan-ly', 'tp-kinh-doanh', 'hcns', 'ke-toan', 'marketing', 'tu-van', 'ky-thuat', 'kinh-doanh'];
                        $primaryRole = collect($rolePriority)->first(fn ($role) => $currentUser?->hasRole($role));

                        if (!$primaryRole) {
                            $primaryRole = $currentUser?->roles?->first()?->name;
                        }
                    @endphp
                    <span
                        class="text-muted">{{ $roleLabels[$primaryRole] ?? 'Nhân viên' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
