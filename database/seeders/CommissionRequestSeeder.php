<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\CommissionRequest;
use App\Models\ContractLegal;
use App\Models\ContractWaste;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommissionRequestSeeder extends Seeder
{
    public function run(): void
    {
        $kdUsers = User::whereHas('roles', fn ($q) => $q->whereIn('name', [Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value]))->pluck('id')->toArray();
        if (empty($kdUsers)) {
            return;
        }

        $statuses = ['Dự chi', 'Đã duyệt', 'Đã chi', 'Từ chối'];
        $i = 0;

        // Waste contracts
        $wasteContracts = ContractWaste::where('commission', '>', 0)->limit(3)->get();
        foreach ($wasteContracts as $contract) {
            $status = $statuses[$i % count($statuses)];
            CommissionRequest::create([
                'contract_type' => ContractWaste::class,
                'contract_id' => $contract->id,
                'receiver_name' => 'Người nhận hoa hồng #'.(++$i),
                'receiver_phone' => '09'.rand(10000000, 99999999),
                'bank_account' => rand(1000000000, 9999999999),
                'amount' => $contract->commission,
                'referrer_info' => 'Giới thiệu qua khách hàng cũ',
                'status' => $status,
                'processed_at' => $status !== 'Dự chi' ? now()->subDays(rand(1, 30)) : null,
                'user_id' => $kdUsers[array_rand($kdUsers)],
            ]);
        }

        // Consulting contracts
        $consultingContracts = ContractLegal::where('commission', '>', 0)->limit(2)->get();
        foreach ($consultingContracts as $contract) {
            $status = $statuses[$i % count($statuses)];
            CommissionRequest::create([
                'contract_type' => ContractLegal::class,
                'contract_id' => $contract->id,
                'receiver_name' => 'Người nhận hoa hồng #'.(++$i),
                'receiver_phone' => '09'.rand(10000000, 99999999),
                'bank_account' => rand(1000000000, 9999999999),
                'amount' => $contract->commission,
                'referrer_info' => 'Giới thiệu qua đối tác',
                'status' => $status,
                'processed_at' => $status !== 'Dự chi' ? now()->subDays(rand(1, 30)) : null,
                'user_id' => $kdUsers[array_rand($kdUsers)],
            ]);
        }
    }
}
