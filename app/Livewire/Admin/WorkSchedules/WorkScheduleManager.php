<?php

namespace App\Livewire\Admin\WorkSchedules;

use Livewire\Component;
use App\Models\WorkSchedule;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class WorkScheduleManager extends Component
{
    // Calendar navigation
    public int $monthFilter;
    public int $yearFilter;

    // Form fields
    public ?int $editingId = null;
    public string $title = '';
    public string $description = '';
    public string $startDate = '';
    public string $endDate = '';
    public string $color = 'primary';
    public array $selectedParticipants = [];

    // Modal state
    public bool $showFormModal = false;
    public bool $showDayDetailModal = false;
    public string $detailDate = '';
    public array $detailEvents = [];

    protected $queryString = [
        'monthFilter' => ['except' => ''],
        'yearFilter'  => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->monthFilter = (int) date('m');
        $this->yearFilter  = (int) date('Y');
    }

    public function openCreateModal(string $date = ''): void
    {
        $this->resetForm();

        if ($date) {
            $parsed = Carbon::parse($date);
            // Don't allow creating events in the past
            if ($parsed->lt(today())) {
                $this->dispatch('swal:error', ['message' => 'Không thể thêm sự kiện vào ngày trong quá khứ.']);
                return;
            }
            $this->startDate = $parsed->format('Y-m-d');
            $this->endDate   = $parsed->format('Y-m-d');
        } else {
            $this->startDate = today()->format('Y-m-d');
            $this->endDate   = today()->format('Y-m-d');
        }

        $this->showFormModal = true;
    }

    public function edit(int $id): void
    {
        $event = WorkSchedule::findOrFail($id);

        // Only owner can edit
        if ($event->user_id !== auth()->id()) {
            $this->dispatch('swal:error', ['message' => 'Bạn chỉ có thể chỉnh sửa sự kiện của mình.']);
            return;
        }

        // Cannot edit past events
        if ($event->start_date->lt(today())) {
            $this->dispatch('swal:error', ['message' => 'Không thể chỉnh sửa sự kiện trong quá khứ.']);
            return;
        }

        $this->editingId   = $event->id;
        $this->title       = $event->title;
        $this->description = $event->description ?? '';
        $this->startDate   = $event->start_date->format('Y-m-d');
        $this->endDate     = $event->effective_end_date->format('Y-m-d');
        $this->color       = $event->color;
        $this->selectedParticipants = $event->participants->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'title'     => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'startDate' => 'required|date',
            'endDate'   => 'nullable|date|after_or_equal:startDate',
            'color'     => 'required|in:' . implode(',', array_keys(WorkSchedule::COLORS)),
        ], [
            'title.required'          => 'Vui lòng nhập tiêu đề sự kiện.',
            'title.max'               => 'Tiêu đề không được vượt quá 255 ký tự.',
            'startDate.required'      => 'Vui lòng chọn ngày bắt đầu.',
            'endDate.after_or_equal'  => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'description.max'         => 'Mô tả không được vượt quá 5,000 ký tự.',
        ]);

        $startDate = Carbon::parse($this->startDate);
        $endDate   = $this->endDate ? Carbon::parse($this->endDate) : null;

        if ($this->editingId) {
            // Update existing
            $event = WorkSchedule::findOrFail($this->editingId);

            if ($event->user_id !== auth()->id()) {
                abort(403, 'Bạn chỉ có thể chỉnh sửa sự kiện của mình.');
            }

            if ($event->start_date->lt(today())) {
                $this->dispatch('swal:error', ['message' => 'Không thể chỉnh sửa sự kiện trong quá khứ.']);
                return;
            }

            $event->update([
                'title'       => $this->title,
                'description' => $this->description ?: null,
                'start_date'  => $startDate,
                'end_date'    => ($endDate && $endDate->ne($startDate)) ? $endDate : null,
                'color'       => $this->color,
            ]);
            $event->participants()->sync($this->selectedParticipants);

            $this->dispatch('swal:success', ['message' => 'Cập nhật sự kiện thành công!']);
        } else {
            // Create new — must be today or future
            if ($startDate->lt(today())) {
                $this->dispatch('swal:error', ['message' => 'Không thể thêm sự kiện vào ngày trong quá khứ.']);
                return;
            }

            $event = WorkSchedule::create([
                'user_id'     => auth()->id(),
                'title'       => $this->title,
                'description' => $this->description ?: null,
                'start_date'  => $startDate,
                'end_date'    => ($endDate && $endDate->ne($startDate)) ? $endDate : null,
                'color'       => $this->color,
            ]);
            $event->participants()->sync($this->selectedParticipants);

            $this->dispatch('swal:success', ['message' => 'Thêm sự kiện thành công!']);
        }

        $this->closeFormModal();
    }

    public function delete(int $id): void
    {
        $event = WorkSchedule::findOrFail($id);

        if ($event->user_id !== auth()->id()) {
            $this->dispatch('swal:error', ['message' => 'Bạn chỉ có thể xóa sự kiện của mình.']);
            return;
        }

        $event->delete();
        $this->dispatch('swal:success', ['message' => 'Đã xóa sự kiện.']);

        // Refresh detail modal if open
        if ($this->showDayDetailModal && $this->detailDate) {
            $this->openDayDetail($this->detailDate);
        }
    }

    public function openDayDetail(string $date): void
    {
        $this->detailDate = $date;
        $parsed = Carbon::parse($date);

        $events = WorkSchedule::with('user', 'user.department')
            ->where('start_date', '<=', $parsed)
            ->where(function ($q) use ($parsed) {
                $q->where('end_date', '>=', $parsed)
                  ->orWhere(function ($q2) use ($parsed) {
                      $q2->whereNull('end_date')
                         ->where('start_date', $parsed);
                  });
            })
            ->orderBy('start_date')
            ->get();

        $this->detailEvents = $events->map(fn ($e) => [
            'id'          => $e->id,
            'title'       => $e->title,
            'description' => $e->description ?? '',
            'start_date'  => $e->start_date->format('d/m/Y'),
            'end_date'    => $e->effective_end_date->format('d/m/Y'),
            'color'       => $e->color,
            'user_name'   => $e->user->name ?? '',
            'department'  => $e->user->department->name ?? '',
            'is_owner'    => $e->user_id === auth()->id(),
            'is_past'     => $e->start_date->lt(today()),
            'is_multi_day' => $e->end_date !== null && $e->end_date->ne($e->start_date),
            'participants' => $e->participants->map(fn($p) => $p->name)->join(', '),
        ])->toArray();

        $this->showDayDetailModal = true;
    }

    public function closeDayDetail(): void
    {
        $this->showDayDetailModal = false;
        $this->detailDate = '';
        $this->detailEvents = [];
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId   = null;
        $this->title       = '';
        $this->description = '';
        $this->startDate   = '';
        $this->endDate     = '';
        $this->color       = 'primary';
        $this->selectedParticipants = [(string) auth()->id()];
        $this->resetValidation();
    }

    public function render()
    {
        $monthStart = Carbon::create($this->yearFilter, $this->monthFilter, 1);
        $monthEnd   = $monthStart->copy()->endOfMonth();

        // Get all events that overlap with this month
        $events = WorkSchedule::with('user', 'user.department', 'participants')
            ->where(function ($query) use ($monthStart, $monthEnd) {
                $query->where(function ($q) use ($monthStart, $monthEnd) {
                    // Events with end_date that overlap the month
                    $q->whereNotNull('end_date')
                      ->where('start_date', '<=', $monthEnd)
                      ->where('end_date', '>=', $monthStart);
                })->orWhere(function ($q) use ($monthStart, $monthEnd) {
                    // Single-day events within the month
                    $q->whereNull('end_date')
                      ->whereBetween('start_date', [$monthStart, $monthEnd]);
                });
            })
            ->orderBy('start_date')
            ->get();

        // Build calendar data: map each day to its events
        $calendarData = [];
        $startOfCalendar = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar   = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);
        $period          = CarbonPeriod::create($startOfCalendar, $endOfCalendar);

        foreach ($period as $date) {
            $dayKey = $date->format('Y-m-d');
            $calendarData[$dayKey] = [];

            foreach ($events as $event) {
                $effectiveEnd = $event->end_date ?? $event->start_date;
                if ($date->between($event->start_date, $effectiveEnd)) {
                    $calendarData[$dayKey][] = $event;
                }
            }
        }

        $totalEvents = $events->count();

        $allUsers = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('livewire.admin.work-schedules.work-schedule-manager', [
            'calendarData' => $calendarData,
            'totalEvents'  => $totalEvents,
            'allUsers'     => $allUsers,
        ])->layout('admin.layouts.app', [
            'title'     => 'Lịch công tác',
            'fullWidth' => true,
        ]);
    }
}
