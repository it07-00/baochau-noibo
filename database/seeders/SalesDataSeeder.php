<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\SalesRenewal;
use App\Models\SalesProgressive;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesDataSeeder extends Seeder
{
    public function run(): void
    {
        $kdUsers = User::whereHas('roles', fn($q) => $q->whereIn('name', [Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value]))->pluck('id')->toArray();
        if (empty($kdUsers)) return;

        // Renewal Sales — 8 bản ghi
        for ($i = 0; $i < 8; $i++) {
            $staffId = $kdUsers[array_rand($kdUsers)];
            $month = rand(1, 12);
            $value = rand(8, 120) * 1000000;

            SalesRenewal::create([
                'contract_number' => 'TK-2025-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'sales_month'     => "2025-{$month}-01",
                'sales_value'     => $value,
                'commission'      => (int) ($value * rand(5, 12) / 100),
                'sales_percentage'=> rand(60, 100),
                'sales_amount'    => (int) ($value * rand(60, 100) / 100),
                'status'          => ['Đã tái ký', 'Chưa tái ký', 'Đang tái ký', 'Rớt tái ký'][rand(0, 3)],
                'user_id'         => $staffId,
            ]);
        }

        // Progressive Sales — 6 bản ghi
        for ($i = 0; $i < 6; $i++) {
            $staffId = $kdUsers[array_rand($kdUsers)];
            $month = rand(1, 12);
            $amount = rand(10, 80) * 1000000;

            SalesProgressive::create([
                'contract_number' => 'TD-2025-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'sales_month'     => "2025-{$month}-01",
                'milestone_name'  => ['Đợt 1', 'Đợt 2', 'Đợt 3', 'Thanh toán cuối'][rand(0, 3)],
                'percentage'      => rand(20, 100),
                'amount'          => $amount,
                'status'          => ['Chờ thanh toán', 'Đã thanh toán', 'Quá hạn'][rand(0, 2)],
                'user_id'         => $staffId,
            ]);
        }
    }
}
