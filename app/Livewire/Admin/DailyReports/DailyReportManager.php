<?php

namespace App\Livewire\Admin\DailyReports;

use App\Actions\DailyReports\SubmitDailyReportAction;
use App\Enums\DailyReportStatus;
use App\Enums\DailyReportSupportStatus;
use App\Enums\Permission;
use App\Enums\Role;
use App\Models\DailyReport;
use App\Models\Department;
use App\Notifications\DailyReportSupportUpdatedNotification;
use App\Support\DailyReportVisibility;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

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

    public $periodFilter;

    public $deptIdFilter;

    public $userIdFilter;

    public $supportStatusFilter = 'open';

    public $supportSearch = '';

    public $selectedSupportReportId;

    public $supportResolution = '';

    public $showSupportModal = false;

    public $supportStats = [
        'pending' => 0,
        'in_progress' => 0,
        'resolved' => 0,
    ];

    public $reportStats = [
        'total' => 0,
        'missing' => 0,
        'issues' => 0,
        'late' => 0,
    ];

    protected $queryString = [
        'dateFilter' => ['except' => ''],
        'viewType' => ['except' => 'day'],
    ];

    public function mount($userId = null)
    {
        $this->reportDate = date('Y-m-d');
        $this->dateFilter = date('Y-m-d');
        $this->monthFilter = (int) now()->format('m');
        $this->yearFilter = (int) now()->format('Y');
        $this->periodFilter = now()->format('Y-m');

        $this->isDirector = auth()->user()->hasRole(Role::GIAM_DOC->value);
        $this->canSubmitOwnReport = ! $this->isDirector;
        $this->isManager = $this->canManageReports();

        if ($this->isDirector && $this->isManager) {
            $this->activeTab = 'management';
        }

        if (! $this->isManager) {
            $this->userIdFilter = auth()->id();
        }

        if ($this->canSubmitOwnReport) {
            $this->loadReportByDate();
        }
    }

    private function canManageReports(): bool
    {
        return DailyReportVisibility::canManage(auth()->user());
    }

    private function scopedUsersQuery()
    {
        return DailyReportVisibility::visibleUsersQuery(auth()->user());
    }

    public function updatedReportDate()
    {
        if (! $this->canSubmitOwnReport) {
            return;
        }

        $this->loadReportByDate();
    }

    public function updatedActiveTab($value)
    {
        if ($this->isDirector && ! in_array($value, ['management', 'support'], true)) {
            $this->activeTab = 'management';
        }
    }

    public function startSupport(int $reportId): void
    {
        $report = $this->findManageableSupportReport($reportId);

        if ($report->support_status === DailyReportSupportStatus::RESOLVED->value) {
            $this->dispatch('swal:error', ['message' => 'Yêu cầu này đã được xử lý.']);

            return;
        }

        $report->update([
            'support_status' => DailyReportSupportStatus::IN_PROGRESS->value,
            'support_handler_id' => auth()->id(),
            'support_started_at' => $report->support_started_at ?? now(),
            'support_resolved_at' => null,
        ]);

        $this->notifySupportOwner($report);
        $this->dispatch('swal:success', ['message' => 'Đã tiếp nhận yêu cầu hỗ trợ.']);
    }

    public function openSupportModal(int $reportId): void
    {
        $report = $this->findManageableSupportReport($reportId);
        $this->selectedSupportReportId = $report->id;
        $this->supportResolution = (string) ($report->support_response ?? '');
        $this->showSupportModal = true;
        $this->resetErrorBag('supportResolution');
    }

    public function closeSupportModal(): void
    {
        $this->showSupportModal = false;
        $this->selectedSupportReportId = null;
        $this->supportResolution = '';
        $this->resetErrorBag('supportResolution');
    }

    public function resolveSupport(): void
    {
        $this->validate([
            'selectedSupportReportId' => 'required|integer',
            'supportResolution' => 'required|string|min:5|max:5000',
        ], [
            'supportResolution.required' => 'Vui lòng nhập nội dung đã hỗ trợ.',
            'supportResolution.min' => 'Nội dung xử lý cần có ít nhất 5 ký tự.',
            'supportResolution.max' => 'Nội dung xử lý không được vượt quá 5.000 ký tự.',
        ]);

        $report = $this->findManageableSupportReport((int) $this->selectedSupportReportId);
        $report->update([
            'support_status' => DailyReportSupportStatus::RESOLVED->value,
            'support_handler_id' => auth()->id(),
            'support_response' => trim($this->supportResolution),
            'support_started_at' => $report->support_started_at ?? now(),
            'support_resolved_at' => now(),
        ]);

        $this->notifySupportOwner($report);
        $this->closeSupportModal();
        $this->dispatch('swal:success', ['message' => 'Đã hoàn tất yêu cầu hỗ trợ.']);
    }

    public function reopenSupport(int $reportId): void
    {
        $report = $this->findManageableSupportReport($reportId);
        $report->update([
            'support_status' => DailyReportSupportStatus::PENDING->value,
            'support_handler_id' => null,
            'support_started_at' => null,
            'support_resolved_at' => null,
        ]);

        $this->notifySupportOwner($report);
        $this->dispatch('swal:success', ['message' => 'Đã mở lại yêu cầu hỗ trợ.']);
    }

    private function findManageableSupportReport(int $reportId): DailyReport
    {
        abort_unless($this->isManager && $this->canManageReports(), 403);

        $visibleUserIds = $this->scopedUsersQuery()->select('users.id');

        $report = DailyReport::query()
            ->whereKey($reportId)
            ->whereNotNull('support_status')
            ->whereIn('user_id', $visibleUserIds)
            ->first();

        abort_if(! $report, 404);

        return $report;
    }

    private function notifySupportOwner(DailyReport $report): void
    {
        $report->refresh()->load('user');

        if ($report->user && $report->user_id !== auth()->id()) {
            $report->user->notify(new DailyReportSupportUpdatedNotification($report, auth()->user()));
        }
    }

    public function updatedPeriodFilter($value): void
    {
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value)) {
            return;
        }

        [$year, $month] = array_map('intval', explode('-', $value));
        $this->yearFilter = $year;
        $this->monthFilter = $month;
    }

    public function updatedMonthFilter(): void
    {
        $this->syncPeriodFilter();
    }

    public function updatedYearFilter(): void
    {
        $this->syncPeriodFilter();
    }

    private function syncPeriodFilter(): void
    {
        $this->periodFilter = sprintf('%04d-%02d', (int) $this->yearFilter, (int) $this->monthFilter);
    }

    public function openReportModal($date)
    {
        if (! $this->canSubmitOwnReport) {
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
        if (! $this->canSubmitOwnReport) {
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
            'status' => ['required', Rule::in(DailyReportStatus::values())],
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
            date: $this->reportDate,
            content: $this->content,
            status: $this->status,
            plan: trim((string) ($this->plan ?? '')),
            issues: trim((string) ($this->issues ?? '')),
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

        if ($report->user_id !== auth()->id()) {
            abort_unless($this->isManager && auth()->user()->can(Permission::DAILY_REPORTS_DELETE->value), 403);
        }

        if ($this->isManager && auth()->user()->hasRole(Role::TP_KINH_DOANH->value) && ! auth()->user()->hasRole(Role::GIAM_DOC->value)) {
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
                $reportsForDay = collect($calendarData[$date->format('Y-m-d')] ?? []);

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
        return collect($calendarData[$currentDate->format('Y-m-d')] ?? []);
    }

    public function dayIssueCount(Collection $reports): int
    {
        return $reports->where('status', DailyReportStatus::GAP_VAN_DE->value)->count();
    }

    public function reportLateDays(DailyReport $report): int
    {
        if (! $report->created_at) {
            return 0;
        }

        $diff = (int) $report->date->copy()->startOfDay()->diffInDays($report->created_at->copy()->startOfDay(), false);

        return $diff >= 3 ? max(0, $diff) : 0;
    }

    public function dayLateCount(Collection $reports): int
    {
        return $reports->filter(fn (DailyReport $report) => $this->reportLateDays($report) > 0)->count();
    }

    public function dayNamesPreview(Collection $reports, int $limit = 3): string
    {
        $names = $reports->pluck('user.name')->filter()->take($limit)->join(', ');

        return $names.($reports->count() > $limit ? '...' : '');
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

        if ($daysDiff >= 3) {
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
        $isPast = $currentDate->isPast() && ! $currentDate->isToday();
        $isWeekend = $currentDate->isSunday();
        $isComplete = $dayReports->isNotEmpty();
        $hasIssue = $dayReports->where('status', DailyReportStatus::GAP_VAN_DE->value)->isNotEmpty();

        if (! $isInsideMonth) {
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
        if (! $this->isManager) {
            abort(403);
        }

        if ($this->viewType === 'day') {
            $filename = 'Bao_cao_ngay_'.Carbon::parse($this->dateFilter)->format('d_m_Y').'.xls';
        } else {
            $filename = 'Bao_cao_thang_'.str_pad($this->monthFilter, 2, '0', STR_PAD_LEFT).'_'.$this->yearFilter.'.xls';
        }

        $allUsers = $this->scopedUsersQuery();
        if ($this->deptIdFilter) {
            $allUsers->where('department_id', $this->deptIdFilter);
        }
        if ($this->userIdFilter) {
            $allUsers->where('id', $this->userIdFilter);
        }
        $userIds = $allUsers->pluck('id');

        $query = DailyReport::with('user', 'user.department')->whereIn('user_id', $userIds);

        if ($this->viewType === 'day') {
            $query->whereDate('date', $this->dateFilter);
        } else {
            $query->whereYear('date', $this->yearFilter)->whereMonth('date', $this->monthFilter);
        }

        $reports = $query->orderBy('date', 'desc')->get();

        return response()->streamDownload(function () use ($reports) {
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

            $allSupportCounts = DailyReport::query()
                ->whereIn('user_id', $this->scopedUsersQuery()->select('users.id'))
                ->whereNotNull('support_status')
                ->selectRaw('support_status, COUNT(*) as aggregate')
                ->groupBy('support_status')
                ->pluck('aggregate', 'support_status');

            $this->supportStats = [
                'pending' => (int) ($allSupportCounts[DailyReportSupportStatus::PENDING->value] ?? 0),
                'in_progress' => (int) ($allSupportCounts[DailyReportSupportStatus::IN_PROGRESS->value] ?? 0),
                'resolved' => (int) ($allSupportCounts[DailyReportSupportStatus::RESOLVED->value] ?? 0),
            ];
        }

        if ($this->activeTab === 'history') {
            $userIds = [auth()->id()];
        } elseif ($this->isManager) {
            $scopedUsers = $this->scopedUsersQuery();
            if ($this->deptIdFilter) {
                $scopedUsers->where('department_id', $this->deptIdFilter);
            }
            if ($this->userIdFilter) {
                $scopedUsers->where('id', $this->userIdFilter);
            }
            $userIds = $scopedUsers->pluck('id');
        } else {
            $userIds = [auth()->id()];
        }

        $reports = collect();
        $supportReports = collect();
        $calendarData = [];

        if ($this->activeTab === 'support' && $this->isManager) {
            $visibleUserIds = $this->scopedUsersQuery();
            if ($this->deptIdFilter) {
                $visibleUserIds->where('department_id', $this->deptIdFilter);
            }
            if ($this->userIdFilter) {
                $visibleUserIds->whereKey($this->userIdFilter);
            }

            $baseSupportQuery = DailyReport::query()
                ->whereIn('user_id', $visibleUserIds->select('users.id'))
                ->whereNotNull('support_status');

            $statusCounts = (clone $baseSupportQuery)
                ->selectRaw('support_status, COUNT(*) as aggregate')
                ->groupBy('support_status')
                ->pluck('aggregate', 'support_status');

            $this->supportStats = [
                'pending' => (int) ($statusCounts[DailyReportSupportStatus::PENDING->value] ?? 0),
                'in_progress' => (int) ($statusCounts[DailyReportSupportStatus::IN_PROGRESS->value] ?? 0),
                'resolved' => (int) ($statusCounts[DailyReportSupportStatus::RESOLVED->value] ?? 0),
            ];

            $supportQuery = (clone $baseSupportQuery)->with(['user.department', 'supportHandler']);

            if ($this->supportStatusFilter === 'open') {
                $supportQuery->whereIn('support_status', [
                    DailyReportSupportStatus::PENDING->value,
                    DailyReportSupportStatus::IN_PROGRESS->value,
                ]);
            } elseif (in_array($this->supportStatusFilter, DailyReportSupportStatus::values(), true)) {
                $supportQuery->where('support_status', $this->supportStatusFilter);
            }

            $search = trim($this->supportSearch);
            if ($search !== '') {
                $supportQuery->where(function ($query) use ($search) {
                    $query->where('issues', 'like', "%{$search}%")
                        ->orWhere('support_response', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            }

            $supportReports = $supportQuery
                ->orderByRaw("CASE support_status WHEN 'pending' THEN 0 WHEN 'in_progress' THEN 1 ELSE 2 END")
                ->orderByDesc('date')
                ->get();
        } elseif ($this->activeTab === 'history') {
            $monthReports = DailyReport::with('user')->whereIn('user_id', $userIds)
                ->whereYear('date', $this->yearFilter)
                ->whereMonth('date', $this->monthFilter)
                ->get();

            foreach ($monthReports as $report) {
                $dateStr = $report->date->format('Y-m-d');
                $calendarData[$dateStr][] = $report;
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
                    $reports->push((object) ['user' => $user, 'report' => $report]);
                }

                $this->reportStats['total'] = $usersToDisplay->count();
                $this->reportStats['issues'] = $dailyReports->where('status', 'Gặp vấn đề, cần hỗ trợ')->count();
                $this->reportStats['missing'] = $usersToDisplay
                    ->filter(fn ($user) => $dailyReports->get($user->id) === null)
                    ->count();
                $this->reportStats['late'] = $dailyReports->filter(function ($report) {
                    return $this->reportLateDays($report) > 0;
                })->count();
            } else {
                $monthReports = DailyReport::with('user')->whereIn('user_id', $userIds)
                    ->whereYear('date', $this->yearFilter)
                    ->whereMonth('date', $this->monthFilter)
                    ->get();

                foreach ($monthReports as $report) {
                    $dateStr = $report->date->format('Y-m-d');
                    $calendarData[$dateStr][] = $report;
                }
                $this->reportStats['total'] = $monthReports->count();
            }
        }

        return view('livewire.admin.daily-reports.daily-report-manager', [
            'users' => $allUsers,
            'departments' => $departments,
            'reports' => $reports,
            'supportReports' => $supportReports,
            'calendarData' => $calendarData,
        ])->layout('admin.layouts.app', [
            'fullWidth' => $this->isManager || $this->activeTab === 'history',
        ]);
    }
}
