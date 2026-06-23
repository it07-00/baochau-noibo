<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => RoleEnum::IT->value,
                'display_name' => 'IT / Quản trị',
                'description' => 'Quản trị hệ thống, quản lý quyền/vai trò/user',
            ],
            [
                'name' => RoleEnum::GIAM_DOC->value,
                'display_name' => 'Giám đốc',
                'description' => 'Xem tất cả, trừ quản trị hệ thống',
            ],
            [
                'name' => RoleEnum::TP_KINH_DOANH->value,
                'display_name' => 'Trưởng phòng KD',
                'description' => 'Quản lý kinh doanh, KH, CXL, hợp đồng, chuyển phát, báo cáo KD',
            ],
            [
                'name' => RoleEnum::KINH_DOANH->value,
                'display_name' => 'Nhân viên KD',
                'description' => 'Kinh doanh, báo giá, doanh số',
            ],
            [
                'name' => RoleEnum::TU_VAN->value,
                'display_name' => 'Tư vấn',
                'description' => 'Tư vấn môi trường, chăm sóc khách hàng',
            ],
            [
                'name' => RoleEnum::KY_THUAT->value,
                'display_name' => 'Kỹ thuật',
                'description' => 'Thực hiện hiện trường, vận hành, kỹ thuật',
            ],
            [
                'name' => RoleEnum::MARKETING->value,
                'display_name' => 'Marketing',
                'description' => 'Bài viết, báo cáo marketing, nội bộ',
            ],
            [
                'name' => RoleEnum::KE_TOAN->value,
                'display_name' => 'Kế toán',
                'description' => 'Hóa đơn, hoa hồng, ứng tiền, công nợ',
            ],
            [
                'name' => RoleEnum::THUC_TAP->value,
                'display_name' => 'Thuc tap',
                'description' => 'Chi truy cap bao cao ngay',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => 'web'],
            );
        }
    }
}
