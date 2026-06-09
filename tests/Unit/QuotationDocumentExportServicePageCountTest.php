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
