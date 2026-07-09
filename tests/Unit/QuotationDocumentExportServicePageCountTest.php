<?php

namespace Tests\Unit;

use App\Models\QuotationDocument;
use App\Models\QuotationDocumentItem;
use App\Models\User;
use App\Services\Quotations\QuotationDocumentExportService;
use DOMDocument;
use DOMElement;
use DOMXPath;
use ReflectionClass;
use Tests\TestCase;
use ZipArchive;

class QuotationDocumentExportServicePageCountTest extends TestCase
{
    public function test_staff_contact_details_do_not_fallback_to_another_person(): void
    {
        $doc = new QuotationDocument;
        $doc->setRelation('staff', new User([
            'name' => 'Kinh Doanh',
            'phone' => null,
            'email' => null,
        ]));

        $this->assertSame(
            ['Kinh Doanh', '', ''],
            $this->resolveStaffDetails($doc)
        );
    }

    public function test_staff_contact_details_use_staff_phone_and_email_when_available(): void
    {
        $doc = new QuotationDocument;
        $doc->setRelation('staff', new User([
            'name' => 'Nguyễn Văn A',
            'phone' => '0909 000 111',
            'email' => 'sales@example.test',
        ]));

        $this->assertSame(
            ['Nguyễn Văn A', '0909 000 111', 'sales@example.test'],
            $this->resolveStaffDetails($doc)
        );
    }

    public function test_replace_docx_page_count_updates_page_count_text(): void
    {
        $sourcePath = resource_path('templates/quotations/qtmtld.docx');
        $targetPath = storage_path('app/temp/qdoc-page-count-test.docx');

        if (! is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0775, true);
        }

        copy($sourcePath, $targetPath);

        try {
            $service = app(QuotationDocumentExportService::class);
            $method = (new ReflectionClass($service))->getMethod('replaceDocxPageCount');
            $method->setAccessible(true);
            $method->invoke($service, $targetPath, '09');

            $this->assertSame('09', $this->readDocxPageCount($targetPath));
        } finally {
            @unlink($targetPath);
        }
    }

    public function test_periodic_monitoring_period_table_repeats_quarter_one_amount_in_quarter_two(): void
    {
        $targetPath = storage_path('app/temp/qdoc-period-summary-test.docx');

        $doc = new QuotationDocument([
            'document_number' => 'QTMT-TEST-001',
            'date' => now(),
            'customer_name' => 'CÔNG TY TEST',
            'customer_contact' => 'Anh A',
            'customer_address' => 'TP. Hồ Chí Minh',
            'service_type' => 'Quan trắc môi trường',
            'template_key' => 'qtmt_periodic',
            'subtotal' => 1000000,
            'vat_rate' => 8,
            'vat_amount' => 80000,
            'total' => 1080000,
        ]);
        $staff = new User(['name' => 'Kinh Doanh']);
        $staff->setRelation('roles', collect());
        $doc->setRelation('staff', $staff);
        $doc->setRelation('items', collect([
            new QuotationDocumentItem([
                'item_type' => 'summary',
                'sort_order' => 1,
                'description' => 'Quan trắc môi trường khí thải',
                'unit' => 'Đợt',
                'quantity' => 1,
                'unit_price' => 1000000,
                'amount' => 1000000,
            ]),
        ]));
        $doc->setRelation('sections', collect());

        try {
            $service = app(QuotationDocumentExportService::class);
            $method = (new ReflectionClass($service))->getMethod('buildDocxFromTemplate');
            $method->setAccessible(true);
            $method->invoke($service, $doc, $targetPath, '03');

            $rows = $this->readDocxTableRows($targetPath);
            $periodRow = collect($rows)
                ->first(fn (array $cells): bool => ($cells[1] ?? '') === 'Quan trắc môi trường khí thải'
                    && ($cells[2] ?? '') === '1.000.000');

            $this->assertNotNull($periodRow);
            $this->assertSame('1.000.000', $periodRow[2] ?? null);
            $this->assertSame('1.000.000', $periodRow[3] ?? null);

            $totalRow = collect($rows)
                ->first(fn (array $cells): bool => ($cells[0] ?? '') === 'TỔNG CỘNG'
                    && collect($cells)->filter(fn (string $cell): bool => $cell === '1.000.000')->count() === 2);

            $this->assertNotNull($totalRow);
        } finally {
            @unlink($targetPath);
        }
    }

    public function test_fallback_pdf_preserves_three_page_layout_for_full_periodic_quote(): void
    {
        $doc = new QuotationDocument([
            'document_number' => 'QTMT-LAYOUT-001',
            'date' => now(),
            'customer_name' => 'FULL PERIODIC MONITORING CUSTOMER',
            'customer_contact' => 'Environmental Manager',
            'customer_address' => 'Industrial Park, Ho Chi Minh City',
            'service_type' => 'Environmental monitoring',
            'template_key' => 'qtmt_periodic',
            'work_location' => 'Factory and wastewater treatment system',
            'subtotal' => 44860000,
            'discount' => 1560000,
            'vat_rate' => 8,
            'vat_amount' => 3464000,
            'total' => 46764000,
            'terms' => 'Payment terms: 50% after signing and 50% after report completion.',
        ]);

        $staff = new User(['name' => 'Sales Staff']);
        $staff->setRelation('roles', collect());
        $doc->setRelation('staff', $staff);

        $items = collect([
            new QuotationDocumentItem([
                'item_type' => 'summary',
                'sort_order' => 0,
                'description' => 'Perform environmental monitoring in 2026 at the factory production area and wastewater treatment system',
                'unit' => 'Report',
                'quantity' => 1,
                'unit_price' => 44860000,
                'amount' => 44860000,
            ]),
        ]);

        foreach (range(1, 23) as $index) {
            $items->push(new QuotationDocumentItem([
                'item_type' => 'detail',
                'sort_order' => $index,
                'group_name' => match (true) {
                    $index <= 5 => 'I. AIR EMISSIONS',
                    $index <= 14 => 'II. WASTEWATER',
                    $index <= 21 => 'III. AMBIENT AIR',
                    default => 'IV. OTHER COSTS',
                },
                'description' => $index % 5 === 0
                    ? 'Flow rate, temperature and pressure at the monitoring location'
                    : 'Environmental monitoring parameter '.$index,
                'unit' => $index % 3 === 0 ? 'Parameter' : 'Sample',
                'quantity' => $index % 4 + 1,
                'frequency' => 4,
                'unit_price' => 150000,
                'amount' => 600000,
            ]));
        }

        $doc->setRelation('items', $items);
        $doc->setRelation('sections', collect());

        $service = app(QuotationDocumentExportService::class);
        $method = (new ReflectionClass($service))->getMethod('generateFallbackPdfContent');
        $method->setAccessible(true);
        $content = $method->invoke($service, $doc, '03');

        preg_match_all('/\/Type\s*\/Page\b/', $content, $matches);

        $this->assertCount(3, $matches[0]);
    }

    public function test_fallback_pdf_details_table_includes_frequency_column(): void
    {
        $doc = new QuotationDocument([
            'document_number' => 'QTMTLD-FREQUENCY-001',
            'date' => now(),
            'customer_name' => 'Frequency Customer',
            'service_type' => 'Labor monitoring',
            'template_key' => 'qtmtld',
            'subtotal' => 996000,
            'discount' => 0,
            'vat_rate' => 8,
            'vat_amount' => 79680,
            'total' => 1075680,
            'terms' => 'Payment terms.',
        ]);

        $doc->setRelation('staff', new User(['name' => 'Sales Staff']));
        $doc->setRelation('sections', collect());
        $doc->setRelation('items', collect([
            new QuotationDocumentItem([
                'item_type' => 'summary',
                'sort_order' => 0,
                'description' => 'Perform labor monitoring',
                'unit' => 'Report',
                'quantity' => 1,
                'unit_price' => 996000,
                'amount' => 996000,
            ]),
            new QuotationDocumentItem([
                'item_type' => 'detail',
                'sort_order' => 1,
                'group_name' => 'I. MICROCLIMATE',
                'description' => 'Temperature',
                'unit' => 'Sample',
                'quantity' => 1,
                'frequency' => 83,
                'unit_price' => 12000,
                'amount' => 996000,
            ]),
        ]));

        $html = view('admin.quotations.quotation-document-pdf', [
            'doc' => $doc,
            'company' => [
                'name' => 'Company',
                'address' => 'Address',
                'phone' => 'Phone',
                'email' => 'Email',
                'tax_code' => 'Tax',
            ],
            'amountInWords' => 'One million',
            'serviceTitle' => 'Labor monitoring',
            'year' => '2026',
            'noteLines' => [],
            'staffName' => 'Sales Staff',
            'staffPhone' => '',
            'staffEmail' => '',
            'staffGenderPrefix' => 'Mr.',
            'staffTitle' => 'Sales Manager',
            'pageCount' => '03',
        ])->render();

        $this->assertStringContainsString('class="col-frequency"', $html);
        $this->assertStringContainsString('TẦN<br />SUẤT', $html);
        $this->assertStringContainsString('<td class="center">83</td>', $html);
    }

    private function resolveStaffDetails(QuotationDocument $doc): array
    {
        $service = app(QuotationDocumentExportService::class);
        $method = (new ReflectionClass($service))->getMethod('resolveStaffDetails');
        $method->setAccessible(true);

        return $method->invoke($service, $doc);
    }

    private function readDocxPageCount(string $path): ?string
    {
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true);

        try {
            $documentXml = $zip->getFromName('word/document.xml');
            $this->assertIsString($documentXml);

            $dom = new DOMDocument;
            $dom->loadXML($documentXml);

            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

            foreach ($xpath->query('//w:p') as $paragraph) {
                if (! $paragraph instanceof DOMElement) {
                    continue;
                }

                $text = collect($xpath->query('.//w:t', $paragraph))
                    ->map(fn (DOMElement $textNode): string => $textNode->textContent)
                    ->implode('');

                if (preg_match('/Số\s*trang:\s*(\d+)/ui', $text, $matches) === 1) {
                    return $matches[1];
                }
            }

            return null;
        } finally {
            $zip->close();
        }
    }

    private function readDocxTableRows(string $path): array
    {
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true);

        try {
            $documentXml = $zip->getFromName('word/document.xml');
            $this->assertIsString($documentXml);

            $dom = new DOMDocument;
            $dom->loadXML($documentXml);

            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

            $rows = [];
            foreach ($xpath->query('//w:tbl/w:tr') as $row) {
                if (! $row instanceof DOMElement) {
                    continue;
                }

                $cells = [];
                foreach ($xpath->query('./w:tc', $row) as $cell) {
                    if (! $cell instanceof DOMElement) {
                        continue;
                    }

                    $cells[] = collect($xpath->query('.//w:t', $cell))
                        ->map(fn (DOMElement $textNode): string => $textNode->textContent)
                        ->implode('');
                }

                $rows[] = $cells;
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }
}
