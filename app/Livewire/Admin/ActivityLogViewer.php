<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class ActivityLogViewer extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $logName = '';
    public $subjectType = '';
    public $event = '';
    public $perPage = 20;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingLogName()
    {
        $this->resetPage();
    }

    public function updatingSubjectType()
    {
        $this->resetPage();
    }

    public function updatingEvent()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Activity::with('causer')
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                  ->orWhereHas('causer', function ($q2) {
                      $q2->where('name', 'like', "%{$this->search}%");
                  });
            });
        }

        if ($this->logName) {
            $query->where('log_name', $this->logName);
        }

        if ($this->subjectType) {
            $query->where('subject_type', $this->subjectType);
        }

        if ($this->event) {
            $query->where('event', $this->event);
        }

        $activities = $query->paginate($this->perPage);

        $logNames = Activity::distinct()->pluck('log_name')->filter()->values();
        $subjectTypes = Activity::distinct()->pluck('subject_type')->filter()->map(function ($type) {
            return ['value' => $type, 'label' => class_basename($type)];
        })->values();
        $events = Activity::distinct()->pluck('event')->filter()->values();

        return view('livewire.admin.activity-log-viewer', compact(
            'activities', 'logNames', 'subjectTypes', 'events'
        ))->layout('admin.layouts.app', ['title' => 'Nhật ký hoạt động']);
    }
}
