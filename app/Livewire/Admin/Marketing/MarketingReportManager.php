<?php

namespace App\Livewire\Admin\Marketing;

use App\Models\MarketingDailyReport;
use Livewire\Component;
use Livewire\WithPagination;

class MarketingReportManager extends Component
{
    use WithPagination;

    // Form fields
    public $report_date;
    public $facebook_count  = 0;
    public $zalo_count      = 0;
    public $website_count   = 0;
    public $tiktok_count    = 0;
    public $youtube_count   = 0;
    public $other_count     = 0;
    public $other_channel_name;
    public $content_details;
    public $banners;
    public $targets_achieved;
    public $notes;

    public $isEditing   = false;
    public $isViewOnly  = false;
    public $activeTab   = 'form'; // 'form' | 'history'

    // Manager filters
    public $filterUser   = '';
    public $filterMonth  = '';
    public $isManager    = false;

    public function mount()
    {
        $this->report_date = now()->format('Y-m-d');
        $this->filterMonth = now()->format('Y-m');
        $this->isManager   = auth()->user()->hasAnyRole(['it', 'giam-doc', 'quan-ly']);
        $this->isViewOnly  = auth()->user()->hasRole('tp-kinh-doanh');
        if ($this->isViewOnly) {
            $this->activeTab = 'history';
        }
        $this->loadTodayReport();
    }

    public function loadTodayReport()
    {
        $report = MarketingDailyReport::where('user_id', auth()->id())
            ->whereDate('report_date', now()->toDateString())
            ->first();

        if ($report) {
            $this->fill([
                'report_date'       => $report->report_date->format('Y-m-d'),
                'facebook_count'    => $report->facebook_count,
                'zalo_count'        => $report->zalo_count,
                'website_count'     => $report->website_count,
                'tiktok_count'      => $report->tiktok_count,
                'youtube_count'     => $report->youtube_count,
                'other_count'       => $report->other_count,
                'other_channel_name'=> $report->other_channel_name,
                'content_details'   => $report->content_details,
                'banners'           => $report->banners,
                'targets_achieved'  => $report->targets_achieved,
                'notes'             => $report->notes,
            ]);
            $this->isEditing = true;
        }
    }

    public function save()
    {
        $this->validate([
            'report_date'        => 'required|date',
            'facebook_count'     => 'nullable|integer|min:0|max:1000000',
            'zalo_count'         => 'nullable|integer|min:0|max:1000000',
            'website_count'      => 'nullable|integer|min:0|max:1000000',
            'tiktok_count'       => 'nullable|integer|min:0|max:1000000',
            'youtube_count'      => 'nullable|integer|min:0|max:1000000',
            'other_count'        => 'nullable|integer|min:0|max:1000000',
            'other_channel_name' => 'nullable|string|max:255',
            'content_details'    => 'required|min:5|max:5000',
            'banners'            => 'nullable|string|max:2000',
            'targets_achieved'   => 'nullable|string|max:2000',
            'notes'              => 'nullable|string|max:2000',
        ], [
            'content_details.required' => 'Vui lòng nhập mô tả công việc đã làm.',
            'content_details.min'      => 'Nội dung quá ngắn.',
            'content_details.max'      => 'Nội dung không được vượt quá 5,000 ký tự.',
        ]);

        MarketingDailyReport::updateOrCreate(
            ['user_id' => auth()->id(), 'report_date' => $this->report_date],
            [
                'facebook_count'     => (int) $this->facebook_count,
                'zalo_count'         => (int) $this->zalo_count,
                'website_count'      => (int) $this->website_count,
                'tiktok_count'       => (int) $this->tiktok_count,
                'youtube_count'      => (int) $this->youtube_count,
                'other_count'        => (int) $this->other_count,
                'other_channel_name' => $this->other_channel_name,
                'content_details'    => $this->content_details,
                'banners'            => $this->banners,
                'targets_achieved'   => $this->targets_achieved,
                'notes'              => $this->notes,
            ]
        );

        $this->isEditing = true;
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Lưu báo cáo thành công!']);
    }

    public function paginationView()
    {
        return 'livewire.admin.users.pagination';
    }

    public function render()
    {
        $history = MarketingDailyReport::with('user')
            ->when(!$this->isManager && !$this->isViewOnly, fn($q) => $q->where('user_id', auth()->id()))
            ->when($this->filterUser,  fn($q) => $q->where('user_id', $this->filterUser))
            ->when($this->filterMonth, function ($q) {
                $q->whereYear('report_date',  substr($this->filterMonth, 0, 4))
                  ->whereMonth('report_date', substr($this->filterMonth, 5, 2));
            })
            ->orderBy('report_date', 'desc')
            ->paginate(20);

        $users = ($this->isManager || $this->isViewOnly)
            ? \App\Models\User::role('marketing')->orderBy('name')->get()
            : collect();

        return view('livewire.admin.marketing.marketing-report-manager', [
            'history' => $history,
            'users'   => $users,
        ])->layout('admin.layouts.app', ['title' => 'Báo cáo Marketing hàng ngày']);
    }
}
