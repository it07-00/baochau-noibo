<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class ActivityLogViewer extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function paginationView(): string
    {
        return 'livewire.admin.users.pagination';
    }

    public $search = '';

    public $logName = '';

    public $subjectType = '';

    public $event = '';

    public $dateFrom = '';

    public $dateTo = '';

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

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'logName', 'subjectType', 'event', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function getActiveFilterCountProperty(): int
    {
        return collect([$this->search, $this->logName, $this->subjectType, $this->event, $this->dateFrom, $this->dateTo])
            ->filter()
            ->count();
    }

    public function eventBadge(?string $event): array
    {
        return match ($event) {
            'created' => ['cls' => 'bg-label-success', 'icon' => 'bi-plus-circle-fill', 'label' => 'Tạo mới'],
            'updated' => ['cls' => 'bg-label-primary', 'icon' => 'bi-pencil-fill', 'label' => 'Cập nhật'],
            'deleted' => ['cls' => 'bg-label-danger', 'icon' => 'bi-trash-fill', 'label' => 'Xóa'],
            default => ['cls' => 'bg-label-secondary', 'icon' => 'bi-circle-fill', 'label' => ucfirst($event ?? 'N/A')],
        };
    }

    public function hasChangedValue(mixed $oldVal, mixed $newVal): bool
    {
        return $oldVal !== $newVal;
    }

    public function displayValue(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        return is_array($value)
            ? json_encode($value, JSON_UNESCAPED_UNICODE)
            : (string) $value;
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

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
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
