<?php

namespace Database\Seeders;

use App\Models\Quotation;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuotationSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::where('username', 'kinhdoanh')->first();
        if (!$staff) {
            $staff = User::first();
        }

        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Quotation::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $data = [
            [
                'date' => '2026-03-02',
                'company_name' => 'CÔNG TY TNHH MTV DỊCH VỤ CÔNG ÍCH NHÀ BÈ',
                'address' => '2281/16 Huỳnh Tấn Phát, Khu phố 7, Thị Trấn Nhà Bè, Huyện Nhà Bè, TP HCM',
                'contact_person' => 'Chi Yến',
                'work_description' => 'BG chi phí 2026 có điều chỉnh tăng giá so với 2025; và gửi BG chân gỗ cho chị;',
                'status' => 'Ký hợp đồng',
                'original_value' => 71900000,
                'value_inc_vat' => 79090000,
                'commission_tax' => 7190000,
                'commission_value' => 2000000,
                'total_value' => 77090000, // 79090000 - 2000000
            ],
            [
                'date' => '2026-03-03',
                'company_name' => 'CÔNG TY TNHH MỘT THÀNH VIÊN TRANSIMEX HI TECH PARK LOGISTICS',
                'address' => 'Lô BT, Đường D2, Khu Công nghệ cao Thành phố Hồ Chí Minh, Phường Tăng Nhơn Phú, TP Hồ Chí Minh, Việt Nam',
                'contact_person' => 'Anh Tùng',
                'work_description' => 'BG anh Tùng xem chi phí',
                'status' => 'Đang theo dõi',
                'original_value' => 30625000,
                'value_inc_vat' => 33687500,
                'commission_tax' => 3062500,
                'commission_value' => 3500000,
                'total_value' => 30187500,
            ],
            [
                'date' => '2026-03-03',
                'company_name' => 'CÔNG TY CỔ PHẦN VÂN HÓA TỔNG HỢP BẾN THÀNH',
                'address' => '160 Hai Bà Trưng, Phường Tân Định, Thành phố Hồ Chí Minh, Việt Nam',
                'contact_person' => 'Chị Ánh',
                'work_description' => 'BG ứng phó sự cố chất thải',
                'status' => 'Đang theo dõi',
                'original_value' => 35000000,
                'value_inc_vat' => 38500000,
                'commission_tax' => 3500000,
                'commission_value' => 0,
                'total_value' => 38500000,
            ],
            [
                'date' => '2026-03-03',
                'company_name' => 'CÔNG TY SẮC KÝ',
                'address' => 'Bình Dương',
                'contact_person' => 'Anh Hòa giới thiệu',
                'work_description' => 'BG QTMTLĐ cho anh Hòa;',
                'status' => 'Đang theo dõi',
                'original_value' => 6191250,
                'value_inc_vat' => 6810375,
                'commission_tax' => 619125,
                'commission_value' => 1651000,
                'total_value' => 5159375,
            ],
            [
                'date' => '2026-03-03',
                'company_name' => 'CÔNG TY TNHH CÔNG NGHIỆP THỰC PHẨM LIWAYWAY SÀI GÒN',
                'address' => 'số 18 đường 6 khu công nghiệp Việt Nam - Singapore, Phường Bình Hòa, Thành phố Thuận An, Tỉnh Bình Dương, Việt Nam',
                'contact_person' => 'Anh Phương',
                'work_description' => 'BG QTMT quý 1 2026;',
                'status' => 'Đang theo dõi',
                'original_value' => 35306000,
                'value_inc_vat' => 38836600,
                'commission_tax' => 3530600,
                'commission_value' => 0,
                'total_value' => 38836600,
            ],
        ];

        foreach ($data as $item) {
            $item['staff_id'] = $staff->id;
            Quotation::create($item);
        }
    }
}
