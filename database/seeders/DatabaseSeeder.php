<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(20)->create();

        User::factory()->create([
            'name' => 'Quản trị hệ thống',
            'username' => 'admin',
            'password' => 'password',
        ]);

        User::factory()->create([
            'name' => 'Tài khoản mẫu',
            'username' => 'testuser',
            'password' => 'password',
        ]);
    }
}
