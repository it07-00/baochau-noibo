<?php

namespace Database\Seeders;

use App\Models\QuotationDocument;
use App\Models\QuotationDocumentItem;
use App\Models\User;
use App\Support\Quotations\QuotationTemplateCatalog;
use Illuminate\Database\Seeder;

class QuotationDocumentSampleSeeder extends Seeder
{
    private const DEFAULT_TERMS = "Kết quả thực hiện: Báo cáo Quan trắc môi trường lao động\n"
        . "Thời gian có cuốn báo cáo QTMTLĐ: 10-15 ngày kể từ ngày quan trắc và có đầy đủ thông tin khách hàng cung cấp (không tính ngày lễ, thứ 7, chủ nhật);\n"
        . "Chi phí trên đã bao gồm VAT tại thời điểm xuất hóa đơn.\n"
        . "Phương thức thanh toán:\n"
        . "• 50% sau khi ký hợp đồng\n"
        . "• 50% sau khi hoàn thành báo cáo Quan trắc môi trường lao động\n"
        . "Hình thức: chuyển khoản\n"
        . "Chúng tôi xin cam kết sẽ tiến hành và hoàn thành công việc theo đúng nội dung được nêu trong báo giá!";

    public function run(): void
    {
        $staff = User::query()->where('username', 'kinhdoanh')->first()
            ?? User::query()->first();

        if (! $staff) {
            $this->command?->warn('Không có user để gán staff_id cho báo giá mẫu. Hãy seed users trước.');
            return;
        }

        $customers = [
            ['name' => 'CÔNG TY TNHH ABC LOGISTICS', 'address' => 'KCN VSIP, Bình Dương', 'contact' => 'Nguyễn Văn A'],
            ['name' => 'CÔNG TY CỔ PHẦN THỰC PHẨM XANH', 'address' => 'Quận 7, TP.HCM', 'contact' => 'Trần Thị B'],
            ['name' => 'CÔNG TY TNHH CƠ KHÍ MINH PHÁT', 'address' => 'KCN Tân Tạo, TP.HCM', 'contact' => 'Lê Văn C'],
            ['name' => 'CÔNG TY TNHH VẬN TẢI SAO NAM', 'address' => 'Thủ Đức, TP.HCM', 'contact' => 'Phạm Thị D'],
            ['name' => 'CÔNG TY CỔ PHẦN DỆT MAY AN PHÚ', 'address' => 'Thuận An, Bình Dương', 'contact' => 'Võ Văn E'],
            ['name' => 'CÔNG TY TNHH BAO BÌ QUỐC TẾ', 'address' => 'Dĩ An, Bình Dương', 'contact' => 'Đỗ Thị F'],
            ['name' => 'CÔNG TY TNHH DƯỢC PHẨM ĐÔ THÀNH', 'address' => 'Biên Hòa, Đồng Nai', 'contact' => 'Ngô Văn G'],
            ['name' => 'CÔNG TY CỔ PHẦN THÉP VIỆT THÀNH', 'address' => 'KCN Mỹ Phước, Bình Dương', 'contact' => 'Phan Thị H'],
        ];

        $templates = QuotationTemplateCatalog::all();

        foreach ($templates as $index => $template) {
            $customer = $customers[$index % count($customers)];
            $date = now()->subDays((int) ($index * 2));
            $templateKey = (string) $template['key'];
            $serviceType = (string) ($template['service_type'] ?? 'Quan trắc môi trường lao động');
            $vatRate = (int) ($template['vat_rate'] ?? 8);

            $subtotal = 12000000 + ($index * 2500000);
            $discount = 0;
            $vatAmount = (int) round(($subtotal - $discount) * $vatRate / 100);
            $total = $subtotal - $discount + $vatAmount;

            $documentNumber = sprintf('BGM-%s-%s-%02d', now()->format('Y'), strtoupper($templateKey), $index + 1);

            $doc = QuotationDocument::updateOrCreate(
                ['document_number' => $documentNumber],
                [
                    'date' => $date->toDateString(),
                    'valid_until' => $date->copy()->addDays(30)->toDateString(),
                    'staff_id' => $staff->id,
                    'customer_name' => $customer['name'],
                    'customer_address' => $customer['address'],
                    'customer_phone' => '0909' . str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                    'customer_contact' => $customer['contact'],
                    'customer_email' => 'khachhang' . ($index + 1) . '@example.com',
                    'customer_tax_code' => '031' . str_pad((string) (100000 + $index), 6, '0', STR_PAD_LEFT),
                    'service_type' => $serviceType,
                    'template_key' => $templateKey,
                    'work_location' => $customer['address'],
                    'subtotal' => $subtotal,
                    'vat_rate' => $vatRate,
                    'vat_amount' => $vatAmount,
                    'total' => $total,
                    'discount' => $discount,
                    'notes' => 'Báo giá mẫu cho dịch vụ ' . $serviceType,
                    'terms' => self::DEFAULT_TERMS,
                ]
            );

            QuotationDocumentItem::query()->where('quotation_document_id', $doc->id)->delete();

            QuotationDocumentItem::create([
                'quotation_document_id' => $doc->id,
                'item_type' => 'summary',
                'sort_order' => 1,
                'group_name' => null,
                'description' => ($template['summary_description'] ?? 'Thực hiện dịch vụ') . ' cho ' . $customer['name'],
                'unit' => $template['summary_unit'] ?? 'Hồ sơ',
                'quantity' => 1,
                'unit_price' => $subtotal,
                'amount' => $subtotal,
                'note' => '',
            ]);

            $detailGroups = $template['detail_groups'] ?? [];
            $detailPrice = (int) round($subtotal / max(1, min(3, count($detailGroups) ?: 1)));
            $sort = 1;

            foreach (array_slice($detailGroups, 0, 3) as $group) {
                QuotationDocumentItem::create([
                    'quotation_document_id' => $doc->id,
                    'item_type' => 'detail',
                    'sort_order' => $sort++,
                    'group_name' => $group,
                    'description' => 'Hạng mục mẫu - ' . $group,
                    'unit' => 'Mẫu',
                    'quantity' => 1,
                    'unit_price' => $detailPrice,
                    'amount' => $detailPrice,
                    'note' => '1',
                ]);
            }
        }

        $this->command?->info('Đã tạo dữ liệu mẫu tao-bao-gia cho ' . count($templates) . ' loại dịch vụ.');
    }
}
