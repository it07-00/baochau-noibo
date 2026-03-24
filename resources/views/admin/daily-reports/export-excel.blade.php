<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Bao cao nhat ky</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style>
        .header {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .cell {
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        .status-success {
            color: #198754;
            font-weight: bold;
        }
        .status-warning {
            color: #ffc107;
            font-weight: bold;
        }
        .status-danger {
            color: #dc3545;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="7" style="font-size: 16pt; text-align: center; font-weight: bold; height: 40px;">
                    BÁO CÁO NHẬT KÝ CÔNG VIỆC 
                    @if($viewType == 'day')
                        NGÀY {{ \Carbon\Carbon::parse($dateFilter)->format('d/m/Y') }}
                    @else
                        THÁNG {{ $monthFilter }}/{{ $yearFilter }}
                    @endif
                </th>
            </tr>
            <tr>
                <th class="header" style="width: 200px;">Nhân viên</th>
                <th class="header" style="width: 150px;">Phòng ban</th>
                <th class="header" style="width: 100px;">Ngày</th>
                <th class="header" style="width: 400px;">Nội dung công việc</th>
                <th class="header" style="width: 150px;">Kết quả tổng thể</th>
                <th class="header" style="width: 300px;">Kế hoạch mai</th>
                <th class="header" style="width: 250px;">Vấn đề/Hỗ trợ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reports as $report)
                @php
                    $isSunday = $report->date->dayOfWeek === 0;
                    $rowStyle = $isSunday ? 'background-color: #f2f2f2;' : '';
                @endphp
                <tr style="{{ $rowStyle }}">
                    <td class="cell">{{ $report->user->name }}</td>
                    <td class="cell">{{ $report->user->department->name ?? 'N/A' }}</td>
                    <td class="cell text-center">{{ $report->date->format('d/m/Y') }}</td>
                    <td class="cell">{!! $report->content !!}</td>
                    <td class="cell text-center">
                        @php
                            $color = '#000000';
                            if ($report->status == 'Hoàn thành đúng kế hoạch') $color = '#198754';
                            elseif ($report->status == 'Gặp vấn đề, cần hỗ trợ') $color = '#dc3545';
                            elseif ($report->status == 'Hoàn thành một phần') $color = '#ffc107';
                            if ($isSunday && !$report->content) $color = '#6c757d'; 
                        @endphp
                        <span style="color: {{ $color }}; font-weight: bold;">{{ $report->status }}</span>
                    </td>
                    <td class="cell">{!! nl2br(e($report->plan)) !!}</td>
                    <td class="cell text-center" style="color: #dc3545;">{{ $report->issues }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
