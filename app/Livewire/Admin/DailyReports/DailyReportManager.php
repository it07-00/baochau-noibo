<?php

namespace App\Livewire\Admin\DailyReports;

use App\Actions\DailyReports\SubmitDailyReportAction;
use App\Enums\DailyReportStatus;
use App\Enums\Role;
use Livewire\Component;
use App\Models\DailyReport;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class DailyReportManager extends Component
{
    // Form fields
    public $reportDate;
    public $status = DailyReportStatus::HOAN_THANH_DUNG_KH->value;
    public $content;
    public $plan;
    public $issues;

    // View states
    public $activeTab = 'form'; // 'form' | 'history' | 'management'
    public $isManager = false;
    public $isDirector = false;
    public $canSubmitOwnReport = true;
    public $isEditing = false;
    public $viewType = 'day'; // 'day' or 'month' (for managers)
    public $showReportModal = false;

    // Filters
    public $dateFilter;
    public $monthFilter;
    public $yearFilter;
    public $deptIdFilter;
    public $userIdFilter;

    public $reportStats = [
        'total' => 0,
        'missing' => 0,
        'issues' => 0,
        'late' => 0,
    ];

    protected $queryString = [
        'dateFilter' => ['except' => ''],
        'viewType'   => ['except' => 'day'],
    ];

    public function mount($userId = null)
    {
        $this->reportDate = date('Y-m-d');
        $this->dateFilter = date('Y-m-d');
        $this->monthFilter = (int)date('m');
        $this->yearFilter = (int)date('Y');

        $this->isDirector = auth()->user()->hasRole(Role::GIAM_DOC->value);
        $this->canSubmitOwnReport = !$this->isDirector;
        $this->isManager = $this->canManageReports();

        if ($this->isDirector && $this->isManager) {
            $this->activeTab = 'management';
        }

        if (!$this->isManager) {
            $this->userIdFilter = auth()->id();
        }

        if ($this->canSubmitOwnReport) {
            $this->loadReportByDate();
        }
    }

    private function canManageReports(): bool
    {
        return auth()->user()->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value]);
    }

    private function scopedUsersQuery()
    {
        $query = User::where('is_active', true);

        $isTopLevel = auth()->user()->hasAnyRole([Role::GIAM_DOC->value]);
        if (auth()->user()->hasRole(Role::TP_KINH_DOANH->value) && !$isTopLevel) {
            $query->role([Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value]);
        }

        return $query;
    }

    public function updatedReportDate()
    {
        if (!$this->canSubmitOwnReport) {
            return;
        }

        $this->loadReportByDate();
    }

    public function updatedActiveTab($value)
    {
        if ($this->isDirector && $value !== 'management') {
            $this->activeTab = 'management';
        }
    }

    public function openReportModal($date)
    {
        if (!$this->canSubmitOwnReport) {
            return;
        }

        $parsed = Carbon::parse($date);
        if ($parsed->gt(today())) {
            $this->dispatch('swal:error', ['message' => 'Không thể gửi báo cáo cho ngày trong tương lai.']);
            return;
        }

        $this->reportDate = $date;
        $this->loadReportByDate();
        $this->showReportModal = true;
    }

    public function closeReportModal()
    {
        $this->showReportModal = false;
        $this->resetErrorBag();
    }

    public function loadReportByDate()
    {
        if (!$this->canSubmitOwnReport) {
            return;
        }

        $report = DailyReport::where('user_id', auth()->id())
            ->whereDate('date', $this->reportDate)
            ->first();

        if ($report) {
            $this->content = $report->content;
            $this->status = $report->status;
            $this->plan = $report->plan;
            $this->issues = $report->issues;
            $this->isEditing = true;
        } else {
            $this->content = '';
            $this->status = DailyReportStatus::HOAN_THANH_DUNG_KH->value;
            $this->plan = '';
            $this->issues = '';
            $this->isEditing = false;
        }

        $this->dispatch('editor:set-content', content: (string) ($this->content ?? ''));
    }

    public function save()
    {
        abort_unless($this->canSubmitOwnReport, 403);

        $this->validate([
            'reportDate' => 'required|date|before_or_equal:today',
            'content' => 'required|min:10|max:10000',
            'status' => 'required|in:' . implode(',', DailyReportStatus::values()),
            'plan' => 'nullable|string|max:5000',
            'issues' => 'nullable|string|max:5000',
        ], [
            'reportDate.required' => 'Vui lòng chọn ngày báo cáo.',
            'reportDate.date' => 'Ngày báo cáo không hợp lệ.',
            'reportDate.before_or_equal' => 'Chỉ được cập nhật báo cáo ở thời điểm hiện tại hoặc trước đó.',
            'content.required' => 'Vui lòng nhập nội dung công việc.',
            'content.min' => 'Nội dung công việc quá ngắn.',
            'content.max' => 'Nội dung công việc không được vượt quá 10,000 ký tự.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);

        app(SubmitDailyReportAction::class)->execute(
            reporter: auth()->user(),
            date:     $this->reportDate,
            content:  $this->content,
            status:   $this->status,
            plan:     trim((string) ($this->plan ?? '')),
            issues:   trim((string) ($this->issues ?? '')),
        );

        $this->dispatch('swal:success', ['message' => 'Gửi báo cáo thành công!']);
        $this->showReportModal = false;
        $this->loadReportByDate();
    }

    public function deleteReport($id)
    {
        $this->delete($id);
    }

    public function delete($id)
    {
        $report = DailyReport::findOrFail($id);
        if (!$this->isManager && $report->user_id !== auth()->id()) {
            abort(403);
        }

        if ($this->isManager && auth()->user()->hasRole(Role::TP_KINH_DOANH->value) && !auth()->user()->hasRole(Role::GIAM_DOC->value)) {
            $canManageThisUser = $this->scopedUsersQuery()->whereKey($report->user_id)->exists();
            abort_unless($canManageThisUser, 403);
        }

        $report->delete();
        $this->dispatch('swal:success', ['message' => 'Xóa báo cáo thành công!']);
    }

    public function shouldRenderCalendar(): bool
    {
        return ($this->canSubmitOwnReport && $this->activeTab === 'history')
            || ($this->activeTab === 'management' && $this->isManager && $this->viewType === 'month');
    }

    public function monthStart(): Carbon
    {
        return Carbon::create((int) $this->yearFilter, (int) $this->monthFilter, 1);
    }

    public function weekdayShortNames(): array
    {
        return ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
    }

    public function mobileReportDays(array $calendarData): Collection
    {
        $monthStart = $this->monthStart();

        return collect(range(1, $monthStart->daysInMonth))
            ->map(function ($day) use ($monthStart, $calendarData) {
                $date = $monthStart->copy()->day($day);
                $reportsForDay = collect($calendarData[$day] ?? []);

                return [
                    'date' => $date,
                    'reports' => $reportsForDay,
                ];
            })
            ->filter(fn ($day) => $day['reports']->isNotEmpty())
            ->values();
    }

    public function calendarPeriod(): CarbonPeriod
    {
        $monthStart = $this->monthStart();

        return CarbonPeriod::create(
            $monthStart->copy()->startOfWeek(Carbon::MONDAY),
            $monthStart->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY)
        );
    }

    public function dayReportsForDate(array $calendarData, Carbon $currentDate): Collection
    {
        return collect($calendarData[(int) $currentDate->day] ?? []);
    }

    public function dayIssueCount(Collection $reports): int
    {
        return $reports->where('status', DailyReportStatus::GAP_VAN_DE->value)->count();
    }

    public function reportLateDays(DailyReport $report): int
    {
        if (!$report->created_at) {
            return 0;
        }

        return max(0, (int) $report->date->copy()->startOfDay()->diffInDays($report->created_at->copy()->startOfDay(), false));
    }

    public function dayLateCount(Collection $reports): int
    {
        return $reports->filter(fn (DailyReport $report) => $this->reportLateDays($report) > 0)->count();
    }

    public function dayNamesPreview(Collection $reports, int $limit = 3): string
    {
        $names = $reports->pluck('user.name')->filter()->take($limit)->join(', ');
        return $names . ($reports->count() > $limit ? '...' : '');
    }

    public function reportPayload(Collection $reports): string
    {
        return $reports->map(function (DailyReport $report) {
            return [
                'id' => $report->id,
                'user_id' => $report->user_id,
                'date' => $report->date->format('Y-m-d'),
                'name' => $report->user->name ?? '',
                'department' => $report->user->department->name ?? '',
                'status' => $report->status,
                'content' => $report->content,
                'plan' => $report->plan ?? '',
                'issues' => $report->issues ?? '',
                'submitted_at' => $report->created_at?->format('d/m/Y H:i'),
                'late_days' => $this->reportLateDays($report),
            ];
        })->toJson();
    }

    public function statusLabelClass(?string $status): string
    {
        if ($status === DailyReportStatus::GAP_VAN_DE->value) {
            return 'text-danger';
        }

        if ($status === DailyReportStatus::HOAN_THANH_MOT_PHAN->value) {
            return 'text-warning';
        }

        return 'text-success';
    }

    public function daysDiffFromDateFilter(): int
    {
        return (int) Carbon::parse($this->dateFilter)->startOfDay()->diffInDays(now()->startOfDay(), false);
    }

    public function lateMissingMeta(int $daysDiff): array
    {
        if ($daysDiff >= 4) {
            return [
                'itemClass' => 'border-danger-subtle',
                'itemStyle' => 'background: rgba(220,53,69,0.05);',
                'avatarClass' => 'bg-danger bg-opacity-10 text-danger fw-bold',
                'nameClass' => 'text-danger fw-semibold',
                'badgeClass' => 'text-danger fw-bold',
                'badgeText' => "Chậm {$daysDiff} ngày",
            ];
        }

        if ($daysDiff >= 1) {
            return [
                'itemClass' => 'border-warning-subtle',
                'itemStyle' => 'background: rgba(255,193,7,0.07);',
                'avatarClass' => 'bg-warning bg-opacity-10 text-warning fw-bold',
                'nameClass' => 'text-warning-emphasis fw-semibold',
                'badgeClass' => 'text-warning fw-bold',
                'badgeText' => "Chậm {$daysDiff} ngày",
            ];
        }

        return [
            'itemClass' => 'border-light-subtle border-dashed',
            'itemStyle' => '',
            'avatarClass' => 'bg-light text-muted fw-bold',
            'nameClass' => 'text-muted opacity-75 fw-normal',
            'badgeClass' => 'text-warning fw-bold',
            'badgeText' => 'Chưa báo cáo',
        ];
    }

    public function dayDotColor(Carbon $currentDate, Collection $dayReports): string
    {
        $isInsideMonth = $currentDate->month == (int) $this->monthFilter;
        $isPast = $currentDate->isPast() && !$currentDate->isToday();
        $isWeekend = $currentDate->isSunday();
        $isComplete = $dayReports->isNotEmpty();
        $hasIssue = $dayReports->where('status', DailyReportStatus::GAP_VAN_DE->value)->isNotEmpty();

        if (!$isInsideMonth) {
            return 'secondary';
        }

        if ($isComplete) {
            return $hasIssue ? 'danger' : 'success';
        }

        if ($isWeekend) {
            return 'secondary opacity-25';
        }

        if ($isPast) {
            return 'danger';
        }

        return 'warning';
    }

    public function export()
    {
        if (!$this->isManager) abort(403);

        if ($this->viewType === 'day') {
            $filename = "Bao_cao_ngay_" . Carbon::parse($this->dateFilter)->format('d_m_Y') . ".xls";
        } else {
            $filename = "Bao_cao_thang_" . str_pad($this->monthFilter, 2, '0', STR_PAD_LEFT) . "_" . $this->yearFilter . ".xls";
        }

        $allUsers = $this->scopedUsersQuery();
        if ($this->deptIdFilter) $allUsers->where('department_id', $this->deptIdFilter);
        if ($this->userIdFilter) $allUsers->where('id', $this->userIdFilter);
        $userIds = $allUsers->pluck('id');

        $query = DailyReport::with('user', 'user.department')->whereIn('user_id', $userIds);

        if ($this->viewType === 'day') {
            $query->whereDate('date', $this->dateFilter);
        } else {
            $query->whereYear('date', $this->yearFilter)->whereMonth('date', $this->monthFilter);
        }

        $reports = $query->orderBy('date', 'desc')->get();

        return response()->streamDownload(function() use ($reports) {
            echo view('admin.daily-reports.export-excel', [
                'reports' => $reports,
                'viewType' => $this->viewType,
                'dateFilter' => $this->dateFilter,
                'monthFilter' => $this->monthFilter,
                'yearFilter' => $this->yearFilter,
            ])->render();
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    public function render()
    {
        $allUsers = collect();
        $departments = collect();

        if ($this->isManager) {
            $allUsers = $this->scopedUsersQuery()->with('department')->orderBy('name')->get();

            $departmentIds = $this->scopedUsersQuery()
                ->whereNotNull('department_id')
                ->distinct()
                ->pluck('department_id');

            $departments = Department::whereIn('id', $departmentIds)->orderBy('name')->get();
        }

        if ($this->activeTab === 'history') {
            $userIds = [auth()->id()];
        } elseif ($this->isManager) {
            $scopedUsers = $this->scopedUsersQuery();
            if ($this->deptIdFilter) $scopedUsers->where('department_id', $this->deptIdFilter);
            if ($this->userIdFilter) $scopedUsers->where('id', $this->userIdFilter);
            $userIds = $scopedUsers->pluck('id');
        } else {
            $userIds = [auth()->id()];
        }

        $reports = collect();
        $calendarData = [];

        if ($this->activeTab === 'history') {
            $monthReports = DailyReport::with('user')->whereIn('user_id', $userIds)
                ->whereYear('date', $this->yearFilter)
                ->whereMonth('date', $this->monthFilter)
                ->get();

            foreach ($monthReports as $report) {
                $dayNum = (int)$report->date->format('j');
                $calendarData[$dayNum][] = $report;
            }
            $this->reportStats['total'] = $monthReports->count();
        } elseif ($this->isManager) {
            if ($this->viewType === 'day') {
                $dailyReports = DailyReport::whereIn('user_id', $userIds)
                    ->whereDate('date', $this->dateFilter)
                    ->get()
                    ->keyBy('user_id');

                $usersToDisplay = $this->scopedUsersQuery()->whereIn('id', $userIds)->where('is_active', true)->get();
                foreach ($usersToDisplay as $user) {
                    $report = $dailyReports->get($user->id);
                    $reports->push((object)['user' => $user, 'report' => $report]);
                }

                $this->reportStats['total'] = $usersToDisplay->count();
                $this->reportStats['issues'] = $dailyReports->where('status', 'Gặp vấn đề, cần hỗ trợ')->count();
                $this->reportStats['missing'] = $usersToDisplay->whereNull(fn($u) => $dailyReports->get($u->id))->count();
                $this->reportStats['late'] = $dailyReports->filter(function ($report) {
                    return $report->created_at
                        && $report->created_at->copy()->startOfDay()->gt($report->date->copy()->startOfDay());
                })->count();
            } else {
                $monthReports = DailyReport::with('user')->whereIn('user_id', $userIds)
                    ->whereYear('date', $this->yearFilter)
                    ->whereMonth('date', $this->monthFilter)
                    ->get();

                foreach ($monthReports as $report) {
                    $dayNum = (int)$report->date->format('j');
                    $calendarData[$dayNum][] = $report;
                }
                $this->reportStats['total'] = $monthReports->count();
            }
        }

        return view('livewire.admin.daily-reports.daily-report-manager', [
            'users' => $allUsers,
            'departments' => $departments,
            'reports' => $reports,
            'calendarData' => $calendarData,
        ])->layout('admin.layouts.app', [
            'fullWidth' => $this->isManager || $this->activeTab === 'history'
        ]);
    }
}
