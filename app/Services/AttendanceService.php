<?php

namespace App\Services;

use App\Models\AttendanceEmployee;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceService
{
    /**
     * Dữ liệu dùng chung cho cả 3 nơi: render, export, exportDetail.
     *
     * @return array{employees: Collection, logs: Collection, dates: Carbon[], daysInMonth: int, startOfMonth: Carbon}
     */
    public function getMonthData(string $month, bool $onlyWithLogs = true): array
    {
        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth   = Carbon::parse($month . '-01')->endOfMonth();
        $daysInMonth  = $startOfMonth->daysInMonth;

        $logs = AttendanceLog::whereBetween('checked_at', [$startOfMonth, $endOfMonth])
            ->orderBy('checked_at')
            ->get()
            ->groupBy('employee_id');

        $employees = $onlyWithLogs
            ? AttendanceEmployee::where('is_active', true)->whereIn('id', $logs->keys())->orderBy('device_uid')->get()
            : AttendanceEmployee::where('is_active', true)->orderBy('device_uid')->get();

        $dates = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dates[] = $startOfMonth->copy()->day($d);
        }

        return compact('employees', 'logs', 'dates', 'daysInMonth', 'startOfMonth');
    }

    /**
     * Grid tổng hợp — dùng cho render() và export().
     *
     * @return array[] Mỗi phần tử: [employee, days, work_days, late_days, early_days]
     */
    public function buildSummaryGrid(Collection $employees, Collection $logs, array $dates, Carbon $startOfMonth): array
    {
        $grid = [];

        foreach ($employees as $emp) {
            $empLogs = $logs->get($emp->id, collect());
            $dayData = [];

            foreach ($dates as $date) {
                $dayLogs = $empLogs->filter(fn($l) => $l->checked_at->isSameDay($date))
                    ->sortBy('checked_at');

                if ($dayLogs->isEmpty()) {
                    $dayData[$date->day] = null;
                } else {
                    $firstTime = $dayLogs->first()->checked_at;
                    $lastTime  = $dayLogs->count() > 1 ? $dayLogs->last()->checked_at : null;
                    $checkinM  = $firstTime->hour * 60 + $firstTime->minute;
                    $checkoutM = $lastTime ? ($lastTime->hour * 60 + $lastTime->minute) : null;

                    $lateMin  = max(0, $checkinM - 480);
                    $earlyMin = $checkoutM !== null ? max(0, 1020 - $checkoutM) : 0;

                    $dayData[$date->day] = [
                        'first'     => $firstTime->format('H:i'),
                        'last'      => $lastTime ? $lastTime->format('H:i') : null,
                        'count'     => $dayLogs->count(),
                        'late_min'  => $lateMin,
                        'early_min' => $earlyMin,
                    ];
                }
            }

            $workDays     = 0;
            $lateDays     = 0;
            $earlyDays    = 0;
            $lateMinutes  = 0;
            $earlyMinutes = 0;

            foreach ($dayData as $day => $data) {
                if (!$data) continue;
                $dateObj = $startOfMonth->copy()->day($day);
                if ($dateObj->isSunday()) continue;

                if ($data['last']) $workDays++;
                if ($data['late_min'] > 0) {
                    $lateDays++;
                    $lateMinutes += $data['late_min'];
                }
                if ($data['early_min'] > 0) {
                    $earlyDays++;
                    $earlyMinutes += $data['early_min'];
                }
            }

            $grid[] = [
                'employee'      => $emp,
                'days'          => $dayData,
                'work_days'     => $workDays,
                'late_days'     => $lateDays,
                'early_days'    => $earlyDays,
                'late_minutes'  => $lateMinutes,
                'early_minutes' => $earlyMinutes,
            ];
        }

        return $grid;
    }

    /**
     * Dữ liệu chi tiết cho 1 nhân viên — dùng trong exportDetail().
     *
     * @return array{dayData: array, summary: array}
     */
    public function buildEmployeeDetail(Collection $empLogs, array $dates, int $startMin = 480, int $endMin = 1020, int $lunchMin = 60): array
    {
        $dayData = [];

        foreach ($dates as $date) {
            $dayLogs  = $empLogs->filter(fn($l) => $l->checked_at->isSameDay($date))
                ->sortBy('checked_at')->values();
            $isSunday = $date->isSunday();

            if ($dayLogs->isEmpty()) {
                $dayData[$date->day] = null;
                continue;
            }

            $first     = $dayLogs->first()->checked_at;
            $last      = $dayLogs->count() > 1 ? $dayLogs->last()->checked_at : null;
            $checkinM  = $first->hour * 60 + $first->minute;
            $checkoutM = $last ? ($last->hour * 60 + $last->minute) : null;
            $hasOut    = $checkoutM !== null;

            $lateMin   = (!$isSunday && $hasOut) ? max(0, $checkinM - $startMin) : 0;
            $earlyMin  = (!$isSunday && $hasOut) ? max(0, $endMin - $checkoutM) : 0;
            $overtimeM = (!$isSunday && $hasOut) ? max(0, $checkoutM - $endMin) : 0;

            if (!$isSunday && $hasOut) {
                $effStart    = max($checkinM, $startMin);
                $effEnd      = min($checkoutM, $endMin);
                $workMinutes = max(0, $effEnd - $effStart - $lunchMin);
                $workHours   = round($workMinutes / 60, 2);
                $cong        = min(1.0, round($workHours / 8, 2));
            } else {
                $workHours = 0.0;
                $cong      = 0.0;
            }

            $dayData[$date->day] = [
                'checkin'    => $first->format('H:i'),
                'checkout'   => $last ? $last->format('H:i') : null,
                'late_min'   => $lateMin,
                'early_min'  => $earlyMin,
                'overtime_m' => $overtimeM,
                'work_hours' => $workHours,
                'cong'       => $cong,
                'ky_hieu'    => $isSunday ? 'V' : ($hasOut ? 'X' : 'O'),
                'is_sunday'  => $isSunday,
            ];
        }

        // Summary
        $totalHours = $totalCong = $lateTimes = $lateMinutes = 0.0;
        $earlyTimes = $earlyMinutes = $vangKP = 0;

        foreach ($dates as $date) {
            if ($date->isSunday()) continue;
            $data = $dayData[$date->day] ?? null;
            if (!$data) {
                if ($date->lte(now())) $vangKP++;
                continue;
            }
            $totalHours  += $data['work_hours'];
            $totalCong   += $data['cong'];
            if ($data['late_min'] > 0)  { $lateTimes++;  $lateMinutes  += $data['late_min']; }
            if ($data['early_min'] > 0) { $earlyTimes++; $earlyMinutes += $data['early_min']; }
        }

        return [
            'dayData' => $dayData,
            'summary' => [
                'total_hours'   => round($totalHours, 2),
                'total_cong'    => round($totalCong, 2),
                'late_times'    => $lateTimes,
                'late_minutes'  => $lateMinutes,
                'early_times'   => $earlyTimes,
                'early_minutes' => $earlyMinutes,
                'vang_kp'       => $vangKP,
            ],
        ];
    }
}
