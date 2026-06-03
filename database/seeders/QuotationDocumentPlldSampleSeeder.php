<?php

namespace Database\Seeders;

use App\Models\QuotationDocument;
use App\Models\QuotationDocumentItem;
use App\Models\QuotationDocumentSection;
use App\Models\QuotationDocumentSectionRow;
use App\Models\User;
use App\Support\Quotations\QuotationTemplateCatalog;
use Illuminate\Database\Seeder;

class QuotationDocumentPlldSampleSeeder extends Seeder
{
    private const RECORD_COUNT = 50;

    private const DEFAULT_TERMS = "Kết quả thực hiện: Báo cáo Phân loại lao động\n"
        . "Thời gian thực hiện: 10-15 ngày kể từ ngày khảo sát và nhận đầy đủ hồ sơ;\n"
        . "Chi phí trên đã bao gồm VAT tại thời điểm xuất hóa đơn.\n"
        . "Phương thức thanh toán:\n"
        . "• 50% sau khi ký hợp đồng\n"
        . "• 50% sau khi hoàn thành hồ sơ phân loại lao động\n"
        . "Hình thức: chuyển khoản\n"
        . "Chúng tôi xin cam kết sẽ tiến hành và hoàn thành công việc theo đúng nội dung nêu trong báo giá.";

    public function run(): void
    {
        $staff = User::query()->where('username', 'kinhdoanh')->first()
            ?? User::query()->first();

        if (! $staff) {
            $this->command?->warn('Không có user để gán staff_id cho báo giá mẫu PLLD.');
            return;
        }

        $template = QuotationTemplateCatalog::find('plld');
        $groups = $template['detail_groups'] ?? [];
        $vatRate = (int) ($template['vat_rate'] ?? 0);

        for ($i = 1; $i <= self::RECORD_COUNT; $i++) {
            $date = now()->subDays($i % 28);
            $documentNumber = sprintf('PLLD-SAMPLE-%03d', $i);

            $basePrice = 3500000 + ($i * 125000);
            $subtotal = $basePrice;
            $discount = $i % 10 === 0 ? 250000 : 0;
            $vatAmount = (int) round(($subtotal - $discount) * $vatRate / 100);
            $total = $subtotal - $discount + $vatAmount;

            $doc = QuotationDocument::updateOrCreate(
                ['document_number' => $documentNumber],
                [
                    'date' => $date->toDateString(),
                    'valid_until' => $date->copy()->addDays(30)->toDateString(),
                    'staff_id' => $staff->id,
                    'customer_name' => 'CÔNG TY MẪU PLLĐ ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'customer_address' => 'KCN Mẫu số ' . (($i % 9) + 1) . ', Bình Dương',
                    'customer_phone' => '0909' . str_pad((string) (200000 + $i), 6, '0', STR_PAD_LEFT),
                    'customer_contact' => 'Người liên hệ ' . $i,
                    'customer_email' => 'plld' . $i . '@example.com',
                    'customer_tax_code' => '370' . str_pad((string) (100000 + $i), 6, '0', STR_PAD_LEFT),
                    'service_type' => (string) ($template['service_type'] ?? 'Phân loại lao động'),
                    'template_key' => 'plld',
                    'work_location' => 'Nhà xưởng khu vực ' . (($i % 5) + 1),
                    'subtotal' => $subtotal,
                    'vat_rate' => $vatRate,
                    'vat_amount' => $vatAmount,
                    'total' => $total,
                    'discount' => $discount,
                    'notes' => 'Dữ liệu mẫu Phân loại lao động #' . $i,
                    'terms' => self::DEFAULT_TERMS,
                ]
            );

            QuotationDocumentItem::query()->where('quotation_document_id', $doc->id)->delete();
            QuotationDocumentSection::query()->where('quotation_document_id', $doc->id)->delete();

            QuotationDocumentItem::create([
                'quotation_document_id' => $doc->id,
                'item_type' => 'summary',
                'sort_order' => 1,
                'group_name' => null,
                'description' => ($template['summary_description'] ?? 'Thực hiện Phân loại lao động') . ' cho ' . $doc->customer_name,
                'unit' => $template['summary_unit'] ?? 'Hồ sơ',
                'quantity' => 1,
                'unit_price' => $subtotal,
                'amount' => $subtotal,
                'note' => '',
            ]);

            $detailSort = 1;
            $detailUnitPrice = (int) round($subtotal / max(1, count($groups)));

            foreach ($groups as $groupIndex => $groupName) {
                QuotationDocumentItem::create([
                    'quotation_document_id' => $doc->id,
                    'item_type' => 'detail',
                    'sort_order' => $detailSort++,
                    'group_name' => $groupName,
                    'description' => 'Hạng mục đánh giá ' . ($groupIndex + 1) . ' - Bộ phận ' . (($i % 7) + 1),
                    'unit' => 'Vị trí',
                    'quantity' => 1 + ($i % 3),
                    'unit_price' => $detailUnitPrice,
                    'amount' => $detailUnitPrice,
                    'note' => '1',
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
                    'subtotal' => $subtotal,
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
                'description' => ($template['summary_description'] ?? 'Thực hiện Phân loại lao động') . ' năm ' . $date->format('Y'),
                'unit' => $template['summary_unit'] ?? 'Hồ sơ',
                'quantity' => 1,
                'unit_price' => $subtotal,
                'amount' => $subtotal,
                'columns' => ['item_type' => 'summary'],
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
                    'total' => (int) round($detailUnitPrice * max(1, count($groups))),
                ],
            ]);

            foreach ($groups as $groupIndex => $groupName) {
                QuotationDocumentSectionRow::create([
                    'quotation_document_section_id' => $detailSection->id,
                    'sort_order' => $groupIndex + 1,
                    'row_type' => 'item',
                    'group_name' => $groupName,
                    'description' => 'Chỉ tiêu đánh giá nhóm ' . ($groupIndex + 1) . ' - vị trí ' . (($i % 6) + 1),
                    'unit' => 'Vị trí',
                    'quantity' => 1 + ($i % 3),
                    'unit_price' => $detailUnitPrice,
                    'amount' => $detailUnitPrice,
                    'columns' => ['item_type' => 'detail'],
                    'note' => '',
                ]);
            }

            $matrixSection = QuotationDocumentSection::create([
                'quotation_document_id' => $doc->id,
                'section_key' => 'plld_matrix',
                'section_type' => 'plld_job_matrix',
                'sort_order' => 30,
                'title' => 'Bảng 03. Ma trận chức danh và chỉ tiêu phân loại lao động',
                'columns' => [
                    'job_title', 'employee_count', 'assessment_count', 'microclimate', 'noise', 'dust', 'vocs', 'co2',
                    'reaction_time', 'muscle_load', 'work_characteristics', 'visual_stress', 'posture', 'responsibility', 'total',
                ],
                'totals' => ['total' => 0],
            ]);

            $jobTitles = [
                'CN vận hành dây chuyền',
                'Nhân viên QC',
                'Kỹ thuật bảo trì',
                'Nhân viên kho nguyên liệu',
                'Tổ trưởng sản xuất',
                'Nhân viên đóng gói thành phẩm',
            ];

            $matrixSeedRows = [];
            foreach ($jobTitles as $jobIndex => $jobTitle) {
                $seed = $i + $jobIndex;
                $matrixSeedRows[] = [
                    'job_title' => $jobTitle . ' ' . (($seed % 3) + 1),
                    'employee_count' => 6 + ($seed % 12),
                    'assessment_count' => 3 + ($seed % 6),
                    'microclimate' => 1 + ($seed % 2),
                    'noise' => 1 + (($seed + 1) % 2),
                    'dust' => $seed % 2,
                    'vocs' => ($seed + 1) % 2,
                    'co2' => $seed % 2,
                    'reaction_time' => 1 + (($seed + 2) % 2),
                    'muscle_load' => 1 + (($seed + 3) % 2),
                    'work_characteristics' => 1,
                    'visual_stress' => $seed % 2,
                    'posture' => 1 + (($seed + 4) % 2),
                    'responsibility' => 1,
                ];
            }

            $matrixTotal = 0;
            foreach ($matrixSeedRows as $matrixIndex => $matrixRow) {
                $rowTotal = (int) (
                    $matrixRow['microclimate'] + $matrixRow['noise'] + $matrixRow['dust'] + $matrixRow['vocs'] + $matrixRow['co2']
                    + $matrixRow['reaction_time'] + $matrixRow['muscle_load'] + $matrixRow['work_characteristics']
                    + $matrixRow['visual_stress'] + $matrixRow['posture'] + $matrixRow['responsibility']
                );
                $matrixRow['total'] = $rowTotal;
                $matrixTotal += $rowTotal;

                QuotationDocumentSectionRow::create([
                    'quotation_document_section_id' => $matrixSection->id,
                    'sort_order' => $matrixIndex + 1,
                    'row_type' => 'matrix_row',
                    'description' => $matrixRow['job_title'],
                    'quantity' => $matrixRow['assessment_count'],
                    'amount' => $rowTotal,
                    'columns' => $matrixRow,
                    'note' => '',
                ]);
            }

            $matrixSection->update([
                'totals' => ['total' => $matrixTotal],
            ]);
        }

        $this->command?->info('Đã tạo/cập nhật 50 dữ liệu mẫu Phân loại lao động (template plld).');
    }
}
