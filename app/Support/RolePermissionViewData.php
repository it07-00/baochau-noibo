<?php

namespace App\Support;

final class RolePermissionViewData
{
    private const MODULE_NAMES = [
        'users' => 'Người dùng',
        'roles' => 'Vai trò & Phân quyền',
        'departments' => 'Phòng ban',
        'settings' => 'Cài đặt hệ thống',
        'master-data' => 'Dữ liệu chuẩn',
        'handlers' => 'Nhà thầu phụ',
        'customers' => 'Khách hàng',
        'contracts-waste' => 'Hợp đồng chất thải',
        'contracts-consulting' => 'Hợp đồng tư vấn',
        'contracts-project' => 'Hợp đồng dự án',
        'contracts-commercial' => 'Hợp đồng thương mại',
        'contracts-sustainability' => 'HĐ Phát triển bền vững',
        'contracts-energy' => 'HĐ Giảm phát thải & NL',
        'invoices' => 'Hóa đơn Bảo Châu',
        'handler-invoices' => 'Hóa đơn nhà thầu phụ',
        'sales-progressive' => 'Doanh số tiến độ',
        'quotation-tracking' => 'Theo dõi báo giá',
        'quotations' => 'Báo giá',
        'commissions' => 'Yêu cầu hoa hồng',
        'advance-requests' => 'Yêu cầu ứng tiền',
        'cash-flow' => 'Dòng tiền',
        'waste-requests' => 'Yêu cầu chất thải',
        'consulting-requests' => 'Yêu cầu tư vấn',
        'project-requests' => 'Yêu cầu dự án',
        'commercial-requests' => 'Yêu cầu thương mại',
        'technical-requests' => 'Yêu cầu kỹ thuật',
        'mail-delivery' => 'Chuyển phát thư',
        'payment-schedules' => 'Lịch thanh toán HĐ',
        'rankings' => 'Bảng xếp hạng',
        'statistics' => 'Bảng thống kê',
        'reports' => 'Báo cáo tổng hợp',
        'reports-consulting' => 'Báo cáo tư vấn',
        'reports-technical' => 'Báo cáo kỹ thuật',
        'reports-sales' => 'Báo cáo kinh doanh',
        'daily-reports' => 'Báo cáo ngày',
        'sales-quotation' => 'Báo giá kinh doanh',
        'cham-cong' => 'Chấm công',
        'hr-profiles' => 'Hồ sơ nhân sự',
        'mail-delivery-admin' => 'Quản trị chuyển phát',
        'marketing-reports' => 'Báo cáo Marketing',
        'activity-log' => 'Nhật ký hoạt động',
        'internal-docs' => 'Tài liệu nội bộ',
        'articles' => 'Bài viết / Marketing',
    ];

    private const ACTION_LABELS = [
        'view' => 'Xem danh sách',
        'view-all' => 'Xem tất cả',
        'create' => 'Thêm mới',
        'edit' => 'Chỉnh sửa',
        'delete' => 'Xóa',
        'approve' => 'Phê duyệt',
        'export' => 'Xuất dữ liệu',
        'report' => 'Xem báo cáo',
    ];

    private const MODULE_ICONS = [
        'users' => 'fa-solid fa-users',
        'roles' => 'fa-solid fa-user-shield',
        'departments' => 'fa-solid fa-sitemap',
        'settings' => 'fa-solid fa-gear',
        'master-data' => 'fa-solid fa-database',
        'handlers' => 'fa-solid fa-truck-field',
        'customers' => 'fa-solid fa-building-user',
        'invoices' => 'fa-solid fa-file-invoice-dollar',
        'handler-invoices' => 'fa-solid fa-file-invoice-dollar',
        'quotations' => 'fa-solid fa-file-signature',
        'quotation-tracking' => 'fa-solid fa-list-check',
        'commissions' => 'fa-solid fa-hand-holding-dollar',
        'advance-requests' => 'fa-solid fa-wallet',
        'cash-flow' => 'fa-solid fa-money-bill-transfer',
        'cham-cong' => 'fa-solid fa-calendar-check',
        'hr-profiles' => 'fa-solid fa-id-card',
        'activity-log' => 'fa-solid fa-clock-rotate-left',
        'internal-docs' => 'fa-solid fa-book-bookmark',
        'articles' => 'fa-solid fa-newspaper',
    ];

    public static function moduleName(string $module): string
    {
        return self::MODULE_NAMES[$module] ?? strtoupper($module);
    }

    public static function moduleIcon(string $module): string
    {
        if (str_contains($module, 'contract')) {
            return 'fa-solid fa-file-contract';
        }
        if (str_contains($module, 'report')) {
            return 'fa-solid fa-chart-column';
        }
        if (str_contains($module, 'request')) {
            return 'fa-solid fa-clipboard-list';
        }

        return self::MODULE_ICONS[$module] ?? 'fa-solid fa-folder-open';
    }

    public static function actionLabel(string $permissionName): string
    {
        $parts = explode('.', $permissionName);
        $action = $parts[1] ?? $parts[0] ?? '';

        return self::ACTION_LABELS[$action] ?? ucfirst($action);
    }
}
