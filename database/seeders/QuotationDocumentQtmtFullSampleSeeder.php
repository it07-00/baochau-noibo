<?php

namespace Database\Seeders;

use App\Models\Quotation;
use App\Models\QuotationDocument;
use App\Models\QuotationDocumentItem;
use App\Models\QuotationDocumentSection;
use App\Models\QuotationDocumentSectionRow;
use App\Models\User;
use App\Support\Quotations\QuotationTemplateCatalog;
use Illuminate\Database\Seeder;

class QuotationDocumentQtmtFullSampleSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::query()->where('username', 'kinhdoanh')->first()
            ?? User::query()->first();

        if (! $staff) {
            $this->command?->warn('Không có user để gán staff_id cho dữ liệu mẫu QTMT đầy đủ.');
            return;
        }

        $template = QuotationTemplateCatalog::find('qtmt_periodic');
        $serviceType = (string) ($template['service_type'] ?? 'Quan trắc môi trường');
        $vatRate = (int) ($template['vat_rate'] ?? 8);

        // 4 monitoring rounds (Quarterly)
        $summaryItems = [
            [
                'description' => 'Thực hiện Quan trắc môi trường đợt 1',
                'unit' => 'Đợt',
                'quantity' => 1,
                'unit_price' => 17890000,
                'amount' => 17890000,
            ],
            [
                'description' => 'Thực hiện Quan trắc môi trường đợt 2',
                'unit' => 'Đợt',
                'quantity' => 1,
                'unit_price' => 17890000,
                'amount' => 17890000,
            ],
            [
                'description' => 'Thực hiện Quan trắc môi trường đợt 3',
                'unit' => 'Đợt',
                'quantity' => 1,
                'unit_price' => 17890000,
                'amount' => 17890000,
            ],
            [
                'description' => 'Thực hiện Quan trắc môi trường đợt 4',
                'unit' => 'Đợt',
                'quantity' => 1,
                'unit_price' => 17890000,
                'amount' => 17890000,
            ],
        ];

        $subtotal = (int) array_sum(array_column($summaryItems, 'amount'));
        $discount = 1560000;
        $afterDiscount = max(0, $subtotal - $discount);
        $vatAmount = (int) round($afterDiscount * $vatRate / 100);
        $total = $afterDiscount + $vatAmount;

        // Create or update the tracking quotation first
        $quotation = Quotation::updateOrCreate(
            ['quotation_number' => 'QTMT-FULL-001'],
            [
                'date' => now()->toDateString(),
                'staff_id' => $staff->id,
                'source' => 'Tạo báo giá',
                'company_name' => 'CÔNG TY CỔ PHẦN PHÁT TRIỂN CÔNG NGHỆ BẢO PHÁT',
                'address' => 'Đường số 3, KCN Sóng Thần 3, P. Phú Tân, TP. Thủ Dầu Một, Bình Dương',
                'work_address' => 'Khu vực sản xuất và hệ thống xử lý nước thải của nhà máy',
                'service' => $serviceType,
                'contact_person' => 'Nguyễn Văn B - Quản lý Môi trường',
                'work_description' => 'Thực hiện Quan trắc môi trường đợt 1; Thực hiện Quan trắc môi trường đợt 2; Thực hiện Quan trắc môi trường đợt 3; Thực hiện Quan trắc môi trường đợt 4',
                'status' => 'Đang theo dõi',
                'original_value' => $afterDiscount,
                'value_inc_vat' => $afterDiscount,
                'commission_value' => 0,
                'commission_tax' => 0,
                'total_value' => $total,
                'notes' => "Chuyển từ báo giá Word/PDF: QTMT-FULL-001\nMẫu đầy đủ dữ liệu Quan trắc môi trường định kỳ để kiểm tra giao diện, xuất PDF và xuất DOCX.",
            ]
        );

        $doc = QuotationDocument::updateOrCreate(
            ['document_number' => 'QTMT-FULL-001'],
            [
                'quotation_id' => $quotation->id,
                'date' => now()->toDateString(),
                'valid_until' => now()->addDays(30)->toDateString(),
                'staff_id' => $staff->id,
                'customer_name' => 'CÔNG TY CỔ PHẦN PHÁT TRIỂN CÔNG NGHỆ BẢO PHÁT',
                'customer_address' => 'Đường số 3, KCN Sóng Thần 3, P. Phú Tân, TP. Thủ Dầu Một, Bình Dương',
                'customer_phone' => '0274 3666 888',
                'customer_contact' => 'Nguyễn Văn B - Quản lý Môi trường',
                'customer_email' => 'env@baophat.com',
                'customer_tax_code' => '3706543210',
                'service_type' => $serviceType,
                'template_key' => 'qtmt_periodic',
                'price_subcontractor' => 'dai_phu',
                'work_location' => 'Khu vực sản xuất và hệ thống xử lý nước thải của nhà máy',
                'subtotal' => $subtotal,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'total' => $total,
                'discount' => $discount,
                'notes' => 'Mẫu đầy đủ dữ liệu Quan trắc môi trường định kỳ để kiểm tra giao diện, xuất PDF và xuất DOCX.',
                'terms' => QuotationTemplateCatalog::defaultTerms('qtmt_periodic'),
            ]
        );

        QuotationDocumentItem::query()->where('quotation_document_id', $doc->id)->delete();
        QuotationDocumentSection::query()->where('quotation_document_id', $doc->id)->delete();

        // 1. Create Summary Items
        foreach ($summaryItems as $index => $item) {
            QuotationDocumentItem::create([
                'quotation_document_id' => $doc->id,
                'item_type' => 'summary',
                'sort_order' => $index + 1,
                'group_name' => null,
                'description' => $item['description'],
                'unit' => $item['unit'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['amount'],
                'note' => '',
            ]);
        }

        // 2. Create Detail Items
        $detailRows = [
            // Group I
            [
                'group_name' => 'I. MÔI TRƯỜNG KHÍ THẢI',
                'description' => 'Bụi tổng',
                'unit' => 'Mẫu',
                'quantity' => 3,
                'unit_price' => 350000,
                'amount' => 1050000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'I. MÔI TRƯỜNG KHÍ THẢI',
                'description' => 'SO2',
                'unit' => 'Mẫu',
                'quantity' => 3,
                'unit_price' => 150000,
                'amount' => 450000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'I. MÔI TRƯỜNG KHÍ THẢI',
                'description' => 'NOx (tính theo NO2)',
                'unit' => 'Mẫu',
                'quantity' => 3,
                'unit_price' => 150000,
                'amount' => 450000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'I. MÔI TRƯỜNG KHÍ THẢI',
                'description' => 'CO',
                'unit' => 'Mẫu',
                'quantity' => 3,
                'unit_price' => 120000,
                'amount' => 360000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'I. MÔI TRƯỜNG KHÍ THẢI',
                'description' => 'Lưu lượng, nhiệt độ, áp suất ống khói',
                'unit' => 'Điểm',
                'quantity' => 3,
                'unit_price' => 300000,
                'amount' => 900000,
                'note' => 'tần suất: 4',
            ],

            // Group II
            [
                'group_name' => 'II. MÔI TRƯỜNG NƯỚC THẢI',
                'description' => 'pH',
                'unit' => 'Chỉ tiêu',
                'quantity' => 2,
                'unit_price' => 30000,
                'amount' => 60000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'II. MÔI TRƯỜNG NƯỚC THẢI',
                'description' => 'BOD5',
                'unit' => 'Chỉ tiêu',
                'quantity' => 2,
                'unit_price' => 180000,
                'amount' => 360000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'II. MÔI TRƯỜNG NƯỚC THẢI',
                'description' => 'COD',
                'unit' => 'Chỉ tiêu',
                'quantity' => 2,
                'unit_price' => 150000,
                'amount' => 300000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'II. MÔI TRƯỜNG NƯỚC THẢI',
                'description' => 'Tổng chất rắn lơ lửng (TSS)',
                'unit' => 'Chỉ tiêu',
                'quantity' => 2,
                'unit_price' => 120000,
                'amount' => 240000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'II. MÔI TRƯỜNG NƯỚC THẢI',
                'description' => 'Nitơ tổng (N)',
                'unit' => 'Chỉ tiêu',
                'quantity' => 2,
                'unit_price' => 150000,
                'amount' => 300000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'II. MÔI TRƯỜNG NƯỚC THẢI',
                'description' => 'Phốt pho tổng (P)',
                'unit' => 'Chỉ tiêu',
                'quantity' => 2,
                'unit_price' => 150000,
                'amount' => 300000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'II. MÔI TRƯỜNG NƯỚC THẢI',
                'description' => 'Dầu mỡ động thực vật',
                'unit' => 'Chỉ tiêu',
                'quantity' => 2,
                'unit_price' => 200000,
                'amount' => 400000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'II. MÔI TRƯỜNG NƯỚC THẢI',
                'description' => 'Tổng chất rắn hòa tan (TDS)',
                'unit' => 'Chỉ tiêu',
                'quantity' => 2,
                'unit_price' => 120000,
                'amount' => 240000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'II. MÔI TRƯỜNG NƯỚC THẢI',
                'description' => 'Coliforms',
                'unit' => 'Chỉ tiêu',
                'quantity' => 2,
                'unit_price' => 150000,
                'amount' => 300000,
                'note' => 'tần suất: 4',
            ],

            // Group III
            [
                'group_name' => 'III. MÔI TRƯỜNG KHÍ XUNG QUANH',
                'description' => 'Tiếng ồn',
                'unit' => 'Mẫu',
                'quantity' => 4,
                'unit_price' => 50000,
                'amount' => 200000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'III. MÔI TRƯỜNG KHÍ XUNG QUANH',
                'description' => 'Độ rung',
                'unit' => 'Mẫu',
                'quantity' => 4,
                'unit_price' => 140000,
                'amount' => 560000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'III. MÔI TRƯỜNG KHÍ XUNG QUANH',
                'description' => 'Bụi lơ lửng tổng số (TSP)',
                'unit' => 'Chỉ tiêu',
                'quantity' => 4,
                'unit_price' => 150000,
                'amount' => 600000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'III. MÔI TRƯỜNG KHÍ XUNG QUANH',
                'description' => 'SO2',
                'unit' => 'Mẫu',
                'quantity' => 4,
                'unit_price' => 120000,
                'amount' => 480000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'III. MÔI TRƯỜNG KHÍ XUNG QUANH',
                'description' => 'NO2',
                'unit' => 'Mẫu',
                'quantity' => 4,
                'unit_price' => 120000,
                'amount' => 480000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'III. MÔI TRƯỜNG KHÍ XUNG QUANH',
                'description' => 'CO',
                'unit' => 'Mẫu',
                'quantity' => 4,
                'unit_price' => 120000,
                'amount' => 480000,
                'note' => 'tần suất: 4',
            ],
            [
                'group_name' => 'III. MÔI TRƯỜNG KHÍ XUNG QUANH',
                'description' => 'Vi khí hậu (nhiệt độ, độ ẩm, tốc độ gió, hướng gió)',
                'unit' => 'Mẫu',
                'quantity' => 4,
                'unit_price' => 120000,
                'amount' => 480000,
                'note' => 'tần suất: 4',
            ],

            // Group IV
            [
                'group_name' => 'IV. CHI PHÍ KHÁC',
                'description' => 'Chi phí lập báo cáo công tác bảo vệ môi trường',
                'unit' => 'Báo cáo',
                'quantity' => 1,
                'unit_price' => 8000000,
                'amount' => 8000000,
                'note' => '1',
            ],
            [
                'group_name' => 'IV. CHI PHÍ KHÁC',
                'description' => 'Chi phí khảo sát, đo đạc hiện trường và vận chuyển mẫu',
                'unit' => 'Chuyến',
                'quantity' => 1,
                'unit_price' => 900000,
                'amount' => 900000,
                'note' => '1',
            ],
        ];

        $sort = count($summaryItems) + 1;
        foreach ($detailRows as $row) {
            QuotationDocumentItem::create([
                'quotation_document_id' => $doc->id,
                'item_type' => 'detail',
                'sort_order' => $sort++,
                'group_name' => $row['group_name'],
                'description' => $row['description'],
                'unit' => $row['unit'],
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'amount' => $row['amount'],
                'note' => $row['note'],
            ]);
        }

        // 3. Create summary section
        $summarySection = QuotationDocumentSection::create([
            'quotation_document_id' => $doc->id,
            'section_key' => 'summary',
            'section_type' => 'price_summary',
            'sort_order' => 10,
            'title' => 'Bảng 01. Tổng hợp chi phí thực hiện',
            'columns' => ['stt', 'description', 'unit', 'quantity', 'unit_price', 'amount'],
            'totals' => [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'total' => $total,
            ],
        ]);

        foreach ($summaryItems as $index => $item) {
            $summarySection->rows()->create([
                'sort_order' => $index + 1,
                'row_type' => 'item',
                'description' => $item['description'],
                'unit' => $item['unit'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['amount'],
                'columns' => [
                    'item_type' => 'summary',
                ],
                'note' => '',
            ]);
        }

        // 4. Create detail section
        $detailSection = QuotationDocumentSection::create([
            'quotation_document_id' => $doc->id,
            'section_key' => 'detail',
            'section_type' => 'grouped_detail',
            'sort_order' => 20,
            'title' => 'Bảng 02. Chi tiết thực hiện theo từng kỳ',
            'columns' => ['stt', 'group_name', 'description', 'unit', 'quantity', 'unit_price', 'amount'],
            'totals' => [
                'total' => array_sum(array_column($detailRows, 'amount')),
            ],
        ]);

        foreach ($detailRows as $index => $row) {
            $detailSection->rows()->create([
                'sort_order' => $index + 1,
                'row_type' => 'item',
                'group_name' => $row['group_name'],
                'description' => $row['description'],
                'unit' => $row['unit'],
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'amount' => $row['amount'],
                'columns' => [
                    'item_type' => 'detail',
                ],
                'note' => $row['note'],
            ]);
        }

        $this->command?->info('Đã tạo/cập nhật 1 dữ liệu mẫu QTMT đầy đủ: QTMT-FULL-001');
    }
}
