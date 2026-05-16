<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    @verbatim
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Dòng tiền</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                        <x:FitToPage/>
                        <x:Print>
                            <x:FitWidth>1</x:FitWidth>
                            <x:FitHeight>0</x:FitHeight>
                        </x:Print>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    @endverbatim
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #111827;
        }

        table {
            border-collapse: collapse;
            table-layout: fixed;
        }

        .title {
            background-color: #0f3d5c;
            color: #ffffff;
            font-size: 18pt;
            font-weight: 700;
            text-align: center;
            padding: 12px 8px;
            border: 1px solid #0f3d5c;
        }

        .subtitle {
            background-color: #e8f2f8;
            color: #334155;
            font-size: 10pt;
            text-align: center;
            padding: 6px 8px;
            border: 1px solid #9fb6c5;
        }

        .section {
            background-color: #dbeafe;
            color: #0f3d5c;
            font-weight: 700;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #93c5fd;
        }

        .summary-label {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
        }

        .summary-value {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-weight: 700;
        }

        .header {
            background-color: #1a5276;
            color: #ffffff;
            font-weight: 700;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #0f3d5c;
            padding: 7px 6px;
        }

        .cell {
            border: 1px solid #cbd5e1;
            vertical-align: top;
            padding: 5px 6px;
            background-color: #ffffff;
        }

        .row-alt .cell {
            background-color: #f8fafc;
        }

        .total-row td {
            background-color: #dcfce7;
            border: 1px solid #86efac;
            font-weight: 700;
            padding: 7px 6px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-cell { mso-number-format: "\@"; }
        .wrap { white-space: normal; }
        .money {
            mso-number-format: "\#\,\#\#0";
            text-align: right;
            white-space: nowrap;
        }
        .positive { color: #15803d; }
        .negative { color: #b91c1c; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    <table>
        <col style="width:42px">
        <col style="width:150px">
        <col style="width:210px">
        <col style="width:130px">
        <col style="width:260px">
        <col style="width:150px">
        <col style="width:96px">
        <col style="width:128px">
        <col style="width:122px">
        <col style="width:122px">
        <col style="width:122px">
        <col style="width:122px">

        <tr>
            <td class="title" colspan="12">BÁO CÁO DÒNG TIỀN</td>
        </tr>
        <tr>
            <td class="subtitle" colspan="12">
                Kỳ báo cáo: {{ $periodLabel }} | Xuất ngày: {{ now()->format('d/m/Y H:i') }}
            </td>
        </tr>
        <tr>
            <td class="section" colspan="12">Tổng quan</td>
        </tr>
        <tr>
            <td class="summary-label" colspan="2">Số hợp đồng</td>
            <td class="summary-value text-center">{{ $totals['count'] }}</td>
            <td class="summary-label" colspan="3">Giá trị chưa VAT</td>
            <td class="summary-value money" colspan="2">{{ (int) $totals['value_without_vat'] }}</td>
            <td class="summary-label" colspan="2">Doanh số</td>
            <td class="summary-value money" colspan="2">{{ (int) $totals['revenue'] }}</td>
        </tr>
        <tr>
            <td class="summary-label" colspan="2">Hoa hồng</td>
            <td class="summary-value money">{{ (int) $totals['commission'] }}</td>
            <td class="summary-label" colspan="3">Chi NCC</td>
            <td class="summary-value money" colspan="2">{{ (int) $totals['ncc_payment'] }}</td>
            <td class="summary-label" colspan="2">Thực nhận</td>
            <td class="summary-value money {{ $totals['net_received'] >= 0 ? 'positive' : 'negative' }}" colspan="2">
                {{ (int) $totals['net_received'] }}
            </td>
        </tr>
        <tr>
            <td colspan="12">&nbsp;</td>
        </tr>
        <tr>
            <th class="header">STT</th>
            <th class="header">Loại HĐ</th>
            <th class="header">Hạng mục dịch vụ</th>
            <th class="header">Số HĐ BC</th>
            <th class="header">Khách hàng</th>
            <th class="header">NV CS</th>
            <th class="header">Ngày ký</th>
            <th class="header">Giá trị chưa VAT</th>
            <th class="header">Doanh số</th>
            <th class="header">Hoa hồng</th>
            <th class="header">Chi NCC</th>
            <th class="header">Thực nhận</th>
        </tr>

        @forelse($rows as $i => $row)
        <tr class="{{ $i % 2 === 1 ? 'row-alt' : '' }}">
            <td class="cell text-center">{{ $i + 1 }}</td>
            <td class="cell wrap">{{ $row['type'] }}</td>
            <td class="cell wrap">{{ $row['service_category'] ?? '' }}</td>
            <td class="cell text-cell">{{ $row['shd_bc'] ?: '' }}</td>
            <td class="cell wrap">{{ $row['customer'] ?? '' }}</td>
            <td class="cell wrap">{{ $row['staff'] ?? '' }}</td>
            <td class="cell text-center text-cell">{{ $row['signed_at'] ?? '' }}</td>
            <td class="cell money">{{ $row['value_without_vat'] > 0 ? (int) $row['value_without_vat'] : '' }}</td>
            <td class="cell money">{{ $row['revenue'] > 0 ? (int) $row['revenue'] : '' }}</td>
            <td class="cell money">{{ $row['commission'] > 0 ? (int) $row['commission'] : '' }}</td>
            <td class="cell money">{{ $row['ncc_payment'] > 0 ? (int) $row['ncc_payment'] : '' }}</td>
            <td class="cell money {{ $row['net_received'] >= 0 ? 'positive' : 'negative' }}">{{ (int) $row['net_received'] }}</td>
        </tr>
        @empty
        <tr>
            <td class="cell text-center muted" colspan="12">Không có dữ liệu cho kỳ đã chọn.</td>
        </tr>
        @endforelse

        <tr class="total-row">
            <td class="text-center" colspan="7">TỔNG CỘNG ({{ $totals['count'] }} hợp đồng)</td>
            <td class="money">{{ (int) $totals['value_without_vat'] }}</td>
            <td class="money">{{ (int) $totals['revenue'] }}</td>
            <td class="money">{{ (int) $totals['commission'] }}</td>
            <td class="money">{{ (int) $totals['ncc_payment'] }}</td>
            <td class="money {{ $totals['net_received'] >= 0 ? 'positive' : 'negative' }}">{{ (int) $totals['net_received'] }}</td>
        </tr>
    </table>
</body>
</html>
