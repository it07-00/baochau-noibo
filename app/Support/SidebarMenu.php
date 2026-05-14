<?php

namespace App\Support;

use App\Enums\Role;
use Illuminate\Support\Facades\Request;

/**
 * Owns sidebar menu definitions and active-state resolution.
 * Keep all route→group/child mapping here; never in Blade.
 */
class SidebarMenu
{
    // ── Icon SVGs ─────────────────────────────────────────────────────────────

    public static function icon(string $name): string
    {
        return match ($name) {
            'stack' => '<svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.49996 1.75L1.75 5.125L8.49996 8.5L15.25 5.125L8.49996 1.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M1.75 8.5L8.49996 11.875L15.25 8.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M1.75 11.875L8.49996 15.25L15.25 11.875" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'users' => '<svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.02084 6.02083C7.38905 6.02083 8.49813 4.91175 8.49813 3.54354C8.49813 2.17533 7.38905 1.06625 6.02084 1.06625C4.65263 1.06625 3.54355 2.17533 3.54355 3.54354C3.54355 4.91175 4.65263 6.02083 6.02084 6.02083Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12.0417 6.72917C13.019 6.72917 13.8115 5.93667 13.8115 4.95938C13.8115 3.98208 13.019 3.18958 12.0417 3.18958" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M1.7746 12.7492C1.7746 11.2507 2.98204 10.0433 4.48056 10.0433H7.56023C9.05875 10.0433 10.2662 11.2507 10.2662 12.7492" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.3334 10.75H12.0417C13.2148 10.75 14.1667 11.7019 14.1667 12.875" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'doc' => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.3333 14H4.66667C3.93029 14 3.33333 13.403 3.33333 12.6667V3.33333C3.33333 2.59695 3.93029 2 4.66667 2H11.3333C12.0697 2 12.6667 2.59695 12.6667 3.33333V12.6667C12.6667 13.403 12.0697 14 11.3333 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 6H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M6 9.33333H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',

            'report' => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2.66667" y="2.66667" width="10.6667" height="10.6667" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M6.66667 5.66667L10.6667 8L6.66667 10.3333V5.66667Z" fill="currentColor"/></svg>',

            'file' => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 2.66667H5.33333C4.59695 2.66667 4 3.26362 4 4V12C4 12.7364 4.59695 13.3333 5.33333 13.3333H10.6667C11.403 13.3333 12 12.7364 12 12V4.66667L10 2.66667Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 2.66667V4.66667H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.66667 8H9.33333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',

            'chevron' => '<svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.5 1.5L5.5 6L1.5 10.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            default => '',
        };
    }

    // ── Accounting-specific menu (ke-toan role) ───────────────────────────────

    private static function accountingMenu(): array
    {
        $accounting = [
            [
                'title'      => 'Dòng tiền',
                'icon'       => 'stack',
                'permission' => 'cash-flow.view',
                'href'       => route('app.finance.cash-flow'),
            ],
            [
                'title'      => 'Hoa hồng',
                'icon'       => 'stack',
                'permission' => 'commissions.view',
                'children'   => ['Yêu cầu chi hoa hồng'],
            ],
            [
                'title'      => 'Quản lý hợp đồng',
                'icon'       => 'stack',
                'permission' => 'payment-schedules.view',
                'children'   => self::contractTypes(),
            ],
        ];

        $operations = [
            [
                'title'      => 'Báo cáo ngày',
                'icon'       => 'report',
                'permission' => 'daily-reports.view',
                'href'       => route('app.daily-reports.index'),
            ],
        ];

        return array_merge(
            array_map(fn ($m) => $m + ['section' => 'KẾ TOÁN'], $accounting),
            array_map(fn ($m) => $m + ['section' => 'NGHIỆP VỤ'], $operations),
        );
    }

    // ── Menu definitions ──────────────────────────────────────────────────────

    /** @return array<int, array{title: string, icon: string, permission: string, section: string, href?: string, children?: string[]}> */
    public static function all(?\App\Models\User $user = null): array
    {
        if ($user && $user->hasRole(\App\Enums\Role::KE_TOAN->value)) {
            return self::accountingMenu();
        }

        $operations = [
            [
                'title'      => 'Báo cáo ngày',
                'icon'       => 'report',
                'permission' => 'daily-reports.view',
                'href'       => route('app.daily-reports.index'),
            ],
            [
                'title'      => 'Quản lý hợp đồng',
                'icon'       => 'stack',
                'permission' => 'payment-schedules.view',
                'children'   => self::contractTypes(),
            ],
            [
                'title'      => 'Bộ phận kinh doanh',
                'icon'       => 'stack',
                'permission' => 'quotation-tracking.view',
                'children'   => ['Bảng theo dõi báo giá', 'Đăng ký mục tiêu doanh số'],
            ],
            [
                'title'      => 'Bộ phận tư vấn',
                'icon'       => 'stack',
                'permission' => 'consulting-requests.view',
                'children'   => self::contractTypes(),
            ],
            [
                'title'      => 'Bộ phận kỹ thuật',
                'icon'       => 'stack',
                'permission' => 'technical-requests.view',
                'children'   => ['HĐ Pháp lý & Hồ sơ MT'],
            ],
            [
                'title'      => 'Bộ phận Marketing',
                'icon'       => 'users',
                'permission' => 'marketing-reports.view',
                'children'   => ['Báo cáo hàng ngày'],
            ],
            [
                'title'      => 'Nội bộ',
                'icon'       => 'users',
                'permission' => 'internal-docs.view',
                'children'   => ['Quy định', 'Phần mềm'],
            ],
            [
                'title'      => 'Chuyển phát thư',
                'icon'       => 'stack',
                'permission' => 'mail-delivery-admin.view',
                'children'   => ['Quản lý chuyển phát'],
            ],
        ];

        $finance = [
            [
                'title'      => 'Dòng tiền',
                'icon'       => 'stack',
                'permission' => 'cash-flow.view',
                'href'       => route('app.finance.cash-flow'),
            ],
            [
                'title'      => 'Hoa hồng',
                'icon'       => 'stack',
                'permission' => 'commissions.view',
                'children'   => ['Yêu cầu chi hoa hồng'],
            ],
        ];

        $reports = [
            [
                'title'      => 'Báo cáo Kinh doanh',
                'icon'       => 'users',
                'permission' => 'reports-sales.view',
                'children'   => ['Bảng tổng kết doanh số', 'Bảng doanh số cam kết', 'Bảng doanh số cá nhân'],
            ],
            [
                'title'      => 'Báo cáo Tư vấn',
                'icon'       => 'users',
                'permission' => 'reports-consulting.view',
                'children'   => ['Chất thải & Tiếng ồn', 'Pháp lý & Hồ sơ MT', 'Kỹ thuật & Ứng phó SC', 'NC & CĐ Công nghệ', 'TV & BC PTBV', 'Phát thải & Năng lượng'],
            ],
            [
                'title'      => 'Báo cáo Kỹ thuật',
                'icon'       => 'users',
                'permission' => 'reports-technical.view',
                'children'   => ['Hồ sơ môi trường'],
            ],
        ];

        return array_merge(
            array_map(fn ($m) => $m + ['section' => 'NGHIỆP VỤ'], $operations),
            array_map(fn ($m) => $m + ['section' => 'TÀI CHÍNH'], $finance),
            array_map(fn ($m) => $m + ['section' => 'BÁO CÁO & THỐNG KÊ'], $reports),
        );
    }

    // ── Active state resolution ───────────────────────────────────────────────

    /**
     * Returns ['group' => string|null, 'child' => string|null]
     * based on the current request route.
     */
    public static function resolveActive(\App\Models\User $user): array
    {
        $map = [
            'app.internal-docs.*'                        => ['Nội bộ',               'Quy định'],
            'app.internal-software.*'                    => ['Nội bộ',               'Phần mềm'],
            'app.contracts.waste.*'                      => ['Quản lý hợp đồng',     'HĐ Chất thải & Tiếng ồn'],
            'app.contracts.consulting.*'                 => ['Quản lý hợp đồng',     'HĐ Pháp lý & Hồ sơ MT'],
            'app.contracts.project.*'                    => ['Quản lý hợp đồng',     'HĐ Kỹ thuật & Ứng phó SC'],
            'app.contracts.commercial.*'                 => ['Quản lý hợp đồng',     'HĐ NC & CĐ Công nghệ'],
            'app.contracts.sustainability.*'             => ['Quản lý hợp đồng',     'HĐ TV & BC PTBV'],
            'app.contracts.energy.*'                     => ['Quản lý hợp đồng',     'HĐ Phát thải & Năng lượng'],
            'app.marketing.daily-report.*'               => ['Bộ phận Marketing',    'Báo cáo hàng ngày'],
            'app.daily-reports.*'                        => ['Báo cáo ngày',         'Báo cáo ngày'],
            'app.commissions.*'                          => ['Hoa hồng',             'Yêu cầu chi hoa hồng'],
            'app.finance.cash-flow'                      => ['Dòng tiền',            'Dòng tiền'],
            'app.postal-deliveries.*'                    => ['Chuyển phát thư',      'Quản lý chuyển phát'],
            'app.quotation-tracking.*'                   => ['Bộ phận kinh doanh',   'Bảng theo dõi báo giá'],
            'app.reports.sales.summary'                  => ['Báo cáo Kinh doanh',   'Bảng tổng kết doanh số'],
            'app.sales.target-registration'              => ['Bộ phận kinh doanh',   'Đăng ký mục tiêu doanh số'],
            'app.reports.sales.target'                   => ['Báo cáo Kinh doanh',   'Bảng doanh số cam kết'],
            'app.reports.sales.personal'                 => ['Báo cáo Kinh doanh',   'Bảng doanh số cá nhân'],
            'app.reports.consulting-work.waste'          => ['Báo cáo Tư vấn',       'Chất thải & Tiếng ồn'],
            'app.reports.consulting-work.consulting'     => ['Báo cáo Tư vấn',       'Pháp lý & Hồ sơ MT'],
            'app.reports.consulting-work.project'        => ['Báo cáo Tư vấn',       'Kỹ thuật & Ứng phó SC'],
            'app.reports.consulting-work.commercial'     => ['Báo cáo Tư vấn',       'NC & CĐ Công nghệ'],
            'app.reports.consulting-work.sustainability' => ['Báo cáo Tư vấn',       'TV & BC PTBV'],
            'app.reports.consulting-work.energy'         => ['Báo cáo Tư vấn',       'Phát thải & Năng lượng'],
            'app.reports.technical.consulting'           => ['Báo cáo Kỹ thuật',     'Hồ sơ môi trường'],
        ];

        [$group, $child] = [null, null];

        foreach ($map as $pattern => [$g, $c]) {
            if (Request::routeIs($pattern)) {
                [$group, $child] = [$g, $c];
                break;
            }
        }

        // Redirect contract group for consulting/technical roles to their own section
        if ($group === 'Quản lý hợp đồng' && $user->hasAnyRole(Role::technicalConsultingRoles())) {
            $group = $user->hasRole(Role::TU_VAN->value) ? 'Bộ phận tư vấn' : 'Bộ phận kỹ thuật';
        }

        return compact('group', 'child');
    }

    // ── Child route resolver ──────────────────────────────────────────────────

    /**
     * Returns the href for a given parent menu title + child label.
     * Falls back to 'javascript:void(0)' when no route is mapped.
     */
    public static function childHref(string $menuTitle, string $child): string
    {
        // Contract type children appear across multiple parent menus
        $contractRoutes = [
            'HĐ Chất thải & Tiếng ồn'  => 'app.contracts.waste.index',
            'HĐ Pháp lý & Hồ sơ MT'    => 'app.contracts.consulting.index',
            'HĐ Kỹ thuật & Ứng phó SC'  => 'app.contracts.project.index',
            'HĐ NC & CĐ Công nghệ'      => 'app.contracts.commercial.index',
            'HĐ TV & BC PTBV'           => 'app.contracts.sustainability.index',
            'HĐ Phát thải & Năng lượng' => 'app.contracts.energy.index',
        ];

        if (isset($contractRoutes[$child])) {
            return route($contractRoutes[$child]);
        }

        $specific = [
            'Nội bộ'             => ['Quy định' => 'app.internal-docs.index', 'Phần mềm' => 'app.internal-software.index'],
            'Hoa hồng'           => ['Yêu cầu chi hoa hồng' => 'app.commissions.index'],
            'Bộ phận kinh doanh' => [
                'Đăng ký mục tiêu doanh số'  => 'app.sales.target-registration',
                'Bảng theo dõi báo giá'      => 'app.quotation-tracking.index',
            ],
            'Chuyển phát thư'    => ['Quản lý chuyển phát' => 'app.postal-deliveries.index'],
            'Báo cáo Kinh doanh' => [
                'Bảng tổng kết doanh số' => 'app.reports.sales.summary',
                'Bảng doanh số cam kết'  => 'app.reports.sales.target',
                'Bảng doanh số cá nhân'  => 'app.reports.sales.personal',
            ],
            'Báo cáo Tư vấn'     => [
                'Chất thải & Tiếng ồn'   => 'app.reports.consulting-work.waste',
                'Pháp lý & Hồ sơ MT'     => 'app.reports.consulting-work.consulting',
                'Kỹ thuật & Ứng phó SC'  => 'app.reports.consulting-work.project',
                'NC & CĐ Công nghệ'      => 'app.reports.consulting-work.commercial',
                'TV & BC PTBV'           => 'app.reports.consulting-work.sustainability',
                'Phát thải & Năng lượng' => 'app.reports.consulting-work.energy',
            ],
            'Báo cáo Kỹ thuật'   => ['Hồ sơ môi trường' => 'app.reports.technical.consulting'],
            'Bộ phận Marketing'  => ['Báo cáo hàng ngày' => 'app.marketing.daily-report.index'],
        ];

        $routeName = $specific[$menuTitle][$child] ?? null;

        return $routeName ? route($routeName) : 'javascript:void(0)';
    }

    // ── Child display label ───────────────────────────────────────────────────

    /**
     * Strips "HĐ " prefix for contract-type children displayed in the sidebar.
     */
    public static function childLabel(string $menuTitle, string $child): string
    {
        $contractParents = ['Quản lý hợp đồng', 'Bộ phận tư vấn', 'Bộ phận kỹ thuật'];

        if (in_array($menuTitle, $contractParents, true)) {
            return match ($child) {
                'HĐ Chất thải & Tiếng ồn'  => 'Chất thải & Tiếng ồn',
                'HĐ Pháp lý & Hồ sơ MT'    => 'Hồ sơ môi trường',
                'HĐ Kỹ thuật & Ứng phó SC'  => 'Kỹ thuật & Ứng phó SC',
                'HĐ NC & CĐ Công nghệ'      => 'NC & CĐ Công nghệ',
                'HĐ TV & BC PTBV'           => 'TV & BC PTBV',
                'HĐ Phát thải & Năng lượng' => 'Phát thải & Năng lượng',
                default                     => $child,
            };
        }

        return $child;
    }

    // ── Child icon ────────────────────────────────────────────────────────────

    public static function childIcon(string $menuTitle, string $section): string
    {
        if ($menuTitle === 'Nội bộ') {
            return self::icon('file');
        }

        if ($section === 'BÁO CÁO & THỐNG KÊ') {
            return self::icon('report');
        }

        return self::icon('doc');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** The six contract sub-types, used in multiple menus. */
    private static function contractTypes(): array
    {
        return [
            'HĐ Chất thải & Tiếng ồn',
            'HĐ Pháp lý & Hồ sơ MT',
            'HĐ Kỹ thuật & Ứng phó SC',
            'HĐ NC & CĐ Công nghệ',
            'HĐ TV & BC PTBV',
            'HĐ Phát thải & Năng lượng',
        ];
    }
}
