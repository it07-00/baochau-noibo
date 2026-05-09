<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Ban Giám đốc', 'slug' => 'ban-giam-doc'],
            ['name' => 'Phòng Admin / IT', 'slug' => 'admin'],
            ['name' => 'Phòng Kinh doanh', 'slug' => 'kinh-doanh'],
            ['name' => 'Phòng Kỹ thuật', 'slug' => 'ky-thuat'],
            ['name' => 'Phòng Kế toán', 'slug' => 'ke-toan'],
            ['name' => 'Phòng Tư vấn', 'slug' => 'tu-van-cskh'],
            ['name' => 'Phòng Marketing', 'slug' => 'marketing'],
            ['name' => 'Phòng Tổng hợp', 'slug' => 'tong-hop'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['slug' => $dept['slug']], $dept);
        }
    }
}
