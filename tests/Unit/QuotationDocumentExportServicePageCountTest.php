<?php

namespace Tests\Unit;

use App\Services\Quotations\QuotationDocumentExportService;
use DOMDocument;
use DOMElement;
use DOMXPath;
use ReflectionClass;
use Tests\TestCase;
use ZipArchive;

class QuotationDocumentExportServicePageCountTest extends TestCase
{
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
}
