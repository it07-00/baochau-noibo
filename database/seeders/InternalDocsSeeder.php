<?php

namespace Database\Seeders;

use App\Models\InternalDoc;
use Illuminate\Database\Seeder;

class InternalDocsSeeder extends Seeder
{
    public function run(): void
    {
        $docs = [
            [
                'title' => 'ĐỀ XUẤT NÂNG CẤP SERVER',
                'files' => [
                    ['name' => 'quy-trinh-de-xuat-nang-cap-server-8685.pdf', 'url' => '#'],
                    ['name' => 'phieu-de-xuat-nang-cap-server-9091.docx', 'url' => '#'],
                ]
            ],
            [
                'title' => 'QUY TRÌNH THAM VẤN BÁO GIÁ DỰ ÁN / MẪU PHIẾU KHẢO SÁT PHÂN LOẠI CẤP ĐỘ DỰ ÁN',
                'files' => [
                    ['name' => 'quy-trinh-tham-van-bao-gia-du-an-dieu-chinh-2169.pdf', 'url' => '#'],
                    ['name' => 'phieu-khao-sat-va-bb-khao-sat-cong-trinh-7908.docx', 'url' => '#'],
                    ['name' => 'phieu-khao-sat-va-bb-khao-sat-cong-trinh-khi-thai-3857.docx', 'url' => '#'],
                    ['name' => 'phan-loai-cap-do-bao-gia-du-an-7358.xlsx', 'url' => '#'],
                ]
            ],
            [
                'title' => 'Mẫu BIÊN BẢN NGHIỆM THU/THANH LÝ/XÁC NHẬN ĐỊA CHỈ XUẤT HÓA ĐƠN (CHẤT THẢI)',
                'files' => [
                    ['name' => 'bien-ban-xac-nhan-dia-chi-2621.doc', 'url' => '#'],
                    ['name' => 'bien-ban-thanh-ly-8343.doc', 'url' => '#'],
                    ['name' => 'bien-ban-thanh-ly-khong-su-dung-dich-vu-1091.docx', 'url' => '#'],
                    ['name' => 'bien-ban-nghiem-thu-1927.docx', 'url' => '#'],
                    ['name' => 'bbnttl-6704.doc', 'url' => '#'],
                ]
            ],
            [
                'title' => 'Mẫu BIÊN BẢN TIÊU HỦY/BÁO CÁO HỦY HÀNG (CHẤT THẢI)',
                'files' => [
                    ['name' => 'mau-bbxn-tieu-huy-hang-hoa-tdx.docx', 'url' => '#'],
                    ['name' => 'mau-thong-bao-hoan-thanh-xu-ly.docx', 'url' => '#'],
                ]
            ],
            [
                'title' => 'Mẫu PHỤ LỤC HỢP ĐỒNG CHẤT THẢI',
                'files' => [
                    ['name' => 'phu-luc-tai-ky-6451.docx', 'url' => '#'],
                    ['name' => 'phu-luc-gia-han-8954.doc', 'url' => '#'],
                    ['name' => 'phu-luc-dieu-chinh-5775.docx', 'url' => '#'],
                ]
            ],
        ];

        foreach ($docs as $doc) {
            InternalDoc::create($doc);
        }
    }
}
