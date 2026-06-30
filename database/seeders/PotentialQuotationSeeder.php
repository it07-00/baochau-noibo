<?php

namespace Database\Seeders;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Database\Seeder;

class PotentialQuotationSeeder extends Seeder
{
    public function run(): void
    {
        $staffIds = User::role(['kinh-doanh', 'tp-kinh-doanh'])
            ->orderBy('id')
            ->pluck('id')
            ->values();

        if ($staffIds->isEmpty()) {
            $this->command?->warn('Không có nhân viên kinh doanh để tạo báo giá mẫu.');

            return;
        }

        $samples = [
            ['month' => 4, 'company' => 'Công ty Mẫu An Phát', 'service' => 'Hồ sơ môi trường', 'source' => 'Sale', 'original' => 80_000_000],
            ['month' => 4, 'company' => 'Công ty Mẫu Minh Long', 'service' => 'Xử lý chất thải', 'source' => 'Thông tin chuyển MKT', 'original' => 125_000_000],
            ['month' => 5, 'company' => 'Công ty Mẫu Đại Nam', 'service' => 'Quan trắc môi trường', 'source' => 'Tái ký', 'original' => 65_000_000],
            ['month' => 5, 'company' => 'Công ty Mẫu Hưng Thịnh', 'service' => 'Ứng phó sự cố', 'source' => 'Sale', 'original' => 210_000_000],
            ['month' => 6, 'company' => 'Công ty Mẫu Phú Gia', 'service' => 'Kiểm kê khí nhà kính', 'source' => 'Thông tin chuyển MKT', 'original' => 150_000_000],
            ['month' => 6, 'company' => 'Công ty Mẫu Thành Công', 'service' => 'Tư vấn phát triển bền vững', 'source' => 'Tái ký', 'original' => 95_000_000],
        ];

        foreach ($samples as $index => $sample) {
            $commission = ($index + 1) * 500_000;
            $commissionTax = $commission <= 1_000_000
                ? $commission * 0.20
                : $commission * 0.30;
            $valueBeforeVat = $sample['original'] + $commission + $commissionTax;

            Quotation::query()->updateOrCreate(
                ['quotation_number' => 'BG-TN-MAU-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)],
                [
                    'date' => "2026-{$sample['month']}-15",
                    'staff_id' => $staffIds[$index % $staffIds->count()],
                    'source' => $sample['source'],
                    'company_name' => $sample['company'],
                    'service' => $sample['service'],
                    'contact_person' => 'Khách hàng mẫu '.($index + 1),
                    'work_description' => 'Dữ liệu mẫu kiểm tra cột doanh số chưa chắc chắn.',
                    'status' => QuotationStatus::BAO_GIA_TIEM_NANG->value,
                    'original_value' => $sample['original'],
                    'commission_value' => $commission,
                    'commission_tax' => $commissionTax,
                    'value_inc_vat' => $valueBeforeVat,
                    'total_value' => round($valueBeforeVat * 1.08),
                    'notes' => 'Dữ liệu mẫu — có thể chạy lại seeder an toàn.',
                ]
            );
        }

        $this->command?->info('Đã tạo 6 báo giá tiềm năng mẫu.');
    }
}
