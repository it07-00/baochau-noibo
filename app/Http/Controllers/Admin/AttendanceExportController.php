<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AttendanceExportController extends Controller
{
    public function export(string $month)
    {
        abort_unless(auth()->user()->hasRole('it'), 403);

        $service   = app(AttendanceService::class);
        $monthData = $service->getMonthData($month);

        $startOfMonth = $monthData['startOfMonth'];
        $daysInMonth  = $monthData['daysInMonth'];
        $dates        = $monthData['dates'];
        $grid         = $service->buildSummaryGrid($monthData['employees'], $monthData['logs'], $dates, $startOfMonth);

        // ── Build spreadsheet ──────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Chấm công');

        $viDayNames = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];

        // Row 1: Title
        $lastCol = $daysInMonth + 5; // STT + Tên + days + Công + Trễ + Sớm
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol);
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->setCellValue('A1', 'BẢNG CHẤM CÔNG THÁNG ' . $startOfMonth->format('m/Y'));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Row 2: Header
        $sheet->setCellValue('A2', 'STT');
        $sheet->setCellValue('B2', 'Họ và tên');
        $col = 3;
        foreach ($dates as $date) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$colLetter}2", $date->day);
            // Sub-row 3: day-of-week
            $sheet->setCellValue("{$colLetter}3", $viDayNames[$date->dayOfWeek]);

            if ($date->isSunday()) {
                $sheet->getStyle("{$colLetter}2:{$colLetter}3")->applyFromArray([
                    'font' => ['color' => ['rgb' => 'CC0000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF0F0']],
                ]);
            }
            $col++;
        }

        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $sheet->setCellValue("{$colLetter}2", 'Công');
        $sheet->mergeCells("{$colLetter}2:{$colLetter}3");
        $col++;

        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $sheet->setCellValue("{$colLetter}2", 'Đi trễ');
        $sheet->mergeCells("{$colLetter}2:{$colLetter}3");
        $col++;

        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $sheet->setCellValue("{$colLetter}2", 'Về sớm');
        $sheet->mergeCells("{$colLetter}2:{$colLetter}3");

        // Style header rows 2-3
        $sheet->getStyle("A2:{$lastColLetter}3")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
        ]);
        $sheet->mergeCells('A2:A3');
        $sheet->mergeCells('B2:B3');

        // Row 3: day-of-week for date columns already set above
        // Override A3, B3 bg to match
        $sheet->getStyle('A3:B3')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
        ]);

        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(16);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(18);
        for ($c = 3; $c <= $daysInMonth + 2; $c++) {
            $sheet->getColumnDimensionByColumn($c)->setWidth(7.5);
        }
        // Summary columns
        for ($c = $daysInMonth + 3; $c <= $lastCol; $c++) {
            $sheet->getColumnDimensionByColumn($c)->setWidth(8);
        }

        // Data rows
        $rowNum = 4;
        foreach ($grid as $idx => $row) {
            $sheet->setCellValue("A{$rowNum}", $idx + 1);
            $sheet->setCellValue("B{$rowNum}", $row['employee']->name);
            $sheet->getRowDimension($rowNum)->setRowHeight(28);

            $col = 3;
            foreach ($dates as $date) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $dayInfo   = $row['days'][$date->day] ?? null;
                $isSunday  = $date->isSunday();

                if ($dayInfo) {
                    $isLate  = !$isSunday && $dayInfo['first'] > '08:00';
                    $isEarly = !$isSunday && $dayInfo['last'] && $dayInfo['last'] < '17:00';

                    $cell = $sheet->getCell("{$colLetter}{$rowNum}");
                    $text = $dayInfo['first'];
                    if ($dayInfo['last']) $text .= "\n" . $dayInfo['last'];
                    $cell->setValue($text);

                    $sheet->getStyle("{$colLetter}{$rowNum}")->getAlignment()
                        ->setWrapText(true)
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // Color: late = red in/out, early = orange out
                    if ($isLate && $isEarly) {
                        $sheet->getStyle("{$colLetter}{$rowNum}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE0E0']],
                        ]);
                    } elseif ($isLate) {
                        $sheet->getStyle("{$colLetter}{$rowNum}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE0E0']],
                        ]);
                    } elseif ($isEarly) {
                        $sheet->getStyle("{$colLetter}{$rowNum}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3CD']],
                        ]);
                    } elseif ($isSunday) {
                        $sheet->getStyle("{$colLetter}{$rowNum}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF0F0']],
                        ]);
                    } else {
                        $sheet->getStyle("{$colLetter}{$rowNum}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E9']],
                        ]);
                    }
                } else {
                    // Absent on working day
                    if ($isSunday) {
                        $sheet->getStyle("{$colLetter}{$rowNum}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF0F0']],
                        ]);
                    } elseif ($date->lte(now())) {
                        $sheet->setCellValue("{$colLetter}{$rowNum}", '✗');
                        $sheet->getStyle("{$colLetter}{$rowNum}")->applyFromArray([
                            'font'      => ['color' => ['rgb' => 'CC0000']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        ]);
                    }
                }
                $col++;
            }

            // Summary
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$colLetter}{$rowNum}", $row['work_days']);
            $sheet->getStyle("{$colLetter}{$rowNum}")->applyFromArray([
                'font'      => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
            ]);
            $col++;

            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$colLetter}{$rowNum}", $row['late_days']);
            $sheet->getStyle("{$colLetter}{$rowNum}")->applyFromArray([
                'font'      => ['bold' => $row['late_days'] > 0, 'color' => ['rgb' => $row['late_days'] > 0 ? 'CC0000' : '000000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
            ]);
            $col++;

            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$colLetter}{$rowNum}", $row['early_days']);
            $sheet->getStyle("{$colLetter}{$rowNum}")->applyFromArray([
                'font'      => ['bold' => $row['early_days'] > 0, 'color' => ['rgb' => $row['early_days'] > 0 ? 'E67E00' : '000000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
            ]);

            // Zebra stripe
            if ($idx % 2 === 1) {
                // light stripe on non-special cells handled via existing fills
            }

            $rowNum++;
        }

        // Borders on the entire table
        $sheet->getStyle("A2:{$lastColLetter}" . ($rowNum - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'AAAAAA'],
                ],
            ],
        ]);
        // Thick outer border
        $sheet->getStyle("A2:{$lastColLetter}" . ($rowNum - 1))->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['rgb' => '333333'],
                ],
            ],
        ]);

        // Freeze panes: freeze first 2 cols + header rows
        $sheet->freezePane('C4');

        // ── Chú thích (legend) ────────────────────────────────────────────
        $legendRow = $rowNum + 1;
        $sheet->setCellValue("A{$legendRow}", 'Chú thích:');
        $sheet->getStyle("A{$legendRow}")->applyFromArray([
            'font' => ['bold' => true],
        ]);

        $legends = [
            ['color' => 'E8F5E9', 'border' => '4CAF50', 'text' => 'Đúng giờ'],
            ['color' => 'FFE0E0', 'border' => 'CC0000', 'text' => 'Đi trễ (vào sau 08:00)'],
            ['color' => 'FFF3CD', 'border' => 'E67E00', 'text' => 'Về sớm (ra trước 17:00)'],
            ['color' => 'FFFFFF', 'border' => '888888', 'text' => 'Chỉ có giờ vào (chưa ra)'],
            ['color' => 'FFF0F0', 'border' => 'CC0000', 'text' => 'Chủ nhật'],
        ];

        $legendRow++;
        foreach ($legends as $legend) {
            // Color box in col A
            $sheet->setCellValue("A{$legendRow}", '   ');
            $sheet->getStyle("A{$legendRow}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $legend['color']]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $legend['border']]]],
            ]);

            // Text in col B
            $sheet->setCellValue("B{$legendRow}", $legend['text']);
            $sheet->getStyle("B{$legendRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);

            $legendRow++;
        }

        // ✗ row (no fill)
        $sheet->setCellValue("A{$legendRow}", '✗');
        $sheet->getStyle("A{$legendRow}")->applyFromArray([
            'font'      => ['color' => ['rgb' => 'CC0000'], 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->setCellValue("B{$legendRow}", 'Vắng (ngày thường)');

        // Note row
        $legendRow += 2;
        $sheet->setCellValue("A{$legendRow}", '* Số liệu tính cho ngày làm việc (Thứ 2 – Thứ 7). Chủ nhật không tính công.');
        $sheet->getStyle("A{$legendRow}")->applyFromArray([
            'font'      => ['italic' => true, 'color' => ['rgb' => '666666']],
        ]);
        $sheet->mergeCells("A{$legendRow}:H{$legendRow}");

        // Output
        $filename = 'bang-cham-cong-' . $month . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Xuất chi tiết từng người – mỗi nhân viên là một block riêng
    // ─────────────────────────────────────────────────────────────────────────
    public function exportDetail(string $month)
    {
        abort_unless(auth()->user()->hasRole('it'), 403);

        $service   = app(AttendanceService::class);
        $monthData = $service->getMonthData($month);

        $startOfMonth = $monthData['startOfMonth'];
        $daysInMonth  = $monthData['daysInMonth'];
        $dates        = $monthData['dates'];
        $employees    = $monthData['employees'];
        $logs         = $monthData['logs'];

        $viDayNames = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];

        // ── Spreadsheet ────────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();

        // Set default font: Times New Roman 11 cho toàn bộ workbook
        $spreadsheet->getDefaultStyle()->applyFromArray([
            'font' => [
                'name' => 'Times New Roman',
                'size' => 11,
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Chi tiết chấm công');

        $lastColLetter = 'R';

        // Helper: border thin toàn ô
        $thinBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '888888'],
                ],
            ],
        ];
        $mediumBorder = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['rgb' => '444444'],
                ],
            ],
        ];

        // Global header rows (không có border)
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->setCellValue('A1', 'Công ty: CÔNG TY TNHH DỊCH VỤ VÀ KỸ THUẬT MÔI TRƯỜNG BẢO CHÂU');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
        ]);

        $sheet->mergeCells("A2:{$lastColLetter}2");
        $sheet->setCellValue('A2', 'Địa chỉ: 180/40 Nguyễn Hữu Cảnh, Phường Thạnh, Thạnh Mỹ Tây, Hồ Chí Minh');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['name' => 'Times New Roman', 'size' => 11],
        ]);

        $sheet->mergeCells("A3:{$lastColLetter}3");
        $sheet->setCellValue('A3', 'BẢNG CHI TIẾT CHẤM CÔNG THÁNG ' . $startOfMonth->format('m/Y'));
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(18);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(24);

        // Column widths
        $colWidths = [
            'A' => 13, 'B' => 6,  'C' => 8,  'D' => 8,
            'E' => 8,  'F' => 8,  'G' => 8,  'H' => 8,
            'I' => 7,  'J' => 7,  'K' => 8,  'L' => 7,
            'M' => 7,  'N' => 8,  'O' => 8,  'P' => 8,
            'Q' => 8,  'R' => 9,
        ];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $currentRow = 4;

        foreach ($employees as $emp) {
            $empLogs = $logs->get($emp->id, collect());
            $empBlockStart = $currentRow;

            // ── Per-day data + Summary via service ───────────────────────
            $detail      = $service->buildEmployeeDetail($empLogs, $dates);
            $dayData     = $detail['dayData'];
            $totalHours  = $detail['summary']['total_hours'];
            $totalCong   = $detail['summary']['total_cong'];
            $lateTimes   = $detail['summary']['late_times'];
            $lateMinutes = $detail['summary']['late_minutes'];
            $earlyTimes  = $detail['summary']['early_times'];
            $earlyMinutes = $detail['summary']['early_minutes'];
            $vangKP      = $detail['summary']['vang_kp'];

            // ── Employee header row ───────────────────────────────────────
            $sheet->mergeCells("A{$currentRow}:{$lastColLetter}{$currentRow}");
            $sheet->setCellValue("A{$currentRow}",
                sprintf('Mã nhân viên: %05d      Tên nhân viên: %s      Phòng ban: %s',
                    $emp->device_uid, $emp->name, $emp->department ?: '---'));
            $sheet->getStyle("A{$currentRow}")->applyFromArray(array_merge_recursive([
                'font'      => ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ], $thinBorder));
            $sheet->getRowDimension($currentRow)->setRowHeight(18);
            $currentRow++;

            // ── Stats rows ────────────────────────────────────────────────
            $baseFontArr = ['name' => 'Times New Roman', 'size' => 11];

            // Row 1: Tổng giờ | Số lần trễ | Số phút trễ
            $sheet->setCellValue("A{$currentRow}", 'Tổng giờ');
            $sheet->setCellValue("C{$currentRow}", round($totalHours, 2));
            $sheet->setCellValue("E{$currentRow}", 'Số lần trễ');
            $sheet->setCellValue("H{$currentRow}", $lateTimes);
            $sheet->setCellValue("J{$currentRow}", 'Số phút trễ');
            $sheet->setCellValue("M{$currentRow}", $lateMinutes);
            $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->applyFromArray(array_merge_recursive(
                ['font' => $baseFontArr], $thinBorder));
            $currentRow++;

            // Row 2: Tổng công | Số lần sớm | Số phút sớm
            $sheet->setCellValue("A{$currentRow}", 'Tổng công');
            $sheet->setCellValue("C{$currentRow}", round($totalCong, 2));
            $sheet->setCellValue("E{$currentRow}", 'Số lần sớm');
            $sheet->setCellValue("H{$currentRow}", $earlyTimes);
            $sheet->setCellValue("J{$currentRow}", 'Số phút sớm');
            $sheet->setCellValue("M{$currentRow}", $earlyMinutes);
            $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->applyFromArray(array_merge_recursive(
                ['font' => $baseFontArr], $thinBorder));
            $currentRow++;

            // Row 3: Tăng ca | Vắng KP | Vắng CP
            $sheet->setCellValue("A{$currentRow}", 'Tăng ca');
            $sheet->setCellValue("C{$currentRow}", 0);
            $sheet->setCellValue("D{$currentRow}", 0);
            $sheet->setCellValue("E{$currentRow}", 'Vắng KP');
            $sheet->setCellValue("H{$currentRow}", $vangKP);
            $sheet->setCellValue("J{$currentRow}", 'Vắng CP');
            $sheet->setCellValue("M{$currentRow}", 0);
            $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->applyFromArray(array_merge_recursive(
                ['font' => $baseFontArr], $thinBorder));
            $currentRow++;

            // "Chi tiết" label row
            $sheet->mergeCells("A{$currentRow}:{$lastColLetter}{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", 'Chi tiết');
            $sheet->getStyle("A{$currentRow}")->applyFromArray(array_merge_recursive([
                'font'      => ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8EAED']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ], $thinBorder));
            $sheet->getRowDimension($currentRow)->setRowHeight(16);
            $currentRow++;

            // Check group header row (cột Vào/Ra nhóm 1, 2, 3)
            $sheet->mergeCells("C{$currentRow}:D{$currentRow}");
            $sheet->setCellValue("C{$currentRow}", '1');
            $sheet->mergeCells("E{$currentRow}:F{$currentRow}");
            $sheet->setCellValue("E{$currentRow}", '2');
            $sheet->mergeCells("G{$currentRow}:H{$currentRow}");
            $sheet->setCellValue("G{$currentRow}", '3');
            $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->applyFromArray(array_merge_recursive([
                'font'      => ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ], $thinBorder));
            $sheet->getRowDimension($currentRow)->setRowHeight(16);
            $currentRow++;

            // Column header row
            $headers = [
                'A' => 'Ngày',   'B' => 'Thứ',   'C' => 'Vào',   'D' => 'Ra',
                'E' => 'Vào',    'F' => 'Ra',    'G' => 'Vào',   'H' => 'Ra',
                'I' => 'Trễ',    'J' => 'Sớm',   'K' => 'Về trễ','L' => 'Giờ',
                'M' => 'Công',   'N' => 'T.Ca1', 'O' => 'T.Ca2', 'P' => 'T.Ca3',
                'Q' => 'T.Ca4',  'R' => 'Ký hiệu',
            ];
            foreach ($headers as $col => $label) {
                $sheet->setCellValue("{$col}{$currentRow}", $label);
            }
            $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->applyFromArray(array_merge_recursive([
                'font'      => ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ], $thinBorder));
            $sheet->getRowDimension($currentRow)->setRowHeight(18);
            $currentRow++;

            // ── Daily rows ────────────────────────────────────────────────
            foreach ($dates as $date) {
                $data     = $dayData[$date->day] ?? null;
                $isSunday = $date->isSunday();

                $sheet->setCellValue("A{$currentRow}", $date->format('d/m/Y'));
                $sheet->setCellValue("B{$currentRow}", $viDayNames[$date->dayOfWeek]);

                if ($data) {
                    $sheet->setCellValue("C{$currentRow}", $data['checkin']);
                    if ($data['checkout']) {
                        $sheet->setCellValue("D{$currentRow}", $data['checkout']);
                    }
                    $sheet->setCellValue("I{$currentRow}", $data['late_min']);
                    $sheet->setCellValue("J{$currentRow}", $data['early_min']);
                    $sheet->setCellValue("K{$currentRow}", $data['overtime_m']);
                    $sheet->setCellValue("L{$currentRow}", $data['work_hours'] > 0 ? $data['work_hours'] : 0);
                    $sheet->setCellValue("M{$currentRow}", $data['cong'] > 0 ? $data['cong'] : 0);
                    foreach (['N','O','P','Q'] as $c) $sheet->setCellValue("{$c}{$currentRow}", 0);
                    $sheet->setCellValue("R{$currentRow}", $data['ky_hieu']);
                } else {
                    foreach (['I','J','K','L','M','N','O','P','Q'] as $c) {
                        $sheet->setCellValue("{$c}{$currentRow}", 0);
                    }
                    $sheet->setCellValue("R{$currentRow}",
                        $isSunday ? 'V' : ($date->lte(now()) ? 'V' : ''));
                }

                // Row background
                $rowFill = match(true) {
                    $isSunday               => 'FFF5F5',
                    $data && $data['late_min'] > 0  => 'FFF0F0',
                    $data && $data['early_min'] > 0 => 'FFFDF0',
                    default                 => 'FFFFFF',
                };

                $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->applyFromArray(array_merge_recursive([
                    'font'      => ['name' => 'Times New Roman', 'size' => 11],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowFill]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ], $thinBorder));

                // Sunday thứ đỏ
                if ($isSunday) {
                    $sheet->getStyle("B{$currentRow}")->applyFromArray([
                        'font' => ['color' => ['rgb' => 'CC0000'], 'bold' => true],
                    ]);
                }
                // Đi trễ đỏ
                if ($data && $data['late_min'] > 0) {
                    $sheet->getStyle("I{$currentRow}")->applyFromArray([
                        'font' => ['color' => ['rgb' => 'CC0000'], 'bold' => true],
                    ]);
                }
                // Về sớm cam
                if ($data && $data['early_min'] > 0) {
                    $sheet->getStyle("J{$currentRow}")->applyFromArray([
                        'font' => ['color' => ['rgb' => 'E67E00'], 'bold' => true],
                    ]);
                }

                $sheet->getRowDimension($currentRow)->setRowHeight(16);
                $currentRow++;
            }

            // Medium border bao quanh toàn bộ block nhân viên
            $empBlockEnd = $currentRow - 1;
            $sheet->getStyle("A{$empBlockStart}:{$lastColLetter}{$empBlockEnd}")->applyFromArray($mediumBorder);

            // Dòng trống giữa các nhân viên
            $currentRow++;
        }

        // Output
        $filename = 'chi-tiet-cham-cong-' . $month . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
