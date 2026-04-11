<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; }
        .header { background-color: #1a5276; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #aaa; padding: 6px; }
        .cell { border: 1px solid #ccc; vertical-align: top; padding: 4px 6px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .status-done { color: #198754; font-weight: bold; }
        .status-warn { color: #e67e22; font-weight: bold; }
        .status-danger { color: #dc3545; font-weight: bold; }
        .title-row td { font-size: 15pt; font-weight: bold; text-align: center; padding: 10px; }
        .meta-row td { font-size: 9pt; color: #555; text-align: center; padding: 2px; }
        tr:nth-child(even) td { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <table>
        <tr class="title-row">
            <td colspan="{{ $showFinancials ? 14 : 11 }}">DANH SÁCH {{ strtoupper($title) }}</td>
        </tr>
        <tr class="meta-row">
            <td colspan="{{ $showFinancials ? 14 : 11 }}">Xuất ngày: {{ now()->format('d/m/Y H:i') }} — Tổng: {{ $docs->count() }} hợp đồng</td>
        </tr>
        <tr>
            <th class="header" style="width:40px;">STT</th>
            <th class="header" style="width:110px;">Số HĐ BC</th>
            <th class="header" style="width:110px;">Số HĐ CXL</th>
            <th class="header" style="width:220px;">Khách hàng</th>
            <th class="header" style="width:160px;">Nhân viên CS</th>
            <th class="header" style="width:130px;">Phòng ban</th>
            <th class="header" style="width:100px;">Ngày ký</th>
            <th class="header" style="width:100px;">Ngày KT</th>
            @if($showFinancials)
            <th class="header" style="width:130px;">Giá trị HĐ</th>
            <th class="header" style="width:120px;">Hoa hồng</th>
            <th class="header" style="width:120px;">Doanh số</th>
            @endif
            <th class="header" style="width:120px;">Phương thức TT</th>
            <th class="header" style="width:130px;">Tình trạng tái ký</th>
            <th class="header" style="width:130px;">Tình trạng CTV</th>
            <th class="header" style="width:130px;">Tình trạng</th>
        </tr>
        @foreach($docs as $i => $doc)
        <tr>
            <td class="cell text-center">{{ $i + 1 }}</td>
            <td class="cell">{{ $doc->shd_bc ?? '-' }}</td>
            <td class="cell">{{ $doc->shd_cxl ?? '-' }}</td>
            <td class="cell">{{ $doc->customer?->name ?? '-' }}</td>
            <td class="cell">{{ $doc->staff?->name ?? '-' }}</td>
            <td class="cell">{{ $doc->department?->name ?? '-' }}</td>
            <td class="cell text-center">{{ $doc->signed_at?->format('d/m/Y') ?? '-' }}</td>
            <td class="cell text-center">{{ $doc->end_at?->format('d/m/Y') ?? '-' }}</td>
            @if($showFinancials)
            <td class="cell text-right">{{ $doc->value ? number_format($doc->value) : '-' }}</td>
            <td class="cell text-right">{{ $doc->commission ? number_format($doc->commission) : '-' }}</td>
            <td class="cell text-right">{{ $doc->revenue ? number_format($doc->revenue) : '-' }}</td>
            @endif
            <td class="cell text-center">{{ $doc->payment_method ?? '-' }}</td>
            <td class="cell text-center">{{ $doc->renewal_status ?? '-' }}</td>
            <td class="cell text-center">{{ $doc->voucher_status ?? '-' }}</td>
            <td class="cell text-center">{{ $doc->status ?? '-' }}</td>
        </tr>
        @endforeach
    </table>
</body>
</html>
