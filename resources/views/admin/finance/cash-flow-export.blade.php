<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; }
        .header { background-color: #1a5276; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #aaa; padding: 6px; }
        .cell { border: 1px solid #ccc; vertical-align: middle; padding: 4px 6px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .title-row td { font-size: 15pt; font-weight: bold; text-align: center; padding: 10px; }
        .meta-row td { font-size: 9pt; color: #555; text-align: center; padding: 2px; }
        .total-row td { background-color: #d5f5e3; font-weight: bold; border: 1px solid #aaa; }
        tr:nth-child(even) td { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <table>
        <tr class="title-row">
            <td colspan="11">BÁO CÁO DÒNG TIỀN — {{ strtoupper($periodLabel) }}</td>
        </tr>
        <tr class="meta-row">
            <td colspan="11">Xuất ngày: {{ now()->format('d/m/Y H:i') }} — Tổng: {{ $totals['count'] }} hợp đồng</td>
        </tr>
        <tr>
            <th class="header" style="width:40px;">STT</th>
            <th class="header" style="width:160px;">Loại HĐ</th>
            <th class="header" style="width:120px;">Số HĐ BC</th>
            <th class="header" style="width:220px;">Khách hàng</th>
            <th class="header" style="width:150px;">NV CS</th>
            <th class="header" style="width:90px;">Ngày ký</th>
            <th class="header" style="width:120px;">Giá trị chưa VAT</th>
            <th class="header" style="width:110px;">Doanh số</th>
            <th class="header" style="width:110px;">Hoa hồng</th>
            <th class="header" style="width:110px;">Chi NCC</th>
            <th class="header" style="width:110px;">Thực nhận</th>
        </tr>
        @foreach($rows as $i => $row)
        <tr>
            <td class="cell text-center">{{ $i + 1 }}</td>
            <td class="cell">{{ $row['type'] }}</td>
            <td class="cell">{{ $row['shd_bc'] ?: '' }}</td>
            <td class="cell">{{ $row['customer'] ?? '' }}</td>
            <td class="cell">{{ $row['staff'] ?? '' }}</td>
            <td class="cell text-center">{{ $row['signed_at'] ?? '' }}</td>
            <td class="cell text-right">{{ $row['value_without_vat'] > 0 ? number_format($row['value_without_vat']) : '' }}</td>
            <td class="cell text-right">{{ number_format($row['revenue']) }}</td>
            <td class="cell text-right">{{ $row['commission'] > 0 ? number_format($row['commission']) : '' }}</td>
            <td class="cell text-right">{{ $row['ncc_payment'] > 0 ? number_format($row['ncc_payment']) : '' }}</td>
            <td class="cell text-right">{{ number_format($row['net_received']) }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td class="cell text-center" colspan="6">TỔNG CỘNG ({{ $totals['count'] }} hợp đồng)</td>
            <td class="cell text-right">{{ number_format($totals['value_without_vat']) }}</td>
            <td class="cell text-right">{{ number_format($totals['revenue']) }}</td>
            <td class="cell text-right">{{ number_format($totals['commission']) }}</td>
            <td class="cell text-right">{{ number_format($totals['ncc_payment']) }}</td>
            <td class="cell text-right">{{ number_format($totals['net_received']) }}</td>
        </tr>
    </table>
</body>
</html>
