<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'it',
                'display_name' => 'IT / Quản trị',
                'description' => 'Quản trị hệ thống, quản lý quyền/vai trò/user',
            ],
            [
                'name' => 'giam-doc',
                'display_name' => 'Giám đốc',
                'description' => 'Xem tất cả, trừ quản trị hệ thống',
            ],
            [
                'name' => 'tp-kinh-doanh',
                'display_name' => 'Trưởng phòng KD',
                'description' => 'Quản lý kinh doanh, KH, CXL, hợp đồng, chuyển phát, báo cáo KD',
            ],
            [
                'name' => 'kinh-doanh',
                'display_name' => 'Nhân viên KD',
                'description' => 'Kinh doanh, báo giá, doanh số',
            ],
            [
                'name' => 'tu-van',
                'display_name' => 'Tư vấn',
                'description' => 'Tư vấn môi trường, chăm sóc khách hàng',
            ],
            [
                'name' => 'ky-thuat',
                'display_name' => 'Kỹ thuật',
                'description' => 'Thực hiện hiện trường, vận hành, kỹ thuật',
            ],
            [
                'name' => 'marketing',
                'display_name' => 'Marketing',
                'description' => 'Bài viết, báo cáo marketing, nội bộ',
            ],
            [
                'name' => 'ke-toan',
                'display_name' => 'Kế toán',
                'description' => 'Hóa đơn, hoa hồng, ứng tiền, công nợ',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => 'web'],
            );
        }
    }
}
