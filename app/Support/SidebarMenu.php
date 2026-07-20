<?php

namespace App\Support;

use App\Enums\Role;
use App\Models\User;
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
            'stack' => '<i class="fi fi-rr-layers"></i>',
            'users' => '<i class="fi fi-rr-users"></i>',
            'doc' => '<i class="fi fi-rr-document"></i>',
            'report' => '<i class="fi fi-rr-chart-pie-alt"></i>',
            'file' => '<i class="fi fi-rr-file"></i>',
            'chevron' => '',
            default => '',
        };
    }

    // ── Accounting-specific menu (ke-toan role) ───────────────────────────────

    private static function accountingMenu(): array
    {
        $accounting = [
            [
                'title' => 'Dòng tiền',
                'icon' => 'stack',
                'permission' => 'cash-flow.view',
                'href' => route('app.finance.cash-flow'),
            ],
            [
                'title' => 'Hoa hồng',
                'icon' => 'stack',
                'permission' => 'commissions.view',
                'children' => ['Yêu cầu chi hoa hồng'],
            ],
            [
                'title' => 'Quản lý hợp đồng',
                'icon' => 'stack',
                'permission' => 'payment-schedules.view',
                'children' => self::contractTypes(),
            ],
        ];

        $operations = [
            [
                'title' => 'Báo cáo ngày',
                'icon' => 'report',
                'permission' => 'daily-reports.view',
                'href' => route('app.daily-reports.index'),
            ],
        ];

        return array_merge(
            array_map(fn ($m) => $m + ['section' => 'KẾ TOÁN'], $accounting),
            array_map(fn ($m) => $m + ['section' => 'NGHIỆP VỤ'], $operations),
        );
    }

    // ── Menu definitions ──────────────────────────────────────────────────────

    /** @return array<int, array{title: string, icon: string, permission: string, section: string, href?: string, children?: string[]}> */
    public static function all(?User $user = null): array
    {
        if ($user && $user->hasRole(Role::KE_TOAN->value)) {
            return self::accountingMenu();
        }

        $operations = [
            [
                'title' => 'Báo cáo ngày',
                'icon' => 'report',
                'permission' => 'daily-reports.view',
                'href' => route('app.daily-reports.index'),
            ],
            [
                'title' => 'Quản lý hợp đồng',
                'icon' => 'stack',
                'permission' => 'payment-schedules.view',
                'children' => self::contractTypes(),
            ],
            [
                'title' => 'Bộ phận kinh doanh',
                'icon' => 'stack',
                'permission' => 'quotation-tracking.view',
                'children' => ['Bảng theo dõi báo giá', 'Tạo báo giá', 'Đăng ký mục tiêu doanh số', 'Tiến độ dự án'],
            ],
            [
                'title' => 'Bộ phận tư vấn',
                'icon' => 'stack',
                'permission' => 'consulting-requests.view',
                'children' => self::contractTypes(),
            ],
            [
                'title' => 'Bộ phận kỹ thuật',
                'icon' => 'stack',
                'permission' => 'technical-requests.view',
                'children' => ['HĐ Quan trắc và hồ sơ môi trường'],
            ],
            [
                'title' => 'Bộ phận Marketing',
                'icon' => 'users',
                'permission' => 'marketing-reports.view',
                'children' => ['Kế hoạch content'],
            ],
            [
                'title' => 'Nội bộ',
                'icon' => 'users',
                'permission' => 'internal-docs.view',
                'children' => ['Quy định', 'Phần mềm'],
            ],
            [
                'title' => 'Chuyển phát thư',
                'icon' => 'stack',
                'permission' => 'mail-delivery-admin.view',
                'children' => ['Quản lý chuyển phát'],
            ],
        ];

        $finance = [
            [
                'title' => 'Dòng tiền',
                'icon' => 'stack',
                'permission' => 'cash-flow.view',
                'href' => route('app.finance.cash-flow'),
            ],
            [
                'title' => 'Hoa hồng',
                'icon' => 'stack',
                'permission' => 'commissions.view',
                'children' => ['Yêu cầu chi hoa hồng'],
            ],
        ];

        $reports = [
            [
                'title' => 'Báo cáo tổng hợp',
                'icon' => 'report',
                'permission' => 'reports-sales.view',
                'href' => route('app.reports.index'),
            ],
            [
                'title' => 'Báo cáo Kinh doanh',
                'icon' => 'users',
                'permission' => 'reports-sales.view',
                'children' => ['Bảng tổng kết doanh số', 'Bảng doanh số cam kết'],
            ],
            [
                'title' => 'Báo cáo Tư vấn',
                'icon' => 'users',
                'permission' => 'reports-consulting.view',
                'children' => ['Chất thải', 'Quan trắc và hồ sơ môi trường', 'Ứng phó sự cố', 'Nghiên cứu và chuyển đổi công nghệ', 'Phát triển bền vững', 'Giảm phát thải, tiết kiệm năng lượng'],
            ],
            [
                'title' => 'Báo cáo Kỹ thuật',
                'icon' => 'users',
                'permission' => 'reports-technical.view',
                'children' => ['Quan trắc và hồ sơ môi trường'],
            ],
        ];

        return array_merge(
            array_map(fn ($m) => $m + ['section' => 'NGHIỆP VỤ'], $operations),
            array_map(fn ($m) => $m + ['section' => 'TÀI CHÍNH'], $finance),
            array_map(fn ($m) => $m + ['section' => 'BÁO CÁO & THỐNG KÊ'], $reports),
        );
    }

    /**
     * Returns menus grouped by section while preserving section order.
     *
     * @return array<string, array<int, array{title: string, icon: string, permission: string, section: string, href?: string, children?: string[]}>>
     */
    public static function groupedBySection(?User $user = null): array
    {
        $grouped = [];

        foreach (self::all($user) as $menu) {
            $section = $menu['section'];

            if (! isset($grouped[$section])) {
                $grouped[$section] = [];
            }

            $grouped[$section][] = $menu;
        }

        return $grouped;
    }

    public static function roleLabel(?User $user): string
    {
        if (! $user) {
            return 'Nhân viên';
        }

        $primaryRole = collect(Role::priorityList())
            ->first(fn ($r) => $user->hasRole($r))
            ?? $user->roles?->first()?->name;

        return Role::tryFrom($primaryRole ?? '')?->label() ?? 'Nhân viên';
    }

    public static function activeGroup(?User $user): ?string
    {
        return self::resolveActive($user)['group'];
    }

    public static function activeChild(?User $user): ?string
    {
        return self::resolveActive($user)['child'];
    }

    // ── Active state resolution ───────────────────────────────────────────────

    /**
     * Returns ['group' => string|null, 'child' => string|null]
     * based on the current request route.
     */
    public static function resolveActive(User $user): array
    {
        $map = [
            'app.reports.index' => ['Báo cáo tổng hợp',      'Xu hướng tiềm năng'],
            'app.internal-docs.*' => ['Nội bộ',               'Quy định'],
            'app.internal-software.*' => ['Nội bộ',               'Phần mềm'],
            'app.contracts.waste.*' => ['Quản lý hợp đồng',     'HĐ Chất thải'],
            'app.contracts.consulting.*' => ['Quản lý hợp đồng',     'HĐ Quan trắc và hồ sơ môi trường'],
            'app.contracts.project.*' => ['Quản lý hợp đồng',     'HĐ Ứng phó sự cố'],
            'app.contracts.commercial.*' => ['Quản lý hợp đồng',     'HĐ Nghiên cứu và chuyển đổi công nghệ'],
            'app.contracts.sustainability.*' => ['Quản lý hợp đồng',     'HĐ Phát triển bền vững'],
            'app.contracts.energy.*' => ['Quản lý hợp đồng',     'HĐ Giảm phát thải, tiết kiệm năng lượng'],
            'app.marketing.content.*' => ['Bộ phận Marketing',    'Kế hoạch content'],
            'app.daily-reports.*' => ['Báo cáo ngày',         'Báo cáo ngày'],
            'app.commissions.*' => ['Hoa hồng',             'Yêu cầu chi hoa hồng'],
            'app.finance.cash-flow' => ['Dòng tiền',            'Dòng tiền'],
            'app.postal-deliveries.*' => ['Chuyển phát thư',      'Quản lý chuyển phát'],
            'app.quotation-tracking.*' => ['Bộ phận kinh doanh',   'Bảng theo dõi báo giá'],
            'app.quotation-docs.*' => ['Bộ phận kinh doanh',   'Tạo báo giá'],
            'app.reports.sales.summary' => ['Báo cáo Kinh doanh',   'Bảng tổng kết doanh số'],
            'app.sales.target-registration' => ['Bộ phận kinh doanh',   'Đăng ký mục tiêu doanh số'],
            'app.reports.sales.target' => ['Báo cáo Kinh doanh',   'Bảng doanh số cam kết'],
            'app.reports.sales.personal' => ['Báo cáo Kinh doanh',   'Bảng doanh số cá nhân'],
            'app.reports.sales.project-progress' => ['Bộ phận kinh doanh',   'Tiến độ dự án'],
            'app.reports.consulting-work.waste' => ['Báo cáo Tư vấn',       'Chất thải'],
            'app.reports.consulting-work.consulting' => ['Báo cáo Tư vấn',       'Quan trắc và hồ sơ môi trường'],
            'app.reports.consulting-work.project' => ['Báo cáo Tư vấn',       'Ứng phó sự cố'],
            'app.reports.consulting-work.commercial' => ['Báo cáo Tư vấn',       'Nghiên cứu và chuyển đổi công nghệ'],
            'app.reports.consulting-work.sustainability' => ['Báo cáo Tư vấn',       'Phát triển bền vững'],
            'app.reports.consulting-work.energy' => ['Báo cáo Tư vấn',       'Giảm phát thải, tiết kiệm năng lượng'],
            'app.reports.technical.consulting' => ['Báo cáo Kỹ thuật',     'Quan trắc và hồ sơ môi trường'],
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
            'HĐ Chất thải' => 'app.contracts.waste.index',
            'HĐ Quan trắc và hồ sơ môi trường' => 'app.contracts.consulting.index',
            'HĐ Ứng phó sự cố' => 'app.contracts.project.index',
            'HĐ Nghiên cứu và chuyển đổi công nghệ' => 'app.contracts.commercial.index',
            'HĐ Phát triển bền vững' => 'app.contracts.sustainability.index',
            'HĐ Giảm phát thải, tiết kiệm năng lượng' => 'app.contracts.energy.index',
        ];

        if (isset($contractRoutes[$child])) {
            return route($contractRoutes[$child]);
        }

        $specific = [
            'Nội bộ' => ['Quy định' => 'app.internal-docs.index', 'Phần mềm' => 'app.internal-software.index'],
            'Hoa hồng' => ['Yêu cầu chi hoa hồng' => 'app.commissions.index'],
            'Bộ phận kinh doanh' => [
                'Đăng ký mục tiêu doanh số' => 'app.sales.target-registration',
                'Bảng theo dõi báo giá' => 'app.quotation-tracking.index',
                'Tạo báo giá' => 'app.quotation-docs.index',
                'Tiến độ dự án' => 'app.reports.sales.project-progress',
            ],
            'Chuyển phát thư' => ['Quản lý chuyển phát' => 'app.postal-deliveries.index'],
            'Báo cáo Kinh doanh' => [
                'Bảng tổng kết doanh số' => 'app.reports.sales.summary',
                'Bảng doanh số cam kết' => 'app.reports.sales.target',
                'Bảng doanh số cá nhân' => 'app.reports.sales.personal',
            ],
            'Báo cáo Tư vấn' => [
                'Chất thải' => 'app.reports.consulting-work.waste',
                'Quan trắc và hồ sơ môi trường' => 'app.reports.consulting-work.consulting',
                'Ứng phó sự cố' => 'app.reports.consulting-work.project',
                'Nghiên cứu và chuyển đổi công nghệ' => 'app.reports.consulting-work.commercial',
                'Phát triển bền vững' => 'app.reports.consulting-work.sustainability',
                'Giảm phát thải, tiết kiệm năng lượng' => 'app.reports.consulting-work.energy',
            ],
            'Báo cáo Kỹ thuật' => ['Quan trắc và hồ sơ môi trường' => 'app.reports.technical.consulting'],
            'Bộ phận Marketing' => ['Kế hoạch content' => 'app.marketing.content.index'],
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
                'HĐ Chất thải' => 'Chất thải',
                'HĐ Quan trắc và hồ sơ môi trường' => 'Quan trắc và hồ sơ môi trường',
                'HĐ Ứng phó sự cố' => 'Ứng phó sự cố',
                'HĐ Nghiên cứu và chuyển đổi công nghệ' => 'Nghiên cứu và chuyển đổi công nghệ',
                'HĐ Phát triển bền vững' => 'Phát triển bền vững',
                'HĐ Giảm phát thải, tiết kiệm năng lượng' => 'Giảm phát thải, tiết kiệm năng lượng',
                default => $child,
            };
        }

        return $child;
    }

    // ── Child icon ────────────────────────────────────────────────────────────

    public static function childIcon(string $menuTitle, string $section, string $child = ''): string
    {
        if (in_array($menuTitle, ['Quản lý hợp đồng', 'Bộ phận tư vấn', 'Bộ phận kỹ thuật'], true)) {
            return match ($child) {
                'HĐ Chất thải' => '<i class="fa-solid fa-recycle"></i>',
                'HĐ Quan trắc và hồ sơ môi trường' => '<i class="fa-solid fa-clipboard-check"></i>',
                'HĐ Ứng phó sự cố' => '<i class="fa-solid fa-shield-halved"></i>',
                'HĐ Nghiên cứu và chuyển đổi công nghệ' => '<i class="fa-solid fa-flask-vial"></i>',
                'HĐ Phát triển bền vững' => '<i class="fa-solid fa-seedling"></i>',
                'HĐ Giảm phát thải, tiết kiệm năng lượng' => '<i class="fa-solid fa-bolt"></i>',
                default => self::icon('doc'),
            };
        }

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
            'HĐ Chất thải',
            'HĐ Quan trắc và hồ sơ môi trường',
            'HĐ Ứng phó sự cố',
            'HĐ Nghiên cứu và chuyển đổi công nghệ',
            'HĐ Phát triển bền vững',
            'HĐ Giảm phát thải, tiết kiệm năng lượng',
        ];
    }
}
