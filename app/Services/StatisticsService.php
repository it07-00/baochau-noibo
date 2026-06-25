<?php

namespace App\Services;

use App\Enums\QuotationStatus;
use App\Enums\Role as RoleEnum;
use App\Models\ContractAssignment;
use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\DailyReport;
use App\Models\Quotation;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Support\DailyReportVisibility;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class StatisticsService
{
    /**
     * Get all statistics data for the dashboard.
     */
    public function getDashboardData(
        User $currentUser,
        int $year,
        string $month = '',
        string $contractDateFrom = '',
        string $contractDateTo = '',
        string $chartMode = 'quarter',
        array $currentItStats = [],
        array $currentEnvData = [],
        string $activeTab = 'overview'
    ): array {
        $selectedMonth = $month !== '' ? (int) $month : null;

        $contractDateFromParsed = preg_match('/^\d{4}-\d{2}-\d{2}$/', $contractDateFrom) ? $contractDateFrom : null;
        $contractDateToParsed = preg_match('/^\d{4}-\d{2}-\d{2}$/', $contractDateTo) ? $contractDateTo : null;

        if ($contractDateFromParsed !== null && $contractDateToParsed !== null && $contractDateFromParsed > $contractDateToParsed) {
            [$contractDateFromParsed, $contractDateToParsed] = [$contractDateToParsed, $contractDateFromParsed];
        }

        $applyContractDateFilter = function ($query, ?int $monthForFallback = null, string $dateColumn = 'signed_at') use ($contractDateFromParsed, $contractDateToParsed, $year) {
            if ($contractDateFromParsed !== null || $contractDateToParsed !== null) {
                if ($contractDateFromParsed !== null) {
                    $query->whereDate($dateColumn, '>=', $contractDateFromParsed);
                }
                if ($contractDateToParsed !== null) {
                    $query->whereDate($dateColumn, '<=', $contractDateToParsed);
                }

                return $query;
            }

            $query->whereYear($dateColumn, $year);
            if ($monthForFallback !== null) {
                $query->whereMonth($dateColumn, $monthForFallback);
            }

            return $query;
        };

        // All contract models use 'signed_at' as their date column.
        $getDateColumn = function (): string {
            return 'signed_at';
        };

        // ── KPI tổng quan ──────────────────────────────
        $customerQuery = Customer::whereYear('created_at', $year);
        if ($selectedMonth !== null) {
            $customerQuery->whereMonth('created_at', $selectedMonth);
        }
        $totalCustomers = (int) $customerQuery->count();

        $contractTypes = [
            'Chất thải' => ContractWaste::class,
            'Pháp lý & Hồ sơ MT' => ContractLegal::class,
            'Ứng phó sự cố' => ContractTechnical::class,
            'Nghiên cứu và chuyển đổi công nghệ' => ContractResearch::class,
            'Phát triển bền vững' => ContractSustainability::class,
            'Giảm phát thải, tiết kiệm năng lượng' => ContractEmission::class,
        ];

        $byType = [];
        $totalContracts = 0;
        $totalContractValue = 0;

        foreach ($contractTypes as $label => $model) {
            $dateColumn = $getDateColumn();

            $yearOrDateQuery = $model::query();
            $applyContractDateFilter($yearOrDateQuery, null, $dateColumn);
            $row = $yearOrDateQuery
                ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(value),0) as val')
                ->first();

            $byType[$label] = [
                'count' => (int) ($row->cnt ?? 0),
                'value' => (float) ($row->val ?? 0),
            ];

            if ($selectedMonth !== null) {
                $kpiQuery = $model::query();
                $applyContractDateFilter($kpiQuery, $selectedMonth, $dateColumn);
                $kpiRow = $kpiQuery->selectRaw('COUNT(*) as cnt, COALESCE(SUM(value),0) as val')->first();
                $totalContracts += (int) ($kpiRow->cnt ?? 0);
                $totalContractValue += (float) ($kpiRow->val ?? 0);
            } else {
                $totalContracts += (int) ($row->cnt ?? 0);
                $totalContractValue += (float) ($row->val ?? 0);
            }
        }

        // ── Doanh số ghi nhận từ cột doanh số (revenue) trong hợp đồng ──────
        $totalSales = 0;
        foreach ($contractTypes as $modelClass) {
            $modelQuery = $modelClass::query();
            if ($contractDateFromParsed || $contractDateToParsed) {
                if ($contractDateFromParsed) {
                    $modelQuery->whereDate(DB::raw('COALESCE(submitted_at, signed_at)'), '>=', $contractDateFromParsed);
                }
                if ($contractDateToParsed) {
                    $modelQuery->whereDate(DB::raw('COALESCE(submitted_at, signed_at)'), '<=', $contractDateToParsed);
                }
            } else {
                $modelQuery->whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $year);
                if ($selectedMonth !== null) {
                    $modelQuery->whereMonth(DB::raw('COALESCE(submitted_at, signed_at)'), $selectedMonth);
                }
            }
            $totalSales += (float) $modelQuery->sum('revenue');
        }

        $totalRevenue = 0;

        // ── Theo tháng: tất cả 6 loại HĐ ký ─────────
        $contractMonthly = [];
        foreach ($contractTypes as $model) {
            $dateColumn = $getDateColumn();

            // Số lượng HĐ và giá trị: theo ngày ký (signed_at)
            $monthlyQuery = $model::query();
            $applyContractDateFilter($monthlyQuery, null, $dateColumn);
            $rows = $monthlyQuery
                ->selectRaw("MONTH({$dateColumn}) as m, COUNT(*) as cnt, SUM(value) as val")
                ->groupByRaw("MONTH({$dateColumn})")
                ->get()
                ->keyBy('m');
            foreach ($rows as $m => $row) {
                $contractMonthly[$m]['cnt'] = ($contractMonthly[$m]['cnt'] ?? 0) + $row->cnt;
                $contractMonthly[$m]['val'] = ($contractMonthly[$m]['val'] ?? 0) + (float) $row->val;
            }

            // Doanh số: theo ngày xuất hóa đơn COALESCE(submitted_at, signed_at)
            $revQuery = $model::query()
                ->whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $year)
                ->selectRaw('MONTH(COALESCE(submitted_at, signed_at)) as m, SUM(revenue) as rev')
                ->groupByRaw('MONTH(COALESCE(submitted_at, signed_at))')
                ->get();
            foreach ($revQuery as $row) {
                $contractMonthly[$row->m]['rev'] = ($contractMonthly[$row->m]['rev'] ?? 0) + (float) $row->rev;
            }
        }

        // ── Tiến độ thu tiền ────────────────────────
        $totalPaymentDue = 0;
        $totalPaymentPaid = 0;

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthly[$m] = [
                'contracts' => $contractMonthly[$m]['cnt'] ?? 0,
                'value' => (float) ($contractMonthly[$m]['val'] ?? 0),
                'sales' => (float) ($contractMonthly[$m]['rev'] ?? 0),
                'revenue' => 0,
                'payment_due' => 0,
                'payment_paid' => 0,
            ];
        }

        // ── Nhắc nhở báo cáo ngày ─────────────────────
        $dailyReportReminder = null;
        if (! $currentUser->hasRole(RoleEnum::GIAM_DOC->value)) {
            $hasReportToday = DailyReport::where('user_id', $currentUser->id)
                ->whereDate('date', today())
                ->exists();
            $dailyReportReminder = ! $hasReportToday;
        }

        // ── Nhắc lịch công tác (mọi role) ─────────────────
        $todayDate = today();
        $nowTime = now();
        $tomorrowDate = today()->addDay();

        $workScheduleHasTime = Schema::hasColumn('work_schedules', 'start_time');
        $workScheduleHasEndTime = Schema::hasColumn('work_schedules', 'end_time');

        $applyWorkScheduleScope = function ($query) use ($currentUser) {
            $query->where(function ($inner) use ($currentUser) {
                $inner->where('user_id', $currentUser->id)
                    ->orWhereHas('participants', fn ($q) => $q->where('users.id', $currentUser->id));
            });
        };

        $workScheduleTodayQuery = WorkSchedule::query()
            ->where($applyWorkScheduleScope)
            ->whereDate('start_date', '<=', $todayDate)
            ->where(function ($query) use ($todayDate) {
                $query->whereDate('end_date', '>=', $todayDate)
                    ->orWhere(function ($single) use ($todayDate) {
                        $single->whereNull('end_date')
                            ->whereDate('start_date', '=', $todayDate);
                    });
            });

        $workScheduleTodayTotal = (clone $workScheduleTodayQuery)->count();

        $workScheduleOverdueTotal = WorkSchedule::query()
            ->where($applyWorkScheduleScope)
            ->where(function ($query) use ($todayDate) {
                $query->where(function ($multi) use ($todayDate) {
                    $multi->whereNotNull('end_date')
                        ->whereDate('end_date', '<', $todayDate);
                })->orWhere(function ($single) use ($todayDate) {
                    $single->whereNull('end_date')
                        ->whereDate('start_date', '<', $todayDate);
                });
            })
            ->count();

        $workScheduleRaw = (clone $workScheduleTodayQuery)
            ->with('user:id,name')
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        $statusClassMap = [
            'overdue' => 'bg-danger-subtle text-danger-emphasis border border-danger-subtle',
            'in_progress' => 'bg-success-subtle text-success-emphasis border border-success-subtle',
            'upcoming' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
        ];

        $workScheduleItems = $workScheduleRaw->map(function (WorkSchedule $schedule) use (
            $workScheduleHasTime,
            $workScheduleHasEndTime,
            $nowTime,
            $statusClassMap
        ) {
            $startDate = $schedule->start_date->copy();
            $effectiveEndDate = $schedule->effective_end_date->copy();

            $startAt = $startDate->copy()->startOfDay();
            $endAt = $effectiveEndDate->copy()->endOfDay();
            $timeLabel = 'Cả ngày';

            if ($workScheduleHasTime) {
                $startTimeValue = $schedule->getAttribute('start_time');
                $endTimeValue = $workScheduleHasEndTime ? $schedule->getAttribute('end_time') : null;

                if (! empty($startTimeValue)) {
                    $startAt = Carbon::parse($startDate->toDateString().' '.$startTimeValue);
                    $timeLabel = $startAt->format('H:i');
                }

                if (! empty($endTimeValue)) {
                    $endAt = Carbon::parse($effectiveEndDate->toDateString().' '.$endTimeValue);
                    if ($endAt->lt($startAt)) {
                        $endAt = $startAt->copy()->addHour();
                    }

                    $timeLabel = ! empty($startTimeValue)
                        ? $startAt->format('H:i').' - '.$endAt->format('H:i')
                        : $endAt->format('H:i');
                } elseif (! empty($startTimeValue)) {
                    $endAt = $startAt->copy()->addHour();
                }
            }

            if ($endAt->lt($nowTime)) {
                $statusKey = 'overdue';
                $statusLabel = 'Quá hạn';
                $statusOrder = 2;
                $distanceToNow = (int) $endAt->diffInSeconds($nowTime);
            } elseif ($startAt->lte($nowTime) && $endAt->gte($nowTime)) {
                $statusKey = 'in_progress';
                $statusLabel = 'Đang diễn ra';
                $statusOrder = 0;
                $distanceToNow = 0;
            } else {
                $statusKey = 'upcoming';
                $statusLabel = 'Sắp tới';
                $statusOrder = 1;
                $distanceToNow = (int) $nowTime->diffInSeconds($startAt);
            }

            return [
                'id' => $schedule->id,
                'title' => $schedule->title,
                'description' => Str::limit((string) ($schedule->description ?? ''), 120),
                'owner_name' => $schedule->user?->name ?? 'Hệ thống',
                'time_label' => $timeLabel,
                'date_label' => $startDate->format('d/m/Y').($effectiveEndDate->ne($startDate) ? ' - '.$effectiveEndDate->format('d/m/Y') : ''),
                'status_key' => $statusKey,
                'status_label' => $statusLabel,
                'status_class' => $statusClassMap[$statusKey],
                'start_at' => $startAt,
                'start_timestamp' => $startAt->timestamp,
                'status_order' => $statusOrder,
                'distance_to_now' => $distanceToNow,
            ];
        });

        $workScheduleTomorrowTotal = WorkSchedule::query()
            ->where($applyWorkScheduleScope)
            ->whereDate('start_date', '<=', $tomorrowDate)
            ->where(function ($query) use ($tomorrowDate) {
                $query->whereDate('end_date', '>=', $tomorrowDate)
                    ->orWhere(function ($single) use ($tomorrowDate) {
                        $single->whereNull('end_date')
                            ->whereDate('start_date', '=', $tomorrowDate);
                    });
            })
            ->count();

        $workScheduleRecentItems = $workScheduleItems
            ->sortBy([
                ['status_order', 'asc'],
                ['distance_to_now', 'asc'],
                ['start_timestamp', 'asc'],
            ])
            ->take(5)
            ->values();

        $workScheduleSummary = [
            'today_total' => (int) $workScheduleTodayTotal,
            'upcoming_tomorrow' => (int) $workScheduleTomorrowTotal,
            'overdue' => (int) $workScheduleOverdueTotal,
        ];

        $canSeeTechnical = $currentUser->hasAnyRole([RoleEnum::GIAM_DOC->value, RoleEnum::KY_THUAT->value]);
        $canSeeConsulting = $currentUser->hasAnyRole([RoleEnum::GIAM_DOC->value, RoleEnum::TU_VAN->value]);
        $canSeeFinance = ! $currentUser->hasAnyRole([RoleEnum::TU_VAN->value, RoleEnum::KY_THUAT->value]);
        $canSeeInvoiceTasks = $currentUser->hasAnyRole([RoleEnum::KE_TOAN->value, RoleEnum::GIAM_DOC->value]);
        $canSeeSalesTasks = $currentUser->hasAnyRole([RoleEnum::KINH_DOANH->value, RoleEnum::TP_KINH_DOANH->value, RoleEnum::GIAM_DOC->value]);

        // ── Needs Action Alerts ───────────────────────
        $needsAction = [
            'missing_bao_chau_invoice' => 0,
            'missing_subcontractor_invoice' => 0,
            'unpaid_subcontractor_payment' => 0,
            'pending_quotations' => 0,
            'incomplete_assigned_contracts' => 0,
            'upcoming_renewals' => 0,
        ];

        foreach ($contractTypes as $modelClass) {
            $dateColumn = $getDateColumn();

            // All contract tables have these columns. Querying directly avoids dynamic schema checking!
            $missingBaoChauQuery = $modelClass::query()
                ->where(function ($query) {
                    $query->whereNull('shd_bc')->orWhere('shd_bc', '');
                });
            $applyContractDateFilter($missingBaoChauQuery, $selectedMonth, $dateColumn);
            $needsAction['missing_bao_chau_invoice'] += (int) $missingBaoChauQuery->count();

            $missingSubcontractorQuery = $modelClass::query()
                ->whereNotNull('handler_id')
                ->where('handler_id', '!=', 0)
                ->where(function ($query) {
                    $query->whereNull('shd_cxl')->orWhere('shd_cxl', '');
                });
            $applyContractDateFilter($missingSubcontractorQuery, $selectedMonth, $dateColumn);
            $needsAction['missing_subcontractor_invoice'] += (int) $missingSubcontractorQuery->count();

            $unpaidSubcontractorQuery = $modelClass::query()
                ->where('ncc_payment', '>', 0)
                ->where(function ($query) {
                    $query->whereNull('ncc_payment_status')
                        ->orWhere('ncc_payment_status', '!=', 'paid');
                });
            $applyContractDateFilter($unpaidSubcontractorQuery, $selectedMonth, $dateColumn);
            $needsAction['unpaid_subcontractor_payment'] += (int) $unpaidSubcontractorQuery->count();
        }

        $pendingQuotationQuery = Quotation::query()
            ->whereIn('status', [
                QuotationStatus::DANG_THEO_DOI->value,
                QuotationStatus::HEN_BAO_GIA->value,
            ]);

        if ($contractDateFromParsed !== null || $contractDateToParsed !== null) {
            if ($contractDateFromParsed !== null) {
                $pendingQuotationQuery->whereDate('date', '>=', $contractDateFromParsed);
            }
            if ($contractDateToParsed !== null) {
                $pendingQuotationQuery->whereDate('date', '<=', $contractDateToParsed);
            }
        } else {
            $pendingQuotationQuery->whereYear('date', $year);
            if ($selectedMonth !== null) {
                $pendingQuotationQuery->whereMonth('date', $selectedMonth);
            }
        }

        $needsAction['pending_quotations'] = (int) $pendingQuotationQuery->count();

        if ($currentUser->hasAnyRole([RoleEnum::TU_VAN->value, RoleEnum::KY_THUAT->value])) {
            $completionDeadlineTo = now()->addDays(15)->endOfDay();

            foreach (array_values($contractTypes) as $modelClass) {
                $incompleteQuery = $modelClass::query()
                    ->whereHas('assignments', fn ($query) => $query
                        ->where('user_id', $currentUser->id)
                        ->whereNotNull('deadline')
                        ->whereDate('deadline', '<=', $completionDeadlineTo))
                    ->where(function ($query) {
                        $query->whereNull('workflow_status')
                            ->orWhere('workflow_status', '!=', 'finished');
                    });

                $needsAction['incomplete_assigned_contracts'] += (int) $incompleteQuery->count();
            }
        }

        if ($canSeeSalesTasks) {
            $renewalReminderFrom = now()->subYear()->startOfDay();
            $renewalReminderTo = now()->subMonths(11)->endOfDay();
            $isRestrictedSales = $currentUser->hasRole(RoleEnum::KINH_DOANH->value)
                && ! $currentUser->hasAnyRole([RoleEnum::GIAM_DOC->value, RoleEnum::TP_KINH_DOANH->value]);

            foreach (array_values($contractTypes) as $modelClass) {
                $renewalQuery = $modelClass::query()
                    ->whereBetween('signed_at', [$renewalReminderFrom, $renewalReminderTo])
                    ->where(function ($query) {
                        $query->whereNull('is_renewal')->orWhere('is_renewal', false);
                    })
                    ->where(function ($query) {
                        $query->whereNull('renewal_status')
                            ->orWhereNotIn('renewal_status', ['ĐÃ TÁI KÝ', 'ĐÃ KÝ', 'KHÔNG TÁI KÝ']);
                    });

                if ($isRestrictedSales) {
                    $renewalQuery->where('staff_id', $currentUser->id);
                }

                $needsAction['upcoming_renewals'] += (int) $renewalQuery->count();
            }
        }

        $visibleNeedsActionKeys = [];
        if ($canSeeInvoiceTasks) {
            $visibleNeedsActionKeys = array_merge($visibleNeedsActionKeys, [
                'missing_bao_chau_invoice',
                'missing_subcontractor_invoice',
                'unpaid_subcontractor_payment',
            ]);
        }
        if ($currentUser->hasAnyRole([RoleEnum::TU_VAN->value, RoleEnum::KY_THUAT->value])) {
            $visibleNeedsActionKeys[] = 'incomplete_assigned_contracts';
        }
        if ($canSeeSalesTasks) {
            $visibleNeedsActionKeys[] = 'upcoming_renewals';
        }

        $needsActionTotal = collect($visibleNeedsActionKeys)
            ->sum(fn ($key) => (int) ($needsAction[$key] ?? 0));

        // ── Insight theo tháng: báo giá vs ký hợp đồng theo dịch vụ/khu vực ──
        $insightMonth = $selectedMonth ?? (int) now()->month;

        $quotedByService = Quotation::whereYear('date', $year)
            ->whereMonth('date', $insightMonth)
            ->selectRaw("COALESCE(NULLIF(TRIM(service), ''), 'Khác') as label, COUNT(*) as cnt")
            ->groupBy('label')
            ->pluck('cnt', 'label')
            ->toArray();

        $quotedByProvince = Quotation::whereYear('date', $year)
            ->whereMonth('date', $insightMonth)
            ->selectRaw("COALESCE(NULLIF(TRIM(province), ''), 'Không rõ') as label, COUNT(*) as cnt")
            ->groupBy('label')
            ->pluck('cnt', 'label')
            ->toArray();

        $signedContractByService = [];
        $signedContractByProvince = [];
        $revenueByProvinceFromContracts = [];

        foreach (array_values($contractTypes) as $modelClass) {
            $dateColumn = $getDateColumn();

            $serviceQuery = $modelClass::query();
            $applyContractDateFilter($serviceQuery, $insightMonth, $dateColumn);
            $serviceRows = $serviceQuery
                ->selectRaw("COALESCE(NULLIF(TRIM(loai_dich_vu), ''), 'Khác') as label, COUNT(*) as cnt")
                ->groupBy('label')
                ->get();

            foreach ($serviceRows as $row) {
                $label = (string) $row->label;
                $signedContractByService[$label] = ($signedContractByService[$label] ?? 0) + (int) $row->cnt;
            }

            $provinceQuery = $modelClass::query();
            $applyContractDateFilter($provinceQuery, $insightMonth, $dateColumn);
            $provinceRows = $provinceQuery
                ->selectRaw("COALESCE(NULLIF(TRIM(province), ''), 'Không rõ') as label, COUNT(*) as cnt, COALESCE(SUM(revenue), 0) as rev")
                ->groupBy('label')
                ->get();

            foreach ($provinceRows as $row) {
                $label = (string) $row->label;
                $signedContractByProvince[$label] = ($signedContractByProvince[$label] ?? 0) + (int) $row->cnt;
                $revenueByProvinceFromContracts[$label] = ($revenueByProvinceFromContracts[$label] ?? 0) + (float) $row->rev;
            }
        }

        $serviceLabels = collect(array_keys($quotedByService))
            ->merge(array_keys($signedContractByService))
            ->unique()
            ->sort()
            ->values();

        $serviceInsightChart = [
            'labels' => $serviceLabels->all(),
            'quoted' => $serviceLabels->map(fn ($label) => (int) ($quotedByService[$label] ?? 0))->all(),
            'signed' => $serviceLabels->map(fn ($label) => (int) ($signedContractByService[$label] ?? 0))->all(),
        ];

        $regionLabels = collect(array_keys($quotedByProvince))
            ->merge(array_keys($signedContractByProvince))
            ->unique()
            ->sortByDesc(fn ($label) => (int) ($quotedByProvince[$label] ?? 0) + (int) ($signedContractByProvince[$label] ?? 0))
            ->take(10)
            ->values();

        $regionInsightChart = [
            'labels' => $regionLabels->all(),
            'quoted' => $regionLabels->map(fn ($label) => (int) ($quotedByProvince[$label] ?? 0))->all(),
            'signed' => $regionLabels->map(fn ($label) => (int) ($signedContractByProvince[$label] ?? 0))->all(),
            'revenue' => $regionLabels->map(fn ($label) => (float) ($revenueByProvinceFromContracts[$label] ?? 0))->all(),
        ];

        // ── Biểu đồ tư vấn: số dự án theo loại / quý hoặc cả năm ──
        $consultingChartData = [];
        $consultingStats = collect();
        $consultingSummary = [
            'total' => 0,
            'completed' => 0,
            'processing' => 0,
            'value' => 0,
        ];
        if ($canSeeConsulting) {
            $yearsList = range(now()->year, now()->year - 4);
            if ($chartMode === 'quarter') {
                foreach ($contractTypes as $label => $model) {
                    $dateColumn = $getDateColumn();

                    $qData = [];
                    for ($q = 1; $q <= 4; $q++) {
                        $startMonth = ($q - 1) * 3 + 1;
                        $endMonth = $startMonth + 2;
                        $quarterQuery = $model::query();
                        $applyContractDateFilter($quarterQuery, null, $dateColumn);
                        $quarterQuery->whereMonth($dateColumn, '>=', $startMonth)
                            ->whereMonth($dateColumn, '<=', $endMonth);
                        $qData[] = (int) $quarterQuery->count();
                    }
                    $consultingChartData[$label] = $qData;
                }
            } else {
                foreach ($contractTypes as $label => $model) {
                    $dateColumn = $getDateColumn();

                    $yData = [];
                    foreach (array_reverse($yearsList) as $y) {
                        if ($contractDateFromParsed !== null || $contractDateToParsed !== null) {
                            $yearModeQuery = $model::query();
                            $applyContractDateFilter($yearModeQuery, null, $dateColumn);
                            $yearModeQuery->whereYear($dateColumn, $y);
                            $yData[] = (int) $yearModeQuery->count();
                        } else {
                            $yData[] = (int) $model::whereYear($dateColumn, $y)->count();
                        }
                    }
                    $consultingChartData[$label] = $yData;
                }
            }

            foreach ($contractTypes as $label => $model) {
                $dateColumn = $getDateColumn();
                $statsQuery = $model::query();
                if ($currentUser->hasRole(RoleEnum::TU_VAN->value)) {
                    $statsQuery->whereHas('assignments', fn ($query) => $query->where('user_id', $currentUser->id));
                }
                $applyContractDateFilter($statsQuery, $selectedMonth, $dateColumn);
                $rows = $statsQuery->get(['id', 'value', 'workflow_status']);

                $count = $rows->count();
                $completed = $rows->where('workflow_status', 'finished')->count();
                $value = (float) $rows->sum('value');

                $consultingStats->push([
                    'label' => $label,
                    'count' => $count,
                    'value' => $value,
                    'completed' => $completed,
                    'processing' => max(0, $count - $completed),
                ]);

                $consultingSummary['total'] += $count;
                $consultingSummary['completed'] += $completed;
                $consultingSummary['value'] += $value;
            }

            $consultingSummary['processing'] = max(0, $consultingSummary['total'] - $consultingSummary['completed']);
        }

        $technicalStats = collect();
        $technicalSummary = [
            'total' => 0,
            'completed' => 0,
            'processing' => 0,
            'value' => 0,
            'completion_rate' => 0,
        ];
        if ($canSeeTechnical) {
            $typeLabels = [
                ContractLegal::class => 'Hồ sơ môi trường',
            ];

            foreach ($typeLabels as $modelClass => $label) {
                $dateColumn = $getDateColumn();

                $assignments = ContractAssignment::where('assignable_type', $modelClass)
                    ->when($currentUser->hasRole(RoleEnum::KY_THUAT->value), fn ($query) => $query->where('user_id', $currentUser->id))
                    ->whereHas('assignable', fn ($q) => $applyContractDateFilter($q, null, $dateColumn))
                    ->with('assignable')
                    ->get();

                $count = $assignments->count();
                $value = $assignments->sum(fn ($a) => (float) ($a->assignable->value ?? 0));
                $completed = $assignments->filter(fn ($a) => ($a->assignable->workflow_status ?? '') === 'finished')->count();

                $technicalStats->push([
                    'label' => $label,
                    'count' => $count,
                    'value' => $value,
                    'completed' => $completed,
                ]);
            }

            $technicalSummary['total'] = (int) $technicalStats->sum('count');
            $technicalSummary['completed'] = (int) $technicalStats->sum('completed');
            $technicalSummary['processing'] = max(0, $technicalSummary['total'] - $technicalSummary['completed']);
            $technicalSummary['value'] = (float) $technicalStats->sum('value');
            $technicalSummary['completion_rate'] = $technicalSummary['total'] > 0
                ? round($technicalSummary['completed'] / $technicalSummary['total'] * 100)
                : 0;
        }

        $consultingRate = ($consultingSummary['total'] ?? 0) > 0
            ? round(($consultingSummary['completed'] ?? 0) / $consultingSummary['total'] * 100)
            : 0;

        $technicalRate = (int) ($technicalSummary['completion_rate'] ?? 0);

        // ── IT Admin Stats ───────────────────────────
        $isIT = $currentUser->hasRole(RoleEnum::IT->value);
        $itStats = $currentItStats;
        $envData = $currentEnvData;

        if ($isIT && empty($itStats)) {
            // Role distribution
            $roleDistribution = Role::withCount('users')->get()->map(fn ($r) => [
                'name' => $r->name,
                'label' => $r->display_name ?: $r->name,
                'count' => $r->users_count,
            ])->filter(fn ($r) => $r['count'] > 0)->values();

            // System health
            $diskPath = base_path();
            $totalSpace = disk_total_space($diskPath) ?: 1;
            $freeSpace = disk_free_space($diskPath) ?: 11;
            $usedSpace = $totalSpace - $freeSpace;
            $diskUsagePercent = round(($usedSpace / $totalSpace) * 100, 1);

            // DB size (MySQL specific)
            $dbName = config('database.connections.mysql.database');
            $dbSize = 0;
            try {
                $dbSizeResult = DB::select('
                    SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                    FROM information_schema.TABLES
                    WHERE table_schema = ?
                ', [$dbName]);
                $dbSize = $dbSizeResult[0]->size_mb ?? 0;
            } catch (\Exception $e) {
            }

            // Queues
            $pendingJobs = DB::table('jobs')->count();

            // Error logs
            $recentErrors = [];
            $logPath = storage_path('logs/laravel.log');
            if (File::exists($logPath)) {
                $lines = File::lines($logPath)->reverse()->take(100);
                $errorCount = 0;
                foreach ($lines as $line) {
                    if (stripos($line, 'error') !== false || stripos($line, 'exception') !== false) {
                        $recentErrors[] = $line;
                        $errorCount++;
                        if ($errorCount >= 10) {
                            break;
                        }
                    }
                }
            }

            $itStats = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'locked_users' => User::where('is_active', false)->count(),
                'recent_activities' => Activity::with('causer')->latest()->take(10)->get(),
                'role_distribution' => $roleDistribution,
                'top_users' => Activity::select('causer_id', 'causer_type', DB::raw('count(*) as total'))
                    ->where('causer_type', User::class)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->groupBy('causer_id', 'causer_type')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->with('causer')
                    ->get(),
                'system' => [
                    'disk_total' => round($totalSpace / (1024 ** 3), 2),
                    'disk_free' => round($freeSpace / (1024 ** 3), 2),
                    'disk_used' => round($usedSpace / (1024 ** 3), 2),
                    'disk_percent' => $diskUsagePercent,
                    'db_size_mb' => $dbSize,
                    'pending_jobs' => $pendingJobs,
                    'active_sessions' => DB::table('sessions')->count(),
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'failed_logins_24h' => Activity::where('log_name', 'auth')
                        ->where('description', 'like', '%Đăng nhập thất bại%')
                        ->where('created_at', '>=', now()->subDay())
                        ->count(),
                ],
                'recent_errors' => $recentErrors,
            ];
        }

        // ── Doanh số theo nguồn thông tin (Dynamic) ──────────
        $sourceSalesMap = [];

        foreach ($contractTypes as $modelClass) {
            $sourceField = 'info_source';
            $dateColumn = $getDateColumn();

            $modelQuery = $modelClass::query();
            $applyContractDateFilter($modelQuery, $selectedMonth, $dateColumn);

            $rows = $modelQuery
                ->selectRaw("
                    CASE
                        WHEN is_renewal = 1 THEN 'TÁI KÝ'
                        ELSE UPPER(TRIM(COALESCE(NULLIF(TRIM({$sourceField}), ''), 'KHÁC')))
                    END as label,
                    COALESCE(SUM(revenue), 0) as total_rev
                ")
                ->groupBy('label')
                ->get();

            foreach ($rows as $row) {
                $sourceSalesMap[$row->label] = ($sourceSalesMap[$row->label] ?? 0) + (float) $row->total_rev;
            }
        }

        arsort($sourceSalesMap);

        $sourceSalesChart = [
            'labels' => array_keys($sourceSalesMap),
            'datasets' => array_values($sourceSalesMap),
        ];

        // ── Dữ liệu cho Bảng Tổng quan vận hành mới ──
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        $workScheduleWeekCount = WorkSchedule::query()
            ->where($applyWorkScheduleScope)
            ->where(function ($q) use ($startOfWeek, $endOfWeek) {
                $q->whereBetween('start_date', [$startOfWeek, $endOfWeek])
                    ->orWhereBetween('end_date', [$startOfWeek, $endOfWeek]);
            })
            ->count();

        $reportingUserIds = DailyReportVisibility::visibleReportingUsersQuery($currentUser)->pluck('id');
        $reportingUsersCount = $reportingUserIds->count();

        $reportedTodayCount = $reportingUsersCount > 0
            ? DailyReport::whereDate('date', today())
                ->whereIn('user_id', $reportingUserIds)
                ->distinct('user_id')
                ->count('user_id')
            : 0;

        $unreportedTodayCount = max(0, $reportingUsersCount - $reportedTodayCount);
        $dailyReportRate = $reportingUsersCount > 0 ? round(($reportedTodayCount / $reportingUsersCount) * 100) : 0;

        $upcomingSchedules = WorkSchedule::query()
            ->where($applyWorkScheduleScope)
            ->whereDate('start_date', '>=', today())
            ->with('user:id,name')
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        $latestReports = DailyReport::query()
            ->with('user:id,name')
            ->whereIn('user_id', $reportingUserIds)
            ->latest('date')
            ->latest('created_at')
            ->take(5)
            ->get();

        $dashboardRoleDistribution = Role::withCount(['users' => fn ($q) => $q->where('is_active', true)])
            ->get()
            ->map(fn ($r) => [
                'name' => $r->display_name ?: $r->name,
                'count' => $r->users_count,
            ])
            ->filter(fn ($r) => $r['count'] > 0)
            ->values()
            ->toArray();

        $totalActiveUsersCount = User::where('is_active', true)->count();

        return compact(
            'totalCustomers', 'totalContracts', 'totalContractValue', 'totalSales',
            'totalRevenue', 'totalPaymentDue', 'totalPaymentPaid',
            'byType', 'monthly', 'canSeeTechnical', 'technicalStats', 'technicalSummary', 'technicalRate',
            'canSeeConsulting', 'consultingChartData', 'consultingStats', 'consultingSummary', 'consultingRate', 'canSeeFinance',
            'canSeeInvoiceTasks', 'canSeeSalesTasks',
            'isIT', 'itStats', 'envData', 'dailyReportReminder', 'needsAction', 'needsActionTotal',
            'workScheduleSummary', 'workScheduleRecentItems', 'workScheduleHasTime',
            'insightMonth', 'serviceInsightChart', 'regionInsightChart',
            'sourceSalesChart',
            'workScheduleWeekCount', 'reportedTodayCount', 'unreportedTodayCount', 'dailyReportRate',
            'upcomingSchedules', 'latestReports', 'dashboardRoleDistribution', 'totalActiveUsersCount'
        );
    }
}
