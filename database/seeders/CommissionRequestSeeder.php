<?php

namespace Database\Seeders;

use App\Models\CommissionRequest;
use App\Models\ContractWaste;
use App\Models\ContractLegal;
use App\Models\ContractTechnical;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommissionRequestSeeder extends Seeder
{
    public function run(): void
    {
        $kdUsers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['kinh-doanh', 'tp-kinh-doanh']))->pluck('id')->toArray();
        if (empty($kdUsers)) return;

        $statuses = ['Chờ chi', 'Đã chi', 'Từ chối'];
        $i = 0;

        // Waste contracts
        $wasteContracts = ContractWaste::where('commission', '>', 0)->limit(3)->get();
        foreach ($wasteContracts as $contract) {
            CommissionRequest::create([
                'contract_type' => ContractWaste::class,
                'contract_id'   => $contract->id,
                'receiver_name' => 'Người nhận hoa hồng #' . (++$i),
                'receiver_phone' => '09' . rand(10000000, 99999999),
                'bank_account'  => rand(1000000000, 9999999999),
                'amount'        => $contract->commission,
                'referrer_info' => 'Giới thiệu qua khách hàng cũ',
                'status'        => $statuses[$i % 3],
                'processed_at'  => $statuses[$i % 3] !== 'Chờ chi' ? now()->subDays(rand(1, 30)) : null,
                'user_id'       => $kdUsers[array_rand($kdUsers)],
            ]);
        }

        // Consulting contracts
        $consultingContracts = ContractLegal::where('commission', '>', 0)->limit(2)->get();
        foreach ($consultingContracts as $contract) {
            CommissionRequest::create([
                'contract_type' => ContractLegal::class,
                'contract_id'   => $contract->id,
                'receiver_name' => 'Người nhận hoa hồng #' . (++$i),
                'receiver_phone' => '09' . rand(10000000, 99999999),
                'bank_account'  => rand(1000000000, 9999999999),
                'amount'        => $contract->commission,
                'referrer_info' => 'Giới thiệu qua đối tác',
                'status'        => $statuses[$i % 3],
                'processed_at'  => $statuses[$i % 3] !== 'Chờ chi' ? now()->subDays(rand(1, 30)) : null,
                'user_id'       => $kdUsers[array_rand($kdUsers)],
            ]);
        }
    }
}
