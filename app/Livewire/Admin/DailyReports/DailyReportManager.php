<?php

namespace App\Livewire\Admin\DailyReports;

use Livewire\Component;
use App\Models\DailyReport;
use Carbon\Carbon;

class DailyReportManager extends Component
{
    // Form fields
    public $status = 'Hoàn thành đúng kế hoạch';
    public $content;
    public $plan;
    public $issues;

    // View states
    public $activeTab = 'form'; // 'form' or 'history'
    public $isManager = false;
    public $isEditing = false;
    public $viewType = 'day'; // 'day' or 'month' (for managers)

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
    ];

    public function mount($userId = null)
    {
        $this->dateFilter = date('Y-m-d');
        $this->monthFilter = (int)date('m');
        $this->yearFilter = (int)date('Y');
        $this->isManager = auth()->user()->hasRole('giam-doc');
        if (!$this->isManager) {
            $this->userIdFilter = auth()->id();
        }
        $this->loadTodayReport();
    }

    public function loadTodayReport()
    {
        $report = DailyReport::where('user_id', auth()->id())->whereDate('date', date('Y-m-d'))->first();
        if ($report) {
            $this->content = $report->content;
            $this->status = $report->status;
            $this->plan = $report->plan;
            $this->issues = $report->issues;
            $this->isEditing = true;
        } else {
            $this->content = '';
            $this->status = 'Hoàn thành đúng kế hoạch';
            $this->plan = '';
            $this->issues = '';
            $this->isEditing = false;
        }
    }

    public function save()
    {
        $this->validate([
            'content' => 'required|min:10',
            'status' => 'required',
            'plan' => 'nullable',
        ], [
            'content.required' => 'Vui lòng nhập nội dung công việc.',
            'content.min' => 'Nội dung công việc quá ngắn.',
            'plan.required' => 'Vui lòng nhập kế hoạch ngày mai.',
        ]);

        DailyReport::updateOrCreate(
            ['user_id' => auth()->id(), 'date' => date('Y-m-d')],
            [
                'content' => $this->content,
                'status' => $this->status,
                'plan' => $this->plan,
                'issues' => $this->issues,
            ]
        );

        $this->dispatch('swal:success', ['message' => 'Gửi báo cáo thành công!']);
        $this->dispatch('editor:reset');
        $this->loadTodayReport();
    }

    public function delete($id)
    {
        $report = DailyReport::findOrFail($id);
        if (!$this->isManager && $report->user_id !== auth()->id()) {
            abort(403);
        }

        $report->delete();
        $this->dispatch('swal:success', ['message' => 'Xóa báo cáo thành công!']);
    }

    public function export()
    {
        if (!$this->isManager) abort(403);

        if ($this->viewType === 'day') {
            $filename = "Bao_cao_ngay_" . Carbon::parse($this->dateFilter)->format('d_m_Y') . ".xls";
        } else {
            $filename = "Bao_cao_thang_" . str_pad($this->monthFilter, 2, '0', STR_PAD_LEFT) . "_" . $this->yearFilter . ".xls";
        }

        $allUsers = \App\Models\User::query();
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
        $allUsers = $this->isManager ? \App\Models\User::with('department')->orderBy('name')->get() : collect();
        $departments = $this->isManager ? \App\Models\Department::orderBy('name')->get() : collect();

        if ($this->activeTab === 'history') {
            $userIds = [auth()->id()];
        } elseif ($this->isManager) {
            $scopedUsers = \App\Models\User::query();
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

                $usersToDisplay = \App\Models\User::whereIn('id', $userIds)->get();
                foreach ($usersToDisplay as $user) {
                    $report = $dailyReports->get($user->id);
                    $reports->push((object)['user' => $user, 'report' => $report]);
                }

                $this->reportStats['total'] = $usersToDisplay->count();
                $this->reportStats['issues'] = $dailyReports->where('status', 'Gặp vấn đề, cần hỗ trợ')->count();
                $this->reportStats['missing'] = $usersToDisplay->count() - $dailyReports->count();
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
