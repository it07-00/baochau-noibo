<?php

namespace Tests\Unit;

use App\Support\Quotations\EnvironmentalMonitoringPriceCatalog;
use App\Support\Quotations\QuotationTemplateCatalog;
use Tests\TestCase;

class EnvironmentalMonitoringPriceCatalogTest extends TestCase
{
    public function test_catalog_exposes_qtmt_subcontractors(): void
    {
        $this->assertSame([
            'dai_phu' => 'Đại Phú',
            'cec' => 'CEC',
            'phuong_nam' => 'Phương Nam',
        ], EnvironmentalMonitoringPriceCatalog::subcontractors());
    }

    public function test_catalog_filters_groups_by_subcontractor(): void
    {
        $cecGroups = EnvironmentalMonitoringPriceCatalog::groups('cec');

        $this->assertContains('Không khí xung quanh', $cecGroups);
        $this->assertContains('Khí thải', $cecGroups);
        $this->assertContains('Bùn', $cecGroups);
        $this->assertContains('Nước uống', $cecGroups);
        $this->assertContains('Nhân công, vận chuyển', $cecGroups);
        $this->assertContains('Chi phí viết BÁO CÁO CÔNG TÁC BVMT', $cecGroups);
        $this->assertContains('Chi phí khác', $cecGroups);
        $this->assertNotContains('Nước ngầm', $cecGroups);
    }

    public function test_catalog_returns_vendor_specific_unit_price(): void
    {
        $daiPhu = EnvironmentalMonitoringPriceCatalog::findByDescription('Nhiệt độ', 'Không khí xung quanh', 'dai_phu');
        $cec = EnvironmentalMonitoringPriceCatalog::findByDescription('Nhiệt độ', 'Không khí xung quanh', 'cec');
        $cecMud = EnvironmentalMonitoringPriceCatalog::findByDescription('Asen (As)', 'Bùn', 'cec');
        $cecDrinkingWater = EnvironmentalMonitoringPriceCatalog::findByDescription('Độ đục', 'Nước uống', 'cec');
        $phuongNamReportCost = EnvironmentalMonitoringPriceCatalog::findByDescription(
            'Lập hồ sơ (viết, in ấn) - Thành phố Hồ Chí Minh - Tân Bình',
            'Chi phí viết BÁO CÁO CÔNG TÁC BVMT',
            'phuong_nam'
        );
        $phuongNam = EnvironmentalMonitoringPriceCatalog::findByDescription('Nhiệt độ', 'Khí thải', 'phuong_nam');

        $this->assertSame(30000, $daiPhu['unit_price']);
        $this->assertSame(30000, $cec['unit_price']);
        $this->assertSame(420000, $cecMud['unit_price']);
        $this->assertSame(120000, $cecDrinkingWater['unit_price']);
        $this->assertSame(2500000, $phuongNamReportCost['unit_price']);
        $this->assertSame(600000, $phuongNam['unit_price']);
    }

    public function test_qtmt_template_returns_items_for_selected_water_sea_group(): void
    {
        $items = QuotationTemplateCatalog::detailPriceCatalog('qtmt_periodic', 'Nước biển', 'dai_phu');
        $descriptions = array_column($items, 'description');

        $this->assertNotEmpty($items);
        $this->assertContains('- HCO3', $descriptions);
    }

    public function test_qtmt_template_uses_environmental_monitoring_price_catalog(): void
    {
        $this->assertSame(
            EnvironmentalMonitoringPriceCatalog::subcontractors(),
            QuotationTemplateCatalog::priceSubcontractors('qtmt_periodic')
        );

        $items = QuotationTemplateCatalog::detailPriceCatalog('qtmt_periodic', 'Không khí xung quanh', 'phuong_nam');
        $descriptions = array_column($items, 'description');

        $this->assertContains('Nhiệt độ', $descriptions);
    }

    public function test_qtmt_template_text_uses_environmental_monitoring_service_name(): void
    {
        $template = QuotationTemplateCatalog::find('qtmt_periodic');
        $terms = QuotationTemplateCatalog::defaultTerms('qtmt_periodic');

        $this->assertSame('Quan trắc môi trường', $template['label']);
        $this->assertSame(
            'Thực hiện Quan trắc môi trường',
            QuotationTemplateCatalog::serviceSummaryDescription('qtmt_periodic')
        );
        $this->assertStringContainsString('Báo cáo Quan trắc môi trường', $terms);
        $this->assertStringContainsString('báo cáo QTMT', $terms);
        $this->assertStringNotContainsString('lao động', mb_strtolower($terms, 'UTF-8'));
    }
}
