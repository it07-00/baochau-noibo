<?php

namespace Database\Seeders;

use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Handler;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;

class ContractWasteSeeder extends Seeder
{
    public function run(): void
    {
        $handler = Handler::create([
            'name' => 'Công ty CP Công Nghệ Môi Trường Trái Đất Xanh',
            'phone' => '028.38123456',
            'address' => 'Số 123, Đường ABC, Quận XYZ, TP.HCM'
        ]);

        $dept = Department::where('slug', 'kinh-doanh')->first();
        if (!$dept) {
            $dept = Department::create(['name' => 'Phòng Kinh doanh', 'slug' => 'kinh-doanh', 'is_active' => true]);
        }

        $staff = User::where('username', 'kinhdoanh')->first();
        if (!$staff) {
            $staff = User::create([
                'name' => 'HỒ THỊ THANH THẢO',
                'username' => 'kinhdoanh',
                'password' => bcrypt('password'),
                'department_id' => $dept->id,
            ]);
        }

        // 1. HOÀN LỘC
        $cus1 = Customer::create([
            'name' => 'CÔNG TY CỔ PHẦN THƯƠNG MẠI DỊCH VỤ HOÀN LỘC - HOANLOC',
            'representative' => 'Chị Diễm',
            'phone' => '0983387388',
            'email' => 'ketoan.dkvanthinh@gmail.com',
            'address' => '444 Ấp 1, Phường Long An, Tỉnh Tây Ninh, Việt Nam',
        ]);

        ContractWaste::create([
            'shd_cxl' => 'HOÀN LỘC',
            'shd_bc' => 'HOÀN LỘC',
            'customer_id' => $cus1->id,
            'handler_id' => $handler->id,
            'staff_id' => $staff->id,
            'department_id' => $dept->id,
            'content' => 'KL: 100 KG/NĂM - TS: 01 LẦN/NĂM',
            'value' => 7500000,
            'commission' => 833000,
            'revenue' => 6500400,
            'payment_method' => 'Sau ký',
            'source' => 'TÁI KÝ',
            'is_renewal' => true,
            'signed_at' => '2026-03-19',
            'effective_at' => '2026-03-19',
            'end_at' => '2027-03-19',
            'submitted_at' => '2026-03-19',
            'billing_address' => '187 Nguyễn Văn Luông, Phường Bình Phú, Thành phố Hồ Chí Minh, Việt Nam',
            'execution_address' => '444 Ấp 1, Phường Long An, Tỉnh Tây Ninh, Việt Nam',
            'mailing_address' => '1099B Hậu Giang, Phường Bình Phú, Thành phố Hồ Chí Minh',
            'status' => 'Đã trình ký Chủ xử lý',
            'renewal_status' => 'Chưa tái ký',
            'waste_type' => 'CTNH & CTCN',
            'service_type' => 'Chất thải',
            'voucher_status' => 'Chưa chọn',
            'note' => 'Đồng ý gom kết hợp',
        ]);

        // 2. DUY KHA
        $cus2 = Customer::create([
            'name' => 'CÔNG TY TNHH CƠ KHÍ - XÂY LẮP VÀ THƯƠNG MẠI DUY KHA - DUY KHA',
            'representative' => 'Anh Kha',
            'phone' => '0706861999',
            'email' => 'duykhaeo@gmail.com',
            'address' => 'Chi Nhánh Công Ty TNHH Cơ Khí - Xây Lắp Và Thương Mại Duy Kha - Nhà Máy Cơ Khí Duy Kha - Địa chỉ: Lô số 31a, Khu B, đường D4, KCN An Hạ, Xã Tân Vinh Lộc, TP Hồ Chí Minh',
        ]);

        ContractWaste::create([
            'shd_cxl' => 'DUY KHA',
            'customer_id' => $cus2->id,
            'handler_id' => $handler->id,
            'staff_id' => $staff->id,
            'department_id' => $dept->id,
            'value' => 5000000,
            'revenue' => 5000000,
            'signed_at' => '2026-03-12',
            'submitted_at' => '2026-03-18',
            'status' => 'Đã gửi khách hàng',
            'renewal_status' => 'Đã tái ký',
            'waste_type' => 'CTCN',
            'service_type' => 'Chất thải',
            'voucher_status' => 'Chưa chọn',
        ]);

        // 3. THANH BÌNH ĐỒNG THÁP
        $cus3 = Customer::create([
            'name' => 'CÔNG TY TNHH MỘT THÀNH VIÊN THANH BÌNH ĐỒNG THÁP - THANH BÌNH ĐỒNG THÁP',
            'representative' => 'Chị Hằng',
            'phone' => '0944120148',
            'email' => 'hang.nguyenl@vinhhoan.com',
            'address' => 'Nhà máy chế biến thủy sản Thanh Bình Đồng Tháp - Lô số 1, Đường số 2, Cụm công nghiệp Bình Thành, Xã Bình Thành, Đồng Tháp',
        ]);

        ContractWaste::create([
            'shd_cxl' => 'THANH BÌNH ĐỒNG THÁP',
            'customer_id' => $cus3->id,
            'handler_id' => $handler->id,
            'staff_id' => $staff->id,
            'department_id' => $dept->id,
            'value' => 31300000,
            'commission' => 4000000,
            'revenue' => 26500000,
            'signed_at' => '2026-03-17',
            'submitted_at' => '2026-03-17',
            'status' => 'Đã hoàn thành KH ký trước',
            'renewal_status' => 'Rớt tái ký',
            'waste_type' => 'CTNH',
            'service_type' => 'Chất thải',
            'voucher_status' => 'Chưa chọn',
        ]);

        // 4. THANH BÌNH ĐỒNG THÁP (Xí nghiệp)
        ContractWaste::create([
            'shd_cxl' => 'THANH BÌNH ĐỒNG THÁP (Xí nghiệp)',
            'customer_id' => $cus3->id,
            'handler_id' => $handler->id,
            'staff_id' => $staff->id,
            'department_id' => $dept->id,
            'value' => 29800000,
            'commission' => 4000000,
            'revenue' => 25000000,
            'signed_at' => '2026-03-17',
            'submitted_at' => '2026-03-17',
            'status' => 'Hợp đồng hủy',
            'renewal_status' => 'Không tái ký',
            'waste_type' => 'CTNH & CTCN',
            'service_type' => 'Hủy hàng',
            'voucher_status' => 'Chưa chọn',
        ]);
    }
}
