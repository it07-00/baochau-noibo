<?php

namespace Database\Seeders;

use App\Models\SalesTarget;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesTargetSeeder extends Seeder
{
    public function run(): void
    {
        $kdUsers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['kinh-doanh', 'tp-kinh-doanh']))->get();
        if ($kdUsers->isEmpty()) return;

        $year = (int) date('Y');

        foreach ($kdUsers as $user) {
            for ($month = 1; $month <= 12; $month++) {
                SalesTarget::firstOrCreate(
                    ['year' => $year, 'month' => $month, 'staff_id' => $user->id],
                    [
                        'target_amount' => rand(50, 300) * 1000000,
                        'target_count'  => rand(3, 15),
                    ]
                );
            }
        }
    }
}
