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
                'description' => 'Quản trị hệ thống, toàn quyền',
            ],
            [
                'name' => 'quan-ly',
                'display_name' => 'Quản lý',
                'description' => 'Quản lý phòng ban, duyệt HĐ, xem báo cáo',
            ],
            [
                'name' => 'kinh-doanh',
                'display_name' => 'Kinh doanh',
                'description' => 'Bán hàng, báo giá, doanh số, bài viết',
            ],
            [
                'name' => 'ke-toan',
                'display_name' => 'Kế toán',
                'description' => 'Hóa đơn, hoa hồng, ứng tiền',
            ],
            [
                'name' => 'tu-van',
                'display_name' => 'Tư vấn - CSKH',
                'description' => 'Tư vấn môi trường, chăm sóc khách hàng',
            ],
            [
                'name' => 'ky-thuat',
                'display_name' => 'Kỹ thuật',
                'description' => 'Thực hiện hiện trường, vận hành, kỹ thuật',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => 'web'],
            );
        }
    }
}
