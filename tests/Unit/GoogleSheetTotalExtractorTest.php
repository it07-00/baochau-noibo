<?php

namespace Tests\Unit;

use App\Services\GoogleSheetTotalExtractor;
use PHPUnit\Framework\TestCase;

class GoogleSheetTotalExtractorTest extends TestCase
{
    public function test_extract_total_from_csv_uses_row_containing_tong_cong(): void
    {
        $service = new GoogleSheetTotalExtractor;

        $csv = <<<'CSV'
Hang muc,So tien
Tam ung,120.000
"Tổng Cộng",936.000
CSV;

        $this->assertSame(936000, $service->extractTotalFromCsv($csv));
    }

    public function test_build_csv_export_url_from_google_sheet_link(): void
    {
        $service = new GoogleSheetTotalExtractor;

        $url = $service->buildCsvExportUrl('https://docs.google.com/spreadsheets/d/1oHGudNXLg8xOovSuU48KVyfcEOdT1vfaw7Vanch-kz4/edit?gid=123#gid=123');

        $this->assertSame(
            'https://docs.google.com/spreadsheets/d/1oHGudNXLg8xOovSuU48KVyfcEOdT1vfaw7Vanch-kz4/export?format=csv&gid=123',
            $url
        );
    }

    public function test_extract_total_from_csv_accepts_unaccented_total_label(): void
    {
        $service = new GoogleSheetTotalExtractor;

        $csv = <<<'CSV'
Muc,So tien,Ghi chu
Phi van chuyen,50.000,
Tong Cong,,1.240.000
CSV;

        $this->assertSame(1240000, $service->extractTotalFromCsv($csv));
    }

    public function test_extract_total_from_csv_ignores_number_inside_total_label_cell(): void
    {
        $service = new GoogleSheetTotalExtractor;

        $csv = <<<'CSV'
Mo ta,So tien,Ghi chu
"Tổng Cộng 999.000",,123.000
CSV;

        $this->assertSame(123000, $service->extractTotalFromCsv($csv));
    }

    public function test_extract_total_from_csv_only_uses_numbers_after_total_column(): void
    {
        $service = new GoogleSheetTotalExtractor;

        $csv = <<<'CSV'
Cot truoc,Nhan,Tong
500.000,"Tổng Cộng",936.000
CSV;

        $this->assertSame(936000, $service->extractTotalFromCsv($csv));
    }

    public function test_extract_total_from_csv_supports_negative_and_decimal_numbers(): void
    {
        $service = new GoogleSheetTotalExtractor;

        $csv = <<<'CSV'
Muc,So tien
"Tổng Cộng","-936,50"
CSV;

        $this->assertSame(-937, $service->extractTotalFromCsv($csv));
    }
}
