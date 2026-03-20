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
                'name'          => 'Quản trị hệ thống',
                'username'      => 'admin',
                'password'      => 'password',
                'role'          => 'it',
                'department'    => 'admin',
            ],
            [
                'name'          => 'Nguyễn Văn Quản Lý',
                'username'      => 'quanly',
                'password'      => 'password',
                'role'          => 'quan-ly',
                'department'    => 'admin',
            ],
            [
                'name'          => 'Trần Thị Kinh Doanh',
                'username'      => 'kinhdoanh',
                'password'      => 'password',
                'role'          => 'kinh-doanh',
                'department'    => 'kinh-doanh',
            ],
            [
                'name'          => 'Lê Văn Kế Toán',
                'username'      => 'ketoan',
                'password'      => 'password',
                'role'          => 'ke-toan',
                'department'    => 'ke-toan',
            ],
            [
                'name'          => 'Phạm Thị Tư Vấn',
                'username'      => 'tuvan',
                'password'      => 'password',
                'role'          => 'tu-van',
                'department'    => 'tu-van-cskh',
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
