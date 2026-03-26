<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================
        // 1. Tạo tất cả Permissions
        // ============================
        $modules = [
            // Quản trị hệ thống
            'users'               => ['view', 'create', 'edit', 'delete'],
            'roles'               => ['view', 'create', 'edit', 'delete'],
            'customers'           => ['view', 'create', 'edit', 'delete'],
            'departments'         => ['view', 'create', 'edit', 'delete'],
            'handlers'            => ['view', 'create', 'edit', 'delete'],
            'master-data'         => ['view', 'create', 'edit', 'delete'],
            'settings'            => ['view', 'edit'],

            // Hợp đồng
            'contracts-waste'         => ['view', 'create', 'edit', 'delete'],
            'contracts-consulting'    => ['view', 'create', 'edit', 'delete'],
            'contracts-project'       => ['view', 'create', 'edit', 'delete'],
            'contracts-commercial'    => ['view', 'create', 'edit', 'delete'],
            'contracts-sustainability'=> ['view', 'create', 'edit', 'delete'],
            'contracts-energy'        => ['view', 'create', 'edit', 'delete'],

            // Hóa đơn
            'invoices'            => ['view', 'create', 'edit', 'delete'],
            'handler-invoices'    => ['view', 'create', 'edit', 'delete'],

            // Kinh doanh
            'sales-quotation'     => ['view', 'create', 'edit', 'delete'],
            'quotation-tracking'  => ['view', 'create', 'edit', 'delete'],
            'sales-renewal'       => ['view', 'create', 'edit', 'delete'],
            'sales-progressive'   => ['view', 'create', 'edit', 'delete'],

            // Tài chính & Báo cáo
            'commissions'         => ['view', 'create', 'edit', 'delete'],
            'advance-requests'    => ['view', 'create', 'edit', 'delete'],
            'daily-reports'       => ['view', 'view-all', 'create', 'edit', 'delete'],

            // Vận hành
            'waste-requests'      => ['view', 'create', 'edit', 'delete'],
            'consulting-requests' => ['view', 'create', 'edit', 'delete'],
            'project-requests'    => ['view', 'create', 'edit', 'delete'],
            'commercial-requests' => ['view', 'create', 'edit', 'delete'],
            'technical-requests'  => ['view', 'create', 'edit', 'delete'],

            // Hỗ trợ
            'mail-delivery'       => ['view', 'create', 'edit', 'delete'],

            // Thống kê & Báo cáo
            'rankings'            => ['view'],
            'statistics'          => ['view'],
            'reports'             => ['view'],

            // Nội bộ & Bài viết
            'internal-docs'       => ['view', 'create', 'edit', 'delete'],
            'articles'            => ['view', 'create', 'edit', 'delete'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // ============================
        // 2. Gán Permissions cho Roles
        // ============================

        // --- IT (Super Admin) → Toàn quyền ---
        $it = Role::findByName('it');
        $it->givePermissionTo(Permission::all());

        // --- Quản lý ---
        $quanLy = Role::findByName('quan-ly');
        $quanLy->givePermissionTo([
            'customers.view', 'customers.create',
            // HĐ: CRUD
            'contracts-waste.view', 'contracts-waste.create', 'contracts-waste.edit', 'contracts-waste.delete',
            'contracts-consulting.view', 'contracts-consulting.create', 'contracts-consulting.edit', 'contracts-consulting.delete',
            'contracts-project.view', 'contracts-project.create', 'contracts-project.edit', 'contracts-project.delete',
            'contracts-commercial.view', 'contracts-commercial.create', 'contracts-commercial.edit', 'contracts-commercial.delete',
            'contracts-sustainability.view', 'contracts-sustainability.create', 'contracts-sustainability.edit', 'contracts-sustainability.delete',
            'contracts-energy.view', 'contracts-energy.create', 'contracts-energy.edit', 'contracts-energy.delete',
            // Hóa đơn: xem
            'invoices.view', 'handler-invoices.view',
            // Kinh doanh: CRUD
            'sales-quotation.view', 'sales-quotation.create', 'sales-quotation.edit', 'sales-quotation.delete',
            'quotation-tracking.view', 'quotation-tracking.create', 'quotation-tracking.edit', 'quotation-tracking.delete',
            'sales-renewal.view', 'sales-renewal.create', 'sales-renewal.edit', 'sales-renewal.delete',
            'sales-progressive.view',
            // Tài chính: xem
            'commissions.view', 'advance-requests.view',
            // Vận hành: xem
            'waste-requests.view', 'consulting-requests.view', 'project-requests.view', 'commercial-requests.view',
            // Hỗ trợ: xem
            'mail-delivery.view',
            // Thống kê
            'rankings.view', 'statistics.view', 'reports.view',
            // Nội bộ & Bài viết
            'internal-docs.view', 'internal-docs.create', 'internal-docs.edit', 'internal-docs.delete',
            'articles.view', 'articles.create',
            // Báo cáo ngày & Hoa hồng
            'daily-reports.view', 'daily-reports.view-all', 'daily-reports.create', 'daily-reports.edit', 'daily-reports.delete',
            'commissions.view', 'commissions.create', 'commissions.edit', 'commissions.delete',
        ]);

        // --- Kinh doanh (gộp Marketing) ---
        $kinhDoanh = Role::findByName('kinh-doanh');
        $kinhDoanh->givePermissionTo([
            // HĐ: tạo + xem
            'contracts-waste.view', 'contracts-waste.create',
            'contracts-consulting.view', 'contracts-consulting.create',
            'contracts-project.view', 'contracts-project.create',
            'contracts-commercial.view', 'contracts-commercial.create',
            'contracts-sustainability.view', 'contracts-sustainability.create',
            'contracts-energy.view', 'contracts-energy.create',
            // Kinh doanh: CRUD
            'sales-quotation.view', 'sales-quotation.create', 'sales-quotation.edit', 'sales-quotation.delete',
            'quotation-tracking.view', 'quotation-tracking.create', 'quotation-tracking.edit', 'quotation-tracking.delete',
            'sales-renewal.view', 'sales-renewal.create', 'sales-renewal.edit', 'sales-renewal.delete',
            'sales-progressive.view', 'sales-progressive.create', 'sales-progressive.edit', 'sales-progressive.delete',
            // Tài chính: tạo YC
            'commissions.view', 'commissions.create',
            'advance-requests.view', 'advance-requests.create',
            // Hỗ trợ
            'mail-delivery.view', 'mail-delivery.create',
            // Thống kê
            'rankings.view', 'statistics.view', 'reports.view',
            // Nội bộ & Bài viết (Marketing)
            'internal-docs.view',
            'articles.view', 'articles.create', 'articles.edit', 'articles.delete',
            // Báo cáo ngày & Hoa hồng
            'daily-reports.view', 'daily-reports.create', 'daily-reports.edit',
            'commissions.view', 'commissions.create',
        ]);

        // --- Kế toán ---
        $keToan = Role::findByName('ke-toan');
        $keToan->givePermissionTo([
            // HĐ: xem
            'contracts-waste.view', 'contracts-consulting.view',
            'contracts-project.view', 'contracts-commercial.view',
            'contracts-sustainability.view', 'contracts-energy.view',
            // Hóa đơn: CRUD
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete',
            'handler-invoices.view', 'handler-invoices.create', 'handler-invoices.edit', 'handler-invoices.delete',
            // Tài chính: CRUD (duyệt)
            'commissions.view', 'commissions.create', 'commissions.edit', 'commissions.delete',
            'advance-requests.view', 'advance-requests.create', 'advance-requests.edit', 'advance-requests.delete',
            // Thống kê
            'rankings.view', 'statistics.view', 'reports.view',
            // Nội bộ
            'internal-docs.view',
            // Báo cáo ngày & Hoa hồng
            'daily-reports.view', 'daily-reports.create',
            'commissions.view', 'commissions.create', 'commissions.edit', 'commissions.delete',
        ]);

        // --- Tư vấn - CSKH (gộp) ---
        $tuVan = Role::findByName('tu-van');
        $tuVan->givePermissionTo([
            // HĐ: xem
            'contracts-waste.view', 'contracts-consulting.view',
            'contracts-project.view', 'contracts-commercial.view',
            'contracts-sustainability.view', 'contracts-energy.view',
            // Hóa đơn: xem
            'invoices.view',
            // Kinh doanh
            'sales-quotation.view', 'sales-quotation.create', 'sales-quotation.edit', 'sales-quotation.delete',
            'quotation-tracking.view', 'quotation-tracking.create', 'quotation-tracking.edit', 'quotation-tracking.delete',
            'sales-renewal.view',
            'sales-progressive.view',
            // Tài chính: tạo YC
            'commissions.view', 'commissions.create',
            'advance-requests.view', 'advance-requests.create',
            // Vận hành: tư vấn CRUD
            'consulting-requests.view', 'consulting-requests.create', 'consulting-requests.edit', 'consulting-requests.delete',
            // Hỗ trợ: CRUD
            'mail-delivery.view', 'mail-delivery.create', 'mail-delivery.edit', 'mail-delivery.delete',
            // Thống kê
            'rankings.view', 'statistics.view', 'reports.view',
            // Nội bộ
            'internal-docs.view',
            // Báo cáo ngày
            'daily-reports.view', 'daily-reports.create',
        ]);
        
        // --- Kỹ thuật ---
        if ($kyThuat = Role::where('name', 'ky-thuat')->first()) {
            $kyThuat->givePermissionTo([
                'contracts-waste.view', 'contracts-consulting.view',
                'contracts-project.view', 'contracts-commercial.view',
                'contracts-sustainability.view', 'contracts-energy.view',
                'waste-requests.view', 'waste-requests.create', 'waste-requests.edit',
                'technical-requests.view', 'technical-requests.create', 'technical-requests.edit', 'technical-requests.delete',
                'internal-docs.view',
                // Báo cáo ngày
                'daily-reports.view', 'daily-reports.create',
                // Báo cáo kỹ thuật
                'reports.view',
            ]);
        }
    }
}
