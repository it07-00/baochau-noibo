<?php

namespace Database\Seeders;

use App\Models\ContractPaymentSchedule;
use App\Models\ContractWaste;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContractPaymentScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::where('username', 'kinhdoanh')->first();
        $createdBy = $staff?->id;

        // Lấy các hợp đồng waste đã seed
        $hoanloc   = ContractWaste::where('shd_cxl', 'HOÀN LỘC')->first();
        $duykha    = ContractWaste::where('shd_cxl', 'DUY KHA')->first();
        $thanhbinh = ContractWaste::where('shd_cxl', 'THANH BÌNH ĐỒNG THÁP')->first();

        $wasteType = ContractWaste::class;

        // ── HĐ HOÀN LỘC: 7.500.000 – chia 2 đợt ──
        if ($hoanloc) {
            ContractPaymentSchedule::create([
                'contract_type'    => $wasteType,
                'contract_id'      => $hoanloc->id,
                'installment_number' => 1,
                'installment_name' => 'Đợt 1 – Tạm ứng sau ký HĐ',
                'percentage'       => 50,
                'amount'           => 3750000,
                'due_date'         => '2026-04-01',
                'paid_date'        => '2026-03-28',
                'paid_amount'      => 3750000,
                'status'           => 'paid',
                'notes'            => 'Khách chuyển khoản đúng hạn',
                'created_by'       => $createdBy,
            ]);

            ContractPaymentSchedule::create([
                'contract_type'    => $wasteType,
                'contract_id'      => $hoanloc->id,
                'installment_number' => 2,
                'installment_name' => 'Đợt 2 – Thanh toán sau hoàn thành',
                'percentage'       => 50,
                'amount'           => 3750000,
                'due_date'         => '2026-06-01',
                'paid_date'        => null,
                'paid_amount'      => 0,
                'status'           => 'pending',
                'notes'            => null,
                'created_by'       => $createdBy,
            ]);
        }

        // ── HĐ DUY KHA: 5.000.000 – thanh toán 1 lần ──
        if ($duykha) {
            ContractPaymentSchedule::create([
                'contract_type'    => $wasteType,
                'contract_id'      => $duykha->id,
                'installment_number' => 1,
                'installment_name' => 'Thanh toán 1 lần',
                'percentage'       => 100,
                'amount'           => 5000000,
                'due_date'         => '2026-04-15',
                'paid_date'        => null,
                'paid_amount'      => 0,
                'status'           => 'pending',
                'notes'            => 'Chờ khách hàng ký xong mới thu',
                'created_by'       => $createdBy,
            ]);
        }

        // ── HĐ THANH BÌNH ĐỒNG THÁP: 31.300.000 – chia 3 đợt ──
        if ($thanhbinh) {
            ContractPaymentSchedule::create([
                'contract_type'    => $wasteType,
                'contract_id'      => $thanhbinh->id,
                'installment_number' => 1,
                'installment_name' => 'Đợt 1 – Tạm ứng 30%',
                'percentage'       => 30,
                'amount'           => 9390000,
                'due_date'         => '2026-03-25',
                'paid_date'        => '2026-03-24',
                'paid_amount'      => 9390000,
                'status'           => 'paid',
                'notes'            => 'Đã nhận chuyển khoản',
                'created_by'       => $createdBy,
            ]);

            ContractPaymentSchedule::create([
                'contract_type'    => $wasteType,
                'contract_id'      => $thanhbinh->id,
                'installment_number' => 2,
                'installment_name' => 'Đợt 2 – Thu giữa kỳ 40%',
                'percentage'       => 40,
                'amount'           => 12520000,
                'due_date'         => '2026-05-15',
                'paid_date'        => null,
                'paid_amount'      => 5000000,
                'status'           => 'partial',
                'notes'            => 'KH thanh toán 1 phần, hẹn trả nốt cuối tháng 5',
                'created_by'       => $createdBy,
            ]);

            ContractPaymentSchedule::create([
                'contract_type'    => $wasteType,
                'contract_id'      => $thanhbinh->id,
                'installment_number' => 3,
                'installment_name' => 'Đợt 3 – Thanh lý 30%',
                'percentage'       => 30,
                'amount'           => 9390000,
                'due_date'         => '2026-03-15',
                'paid_date'        => null,
                'paid_amount'      => 0,
                'status'           => 'overdue',
                'notes'            => 'Đã quá hạn, cần liên hệ lại KH',
                'created_by'       => $createdBy,
            ]);
        }
    }
}
