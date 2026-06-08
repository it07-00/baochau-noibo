<?php

namespace App\Support;

use App\Models\QuotationDocument;
use App\Support\Quotations\QuotationTemplateCatalog;
use Illuminate\Support\Collection;

class QuotationPdfViewData
{
    public function dompdfFont(string $file): string
    {
        return 'file:///'.str_replace('\\', '/', base_path('vendor/dompdf/dompdf/lib/fonts/'.$file));
    }

    public function detailItems(QuotationDocument $doc): Collection
    {
        return $doc->items->where('item_type', 'detail')->values();
    }

    public function summaryItems(QuotationDocument $doc): Collection
    {
        return $doc->items->where('item_type', 'summary')->values();
    }

    public function isLaborMonitoringTemplate(QuotationDocument $doc): bool
    {
        return ($doc->template_key ?? QuotationTemplateCatalog::DEFAULT_KEY) === QuotationTemplateCatalog::DEFAULT_KEY;
    }

    public function mainPriceItems(QuotationDocument $doc): Collection
    {
        $details = $this->detailItems($doc);

        return $details->isNotEmpty()
            ? $details
            : $this->summaryItems($doc);
    }

    public function hasDetailItems(QuotationDocument $doc): bool
    {
        return $this->detailItems($doc)->isNotEmpty();
    }

    public function groupedMainItems(QuotationDocument $doc): Collection
    {
        $details = $this->detailItems($doc);
        if ($details->isNotEmpty()) {
            return $details->groupBy(fn ($item) => $item->group_name ?: 'CHI TIẾT');
        }

        return collect(['' => $this->summaryItems($doc)]);
    }

    public function hasMainItems(QuotationDocument $doc): bool
    {
        return $this->groupedMainItems($doc)->flatten(1)->isNotEmpty();
    }

    public function customerName(QuotationDocument $doc): string
    {
        return $doc->customer_name ?: 'QUÝ CÔNG TY';
    }

    public function documentDate(QuotationDocument $doc): string
    {
        return $doc->date?->format('d/m/Y') ?? now()->format('d/m/Y');
    }

    public function subtitleText(string $serviceTitle, string $year): string
    {
        return $serviceTitle.' '.$year;
    }

    public function introText(string $serviceTitle, string $year): string
    {
        return 'Xin chân thành cảm ơn Quý khách hàng đã tin tưởng và lựa chọn chúng tôi. '
            .'Công ty TNHH Dịch vụ và Kỹ thuật Môi trường Bảo Châu hân hạnh được đồng hành cùng Quý khách hàng trong lĩnh vực môi trường. '
            .'Về dịch vụ hiện '.mb_strtolower($serviceTitle, 'UTF-8').' '.$year.', Công ty Bảo Châu xin gửi báo giá như sau:';
    }

    public function formatQty(mixed $qty): string
    {
        return (float) $qty == (int) $qty ? (string) (int) $qty : number_format((float) $qty, 2, ',', '.');
    }

    public function formatMoney(int|float|string|null $amount): string
    {
        return number_format((float) $amount, 0, ',', '.');
    }

    public function frequencyOf(object $item): string
    {
        $note = trim((string) ($item->note ?? ''));

        if ($note !== '' && preg_match('/^(?:tần\s*suất|tan\s*suat|frequency)?\s*[:=]?\s*([0-9]+(?:[,.][0-9]+)?)$/iu', $note, $matches)) {
            return str_replace('.', ',', $matches[1]);
        }

        return '1';
    }

    public function splitGroup(string $groupName): array
    {
        $groupName = trim($groupName);

        if (preg_match('/^([IVXLCDM]+)\.?\s*(.+)$/u', $groupName, $matches)) {
            return [$matches[1], mb_strtoupper(trim($matches[2]), 'UTF-8')];
        }

        return ['', mb_strtoupper($groupName ?: 'CHI TIẾT', 'UTF-8')];
    }

    public function highlightNote(string $line): string
    {
        $escaped = e($line);
        $labels = [
            'Kết quả thực hiện:',
            'Thời gian hoàn thành phiếu kết quả:',
            'Thời gian hoàn thành Báo cáo:',
            'Thời gian có cuốn báo cáo QTMTLĐ:',
            'Chi phí trên đã bao gồm VAT',
            'Hình thức:',
        ];

        foreach ($labels as $label) {
            if (str_starts_with($line, $label)) {
                return '<span class="note-label">'.e($label).'</span>'.e(mb_substr($line, mb_strlen($label, 'UTF-8'), null, 'UTF-8'));
            }
        }

        return $escaped;
    }

    public function isPaymentHeading(string $line): bool
    {
        return str_starts_with(trim($line), 'Phương thức thanh toán');
    }

    public function isPaymentChild(string $line): bool
    {
        return (bool) preg_match('/^(?:[0-9]+%|Hình thức:)/u', trim($line));
    }

    public function isCommit(string $line): bool
    {
        return str_starts_with(trim($line), 'Chúng tôi xin cam kết');
    }

    public function isThanks(string $line): bool
    {
        return str_starts_with(trim($line), 'Trân trọng cảm ơn');
    }
}
