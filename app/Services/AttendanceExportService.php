<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

final class AttendanceExportService
{
    public function __construct(private AttendanceService $attendanceService) {}

    public function buildSummarySpreadsheet(string $month): Spreadsheet
    {
        $monthData    = $this->attendanceService->getMonthData($month);
        $startOfMonth = $monthData['startOfMonth'];
        $daysInMonth  = $monthData['daysInMonth'];
        $dates        = $monthData['dates'];
        $grid         = $this->attendanceService->buildSummaryGrid(
            $monthData['employees'], $monthData['logs'], $dates, $startOfMonth
        );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Chấm công');

        $viDayNames  = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
        $lastCol     = $daysInMonth + 5;
        $lastColLetter = Coordinate::stringFromColumnIndex($lastCol);

        // Row 1: Title
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->setCellValue('A1', 'BẢNG CHẤM CÔNG THÁNG ' . $startOfMonth->format('m/Y'));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Row 2–3: Headers
        $sheet->setCellValue('A2', 'STT');
        $sheet->setCellValue('B2', 'Họ và tên');
        $col = 3;
        foreach ($dates as $date) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$colLetter}2", $date->day);
            $sheet->setCellValue("{$colLetter}3", $viDayNames[$date->dayOfWeek]);
            if ($date->isSunday()) {
                $sheet->getStyle("{$colLetter}2:{$colLetter}3")->applyFromArray([
                    'font' => ['color' => ['rgb' => 'CC0000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF0F0']],
                ]);
            }
            $col++;
        }

        foreach (['Công', 'Đi trễ', 'Về sớm'] as $label) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$colLetter}2", $label);
            $sheet->mergeCells("{$colLetter}2:{$colLetter}3");
            $col++;
        }

        $sheet->getStyle("A2:{$lastColLetter}3")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
        ]);
        $sheet->mergeCells('A2:A3');
        $sheet->mergeCells('B2:B3');
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
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $dayInfo   = $row['days'][$date->day] ?? null;
                $isSunday  = $date->isSunday();

                if ($dayInfo) {
                    $isLate  = !$isSunday && $dayInfo['first'] > '08:00';
                    $isEarly = !$isSunday && $dayInfo['last'] && $dayInfo['last'] < '17:00';

                    $text = $dayInfo['first'];
                    if ($dayInfo['last']) $text .= "\n" . $dayInfo['last'];
                    $sheet->getCell("{$colLetter}{$rowNum}")->setValue($text);
                    $sheet->getStyle("{$colLetter}{$rowNum}")->getAlignment()
                        ->setWrapText(true)
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    $fillColor = match(true) {
                        $isLate || ($isLate && $isEarly) => 'FFE0E0',
                        $isEarly  => 'FFF3CD',
                        $isSunday => 'FFF0F0',
                        default   => 'E8F5E9',
                    };
                    $sheet->getStyle("{$colLetter}{$rowNum}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                    ]);
                } else {
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

            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$colLetter}{$rowNum}", $row['work_days']);
            $sheet->getStyle("{$colLetter}{$rowNum}")->applyFromArray([
                'font'      => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
            ]);
            $col++;

            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$colLetter}{$rowNum}", $row['late_days']);
            $sheet->getStyle("{$colLetter}{$rowNum}")->applyFromArray([
                'font'      => ['bold' => $row['late_days'] > 0, 'color' => ['rgb' => $row['late_days'] > 0 ? 'CC0000' : '000000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
            ]);
            $col++;

            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$colLetter}{$rowNum}", $row['early_days']);
            $sheet->getStyle("{$colLetter}{$rowNum}")->applyFromArray([
                'font'      => ['bold' => $row['early_days'] > 0, 'color' => ['rgb' => $row['early_days'] > 0 ? 'E67E00' : '000000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
            ]);

            $rowNum++;
        }

        // Table borders
        $sheet->getStyle("A2:{$lastColLetter}" . ($rowNum - 1))->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']],
                'outline'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '333333']],
            ],
        ]);

        $sheet->freezePane('C4');

        // Legend
        $legendRow = $rowNum + 1;
        $sheet->setCellValue("A{$legendRow}", 'Chú thích:');
        $sheet->getStyle("A{$legendRow}")->applyFromArray(['font' => ['bold' => true]]);

        $legends = [
            ['color' => 'E8F5E9', 'border' => '4CAF50', 'text' => 'Đúng giờ'],
            ['color' => 'FFE0E0', 'border' => 'CC0000', 'text' => 'Đi trễ (vào sau 08:00)'],
            ['color' => 'FFF3CD', 'border' => 'E67E00', 'text' => 'Về sớm (ra trước 17:00)'],
            ['color' => 'FFFFFF', 'border' => '888888', 'text' => 'Chỉ có giờ vào (chưa ra)'],
            ['color' => 'FFF0F0', 'border' => 'CC0000', 'text' => 'Chủ nhật'],
        ];
        $legendRow++;
        foreach ($legends as $legend) {
            $sheet->setCellValue("A{$legendRow}", '   ');
            $sheet->getStyle("A{$legendRow}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $legend['color']]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $legend['border']]]],
            ]);
            $sheet->setCellValue("B{$legendRow}", $legend['text']);
            $sheet->getStyle("B{$legendRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);
            $legendRow++;
        }
        $sheet->setCellValue("A{$legendRow}", '✗');
        $sheet->getStyle("A{$legendRow}")->applyFromArray([
            'font'      => ['color' => ['rgb' => 'CC0000'], 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->setCellValue("B{$legendRow}", 'Vắng (ngày thường)');

        $legendRow += 2;
        $sheet->setCellValue("A{$legendRow}", '* Số liệu tính cho ngày làm việc (Thứ 2 – Thứ 7). Chủ nhật không tính công.');
        $sheet->getStyle("A{$legendRow}")->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '666666']],
        ]);
        $sheet->mergeCells("A{$legendRow}:H{$legendRow}");

        return $spreadsheet;
    }

    public function buildDetailSpreadsheet(string $month): Spreadsheet
    {
        $monthData    = $this->attendanceService->getMonthData($month);
        $startOfMonth = $monthData['startOfMonth'];
        $dates        = $monthData['dates'];
        $employees    = $monthData['employees'];
        $logs         = $monthData['logs'];

        $viDayNames = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->applyFromArray([
            'font'      => ['name' => 'Times New Roman', 'size' => 11],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Chi tiết chấm công');

        $lastColLetter = 'R';
        $thinBorder    = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '888888']]]];
        $mediumBorder  = ['borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '444444']]]];

        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->setCellValue('A1', 'Công ty: CÔNG TY TNHH DỊCH VỤ VÀ KỸ THUẬT MÔI TRƯỜNG BẢO CHÂU');
        $sheet->getStyle('A1')->applyFromArray(['font' => ['name' => 'Times New Roman', 'size' => 11, 'bold' => true]]);

        $sheet->mergeCells("A2:{$lastColLetter}2");
        $sheet->setCellValue('A2', 'Địa chỉ: 180/40 Nguyễn Hữu Cảnh, Phường Thạnh, Thạnh Mỹ Tây, Hồ Chí Minh');
        $sheet->getStyle('A2')->applyFromArray(['font' => ['name' => 'Times New Roman', 'size' => 11]]);

        $sheet->mergeCells("A3:{$lastColLetter}3");
        $sheet->setCellValue('A3', 'BẢNG CHI TIẾT CHẤM CÔNG THÁNG ' . $startOfMonth->format('m/Y'));
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        foreach ([1 => 18, 2 => 18, 3 => 24] as $r => $h) {
            $sheet->getRowDimension($r)->setRowHeight($h);
        }

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
            $empLogs       = $logs->get($emp->id, collect());
            $empBlockStart = $currentRow;

            $detail       = $this->attendanceService->buildEmployeeDetail($empLogs, $dates);
            $dayData      = $detail['dayData'];
            $summary      = $detail['summary'];

            // Employee header row
            $sheet->mergeCells("A{$currentRow}:{$lastColLetter}{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", sprintf(
                'Mã nhân viên: %05d      Tên nhân viên: %s      Phòng ban: %s',
                $emp->device_uid, $emp->name, $emp->department ?: '---'
            ));
            $sheet->getStyle("A{$currentRow}")->applyFromArray(array_merge_recursive([
                'font'      => ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ], $thinBorder));
            $sheet->getRowDimension($currentRow)->setRowHeight(18);
            $currentRow++;

            $baseFontArr = ['name' => 'Times New Roman', 'size' => 11];

            // Stats rows
            $statsRows = [
                ['Tổng giờ', 'C', round($summary['total_hours'], 2), 'Số lần trễ', 'H', $summary['late_times'], 'Số phút trễ', 'M', $summary['late_minutes']],
                ['Tổng công', 'C', round($summary['total_cong'], 2), 'Số lần sớm', 'H', $summary['early_times'], 'Số phút sớm', 'M', $summary['early_minutes']],
                ['Tăng ca', 'C', 0, 'Vắng KP', 'H', $summary['vang_kp'], 'Vắng CP', 'M', 0],
            ];
            foreach ($statsRows as $sr) {
                $sheet->setCellValue("A{$currentRow}", $sr[0]);
                $sheet->setCellValue("{$sr[1]}{$currentRow}", $sr[2]);
                $sheet->setCellValue("E{$currentRow}", $sr[3]);
                $sheet->setCellValue("{$sr[4]}{$currentRow}", $sr[5]);
                $sheet->setCellValue("J{$currentRow}", $sr[6]);
                $sheet->setCellValue("{$sr[7]}{$currentRow}", $sr[8]);
                $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")
                    ->applyFromArray(array_merge_recursive(['font' => $baseFontArr], $thinBorder));
                $currentRow++;
            }

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

            // Check group header
            foreach ([['C', 'D', '1'], ['E', 'F', '2'], ['G', 'H', '3']] as [$from, $to, $label]) {
                $sheet->mergeCells("{$from}{$currentRow}:{$to}{$currentRow}");
                $sheet->setCellValue("{$from}{$currentRow}", $label);
            }
            $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->applyFromArray(array_merge_recursive([
                'font'      => ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ], $thinBorder));
            $sheet->getRowDimension($currentRow)->setRowHeight(16);
            $currentRow++;

            // Column header row
            $headers = [
                'A' => 'Ngày', 'B' => 'Thứ', 'C' => 'Vào', 'D' => 'Ra',
                'E' => 'Vào',  'F' => 'Ra',  'G' => 'Vào', 'H' => 'Ra',
                'I' => 'Trễ',  'J' => 'Sớm', 'K' => 'Về trễ', 'L' => 'Giờ',
                'M' => 'Công', 'N' => 'T.Ca1', 'O' => 'T.Ca2', 'P' => 'T.Ca3',
                'Q' => 'T.Ca4', 'R' => 'Ký hiệu',
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

            // Daily rows
            foreach ($dates as $date) {
                $data     = $dayData[$date->day] ?? null;
                $isSunday = $date->isSunday();

                $sheet->setCellValue("A{$currentRow}", $date->format('d/m/Y'));
                $sheet->setCellValue("B{$currentRow}", $viDayNames[$date->dayOfWeek]);

                if ($data) {
                    $sheet->setCellValue("C{$currentRow}", $data['checkin']);
                    if ($data['checkout']) $sheet->setCellValue("D{$currentRow}", $data['checkout']);
                    $sheet->setCellValue("I{$currentRow}", $data['late_min']);
                    $sheet->setCellValue("J{$currentRow}", $data['early_min']);
                    $sheet->setCellValue("K{$currentRow}", $data['overtime_m']);
                    $sheet->setCellValue("L{$currentRow}", $data['work_hours'] > 0 ? $data['work_hours'] : 0);
                    $sheet->setCellValue("M{$currentRow}", $data['cong'] > 0 ? $data['cong'] : 0);
                    foreach (['N', 'O', 'P', 'Q'] as $c) $sheet->setCellValue("{$c}{$currentRow}", 0);
                    $sheet->setCellValue("R{$currentRow}", $data['ky_hieu']);
                } else {
                    foreach (['I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q'] as $c) {
                        $sheet->setCellValue("{$c}{$currentRow}", 0);
                    }
                    $sheet->setCellValue("R{$currentRow}", $isSunday ? 'V' : ($date->lte(now()) ? 'V' : ''));
                }

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

                if ($isSunday) {
                    $sheet->getStyle("B{$currentRow}")->applyFromArray(['font' => ['color' => ['rgb' => 'CC0000'], 'bold' => true]]);
                }
                if ($data && $data['late_min'] > 0) {
                    $sheet->getStyle("I{$currentRow}")->applyFromArray(['font' => ['color' => ['rgb' => 'CC0000'], 'bold' => true]]);
                }
                if ($data && $data['early_min'] > 0) {
                    $sheet->getStyle("J{$currentRow}")->applyFromArray(['font' => ['color' => ['rgb' => 'E67E00'], 'bold' => true]]);
                }

                $sheet->getRowDimension($currentRow)->setRowHeight(16);
                $currentRow++;
            }

            $sheet->getStyle("A{$empBlockStart}:{$lastColLetter}" . ($currentRow - 1))->applyFromArray($mediumBorder);
            $currentRow++;
        }

        return $spreadsheet;
    }
}
