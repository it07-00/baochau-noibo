<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->error('SampleUsersSeeder must not run in production. Aborting.');
            return;
        }

        $sampleUsers = [
            [
                'name'       => 'Quản trị hệ thống',
                'username'   => 'admin',
                'password'   => 'password',
                'role'       => Role::IT->value,
                'department' => 'admin',
            ],
            [
                'name'       => 'Giám Đốc',
                'username'   => 'giamdoc',
                'password'   => 'password',
                'role'       => Role::GIAM_DOC->value,
                'department' => 'ban-giam-doc',
            ],
            [
                'name'       => 'Trưởng Phòng KD',
                'username'   => 'tpkd',
                'password'   => 'password',
                'role'       => Role::TP_KINH_DOANH->value,
                'department' => 'kinh-doanh',
            ],
            [
                'name'       => 'Kinh Doanh',
                'username'   => 'kinhdoanh',
                'password'   => 'password',
                'role'       => Role::KINH_DOANH->value,
                'department' => 'kinh-doanh',
            ],
            [
                'name'       => 'Kinh Doanh 2',
                'username'   => 'kinhdoanh2',
                'password'   => 'password',
                'role'       => Role::KINH_DOANH->value,
                'department' => 'kinh-doanh',
            ],
            [
                'name'       => 'Tư Vấn',
                'username'   => 'tuvan',
                'password'   => 'password',
                'role'       => Role::TU_VAN->value,
                'department' => 'tu-van-cskh',
            ],
            [
                'name'       => 'Kỹ Thuật',
                'username'   => 'kythuat',
                'password'   => 'password',
                'role'       => Role::KY_THUAT->value,
                'department' => 'ky-thuat',
            ],
            [
                'name'       => 'Marketing',
                'username'   => 'marketing',
                'password'   => 'password',
                'role'       => Role::MARKETING->value,
                'department' => 'marketing',
            ],
            [
                'name'       => 'Kế Toán',
                'username'   => 'ketoan',
                'password'   => 'password',
                'role'       => Role::KE_TOAN->value,
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
