<?php

namespace App\Support\Quotations;

final class QuotationTemplateCatalog
{
    public const DEFAULT_KEY = 'qtmtld';

    private const TEMPLATES = [
        'qtmtld' => [
            'key' => 'qtmtld',
            'label' => 'Quan trắc môi trường lao động',
            'service_type' => 'Quan trắc môi trường lao động',
            'template_path' => 'templates/quotations/qtmtld.docx',
            'orientation' => 'portrait',
            'vat_rate' => 8,
            'summary_description' => 'Thực hiện Quan trắc môi trường lao động',
            'summary_unit' => 'Hồ sơ',
            'price_catalog' => 'labor_monitoring_2026',
            'detail_groups' => [
                'I. YẾU TỐ VI KHÍ HẬU',
                'II. YẾU TỐ VẬT LÝ',
                'III. YẾU TỐ TIẾP XÚC',
                'IV. YẾU TỐ TÂM SINH LÝ VÀ ECGONOMI',
                'V. YẾU TỐ BỤI CÁC LOẠI',
                'VI. YẾU TỐ HÓA HỌC',
                'VII. CHI PHÍ KHÁC',
            ],
        ],
        'qtmt_periodic' => [
            'key' => 'qtmt_periodic',
            'label' => 'Quan trắc môi trường',
            'service_type' => 'Quan trắc môi trường',
            'template_path' => 'templates/quotations/qtmt-periodic.docx',
            'orientation' => 'portrait',
            'vat_rate' => 8,
            'summary_description' => 'Thực hiện Quan trắc môi trường',
            'summary_unit' => 'Đợt',
            'price_catalog' => 'environmental_monitoring_2026',
            'default_price_subcontractor' => 'dai_phu',
            'price_subcontractors' => [
                'dai_phu' => 'Đại Phú',
                'cec' => 'CEC',
                'phuong_nam' => 'Phương Nam',
            ],
            'detail_groups' => [
                'I. MÔI TRƯỜNG KHÍ THẢI',
                'II. MÔI TRƯỜNG NƯỚC THẢI',
                'III. MÔI TRƯỜNG KHÍ XUNG QUANH',
                'IV. CHI PHÍ KHÁC',
            ],
            'requires' => ['frequency', 'period_breakdown'],
        ],
        'plld' => [
            'key' => 'plld',
            'label' => 'Phân loại lao động',
            'service_type' => 'Phân loại lao động',
            'template_path' => 'templates/quotations/plld-landscape.docx',
            'orientation' => 'landscape',
            'vat_rate' => 0,
            'summary_description' => 'Thực hiện Phân loại lao động',
            'summary_unit' => 'Hồ sơ',
            'detail_groups' => [
                'I. NHÓM YẾU TỐ ĐÁNH GIÁ VỀ VỆ SINH MÔI TRƯỜNG LAO ĐỘNG',
                'II. NHÓM YẾU TỐ ĐÁNH GIÁ TÁC ĐỘNG VỀ TÂM SINH LÝ LAO ĐỘNG',
                'III. NHÓM YẾU TỐ ĐÁNH GIÁ VỀ ECGONOMI - TỔ CHỨC LAO ĐỘNG',
            ],
            'requires' => ['job_matrix', 'landscape'],
        ],
        'vhnt' => [
            'key' => 'vhnt',
            'label' => 'Vận hành thử nghiệm',
            'service_type' => 'Vận hành thử nghiệm',
            'template_path' => 'templates/quotations/vhnt.docx',
            'orientation' => 'portrait',
            'vat_rate' => 8,
            'summary_description' => 'Thực hiện báo cáo Vận hành thử nghiệm',
            'summary_unit' => 'Hồ sơ',
            'detail_groups' => [
                'A. CHI PHÍ VẬN HÀNH THỬ NGHIỆM',
                'B. CHI PHÍ TƯ VẤN HỒ SƠ',
                'C. CHI PHÍ KHÁC',
            ],
            'requires' => ['location_indicator_table'],
        ],
        'dkmt' => [
            'key' => 'dkmt',
            'label' => 'Đăng ký môi trường',
            'service_type' => 'Hồ sơ môi trường',
            'template_path' => 'templates/quotations/dkmt.docx',
            'orientation' => 'portrait',
            'vat_rate' => 8,
            'summary_description' => 'Thực hiện Đăng ký môi trường',
            'summary_unit' => 'Hồ sơ',
            'detail_groups' => [
                'I. HỒ SƠ PHÁP LÝ',
                'II. GIẢI TRÌNH / HỖ TRỢ',
            ],
        ],
        'ctnh' => [
            'key' => 'ctnh',
            'label' => 'Thu gom, vận chuyển và xử lý CTNH',
            'service_type' => 'Thu gom, vận chuyển và xử lý chất thải nguy hại',
            'template_path' => 'templates/quotations/ctnh.docx',
            'orientation' => 'portrait',
            'vat_rate' => 8,
            'summary_description' => 'Thực hiện vận chuyển, thu gom và xử lý chất thải nguy hại',
            'summary_unit' => 'Chuyến',
            'detail_groups' => [
                'I. CHẤT THẢI NGUY HẠI',
                'II. CHI PHÍ PHÁT SINH',
            ],
            'requires' => ['waste_formula'],
        ],
        'huy_hang' => [
            'key' => 'huy_hang',
            'label' => 'Hủy hàng',
            'service_type' => 'Hủy hàng',
            'template_path' => 'templates/quotations/huy-hang.docx',
            'orientation' => 'portrait',
            'vat_rate' => 8,
            'summary_description' => 'Thực hiện hủy hàng',
            'summary_unit' => 'Gói',
            'detail_groups' => [
                'I. HỦY HÀNG',
                'II. VẬN CHUYỂN / XỬ LÝ',
            ],
        ],
        'giam_phat_thai' => [
            'key' => 'giam_phat_thai',
            'label' => 'Kế hoạch giảm phát thải khí nhà kính',
            'service_type' => 'Kế hoạch giảm phát thải khí nhà kính',
            'template_path' => 'templates/quotations/giam-phat-thai.docx',
            'orientation' => 'portrait',
            'vat_rate' => 8,
            'summary_description' => 'Xây dựng kế hoạch giảm phát thải khí nhà kính',
            'summary_unit' => 'Hồ sơ',
            'detail_groups' => [
                'I. NỘI DUNG HẠNG MỤC THỰC HIỆN',
                'II. TÀI LIỆU KHÁCH HÀNG CUNG CẤP',
            ],
            'requires' => ['schedule_table', 'required_documents_table'],
        ],
    ];

    private const PRICE_CATALOGS = [
        'labor_monitoring_2026' => LaborMonitoringPriceCatalog::class,
        'environmental_monitoring_2026' => EnvironmentalMonitoringPriceCatalog::class,
    ];

    public static function all(): array
    {
        return array_values(self::TEMPLATES);
    }

    public static function find(?string $key): array
    {
        return self::TEMPLATES[$key ?: self::DEFAULT_KEY] ?? self::TEMPLATES[self::DEFAULT_KEY];
    }

    public static function labels(): array
    {
        return collect(self::TEMPLATES)
            ->mapWithKeys(fn (array $template) => [$template['key'] => $template['label']])
            ->all();
    }

    public static function serviceTypes(): array
    {
        return collect(self::TEMPLATES)
            ->pluck('service_type')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function detailGroups(?string $key, ?string $subcontractor = null): array
    {
        $catalogClass = self::priceCatalogClass($key);

        if ($catalogClass !== null) {
            return $catalogClass::groups($subcontractor);
        }

        return self::find($key)['detail_groups'] ?? [];
    }

    public static function detailPriceCatalog(
        ?string $key,
        ?string $groupName = null,
        ?string $subcontractor = null
    ): array {
        $catalogClass = self::priceCatalogClass($key);

        return $catalogClass !== null ? $catalogClass::forGroup($groupName, $subcontractor) : [];
    }

    public static function findDetailPriceItem(
        ?string $key,
        ?string $description,
        ?string $groupName = null,
        ?string $subcontractor = null
    ): ?array {
        $catalogClass = self::priceCatalogClass($key);

        return $catalogClass !== null
            ? $catalogClass::findByDescription($description, $groupName, $subcontractor)
            : null;
    }

    public static function catalogDetailItem(?string $key, array $catalogItem, int|float $quantity = 1): array
    {
        $catalogClass = self::priceCatalogClass($key);

        return $catalogClass !== null ? $catalogClass::toDetailItem($catalogItem, $quantity) : [];
    }

    public static function serviceSummaryDescription(?string $key, ?string $serviceType = null): string
    {
        $template = self::find($key);
        $serviceType = trim((string) ($serviceType ?: ($template['service_type'] ?? '')));

        return $serviceType !== ''
            ? 'Thực hiện '.$serviceType
            : ($template['summary_description'] ?? '');
    }

    public static function defaultTerms(?string $key, ?string $serviceType = null): string
    {
        $template = self::find($key);
        $templateKey = $template['key'] ?? self::DEFAULT_KEY;
        $serviceType = trim((string) ($serviceType ?: ($template['service_type'] ?? 'dịch vụ')));

        if ($templateKey === self::DEFAULT_KEY) {
            return self::monitoringTerms('Quan trắc môi trường lao động', 'QTMTLĐ');
        }

        if ($templateKey === 'qtmt_periodic') {
            return self::monitoringTerms('Quan trắc môi trường', 'QTMT');
        }

        return "Kết quả thực hiện: {$serviceType}\nThời gian thực hiện: theo thỏa thuận giữa hai bên.\nChi phí trên đã bao gồm VAT tại thời điểm xuất hóa đơn.\nPhương thức thanh toán:\n• 50% sau khi ký hợp đồng\n• 50% sau khi hoàn thành công việc\nHình thức: chuyển khoản\nChúng tôi xin cam kết sẽ tiến hành và hoàn thành công việc theo đúng nội dung được nêu trong báo giá!";
    }

    public static function defaultSummaryItem(?string $key): array
    {
        $template = self::find($key);

        return [
            'description' => $template['summary_description'] ?? '',
            'unit' => $template['summary_unit'] ?? 'Hồ sơ',
            'quantity' => 1,
            'unit_price' => 0,
            'amount' => 0,
            'note' => '',
        ];
    }

    public static function priceSubcontractors(?string $key): array
    {
        return self::find($key)['price_subcontractors'] ?? [];
    }

    public static function defaultPriceSubcontractor(?string $key): string
    {
        $template = self::find($key);
        $options = self::priceSubcontractors($key);

        return $template['default_price_subcontractor'] ?? array_key_first($options) ?? '';
    }

    private static function priceCatalogClass(?string $key): ?string
    {
        $catalogKey = self::find($key)['price_catalog'] ?? null;

        return self::PRICE_CATALOGS[$catalogKey] ?? null;
    }

    private static function monitoringTerms(string $serviceName, string $shortName): string
    {
        return "Kết quả thực hiện: Báo cáo {$serviceName}\nThời gian có cuốn báo cáo {$shortName}: 10-15 ngày kể từ ngày quan trắc và có đầy đủ thông tin khách hàng cung cấp (không tính ngày lễ, thứ 7, chủ nhật);\nChi phí trên đã bao gồm VAT tại thời điểm xuất hóa đơn.\nPhương thức thanh toán:\n• 50% sau khi ký hợp đồng\n• 50% sau khi hoàn thành báo cáo {$serviceName}\nHình thức: chuyển khoản\nChúng tôi xin cam kết sẽ tiến hành và hoàn thành công việc theo đúng nội dung được nêu trong báo giá!";
    }
}
