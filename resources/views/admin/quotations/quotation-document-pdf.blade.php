<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Báo giá {{ $doc->document_number }}</title>
    @inject('pdfViewData', 'App\Support\QuotationPdfViewData')
    <style>
        @font-face {
            font-family: "BaoChauSerif";
            font-style: normal;
            font-weight: 400;
            src: url("{{ $pdfViewData->dompdfFont('DejaVuSerif.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: "BaoChauSerif";
            font-style: normal;
            font-weight: 700;
            src: url("{{ $pdfViewData->dompdfFont('DejaVuSerif-Bold.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: "BaoChauSerif";
            font-style: italic;
            font-weight: 400;
            src: url("{{ $pdfViewData->dompdfFont('DejaVuSerif-Italic.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: "BaoChauSerif";
            font-style: italic;
            font-weight: 700;
            src: url("{{ $pdfViewData->dompdfFont('DejaVuSerif-BoldItalic.ttf') }}") format("truetype");
        }

        @page {
            size: A4 portrait;
            margin: 8mm 14mm 30mm 14mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #000;
            font-family: "BaoChauSerif", "DejaVu Serif", serif;
            font-size: 11pt;
            line-height: 1.35;
        }

        .watermark {
            position: fixed;
            top: 18%;
            left: 0;
            width: 100%;
            opacity: 0.1;
            z-index: -10;
        }

        .footer {
            position: fixed;
            left: 14mm;
            right: 14mm;
            bottom: -27mm;
            height: 13mm;
            color: #48665a;
            font-family: "BaoChauSerif", "DejaVu Serif", serif;
            font-size: 7.4pt;
            line-height: 1.12;
        }

        .footer-line {
            width: 100%;
            height: 2px;
            margin-bottom: 3px;
            display: block;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .footer-table td {
            padding: 0;
            border: 0;
            vertical-align: top;
        }

        .footer-slogan {
            width: 90%;
            font-style: italic;
            color: #3f6f58;
            white-space: nowrap;
        }

        .footer-page {
            width: 10%;
            text-align: right;
            color: #4d4d4d;
            white-space: nowrap;
        }

        .footer-page:after {
            content: counter(page);
        }

        .banner {
            width: 100%;
            margin: 0 0 7px;
        }

        .title {
            margin: 4px 0 1px;
            text-align: center;
            font-size: 18pt;
            font-weight: 700;
            letter-spacing: 0;
            color: #000;
        }

        .subtitle {
            margin: 0 0 8px;
            text-align: center;
            font-size: 11.5pt;
            font-weight: 700;
            font-style: italic;
            color: #000;
        }

        table {
            border-collapse: collapse;
        }

        .info-table {
            width: 100%;
            margin: 4px 0 4px;
            border: 1px solid #1f8f50;
        }

        .info-table td {
            width: 50%;
            padding: 5px 6px;
            border: 1px solid #1f8f50;
            text-align: center;
            vertical-align: top;
            font-size: 10.2pt;
            line-height: 1.35;
            background: #fbfffc;
        }

        .info-table p {
            margin: 0 0 2px;
        }

        .intro {
            margin: 3px 12px 6px;
            text-align: justify;
            text-indent: 28px;
            font-size: 10.6pt;
            line-height: 1.48;
        }

        .caption {
            margin: 4px 0 4px;
            text-align: center;
            font-size: 10.8pt;
            font-weight: 700;
            color: #000;
        }

        .quote-table {
            width: 100%;
            margin: 0 0 7px;
            border: 1px solid #222;
            page-break-inside: auto;
        }

        .quote-table th,
        .quote-table td {
            padding: 3px 4px;
            border: 1px solid #222;
            font-size: 9.2pt;
            line-height: 1.2;
            vertical-align: middle;
        }

        .quote-table th {
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
            color: #000;
            background: #C5EECE;
        }

        .quote-table tr {
            page-break-inside: avoid;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: 700;
        }

        .group-row td {
            font-weight: 700;
            text-transform: uppercase;
            color: #000;
            background: #f7fff9;
        }

        .detail-total-row td {
            background: #fff;
        }

        .grand-total-row td {
            color: #000;
            background: #fff;
        }

        .notes-title {
            margin: 10px 0 3px;
            font-weight: 700;
            color: #145a32;
        }

        .notes {
            margin: 8px 30px 0;
            page-break-inside: avoid;
            font-style: italic;
        }

        .notes p {
            margin: 0 0 7px;
            font-size: 10.2pt;
            line-height: 1.45;
            text-align: justify;
        }

        .note-line {
            padding-left: 18px;
            text-indent: -13px;
        }

        .note-child {
            margin-left: 48px !important;
            padding-left: 18px;
            text-indent: -12px;
        }

        .note-label {
            font-weight: 700;
            font-style: italic;
        }

        .note-payment {
            font-weight: 700;
            font-style: italic;
            text-decoration: underline;
        }

        .note-commit {
            margin-top: 3px !important;
            text-indent: 0;
        }

        .note-thanks {
            margin: 13px 0 16px !important;
            text-align: center !important;
            font-weight: 700;
            font-style: normal;
        }

        .signature-table {
            width: 100%;
            margin-top: 14px;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 50%;
            padding: 0 8px;
            border: 0;
            vertical-align: top;
            font-size: 9.4pt;
            line-height: 1.28;
        }

        .signer {
            text-align: center;
            font-weight: 700;
        }

        .sign-space {
            height: 42px;
        }
    </style>
</head>
<body>
@if(file_exists(public_path('temp_media/image3.png')))
    <img class="watermark" src="{{ public_path('temp_media/image3.png') }}" alt="">
@endif

<div class="footer">
    @if(file_exists(public_path('temp_media/image4.png')))
        <img class="footer-line" src="{{ public_path('temp_media/image4.png') }}" alt="">
    @endif
    <table class="footer-table">
        <tr>
            <td class="footer-slogan">“Môi Trường Bảo Châu – Dịch vụ chất lượng – Giải pháp hiệu quả”</td>
            <td class="footer-page"></td>
        </tr>
    </table>
</div>

@if(file_exists(public_path('temp_media/image2.png')))
    <img class="banner" src="{{ public_path('temp_media/image2.png') }}" alt="">
@endif

<div class="title">BẢNG BÁO GIÁ</div>
<div class="subtitle">(V/v:{{ $pdfViewData->subtitleText($serviceTitle, $year) }})</div>

<table class="info-table">
    <tr>
        <td>
            <p class="bold">Kính gửi: Ban giám đốc</p>
            <p class="bold">{{ mb_strtoupper($pdfViewData->customerName($doc), 'UTF-8') }}</p>
        </td>
        <td>
            <p>Người gửi: {{ $staffGenderPrefix ? $staffGenderPrefix . ' ' : '' }}{{ $staffName }}</p>
            <p>Điện thoại: {{ $staffPhone }}</p>
            <p>Email: {{ $staffEmail }}</p>
        </td>
    </tr>
    <tr>
        <td style="text-align: left;">
            <p><u>Người nhận:</u> {{ $doc->customer_contact ?: '' }}</p>
            <p><u>Địa chỉ:</u> {{ $doc->customer_address ?: $doc->work_location }}</p>
        </td>
        <td style="text-align: left;">
            <p><u>Báo giá số:</u>{{ $doc->document_number }}</p>
            <p><u>Ngày báo giá:</u> {{ $pdfViewData->documentDate($doc) }}</p>
            <p>Số trang: {{ $pageCount ?? '03' }}</p>
        </td>
    </tr>
</table>

<p class="intro">
    {{ $pdfViewData->introText($serviceTitle, $year) }}
</p>

@if($pdfViewData->isLaborMonitoringTemplate($doc))
<table class="quote-table">
    <thead>
    <tr>
        <th style="width: 8%;">STT</th>
        <th style="width: 42%;">CHỈ TIÊU</th>
        <th style="width: 12%;">SỐ LƯỢNG</th>
        <th style="width: 18%;">ĐƠN GIÁ</th>
        <th style="width: 20%;">THÀNH TIỀN</th>
    </tr>
    </thead>
    <tbody>
    @forelse($pdfViewData->mainPriceItems($doc) as $item)
        <tr>
            <td class="center">{{ $loop->iteration }}</td>
            <td>{{ $item->description }}</td>
            <td class="center">{{ $pdfViewData->formatQty($item->quantity) }}</td>
            <td class="right">{{ $pdfViewData->formatMoney($item->unit_price ?: $item->amount) }}</td>
            <td class="right">{{ $pdfViewData->formatMoney($item->amount) }}</td>
        </tr>
    @empty
        <tr>
            <td class="center">1</td>
            <td>Thực hiện {{ $pdfViewData->subtitleText($serviceTitle, $year) }}</td>
            <td class="center">1</td>
            <td class="right">{{ $pdfViewData->formatMoney($doc->subtotal) }}</td>
            <td class="right">{{ $pdfViewData->formatMoney($doc->subtotal) }}</td>
        </tr>
    @endforelse
    <tr class="detail-total-row">
        <td colspan="4" class="right bold">TỔNG CỘNG CHƯA VAT</td>
        <td class="right bold">{{ $pdfViewData->formatMoney($doc->subtotal) }}</td>
    </tr>
    @if((int) $doc->discount > 0)
        <tr class="detail-total-row">
            <td colspan="4" class="right bold">CHIẾT KHẤU</td>
            <td class="right bold">-{{ $pdfViewData->formatMoney($doc->discount) }}</td>
        </tr>
    @endif
    <tr class="detail-total-row">
        <td colspan="4" class="right bold">VAT {{ $doc->vat_rate }}%</td>
        <td class="right bold">{{ $pdfViewData->formatMoney($doc->vat_amount) }}</td>
    </tr>
    <tr class="grand-total-row">
        <td colspan="4" class="right bold">TỔNG THÀNH TIỀN ĐÃ VAT</td>
        <td class="right bold">{{ $pdfViewData->formatMoney($doc->total) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="bold">Bằng chữ: "{{ $amountInWords }} đồng"</td>
    </tr>
    </tbody>
</table>
@else
<div class="caption">Bảng 01. Tổng hợp dự toán chi phí thực hiện</div>
<table class="quote-table">
    <thead>
    <tr>
        <th style="width: 10%;">STT</th>
        <th style="width: 42%;">Nội dung dịch vụ</th>
        <th style="width: 14%;">ĐVT</th>
        <th style="width: 12%;">SL</th>
        <th style="width: 22%;">Thành tiền</th>
    </tr>
    </thead>
    <tbody>
    @forelse($pdfViewData->summaryItems($doc) as $item)
        <tr>
            <td class="center">{{ $loop->iteration }}</td>
            <td>{{ $item->description }}</td>
            <td class="center">{{ $item->unit ?: 'Hồ sơ' }}</td>
            <td class="center">{{ $pdfViewData->formatQty($item->quantity) }}</td>
            <td class="right">{{ $pdfViewData->formatMoney($item->amount) }}</td>
        </tr>
    @empty
        <tr>
            <td class="center">1</td>
            <td>Thực hiện {{ $pdfViewData->subtitleText($serviceTitle, $year) }}</td>
            <td class="center">Hồ sơ</td>
            <td class="center">1</td>
            <td class="right">{{ $pdfViewData->formatMoney($doc->subtotal) }}</td>
        </tr>
    @endforelse
    <tr class="detail-total-row">
        <td colspan="4" class="right bold">TỔNG CỘNG CHƯA VAT</td>
        <td class="right bold">{{ $pdfViewData->formatMoney($doc->subtotal) }}</td>
    </tr>
    @if((int) $doc->discount > 0)
        <tr class="detail-total-row">
            <td colspan="4" class="right bold">CHIẾT KHẤU</td>
            <td class="right bold">-{{ $pdfViewData->formatMoney($doc->discount) }}</td>
        </tr>
    @endif
    <tr class="detail-total-row">
        <td colspan="4" class="right bold">VAT {{ $doc->vat_rate }}%</td>
        <td class="right bold">{{ $pdfViewData->formatMoney($doc->vat_amount) }}</td>
    </tr>
    <tr class="grand-total-row">
        <td colspan="4" class="right bold">TỔNG THÀNH TIỀN ĐÃ VAT</td>
        <td class="right bold">{{ $pdfViewData->formatMoney($doc->total) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="bold">Bằng chữ: "{{ $amountInWords }} đồng"</td>
    </tr>
    </tbody>
</table>

<div class="caption">Bảng 02. Tổng hợp chỉ tiêu đánh giá</div>
<table class="quote-table">
    <thead>
    <tr>
        <th style="width: 8%;">STT</th>
        <th style="width: 39%;">Chỉ tiêu / Nội dung công việc</th>
        <th style="width: 11%;">ĐVT</th>
        <th style="width: 18%;">Chi phí (đơn giá)</th>
        <th style="width: 8%;">SL</th>
        <th style="width: 16%;">Thành tiền</th>
    </tr>
    </thead>
    <tbody>
    @if($pdfViewData->hasDetailItems($doc))
        @foreach($pdfViewData->groupedMainItems($doc) as $groupName => $groupItems)
            <tr class="group-row">
                <td class="center">{{ $pdfViewData->splitGroup((string) $groupName)[0] }}</td>
                <td colspan="5" class="center">{{ $pdfViewData->splitGroup((string) $groupName)[1] }}</td>
            </tr>
            @foreach($groupItems as $item)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="center">{{ $item->unit ?: 'Mẫu' }}</td>
                    <td class="right">{{ $pdfViewData->formatMoney($item->unit_price) }}</td>
                    <td class="center">{{ $pdfViewData->formatQty($item->quantity) }}</td>
                    <td class="right">{{ $pdfViewData->formatMoney($item->amount) }}</td>
                </tr>
            @endforeach
        @endforeach
    @else
        <tr>
            <td class="center">1</td>
            <td>Thực hiện {{ $pdfViewData->subtitleText($serviceTitle, $year) }}</td>
            <td class="center">Hồ sơ</td>
            <td class="right">{{ $pdfViewData->formatMoney($doc->subtotal) }}</td>
            <td class="center">1</td>
            <td class="right">{{ $pdfViewData->formatMoney($doc->subtotal) }}</td>
        </tr>
    @endif
    <tr class="detail-total-row">
        <td colspan="5" class="right bold">Tổng trước VAT</td>
        <td class="right bold">{{ $pdfViewData->formatMoney($doc->subtotal) }}</td>
    </tr>
    <tr class="detail-total-row">
        <td colspan="5" class="right bold">VAT</td>
        <td class="right bold">{{ $pdfViewData->formatMoney($doc->vat_amount) }}</td>
    </tr>
    <tr class="grand-total-row">
        <td colspan="5" class="right bold">Tổng sau VAT</td>
        <td class="right bold">{{ $pdfViewData->formatMoney($doc->total) }}</td>
    </tr>
    </tbody>
</table>
@endif

<div class="notes">
    @foreach($noteLines as $line)
        @if($pdfViewData->isPaymentHeading((string) $line))
            <p class="note-line">– <span class="note-payment">{{ trim($line) }}</span></p>
        @elseif($pdfViewData->isPaymentChild((string) $line))
            <p class="note-child">❖ {!! $pdfViewData->highlightNote(trim((string) $line)) !!}</p>
        @elseif($pdfViewData->isCommit((string) $line))
            <p class="note-commit">{{ trim($line) }}</p>
        @elseif($pdfViewData->isThanks((string) $line))
            <p class="note-thanks">{{ trim($line) }}</p>
        @else
            <p class="note-line">– {!! $pdfViewData->highlightNote(trim((string) $line)) !!}</p>
        @endif
    @endforeach
</div>

<table class="signature-table">
    <tr>
        <td>
            <p><span class="bold">Thông tin liên hệ:</span></p>
            <p>{{ $staffName }} – {{ $staffTitle }}</p>
            <p>Số điện thoại: {{ $staffPhone }}</p>
            <p>Email: {{ $staffEmail }}</p>
        </td>
        <td class="signer">
            <p>Người báo giá</p>
            <div class="sign-space"></div>
            <p>{{ $staffName }}</p>
        </td>
    </tr>
</table>
</body>
</html>
