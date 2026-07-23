<?php

namespace App\Services\Quotations;

use App\Enums\Role;
use App\Models\QuotationDocument;
use App\Support\QuotationPdfViewData;
use App\Support\Quotations\QuotationTemplateCatalog;
use Barryvdh\DomPDF\Facade\Pdf;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class QuotationDocumentExportService
{
    private const COMPANY_NAME = 'CÔNG TY TNHH DỊCH VỤ VÀ KỸ THUẬT MÔI TRƯỜNG BẢO CHÂU';

    private const COMPANY_ADDRESS = 'Số 5, Đường 35, Phường An Khánh, TP. Thủ Đức, TP. Hồ Chí Minh';

    private const COMPANY_PHONE = '028.6681.1708';

    private const COMPANY_EMAIL = 'moitruongbaochau@gmail.com';

    private const COMPANY_TAX_CODE = '0316928127';

    private const WORD_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    private const TEMPLATE_RELATIVE_PATH = 'templates/quotations/qtmtld.docx';

    private const TABLE_HEADER_FILL = 'C5EECE';

    public function exportDocx(QuotationDocument $doc): string
    {
        $doc->loadMissing('items', 'sections.rows', 'staff');

        $storagePath = 'quotation-docs/BG-'.$this->safeFilePart($doc->document_number).'-'.now()->format('YmdHis').'.docx';
        $tempPath = storage_path('app/temp/'.$storagePath);

        $this->ensureDirectory(dirname($tempPath));
        $this->buildDocxWithFinalPageCount($doc, $tempPath);

        Storage::disk(config('filesystems.upload_disk', 'public'))->put($storagePath, file_get_contents($tempPath));
        $doc->update(['docx_path' => $storagePath]);

        @unlink($tempPath);

        return $storagePath;
    }

    public function exportPdf(QuotationDocument $doc): string
    {
        $doc->loadMissing('items', 'sections.rows', 'staff');

        $baseName = 'BG-'.$this->safeFilePart($doc->document_number).'-'.now()->format('YmdHis');
        $workDir = storage_path('app/temp/quotation-docs/'.$baseName);
        $docxPath = $workDir.DIRECTORY_SEPARATOR.$baseName.'.docx';
        $pdfPath = $workDir.DIRECTORY_SEPARATOR.$baseName.'.pdf';

        $this->ensureDirectory($workDir);

        try {
            $this->buildDocxWithFinalPageCount($doc, $docxPath);

            $pdfViewData = new QuotationPdfViewData;
            $useFallback = $pdfViewData->hasFrequency($doc) && ! $this->templateSupportsFrequency($doc->template_key);

            $content = (! $useFallback && $this->convertDocxToPdf($docxPath, $pdfPath))
                ? file_get_contents($pdfPath)
                : $this->generateFallbackPdfContent($doc, $this->estimatePageCount($doc));

            $storagePath = 'quotation-docs/'.$baseName.'.pdf';
            Storage::disk(config('filesystems.upload_disk', 'public'))->put($storagePath, $content);
            $doc->update(['pdf_path' => $storagePath]);

            return $storagePath;
        } finally {
            $this->removeDirectory($workDir);
        }
    }

    public function generatePdfContent(QuotationDocument $doc): string
    {
        $doc->loadMissing('items', 'sections.rows', 'staff');

        $baseName = 'BG-'.$this->safeFilePart($doc->document_number).'-'.uniqid();
        $workDir = storage_path('app/temp/quotation-docs/'.$baseName);
        $docxPath = $workDir.DIRECTORY_SEPARATOR.$baseName.'.docx';
        $pdfPath = $workDir.DIRECTORY_SEPARATOR.$baseName.'.pdf';

        $this->ensureDirectory($workDir);

        try {
            $this->buildDocxWithFinalPageCount($doc, $docxPath);

            $pdfViewData = new QuotationPdfViewData;
            $useFallback = $pdfViewData->hasFrequency($doc) && ! $this->templateSupportsFrequency($doc->template_key);

            return (! $useFallback && $this->convertDocxToPdf($docxPath, $pdfPath))
                ? file_get_contents($pdfPath)
                : $this->generateFallbackPdfContent($doc, $this->estimatePageCount($doc));
        } finally {
            $this->removeDirectory($workDir);
        }
    }

    public function downloadFileName(QuotationDocument $doc, string $extension): string
    {
        return 'Bao-gia-'.$this->safeFilePart($doc->document_number).'.'.ltrim($extension, '.');
    }

    private function templatePathFor(QuotationDocument $doc): string
    {
        $template = QuotationTemplateCatalog::find($doc->template_key ?? null);
        $preferredPath = resource_path($template['template_path'] ?? self::TEMPLATE_RELATIVE_PATH);

        if (is_file($preferredPath)) {
            return $preferredPath;
        }

        return resource_path(self::TEMPLATE_RELATIVE_PATH);
    }

    private function buildDocxFromTemplate(QuotationDocument $doc, string $outputPath, string $pageCount): void
    {
        $templatePath = $this->templatePathFor($doc);

        if (! is_file($templatePath)) {
            throw new RuntimeException('Không tìm thấy file mẫu báo giá: '.$templatePath);
        }

        $this->ensureDirectory(dirname($outputPath));
        copy($templatePath, $outputPath);

        $zip = new ZipArchive;
        if ($zip->open($outputPath) !== true) {
            throw new RuntimeException('Không thể mở file Word mẫu để xuất báo giá.');
        }

        $documentXml = $zip->getFromName('word/document.xml');
        if ($documentXml === false) {
            $zip->close();
            throw new RuntimeException('File Word mẫu không có word/document.xml.');
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($documentXml);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', self::WORD_NS);

        $this->hydrateTemplateDocument($doc, $dom, $xpath, $pageCount);

        $zip->addFromString('word/document.xml', $dom->saveXML());
        $zip->close();
    }

    private function buildDocxWithFinalPageCount(QuotationDocument $doc, string $outputPath): string
    {
        $pageCount = $this->estimatePageCount($doc);
        $this->buildDocxFromTemplate($doc, $outputPath, $pageCount);

        $actualPageCount = $this->detectDocxPageCount($outputPath);
        if ($actualPageCount !== null && $actualPageCount !== $pageCount) {
            $this->replaceDocxPageCount($outputPath, $actualPageCount);

            return $actualPageCount;
        }

        return $pageCount;
    }

    private function hydrateTemplateDocument(QuotationDocument $doc, DOMDocument $dom, DOMXPath $xpath, string $pageCount): void
    {
        $body = $xpath->query('/w:document/w:body')->item(0);
        if (! $body instanceof DOMElement) {
            throw new RuntimeException('File Word mẫu không có phần nội dung.');
        }

        $year = $doc->date?->format('Y') ?? now()->format('Y');
        $serviceType = $this->displayServiceType($doc);
        $serviceTypeLower = mb_strtolower($serviceType, 'UTF-8');
        $customerName = $doc->customer_name ?: 'QUÝ CÔNG TY';
        [$staffName, $staffPhone, $staffEmail] = $this->resolveStaffDetails($doc);
        $templateKey = QuotationTemplateCatalog::find($doc->template_key ?? null)['key'];

        if ($templateKey === 'plld') {
            $this->hydrateLaborClassificationTemplate(
                $body,
                $xpath,
                $doc,
                $year,
                $serviceType,
                $customerName,
                $staffName,
                $staffPhone,
                $staffEmail,
                $pageCount
            );

            return;
        }

        if ($templateKey === 'qtmt_periodic') {
            $this->hydratePeriodicMonitoringTemplate(
                $body,
                $xpath,
                $doc,
                $year,
                $serviceType,
                $serviceTypeLower,
                $customerName,
                $staffName,
                $staffPhone,
                $staffEmail,
                $pageCount
            );

            return;
        }

        if ($templateKey === QuotationTemplateCatalog::DEFAULT_KEY) {
            $this->hydrateLaborMonitoringTemplate(
                $body,
                $xpath,
                $doc,
                $year,
                $serviceType,
                $serviceTypeLower,
                $customerName,
                $staffName,
                $staffPhone,
                $staffEmail,
                $pageCount
            );

            return;
        }

        if (in_array($templateKey, ['ctnh', 'huy_hang'], true)) {
            $this->hydrateWasteServiceTemplate(
                $body,
                $xpath,
                $doc,
                $year,
                $templateKey,
                $serviceType,
                $customerName,
                $staffName,
                $staffPhone,
                $staffEmail
            );

            return;
        }

        if (in_array($templateKey, ['vhnt', 'dkmt'], true)) {
            $this->hydrateProjectCoverTemplate(
                $body,
                $xpath,
                $doc,
                $year,
                $templateKey,
                $serviceType,
                $customerName,
                $staffName,
                $staffPhone,
                $staffEmail
            );

            return;
        }

        if ($templateKey === 'giam_phat_thai') {
            $this->hydrateGreenhouseGasTemplate(
                $body,
                $xpath,
                $doc,
                $year,
                $serviceType,
                $customerName,
                $staffName,
                $staffPhone,
                $staffEmail,
                $pageCount
            );

            return;
        }

        $this->setDirectParagraphStartingWith($body, $xpath, 'BẢNG BÁO GIÁ', 'BẢNG BÁO GIÁ');
        $this->setDirectParagraphStartingWith($body, $xpath, '(V/v:', '(V/v: '.$serviceType.' '.$year.')');
        $this->setDirectParagraphStartingWith(
            $body,
            $xpath,
            'Xin chân thành',
            'Xin chân thành cảm ơn Quý khách hàng đã tin tưởng và lựa chọn chúng tôi. '
            .'**Công ty TNHH Dịch vụ và Kỹ thuật Môi trường Bảo Châu** hân hạnh được đồng hành cùng Quý khách hàng trong lĩnh vực môi trường. '
            .'Về dịch vụ hiện '.$serviceTypeLower.' '.$year.', Công ty Bảo Châu xin gửi báo giá như sau:'
        );
        $this->setDirectParagraphStartingWith($body, $xpath, 'Bảng 01.', 'Bảng 01. Tổng hợp dự toán chi phí thực hiện');
        $this->setDirectParagraphStartingWith($body, $xpath, 'Bảng 02.', 'Bảng 02. Chi tiết thực hiện');
        $this->setDirectParagraphStartingWith($body, $xpath, 'Ghi chú', 'Ghi chú :');

        $tables = $this->directChildElements($body, 'tbl');

        if (isset($tables[0])) {
            $this->hydrateInfoTable($tables[0], $xpath, $doc, $customerName, $staffName, $staffPhone, $staffEmail, $pageCount);
        }

        if (isset($tables[1])) {
            $this->hydrateSummaryTable($tables[1], $xpath, $doc);
        }

        if (isset($tables[2])) {
            $this->hydrateDetailTable($tables[2], $xpath, $doc);
        }

        if (isset($tables[3])) {
            $this->hydrateSignatureTable($tables[3], $xpath, $doc, $staffName, $staffPhone, $staffEmail);
        }

        $noteHeading = $this->findDirectParagraphStartingWith($body, $xpath, 'Ghi chú');
        if ($noteHeading instanceof DOMElement) {
            $this->replaceParagraphsAfterHeadingUntilNextTable($noteHeading, $xpath, $this->noteLines($doc, $year));
        }
    }

    private function hydrateLaborMonitoringTemplate(
        DOMElement $body,
        DOMXPath $xpath,
        QuotationDocument $doc,
        string $year,
        string $serviceType,
        string $serviceTypeLower,
        string $customerName,
        string $staffName,
        string $staffPhone,
        string $staffEmail,
        string $pageCount
    ): void {
        $this->setDirectParagraphStartingWith($body, $xpath, 'BẢNG BÁO GIÁ', 'BẢNG BÁO GIÁ');
        $this->setDirectParagraphStartingWith($body, $xpath, '(V/v:', '(V/v: '.$serviceType.' '.$year.')');
        $this->setDirectParagraphStartingWith(
            $body,
            $xpath,
            'Xin chân thành',
            'Xin chân thành cảm ơn Quý khách hàng đã tin tưởng và lựa chọn chúng tôi. '
            .'**Công ty TNHH Dịch vụ và Kỹ thuật Môi trường Bảo Châu** hân hạnh được đồng hành cùng Quý khách hàng trong lĩnh vực môi trường. '
            .'Về dịch vụ hiện '.$serviceTypeLower.' '.$year.', Công ty Bảo Châu xin gửi báo giá như sau:'
        );
        $this->setDirectParagraphStartingWith($body, $xpath, 'Ghi chú', 'Ghi chú :');

        $tables = $this->directChildElements($body, 'tbl');

        if (isset($tables[0])) {
            $this->hydrateInfoTable($tables[0], $xpath, $doc, $customerName, $staffName, $staffPhone, $staffEmail, $pageCount);
        }

        if (isset($tables[1])) {
            $this->hydrateSummaryTable($tables[1], $xpath, $doc);
        }

        if (isset($tables[2])) {
            $this->hydrateDetailTable($tables[2], $xpath, $doc);
        }

        if (isset($tables[3])) {
            $this->hydrateSignatureTable($tables[3], $xpath, $doc, $staffName, $staffPhone, $staffEmail);
        }

        $noteHeading = $this->findDirectParagraphStartingWith($body, $xpath, 'Ghi chú');
        if ($noteHeading instanceof DOMElement) {
            $this->replaceParagraphsAfterHeadingUntilNextTable($noteHeading, $xpath, $this->noteLines($doc, $year));
        }
    }

    private function hydratePeriodicMonitoringTemplate(
        DOMElement $body,
        DOMXPath $xpath,
        QuotationDocument $doc,
        string $year,
        string $serviceType,
        string $serviceTypeLower,
        string $customerName,
        string $staffName,
        string $staffPhone,
        string $staffEmail,
        string $pageCount
    ): void {
        $this->setDirectParagraphStartingWith($body, $xpath, 'BẢNG BÁO GIÁ', 'BẢNG BÁO GIÁ');
        $this->setDirectParagraphStartingWith($body, $xpath, '(V/v:', '(V/v: '.$serviceType.' '.$year.')');
        $this->setDirectParagraphStartingWith(
            $body,
            $xpath,
            'Xin chân thành',
            'Xin chân thành cảm ơn Quý khách hàng đã tin tưởng và lựa chọn chúng tôi. '
            .'**Công ty TNHH Dịch vụ và Kỹ thuật Môi trường Bảo Châu** hân hạnh được đồng hành cùng Quý khách hàng trong lĩnh vực môi trường. '
            .'Về dịch vụ '.$serviceTypeLower.' '.$year.', Công ty Bảo Châu xin gửi báo giá như sau:'
        );
        $this->setDirectParagraphStartingWith($body, $xpath, 'Bảng 01.', 'Bảng 01. Tổng hợp chi phí thực hiện');
        $this->setDirectParagraphStartingWith($body, $xpath, 'Bảng 02.', 'Bảng 02. Chi phí chi tiết theo từng kỳ');
        $this->setDirectParagraphStartingWith($body, $xpath, 'Ghi chú', 'Ghi chú :');

        $tables = $this->directChildElements($body, 'tbl');

        if (isset($tables[0])) {
            $this->hydrateInfoTable($tables[0], $xpath, $doc, $customerName, $staffName, $staffPhone, $staffEmail, $pageCount);
        }

        if (isset($tables[1])) {
            $this->hydrateFrequencyDetailTable($tables[1], $xpath, $doc);
        }

        if (isset($tables[2])) {
            $this->hydratePeriodSummaryTable($tables[2], $xpath, $doc);
        }

        $noteHeading = $this->findDirectParagraphStartingWith($body, $xpath, 'Ghi chú');
        if ($noteHeading instanceof DOMElement) {
            $this->replaceParagraphsAfterHeadingUntilNextTable($noteHeading, $xpath, $this->noteLines($doc, $year));
        }

        if (isset($tables[3])) {
            $this->hydrateSignatureTable($tables[3], $xpath, $doc, $staffName, $staffPhone, $staffEmail);
        }
    }

    private function hydrateLaborClassificationTemplate(
        DOMElement $body,
        DOMXPath $xpath,
        QuotationDocument $doc,
        string $year,
        string $serviceType,
        string $customerName,
        string $staffName,
        string $staffPhone,
        string $staffEmail,
        string $pageCount
    ): void {
        $this->setDirectParagraphStartingWith($body, $xpath, 'BẢNG BÁO GIÁ', 'BẢNG BÁO GIÁ');
        $this->setDirectParagraphStartingWith($body, $xpath, '(V/v:', '(V/v: '.$serviceType.')');
        $this->setDirectParagraphStartingWith(
            $body,
            $xpath,
            'Xin chân thành',
            'Xin chân thành cảm ơn Quý khách hàng đã tin tưởng và lựa chọn chúng tôi. '
            .'**Công ty TNHH Dịch vụ và Kỹ thuật Môi trường Bảo Châu** hân hạnh được đồng hành cùng Quý khách hàng trong lĩnh vực môi trường. '
            .'Về dịch vụ '.$serviceType.', Công ty Bảo Châu xin gửi báo giá như sau:'
        );
        $this->setDirectParagraphStartingWith($body, $xpath, 'Bảng 01.', 'Bảng 01. Tổng hợp chi phí thực hiện phân loại lao động');
        $this->setDirectParagraphStartingWith($body, $xpath, 'Ghi chú', 'Ghi chú:');

        $tables = $this->directChildElements($body, 'tbl');

        if (isset($tables[0])) {
            $this->hydrateInfoTable($tables[0], $xpath, $doc, $customerName, $staffName, $staffPhone, $staffEmail, $pageCount);
        }

        if (isset($tables[1])) {
            $this->hydrateLaborCostTable($tables[1], $xpath, $doc);
        }

        if (isset($tables[2])) {
            $this->hydrateLaborMatrixTable($tables[2], $xpath, $doc);
        }

        $noteHeading = $this->findDirectParagraphStartingWith($body, $xpath, 'Ghi chú');
        if ($noteHeading instanceof DOMElement) {
            $this->replaceParagraphsAfterHeadingUntilNextTable($noteHeading, $xpath, $this->noteLines($doc, $year));
        }

        if (isset($tables[3])) {
            $this->hydrateSignatureTable($tables[3], $xpath, $doc, $staffName, $staffPhone, $staffEmail);
        }
    }

    private function hydrateProjectCoverTemplate(
        DOMElement $body,
        DOMXPath $xpath,
        QuotationDocument $doc,
        string $year,
        string $templateKey,
        string $serviceType,
        string $customerName,
        string $staffName,
        string $staffPhone,
        string $staffEmail
    ): void {
        $this->setDirectParagraphStartingWith($body, $xpath, 'BÁO GIÁ THỰC HIỆN', 'BÁO GIÁ THỰC HIỆN');
        $this->setDirectParagraphStartingWith($body, $xpath, '“', '“'.mb_strtoupper($customerName, 'UTF-8').'”');
        $this->setDirectParagraphStartingWith(
            $body,
            $xpath,
            'Địa điểm:',
            'Địa điểm: '.($doc->work_location ?: $doc->customer_address ?: '')
        );
        $this->setDirectParagraphStartingWith($body, $xpath, 'TP Hồ Chí Minh', 'TP Hồ Chí Minh, năm '.$year);

        if ($templateKey === 'vhnt') {
            $this->setDirectParagraphStartingWith($body, $xpath, 'VẬN HÀNH', mb_strtoupper($serviceType, 'UTF-8'));
        }

        $tables = $this->directChildElements($body, 'tbl');

        if (isset($tables[1])) {
            $this->hydrateCoverSummaryTable($tables[1], $xpath, $doc);
        }

        if ($templateKey === 'vhnt' && isset($tables[2])) {
            $this->hydrateTrialOperationDetailTable($tables[2], $xpath, $doc);
        }

        $customLines = $this->customNoteLines($doc);
        if ($templateKey === 'vhnt' && $customLines !== []) {
            $noteHeading = $this->findDirectParagraphStartingWith($body, $xpath, 'Ghi chú');
            if ($noteHeading instanceof DOMElement) {
                $this->replaceParagraphsAfterHeadingUntilNextTable($noteHeading, $xpath, $customLines);
            }
        }

        $signatureTableIndex = $templateKey === 'vhnt' ? 3 : 2;
        if (isset($tables[$signatureTableIndex])) {
            $this->hydrateSignatureTable($tables[$signatureTableIndex], $xpath, $doc, $staffName, $staffPhone, $staffEmail);
        }
    }

    private function hydrateGreenhouseGasTemplate(
        DOMElement $body,
        DOMXPath $xpath,
        QuotationDocument $doc,
        string $year,
        string $serviceType,
        string $customerName,
        string $staffName,
        string $staffPhone,
        string $staffEmail,
        string $pageCount
    ): void {
        $this->setDirectParagraphStartingWith($body, $xpath, 'BẢNG BÁO GIÁ', 'BẢNG BÁO GIÁ');
        $this->setDirectParagraphStartingWith($body, $xpath, '(V.v:', '(V.v: '.$serviceType.' giai đoạn '.$year.'-2030)');
        $this->setDirectParagraphStartingWith($body, $xpath, 'Bảng 01.', 'Bảng 01. Tổng hợp dự toán');

        $tables = $this->directChildElements($body, 'tbl');

        if (isset($tables[0])) {
            $this->hydrateInfoTable($tables[0], $xpath, $doc, $customerName, $staffName, $staffPhone, $staffEmail, $pageCount);
        }

        if (isset($tables[1])) {
            $this->hydrateFiveColumnSummaryTable($tables[1], $xpath, $doc);
        }

        if (isset($tables[2])) {
            $this->hydrateScheduleTable($tables[2], $xpath, $doc);
        }

        if (isset($tables[3])) {
            $this->hydrateRequiredDocumentsTable($tables[3], $xpath, $doc);
        }

        if (isset($tables[4])) {
            $this->hydrateSignatureTable($tables[4], $xpath, $doc, $staffName, $staffPhone, $staffEmail);
        }
    }

    private function hydrateWasteServiceTemplate(
        DOMElement $body,
        DOMXPath $xpath,
        QuotationDocument $doc,
        string $year,
        string $templateKey,
        string $serviceType,
        string $customerName,
        string $staffName,
        string $staffPhone,
        string $staffEmail
    ): void {
        $title = $templateKey === 'huy_hang'
            ? 'BẢNG BÁO GIÁ HỦY HÀNG'
            : 'BẢNG BÁO GIÁ THU GOM CHẤT THẢI NGUY HẠI';

        $this->setDirectParagraphStartingWith($body, $xpath, 'BẢNG BÁO GIÁ', $title);
        $this->setDirectParagraphStartingWith($body, $xpath, '(V/v:', '(V/v: '.$serviceType.' '.$year.')');
        $this->setDirectParagraphStartingWith($body, $xpath, 'Kính gửi:', 'Kính gửi: '.mb_strtoupper($customerName, 'UTF-8'));
        $this->setDirectParagraphStartingWith($body, $xpath, 'Ghi chú', 'Ghi chú:');

        $tables = $this->directChildElements($body, 'tbl');

        if (isset($tables[0])) {
            $this->hydrateShortPriceTable($tables[0], $xpath, $doc);
        }

        $noteHeading = $this->findDirectParagraphStartingWith($body, $xpath, 'Ghi chú');
        if ($noteHeading instanceof DOMElement) {
            $this->replaceParagraphsAfterHeadingUntilNextTable($noteHeading, $xpath, $this->noteLines($doc, $year));
        }

        if (isset($tables[1])) {
            $this->hydrateDirectorSignatureTable($tables[1], $xpath, $doc, $staffName, $staffPhone, $staffEmail);
        }
    }

    private function hydrateInfoTable(
        DOMElement $table,
        DOMXPath $xpath,
        QuotationDocument $doc,
        string $customerName,
        string $staffName,
        string $staffPhone,
        string $staffEmail,
        string $pageCount
    ): void {
        $rows = $this->tableRows($table);

        if (! isset($rows[0], $rows[1])) {
            return;
        }

        $this->setCellParagraphTexts($rows[0], $xpath, 0, [
            'Kính gửi: Ban giám đốc',
            mb_strtoupper($customerName, 'UTF-8'),
        ]);
        $genderPrefix = $this->resolveStaffGenderPrefix($doc);
        $this->setCellParagraphTexts($rows[0], $xpath, 1, [
            ' Người gửi: '.($genderPrefix ? $genderPrefix.' ' : '').'**'.$staffName.'**',
            'Điện thoại: '.$staffPhone,
            'Email: '.$staffEmail,
        ]);
        $this->setCellParagraphTexts($rows[1], $xpath, 0, [
            'Người nhận: '.($doc->customer_contact ?: ''),
            'Địa chỉ: '.($doc->customer_address ?: $doc->work_location ?: ''),
        ]);

        $this->setCellParagraphTexts($rows[1], $xpath, 1, [
            'Báo giá số: '.$doc->document_number,
            'Ngày báo giá: '.($doc->date?->format('d/m/Y') ?? now()->format('d/m/Y')),
            'Số trang: '.$pageCount,
        ]);
    }

    private function hydrateSummaryTable(DOMElement $table, DOMXPath $xpath, QuotationDocument $doc): void
    {
        $rows = $this->tableRows($table);
        if (count($rows) < 6) {
            return;
        }

        $this->shadeRow($rows[0], self::TABLE_HEADER_FILL);

        $itemTemplate = $rows[1]->cloneNode(true);
        $subtotalTemplate = $rows[2]->cloneNode(true);
        $vatTemplate = $rows[3]->cloneNode(true);
        $totalTemplate = $rows[4]->cloneNode(true);
        $wordsTemplate = $rows[5]->cloneNode(true);

        $this->removeTableRowsAfter($table, 0);

        $summaryItems = $doc->items->where('item_type', 'summary')->values();
        foreach ($summaryItems as $index => $item) {
            $row = $itemTemplate->cloneNode(true);
            $this->setCellText($row, $xpath, 0, (string) ($index + 1));
            $this->setCellText($row, $xpath, 1, (string) $item->description);
            $this->setCellText($row, $xpath, 2, $item->unit ?: 'Hồ sơ');
            $this->setCellText($row, $xpath, 3, $this->formatSummaryQty((float) $item->quantity));
            $this->setCellText($row, $xpath, 4, $this->formatMoney((int) $item->amount));
            $table->appendChild($row);
        }

        $subtotalRow = $subtotalTemplate->cloneNode(true);
        $this->setCellText($subtotalRow, $xpath, 0, 'TỔNG CỘNG CHƯA VAT ');
        $this->setCellText($subtotalRow, $xpath, 1, $this->formatMoney($doc->subtotal));
        $table->appendChild($subtotalRow);

        if ((int) $doc->discount > 0) {
            $discountRow = $subtotalTemplate->cloneNode(true);
            $this->setCellText($discountRow, $xpath, 0, 'CHIẾT KHẤU');
            $this->setCellText($discountRow, $xpath, 1, '-'.$this->formatMoney($doc->discount));
            $table->appendChild($discountRow);
        }

        $vatRow = $vatTemplate->cloneNode(true);
        $this->setCellText($vatRow, $xpath, 0, 'VAT '.$doc->vat_rate.'%');
        $this->setCellText($vatRow, $xpath, 1, $this->formatMoney($doc->vat_amount));
        $table->appendChild($vatRow);

        $totalRow = $totalTemplate->cloneNode(true);
        $this->setCellText($totalRow, $xpath, 0, 'TỔNG THÀNH TIỀN ĐÃ VAT');
        $this->setCellText($totalRow, $xpath, 1, $this->formatMoney($doc->total));
        $table->appendChild($totalRow);

        $wordsRow = $wordsTemplate->cloneNode(true);
        $this->setCellText($wordsRow, $xpath, 0, 'Bằng chữ: “'.$this->numberToWords($doc->total).' đồng”');
        $table->appendChild($wordsRow);
    }

    private function hydrateDetailTable(DOMElement $table, DOMXPath $xpath, QuotationDocument $doc): void
    {
        $rows = $this->tableRows($table);
        if (count($rows) < 5) {
            return;
        }

        $this->shadeRow($rows[0], self::TABLE_HEADER_FILL);

        $groupTemplate = $rows[1]->cloneNode(true);
        $itemTemplate = $rows[2]->cloneNode(true);
        $totalBeforeTemplate = $this->findRowWithCellText($rows, $xpath, 1, 'Tổng trước VAT')?->cloneNode(true) ?: $itemTemplate->cloneNode(true);
        $vatTemplate = $this->findRowWithCellText($rows, $xpath, 1, 'VAT')?->cloneNode(true) ?: $itemTemplate->cloneNode(true);
        $totalAfterTemplate = $this->findRowWithCellText($rows, $xpath, 1, 'Tổng sau VAT')?->cloneNode(true) ?: $itemTemplate->cloneNode(true);

        $this->removeTableRowsAfter($table, 0);

        $groupedDetails = $doc->items
            ->where('item_type', 'detail')
            ->values()
            ->groupBy(fn ($item) => $item->group_name ?: 'CHI TIẾT');

        $lastGroupIndex = 0;
        foreach ($groupedDetails as $groupName => $items) {
            [$groupNo, $groupTitle] = $this->splitGroupName((string) $groupName);

            $groupRow = $groupTemplate->cloneNode(true);
            $this->setCellText($groupRow, $xpath, 0, $groupNo);
            $this->setCellText($groupRow, $xpath, 1, $groupTitle);
            for ($cellIndex = 2; $cellIndex < 6; $cellIndex++) {
                $this->setCellText($groupRow, $xpath, $cellIndex, '');
            }
            $table->appendChild($groupRow);

            $lineNo = 1;
            foreach ($items as $item) {
                $row = $itemTemplate->cloneNode(true);
                $this->setCellText($row, $xpath, 0, (string) $lineNo++);
                $this->setCellText($row, $xpath, 1, (string) $item->description);
                $this->setCellText($row, $xpath, 2, $item->unit ?: 'Mẫu');
                $this->setCellText($row, $xpath, 3, $this->formatMoney($item->unit_price));
                $this->setCellText($row, $xpath, 4, $this->formatDetailQty((float) $item->quantity));
                $this->setCellText($row, $xpath, 5, $this->formatMoney($item->amount));
                $table->appendChild($row);
            }

            $lastGroupIndex = max(0, $lineNo - 1);
        }

        $totalRows = [
            [$totalBeforeTemplate, 'Tổng trước VAT', $doc->subtotal],
            [$vatTemplate, 'VAT', $doc->vat_amount],
            [$totalAfterTemplate, 'Tổng sau VAT', $doc->total],
        ];

        foreach ($totalRows as $offset => [$template, $label, $amount]) {
            $row = $template->cloneNode(true);
            $this->setCellText($row, $xpath, 0, (string) ($lastGroupIndex + $offset + 1));
            $this->setCellText($row, $xpath, 1, $label);
            $this->setCellAlignment($row, 1, 'center');
            for ($cellIndex = 2; $cellIndex < 5; $cellIndex++) {
                $this->setCellText($row, $xpath, $cellIndex, '');
            }
            $this->setCellText($row, $xpath, 5, $this->formatMoney($amount));
            $this->setCellAlignment($row, 5, 'right');
            $table->appendChild($row);
        }
    }

    private function hydrateCoverSummaryTable(DOMElement $table, DOMXPath $xpath, QuotationDocument $doc): void
    {
        $rows = $this->tableRows($table);
        if (count($rows) < 2) {
            return;
        }

        $this->shadeRow($rows[0], self::TABLE_HEADER_FILL);

        $itemTemplate = $this->firstRowWithAtLeastCells($rows, 6, 1)?->cloneNode(true) ?: $rows[1]->cloneNode(true);
        $totalTemplate = $this->findRowContainingText($rows, $xpath, 'TỔNG')?->cloneNode(true) ?: $itemTemplate->cloneNode(true);
        $wordsTemplate = $this->findRowContainingText($rows, $xpath, 'Viết bằng chữ')?->cloneNode(true)
            ?: $this->findRowContainingText($rows, $xpath, 'Bằng chữ')?->cloneNode(true)
            ?: $itemTemplate->cloneNode(true);

        $this->removeTableRowsAfter($table, 0);

        $summaryItems = $doc->items->where('item_type', 'summary')->values();
        foreach ($summaryItems as $index => $item) {
            $row = $itemTemplate->cloneNode(true);
            $unitPrice = (int) ($item->unit_price ?: $item->amount);

            $this->setCellText($row, $xpath, 0, (string) ($index + 1));
            $this->setCellText($row, $xpath, 1, (string) $item->description);
            $this->setCellText($row, $xpath, 2, $item->unit ?: 'Hồ sơ');
            $this->setCellText($row, $xpath, 3, $this->formatSummaryQty((float) $item->quantity));
            $this->setCellText($row, $xpath, 4, $this->formatPlainMoney($unitPrice));
            $this->setCellText($row, $xpath, 5, $this->formatPlainMoney($item->amount));
            $table->appendChild($row);
        }

        $this->appendWideAmountRow($table, $xpath, $totalTemplate, 'TỔNG CỘNG CHƯA VAT', (int) $doc->subtotal);

        if ((int) $doc->discount > 0) {
            $this->appendWideAmountRow($table, $xpath, $totalTemplate, 'CHIẾT KHẤU', -1 * (int) $doc->discount);
        }

        if ((int) $doc->vat_amount > 0) {
            $this->appendWideAmountRow($table, $xpath, $totalTemplate, 'VAT '.$doc->vat_rate.'%', (int) $doc->vat_amount);
            $this->appendWideAmountRow($table, $xpath, $totalTemplate, 'TỔNG CỘNG ĐÃ VAT', (int) $doc->total);
        }

        $amountForWords = (int) ((int) $doc->vat_amount > 0 ? $doc->total : $doc->subtotal);
        $this->appendWordsRow($table, $xpath, $wordsTemplate, 'Viết bằng chữ: '.$this->numberToWords($amountForWords).' đồng./.');
    }

    private function hydrateFiveColumnSummaryTable(DOMElement $table, DOMXPath $xpath, QuotationDocument $doc): void
    {
        $rows = $this->tableRows($table);
        if (count($rows) < 2) {
            return;
        }

        $this->shadeRow($rows[0], self::TABLE_HEADER_FILL);

        $itemTemplate = $this->firstRowWithAtLeastCells($rows, 5, 1)?->cloneNode(true) ?: $rows[1]->cloneNode(true);
        $totalTemplate = $this->findRowContainingText($rows, $xpath, 'TỔNG')?->cloneNode(true) ?: $itemTemplate->cloneNode(true);
        $wordsTemplate = $this->findRowContainingText($rows, $xpath, 'Bằng chữ')?->cloneNode(true) ?: $itemTemplate->cloneNode(true);

        $this->removeTableRowsAfter($table, 0);

        $summaryItems = $doc->items->where('item_type', 'summary')->values();
        foreach ($summaryItems as $index => $item) {
            $row = $itemTemplate->cloneNode(true);
            $this->setCellText($row, $xpath, 0, (string) ($index + 1));
            $this->setCellText($row, $xpath, 1, (string) $item->description);
            $this->setCellText($row, $xpath, 2, $item->unit ?: 'Hồ sơ');
            $this->setCellText($row, $xpath, 3, $this->formatSummaryQty((float) $item->quantity));
            $this->setCellText($row, $xpath, 4, $this->formatPlainMoney($item->amount));
            $table->appendChild($row);
        }

        $this->appendWideAmountRow($table, $xpath, $totalTemplate, 'TỔNG CỘNG CHƯA VAT', (int) $doc->subtotal);

        if ((int) $doc->discount > 0) {
            $this->appendWideAmountRow($table, $xpath, $totalTemplate, 'CHIẾT KHẤU', -1 * (int) $doc->discount);
        }

        if ((int) $doc->vat_amount > 0) {
            $this->appendWideAmountRow($table, $xpath, $totalTemplate, 'VAT '.$doc->vat_rate.'%', (int) $doc->vat_amount);
            $this->appendWideAmountRow($table, $xpath, $totalTemplate, 'TỔNG CỘNG ĐÃ VAT', (int) $doc->total);
        }

        $amountForWords = (int) ((int) $doc->vat_amount > 0 ? $doc->total : $doc->subtotal);
        $this->appendWordsRow($table, $xpath, $wordsTemplate, '(Bằng chữ: '.$this->numberToWords($amountForWords).' đồng./.)');
    }

    private function hydrateTrialOperationDetailTable(DOMElement $table, DOMXPath $xpath, QuotationDocument $doc): void
    {
        $rows = $this->tableRows($table);
        if (count($rows) < 3) {
            return;
        }

        $this->shadeRow($rows[0], self::TABLE_HEADER_FILL);
        if (isset($rows[1])) {
            $this->shadeRow($rows[1], self::TABLE_HEADER_FILL);
        }

        $groupTemplate = $rows[2]->cloneNode(true);
        $itemTemplate = $this->firstRowWithAtLeastCells($rows, 8, 3)?->cloneNode(true)
            ?: $this->firstRowWithAtLeastCells($rows, 7, 3)?->cloneNode(true)
            ?: $rows[2]->cloneNode(true);

        $this->removeTableRowsAfter($table, 1);

        $detailItems = $doc->items->where('item_type', 'detail')->values();
        if ($detailItems->isEmpty()) {
            $detailItems = $doc->items->where('item_type', 'summary')->values();
        }

        $detailTotal = 0;
        foreach ($detailItems->groupBy(fn ($item) => $item->group_name ?: 'CHI PHÍ THỰC HIỆN') as $groupName => $items) {
            [$groupNo, $groupTitle] = $this->splitGroupName((string) $groupName);
            $groupTotal = (int) $items->sum('amount');
            $detailTotal += $groupTotal;

            $groupRow = $groupTemplate->cloneNode(true);
            $groupLastCell = max(0, count($this->rowCells($groupRow)) - 1);
            $this->setCellText($groupRow, $xpath, 0, $groupNo ?: '');
            $this->setCellText($groupRow, $xpath, 1, $groupTitle);
            $this->setCellText($groupRow, $xpath, $groupLastCell, $this->formatPlainMoney($groupTotal));
            $table->appendChild($groupRow);

            $lineNo = 1;
            foreach ($items as $item) {
                $row = $itemTemplate->cloneNode(true);
                $cellCount = count($this->rowCells($row));
                $frequency = (string) (isset($item->frequency) && (int) $item->frequency > 0 ? (int) $item->frequency : $this->frequencyFromNote($item->note));

                $this->setCellText($row, $xpath, 0, (string) $lineNo++);
                $this->setCellText($row, $xpath, 1, (string) $item->description);

                if ($cellCount >= 8) {
                    $this->setCellText($row, $xpath, 2, '');
                    $this->setCellText($row, $xpath, 3, $item->unit ?: 'Mẫu');
                    $this->setCellText($row, $xpath, 4, $this->formatDetailQty((float) $item->quantity));
                    $this->setCellText($row, $xpath, 5, $frequency);
                    $this->setCellText($row, $xpath, 6, $this->formatPlainMoney($item->unit_price));
                    $this->setCellText($row, $xpath, 7, $this->formatPlainMoney($item->amount));
                } else {
                    $this->setCellText($row, $xpath, 2, $item->unit ?: 'Mẫu');
                    $this->setCellText($row, $xpath, 3, $this->formatDetailQty((float) $item->quantity));
                    $this->setCellText($row, $xpath, 4, $frequency);
                    $this->setCellText($row, $xpath, 5, $this->formatPlainMoney($item->unit_price));
                    $this->setCellText($row, $xpath, 6, $this->formatPlainMoney($item->amount));
                }

                $table->appendChild($row);
            }
        }

        $totalRow = $groupTemplate->cloneNode(true);
        $totalLastCell = max(0, count($this->rowCells($totalRow)) - 1);
        $this->setCellText($totalRow, $xpath, 0, '');
        $this->setCellText($totalRow, $xpath, 1, 'TỔNG CỘNG CHƯA VAT');
        $this->setCellText($totalRow, $xpath, $totalLastCell, $this->formatPlainMoney($detailTotal ?: $doc->subtotal));
        $table->appendChild($totalRow);
    }

    private function hydrateScheduleTable(DOMElement $table, DOMXPath $xpath, QuotationDocument $doc): void
    {
        $scheduleItems = $doc->items
            ->where('item_type', 'detail')
            ->filter(fn ($item) => ! str_contains(mb_strtoupper((string) $item->group_name, 'UTF-8'), 'TÀI LIỆU'))
            ->values();

        if ($scheduleItems->isEmpty()) {
            return;
        }

        $rows = $this->tableRows($table);
        if (count($rows) < 2) {
            return;
        }

        $this->shadeRow($rows[0], self::TABLE_HEADER_FILL);

        $itemTemplate = $this->firstRowWithAtLeastCells($rows, 4, 1)?->cloneNode(true) ?: $rows[1]->cloneNode(true);
        $this->removeTableRowsAfter($table, 0);

        foreach ($scheduleItems as $index => $item) {
            $row = $itemTemplate->cloneNode(true);
            $this->setCellText($row, $xpath, 0, (string) ($index + 1));
            $this->setCellText($row, $xpath, 1, (string) $item->description);
            $this->setCellText($row, $xpath, 2, $this->durationTextForItem($item));
            $this->setCellText($row, $xpath, 3, (string) $item->note);
            $table->appendChild($row);
        }
    }

    private function hydrateRequiredDocumentsTable(DOMElement $table, DOMXPath $xpath, QuotationDocument $doc): void
    {
        $documentItems = $doc->items
            ->where('item_type', 'detail')
            ->filter(fn ($item) => str_contains(mb_strtoupper((string) $item->group_name, 'UTF-8'), 'TÀI LIỆU'))
            ->values();

        if ($documentItems->isEmpty()) {
            return;
        }

        $rows = $this->tableRows($table);
        if (count($rows) < 2) {
            return;
        }

        $this->shadeRow($rows[0], self::TABLE_HEADER_FILL);

        $itemTemplate = $this->firstRowWithAtLeastCells($rows, 5, 1)?->cloneNode(true) ?: $rows[1]->cloneNode(true);
        $this->removeTableRowsAfter($table, 0);

        foreach ($documentItems as $index => $item) {
            $row = $itemTemplate->cloneNode(true);
            $this->setCellText($row, $xpath, 0, (string) ($index + 1));
            $this->setCellText($row, $xpath, 1, (string) $item->description);
            $this->setCellText($row, $xpath, 2, $this->formatDetailQty((float) $item->quantity));
            $this->setCellText($row, $xpath, 3, $item->unit ?: 'Bản copy');
            $this->setCellText($row, $xpath, 4, (string) $item->note);
            $table->appendChild($row);
        }
    }

    private function hydrateFrequencyDetailTable(DOMElement $table, DOMXPath $xpath, QuotationDocument $doc): void
    {
        $rows = $this->tableRows($table);
        if (count($rows) < 2) {
            return;
        }

        $this->shadeRow($rows[0], self::TABLE_HEADER_FILL);

        $groupTemplate = $rows[1]->cloneNode(true);
        $itemTemplate = $this->firstRowWithAtLeastCells($rows, 7, 1)?->cloneNode(true) ?: $rows[1]->cloneNode(true);
        $this->removeTableRowsAfter($table, 0);

        $detailItems = $doc->items->where('item_type', 'detail')->values();
        if ($detailItems->isEmpty()) {
            $detailItems = $doc->items->where('item_type', 'summary')->values();
        }

        $groupedDetails = $detailItems->groupBy(fn ($item) => $item->group_name ?: 'CHI TIẾT');
        $runningIndex = 1;
        $detailTotal = 0;

        foreach ($groupedDetails as $groupName => $items) {
            [$groupNo, $groupTitle] = $this->splitGroupName((string) $groupName);
            $groupTotal = (int) $items->sum('amount');
            $detailTotal += $groupTotal;

            $groupRow = $groupTemplate->cloneNode(true);
            $this->setCellText($groupRow, $xpath, 0, $groupNo);
            $this->setCellText($groupRow, $xpath, 1, $groupTitle);
            $this->setCellText($groupRow, $xpath, max(2, count($this->rowCells($groupRow)) - 1), $this->formatPlainMoney($groupTotal));
            $table->appendChild($groupRow);

            foreach ($items as $item) {
                $row = $itemTemplate->cloneNode(true);
                $frequency = (string) (isset($item->frequency) && (int) $item->frequency > 0 ? (int) $item->frequency : $this->frequencyFromNote($item->note));

                $this->setCellText($row, $xpath, 0, (string) $runningIndex++);
                $this->setCellText($row, $xpath, 1, (string) $item->description);
                $this->setCellText($row, $xpath, 2, $item->unit ?: 'Mẫu');
                $this->setCellText($row, $xpath, 3, $this->formatDetailQty((float) $item->quantity));
                $this->setCellText($row, $xpath, 4, $this->formatPlainMoney($item->unit_price));
                $this->setCellText($row, $xpath, 5, $frequency);
                $this->setCellText($row, $xpath, 6, $this->formatPlainMoney($item->amount));
                $table->appendChild($row);
            }
        }

        $totalRow = $itemTemplate->cloneNode(true);
        $this->setCellText($totalRow, $xpath, 0, '');
        $this->setCellText($totalRow, $xpath, 1, 'TỔNG CỘNG');
        for ($cellIndex = 2; $cellIndex < 6; $cellIndex++) {
            $this->setCellText($totalRow, $xpath, $cellIndex, '');
        }
        $this->setCellText($totalRow, $xpath, 6, $this->formatPlainMoney($detailTotal ?: $doc->subtotal));
        $table->appendChild($totalRow);
    }

    private function hydratePeriodSummaryTable(DOMElement $table, DOMXPath $xpath, QuotationDocument $doc): void
    {
        $rows = $this->tableRows($table);
        if (count($rows) < 3) {
            return;
        }

        $this->shadeRow($rows[0], self::TABLE_HEADER_FILL);
        if (isset($rows[1])) {
            $this->shadeRow($rows[1], self::TABLE_HEADER_FILL);
        }

        $itemTemplate = $rows[2]->cloneNode(true);
        $totalTemplate = end($rows)->cloneNode(true);
        $this->removeTableRowsAfter($table, 1);

        $summaryItems = $doc->items->where('item_type', 'summary')->values();
        foreach ($summaryItems as $index => $item) {
            $row = $itemTemplate->cloneNode(true);
            $amount = (int) $item->amount;

            $this->setCellText($row, $xpath, 0, (string) ($index + 1));
            $this->setCellText($row, $xpath, 1, (string) $item->description);
            $this->setCellText($row, $xpath, 2, $this->formatPlainMoney($amount));
            $this->setCellText($row, $xpath, 3, $this->formatPlainMoney($amount));
            $table->appendChild($row);
        }

        $totalRow = $totalTemplate->cloneNode(true);
        $totalAmount = $this->formatPlainMoney($doc->subtotal);
        $totalCellCount = count($this->rowCells($totalRow));
        $this->setCellText($totalRow, $xpath, 0, 'TỔNG CỘNG');
        if ($totalCellCount >= 4) {
            $this->setCellText($totalRow, $xpath, 1, '');
            $this->setCellText($totalRow, $xpath, 2, $totalAmount);
            $this->setCellText($totalRow, $xpath, 3, $totalAmount);
        } elseif ($totalCellCount >= 3) {
            $this->setCellText($totalRow, $xpath, 1, $totalAmount);
            $this->setCellText($totalRow, $xpath, 2, $totalAmount);
        }
        $table->appendChild($totalRow);
    }

    private function hydrateShortPriceTable(DOMElement $table, DOMXPath $xpath, QuotationDocument $doc): void
    {
        $rows = $this->tableRows($table);
        if (count($rows) < 2) {
            return;
        }

        $this->shadeRow($rows[0], self::TABLE_HEADER_FILL);

        $itemTemplate = $rows[1]->cloneNode(true);
        $this->removeTableRowsAfter($table, 0);

        $summaryItems = $doc->items->where('item_type', 'summary')->values();
        foreach ($summaryItems as $index => $item) {
            $row = $itemTemplate->cloneNode(true);
            $lastCell = max(0, count($this->rowCells($row)) - 1);

            $this->setCellText($row, $xpath, 0, (string) ($index + 1));
            $this->setCellText($row, $xpath, 1, (string) $item->description);
            $this->setCellText($row, $xpath, 2, $item->unit ?: 'Gói');
            $this->setCellText($row, $xpath, $lastCell, $this->formatPlainMoney($item->amount));
            $table->appendChild($row);
        }

        if ($summaryItems->count() > 1 || (int) $doc->discount > 0 || (int) $doc->vat_amount > 0) {
            $this->appendShortTotalRow($table, $xpath, $itemTemplate, 'TỔNG CỘNG CHƯA VAT', (int) $doc->subtotal);

            if ((int) $doc->discount > 0) {
                $this->appendShortTotalRow($table, $xpath, $itemTemplate, 'CHIẾT KHẤU', -1 * (int) $doc->discount);
            }

            if ((int) $doc->vat_amount > 0) {
                $this->appendShortTotalRow($table, $xpath, $itemTemplate, 'VAT '.$doc->vat_rate.'%', (int) $doc->vat_amount);
            }

            $this->appendShortTotalRow($table, $xpath, $itemTemplate, 'TỔNG CỘNG ĐÃ VAT', (int) $doc->total);
        }
    }

    private function appendShortTotalRow(
        DOMElement $table,
        DOMXPath $xpath,
        DOMElement $template,
        string $label,
        int $amount
    ): void {
        $row = $template->cloneNode(true);
        $cellCount = count($this->rowCells($row));
        $lastCell = max(0, $cellCount - 1);

        $this->setCellText($row, $xpath, 0, '');
        $this->setCellText($row, $xpath, 1, $label);
        for ($cellIndex = 2; $cellIndex < $lastCell; $cellIndex++) {
            $this->setCellText($row, $xpath, $cellIndex, '');
        }
        $this->setCellText($row, $xpath, $lastCell, $this->formatPlainMoney($amount));
        $table->appendChild($row);
    }

    private function appendWideAmountRow(
        DOMElement $table,
        DOMXPath $xpath,
        DOMElement $template,
        string $label,
        int $amount
    ): void {
        $row = $template->cloneNode(true);
        $cellCount = count($this->rowCells($row));
        $lastCell = max(0, $cellCount - 1);

        if ($cellCount <= 2) {
            $this->setCellText($row, $xpath, 0, $label);
            $this->setCellText($row, $xpath, 1, $this->formatPlainMoney($amount));
        } else {
            $this->setCellText($row, $xpath, 0, '');
            $this->setCellText($row, $xpath, 1, $label);
            for ($cellIndex = 2; $cellIndex < $lastCell; $cellIndex++) {
                $this->setCellText($row, $xpath, $cellIndex, '');
            }
            $this->setCellText($row, $xpath, $lastCell, $this->formatPlainMoney($amount));
        }

        $table->appendChild($row);
    }

    private function appendWordsRow(DOMElement $table, DOMXPath $xpath, DOMElement $template, string $text): void
    {
        $row = $template->cloneNode(true);
        $cellCount = count($this->rowCells($row));

        $this->setCellText($row, $xpath, 0, $text);
        for ($cellIndex = 1; $cellIndex < $cellCount; $cellIndex++) {
            $this->setCellText($row, $xpath, $cellIndex, '');
        }

        $table->appendChild($row);
    }

    private function hydrateLaborCostTable(DOMElement $table, DOMXPath $xpath, QuotationDocument $doc): void
    {
        $rows = $this->tableRows($table);
        if (count($rows) < 3) {
            return;
        }

        $this->shadeRow($rows[0], self::TABLE_HEADER_FILL);

        $groupTemplate = $rows[1]->cloneNode(true);
        $itemTemplate = $this->firstRowWithAtLeastCells($rows, 5, 2)?->cloneNode(true) ?: $rows[2]->cloneNode(true);
        $this->removeTableRowsAfter($table, 0);

        $detailItems = $doc->items->where('item_type', 'detail')->values();
        if ($detailItems->isEmpty()) {
            $detailItems = $doc->items->where('item_type', 'summary')->values();
        }

        $lineNo = 1;
        foreach ($detailItems->groupBy(fn ($item) => $item->group_name ?: 'HẠNG MỤC') as $groupName => $items) {
            [$groupNo, $groupTitle] = $this->splitGroupName((string) $groupName);

            $groupRow = $groupTemplate->cloneNode(true);
            $this->setCellText($groupRow, $xpath, 0, $groupNo ?: '');
            $this->setCellText($groupRow, $xpath, 1, $groupTitle);
            $table->appendChild($groupRow);

            foreach ($items as $item) {
                $row = $itemTemplate->cloneNode(true);
                $this->setCellText($row, $xpath, 0, (string) $lineNo++);
                $this->setCellText($row, $xpath, 1, (string) $item->description);
                $this->setCellText($row, $xpath, 2, $this->formatDetailQty((float) $item->quantity));
                $this->setCellText($row, $xpath, 3, $this->formatPlainMoney($item->unit_price));
                $this->setCellText($row, $xpath, 4, $this->formatPlainMoney($item->amount));
                $table->appendChild($row);
            }
        }

        $this->appendLaborCostTotalRow($table, $xpath, $itemTemplate, 'TỔNG CỘNG CHƯA VAT', (int) $doc->subtotal);
        if ((int) $doc->vat_amount > 0) {
            $this->appendLaborCostTotalRow($table, $xpath, $itemTemplate, 'VAT '.$doc->vat_rate.'%', (int) $doc->vat_amount);
        }
        $this->appendLaborCostTotalRow($table, $xpath, $itemTemplate, 'TỔNG CỘNG', (int) $doc->total);
    }

    private function appendLaborCostTotalRow(DOMElement $table, DOMXPath $xpath, DOMElement $template, string $label, int $amount): void
    {
        $row = $template->cloneNode(true);
        $this->setCellText($row, $xpath, 0, '');
        $this->setCellText($row, $xpath, 1, $label);
        $this->setCellText($row, $xpath, 2, '');
        $this->setCellText($row, $xpath, 3, '');
        $this->setCellText($row, $xpath, 4, $this->formatPlainMoney($amount));
        $table->appendChild($row);
    }

    private function hydrateLaborMatrixTable(DOMElement $table, DOMXPath $xpath, QuotationDocument $doc): void
    {
        $rows = $this->tableRows($table);
        if (count($rows) < 4) {
            return;
        }

        $this->shadeRow($rows[0], self::TABLE_HEADER_FILL);
        $this->shadeRow($rows[1], self::TABLE_HEADER_FILL);
        $this->shadeRow($rows[2], self::TABLE_HEADER_FILL);

        $itemTemplate = $this->firstRowWithAtLeastCells($rows, 16, 3)?->cloneNode(true);
        if (! $itemTemplate instanceof DOMElement) {
            return;
        }

        $this->removeTableRowsAfter($table, 2);
        $matrixRows = $this->laborMatrixRows($doc);
        $totals = array_fill(0, 16, 0);

        foreach ($matrixRows as $index => $matrixRow) {
            $row = $itemTemplate->cloneNode(true);
            $values = $this->laborMatrixValues($matrixRow);

            $this->setCellText($row, $xpath, 0, (string) ($index + 1));
            $this->setCellText($row, $xpath, 1, (string) ($matrixRow['job_title'] ?? ''));
            for ($cellIndex = 2; $cellIndex < 16; $cellIndex++) {
                $this->setCellText($row, $xpath, $cellIndex, (string) $values[$cellIndex]);
                $totals[$cellIndex] += (int) $values[$cellIndex];
            }
            $table->appendChild($row);
        }

        if ($matrixRows !== []) {
            $totalRow = $itemTemplate->cloneNode(true);
            $this->setCellText($totalRow, $xpath, 0, 'TỔNG CỘNG');
            $this->setCellText($totalRow, $xpath, 1, '');
            for ($cellIndex = 2; $cellIndex < 16; $cellIndex++) {
                $this->setCellText($totalRow, $xpath, $cellIndex, (string) $totals[$cellIndex]);
            }
            $table->appendChild($totalRow);
        }
    }

    private function laborMatrixRows(QuotationDocument $doc): array
    {
        $section = $doc->sections->firstWhere('section_key', 'plld_matrix');
        if (! $section) {
            return [];
        }

        return $section->rows
            ->map(fn ($row) => array_merge(['job_title' => $row->description], $row->columns ?? []))
            ->values()
            ->all();
    }

    private function laborMatrixValues(array $matrixRow): array
    {
        $metricKeys = [
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
        ];

        $values = [
            2 => (int) ($matrixRow['employee_count'] ?? 0),
            3 => (int) ($matrixRow['assessment_count'] ?? 0),
        ];

        $cellIndex = 4;
        $total = 0;
        foreach ($metricKeys as $key) {
            $value = (int) ($matrixRow[$key] ?? 0);
            $values[$cellIndex++] = $value;
            $total += $value;
        }
        $values[15] = $total;

        return $values;
    }

    private function hydrateSignatureTable(
        DOMElement $table,
        DOMXPath $xpath,
        QuotationDocument $doc,
        string $staffName,
        string $staffPhone,
        string $staffEmail
    ): void {
        $rows = $this->tableRows($table);
        if (! isset($rows[0])) {
            return;
        }

        $staffTitle = $this->resolveStaffTitle($doc, true);

        $this->setCellParagraphTexts($rows[0], $xpath, 0, [
            'Thông tin liên hệ: ',
            $staffName.' – '.$staffTitle,
            'Số điện thoại: '.$staffPhone,
            'Email: '.$staffEmail.' ',
        ]);
        $this->setCellParagraphTexts($rows[0], $xpath, 1, [
            'Người báo giá',
            '',
            '',
            '     '.$staffName,
        ]);
    }

    private function hydrateDirectorSignatureTable(
        DOMElement $table,
        DOMXPath $xpath,
        QuotationDocument $doc,
        string $staffName,
        string $staffPhone,
        string $staffEmail
    ): void {
        $rows = $this->tableRows($table);
        if (! isset($rows[0])) {
            return;
        }

        $staffTitle = $this->resolveStaffTitle($doc, false);

        $this->setCellParagraphTexts($rows[0], $xpath, 0, [
            'Mọi chi tiết xin vui lòng liên hệ:',
            $staffName.' - '.$staffTitle,
            'Điện thoại: '.$staffPhone,
            'Email: '.$staffEmail,
        ]);

        $this->setCellParagraphTexts($rows[0], $xpath, 1, [
            'TỔNG GIÁM ĐỐC',
            '',
            '',
            'ĐỖ HUY LỰC',
        ]);
    }

    private function generateFallbackPdfContent(QuotationDocument $doc, string $pageCount): string
    {
        $this->ensureDirectory(storage_path('fonts'));

        $year = $doc->date?->format('Y') ?? now()->format('Y');
        [$staffName, $staffPhone, $staffEmail] = $this->resolveStaffDetails($doc);

        $pdf = Pdf::loadView('admin.quotations.quotation-document-pdf', [
            'doc' => $doc,
            'company' => [
                'name' => self::COMPANY_NAME,
                'address' => self::COMPANY_ADDRESS,
                'phone' => self::COMPANY_PHONE,
                'email' => self::COMPANY_EMAIL,
                'tax_code' => self::COMPANY_TAX_CODE,
            ],
            'amountInWords' => $this->numberToWords($doc->total),
            'serviceTitle' => $this->displayServiceType($doc),
            'year' => $year,
            'noteLines' => $this->noteLines($doc, $year),
            'staffName' => $staffName,
            'staffPhone' => $staffPhone,
            'staffEmail' => $staffEmail,
            'staffGenderPrefix' => $this->resolveStaffGenderPrefix($doc),
            'staffTitle' => $this->resolveStaffTitle($doc, true),
            'pageCount' => $pageCount,
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('defaultFont', 'DejaVu Serif');

        return $pdf->output();
    }

    private function estimatePageCount(QuotationDocument $doc): string
    {
        try {
            $year = $doc->date?->format('Y') ?? now()->format('Y');
            $baselineCount = $this->getTemplatePageCount($doc);
            [$staffName, $staffPhone, $staffEmail] = $this->resolveStaffDetails($doc);

            $pdf = Pdf::loadView('admin.quotations.quotation-document-pdf', [
                'doc' => $doc,
                'company' => [
                    'name' => self::COMPANY_NAME,
                    'address' => self::COMPANY_ADDRESS,
                    'phone' => self::COMPANY_PHONE,
                    'email' => self::COMPANY_EMAIL,
                    'tax_code' => self::COMPANY_TAX_CODE,
                ],
                'amountInWords' => $this->numberToWords($doc->total),
                'serviceTitle' => $this->displayServiceType($doc),
                'year' => $year,
                'noteLines' => $this->noteLines($doc, $year),
                'staffName' => $staffName,
                'staffPhone' => $staffPhone,
                'staffEmail' => $staffEmail,
                'staffGenderPrefix' => $this->resolveStaffGenderPrefix($doc),
                'staffTitle' => $this->resolveStaffTitle($doc, true),
                'pageCount' => $baselineCount,
            ]);

            $template = QuotationTemplateCatalog::find($doc->template_key ?? null);
            $orientation = $template['orientation'] ?? 'portrait';
            $pdf->setPaper('A4', $orientation);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('defaultFont', 'DejaVu Serif');

            $output = $pdf->output();
            $pages = 0;
            if (preg_match_all('/\/Type\s*\/Page\b/', $output, $matches)) {
                $pages = count($matches[0]);
            }
            if ($pages > 0) {
                return str_pad((string) $pages, 2, '0', STR_PAD_LEFT);
            }
        } catch (Throwable $e) {
            Log::debug('Error estimating page count: '.$e->getMessage());
        }

        return $this->getTemplatePageCount($doc);
    }

    private function detectDocxPageCount(string $docxPath): ?string
    {
        if (PHP_OS_FAMILY !== 'Windows' || ! is_file($docxPath)) {
            return null;
        }

        $scriptPath = $docxPath.'.page-count.ps1';
        $script = <<<'POWERSHELL'
param([string]$DocxPath)
$ErrorActionPreference = 'Stop'
$word = $null
$document = $null
try {
    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $word.AutomationSecurity = 3
    $word.Options.SaveNormalPrompt = $false
    $word.Options.ConfirmConversions = $false
    $document = $word.Documents.Open($DocxPath, $false, $true, $false)
    $document.Repaginate()
    $pages = $document.ComputeStatistics(2)
    Write-Output $pages
} finally {
    if ($null -ne $document) { $document.Close($false) | Out-Null }
    if ($null -ne $word) { $word.Quit() | Out-Null }
}
POWERSHELL;

        file_put_contents($scriptPath, $script);

        try {
            $process = new Process([
                'powershell.exe',
                '-NoProfile',
                '-NonInteractive',
                '-ExecutionPolicy',
                'Bypass',
                '-File',
                $scriptPath,
                $docxPath,
            ]);
            $process->setTimeout((int) env('QUOTATION_WORD_PAGE_COUNT_TIMEOUT', 30));
            $process->run();

            if (! $process->isSuccessful()) {
                return null;
            }

            $pages = (int) trim($process->getOutput());

            return $pages > 0 ? str_pad((string) $pages, 2, '0', STR_PAD_LEFT) : null;
        } catch (Throwable $e) {
            Log::debug('Không thể tính số trang DOCX bằng Microsoft Word.', ['error' => $e->getMessage()]);

            return null;
        } finally {
            @unlink($scriptPath);
        }
    }

    private function replaceDocxPageCount(string $docxPath, string $pageCount): void
    {
        $zip = new ZipArchive;
        if ($zip->open($docxPath) !== true) {
            return;
        }

        try {
            $documentXml = $zip->getFromName('word/document.xml');
            if ($documentXml === false) {
                return;
            }

            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = false;
            $dom->loadXML($documentXml);

            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('w', self::WORD_NS);

            foreach ($xpath->query('//w:p') as $paragraph) {
                if (! $paragraph instanceof DOMElement) {
                    continue;
                }

                if (preg_match('/Số\s*trang:/ui', $this->nodeText($paragraph, $xpath)) !== 1) {
                    continue;
                }

                $this->setParagraphText($paragraph, $xpath, 'Số trang: '.$pageCount);
                $zip->addFromString('word/document.xml', $dom->saveXML());

                return;
            }
        } finally {
            $zip->close();
        }
    }

    private function convertDocxToPdf(string $docxPath, string $pdfPath): bool
    {
        if ($this->convertWithLibreOffice($docxPath, $pdfPath)) {
            return true;
        }

        if (filter_var(env('QUOTATION_ENABLE_WORD_CONVERTER', false), FILTER_VALIDATE_BOOLEAN)) {
            return $this->convertWithWordPowerShell($docxPath, $pdfPath);
        }

        return false;
    }

    private function convertWithLibreOffice(string $docxPath, string $pdfPath): bool
    {
        $candidates = array_values(array_filter([
            env('LIBREOFFICE_PATH'),
            'soffice',
            'C:\Program Files\LibreOffice\program\soffice.exe',
            'C:\Program Files (x86)\LibreOffice\program\soffice.exe',
        ]));

        foreach ($candidates as $candidate) {
            if ($candidate !== 'soffice' && ! is_file($candidate)) {
                continue;
            }

            $process = new Process([
                $candidate,
                '--headless',
                '--convert-to',
                'pdf',
                '--outdir',
                dirname($pdfPath),
                $docxPath,
            ]);
            $process->setTimeout(90);

            try {
                $process->run();
            } catch (Throwable $e) {
                Log::debug('Không thể chuyển DOCX sang PDF bằng LibreOffice.', ['error' => $e->getMessage()]);

                continue;
            }

            $convertedPath = dirname($pdfPath).DIRECTORY_SEPARATOR.pathinfo($docxPath, PATHINFO_FILENAME).'.pdf';
            if ($process->isSuccessful() && is_file($convertedPath)) {
                if ($convertedPath !== $pdfPath) {
                    @rename($convertedPath, $pdfPath);
                }

                return is_file($pdfPath);
            }
        }

        return false;
    }

    private function convertWithWordPowerShell(string $docxPath, string $pdfPath): bool
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return false;
        }

        $scriptPath = $pdfPath.'.convert.ps1';
        $script = <<<'POWERSHELL'
param([string]$DocxPath, [string]$PdfPath)
$ErrorActionPreference = 'Stop'
$word = $null
$document = $null
try {
    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $word.AutomationSecurity = 3
    $word.Options.SaveNormalPrompt = $false
    $word.Options.ConfirmConversions = $false
    $document = $word.Documents.Open($DocxPath, $false, $true, $false)
    $document.ExportAsFixedFormat($PdfPath, 17)
} finally {
    if ($null -ne $document) { $document.Close($false) | Out-Null }
    if ($null -ne $word) { $word.Quit() | Out-Null }
}
POWERSHELL;

        file_put_contents($scriptPath, $script);

        try {
            $process = new Process([
                'powershell.exe',
                '-NoProfile',
                '-NonInteractive',
                '-ExecutionPolicy',
                'Bypass',
                '-File',
                $scriptPath,
                $docxPath,
                $pdfPath,
            ]);
            $process->setTimeout((int) env('QUOTATION_WORD_PDF_TIMEOUT', 60));
            $process->run();

            return $process->isSuccessful() && is_file($pdfPath);
        } catch (Throwable $e) {
            Log::debug('Không thể chuyển DOCX sang PDF bằng Microsoft Word.', ['error' => $e->getMessage()]);

            return false;
        } finally {
            @unlink($scriptPath);
        }
    }

    private function setDirectParagraphStartingWith(DOMElement $parent, DOMXPath $xpath, string $needle, string $text): void
    {
        $paragraph = $this->findDirectParagraphStartingWith($parent, $xpath, $needle);
        if ($paragraph instanceof DOMElement) {
            $this->setParagraphText($paragraph, $xpath, $text);
        }
    }

    private function removeDirectParagraphStartingWith(DOMElement $parent, DOMXPath $xpath, string $needle): void
    {
        $paragraph = $this->findDirectParagraphStartingWith($parent, $xpath, $needle);
        if ($paragraph instanceof DOMElement) {
            $this->removeNode($paragraph);
        }
    }

    private function removeNode(DOMNode $node): void
    {
        if ($node->parentNode instanceof DOMNode) {
            $node->parentNode->removeChild($node);
        }
    }

    private function findDirectParagraphStartingWith(DOMElement $parent, DOMXPath $xpath, string $needle): ?DOMElement
    {
        foreach ($this->directChildElements($parent, 'p') as $paragraph) {
            if (str_starts_with(trim($this->nodeText($paragraph, $xpath)), $needle)) {
                return $paragraph;
            }
        }

        return null;
    }

    private function replaceParagraphsAfterHeadingUntilNextTable(DOMElement $heading, DOMXPath $xpath, array $lines): void
    {
        $existing = [];
        $stopNode = null;

        for ($node = $heading->nextSibling; $node instanceof DOMNode; $node = $node->nextSibling) {
            if ($node instanceof DOMElement && $node->localName === 'tbl') {
                $stopNode = $node;
                break;
            }

            if ($node instanceof DOMElement && $node->localName === 'p') {
                $existing[] = $node;
            }
        }

        if ($existing === []) {
            return;
        }

        $parent = $heading->parentNode;
        $template = $existing[0];

        foreach ($lines as $index => $line) {
            if (! isset($existing[$index])) {
                $newParagraph = $template->cloneNode(true);
                $parent->insertBefore($newParagraph, $stopNode);
                $existing[$index] = $newParagraph;
            }

            $this->setParagraphText($existing[$index], $xpath, $line);
        }

        for ($index = count($lines); $index < count($existing); $index++) {
            $parent->removeChild($existing[$index]);
        }
    }

    private function setCellText(DOMElement $row, DOMXPath $xpath, int $cellIndex, string $text): void
    {
        $this->setCellParagraphTexts($row, $xpath, $cellIndex, [$text]);
    }

    private function setCellAlignment(DOMElement $row, int $cellIndex, string $align = 'center'): void
    {
        $cells = $this->rowCells($row);
        if (! isset($cells[$cellIndex])) {
            return;
        }

        foreach ($this->directChildElements($cells[$cellIndex], 'p') as $paragraph) {
            $this->setParagraphAlignment($paragraph, $align);
        }
    }

    private function setParagraphAlignment(DOMElement $paragraph, string $align = 'center'): void
    {
        $dom = $paragraph->ownerDocument;
        $pPr = $this->directChildElements($paragraph, 'pPr')[0] ?? null;
        if (! $pPr instanceof DOMElement) {
            $pPr = $dom->createElementNS(self::WORD_NS, 'w:pPr');
            $paragraph->insertBefore($pPr, $paragraph->firstChild);
        }

        $jc = $this->directChildElements($pPr, 'jc')[0] ?? null;
        if (! $jc instanceof DOMElement) {
            $jc = $dom->createElementNS(self::WORD_NS, 'w:jc');
            $pPr->appendChild($jc);
        }

        $jc->setAttributeNS(self::WORD_NS, 'w:val', $align);
    }

    private function setCellParagraphTexts(DOMElement $row, DOMXPath $xpath, int $cellIndex, array $texts): void
    {
        $cells = $this->rowCells($row);
        if (! isset($cells[$cellIndex])) {
            return;
        }

        $cell = $cells[$cellIndex];
        $paragraphs = $this->directChildElements($cell, 'p');

        if ($paragraphs === []) {
            $paragraphs[] = $this->createParagraph($cell->ownerDocument);
            $cell->appendChild($paragraphs[0]);
        }

        $template = $paragraphs[0];
        foreach ($texts as $index => $text) {
            if (! isset($paragraphs[$index])) {
                $paragraphs[$index] = $template->cloneNode(true);
                $cell->appendChild($paragraphs[$index]);
            }

            $this->setParagraphText($paragraphs[$index], $xpath, $text);
        }

        for ($index = count($texts); $index < count($paragraphs); $index++) {
            $cell->removeChild($paragraphs[$index]);
        }
    }

    private function setParagraphText(DOMElement $paragraph, DOMXPath $xpath, string $text): void
    {
        // 1. Detect if the original paragraph has any underlined runs
        $hasUnderline = $xpath->query('.//w:r[w:rPr/w:u]', $paragraph)->length > 0;

        // 2. Check if the text contains a label-value pattern (contains ':')
        $parts = explode(':', $text, 2);

        $hasBoldMarker = str_contains($text, '**');

        // If the paragraph has underline or contains bold markers, we rebuild its runs
        if (($hasUnderline && count($parts) === 2) || $hasBoldMarker) {
            $segments = [];
            if ($hasUnderline && count($parts) === 2) {
                $labelText = $parts[0].':';
                $valueText = $parts[1];

                $segments[] = [
                    'text' => $labelText,
                    'underline' => true,
                    'bold' => false,
                ];

                $valueSegments = $this->parseBoldSegments($valueText);
                foreach ($valueSegments as $seg) {
                    $segments[] = [
                        'text' => $seg['text'],
                        'underline' => false,
                        'bold' => $seg['bold'],
                    ];
                }
            } else {
                $boldSegments = $this->parseBoldSegments($text);
                foreach ($boldSegments as $seg) {
                    $segments[] = [
                        'text' => $seg['text'],
                        'underline' => false,
                        'bold' => $seg['bold'],
                    ];
                }
            }

            if ($segments === []) {
                $segments[] = [
                    'text' => '',
                    'underline' => false,
                    'bold' => false,
                ];
            }

            // Find the first run to copy its run properties (rPr)
            $firstRun = $this->directChildElements($paragraph, 'r')[0] ?? null;
            $rPrTemplate = null;
            if ($firstRun instanceof DOMElement) {
                $rPrTemplate = $this->directChildElements($firstRun, 'rPr')[0] ?? null;
            }

            // Let's remove all existing children of the paragraph except paragraph properties (pPr)
            $toRemove = [];
            foreach ($paragraph->childNodes as $child) {
                if ($child instanceof DOMElement && $child->localName === 'pPr' && $child->namespaceURI === self::WORD_NS) {
                    continue;
                }
                $toRemove[] = $child;
            }
            foreach ($toRemove as $child) {
                $paragraph->removeChild($child);
            }

            $dom = $paragraph->ownerDocument;

            foreach ($segments as $segment) {
                $run = $dom->createElementNS(self::WORD_NS, 'w:r');
                $rPr = $rPrTemplate ? $rPrTemplate->cloneNode(true) : $dom->createElementNS(self::WORD_NS, 'w:rPr');

                // Handle underline
                $u = $this->directChildElements($rPr, 'u')[0] ?? null;
                if ($segment['underline']) {
                    if (! $u instanceof DOMElement) {
                        $u = $dom->createElementNS(self::WORD_NS, 'w:u');
                        $u->setAttributeNS(self::WORD_NS, 'w:val', 'single');
                        $rPr->appendChild($u);
                    }
                } else {
                    if ($u instanceof DOMElement) {
                        $rPr->removeChild($u);
                    }
                }

                // Handle bold (only if we have bold markers in the text, otherwise preserve original)
                if ($hasBoldMarker) {
                    $b = $this->directChildElements($rPr, 'b')[0] ?? null;
                    $bCs = $this->directChildElements($rPr, 'bCs')[0] ?? null;
                    if ($segment['bold']) {
                        if (! $b instanceof DOMElement) {
                            $rPr->appendChild($dom->createElementNS(self::WORD_NS, 'w:b'));
                        }
                        if (! $bCs instanceof DOMElement) {
                            $rPr->appendChild($dom->createElementNS(self::WORD_NS, 'w:bCs'));
                        }
                    } else {
                        if ($b instanceof DOMElement) {
                            $rPr->removeChild($b);
                        }
                        if ($bCs instanceof DOMElement) {
                            $rPr->removeChild($bCs);
                        }
                    }
                }

                $run->appendChild($rPr);

                $t = $dom->createElementNS(self::WORD_NS, 'w:t');
                $t->nodeValue = $segment['text'];
                if ($segment['text'] !== trim($segment['text'])) {
                    $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
                }
                $run->appendChild($t);
                $paragraph->appendChild($run);
            }

            return;
        }

        // Default behavior (normal replacement)
        $textNodes = iterator_to_array($xpath->query('.//w:t', $paragraph));

        if ($textNodes === []) {
            $run = $paragraph->ownerDocument->createElementNS(self::WORD_NS, 'w:r');
            $textNode = $paragraph->ownerDocument->createElementNS(self::WORD_NS, 'w:t');
            $run->appendChild($textNode);
            $paragraph->appendChild($run);
            $textNodes = [$textNode];
        }

        foreach ($textNodes as $index => $textNode) {
            $textNode->nodeValue = $index === 0 ? $text : '';

            if ($index === 0 && $text !== trim($text)) {
                $textNode->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
            }
        }
    }

    private function parseBoldSegments(string $text): array
    {
        $segments = [];
        $parts = explode('**', $text);
        foreach ($parts as $index => $part) {
            if ($part === '' && $index === 0) {
                continue;
            }
            $segments[] = [
                'text' => $part,
                'bold' => ($index % 2 === 1),
            ];
        }

        return $segments;
    }

    private function shadeRow(DOMElement $row, string $fill): void
    {
        foreach ($this->rowCells($row) as $cell) {
            $this->shadeCell($cell, $fill);
        }
    }

    private function shadeCell(DOMElement $cell, string $fill): void
    {
        $dom = $cell->ownerDocument;
        $properties = $this->directChildElements($cell, 'tcPr')[0] ?? null;

        if (! $properties instanceof DOMElement) {
            $properties = $dom->createElementNS(self::WORD_NS, 'w:tcPr');
            if ($cell->firstChild) {
                $cell->insertBefore($properties, $cell->firstChild);
            } else {
                $cell->appendChild($properties);
            }
        }

        $shading = $this->directChildElements($properties, 'shd')[0] ?? null;
        if (! $shading instanceof DOMElement) {
            $shading = $dom->createElementNS(self::WORD_NS, 'w:shd');
            $properties->appendChild($shading);
        }

        $shading->setAttributeNS(self::WORD_NS, 'w:val', 'clear');
        $shading->setAttributeNS(self::WORD_NS, 'w:color', 'auto');
        $shading->setAttributeNS(self::WORD_NS, 'w:fill', strtoupper(ltrim($fill, '#')));
    }

    private function findRowWithCellText(array $rows, DOMXPath $xpath, int $cellIndex, string $text): ?DOMElement
    {
        foreach ($rows as $row) {
            $cells = $this->rowCells($row);

            if (isset($cells[$cellIndex]) && trim($this->nodeText($cells[$cellIndex], $xpath)) === $text) {
                return $row;
            }
        }

        return null;
    }

    private function findRowContainingText(array $rows, DOMXPath $xpath, string $text): ?DOMElement
    {
        foreach ($rows as $row) {
            if (str_contains($this->nodeText($row, $xpath), $text)) {
                return $row;
            }
        }

        return null;
    }

    private function firstRowWithAtLeastCells(array $rows, int $cellCount, int $startIndex = 0): ?DOMElement
    {
        for ($index = $startIndex; $index < count($rows); $index++) {
            if (count($this->rowCells($rows[$index])) >= $cellCount) {
                return $rows[$index];
            }
        }

        return null;
    }

    private function frequencyFromNote(?string $note): string
    {
        $note = trim((string) $note);
        if ($note === '') {
            return '1';
        }

        if (preg_match('/(?:tan\s*suat|tần\s*suất|frequency)\D*(\d+(?:[,.]\d+)?)/iu', $note, $matches)) {
            return str_replace('.', ',', $matches[1]);
        }

        if (preg_match('/^\s*(\d+(?:[,.]\d+)?)\s*$/u', $note, $matches)) {
            return str_replace('.', ',', $matches[1]);
        }

        return $note;
    }

    private function removeTableRowsAfter(DOMElement $table, int $lastIndexToKeep): void
    {
        $rows = $this->tableRows($table);

        for ($index = count($rows) - 1; $index > $lastIndexToKeep; $index--) {
            $table->removeChild($rows[$index]);
        }
    }

    /**
     * @return list<DOMElement>
     */
    private function tableRows(DOMElement $table): array
    {
        return $this->directChildElements($table, 'tr');
    }

    /**
     * @return list<DOMElement>
     */
    private function rowCells(DOMElement $row): array
    {
        return $this->directChildElements($row, 'tc');
    }

    /**
     * @return list<DOMElement>
     */
    private function directChildElements(DOMElement $parent, string $localName): array
    {
        $elements = [];

        foreach ($parent->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === $localName && $child->namespaceURI === self::WORD_NS) {
                $elements[] = $child;
            }
        }

        return $elements;
    }

    private function createParagraph(DOMDocument $dom): DOMElement
    {
        $paragraph = $dom->createElementNS(self::WORD_NS, 'w:p');
        $paragraph->appendChild($dom->createElementNS(self::WORD_NS, 'w:r'))
            ->appendChild($dom->createElementNS(self::WORD_NS, 'w:t'));

        return $paragraph;
    }

    private function nodeText(DOMNode $node, DOMXPath $xpath): string
    {
        $parts = [];

        foreach ($xpath->query('.//w:t', $node) as $textNode) {
            $parts[] = $textNode->nodeValue;
        }

        return implode('', $parts);
    }

    private function noteLines(QuotationDocument $doc, string $year): array
    {
        $defaultLines = [
            '<strong>Kết quả thực hiện:</strong> Báo cáo Quan trắc môi trường lao động '.$year,
            '<strong>Thời gian có cuốn báo cáo QTMTLĐ:</strong> 10-15 ngày kể từ ngày quan trắc và có đầy đủ thông tin khách hàng cung cấp (không tính ngày lễ, thứ 7, chủ nhật);',
            '<strong>Chi phí trên đã bao gồm VAT '.$doc->vat_rate.'%</strong> tại thời điểm xuất hóa đơn.',
            '<strong>Phương thức thanh toán:</strong>',
            '50% sau khi ký hợp đồng',
            '50% sau khi hoàn thành báo cáo Quan trắc môi trường lao động',
            '<strong>Hình thức:</strong> chuyển khoản',
            '<strong>Chúng tôi xin cam kết sẽ tiến hành và hoàn thành công việc theo đúng nội dung được nêu trong báo giá!</strong>',
            'Trân trọng cảm ơn và mong nhận được sự hợp tác từ Quý Khách hàng!',
        ];

        $notes = $this->splitUserLines($doc->notes);
        $terms = $this->splitUserLines($doc->terms);

        if ($terms === []) {
            $terms = $defaultLines;
        } else {
            foreach ($terms as &$line) {
                if (str_contains($line, 'Báo cáo Quan trắc môi trường lao động') && ! str_contains($line, $year)) {
                    $line = preg_replace('/Báo cáo Quan trắc môi trường lao động/u', 'Báo cáo Quan trắc môi trường lao động '.$year, $line);
                }

                if (str_contains($line, 'bao gồm VAT') && ! str_contains($line, '%')) {
                    $line = str_replace('bao gồm VAT', 'bao gồm VAT '.$doc->vat_rate.'%', $line);
                }
            }
            unset($line);
        }

        return array_values(array_filter([...$notes, ...$terms], fn ($line) => $line !== ''));
    }

    private function customNoteLines(QuotationDocument $doc): array
    {
        $notes = $this->splitUserLines($doc->notes);
        $terms = $this->splitUserLines($doc->terms);

        if ($this->looksLikeDefaultLaborTerms($terms)) {
            $terms = [];
        }

        return array_values(array_filter([...$notes, ...$terms], fn ($line) => $line !== ''));
    }

    private function durationTextForItem(object $item): string
    {
        $quantity = (float) ($item->quantity ?? 0);
        $unit = trim((string) ($item->unit ?? ''));

        if ($quantity > 0) {
            return trim($this->formatDetailQty($quantity).' '.$unit);
        }

        return $unit;
    }

    private function looksLikeDefaultLaborTerms(array $terms): bool
    {
        $joined = implode(' ', $terms);

        return str_contains($joined, 'Báo cáo Quan trắc môi trường lao động')
            && str_contains($joined, '50% sau khi ký hợp đồng')
            && str_contains($joined, '50% sau khi hoàn thành');
    }

    /**
     * @return list<string>
     */
    private function splitUserLines(?string $value): array
    {
        if (! $value) {
            return [];
        }

        // Insert newlines before known label titles/bullet points if text was merged into 1 paragraph
        $value = preg_replace('/(?<!^)(?<!\n)\s*(Kết quả thực hiện:|Thời gian (?:có cuốn báo cáo|hoàn thành|thực hiện)[^:]*:|Chi phí trên đã bao gồm VAT|Phương thức thanh toán:|Hình thức:|Chúng tôi xin cam kết|•|\b\d+%\s*sau khi)/u', "\n$1", $value) ?? $value;

        // Convert HTML block endings and breaks to newlines
        $value = preg_replace('/<\/(p|div|li|h[1-6])>/i', "\n", $value) ?? $value;
        $value = preg_replace('/<br\s*\/?>/i', "\n", $value) ?? $value;
        // Strip block containers while preserving safe inline HTML tags
        $value = strip_tags($value, '<b><strong><i><em><u><span><sub><sup>');

        $lines = preg_split('/\R/u', $value) ?: [];

        return array_values(array_filter(array_map(function (string $line): string {
            return trim(preg_replace('/^[\-\x{2022}\x{25E6}\x{F076}\s]+/u', '', $line) ?? '');
        }, $lines), fn ($line) => $line !== ''));
    }

    /**
     * @return array{0:string,1:string}
     */
    private function splitGroupName(string $groupName): array
    {
        $groupName = trim($groupName);

        if (preg_match('/^([IVXLCDM]+)\.?\s*(.+)$/u', $groupName, $matches)) {
            return [$matches[1], mb_strtoupper(trim($matches[2]), 'UTF-8')];
        }

        return ['', mb_strtoupper($groupName ?: 'CHI TIẾT', 'UTF-8')];
    }

    private function displayServiceType(QuotationDocument $doc): string
    {
        $serviceType = trim((string) $doc->service_type);
        if ($serviceType === '' || $serviceType === 'Khác') {
            $serviceType = QuotationTemplateCatalog::find($doc->template_key ?? null)['service_type'] ?? '';
        }

        return match ($serviceType) {
            'QTMT và BCCTBVMT', 'BCCTBVMT' => 'Báo cáo công tác bảo vệ môi trường',
            '', 'Khác' => 'Quan trắc môi trường lao động',
            default => $serviceType,
        };
    }

    private function formatSummaryQty(float $qty): string
    {
        if ($qty == (int) $qty) {
            return str_pad((string) (int) $qty, 2, '0', STR_PAD_LEFT);
        }

        return number_format($qty, 2, ',', '.');
    }

    private function formatDetailQty(float $qty): string
    {
        return $qty == (int) $qty ? (string) (int) $qty : number_format($qty, 2, ',', '.');
    }

    private function formatMoney(int|float|string|null $amount): string
    {
        return number_format((float) $amount, 0, ',', '.').' đ';
    }

    private function formatPlainMoney(int|float|string|null $amount): string
    {
        return number_format((float) $amount, 0, ',', '.');
    }

    private function safeFilePart(?string $value): string
    {
        $safe = trim((string) $value);
        $safe = preg_replace('/[\\\\\/:*?"<>|]+/u', '-', $safe) ?? '';
        $safe = preg_replace('/\s+/u', '-', $safe) ?? '';
        $safe = trim($safe, '-.');

        return $safe !== '' ? $safe : 'bao-gia';
    }

    private function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory.DIRECTORY_SEPARATOR.$item;
            is_dir($path) ? $this->removeDirectory($path) : @unlink($path);
        }

        @rmdir($directory);
    }

    public function numberToWords(int $number): string
    {
        if ($number === 0) {
            return 'Không';
        }

        $units = ['', 'nghìn', 'triệu', 'tỷ', 'nghìn tỷ', 'triệu tỷ'];
        $digits = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];

        $groups = [];
        $remaining = $number;

        while ($remaining > 0) {
            $groups[] = $remaining % 1000;
            $remaining = intdiv($remaining, 1000);
        }

        $parts = [];
        for ($i = count($groups) - 1; $i >= 0; $i--) {
            $group = $groups[$i];
            if ($group === 0) {
                continue;
            }

            $parts[] = trim($this->threeDigitsToWords($group, $digits, $i < count($groups) - 1).' '.($units[$i] ?? ''));
        }

        $result = implode(' ', $parts);

        return mb_strtoupper(mb_substr($result, 0, 1, 'UTF-8'), 'UTF-8').mb_substr($result, 1, null, 'UTF-8');
    }

    private function threeDigitsToWords(int $number, array $digits, bool $hasHigherGroup): string
    {
        $hundreds = intdiv($number, 100);
        $tens = intdiv($number % 100, 10);
        $ones = $number % 10;

        $parts = [];

        if ($hundreds > 0) {
            $parts[] = $digits[$hundreds].' trăm';
        } elseif ($hasHigherGroup && ($tens > 0 || $ones > 0)) {
            $parts[] = 'không trăm';
        }

        if ($tens > 1) {
            $parts[] = $digits[$tens].' mươi';
            if ($ones === 1) {
                $parts[] = 'mốt';
            } elseif ($ones === 5) {
                $parts[] = 'lăm';
            } elseif ($ones > 0) {
                $parts[] = $digits[$ones];
            }
        } elseif ($tens === 1) {
            $parts[] = 'mười';
            if ($ones === 5) {
                $parts[] = 'lăm';
            } elseif ($ones > 0) {
                $parts[] = $digits[$ones];
            }
        } elseif ($ones > 0) {
            if ($hundreds > 0 || $hasHigherGroup) {
                $parts[] = 'lẻ';
            }
            $parts[] = $digits[$ones];
        }

        return implode(' ', $parts);
    }

    private function getTemplatePageCount(QuotationDocument $doc): string
    {
        try {
            $templatePath = $this->templatePathFor($doc);
            if (is_file($templatePath)) {
                $zip = new ZipArchive;
                if ($zip->open($templatePath) === true) {
                    $docXml = $zip->getFromName('word/document.xml');
                    $zip->close();
                    if ($docXml) {
                        $tempDom = new DOMDocument;
                        @$tempDom->loadXML($docXml);
                        $tempXpath = new DOMXPath($tempDom);
                        $tempXpath->registerNamespace('w', self::WORD_NS);

                        $tables = $tempXpath->query('//w:tbl');
                        if ($tables->length > 0) {
                            $rows = $tempXpath->query('.//w:tr', $tables->item(0));
                            if ($rows->length > 1) {
                                $cells = $tempXpath->query('.//w:tc', $rows->item(1));
                                if ($cells->length > 1) {
                                    $ps = $tempXpath->query('.//w:p', $cells->item(1));
                                    foreach ($ps as $p) {
                                        $rawText = trim($this->nodeText($p, $tempXpath));
                                        if (preg_match('/Số\s*trang:\s*(\d+)/ui', $rawText, $matches)) {
                                            return str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            // Ignore and fallback
        }

        return '03';
    }

    private function resolveStaffDetails(QuotationDocument $doc): array
    {
        $staff = $doc->staff ?: auth()->user();

        $name = trim((string) ($staff?->name ?: 'Kinh Doanh'));
        $phone = trim((string) ($staff?->phone ?: ''));
        $email = trim((string) ($staff?->email ?: ''));

        return [$name, $phone, $email];
    }

    private function resolveStaffGenderPrefix(QuotationDocument $doc): string
    {
        return '(Mrs)';
    }

    private function resolveStaffTitle(QuotationDocument $doc, bool $useEnglish = false): string
    {
        $user = $doc->staff;
        if (! $user) {
            return $useEnglish ? 'Sales Manager' : 'Nhân viên kinh doanh';
        }

        if ($user->hasRole(Role::TP_KINH_DOANH->value)) {
            return $useEnglish ? 'Sales Manager' : 'Trưởng phòng Kinh doanh';
        }

        if ($user->hasRole(Role::KINH_DOANH->value)) {
            return $useEnglish ? 'Sales Executive' : 'Nhân viên kinh doanh';
        }

        $role = $user->roles->first();
        if ($role) {
            $roleEnum = Role::tryFrom($role->name);
            if ($roleEnum) {
                return $roleEnum->label();
            }
        }

        return $useEnglish ? 'Sales Manager' : 'Nhân viên kinh doanh';
    }

    private function templateSupportsFrequency(?string $templateKey): bool
    {
        $template = QuotationTemplateCatalog::find($templateKey);

        return in_array('frequency', $template['requires'] ?? []);
    }
}
