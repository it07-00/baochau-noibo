<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
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
        foreach (PermissionEnum::cases() as $perm) {
            Permission::firstOrCreate([
                'name'       => $perm->value,
                'guard_name' => 'web',
            ]);
        }

        // ============================
        // 2. Gán permissions cho từng role
        // ============================

        // ------------------------------------------------
        // IT — Quản trị hệ thống
        // ------------------------------------------------
        Role::findOrCreate(RoleEnum::IT->value)->syncPermissions([
            // Quản trị hệ thống
            PermissionEnum::USERS_VIEW->value, PermissionEnum::USERS_CREATE->value, PermissionEnum::USERS_EDIT->value, PermissionEnum::USERS_DELETE->value,
            PermissionEnum::ROLES_VIEW->value, PermissionEnum::ROLES_CREATE->value, PermissionEnum::ROLES_EDIT->value, PermissionEnum::ROLES_DELETE->value,
            PermissionEnum::DEPARTMENTS_VIEW->value, PermissionEnum::DEPARTMENTS_CREATE->value, PermissionEnum::DEPARTMENTS_EDIT->value, PermissionEnum::DEPARTMENTS_DELETE->value,
            PermissionEnum::SETTINGS_VIEW->value, PermissionEnum::SETTINGS_EDIT->value,
            // Chuyển phát thư (quản lý tập trung)
            PermissionEnum::MAIL_DELIVERY_VIEW->value, PermissionEnum::MAIL_DELIVERY_CREATE->value, PermissionEnum::MAIL_DELIVERY_EDIT->value, PermissionEnum::MAIL_DELIVERY_DELETE->value,
            PermissionEnum::MAIL_DELIVERY_ADMIN_VIEW->value,
            // Nội bộ
            PermissionEnum::INTERNAL_DOCS_VIEW->value, PermissionEnum::INTERNAL_DOCS_CREATE->value, PermissionEnum::INTERNAL_DOCS_EDIT->value, PermissionEnum::INTERNAL_DOCS_DELETE->value,
            // Báo cáo ngày
            PermissionEnum::DAILY_REPORTS_VIEW->value, PermissionEnum::DAILY_REPORTS_VIEW_ALL->value, PermissionEnum::DAILY_REPORTS_CREATE->value, PermissionEnum::DAILY_REPORTS_EDIT->value, PermissionEnum::DAILY_REPORTS_DELETE->value,
            // Nhật ký hoạt động
            PermissionEnum::ACTIVITY_LOG_VIEW->value,
            // Chấm công
            PermissionEnum::CHAM_CONG_VIEW->value, PermissionEnum::CHAM_CONG_EDIT->value, PermissionEnum::CHAM_CONG_EXPORT->value,
        ]);

        // ------------------------------------------------
        // GĐ (Giám đốc) — Xem mọi thứ, không quản trị hệ thống
        // ------------------------------------------------
        Role::findOrCreate(RoleEnum::GIAM_DOC->value)->syncPermissions(
            Permission::whereNotIn('name', [
                PermissionEnum::USERS_VIEW->value, PermissionEnum::USERS_CREATE->value, PermissionEnum::USERS_EDIT->value, PermissionEnum::USERS_DELETE->value,
                PermissionEnum::ROLES_VIEW->value, PermissionEnum::ROLES_CREATE->value, PermissionEnum::ROLES_EDIT->value, PermissionEnum::ROLES_DELETE->value,
                PermissionEnum::SETTINGS_VIEW->value, PermissionEnum::SETTINGS_EDIT->value,
                // GĐ không truy cập bộ phận kinh doanh/tư vấn và postal admin
                PermissionEnum::CONSULTING_REQUESTS_VIEW->value,
                PermissionEnum::TECHNICAL_REQUESTS_VIEW->value,
                PermissionEnum::MARKETING_REPORTS_VIEW->value,
                PermissionEnum::MAIL_DELIVERY_ADMIN_VIEW->value,
                // Phòng ban
                PermissionEnum::DEPARTMENTS_VIEW->value, PermissionEnum::DEPARTMENTS_CREATE->value, PermissionEnum::DEPARTMENTS_EDIT->value, PermissionEnum::DEPARTMENTS_DELETE->value,
                // Hợp đồng
                PermissionEnum::CONTRACTS_WASTE_VIEW->value, PermissionEnum::CONTRACTS_WASTE_CREATE->value, PermissionEnum::CONTRACTS_WASTE_EDIT->value, PermissionEnum::CONTRACTS_WASTE_DELETE->value,
                PermissionEnum::CONTRACTS_CONSULTING_VIEW->value, PermissionEnum::CONTRACTS_CONSULTING_CREATE->value, PermissionEnum::CONTRACTS_CONSULTING_EDIT->value, PermissionEnum::CONTRACTS_CONSULTING_DELETE->value,
                PermissionEnum::CONTRACTS_PROJECT_VIEW->value, PermissionEnum::CONTRACTS_PROJECT_CREATE->value, PermissionEnum::CONTRACTS_PROJECT_EDIT->value, PermissionEnum::CONTRACTS_PROJECT_DELETE->value,
                PermissionEnum::CONTRACTS_COMMERCIAL_VIEW->value, PermissionEnum::CONTRACTS_COMMERCIAL_CREATE->value, PermissionEnum::CONTRACTS_COMMERCIAL_EDIT->value, PermissionEnum::CONTRACTS_COMMERCIAL_DELETE->value,
                PermissionEnum::CONTRACTS_SUSTAINABILITY_VIEW->value, PermissionEnum::CONTRACTS_SUSTAINABILITY_CREATE->value, PermissionEnum::CONTRACTS_SUSTAINABILITY_EDIT->value, PermissionEnum::CONTRACTS_SUSTAINABILITY_DELETE->value,
                PermissionEnum::CONTRACTS_ENERGY_VIEW->value, PermissionEnum::CONTRACTS_ENERGY_CREATE->value, PermissionEnum::CONTRACTS_ENERGY_EDIT->value, PermissionEnum::CONTRACTS_ENERGY_DELETE->value,
                // Lịch thanh toán
                PermissionEnum::PAYMENT_SCHEDULES_VIEW->value, PermissionEnum::PAYMENT_SCHEDULES_CREATE->value, PermissionEnum::PAYMENT_SCHEDULES_EDIT->value, PermissionEnum::PAYMENT_SCHEDULES_DELETE->value,
                // Kinh doanh
                PermissionEnum::SALES_PROGRESSIVE_VIEW->value, PermissionEnum::SALES_PROGRESSIVE_CREATE->value, PermissionEnum::SALES_PROGRESSIVE_EDIT->value, PermissionEnum::SALES_PROGRESSIVE_DELETE->value,
                PermissionEnum::QUOTATION_TRACKING_CREATE->value, PermissionEnum::QUOTATION_TRACKING_EDIT->value, PermissionEnum::QUOTATION_TRACKING_DELETE->value,
            ])->pluck('name')->toArray()
        );

        // ------------------------------------------------
        // TPKD (Trưởng phòng KD) — Quản lý KD đầy đủ
        // ------------------------------------------------
        Role::findOrCreate(RoleEnum::TP_KINH_DOANH->value)->syncPermissions([
            // Dữ liệu nền
            PermissionEnum::CUSTOMERS_VIEW->value, PermissionEnum::CUSTOMERS_CREATE->value, PermissionEnum::CUSTOMERS_EDIT->value,
            PermissionEnum::HANDLERS_VIEW->value, PermissionEnum::HANDLERS_CREATE->value, PermissionEnum::HANDLERS_EDIT->value,
            // Hợp đồng: CRUD đầy đủ
            PermissionEnum::CONTRACTS_WASTE_VIEW->value, PermissionEnum::CONTRACTS_WASTE_CREATE->value, PermissionEnum::CONTRACTS_WASTE_EDIT->value, PermissionEnum::CONTRACTS_WASTE_DELETE->value,
            PermissionEnum::CONTRACTS_CONSULTING_VIEW->value, PermissionEnum::CONTRACTS_CONSULTING_CREATE->value, PermissionEnum::CONTRACTS_CONSULTING_EDIT->value, PermissionEnum::CONTRACTS_CONSULTING_DELETE->value,
            PermissionEnum::CONTRACTS_PROJECT_VIEW->value, PermissionEnum::CONTRACTS_PROJECT_CREATE->value, PermissionEnum::CONTRACTS_PROJECT_EDIT->value, PermissionEnum::CONTRACTS_PROJECT_DELETE->value,
            PermissionEnum::CONTRACTS_COMMERCIAL_VIEW->value, PermissionEnum::CONTRACTS_COMMERCIAL_CREATE->value, PermissionEnum::CONTRACTS_COMMERCIAL_EDIT->value, PermissionEnum::CONTRACTS_COMMERCIAL_DELETE->value,
            PermissionEnum::CONTRACTS_SUSTAINABILITY_VIEW->value, PermissionEnum::CONTRACTS_SUSTAINABILITY_CREATE->value, PermissionEnum::CONTRACTS_SUSTAINABILITY_EDIT->value, PermissionEnum::CONTRACTS_SUSTAINABILITY_DELETE->value,
            PermissionEnum::CONTRACTS_ENERGY_VIEW->value, PermissionEnum::CONTRACTS_ENERGY_CREATE->value, PermissionEnum::CONTRACTS_ENERGY_EDIT->value, PermissionEnum::CONTRACTS_ENERGY_DELETE->value,
            // Lịch thanh toán: CRUD
            PermissionEnum::PAYMENT_SCHEDULES_VIEW->value, PermissionEnum::PAYMENT_SCHEDULES_CREATE->value, PermissionEnum::PAYMENT_SCHEDULES_EDIT->value, PermissionEnum::PAYMENT_SCHEDULES_DELETE->value,
            // Kinh doanh: CRUD đầy đủ
            PermissionEnum::SALES_PROGRESSIVE_VIEW->value, PermissionEnum::SALES_PROGRESSIVE_CREATE->value, PermissionEnum::SALES_PROGRESSIVE_EDIT->value, PermissionEnum::SALES_PROGRESSIVE_DELETE->value,
            PermissionEnum::QUOTATION_TRACKING_VIEW->value, PermissionEnum::QUOTATION_TRACKING_CREATE->value, PermissionEnum::QUOTATION_TRACKING_EDIT->value, PermissionEnum::QUOTATION_TRACKING_DELETE->value,
            // Tài chính
            PermissionEnum::COMMISSIONS_VIEW->value, PermissionEnum::COMMISSIONS_CREATE->value, PermissionEnum::COMMISSIONS_EDIT->value,
            PermissionEnum::ADVANCE_REQUESTS_VIEW->value, PermissionEnum::ADVANCE_REQUESTS_CREATE->value,
            PermissionEnum::CASH_FLOW_VIEW->value,
            // Chuyển phát
            PermissionEnum::MAIL_DELIVERY_VIEW->value, PermissionEnum::MAIL_DELIVERY_CREATE->value, PermissionEnum::MAIL_DELIVERY_EDIT->value,
            // Thống kê & Báo cáo
            PermissionEnum::RANKINGS_VIEW->value, PermissionEnum::STATISTICS_VIEW->value, PermissionEnum::REPORTS_VIEW->value, PermissionEnum::REPORTS_SALES_VIEW->value,
            // Nội bộ
            PermissionEnum::INTERNAL_DOCS_VIEW->value,
            // Báo cáo ngày: xem tất cả + tạo/sửa
            PermissionEnum::DAILY_REPORTS_VIEW->value, PermissionEnum::DAILY_REPORTS_VIEW_ALL->value, PermissionEnum::DAILY_REPORTS_CREATE->value, PermissionEnum::DAILY_REPORTS_EDIT->value,
            // Báo cáo Marketing hàng ngày: chỉ xem
            PermissionEnum::MARKETING_REPORTS_VIEW->value, PermissionEnum::MARKETING_REPORTS_VIEW_ALL->value,
        ]);

        // ------------------------------------------------
        // KD (Nhân viên Kinh doanh)
        // ------------------------------------------------
        Role::findOrCreate(RoleEnum::KINH_DOANH->value)->syncPermissions([
            // Dữ liệu nền
            PermissionEnum::CUSTOMERS_VIEW->value, PermissionEnum::CUSTOMERS_CREATE->value, PermissionEnum::CUSTOMERS_EDIT->value,
            PermissionEnum::HANDLERS_VIEW->value,
            // Hợp đồng: xem + tạo
            PermissionEnum::CONTRACTS_WASTE_VIEW->value, PermissionEnum::CONTRACTS_WASTE_CREATE->value, PermissionEnum::CONTRACTS_WASTE_EDIT->value,
            PermissionEnum::CONTRACTS_CONSULTING_VIEW->value, PermissionEnum::CONTRACTS_CONSULTING_CREATE->value, PermissionEnum::CONTRACTS_CONSULTING_EDIT->value,
            PermissionEnum::CONTRACTS_PROJECT_VIEW->value, PermissionEnum::CONTRACTS_PROJECT_CREATE->value, PermissionEnum::CONTRACTS_PROJECT_EDIT->value,
            PermissionEnum::CONTRACTS_COMMERCIAL_VIEW->value, PermissionEnum::CONTRACTS_COMMERCIAL_CREATE->value, PermissionEnum::CONTRACTS_COMMERCIAL_EDIT->value,
            PermissionEnum::CONTRACTS_SUSTAINABILITY_VIEW->value, PermissionEnum::CONTRACTS_SUSTAINABILITY_CREATE->value, PermissionEnum::CONTRACTS_SUSTAINABILITY_EDIT->value,
            PermissionEnum::CONTRACTS_ENERGY_VIEW->value, PermissionEnum::CONTRACTS_ENERGY_CREATE->value, PermissionEnum::CONTRACTS_ENERGY_EDIT->value,
            // Lịch thanh toán: xem + tạo/sửa
            PermissionEnum::PAYMENT_SCHEDULES_VIEW->value, PermissionEnum::PAYMENT_SCHEDULES_CREATE->value, PermissionEnum::PAYMENT_SCHEDULES_EDIT->value,
            // Kinh doanh: CRUD cá nhân
            PermissionEnum::SALES_PROGRESSIVE_VIEW->value, PermissionEnum::SALES_PROGRESSIVE_CREATE->value, PermissionEnum::SALES_PROGRESSIVE_EDIT->value, PermissionEnum::SALES_PROGRESSIVE_DELETE->value,
            PermissionEnum::QUOTATION_TRACKING_VIEW->value, PermissionEnum::QUOTATION_TRACKING_CREATE->value, PermissionEnum::QUOTATION_TRACKING_EDIT->value, PermissionEnum::QUOTATION_TRACKING_DELETE->value,
            // Tài chính: tạo yêu cầu
            PermissionEnum::COMMISSIONS_VIEW->value, PermissionEnum::COMMISSIONS_CREATE->value,
            PermissionEnum::ADVANCE_REQUESTS_VIEW->value, PermissionEnum::ADVANCE_REQUESTS_CREATE->value,
            // Chuyển phát
            PermissionEnum::MAIL_DELIVERY_VIEW->value, PermissionEnum::MAIL_DELIVERY_CREATE->value,
            // Thống kê & Báo cáo
            PermissionEnum::RANKINGS_VIEW->value, PermissionEnum::STATISTICS_VIEW->value, PermissionEnum::REPORTS_VIEW->value, PermissionEnum::REPORTS_SALES_VIEW->value,
            // Nội bộ
            PermissionEnum::INTERNAL_DOCS_VIEW->value,
            // Báo cáo ngày: chỉ xem của mình + tạo/sửa
            PermissionEnum::DAILY_REPORTS_VIEW->value, PermissionEnum::DAILY_REPORTS_CREATE->value, PermissionEnum::DAILY_REPORTS_EDIT->value,
            // Báo cáo Marketing hàng ngày: chỉ xem
            PermissionEnum::MARKETING_REPORTS_VIEW->value,
        ]);

        // ------------------------------------------------
        // TV (Tư vấn / CSKH)
        // ------------------------------------------------
        Role::findOrCreate(RoleEnum::TU_VAN->value)->syncPermissions([
            // Dữ liệu nền: xem khách hàng (CSKH cần)
            PermissionEnum::CUSTOMERS_VIEW->value,
            PermissionEnum::HANDLERS_VIEW->value,
            // Hợp đồng: xem tất cả
            PermissionEnum::CONTRACTS_WASTE_VIEW->value,
            PermissionEnum::CONTRACTS_CONSULTING_VIEW->value,
            PermissionEnum::CONTRACTS_PROJECT_VIEW->value,
            PermissionEnum::CONTRACTS_COMMERCIAL_VIEW->value,
            PermissionEnum::CONTRACTS_SUSTAINABILITY_VIEW->value,
            PermissionEnum::CONTRACTS_ENERGY_VIEW->value,
            // Vận hành tư vấn: CRUD
            PermissionEnum::CONSULTING_REQUESTS_VIEW->value, PermissionEnum::CONSULTING_REQUESTS_CREATE->value, PermissionEnum::CONSULTING_REQUESTS_EDIT->value, PermissionEnum::CONSULTING_REQUESTS_DELETE->value,
            // Tài chính: tạo yêu cầu hoa hồng
            PermissionEnum::COMMISSIONS_VIEW->value, PermissionEnum::COMMISSIONS_CREATE->value,
            // Chuyển phát
            PermissionEnum::MAIL_DELIVERY_VIEW->value, PermissionEnum::MAIL_DELIVERY_CREATE->value, PermissionEnum::MAIL_DELIVERY_EDIT->value, PermissionEnum::MAIL_DELIVERY_DELETE->value,
            // Thống kê & Báo cáo
            PermissionEnum::RANKINGS_VIEW->value, PermissionEnum::STATISTICS_VIEW->value, PermissionEnum::REPORTS_VIEW->value, PermissionEnum::REPORTS_CONSULTING_VIEW->value,
            // Nội bộ
            PermissionEnum::INTERNAL_DOCS_VIEW->value,
            // Báo cáo ngày
            PermissionEnum::DAILY_REPORTS_VIEW->value, PermissionEnum::DAILY_REPORTS_CREATE->value, PermissionEnum::DAILY_REPORTS_EDIT->value,
        ]);

        // ------------------------------------------------
        // KT (Kỹ thuật)
        // ------------------------------------------------
        Role::findOrCreate(RoleEnum::KY_THUAT->value)->syncPermissions([
            // Hợp đồng: chỉ xem HĐ Pháp lý & Hồ sơ MT
            PermissionEnum::CONTRACTS_CONSULTING_VIEW->value,
            // Vận hành kỹ thuật: CRUD
            PermissionEnum::WASTE_REQUESTS_VIEW->value, PermissionEnum::WASTE_REQUESTS_CREATE->value, PermissionEnum::WASTE_REQUESTS_EDIT->value,
            PermissionEnum::TECHNICAL_REQUESTS_VIEW->value, PermissionEnum::TECHNICAL_REQUESTS_CREATE->value, PermissionEnum::TECHNICAL_REQUESTS_EDIT->value, PermissionEnum::TECHNICAL_REQUESTS_DELETE->value,
            // Báo cáo
            PermissionEnum::REPORTS_VIEW->value, PermissionEnum::REPORTS_TECHNICAL_VIEW->value,
            PermissionEnum::RANKINGS_VIEW->value, PermissionEnum::STATISTICS_VIEW->value,
            // Nội bộ
            PermissionEnum::INTERNAL_DOCS_VIEW->value,
            // Báo cáo ngày
            PermissionEnum::DAILY_REPORTS_VIEW->value, PermissionEnum::DAILY_REPORTS_CREATE->value, PermissionEnum::DAILY_REPORTS_EDIT->value,
        ]);

        // ------------------------------------------------
        // MKT (Marketing)
        // ------------------------------------------------
        Role::findOrCreate(RoleEnum::MARKETING->value)->syncPermissions([
            // Bài viết / nội dung: CRUD
            PermissionEnum::ARTICLES_VIEW->value, PermissionEnum::ARTICLES_CREATE->value, PermissionEnum::ARTICLES_EDIT->value, PermissionEnum::ARTICLES_DELETE->value,
            // Thống kê & Báo cáo
            PermissionEnum::RANKINGS_VIEW->value, PermissionEnum::STATISTICS_VIEW->value, PermissionEnum::REPORTS_VIEW->value,
            // Nội bộ
            PermissionEnum::INTERNAL_DOCS_VIEW->value,
            // Báo cáo ngày
            PermissionEnum::DAILY_REPORTS_VIEW->value, PermissionEnum::DAILY_REPORTS_CREATE->value, PermissionEnum::DAILY_REPORTS_EDIT->value,
            // Báo cáo Marketing hàng ngày
            PermissionEnum::MARKETING_REPORTS_VIEW->value, PermissionEnum::MARKETING_REPORTS_CREATE->value, PermissionEnum::MARKETING_REPORTS_EDIT->value,
        ]);

        // ------------------------------------------------
        // KeToan (Kế toán)
        // ------------------------------------------------
        Role::findOrCreate(RoleEnum::KE_TOAN->value)->syncPermissions([
            // Dữ liệu nền: xem để tra cứu hóa đơn
            PermissionEnum::CUSTOMERS_VIEW->value,
            PermissionEnum::HANDLERS_VIEW->value,
            // Hợp đồng: xem + sửa (để nhập chi NCC)
            PermissionEnum::CONTRACTS_WASTE_VIEW->value,
            PermissionEnum::CONTRACTS_WASTE_EDIT->value,
            PermissionEnum::CONTRACTS_CONSULTING_VIEW->value,
            PermissionEnum::CONTRACTS_CONSULTING_EDIT->value,
            PermissionEnum::CONTRACTS_PROJECT_VIEW->value,
            PermissionEnum::CONTRACTS_PROJECT_EDIT->value,
            PermissionEnum::CONTRACTS_COMMERCIAL_VIEW->value,
            PermissionEnum::CONTRACTS_COMMERCIAL_EDIT->value,
            PermissionEnum::CONTRACTS_SUSTAINABILITY_VIEW->value,
            PermissionEnum::CONTRACTS_SUSTAINABILITY_EDIT->value,
            PermissionEnum::CONTRACTS_ENERGY_VIEW->value,
            PermissionEnum::CONTRACTS_ENERGY_EDIT->value,
            // Lịch thanh toán: CRUD
            PermissionEnum::PAYMENT_SCHEDULES_VIEW->value, PermissionEnum::PAYMENT_SCHEDULES_CREATE->value, PermissionEnum::PAYMENT_SCHEDULES_EDIT->value, PermissionEnum::PAYMENT_SCHEDULES_DELETE->value,
            // Tài chính: CRUD (duyệt, xử lý)
            PermissionEnum::COMMISSIONS_VIEW->value, PermissionEnum::COMMISSIONS_CREATE->value, PermissionEnum::COMMISSIONS_EDIT->value, PermissionEnum::COMMISSIONS_DELETE->value,
            PermissionEnum::ADVANCE_REQUESTS_VIEW->value, PermissionEnum::ADVANCE_REQUESTS_CREATE->value, PermissionEnum::ADVANCE_REQUESTS_EDIT->value, PermissionEnum::ADVANCE_REQUESTS_DELETE->value,
            // Dòng tiền
            PermissionEnum::CASH_FLOW_VIEW->value, PermissionEnum::CASH_FLOW_EXPORT->value,
            // Thống kê & Báo cáo
            PermissionEnum::RANKINGS_VIEW->value, PermissionEnum::STATISTICS_VIEW->value, PermissionEnum::REPORTS_VIEW->value,
            // Nội bộ
            PermissionEnum::INTERNAL_DOCS_VIEW->value,
            // Báo cáo ngày
            PermissionEnum::DAILY_REPORTS_VIEW->value, PermissionEnum::DAILY_REPORTS_CREATE->value, PermissionEnum::DAILY_REPORTS_EDIT->value,
        ]);
    }
}
