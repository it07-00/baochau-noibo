<?php

namespace Tests\Unit;

use App\Support\Quotations\LaborMonitoringPriceCatalog;
use App\Support\Quotations\QuotationTemplateCatalog;
use PHPUnit\Framework\TestCase;

class LaborMonitoringPriceCatalogTest extends TestCase
{
    public function test_catalog_contains_labor_monitoring_price_items(): void
    {
        $items = LaborMonitoringPriceCatalog::all();

        $this->assertGreaterThan(80, count($items));
        $this->assertSame(
            [
                'group_name' => 'I. YẾU TỐ VI KHÍ HẬU',
                'description' => 'Nhiệt độ',
                'unit_price' => 12000,
                'note' => '',
                'unit' => 'Mẫu',
            ],
            $items[0]
        );
    }

    public function test_find_by_description_returns_group_unit_price_and_note(): void
    {
        $item = LaborMonitoringPriceCatalog::findByDescription('Yếu tố vi sinh vật');

        $this->assertSame('III. YẾU TỐ TIẾP XÚC', $item['group_name']);
        $this->assertSame('Mẫu', $item['unit']);
        $this->assertSame(300000, $item['unit_price']);
        $this->assertSame('Xét nghiệm mẫu vi sinh', $item['note']);
    }

    public function test_for_group_only_returns_items_in_selected_group(): void
    {
        $items = LaborMonitoringPriceCatalog::forGroup('I. YẾU TỐ VI KHÍ HẬU');
        $descriptions = array_column($items, 'description');

        $this->assertSame(['Nhiệt độ', 'Độ ẩm', 'Tốc độ gió', 'Bức xạ nhiệt'], $descriptions);
        $this->assertNotContains('Điện từ trường CN', $descriptions);
    }

    public function test_description_aliases_match_common_short_names(): void
    {
        $item = LaborMonitoringPriceCatalog::findByDescription('VOC');

        $this->assertSame('VOCs', $item['description']);
        $this->assertSame(150000, $item['unit_price']);
    }

    public function test_to_detail_item_calculates_amount_from_quantity(): void
    {
        $catalogItem = LaborMonitoringPriceCatalog::findByDescription('NO2');
        $detailItem = LaborMonitoringPriceCatalog::toDetailItem($catalogItem, 3);

        $this->assertSame('VI. YẾU TỐ HÓA HỌC', $detailItem['group_name']);
        $this->assertSame(3, $detailItem['quantity']);
        $this->assertSame(80000, $detailItem['unit_price']);
        $this->assertSame(240000, $detailItem['amount']);
    }

    public function test_to_detail_item_rounds_quantity_to_integer(): void
    {
        $catalogItem = LaborMonitoringPriceCatalog::findByDescription('NO2');
        $detailItem = LaborMonitoringPriceCatalog::toDetailItem($catalogItem, 2.6);

        $this->assertSame(3, $detailItem['quantity']);
        $this->assertSame(240000, $detailItem['amount']);
    }

    public function test_qtmtld_template_uses_labor_monitoring_2026_price_catalog(): void
    {
        $template = QuotationTemplateCatalog::find(QuotationTemplateCatalog::DEFAULT_KEY);

        $this->assertSame('labor_monitoring_2026', $template['price_catalog']);
        $this->assertSame(
            LaborMonitoringPriceCatalog::groups(),
            QuotationTemplateCatalog::detailGroups(QuotationTemplateCatalog::DEFAULT_KEY)
        );

        $catalogItem = QuotationTemplateCatalog::findDetailPriceItem(QuotationTemplateCatalog::DEFAULT_KEY, 'NO2');
        $detailItem = QuotationTemplateCatalog::catalogDetailItem(QuotationTemplateCatalog::DEFAULT_KEY, $catalogItem, 2);

        $this->assertSame(80000, $catalogItem['unit_price']);
        $this->assertSame(160000, $detailItem['amount']);
    }
}
