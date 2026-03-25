@php
    $currentUser = auth()->user();
    $avatarUrl = $currentUser && $currentUser->avatar
        ? asset('storage/' . $currentUser->avatar)
        : asset('assets/images/10.jpg');
@endphp

<div id="app-sidebar" class="app-sidebar overflow-hidden">
    <div class="app-sidebar-wrapper">
        <div class="app-sidebar-header d-flex align-items-center justify-content-between">
            <a href="{{ route('app.dashboard') }}" class="app-sidebar-logo text-decoration-none">
                <span class="fw-bolder fs-3 text-primary" style="letter-spacing: 2px;">BẢO CHÂU</span>
            </a>

            <button type="button" class="app-sidebar-close-btn app-sidebar-mobile-close d-xl-none">
                <svg width="20" height="12" viewBox="0 0 20 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10.6923 10.2857L6.53846 6M6.53846 6L10.6923 1.71429M6.53846 6L19 6M1 11L1 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </button>
        </div>

        <div id="app-sidebar-menu" class="app-sidebar-menu">
            <ul>
                <li class="app-sidebar-menu-item">
                    <a href="{{ route('app.dashboard') }}" class="menu-link d-flex align-items-center {{ request()->routeIs('app.dashboard') ? 'active menu-current' : '' }}">
                        <span class="menu-icon flex-shrink-0">
                            <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="0.75" y="0.75" width="6.00021" height="7.5" rx="1.5" stroke="currentColor" stroke-width="1.5"></rect>
                                <rect x="0.75" y="11.2499" width="6.00021" height="4.5" rx="1.5" stroke="currentColor" stroke-width="1.5"></rect>
                                <rect x="9.74976" y="8.25" width="6.00021" height="7.5" rx="1.5" stroke="currentColor" stroke-width="1.5"></rect>
                                <rect x="9.74976" y="0.75" width="6.00021" height="4.5" rx="1.5" stroke="currentColor" stroke-width="1.5"></rect>
                            </svg>
                        </span>
                        <span class="menu-title flex-grow-1">Bảng điều khiển</span>
                    </a>
                </li>

                <li class="app-sidebar-menu-heading">
                    <span>
                        <span class="app-sidebar-menu-heading-line"></span>
                        QUẢN LÝ
                    </span>
                </li>

                @can('users.view')
                <li class="app-sidebar-menu-item">
                    <a href="{{ route('app.users.index') }}" class="menu-link d-flex align-items-center {{ request()->routeIs('app.users.*') ? 'active menu-current' : '' }}">
                        <span class="menu-icon flex-shrink-0">
                            <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5.94234 5.9423C7.37615 5.9423 8.53849 4.77997 8.53849 3.34615C8.53849 1.91234 7.37615 0.75 5.94234 0.75C4.50853 0.75 3.34619 1.91234 3.34619 3.34615C3.34619 4.77997 4.50853 5.9423 5.94234 5.9423Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M11.1346 14.5961H0.75V13.4423C0.75 12.0652 1.29704 10.7445 2.27079 9.77079C3.24453 8.79705 4.56521 8.25 5.9423 8.25C7.31938 8.25 8.64006 8.79705 9.6138 9.77079C10.5875 10.7445 11.1346 12.0652 11.1346 13.4423V14.5961Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                        <span class="menu-title flex-grow-1">Người dùng</span>
                    </a>
                </li>
                @endcan

                @can('roles.view')
                <li class="app-sidebar-menu-item">
                    <a href="{{ route('app.roles.index') }}" class="menu-link d-flex align-items-center {{ request()->routeIs('app.roles.*') ? 'active menu-current' : '' }}">
                        <span class="menu-icon flex-shrink-0">
                            <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.5 1.5L2.5 4.5V8.5C2.5 12.5 5.5 15.5 8.5 16.5C11.5 15.5 14.5 12.5 14.5 8.5V4.5L8.5 1.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="menu-title flex-grow-1">Quản lý vai trò quyền hạn</span>
                    </a>
                </li>
                @endcan

                @can('departments.view')
                <li class="app-sidebar-menu-item">
                    <a href="{{ route('app.departments.index') }}" class="menu-link d-flex align-items-center {{ request()->routeIs('app.departments.*') ? 'active menu-current' : '' }}">
                        <span class="menu-icon flex-shrink-0">
                            <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.5 15.5V5.5L8.5 1.5L14.5 5.5V15.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6.5 15.5V10.5H10.5V15.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="menu-title flex-grow-1">Phòng ban</span>
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

                    // Mỗi menu gắn với permission tương ứng
                    $groupMenus = [
                        [
                            'title' => 'Quản lý hợp đồng',
                            'icon' => $stackIcon,
                            'permission' => 'contracts-waste.view',
                            'children' => ['Hợp đồng chất thải', 'Hợp đồng tư vấn', 'Hợp đồng dự án', 'Hợp đồng thương mại'],
                        ],
                        [
                            'title' => 'Quản lý hóa đơn',
                            'icon' => $stackIcon,
                            'permission' => 'invoices.view',
                            'children' => ['Hóa đơn Bảo Châu', 'Hóa đơn chủ xử lý'],
                        ],
                        [
                            'title' => 'Chuyển phát thư',
                            'icon' => $stackIcon,
                            'permission' => 'mail-delivery.view',
                            'children' => ['Quản lý chuyển phát'],
                        ],
                        [
                            'title' => 'Bộ phận kinh doanh',
                            'icon' => $stackIcon,
                            'permission' => 'sales-quotation.view',
                            'children' => ['Doanh số báo giá', 'Doanh số tái ký', 'Doanh số theo tiến độ', 'Bảng theo dõi báo giá'],
                        ],
                        [
                            'title' => 'Bộ phận chất thải',
                            'icon' => $stackIcon,
                            'permission' => 'waste-requests.view',
                            'children' => ['Yêu cầu gom rác'],
                        ],
                        [
                            'title' => 'Bộ phận tư vấn',
                            'icon' => $stackIcon,
                            'permission' => 'consulting-requests.view',
                            'children' => ['Yêu cầu báo cáo', 'Yêu cầu đo mẫu', 'Yêu cầu đo mẫu dự án', 'Yêu cầu xuất mẫu', 'Yêu cầu GPMT/ĐTM', 'Yêu cầu ĐKMT', 'Yêu cầu VHTN'],
                        ],
                        [
                            'title' => 'Bộ phận dự án',
                            'icon' => $stackIcon,
                            'permission' => 'project-requests.view',
                            'children' => ['Yêu cầu dự án lớn', 'Yêu cầu dự án nhỏ', 'Yêu cầu khảo sát dự án'],
                        ],
                        [
                            'title' => 'Bộ phận thương mại',
                            'icon' => $stackIcon,
                            'permission' => 'commercial-requests.view',
                            'children' => ['Yêu cầu thực hiện BPTM'],
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
                            'children' => ['Yêu cầu kỹ thuật', 'Lịch công tác kỹ thuật', 'Phiếu kết quả'],
                        ],
                        [
                            'title' => 'Báo cáo Marketing',
                            'icon' => $usersIcon,
                            'permission' => 'reports.view',
                            'children' => ['Bảng tổng kết', 'Bảng mục tiêu cam kết'],
                        ],
                        [
                            'title' => 'Báo cáo Kinh doanh',
                            'icon' => $usersIcon,
                            'permission' => 'reports.view',
                            'children' => ['Bảng tổng kết doanh số', 'Bảng doanh số cam kết', 'Bảng tổng kết', 'Bảng doanh số cá nhân', 'Bảng theo dõi tái ký cá nhân', 'Bảng thành tích', 'Bảng theo dõi doanh số'],
                        ],
                        [
                            'title' => 'Báo cáo HC - CTR',
                            'icon' => $usersIcon,
                            'permission' => 'reports.view',
                            'children' => ['Hợp đồng', 'Hóa đơn', 'Công nợ', 'Gom rác'],
                        ],
                        [
                            'title' => 'Báo cáo Tư vấn',
                            'icon' => $usersIcon,
                            'permission' => 'reports.view',
                            'children' => ['Báo cáo chung', 'Bảng theo dõi đo mẫu', 'Báo cáo', 'GPMT/ĐTM', 'ĐKMT', 'VHTN'],
                        ],
                        [
                            'title' => 'Báo cáo Kỹ thuật',
                            'icon' => $usersIcon,
                            'permission' => 'reports.view',
                            'children' => ['Báo cáo hiện trường', 'Lịch xe', 'Vật tư'],
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
                            'children' => ['Báo cáo ngày'],
                        ],
                    ];

                    $activeGroup = null;
                    $activeChild = null;

                    if (request()->routeIs('app.internal-docs.*')) {
                        $activeGroup = 'Nội bộ';
                        $activeChild = 'Quy định';
                    } elseif (request()->routeIs('app.contracts.waste.*')) {
                        $activeGroup = 'Quản lý hợp đồng';
                        $activeChild = 'Hợp đồng chất thải';
                    } elseif (request()->routeIs('app.contracts.consulting.*')) {
                        $activeGroup = 'Quản lý hợp đồng';
                        $activeChild = 'Hợp đồng tư vấn';
                    } elseif (request()->routeIs('app.contracts.project.*')) {
                        $activeGroup = 'Quản lý hợp đồng';
                        $activeChild = 'Hợp đồng dự án';
                    } elseif (request()->routeIs('app.contracts.commercial.*')) {
                        $activeGroup = 'Quản lý hợp đồng';
                        $activeChild = 'Hợp đồng thương mại';
                    } elseif (request()->routeIs('app.daily-reports.*')) {
                        $activeGroup = 'Báo cáo ngày';
                        $activeChild = 'Báo cáo ngày';
                    } elseif (request()->routeIs('app.commissions.*')) {
                        $activeGroup = 'Bộ phận kế toán';
                        $activeChild = 'Yêu cầu chi hoa hồng';
                    } elseif (request()->routeIs('app.sales.quotation.*')) {
                        $activeGroup = 'Bộ phận kinh doanh';
                        $activeChild = 'Doanh số báo giá';
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
                        $activeGroup = 'Báo cáo Kinh doanh';
                        $activeChild = 'Bảng theo dõi báo giá';
                    }
                @endphp

                <li class="app-sidebar-menu-item">
                    <a href="javascript:void(0)" class="menu-link d-flex align-items-center">
                        <span class="menu-icon flex-shrink-0">
                            <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.125 3.54166H4.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M2.125 6.37499H8.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M2.125 9.20834H11.3333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M2.125 12.0417H14.1667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="menu-title flex-grow-1">Bảng xếp hạng</span>
                    </a>
                </li>

                <li class="app-sidebar-menu-item">
                    <a href="javascript:void(0)" class="menu-link d-flex align-items-center">
                        <span class="menu-icon flex-shrink-0">
                            <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="8.5" cy="8.5" r="6.75" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8.5 8.5L11.3333 6.37499" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M8.5 8.5L6.375 3.54166" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <circle cx="8.5" cy="8.5" r="1" fill="currentColor"/>
                            </svg>
                        </span>
                        <span class="menu-title flex-grow-1">Bảng thống kê</span>
                    </a>
                </li>

                @foreach ($groupMenus as $menu)
                    @can($menu['permission'])
                    <li class="app-sidebar-menu-item">
                        <a href="javascript:void(0)" class="menu-link d-flex align-items-center {{ $menu['title'] === $activeGroup ? 'active' : '' }}">
                            <span class="menu-icon flex-shrink-0">{!! $menu['icon'] !!}</span>
                            <span class="menu-title flex-grow-1">
                                {{ $menu['title'] }}
                                <span class="menu-arrow">{!! $chevronIcon !!}</span>
                            </span>
                        </a>

                        <ul class="app-sidebar-submenu" style="display: {{ $menu['title'] === $activeGroup ? 'block' : 'none' }};">
                            @foreach ($menu['children'] as $child)
                                @php
                                    $childActive = $menu['title'] === $activeGroup && $child === $activeChild;
                                    $childIcon = $menu['title'] === 'Nội bộ'
                                        ? $fileIcon
                                        : (str_starts_with($menu['title'], 'Báo cáo') ? $reportNodeIcon : $docIcon);
                                @endphp
                                    @php
                                        $href = 'javascript:void(0)';
                                        if ($menu['title'] === 'Nội bộ' && $child === 'Quy định') {
                                            $href = route('app.internal-docs.index');
                                        } elseif ($menu['title'] === 'Quản lý hợp đồng' && $child === 'Hợp đồng chất thải') {
                                            $href = route('app.contracts.waste.index');
                                        } elseif ($menu['title'] === 'Quản lý hợp đồng' && $child === 'Hợp đồng tư vấn') {
                                            $href = route('app.contracts.consulting.index');
                                        } elseif ($menu['title'] === 'Quản lý hợp đồng' && $child === 'Hợp đồng dự án') {
                                            $href = route('app.contracts.project.index');
                                        } elseif ($menu['title'] === 'Quản lý hợp đồng' && $child === 'Hợp đồng thương mại') {
                                            $href = route('app.contracts.commercial.index');
                                        } elseif ($menu['title'] === 'Báo cáo ngày' && $child === 'Báo cáo ngày') {
                                            $href = route('app.daily-reports.index');
                                        } elseif (request()->routeIs('app.commissions.*')) {
                                            $href = route('app.commissions.index');
                                        } elseif ($menu['title'] === 'Bộ phận kinh doanh' && $child === 'Doanh số báo giá') {
                                            $href = route('app.sales.quotation.index');
                                        } elseif ($menu['title'] === 'Bộ phận kinh doanh' && $child === 'Doanh số tái ký') {
                                            $href = route('app.sales.renewal.index');
                                        } elseif ($menu['title'] === 'Bộ phận kinh doanh' && $child === 'Doanh số theo tiến độ') {
                                            $href = route('app.sales.progressive.index');
                                        } elseif ($menu['title'] === 'Chuyển phát thư' && $child === 'Quản lý chuyển phát') {
                                            $href = route('app.postal-deliveries.index');
                                        } elseif ($child === 'Bảng theo dõi báo giá') {
                                            $href = route('app.quotation-tracking.index');
                                        }

                                        $childActive = ($menu['title'] === 'Nội bộ' && $child === 'Quy định' && request()->routeIs('app.internal-docs.*')) ||
                                                      ($menu['title'] === 'Quản lý hợp đồng' && $child === 'Hợp đồng chất thải' && request()->routeIs('app.contracts.waste.*')) ||
                                                      ($menu['title'] === 'Quản lý hợp đồng' && $child === 'Hợp đồng tư vấn' && request()->routeIs('app.contracts.consulting.*')) ||
                                                      ($menu['title'] === 'Quản lý hợp đồng' && $child === 'Hợp đồng dự án' && request()->routeIs('app.contracts.project.*')) ||
                                                      ($menu['title'] === 'Quản lý hợp đồng' && $child === 'Hợp đồng thương mại' && request()->routeIs('app.contracts.commercial.*')) ||
                                                      ($menu['title'] === 'Báo cáo ngày' && $child === 'Báo cáo ngày' && request()->routeIs('app.daily-reports.*')) ||
                                                      ($menu['title'] === 'Bộ phận kế toán' && $child === 'Yêu cầu chi hoa hồng' && request()->routeIs('app.commissions.*')) ||
                                                      ($menu['title'] === 'Bộ phận kinh doanh' && $child === 'Doanh số báo giá' && request()->routeIs('app.sales.quotation.*')) ||
                                                      ($menu['title'] === 'Bộ phận kinh doanh' && $child === 'Doanh số tái ký' && request()->routeIs('app.sales.renewal.*')) ||
                                                      ($menu['title'] === 'Bộ phận kinh doanh' && $child === 'Doanh số theo tiến độ' && request()->routeIs('app.sales.progressive.*')) ||
                                                      ($menu['title'] === 'Chuyển phát thư' && $child === 'Quản lý chuyển phát' && request()->routeIs('app.postal-deliveries.*')) ||
                                                      ($child === 'Bảng theo dõi báo giá' && request()->routeIs('app.quotation-tracking.*'));
                                    @endphp
                                    <li>
                                        <a href="{{ $href }}" class="menu-link d-flex align-items-center {{ $childActive ? 'menu-current active' : '' }}">
                                            <span class="menu-icon flex-shrink-0">{!! $childIcon !!}</span>
                                            <span class="menu-title flex-grow-1">{{ $child }}</span>
                                        </a>
                                    </li>
                            @endforeach
                        </ul>
                    </li>
                    @endcan
                @endforeach
            </ul>
        </div>

        <div class="app-sidebar-footer">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar rounded-pill">
                    <img src="{{ $avatarUrl }}" alt="Admin" style="object-fit: cover;">
                </div>
                <div>
                    <h6 class="mb-0">{{ $currentUser?->name ?? 'Người dùng' }}</h6>
                    <span class="text-muted">{{ $currentUser?->roles?->first()?->name === 'it' ? 'IT / Quản trị' : ($currentUser?->roles?->first()?->name === 'quan-ly' ? 'Quản lý' : ($currentUser?->roles?->first()?->name === 'kinh-doanh' ? 'Kinh doanh' : ($currentUser?->roles?->first()?->name === 'ke-toan' ? 'Kế toán' : ($currentUser?->roles?->first()?->name === 'tu-van' ? 'Tư vấn' : ($currentUser?->roles?->first()?->name === 'ky-thuat' ? 'Kỹ thuật' : 'Nhân viên'))))) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
