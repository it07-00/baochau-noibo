<?php

namespace App\Livewire\Admin\DailyReports;

use App\Models\DailyReport;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class DailyReportManager extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFilter;
    public $userIdFilter;
    public $perPage = 10;

    // For Create/Edit
    public $reportId;
    public $date;
    public $content;
    public $plan;
    public $issues;

    protected $listeners = ['deleteConfirmed' => 'delete'];

    public function mount()
    {
        $this->dateFilter = date('Y-m-d');
        $this->date = date('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->reportId = null;
        $this->date = date('Y-m-d');
        $this->content = '';
        $this->plan = '';
        $this->issues = '';
    }

    public function openCreateModal()
    {
        $this->resetFields();
        $this->dispatch('openModal');
    }

    public function save()
    {
        $this->validate([
            'date' => 'required|date',
            'content' => 'required|string',
            'plan' => 'required|string',
            'issues' => 'nullable|string',
        ]);

        if ($this->reportId) {
            $report = DailyReport::findOrFail($this->reportId);
            
            // Check authorization
            if (!auth()->user()->can('daily-reports.view-all') && $report->user_id !== auth()->id()) {
                abort(403);
            }

            $report->update([
                'date' => $this->date,
                'content' => $this->content,
                'plan' => $this->plan,
                'issues' => $this->issues,
            ]);
            $this->dispatch('swal:success', ['message' => 'Cập nhật báo cáo thành công!']);
        } else {
            DailyReport::create([
                'user_id' => auth()->id(),
                'date' => $this->date,
                'content' => $this->content,
                'plan' => $this->plan,
                'issues' => $this->issues,
            ]);
            $this->dispatch('swal:success', ['message' => 'Gửi báo cáo thành công!']);
        }

        $this->resetFields();
        $this->dispatch('closeModal');
    }

    public function edit($id)
    {
        $report = DailyReport::findOrFail($id);
        
        // Check authorization
        if (!auth()->user()->can('daily-reports.view-all') && $report->user_id !== auth()->id()) {
            abort(403);
        }

        $this->reportId = $report->id;
        $this->date = $report->date->format('Y-m-d');
        $this->content = $report->content;
        $this->plan = $report->plan;
        $this->issues = $report->issues;
        
        $this->dispatch('openModal');
    }

    public function delete($id)
    {
        $report = DailyReport::findOrFail($id);
        
        // Check authorization
        if (!auth()->user()->can('daily-reports.view-all') && $report->user_id !== auth()->id()) {
            abort(403);
        }

        $report->delete();
        $this->dispatch('swal:success', ['message' => 'Xóa báo cáo thành công!']);
    }

    public function render()
    {
        $query = DailyReport::with('user');

        // Authorization: standard employee only sees their own
        if (!auth()->user()->can('daily-reports.view-all')) {
            $query->where('user_id', auth()->id());
        } elseif ($this->userIdFilter) {
            $query->where('user_id', $this->userIdFilter);
        }

        if ($this->dateFilter) {
            $query->whereDate('date', $this->dateFilter);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('content', 'like', '%' . $this->search . '%')
                  ->orWhere('plan', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($qu) {
                      $qu->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $reports = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate($this->perPage);

        // For director filter
        $users = [];
        if (auth()->user()->can('daily-reports.view-all')) {
            $users = \App\Models\User::orderBy('name')->get();
        }

        return view('livewire.admin.daily-reports.daily-report-manager', [
            'reports' => $reports,
            'users' => $users
        ])->layout('admin.layouts.app', ['title' => 'Báo cáo ngày']);
    }
}
