<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        $sampleUsers = [
            [
                'name'       => 'Quản trị hệ thống',
                'username'   => 'admin',
                'password'   => 'password',
                'role'       => 'it',
                'department' => 'admin',
            ],
            [
                'name'       => 'Giám Đốc',
                'username'   => 'giamdoc',
                'password'   => 'password',
                'role'       => 'giam-doc',
                'department' => 'ban-giam-doc',
            ],
            [
                'name'       => 'Trưởng Phòng KD',
                'username'   => 'tpkd',
                'password'   => 'password',
                'role'       => 'tp-kinh-doanh',
                'department' => 'kinh-doanh',
            ],
            [
                'name'       => 'Kinh Doanh',
                'username'   => 'kinhdoanh',
                'password'   => 'password',
                'role'       => 'kinh-doanh',
                'department' => 'kinh-doanh',
            ],
            [
                'name'       => 'Kinh Doanh 2',
                'username'   => 'kinhdoanh2',
                'password'   => 'password',
                'role'       => 'kinh-doanh',
                'department' => 'kinh-doanh',
            ],
            [
                'name'       => 'Tư Vấn',
                'username'   => 'tuvan',
                'password'   => 'password',
                'role'       => 'tu-van',
                'department' => 'tu-van-cskh',
            ],
            [
                'name'       => 'Kỹ Thuật',
                'username'   => 'kythuat',
                'password'   => 'password',
                'role'       => 'ky-thuat',
                'department' => 'ky-thuat',
            ],
            [
                'name'       => 'Marketing',
                'username'   => 'marketing',
                'password'   => 'password',
                'role'       => 'marketing',
                'department' => 'marketing',
            ],
            [
                'name'       => 'Kế Toán',
                'username'   => 'ketoan',
                'password'   => 'password',
                'role'       => 'ke-toan',
                'department' => 'ke-toan',
            ],
        ];

        foreach ($sampleUsers as $userData) {
            $dept = Department::where('slug', $userData['department'])->first();

            $user = User::updateOrCreate(
                ['username' => $userData['username']],
                [
                    'name'          => $userData['name'],
                    'password'      => Hash::make($userData['password']),
                    'department_id' => $dept?->id,
                    'is_active'     => true,
                ]
            );

            if (!$user->hasRole($userData['role'])) {
                $user->syncRoles([$userData['role']]);
            }
        }
    }
}
