<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================
        // 1. Định nghĩa tất cả permissions
        // ============================
        $modules = [
            // --- Quản trị hệ thống (chỉ IT) ---
            'users'               => ['view', 'create', 'edit', 'delete'],
            'roles'               => ['view', 'create', 'edit', 'delete'],
            'departments'         => ['view', 'create', 'edit', 'delete'],
            'settings'            => ['view', 'edit'],

            // --- Dữ liệu nền ---
            'handlers'            => ['view', 'create', 'edit', 'delete'],
            'customers'           => ['view', 'create', 'edit', 'delete'],

            // --- Hợp đồng (6 loại) ---
            'contracts-waste'          => ['view', 'create', 'edit', 'delete'],
            'contracts-consulting'     => ['view', 'create', 'edit', 'delete'],
            'contracts-project'        => ['view', 'create', 'edit', 'delete'],
            'contracts-commercial'     => ['view', 'create', 'edit', 'delete'],
            'contracts-sustainability' => ['view', 'create', 'edit', 'delete'],
            'contracts-energy'         => ['view', 'create', 'edit', 'delete'],

            // --- Lịch thanh toán ---
            'payment-schedules'   => ['view', 'create', 'edit', 'delete'],

            // --- Hóa đơn ---
            'invoices'            => ['view', 'create', 'edit', 'delete'],
            'handler-invoices'    => ['view', 'create', 'edit', 'delete'],

            // --- Kinh doanh ---
            'sales-quotation'     => ['view', 'create', 'edit', 'delete'],
            'sales-renewal'       => ['view', 'create', 'edit', 'delete'],
            'sales-progressive'   => ['view', 'create', 'edit', 'delete'],
            'quotation-tracking'  => ['view', 'create', 'edit', 'delete'],

            // --- Tài chính ---
            'commissions'         => ['view', 'create', 'edit', 'delete'],
            'advance-requests'    => ['view', 'create', 'edit', 'delete'],

            // --- Vận hành ---
            'waste-requests'      => ['view', 'create', 'edit', 'delete'],
            'consulting-requests' => ['view', 'create', 'edit', 'delete'],
            'project-requests'    => ['view', 'create', 'edit', 'delete'],
            'commercial-requests' => ['view', 'create', 'edit', 'delete'],
            'technical-requests'  => ['view', 'create', 'edit', 'delete'],

            // --- Chuyển phát ---
            'mail-delivery'       => ['view', 'create', 'edit', 'delete'],

            // --- Bảng thống kê & Xếp hạng ---
            'rankings'            => ['view'],
            'statistics'          => ['view'],

            // --- Báo cáo ---
            'reports'             => ['view'],

            // --- Nội bộ & Marketing ---
            'internal-docs'       => ['view', 'create', 'edit', 'delete'],
            'articles'            => ['view', 'create', 'edit', 'delete'],

            // --- Báo cáo ngày ---
            'daily-reports'       => ['view', 'view-all', 'create', 'edit', 'delete'],

            // --- Nhật ký hoạt động (chỉ IT) ---
            'activity-log'        => ['view'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name'       => "{$module}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // ============================
        // 2. Gán permissions cho từng role
        // ============================

        // ------------------------------------------------
        // IT — Quản trị hệ thống
        // ------------------------------------------------
        Role::findOrCreate('it')->syncPermissions([
            // Quản trị hệ thống
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
            'settings.view', 'settings.edit',
            // Chuyển phát thư
            'mail-delivery.view', 'mail-delivery.create', 'mail-delivery.edit', 'mail-delivery.delete',
            // Nội bộ
            'internal-docs.view', 'internal-docs.create', 'internal-docs.edit', 'internal-docs.delete',
            // Báo cáo ngày
            'daily-reports.view', 'daily-reports.view-all', 'daily-reports.create', 'daily-reports.edit', 'daily-reports.delete',
            // Nhật ký hoạt động
            'activity-log.view',
        ]);

        // ------------------------------------------------
        // GĐ (Giám đốc) — Xem mọi thứ, không quản trị hệ thống
        // ------------------------------------------------
        Role::findOrCreate('giam-doc')->syncPermissions(
            Permission::whereNotIn('name', [
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
                'settings.view', 'settings.edit',
            ])->pluck('name')->toArray()
        );

        // ------------------------------------------------
        // TPKD (Trưởng phòng KD) — Quản lý KD đầy đủ
        // ------------------------------------------------
        Role::findOrCreate('tp-kinh-doanh')->syncPermissions([
            // Dữ liệu nền
            'customers.view', 'customers.create', 'customers.edit',
            'handlers.view', 'handlers.create', 'handlers.edit',
            // Hợp đồng: CRUD đầy đủ
            'contracts-waste.view', 'contracts-waste.create', 'contracts-waste.edit', 'contracts-waste.delete',
            'contracts-consulting.view', 'contracts-consulting.create', 'contracts-consulting.edit', 'contracts-consulting.delete',
            'contracts-project.view', 'contracts-project.create', 'contracts-project.edit', 'contracts-project.delete',
            'contracts-commercial.view', 'contracts-commercial.create', 'contracts-commercial.edit', 'contracts-commercial.delete',
            'contracts-sustainability.view', 'contracts-sustainability.create', 'contracts-sustainability.edit', 'contracts-sustainability.delete',
            'contracts-energy.view', 'contracts-energy.create', 'contracts-energy.edit', 'contracts-energy.delete',
            // Lịch thanh toán: CRUD
            'payment-schedules.view', 'payment-schedules.create', 'payment-schedules.edit', 'payment-schedules.delete',
            // Kinh doanh: CRUD đầy đủ
            'sales-quotation.view', 'sales-quotation.create', 'sales-quotation.edit', 'sales-quotation.delete',
            'sales-renewal.view', 'sales-renewal.create', 'sales-renewal.edit', 'sales-renewal.delete',
            'sales-progressive.view', 'sales-progressive.create', 'sales-progressive.edit', 'sales-progressive.delete',
            'quotation-tracking.view', 'quotation-tracking.create', 'quotation-tracking.edit', 'quotation-tracking.delete',
            // Tài chính
            'commissions.view', 'commissions.create', 'commissions.edit',
            'advance-requests.view', 'advance-requests.create',
            // Chuyển phát
            'mail-delivery.view', 'mail-delivery.create', 'mail-delivery.edit',
            // Thống kê & Báo cáo
            'rankings.view', 'statistics.view', 'reports.view',
            // Nội bộ
            'internal-docs.view',
            // Báo cáo ngày: xem tất cả + tạo/sửa
            'daily-reports.view', 'daily-reports.view-all', 'daily-reports.create', 'daily-reports.edit',
        ]);

        // ------------------------------------------------
        // KD (Nhân viên Kinh doanh)
        // ------------------------------------------------
        Role::findOrCreate('kinh-doanh')->syncPermissions([
            // Dữ liệu nền
            'customers.view', 'customers.create', 'customers.edit',
            'handlers.view',
            // Hợp đồng: xem + tạo
            'contracts-waste.view', 'contracts-waste.create', 'contracts-waste.edit',
            'contracts-consulting.view', 'contracts-consulting.create', 'contracts-consulting.edit',
            'contracts-project.view', 'contracts-project.create', 'contracts-project.edit',
            'contracts-commercial.view', 'contracts-commercial.create', 'contracts-commercial.edit',
            'contracts-sustainability.view', 'contracts-sustainability.create', 'contracts-sustainability.edit',
            'contracts-energy.view', 'contracts-energy.create', 'contracts-energy.edit',
            // Lịch thanh toán: xem + tạo/sửa
            'payment-schedules.view', 'payment-schedules.create', 'payment-schedules.edit',
            // Kinh doanh: CRUD cá nhân
            'sales-quotation.view', 'sales-quotation.create', 'sales-quotation.edit', 'sales-quotation.delete',
            'sales-renewal.view', 'sales-renewal.create', 'sales-renewal.edit', 'sales-renewal.delete',
            'sales-progressive.view', 'sales-progressive.create', 'sales-progressive.edit', 'sales-progressive.delete',
            'quotation-tracking.view', 'quotation-tracking.create', 'quotation-tracking.edit', 'quotation-tracking.delete',
            // Tài chính: tạo yêu cầu
            'commissions.view', 'commissions.create',
            'advance-requests.view', 'advance-requests.create',
            // Chuyển phát
            'mail-delivery.view', 'mail-delivery.create',
            // Thống kê & Báo cáo
            'rankings.view', 'statistics.view', 'reports.view',
            // Nội bộ
            'internal-docs.view',
            // Báo cáo ngày: chỉ xem của mình + tạo/sửa
            'daily-reports.view', 'daily-reports.create', 'daily-reports.edit',
        ]);

        // ------------------------------------------------
        // TV (Tư vấn / CSKH)
        // ------------------------------------------------
        Role::findOrCreate('tu-van')->syncPermissions([
            // Dữ liệu nền: xem khách hàng (CSKH cần)
            'customers.view',
            'handlers.view',
            // Hợp đồng: xem tất cả + sửa tư vấn
            'contracts-waste.view',
            'contracts-consulting.view', 'contracts-consulting.edit',
            'contracts-project.view',
            'contracts-commercial.view',
            'contracts-sustainability.view',
            'contracts-energy.view',
            // Vận hành tư vấn: CRUD
            'consulting-requests.view', 'consulting-requests.create', 'consulting-requests.edit', 'consulting-requests.delete',
            // Tài chính: tạo yêu cầu hoa hồng
            'commissions.view', 'commissions.create',
            // Chuyển phát
            'mail-delivery.view', 'mail-delivery.create', 'mail-delivery.edit', 'mail-delivery.delete',
            // Thống kê & Báo cáo
            'rankings.view', 'statistics.view', 'reports.view',
            // Nội bộ
            'internal-docs.view',
            // Báo cáo ngày
            'daily-reports.view', 'daily-reports.create', 'daily-reports.edit',
        ]);

        // ------------------------------------------------
        // KT (Kỹ thuật)
        // ------------------------------------------------
        Role::findOrCreate('ky-thuat')->syncPermissions([
            // Hợp đồng: chỉ xem
            'contracts-waste.view',
            'contracts-consulting.view',
            'contracts-project.view',
            'contracts-commercial.view',
            'contracts-sustainability.view',
            'contracts-energy.view',
            // Vận hành kỹ thuật: CRUD
            'waste-requests.view', 'waste-requests.create', 'waste-requests.edit',
            'technical-requests.view', 'technical-requests.create', 'technical-requests.edit', 'technical-requests.delete',
            // Báo cáo
            'reports.view',
            'rankings.view', 'statistics.view',
            // Nội bộ
            'internal-docs.view',
            // Báo cáo ngày
            'daily-reports.view', 'daily-reports.create', 'daily-reports.edit',
        ]);

        // ------------------------------------------------
        // MKT (Marketing)
        // ------------------------------------------------
        Role::findOrCreate('marketing')->syncPermissions([
            // Bài viết / nội dung: CRUD
            'articles.view', 'articles.create', 'articles.edit', 'articles.delete',
            // Thống kê & Báo cáo
            'rankings.view', 'statistics.view', 'reports.view',
            // Nội bộ
            'internal-docs.view',
            // Báo cáo ngày
            'daily-reports.view', 'daily-reports.create', 'daily-reports.edit',
        ]);

        // ------------------------------------------------
        // KeToan (Kế toán)
        // ------------------------------------------------
        Role::findOrCreate('ke-toan')->syncPermissions([
            // Dữ liệu nền: xem để tra cứu hóa đơn
            'customers.view',
            'handlers.view',
            // Hợp đồng: chỉ xem
            'contracts-waste.view',
            'contracts-consulting.view',
            'contracts-project.view',
            'contracts-commercial.view',
            'contracts-sustainability.view',
            'contracts-energy.view',
            // Lịch thanh toán: CRUD
            'payment-schedules.view', 'payment-schedules.create', 'payment-schedules.edit', 'payment-schedules.delete',
            // Hóa đơn: CRUD đầy đủ
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete',
            'handler-invoices.view', 'handler-invoices.create', 'handler-invoices.edit', 'handler-invoices.delete',
            // Tài chính: CRUD (duyệt, xử lý)
            'commissions.view', 'commissions.create', 'commissions.edit', 'commissions.delete',
            'advance-requests.view', 'advance-requests.create', 'advance-requests.edit', 'advance-requests.delete',
            // Thống kê & Báo cáo
            'rankings.view', 'statistics.view', 'reports.view',
            // Nội bộ
            'internal-docs.view',
            // Báo cáo ngày
            'daily-reports.view', 'daily-reports.create', 'daily-reports.edit',
        ]);

        // ------------------------------------------------
        // Backward compat: quan-ly (role cũ) = giống giam-doc
        // ------------------------------------------------
        if ($quanLy = Role::where('name', 'quan-ly')->first()) {
            $quanLy->syncPermissions(
                Role::findByName('giam-doc')->permissions->pluck('name')->toArray()
            );
        }
    }
}
