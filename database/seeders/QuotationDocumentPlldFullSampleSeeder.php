<?php

namespace Database\Seeders;

use App\Models\QuotationDocument;
use App\Models\QuotationDocumentItem;
use App\Models\QuotationDocumentSection;
use App\Models\QuotationDocumentSectionRow;
use App\Models\User;
use App\Support\Quotations\QuotationTemplateCatalog;
use Illuminate\Database\Seeder;

class QuotationDocumentPlldFullSampleSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::query()->where('username', 'kinhdoanh')->first()
            ?? User::query()->first();

        if (! $staff) {
            $this->command?->warn('Không có user để gán staff_id cho dữ liệu mẫu PLLD đầy đủ.');
            return;
        }

        $template = QuotationTemplateCatalog::find('plld');
        $serviceType = (string) ($template['service_type'] ?? 'Phân loại lao động');
        $vatRate = (int) ($template['vat_rate'] ?? 0);

        $summaryAmount = 18500000;
        $discount = 500000;
        $vatAmount = (int) round(($summaryAmount - $discount) * $vatRate / 100);
        $total = $summaryAmount - $discount + $vatAmount;

        $doc = QuotationDocument::updateOrCreate(
            ['document_number' => 'PLLD-FULL-001'],
            [
                'date' => now()->toDateString(),
                'valid_until' => now()->addDays(30)->toDateString(),
                'staff_id' => $staff->id,
                'customer_name' => 'CÔNG TY TNHH SẢN XUẤT MẪU AN TOÀN LAO ĐỘNG',
                'customer_address' => 'Lô B2-3, KCN VSIP II-A, P. Vĩnh Tân, TP. Tân Uyên, Bình Dương',
                'customer_phone' => '0274 3888 999',
                'customer_contact' => 'Nguyễn Văn A - Trưởng phòng HSE',
                'customer_email' => 'hse@example.com',
                'customer_tax_code' => '3701234567',
                'service_type' => $serviceType,
                'template_key' => 'plld',
                'work_location' => 'Xưởng sản xuất số 1 và số 2',
                'subtotal' => $summaryAmount,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'total' => $total,
                'discount' => $discount,
                'notes' => 'Mẫu đầy đủ dữ liệu PLLD để kiểm tra giao diện, xuất PDF và xuất DOCX.',
                'terms' => "Kết quả thực hiện: Báo cáo Phân loại lao động\n"
                    . "Thời gian thực hiện: 10-15 ngày kể từ ngày khảo sát và nhận đầy đủ hồ sơ.\n"
                    . "Thanh toán: 50% sau khi ký hợp đồng, 50% sau khi hoàn thành báo cáo.\n"
                    . "Báo giá đã bao gồm toàn bộ chi phí triển khai theo phạm vi nêu trong hợp đồng.",
            ]
        );

        QuotationDocumentItem::query()->where('quotation_document_id', $doc->id)->delete();
        QuotationDocumentSection::query()->where('quotation_document_id', $doc->id)->delete();

        QuotationDocumentItem::create([
            'quotation_document_id' => $doc->id,
            'item_type' => 'summary',
            'sort_order' => 1,
            'group_name' => null,
            'description' => 'Thực hiện Phân loại lao động năm ' . now()->format('Y') . ' tại ' . $doc->customer_name,
            'unit' => 'Hồ sơ',
            'quantity' => 1,
            'unit_price' => $summaryAmount,
            'amount' => $summaryAmount,
            'note' => '',
        ]);

        $detailRows = [
            [
                'group_name' => 'I. NHÓM YẾU TỐ ĐÁNH GIÁ VỀ VỆ SINH MÔI TRƯỜNG LAO ĐỘNG',
                'description' => 'Khảo sát vi khí hậu, tiếng ồn, bụi và hơi khí độc tại vị trí làm việc',
                'unit' => 'Vị trí',
                'quantity' => 12,
                'unit_price' => 450000,
                'amount' => 5400000,
            ],
            [
                'group_name' => 'II. NHÓM YẾU TỐ ĐÁNH GIÁ TÁC ĐỘNG VỀ TÂM SINH LÝ LAO ĐỘNG',
                'description' => 'Đánh giá tải trọng lao động, phản xạ thần kinh tâm lý và cường độ lao động',
                'unit' => 'Chức danh',
                'quantity' => 8,
                'unit_price' => 650000,
                'amount' => 5200000,
            ],
            [
                'group_name' => 'III. NHÓM YẾU TỐ ĐÁNH GIÁ VỀ ECGONOMI - TỔ CHỨC LAO ĐỘNG',
                'description' => 'Đánh giá ecgonomi tư thế làm việc, tổ chức ca kíp và mức độ trách nhiệm',
                'unit' => 'Chức danh',
                'quantity' => 10,
                'unit_price' => 790000,
                'amount' => 7900000,
            ],
        ];

        foreach ($detailRows as $index => $row) {
            QuotationDocumentItem::create([
                'quotation_document_id' => $doc->id,
                'item_type' => 'detail',
                'sort_order' => $index + 1,
                'group_name' => $row['group_name'],
                'description' => $row['description'],
                'unit' => $row['unit'],
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'amount' => $row['amount'],
                'note' => '',
            ]);
        }

        $summarySection = QuotationDocumentSection::create([
            'quotation_document_id' => $doc->id,
            'section_key' => 'summary',
            'section_type' => 'price_summary',
            'sort_order' => 10,
            'title' => 'Bảng 01. Tổng hợp dự toán chi phí thực hiện',
            'columns' => ['stt', 'description', 'unit', 'quantity', 'unit_price', 'amount'],
            'totals' => [
                'subtotal' => $summaryAmount,
                'discount' => $discount,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'total' => $total,
            ],
        ]);

        QuotationDocumentSectionRow::create([
            'quotation_document_section_id' => $summarySection->id,
            'sort_order' => 1,
            'row_type' => 'item',
            'description' => 'Thực hiện Phân loại lao động năm ' . now()->format('Y'),
            'unit' => 'Hồ sơ',
            'quantity' => 1,
            'unit_price' => $summaryAmount,
            'amount' => $summaryAmount,
            'columns' => [
                'item_type' => 'summary',
            ],
            'note' => '',
        ]);

        $detailSection = QuotationDocumentSection::create([
            'quotation_document_id' => $doc->id,
            'section_key' => 'detail',
            'section_type' => 'grouped_detail',
            'sort_order' => 20,
            'title' => 'Bảng 02. Tổng hợp chỉ tiêu đánh giá',
            'columns' => ['stt', 'group_name', 'description', 'unit', 'quantity', 'unit_price', 'amount'],
            'totals' => [
                'total' => array_sum(array_column($detailRows, 'amount')),
            ],
        ]);

        foreach ($detailRows as $index => $row) {
            QuotationDocumentSectionRow::create([
                'quotation_document_section_id' => $detailSection->id,
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
                'note' => '',
            ]);
        }

        $matrixColumns = [
            'job_title',
            'employee_count',
            'assessment_count',
            'microclimate',
            'noise',
            'dust',
            'vocs',
            'co2',
            'reaction_time',
            'muscle_load',
            'work_characteristics',
            'visual_stress',
            'posture',
            'responsibility',
            'total',
        ];

        $matrixRows = [
            [
                'job_title' => 'Công nhân vận hành máy ép nhựa',
                'employee_count' => 24,
                'assessment_count' => 12,
                'microclimate' => 1,
                'noise' => 1,
                'dust' => 0,
                'vocs' => 1,
                'co2' => 0,
                'reaction_time' => 1,
                'muscle_load' => 1,
                'work_characteristics' => 1,
                'visual_stress' => 1,
                'posture' => 1,
                'responsibility' => 1,
            ],
            [
                'job_title' => 'Nhân viên kiểm tra chất lượng (QC)',
                'employee_count' => 14,
                'assessment_count' => 8,
                'microclimate' => 1,
                'noise' => 0,
                'dust' => 0,
                'vocs' => 0,
                'co2' => 0,
                'reaction_time' => 1,
                'muscle_load' => 0,
                'work_characteristics' => 1,
                'visual_stress' => 1,
                'posture' => 1,
                'responsibility' => 1,
            ],
            [
                'job_title' => 'Kỹ thuật bảo trì cơ điện',
                'employee_count' => 10,
                'assessment_count' => 6,
                'microclimate' => 1,
                'noise' => 1,
                'dust' => 1,
                'vocs' => 1,
                'co2' => 0,
                'reaction_time' => 1,
                'muscle_load' => 1,
                'work_characteristics' => 1,
                'visual_stress' => 0,
                'posture' => 1,
                'responsibility' => 1,
            ],
            [
                'job_title' => 'Nhân viên kho nguyên liệu',
                'employee_count' => 11,
                'assessment_count' => 7,
                'microclimate' => 1,
                'noise' => 0,
                'dust' => 1,
                'vocs' => 0,
                'co2' => 0,
                'reaction_time' => 1,
                'muscle_load' => 1,
                'work_characteristics' => 1,
                'visual_stress' => 0,
                'posture' => 1,
                'responsibility' => 1,
            ],
            [
                'job_title' => 'Tổ trưởng sản xuất',
                'employee_count' => 9,
                'assessment_count' => 6,
                'microclimate' => 1,
                'noise' => 1,
                'dust' => 0,
                'vocs' => 0,
                'co2' => 0,
                'reaction_time' => 1,
                'muscle_load' => 0,
                'work_characteristics' => 1,
                'visual_stress' => 1,
                'posture' => 1,
                'responsibility' => 1,
            ],
            [
                'job_title' => 'Nhân viên đóng gói thành phẩm',
                'employee_count' => 16,
                'assessment_count' => 10,
                'microclimate' => 1,
                'noise' => 1,
                'dust' => 1,
                'vocs' => 0,
                'co2' => 0,
                'reaction_time' => 1,
                'muscle_load' => 1,
                'work_characteristics' => 1,
                'visual_stress' => 1,
                'posture' => 1,
                'responsibility' => 1,
            ],
        ];

        $matrixSection = QuotationDocumentSection::create([
            'quotation_document_id' => $doc->id,
            'section_key' => 'plld_matrix',
            'section_type' => 'plld_job_matrix',
            'sort_order' => 30,
            'title' => 'Bảng 03. Ma trận chức danh và chỉ tiêu phân loại lao động',
            'columns' => $matrixColumns,
            'totals' => [
                'total' => 0,
            ],
        ]);

        $matrixTotal = 0;

        foreach ($matrixRows as $index => $row) {
            $rowTotal = (int) (
                $row['microclimate'] + $row['noise'] + $row['dust'] + $row['vocs'] + $row['co2']
                + $row['reaction_time'] + $row['muscle_load'] + $row['work_characteristics']
                + $row['visual_stress'] + $row['posture'] + $row['responsibility']
            );
            $row['total'] = $rowTotal;
            $matrixTotal += $rowTotal;

            QuotationDocumentSectionRow::create([
                'quotation_document_section_id' => $matrixSection->id,
                'sort_order' => $index + 1,
                'row_type' => 'matrix_row',
                'description' => $row['job_title'],
                'quantity' => $row['assessment_count'],
                'amount' => $rowTotal,
                'columns' => $row,
                'note' => '',
            ]);
        }

        $matrixSection->update([
            'totals' => [
                'total' => $matrixTotal,
            ],
        ]);

        $this->command?->info('Đã tạo/cập nhật 1 dữ liệu mẫu PLLD đầy đủ: PLLD-FULL-001');
    }
}
