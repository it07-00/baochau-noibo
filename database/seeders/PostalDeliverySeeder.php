<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\PostalDelivery;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostalDeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dept = Department::where('slug', 'kinh-doanh')->first();
        $admin = User::where('username', 'admin')->first();

        if (!$dept || !$admin) return;

        $samples = [
            [
                'customer_name' => 'CÔNG TY TNHH MỘT THÀNH VIÊN BÊ TÔNG PHAN VŨ LONG AN',
                'customer_phone' => '0812007939',
                'customer_email' => 'thuatdang@gmail.com',
                'address' => 'Đường 830, ấp 4, Xã Thạnh Lợi, Tỉnh Tây Ninh, Việt Nam',
                'sender_name' => 'HỒ THỊ THANH THẢO',
                'bill_viettel' => 'WQNHG2600001852',
                'bill_247' => '',
                'content' => '1 ĐỀ NGHỊ THANH TOÁN',
                'department_id' => $dept->id,
                'user_id' => $admin->id,
                'status' => 'sent',
                'created_at' => '2026-03-23 10:00:00',
            ],
            [
                'customer_name' => 'Chị Thu - CÔNG TY CỔ PHẦN DƯỢC PHẨM BỒ CÔNG ANH',
                'customer_phone' => '0981097208',
                'customer_email' => 'boconganhpharma@gmail.com',
                'address' => '200/12 Thái Phiên, Phường Bình Thới, TP Hồ Chí Minh, Việt Nam',
                'sender_name' => 'HỒ THỊ THANH THẢO',
                'bill_viettel' => '',
                'bill_247' => '',
                'content' => '05 hợp đồng chất thải 05 chứng từ',
                'department_id' => $dept->id,
                'user_id' => $admin->id,
                'status' => 'sent',
                'created_at' => '2026-03-23 11:30:00',
            ],
            [
                'customer_name' => 'Anh Du - CÔNG TY TNHH MÔ TÔ HOÀNG VIỆT',
                'customer_phone' => '0908432420',
                'customer_email' => 'info@moitruonganhduong.vn',
                'address' => '68/14 Đồng Nai, Phường hòa hưng, TP HCM',
                'sender_name' => 'HỒ THỊ THANH THẢO',
                'bill_viettel' => 'WQNHG2600001846',
                'bill_247' => '',
                'content' => 'phụ lục hợp đồng chất thải',
                'department_id' => $dept->id,
                'user_id' => $admin->id,
                'status' => 'sent',
                'created_at' => '2026-03-23 09:15:00',
            ]
        ];

        foreach ($samples as $sample) {
            PostalDelivery::create($sample);
        }
    }
}
