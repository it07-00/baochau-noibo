<?php

namespace Database\Seeders;

use App\Models\QuotationSales;
use App\Models\RenewalSales;
use App\Models\ProgressiveSales;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesDataSeeder extends Seeder
{
    public function run(): void
    {
        $kdUsers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['kinh-doanh', 'tp-kinh-doanh']))->pluck('id')->toArray();
        if (empty($kdUsers)) return;

        $services = [
            'Chất thải nguy hại', 'Chất thải công nghiệp', 'Quan trắc môi trường',
            'Giấy phép môi trường', 'Đánh giá tác động MT', 'Vận hành hệ thống XLNT',
            'Kiểm kê khí nhà kính', 'Hủy hàng',
        ];
        $provinces = [
            'TP. Hồ Chí Minh', 'Bình Dương', 'Đồng Nai', 'Long An', 'Bà Rịa - Vũng Tàu',
            'Bình Thuận', 'Tây Ninh', 'Hà Nội', 'Bắc Ninh', 'Hải Phòng',
        ];
        $sources = ['Sale mới', 'Thông tin chuyển', 'Tái ký', 'Khách giới thiệu'];
        $statuses = ['Đang báo giá', 'Chờ phản hồi', 'Đã ký HĐ', 'Không ký', 'Đang theo dõi'];

        // Quotation Sales — 12 tháng x 2-3 báo giá
        for ($m = 1; $m <= 12; $m++) {
            $count = rand(2, 4);
            for ($i = 0; $i < $count; $i++) {
                $staffId = $kdUsers[array_rand($kdUsers)];
                $value = rand(5, 200) * 1000000;
                $commission = (int) ($value * rand(5, 15) / 100);
                $salesPct = rand(50, 100);

                QuotationSales::create([
                    'quotation_number' => 'BG-2025-' . str_pad($m, 2, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'staff_id'         => $staffId,
                    'sales_month'      => "2025-{$m}-01",
                    'service'          => $services[array_rand($services)],
                    'info_source'      => $sources[array_rand($sources)],
                    'quotation_date'   => "2025-{$m}-" . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
                    'value_ext_vat'    => $value,
                    'commission'       => $commission,
                    'sales_percentage' => $salesPct,
                    'sales_amount'     => (int) ($value * $salesPct / 100),
                    'company_name'     => 'Công ty mẫu #' . rand(100, 999),
                    'province'         => $provinces[array_rand($provinces)],
                    'status'           => $statuses[array_rand($statuses)],
                    'user_id'          => $staffId,
                ]);
            }
        }

        // Renewal Sales — 8 bản ghi
        for ($i = 0; $i < 8; $i++) {
            $staffId = $kdUsers[array_rand($kdUsers)];
            $month = rand(1, 12);
            $value = rand(8, 120) * 1000000;

            RenewalSales::create([
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

            ProgressiveSales::create([
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
