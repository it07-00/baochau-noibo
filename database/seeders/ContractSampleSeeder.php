<?php

namespace Database\Seeders;

use App\Models\ContractResearch;
use App\Models\ContractLegal;
use App\Models\ContractEmission;
use App\Models\ContractTechnical;
use App\Models\ContractSustainability;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContractSampleSeeder extends Seeder
{
    public function run(): void
    {
        $deptKD  = Department::where('slug', 'kinh-doanh')->first();
        $deptTV  = Department::where('slug', 'tu-van-cskh')->first();
        $deptKT  = Department::where('slug', 'ky-thuat')->first();

        $staffKD = User::where('username', 'kinhdoanh')->first();
        $staffTV = User::where('username', 'tuvan')->first();
        $staffTP = User::where('username', 'tpkd')->first();

        // ============================================================
        // 1. HỢP ĐỒNG CHẤT THẢI (ContractWaste) — 10 bản ghi
        // ============================================================
        $handler = Handler::firstOrCreate(
            ['name' => 'Công ty CP Công Nghệ Môi Trường Trái Đất Xanh'],
            ['phone' => '028.38123456', 'address' => 'Số 123, Đường ABC, Quận XYZ, TP.HCM']
        );

        $wasteData = [
            [
                'customer' => ['name' => 'CÔNG TY TNHH SẢN XUẤT BAO BÌ ĐÔNG NAM',      'representative' => 'Anh Hùng',    'phone' => '0901234501', 'address' => 'KCN Đông Nam, Củ Chi, TP.HCM'],
                'contract' => ['shd_cxl' => 'ĐÔNG NAM BB-01',  'shd_bc' => 'ĐÔNG NAM BB-01',  'content' => 'KL: 500 KG/NĂM - TS: 02 LẦN/NĂM', 'value' => 18500000, 'commission' => 2000000, 'revenue' => 16000000, 'signed_at' => '2025-01-10', 'effective_at' => '2025-01-10', 'end_at' => '2026-01-10', 'submitted_at' => '2025-01-15', 'waste_type' => 'CTNH', 'service_type' => 'Chất thải', 'status' => 'Đã trình ký Chủ xử lý', 'renewal_status' => 'Chưa tái ký', 'source' => 'TÁI KÝ', 'payment_method' => 'Sau ký', 'province' => 'TP. Hồ Chí Minh'],
            ],
            [
                'customer' => ['name' => 'CÔNG TY TNHH CHẾ BIẾN THỦY SẢN BÌNH THUẬN',  'representative' => 'Chị Lan',     'phone' => '0901234502', 'address' => 'KCN Phan Thiết, Bình Thuận'],
                'contract' => ['shd_cxl' => 'THỦY SẢN BT-02',  'shd_bc' => 'THỦY SẢN BT-02',  'content' => 'KL: 200 KG/NĂM - TS: 01 LẦN/NĂM', 'value' => 9800000,  'commission' => 1000000, 'revenue' => 8500000,  'signed_at' => '2025-02-05', 'effective_at' => '2025-02-05', 'end_at' => '2026-02-05', 'submitted_at' => '2025-02-10', 'waste_type' => 'CTNH & CTCN', 'service_type' => 'Chất thải', 'status' => 'Đã gửi khách hàng', 'renewal_status' => 'Đã tái ký', 'source' => 'TÁI KÝ', 'payment_method' => 'Sau ký', 'province' => 'Bình Thuận'],
            ],
            [
                'customer' => ['name' => 'CÔNG TY CP DỆT MAY LONG AN',                  'representative' => 'Anh Toàn',    'phone' => '0901234503', 'address' => 'KCN Đức Hoà III, Long An'],
                'contract' => ['shd_cxl' => 'DỆT MAY LA-03',   'shd_bc' => 'DỆT MAY LA-03',   'content' => 'KL: 1000 KG/NĂM - TS: 04 LẦN/NĂM', 'value' => 35000000, 'commission' => 4000000, 'revenue' => 30000000, 'signed_at' => '2025-03-12', 'effective_at' => '2025-03-12', 'end_at' => '2026-03-12', 'submitted_at' => '2025-03-18', 'waste_type' => 'CTNH', 'service_type' => 'Chất thải', 'status' => 'Đã hoàn thành KH ký trước', 'renewal_status' => 'Chưa tái ký', 'source' => 'MỚI', 'payment_method' => 'Trước ký', 'province' => 'Long An'],
            ],
            [
                'customer' => ['name' => 'CÔNG TY TNHH SẠ CHẾ BIẾN GỖ BÌNH DƯƠNG',     'representative' => 'Chị Tuyết',   'phone' => '0901234504', 'address' => 'KCN VSIP II, Bình Dương'],
                'contract' => ['shd_cxl' => 'GỖ BD-04',         'shd_bc' => 'GỖ BD-04',         'content' => 'KL: 300 KG/NĂM - TS: 01 LẦN/NĂM', 'value' => 12000000, 'commission' => 1500000, 'revenue' => 10200000, 'signed_at' => '2025-04-08', 'effective_at' => '2025-04-08', 'end_at' => '2026-04-08', 'submitted_at' => '2025-04-12', 'waste_type' => 'CTCN', 'service_type' => 'Chất thải', 'status' => 'Hợp đồng hủy', 'renewal_status' => 'Không tái ký', 'source' => 'MỚI', 'payment_method' => 'Sau ký', 'province' => 'Bình Dương'],
            ],
            [
                'customer' => ['name' => 'CÔNG TY CP IN ẤN VÀ BAO BÌ HÀ NỘI',           'representative' => 'Anh Minh',    'phone' => '0901234505', 'address' => 'KCN Thạch Thất, Hà Nội'],
                'contract' => ['shd_cxl' => 'IN ẤN HN-05',      'shd_bc' => 'IN ẤN HN-05',      'content' => 'KL: 150 KG/NĂM - TS: 02 LẦN/NĂM', 'value' => 8500000,  'commission' => 900000,  'revenue' => 7300000,  'signed_at' => '2025-05-20', 'effective_at' => '2025-05-20', 'end_at' => '2026-05-20', 'submitted_at' => '2025-05-25', 'waste_type' => 'CTNH & CTCN', 'service_type' => 'Hủy hàng', 'status' => 'Đã trình ký Chủ xử lý', 'renewal_status' => 'Đang tái ký', 'source' => 'TÁI KÝ', 'payment_method' => 'Sau ký', 'province' => 'Hà Nội'],
            ],
            [
                'customer' => ['name' => 'CÔNG TY TNHH ĐIỆN TỬ SAMSUNG VIỆT NAM',       'representative' => 'Anh Hải',     'phone' => '0901234506', 'address' => 'KCN Tiên Sơn, Bắc Ninh'],
                'contract' => ['shd_cxl' => 'SAMSUNG BN-06',    'shd_bc' => 'SAMSUNG BN-06',    'content' => 'KL: 5000 KG/NĂM - TS: 12 LẦN/NĂM', 'value' => 120000000, 'commission' => 15000000, 'revenue' => 100000000, 'signed_at' => '2025-06-01', 'effective_at' => '2025-06-01', 'end_at' => '2026-06-01', 'submitted_at' => '2025-06-05', 'waste_type' => 'CTNH', 'service_type' => 'Chất thải', 'status' => 'Đã hoàn thành KH ký trước', 'renewal_status' => 'Đã tái ký', 'source' => 'TÁI KÝ', 'payment_method' => 'Sau ký', 'province' => 'Bắc Ninh'],
            ],
            [
                'customer' => ['name' => 'CÔNG TY CP SẢN XUẤT NHỰA ĐỒNG NAI',           'representative' => 'Chị Phương',  'phone' => '0901234507', 'address' => 'KCN Biên Hòa 2, Đồng Nai'],
                'contract' => ['shd_cxl' => 'NHỰA ĐN-07',       'shd_bc' => 'NHỰA ĐN-07',       'content' => 'KL: 800 KG/NĂM - TS: 02 LẦN/NĂM', 'value' => 22000000, 'commission' => 2500000, 'revenue' => 19000000, 'signed_at' => '2025-07-14', 'effective_at' => '2025-07-14', 'end_at' => '2026-07-14', 'submitted_at' => '2025-07-20', 'waste_type' => 'CTCN', 'service_type' => 'Chất thải', 'status' => 'Đã gửi khách hàng', 'renewal_status' => 'Chưa tái ký', 'source' => 'MỚI', 'payment_method' => 'Sau ký', 'province' => 'Đồng Nai'],
            ],
            [
                'customer' => ['name' => 'CÔNG TY TNHH THỰC PHẨM CẦU TRE',              'representative' => 'Anh Khoa',    'phone' => '0901234508', 'address' => 'Quận Bình Tân, TP.HCM'],
                'contract' => ['shd_cxl' => 'CẦU TRE-08',       'shd_bc' => 'CẦU TRE-08',       'content' => 'KL: 400 KG/NĂM - TS: 02 LẦN/NĂM', 'value' => 15000000, 'commission' => 1800000, 'revenue' => 12800000, 'signed_at' => '2025-08-03', 'effective_at' => '2025-08-03', 'end_at' => '2026-08-03', 'submitted_at' => '2025-08-08', 'waste_type' => 'CTNH & CTCN', 'service_type' => 'Chất thải', 'status' => 'Đã trình ký Chủ xử lý', 'renewal_status' => 'Đang tái ký', 'source' => 'TÁI KÝ', 'payment_method' => 'Trước ký', 'province' => 'TP. Hồ Chí Minh'],
            ],
            [
                'customer' => ['name' => 'CÔNG TY CP SẢN XUẤT GIẤY VĨNH LONG',          'representative' => 'Chị Nga',     'phone' => '0901234509', 'address' => 'KCN Hoà Phú, Vĩnh Long'],
                'contract' => ['shd_cxl' => 'GIẤY VL-09',       'shd_bc' => 'GIẤY VL-09',       'content' => 'KL: 600 KG/NĂM - TS: 02 LẦN/NĂM', 'value' => 19500000, 'commission' => 2200000, 'revenue' => 16800000, 'signed_at' => '2025-09-22', 'effective_at' => '2025-09-22', 'end_at' => '2026-09-22', 'submitted_at' => '2025-09-28', 'waste_type' => 'CTCN', 'service_type' => 'Chất thải', 'status' => 'Đã hoàn thành KH ký trước', 'renewal_status' => 'Rớt tái ký', 'source' => 'MỚI', 'payment_method' => 'Sau ký', 'province' => 'Vĩnh Long'],
            ],
            [
                'customer' => ['name' => 'CÔNG TY CP HÓA CHẤT MIỀN NAM',                 'representative' => 'Anh Đức',     'phone' => '0901234510', 'address' => 'KCN Hiệp Phước, Nhà Bè, TP.HCM'],
                'contract' => ['shd_cxl' => 'HÓA CHẤT MN-10',   'shd_bc' => 'HÓA CHẤT MN-10',   'content' => 'KL: 2000 KG/NĂM - TS: 04 LẦN/NĂM', 'value' => 55000000, 'commission' => 6500000, 'revenue' => 47000000, 'signed_at' => '2025-10-15', 'effective_at' => '2025-10-15', 'end_at' => '2026-10-15', 'submitted_at' => '2025-10-20', 'waste_type' => 'CTNH', 'service_type' => 'Chất thải', 'status' => 'Đã hoàn thành KH ký trước', 'renewal_status' => 'Đã tái ký', 'source' => 'TÁI KÝ', 'payment_method' => 'Sau ký', 'province' => 'TP. Hồ Chí Minh'],
            ],
        ];

        foreach ($wasteData as $row) {
            $cus = Customer::firstOrCreate(['name' => $row['customer']['name']], $row['customer']);
            $wasteContract = $row['contract'];
            unset($wasteContract['province']);
            ContractWaste::create(array_merge($wasteContract, [
                'customer_id'  => $cus->id,
                'handler_id'   => $handler->id,
                'staff_id'     => $staffKD->id,
                'department_id' => $deptKD->id,
                'voucher_status' => 'Chưa chọn',
                'billing_address'   => '187 Nguyễn Văn Luông, P. Bình Phú, TP.HCM',
                'mailing_address'   => '187 Nguyễn Văn Luông, P. Bình Phú, TP.HCM',
                'execution_address' => $row['customer']['address'],
            ]));
        }

        // ============================================================
        // 2. HỢP ĐỒNG TƯ VẤN (ContractLegal) — 10 bản ghi
        // ============================================================
        $consultingData = [
            ['customer' => 'CÔNG TY TNHH KHU CÔNG NGHIỆP THÀNH ĐẠT',       'shd_bc' => 'TV-2025-001', 'value' => 45000000, 'commission' => 5000000, 'revenue' => 38000000, 'signed_at' => '2025-01-08', 'province' => 'TP. Hồ Chí Minh', 'loai_dich_vu' => 'Tư vấn, lập ĐTM, GPMT, DKMT',           'workflow_status' => 'finished',               'status' => 'HOÀN THÀNH',      'info_source' => 'TÁI KÝ'],
            ['customer' => 'CÔNG TY CP SẢN XUẤT THIẾT BỊ Y TẾ MEDIC',       'shd_bc' => 'TV-2025-002', 'value' => 32000000, 'commission' => 3500000, 'revenue' => 27000000, 'signed_at' => '2025-02-14', 'province' => 'Bình Dương',          'loai_dich_vu' => 'Quan trắc môi trường',                             'workflow_status' => 'consulting_processing',  'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY TNHH THÉP VINA STEEL',                   'shd_bc' => 'TV-2025-003', 'value' => 68000000, 'commission' => 8000000, 'revenue' => 57000000, 'signed_at' => '2025-03-05', 'province' => 'Đồng Nai',            'loai_dich_vu' => 'Tư vấn, lập ĐTM, GPMT, DKMT',           'workflow_status' => 'finished',               'status' => 'HOÀN THÀNH',      'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP CHẾ BIẾN NÔNG SẢN ĐĂKLĂK',           'shd_bc' => 'TV-2025-004', 'value' => 28500000, 'commission' => 3000000, 'revenue' => 24000000, 'signed_at' => '2025-04-22', 'province' => 'Đắk Lắk',             'loai_dich_vu' => 'Quan trắc môi trường lao động và phân loại lao động', 'workflow_status' => 'waiting_client',          'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY TNHH SẢN XUẤT GIÀY DA PHÚC THỊNH',      'shd_bc' => 'TV-2025-005', 'value' => 52000000, 'commission' => 6000000, 'revenue' => 44000000, 'signed_at' => '2025-05-10', 'province' => 'TP. Hồ Chí Minh',    'loai_dich_vu' => 'Tư vấn, lập ĐTM, GPMT, DKMT',           'workflow_status' => 'finished',               'status' => 'HOÀN THÀNH',      'info_source' => 'TÁI KÝ'],
            ['customer' => 'CÔNG TY CP XI MĂNG VICEM HÀ TIÊN',               'shd_bc' => 'TV-2025-006', 'value' => 95000000, 'commission' => 10000000, 'revenue' => 80000000,'signed_at' => '2025-06-18', 'province' => 'Kiên Giang',          'loai_dich_vu' => 'Quan trắc môi trường',                             'workflow_status' => 'pending_accounting',     'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'TÁI KÝ'],
            ['customer' => 'CÔNG TY TNHH SẢN XUẤT HÓA CHẤT VIỆT NHẬT',      'shd_bc' => 'TV-2025-007', 'value' => 41000000, 'commission' => 4500000, 'revenue' => 35000000, 'signed_at' => '2025-07-03', 'province' => 'Bình Dương',          'loai_dich_vu' => 'Tư vấn, lập ĐTM, GPMT, DKMT',           'workflow_status' => 'consulting_survey',      'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP DU LỊCH VÀ DỊCH VỤ HỘI AN',          'shd_bc' => 'TV-2025-008', 'value' => 22000000, 'commission' => 2500000, 'revenue' => 18500000, 'signed_at' => '2025-08-25', 'province' => 'Quảng Nam',           'loai_dich_vu' => 'Quan trắc môi trường lao động và phân loại lao động', 'workflow_status' => 'finished',               'status' => 'HOÀN THÀNH',      'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY TNHH SẢN XUẤT ĐỒ GỐM SỨ BÌNH DƯƠNG',   'shd_bc' => 'TV-2025-009', 'value' => 36000000, 'commission' => 4000000, 'revenue' => 30500000, 'signed_at' => '2025-09-11', 'province' => 'Bình Dương',          'loai_dich_vu' => 'Quan trắc môi trường',                             'workflow_status' => 'client_confirmed',       'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'TÁI KÝ'],
            ['customer' => 'CÔNG TY CP SẢN XUẤT CÁT TIÊN SA',                'shd_bc' => 'TV-2025-010', 'value' => 58000000, 'commission' => 7000000, 'revenue' => 49000000, 'signed_at' => '2025-10-30', 'province' => 'Lâm Đồng',           'loai_dich_vu' => 'Tư vấn, lập ĐTM, GPMT, DKMT',           'workflow_status' => 'draft',                  'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
        ];

        foreach ($consultingData as $row) {
            $cus = Customer::firstOrCreate(['name' => $row['customer']], ['name' => $row['customer']]);
            ContractLegal::create([
                'shd_bc'         => $row['shd_bc'],
                'customer_id'    => $cus->id,
                'staff_id'       => $staffKD->id,
                'department_id'  => $deptKD->id,
                'signed_at'      => $row['signed_at'],
                'submitted_at'   => date('Y-m-d', strtotime($row['signed_at'] . ' +5 days')),
                'value'          => $row['value'],
                'commission'     => $row['commission'],
                'revenue'        => $row['revenue'],
                'province'       => $row['province'],
                'loai_dich_vu'   => $row['loai_dich_vu'],
                'info_source'    => $row['info_source'],
                'payment_method' => 'Sau ký',
                'workflow_status' => $row['workflow_status'],
                'status'         => $row['status'],
                'renewal_status' => $row['info_source'] === 'TÁI KÝ' ? 'Đã tái ký' : '',
                'is_offset'      => false,
                'has_room_fund'  => false,
                'is_overdue'     => false,
            ]);
        }

        // ============================================================
        // 3. HỢP ĐỒNG THƯƠNG MẠI (ContractResearch) — 10 bản ghi
        // ============================================================
        $commercialData = [
            ['customer' => 'CÔNG TY CP NGHIÊN CỨU VÀ ỨNG DỤNG MÔI TRƯỜNG XANH',  'shd_bc' => 'TM-2025-001', 'value' => 85000000,  'commission' => 9000000,  'revenue' => 72000000, 'signed_at' => '2025-01-20', 'province' => 'Hà Nội',             'loai_dich_vu' => 'Nghiên cứu khoa học về môi trường',   'status' => 'HOÀN THÀNH',      'info_source' => 'MỚI'],
            ['customer' => 'VIỆN CÔNG NGHỆ MÔI TRƯỜNG VIỆT NAM',                   'shd_bc' => 'TM-2025-002', 'value' => 120000000, 'commission' => 15000000, 'revenue' => 100000000,'signed_at' => '2025-02-08', 'province' => 'Hà Nội',             'loai_dich_vu' => 'Cung cấp giải pháp chuyển đổi công nghệ', 'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY TNHH PHÁT TRIỂN CÔNG NGHỆ ECO-TECH',           'shd_bc' => 'TM-2025-003', 'value' => 65000000,  'commission' => 7500000,  'revenue' => 55000000, 'signed_at' => '2025-03-25', 'province' => 'TP. Hồ Chí Minh',    'loai_dich_vu' => 'Nghiên cứu khoa học về môi trường',   'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'TÁI KÝ'],
            ['customer' => 'TRUNG TÂM NGHIÊN CỨU CHUYỂN ĐỔI VÀ PHÁT TRIỂN',       'shd_bc' => 'TM-2025-004', 'value' => 48000000,  'commission' => 5500000,  'revenue' => 40500000, 'signed_at' => '2025-04-14', 'province' => 'Đà Nẵng',             'loai_dich_vu' => 'Cung cấp giải pháp chuyển đổi công nghệ', 'status' => 'HOÀN THÀNH',      'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP CÔNG NGHỆ MÔI TRƯỜNG MIỀN TRUNG',           'shd_bc' => 'TM-2025-005', 'value' => 75000000,  'commission' => 8500000,  'revenue' => 63000000, 'signed_at' => '2025-05-30', 'province' => 'Thừa Thiên Huế',      'loai_dich_vu' => 'Nghiên cứu khoa học về môi trường',   'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY TNHH TƯ VẤN CÔNG NGHỆ SẠCH MIỀN NAM',         'shd_bc' => 'TM-2025-006', 'value' => 95000000,  'commission' => 11000000, 'revenue' => 80000000, 'signed_at' => '2025-06-09', 'province' => 'TP. Hồ Chí Minh',    'loai_dich_vu' => 'Cung cấp giải pháp chuyển đổi công nghệ', 'status' => 'HOÀN THÀNH',      'info_source' => 'TÁI KÝ'],
            ['customer' => 'SỞ TÀI NGUYÊN VÀ MÔI TRƯỜNG BÌNH DƯƠNG',               'shd_bc' => 'TM-2025-007', 'value' => 180000000, 'commission' => 0,        'revenue' => 180000000,'signed_at' => '2025-07-17', 'province' => 'Bình Dương',          'loai_dich_vu' => 'Nghiên cứu khoa học về môi trường',   'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP PHÁT TRIỂN HẠ TẦNG ECO PARK',               'shd_bc' => 'TM-2025-008', 'value' => 55000000,  'commission' => 6000000,  'revenue' => 46500000, 'signed_at' => '2025-08-04', 'province' => 'Đồng Nai',            'loai_dich_vu' => 'Cung cấp giải pháp chuyển đổi công nghệ', 'status' => 'ĐÃ HỦY',          'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY TNHH ĐẦU TƯ VÀ PHÁT TRIỂN XANH',              'shd_bc' => 'TM-2025-009', 'value' => 72000000,  'commission' => 8000000,  'revenue' => 61000000, 'signed_at' => '2025-09-18', 'province' => 'Hà Nội',             'loai_dich_vu' => 'Nghiên cứu khoa học về môi trường',   'status' => 'HOÀN THÀNH',      'info_source' => 'TÁI KÝ'],
            ['customer' => 'CÔNG TY CP DỊCH VỤ KHOA HỌC MÔI TRƯỜNG TOÀN CẦU',    'shd_bc' => 'TM-2025-010', 'value' => 43000000,  'commission' => 5000000,  'revenue' => 36500000, 'signed_at' => '2025-11-02', 'province' => 'Cần Thơ',            'loai_dich_vu' => 'Cung cấp giải pháp chuyển đổi công nghệ', 'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
        ];

        foreach ($commercialData as $row) {
            $cus = Customer::firstOrCreate(['name' => $row['customer']], ['name' => $row['customer']]);
            ContractResearch::create([
                'shd_bc'         => $row['shd_bc'],
                'customer_id'    => $cus->id,
                'staff_id'       => $staffValue = ($row['info_source'] === 'TÁI KÝ' ? $staffTP->id : $staffKD->id),
                'department_id'  => $deptKD->id,
                'signed_at'      => $row['signed_at'],
                'submitted_at'   => date('Y-m-d', strtotime($row['signed_at'] . ' +7 days')),
                'value'          => $row['value'],
                'commission'     => $row['commission'],
                'revenue'        => $row['revenue'],
                'province'       => $row['province'],
                'loai_dich_vu'   => $row['loai_dich_vu'],
                'info_source'    => $row['info_source'],
                'payment_method' => 'Sau ký',
                'status'         => $row['status'],
                'renewal_status' => '',
                'is_offset'      => false,
                'has_room_fund'  => false,
                'is_overdue'     => false,
            ]);
        }

        // ============================================================
        // 4. HỢP ĐỒNG DỰ ÁN (ContractTechnical) — 10 bản ghi
        // ============================================================
        $projectData = [
            ['customer' => 'CÔNG TY TNHH SẢN XUẤT GIẤY KBP LONG AN',              'shd_bc' => 'DA-2025-001', 'value' => 350000000,  'commission' => 0,         'revenue' => 350000000, 'signed_at' => '2025-02-01', 'province' => 'Long An',        'loai_dich_vu' => 'Tư vấn, thiết kế và thi công hệ thống xử lý khí thải, nước thải', 'status' => 'HOÀN THÀNH',      'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP CƠ ĐIỆN LẠNH REE',                          'shd_bc' => 'DA-2025-002', 'value' => 580000000,  'commission' => 0,         'revenue' => 580000000, 'signed_at' => '2025-02-20', 'province' => 'TP. Hồ Chí Minh','loai_dich_vu' => 'Tư vấn, thiết kế và thi công hệ thống quan trắc tự động',         'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'TÁI KÝ'],
            ['customer' => 'CÔNG TY TNHH LỌC HÓA DẦU BÌNH SƠN',                   'shd_bc' => 'DA-2025-003', 'value' => 1200000000, 'commission' => 50000000,  'revenue' => 1100000000,'signed_at' => '2025-03-10', 'province' => 'Quảng Ngãi',     'loai_dich_vu' => 'Ứng phó sự cố hóa chất, tràn dầu',                                'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'TỔNG CÔNG TY ĐIỆN LỰC MIỀN NAM',                       'shd_bc' => 'DA-2025-004', 'value' => 850000000,  'commission' => 0,         'revenue' => 850000000, 'signed_at' => '2025-04-05', 'province' => 'TP. Hồ Chí Minh','loai_dich_vu' => 'Tư vấn, thiết kế và thi công hệ thống quan trắc tự động',         'status' => 'HOÀN THÀNH',      'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP CẢNG SIHANOUKVILLE',                         'shd_bc' => 'DA-2025-005', 'value' => 480000000,  'commission' => 30000000,  'revenue' => 440000000, 'signed_at' => '2025-05-12', 'province' => 'TP. Hồ Chí Minh','loai_dich_vu' => 'Ứng phó sự cố hóa chất, tràn dầu',                                'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'TÁI KÝ'],
            ['customer' => 'CÔNG TY CP MÔI TRƯỜNG ĐÔ THỊ BÌNH DƯƠNG',              'shd_bc' => 'DA-2025-006', 'value' => 680000000,  'commission' => 0,         'revenue' => 680000000, 'signed_at' => '2025-06-25', 'province' => 'Bình Dương',     'loai_dich_vu' => 'Tư vấn, thiết kế và thi công hệ thống xử lý khí thải, nước thải', 'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY TNHH ĐIỆN TỬ LG VIỆT NAM',                     'shd_bc' => 'DA-2025-007', 'value' => 920000000,  'commission' => 80000000,  'revenue' => 820000000, 'signed_at' => '2025-07-08', 'province' => 'Hải Phòng',      'loai_dich_vu' => 'Tư vấn, thiết kế và thi công hệ thống quan trắc tự động',         'status' => 'HOÀN THÀNH',      'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP VIGLACERA',                                  'shd_bc' => 'DA-2025-008', 'value' => 420000000,  'commission' => 0,         'revenue' => 420000000, 'signed_at' => '2025-08-19', 'province' => 'Hà Nội',        'loai_dich_vu' => 'Tư vấn, thiết kế và thi công hệ thống xử lý khí thải, nước thải', 'status' => 'ĐÃ HỦY',          'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY TNHH ĐIỆN NĂNG LƯỢNG SOLAR MAX',               'shd_bc' => 'DA-2025-009', 'value' => 760000000,  'commission' => 60000000,  'revenue' => 680000000, 'signed_at' => '2025-09-03', 'province' => 'Bình Thuận',     'loai_dich_vu' => 'Tư vấn, thiết kế và thi công hệ thống xử lý khí thải, nước thải', 'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'TÁI KÝ'],
            ['customer' => 'BAN QUẢN LÝ DỰ ÁN KCN HIỆP PHƯỚC MỞ RỘNG',            'shd_bc' => 'DA-2025-010', 'value' => 1500000000, 'commission' => 100000000, 'revenue' => 1350000000,'signed_at' => '2025-10-01', 'province' => 'TP. Hồ Chí Minh','loai_dich_vu' => 'Tư vấn, thiết kế và thi công hệ thống quan trắc tự động',         'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
        ];

        foreach ($projectData as $row) {
            $cus = Customer::firstOrCreate(['name' => $row['customer']], ['name' => $row['customer']]);
            ContractTechnical::create([
                'shd_bc'         => $row['shd_bc'],
                'customer_id'    => $cus->id,
                'staff_id'       => $staffKD->id,
                'department_id'  => $deptKT->id,
                'signed_at'      => $row['signed_at'],
                'submitted_at'   => date('Y-m-d', strtotime($row['signed_at'] . ' +10 days')),
                'value'          => $row['value'],
                'commission'     => $row['commission'],
                'revenue'        => $row['revenue'],
                'province'       => $row['province'],
                'loai_dich_vu'   => $row['loai_dich_vu'],
                'info_source'    => $row['info_source'],
                'payment_method' => 'Sau ký',
                'status'         => $row['status'],
                'renewal_status' => '',
                'is_offset'      => false,
                'has_room_fund'  => false,
                'is_overdue'     => false,
            ]);
        }

        // ============================================================
        // 5. HỢP ĐỒNG PHÁT TRIỂN BỀN VỮNG (ContractSustainability) — 10 bản ghi
        // ============================================================
        $sustainData = [
            ['customer' => 'CÔNG TY CP SỢI THẾ KỶ',                     'shd_bc' => 'PTBV-2025-001', 'value' => 135000000, 'commission' => 15000000, 'revenue' => 115000000, 'signed_at' => '2025-01-25', 'province' => 'TP. Hồ Chí Minh', 'loai_dich_vu' => 'Tư vấn, lập báo cáo ESG',             'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP THỦY SẢN MINH PHÚ',              'shd_bc' => 'PTBV-2025-002', 'value' => 85000000,  'commission' => 0,        'revenue' => 85000000,  'signed_at' => '2025-02-18', 'province' => 'Cà Mau',          'loai_dich_vu' => 'Lập báo cáo CBAM',                    'status' => 'HOÀN THÀNH',      'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP CẢNG CONTAINER QUỐC TẾ VIỆT NAM', 'shd_bc' => 'PTBV-2025-003', 'value' => 160000000, 'commission' => 18000000, 'revenue' => 135000000, 'signed_at' => '2025-03-07', 'province' => 'Bà Rịa - Vũng Tàu','loai_dich_vu' => 'Tư vấn tiêu chí cảng xanh',           'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'TÁI KÝ'],
            ['customer' => 'CÔNG TY CP VINAMIT',                         'shd_bc' => 'PTBV-2025-004', 'value' => 72000000,  'commission' => 8000000,  'revenue' => 61000000,  'signed_at' => '2025-04-16', 'province' => 'Bình Dương',       'loai_dich_vu' => 'Đánh giá vòng đời sản phẩm (ISO 14067)', 'status' => 'HOÀN THÀNH',      'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY TNHH XUẤT NHẬP KHẨU TRÁI CÂY MEKONG','shd_bc' => 'PTBV-2025-005', 'value' => 48000000,  'commission' => 5500000,  'revenue' => 40500000,  'signed_at' => '2025-05-28', 'province' => 'Tiền Giang',       'loai_dich_vu' => 'Tín chỉ Carbon',                       'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP ĐẦU TƯ HẠ TẦNG KỸ THUẬT TP.HCM', 'shd_bc' => 'PTBV-2025-006', 'value' => 220000000, 'commission' => 25000000, 'revenue' => 188000000, 'signed_at' => '2025-06-12', 'province' => 'TP. Hồ Chí Minh', 'loai_dich_vu' => 'Tư vấn, lập báo cáo ESG',             'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY TNHH CẢNG QUỐC TẾ SP-SSA',          'shd_bc' => 'PTBV-2025-007', 'value' => 145000000, 'commission' => 16000000, 'revenue' => 123000000, 'signed_at' => '2025-07-21', 'province' => 'Bà Rịa - Vũng Tàu','loai_dich_vu' => 'Tư vấn tiêu chí cảng xanh',           'status' => 'HOÀN THÀNH',      'info_source' => 'TÁI KÝ'],
            ['customer' => 'CÔNG TY CP NHỰA BÌNH MINH',                  'shd_bc' => 'PTBV-2025-008', 'value' => 95000000,  'commission' => 10000000, 'revenue' => 80500000,  'signed_at' => '2025-08-09', 'province' => 'TP. Hồ Chí Minh', 'loai_dich_vu' => 'Đánh giá vòng đời sản phẩm (ISO 14067)', 'status' => 'ĐÃ HỦY',          'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP THÉP NAM KIM',                    'shd_bc' => 'PTBV-2025-009', 'value' => 180000000, 'commission' => 20000000, 'revenue' => 153000000, 'signed_at' => '2025-09-14', 'province' => 'Bình Dương',       'loai_dich_vu' => 'Lập báo cáo CBAM',                    'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP PHÂN BÓN BÌNH ĐIỀN',              'shd_bc' => 'PTBV-2025-010', 'value' => 62000000,  'commission' => 7000000,  'revenue' => 52500000,  'signed_at' => '2025-10-08', 'province' => 'TP. Hồ Chí Minh', 'loai_dich_vu' => 'Tín chỉ Carbon',                       'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'TÁI KÝ'],
        ];

        foreach ($sustainData as $row) {
            $cus = Customer::firstOrCreate(['name' => $row['customer']], ['name' => $row['customer']]);
            ContractSustainability::create([
                'shd_bc'         => $row['shd_bc'],
                'customer_id'    => $cus->id,
                'staff_id'       => $staffTV->id,
                'department_id'  => $deptTV->id,
                'signed_at'      => $row['signed_at'],
                'submitted_at'   => date('Y-m-d', strtotime($row['signed_at'] . ' +7 days')),
                'value'          => $row['value'],
                'commission'     => $row['commission'],
                'revenue'        => $row['revenue'],
                'province'       => $row['province'],
                'loai_dich_vu'   => $row['loai_dich_vu'],
                'info_source'    => $row['info_source'],
                'payment_method' => 'Sau ký',
                'status'         => $row['status'],
                'renewal_status' => '',
                'is_offset'      => false,
                'has_room_fund'  => false,
                'is_overdue'     => false,
            ]);
        }

        // ============================================================
        // 6. HỢP ĐỒNG NĂNG LƯỢNG (ContractEmission) — 10 bản ghi
        // ============================================================
        $energyData = [
            ['customer' => 'CÔNG TY TNHH ERICSSON VIỆT NAM',                   'shd_bc' => 'NL-2025-001', 'value' => 95000000,  'commission' => 10000000, 'revenue' => 80500000,  'signed_at' => '2025-01-15', 'province' => 'Hà Nội',             'loai_dich_vu' => 'Kiểm kê khí nhà kính NĐ 06/2022 và ISO 14064-1',                                              'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP NHIỆT ĐIỆN PHẢ LẠI',                   'shd_bc' => 'NL-2025-002', 'value' => 280000000, 'commission' => 0,        'revenue' => 280000000, 'signed_at' => '2025-02-10', 'province' => 'Hải Dương',           'loai_dich_vu' => 'Kiểm toán năng lượng, giải pháp tiết kiệm năng lượng, và giảm phát thải khí nhà kính', 'status' => 'HOÀN THÀNH',      'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP SỮA VIỆT NAM VINAMILK',                 'shd_bc' => 'NL-2025-003', 'value' => 165000000, 'commission' => 18000000, 'revenue' => 140000000, 'signed_at' => '2025-03-20', 'province' => 'TP. Hồ Chí Minh',    'loai_dich_vu' => 'Kiểm kê khí nhà kính NĐ 06/2022 và ISO 14064-1',                                              'status' => 'HOÀN THÀNH',      'info_source' => 'TÁI KÝ'],
            ['customer' => 'CÔNG TY TNHH HEINEKEN VIỆT NAM',                   'shd_bc' => 'NL-2025-004', 'value' => 195000000, 'commission' => 22000000, 'revenue' => 165000000, 'signed_at' => '2025-04-03', 'province' => 'TP. Hồ Chí Minh',    'loai_dich_vu' => 'Kiểm toán năng lượng, giải pháp tiết kiệm năng lượng, và giảm phát thải khí nhà kính', 'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP TẬP ĐOÀN THACO',                        'shd_bc' => 'NL-2025-005', 'value' => 420000000, 'commission' => 0,        'revenue' => 420000000, 'signed_at' => '2025-05-15', 'province' => 'Quảng Nam',           'loai_dich_vu' => 'Kiểm kê khí nhà kính NĐ 06/2022 và ISO 14064-1',                                              'status' => 'HOÀN THÀNH',      'info_source' => 'MỚI'],
            ['customer' => 'TỔNG CÔNG TY XI MĂNG VIỆT NAM (VICEM)',             'shd_bc' => 'NL-2025-006', 'value' => 550000000, 'commission' => 0,        'revenue' => 550000000, 'signed_at' => '2025-06-02', 'province' => 'Hà Nội',             'loai_dich_vu' => 'Kiểm toán năng lượng, giải pháp tiết kiệm năng lượng, và giảm phát thải khí nhà kính', 'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'TÁI KÝ'],
            ['customer' => 'CÔNG TY TNHH SẢN XUẤT ĐIỆN MẶT TRỜI SUNPOWER',     'shd_bc' => 'NL-2025-007', 'value' => 380000000, 'commission' => 40000000, 'revenue' => 325000000, 'signed_at' => '2025-07-25', 'province' => 'Bình Thuận',          'loai_dich_vu' => 'Tư vấn, thiết kế hệ thống điện mặt trời',                                                    'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP ĐIỆN MẶT TRỜI PHƯỚC AN',                'shd_bc' => 'NL-2025-008', 'value' => 620000000, 'commission' => 65000000, 'revenue' => 540000000, 'signed_at' => '2025-08-14', 'province' => 'Đồng Nai',            'loai_dich_vu' => 'Tư vấn, thiết kế hệ thống điện mặt trời',                                                    'status' => 'HOÀN THÀNH',      'info_source' => 'TÁI KÝ'],
            ['customer' => 'CÔNG TY TNHH CÔNG NGHIỆP NẶNG DOOSAN VINA',        'shd_bc' => 'NL-2025-009', 'value' => 235000000, 'commission' => 25000000, 'revenue' => 200000000, 'signed_at' => '2025-09-05', 'province' => 'Quảng Ngãi',          'loai_dich_vu' => 'Kiểm kê khí nhà kính NĐ 06/2022 và ISO 14064-1',                                              'status' => 'ĐANG THỰC HIỆN', 'info_source' => 'MỚI'],
            ['customer' => 'CÔNG TY CP TÔN HOA SEN',                            'shd_bc' => 'NL-2025-010', 'value' => 175000000, 'commission' => 20000000, 'revenue' => 148000000, 'signed_at' => '2025-10-22', 'province' => 'Bình Dương',          'loai_dich_vu' => 'Kiểm toán năng lượng, giải pháp tiết kiệm năng lượng, và giảm phát thải khí nhà kính', 'status' => 'ĐÃ HỦY',          'info_source' => 'MỚI'],
        ];

        foreach ($energyData as $row) {
            $cus = Customer::firstOrCreate(['name' => $row['customer']], ['name' => $row['customer']]);
            ContractEmission::create([
                'shd_bc'         => $row['shd_bc'],
                'customer_id'    => $cus->id,
                'staff_id'       => $staffTV->id,
                'department_id'  => $deptTV->id,
                'signed_at'      => $row['signed_at'],
                'submitted_at'   => date('Y-m-d', strtotime($row['signed_at'] . ' +7 days')),
                'value'          => $row['value'],
                'commission'     => $row['commission'],
                'revenue'        => $row['revenue'],
                'province'       => $row['province'],
                'loai_dich_vu'   => $row['loai_dich_vu'],
                'info_source'    => $row['info_source'],
                'payment_method' => 'Sau ký',
                'status'         => $row['status'],
                'renewal_status' => '',
                'is_offset'      => false,
                'has_room_fund'  => false,
                'is_overdue'     => false,
            ]);
        }
    }
}
