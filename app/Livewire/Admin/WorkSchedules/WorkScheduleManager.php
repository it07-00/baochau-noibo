<?php

namespace App\Livewire\Admin\WorkSchedules;

use App\Enums\Role;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Notifications\WorkScheduleNotification;
use App\Services\GreecoWorkScheduleRepository;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class WorkScheduleManager extends Component
{
    private const MAX_EVENT_LANES = 3;

    // Calendar navigation
    public int $monthFilter;

    public int $yearFilter;

    // Form fields
    public ?int $editingId = null;

    public string $title = '';

    public string $description = '';

    public string $startDate = '';

    public string $startTime = '';

    public string $endDate = '';

    public string $endTime = '';

    public string $color = 'primary';

    public array $selectedParticipants = [];

    public bool $isPrivate = false;

    // Modal state
    public bool $showFormModal = false;

    public bool $showDayDetailModal = false;

    public string $detailDate = '';

    public array $detailEvents = [];

    public bool $showGreecoSchedules = true;

    protected $queryString = [
        'monthFilter' => ['except' => ''],
        'yearFilter' => ['except' => ''],
        'showGreecoSchedules' => ['except' => true],
    ];

    public function mount(): void
    {
        if (auth()->user()->hasRole(Role::THUC_TAP->value)) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $this->monthFilter = (int) date('m');
        $this->yearFilter = (int) date('Y');
    }

    public function previousMonth(): void
    {
        if ($this->monthFilter === 1) {
            $this->monthFilter = 12;
            $this->yearFilter--;

            return;
        }

        $this->monthFilter--;
    }

    public function nextMonth(): void
    {
        if ($this->monthFilter === 12) {
            $this->monthFilter = 1;
            $this->yearFilter++;

            return;
        }

        $this->monthFilter++;
    }

    public function goToToday(): void
    {
        $this->monthFilter = (int) date('m');
        $this->yearFilter = (int) date('Y');
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
            $this->endDate = $parsed->format('Y-m-d');
        } else {
            $this->startDate = today()->format('Y-m-d');
            $this->endDate = today()->format('Y-m-d');
        }

        $this->showFormModal = true;
    }

    public function edit(int $id): void
    {
        $event = WorkSchedule::findOrFail($id);

        // Only owner or GIAM_DOC / IT can edit
        if ((int) $event->user_id !== (int) auth()->id() && ! auth()->user()->hasAnyRole([Role::GIAM_DOC->value, Role::IT->value])) {
            $this->dispatch('swal:error', ['message' => 'Bạn chỉ có thể chỉnh sửa sự kiện của mình.']);

            return;
        }

        // Cannot edit past events
        if ($event->start_date->lt(today())) {
            $this->dispatch('swal:error', ['message' => 'Không thể chỉnh sửa sự kiện trong quá khứ.']);

            return;
        }

        $this->editingId = $event->id;
        $this->title = $event->title;
        $this->description = $event->description ?? '';
        $this->startDate = $event->start_date->format('Y-m-d');
        $this->startTime = $event->formatted_start_time ?? '';
        $this->endDate = $event->effective_end_date->format('Y-m-d');
        $this->endTime = $event->formatted_end_time ?? '';
        $this->color = $event->color;
        $this->isPrivate = (bool) $event->is_private;

        $greecoParticipants = DB::table('work_schedule_participants')
            ->where('work_schedule_id', $event->id)
            ->whereNotNull('greeco_user_id')
            ->pluck('greeco_user_id')
            ->map(fn ($id) => 'greeco_'.$id)
            ->toArray();

        $this->selectedParticipants = array_merge(
            $event->participants->pluck('id')->map(fn ($id) => (string) $id)->toArray(),
            $greecoParticipants
        );
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'startDate' => 'required|date',
            'startTime' => 'nullable|date_format:H:i|required_with:endTime',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'endTime' => 'nullable|date_format:H:i',
            'color' => 'required|in:'.implode(',', array_keys(WorkSchedule::COLORS)),
            'isPrivate' => 'boolean',
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề sự kiện.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'startDate.required' => 'Vui lòng chọn ngày bắt đầu.',
            'startTime.date_format' => 'Giờ bắt đầu phải đúng định dạng HH:MM.',
            'startTime.required_with' => 'Vui lòng nhập giờ bắt đầu khi đã có giờ kết thúc.',
            'endDate.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'endTime.date_format' => 'Giờ kết thúc phải đúng định dạng HH:MM.',
            'description.max' => 'Mô tả không được vượt quá 5,000 ký tự.',
        ]);

        $startDate = Carbon::parse($this->startDate);
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;
        $startTime = $this->normalizeTimeForStorage($this->startTime);
        $endTime = $this->normalizeTimeForStorage($this->endTime);

        if ($startTime !== null && $endTime !== null && ($endDate === null || $endDate->isSameDay($startDate))) {
            $startAt = Carbon::parse($startDate->toDateString().' '.$startTime);
            $endAt = Carbon::parse(($endDate ?? $startDate)->toDateString().' '.$endTime);

            if ($endAt->lte($startAt)) {
                $this->addError('endTime', 'Giờ kết thúc phải sau giờ bắt đầu nếu sự kiện diễn ra trong cùng một ngày.');

                return;
            }
        }

        if ($this->editingId) {
            // Update existing
            $event = WorkSchedule::findOrFail($this->editingId);
            $previousParticipantIds = $event->participants()
                ->pluck('users.id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $localParticipantIds = [];
            $greecoParticipantsData = [];
            $greecoUsers = collect($this->getGreecoUsers());

            foreach ($this->selectedParticipants as $val) {
                if (str_starts_with((string) $val, 'greeco_')) {
                    $greecoId = (int) substr((string) $val, 7);
                    $greecoUser = $greecoUsers->firstWhere('id', $greecoId);
                    $greecoName = $greecoUser['name'] ?? 'Greeco User';
                    $greecoParticipantsData[] = [
                        'id' => $greecoId,
                        'name' => $greecoName,
                    ];
                } else {
                    $localParticipantIds[] = (int) $val;
                }
            }

            if ((int) $event->user_id !== (int) auth()->id() && ! auth()->user()->hasAnyRole([Role::GIAM_DOC->value, Role::IT->value])) {
                abort(403, 'Bạn chỉ có thể chỉnh sửa sự kiện của mình.');
            }

            if ($event->start_date->lt(today())) {
                $this->dispatch('swal:error', ['message' => 'Không thể chỉnh sửa sự kiện trong quá khứ.']);

                return;
            }

            $event->update([
                'title' => $this->title,
                'description' => $this->description ?: null,
                'start_date' => $startDate,
                'start_time' => $startTime,
                'end_date' => ($endDate && $endDate->ne($startDate)) ? $endDate : null,
                'end_time' => $endTime,
                'color' => $this->color,
                'is_private' => $this->isPrivate,
            ]);

            $event->participants()->sync($localParticipantIds);

            DB::table('work_schedule_participants')
                ->where('work_schedule_id', $event->id)
                ->whereNotNull('greeco_user_id')
                ->delete();

            foreach ($greecoParticipantsData as $gp) {
                DB::table('work_schedule_participants')->insert([
                    'work_schedule_id' => $event->id,
                    'user_id' => null,
                    'greeco_user_id' => $gp['id'],
                    'greeco_user_name' => $gp['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $addedParticipantIds = array_values(array_diff($localParticipantIds, $previousParticipantIds));
            $event->load('participants');

            // Notify local participants
            foreach ($event->participants as $participant) {
                if ($participant->id !== auth()->id()) {
                    $action = in_array((int) $participant->id, $addedParticipantIds, true) ? 'added' : 'updated';
                    $participant->notify(new WorkScheduleNotification(
                        $event->title,
                        auth()->user()->name,
                        $action,
                        $event->start_date->format('Y-m-d'),
                        $event->time_range_label
                    ));
                }
            }

            // Notify Greeco participants via cross-system API
            $this->notifyGreecoParticipants($event, $greecoParticipantsData, 'updated');

            $this->dispatch('swal:success', ['message' => 'Cập nhật sự kiện thành công!']);
        } else {
            // Create new — must be today or future
            if ($startDate->lt(today())) {
                $this->dispatch('swal:error', ['message' => 'Không thể thêm sự kiện vào ngày trong quá khứ.']);

                return;
            }

            $event = WorkSchedule::create([
                'user_id' => auth()->id(),
                'title' => $this->title,
                'description' => $this->description ?: null,
                'start_date' => $startDate,
                'start_time' => $startTime,
                'end_date' => ($endDate && $endDate->ne($startDate)) ? $endDate : null,
                'end_time' => $endTime,
                'color' => $this->color,
                'is_private' => $this->isPrivate,
            ]);

            $localParticipantIds = [];
            $greecoParticipantsData = [];
            $greecoUsers = collect($this->getGreecoUsers());

            foreach ($this->selectedParticipants as $val) {
                if (str_starts_with((string) $val, 'greeco_')) {
                    $greecoId = (int) substr((string) $val, 7);
                    $greecoUser = $greecoUsers->firstWhere('id', $greecoId);
                    $greecoName = $greecoUser['name'] ?? 'Greeco User';
                    $greecoParticipantsData[] = [
                        'id' => $greecoId,
                        'name' => $greecoName,
                    ];
                } else {
                    $localParticipantIds[] = (int) $val;
                }
            }

            $event->participants()->sync($localParticipantIds);

            foreach ($greecoParticipantsData as $gp) {
                DB::table('work_schedule_participants')->insert([
                    'work_schedule_id' => $event->id,
                    'user_id' => null,
                    'greeco_user_id' => $gp['id'],
                    'greeco_user_name' => $gp['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $event->load('participants');

            // Notify local participants
            foreach ($event->participants as $participant) {
                if ($participant->id !== auth()->id()) {
                    $participant->notify(new WorkScheduleNotification(
                        $event->title,
                        auth()->user()->name,
                        'added',
                        $event->start_date->format('Y-m-d'),
                        $event->time_range_label
                    ));
                }
            }

            // Notify Greeco participants via cross-system API
            $this->notifyGreecoParticipants($event, $greecoParticipantsData, 'added');

            $this->dispatch('swal:success', ['message' => 'Thêm sự kiện thành công!']);
        }

        $this->closeFormModal();
    }

    /**
     * Fire a POST to Greeco's /api/notify so Greeco users receive a notification
     * in their own system when added to a Bảo Châu work schedule.
     *
     * @param array<int, array{id: int, name: string}> $greecoParticipants
     */
    private function notifyGreecoParticipants(WorkSchedule $event, array $greecoParticipants, string $action = 'added'): void
    {
        if (empty($greecoParticipants)) {
            return;
        }

        $baseUrl = config('services.greeco.base_url');
        $token   = config('services.greeco.api_token');

        if (! $baseUrl || ! $token) {
            return;
        }

        $userIds = array_column($greecoParticipants, 'id');

        try {
            Http::timeout(5)->post(rtrim($baseUrl, '/').'/api/notify', [
                'token'        => $token,
                'user_ids'     => $userIds,
                'event_title'  => $event->title,
                'creator_name' => auth()->user()->name,
                'action'       => $action,
                'event_date'   => $event->start_date->format('Y-m-d'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('WorkScheduleManager: không thể gửi thông báo sang Greeco.', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function delete(int $id): void
    {
        $event = WorkSchedule::findOrFail($id);

        if ((int) $event->user_id !== (int) auth()->id() && ! auth()->user()->hasAnyRole([Role::GIAM_DOC->value, Role::IT->value])) {
            $this->dispatch('swal:error', ['message' => 'Bạn chỉ có thể xóa sự kiện của mình.']);

            return;
        }

        // Notify participants before deleting
        foreach ($event->participants as $participant) {
            if ($participant->id !== auth()->id()) {
                $participant->notify(new WorkScheduleNotification(
                    $event->title,
                    auth()->user()->name,
                    'deleted',
                    $event->start_date->format('Y-m-d'),
                    $event->time_range_label
                ));
            }
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

        $user = auth()->user();
        $isGdOrIt = $user->hasAnyRole([Role::GIAM_DOC->value, Role::IT->value]);

        $query = WorkSchedule::with('user', 'user.department', 'participants')
            ->where('start_date', '<=', $parsed)
            ->where(function ($q) use ($parsed) {
                $q->where('end_date', '>=', $parsed)
                    ->orWhere(function ($q2) use ($parsed) {
                        $q2->whereNull('end_date')
                            ->where('start_date', $parsed);
                    });
            });

        if (! $isGdOrIt) {
            $query->where(function ($q) use ($user) {
                $q->where('is_private', false)
                    ->orWhere('user_id', $user->id)
                    ->orWhereHas('participants', function ($pq) use ($user) {
                        $pq->where('users.id', $user->id);
                    });
            });
        }

        $events = $query->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

        $detailEvents = [];
        foreach ($events as $e) {
            $clone = $e->replicate();
            $clone->id = $e->id;
            $clone->start_date = $parsed->copy();
            $clone->end_date = null;
            if ($e->relationLoaded('user')) {
                $clone->setRelation('user', $e->user);
            }
            if ($e->relationLoaded('participants')) {
                $clone->setRelation('participants', $e->participants);
            }

            $detailEvents[] = [
                'id' => $clone->id,
                'title' => $clone->title,
                'description' => $clone->description ?? '',
                'start_date' => $clone->start_date->format('d/m/Y'),
                'end_date' => $clone->effective_end_date->format('d/m/Y'),
                'time_label' => $clone->time_range_label,
                'color' => $clone->color,
                'user_name' => $clone->user->name ?? '',
                'department' => $clone->user->department->name ?? '',
                'is_owner' => (int) $clone->user_id === (int) auth()->id() || auth()->user()->hasAnyRole([Role::GIAM_DOC->value, Role::IT->value]),
                'is_past' => $clone->ends_at->lt(now()),
                'is_multi_day' => false,
                'is_private' => (bool) $clone->is_private,
                'participants' => collect($clone->combined_participants)->pluck('name')->join(', '),
            ];
        }

        if ($this->showGreecoSchedules) {
            $greecoRepo = app(GreecoWorkScheduleRepository::class);
            $greecoItems = $greecoRepo->getEventsInRange($date, $date);
            foreach ($greecoItems as $item) {
                $startTime = ! empty($item['start_time']) ? substr((string) $item['start_time'], 0, 5) : null;
                $endTime = ! empty($item['end_time']) ? substr((string) $item['end_time'], 0, 5) : null;
                if ($startTime && $endTime) {
                    $timeLabel = $startTime.' - '.$endTime;
                } elseif ($startTime) {
                    $timeLabel = $startTime;
                } else {
                    $timeLabel = 'Cả ngày';
                }

                $startDate = $date;
                $endDate = $date;
                $endsAt = Carbon::parse($date);
                if ($endTime) {
                    $endsAt = Carbon::parse($date.' '.$endTime);
                } else {
                    $endsAt = $endsAt->endOfDay();
                }

                $detailEvents[] = [
                    'id' => 'greeco_'.($item['id'] ?? md5(json_encode($item))),
                    'title' => 'Greeco: '.($item['title'] ?? 'Work schedule'),
                    'description' => $item['description'] ?? '',
                    'start_date' => Carbon::parse($startDate)->format('d/m/Y'),
                    'end_date' => Carbon::parse($endDate)->format('d/m/Y'),
                    'time_label' => $timeLabel,
                    'color' => $item['color'] ?? 'primary',
                    'user_name' => $item['creator_name'] ?? 'N/A',
                    'department' => 'Greeco',
                    'is_owner' => false,
                    'is_past' => $endsAt->lt(now()),
                    'is_multi_day' => false,
                    'is_private' => false,
                    'participants' => collect($item['participants'] ?? [])->pluck('name')->join(', '),
                ];
            }
        }

        $this->detailEvents = $detailEvents;

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
        $this->editingId = null;
        $this->title = '';
        $this->description = '';
        $this->startDate = '';
        $this->startTime = '';
        $this->endDate = '';
        $this->endTime = '';
        $this->color = 'primary';
        $this->isPrivate = false;
        $this->selectedParticipants = [(string) auth()->id()];
        $this->resetValidation();
    }

    public function weekdayShortNames(): array
    {
        return ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'CN'];
    }

    public function calendarDayKey(Carbon $date): string
    {
        return $date->format('Y-m-d');
    }

    public function eventsForDate(array $calendarData, Carbon $date): array
    {
        return $calendarData[$this->calendarDayKey($date)] ?? [];
    }

    public function isInsideCurrentMonth(Carbon $date): bool
    {
        return $date->month === $this->monthFilter;
    }

    public function canAddInCalendarDate(Carbon $date): bool
    {
        return ! $date->lt(today()) && $this->isInsideCurrentMonth($date);
    }

    public function canAddForDetailDate(?string $date): bool
    {
        return ! empty($date) && ! Carbon::parse($date)->lt(today());
    }

    public function render()
    {
        $monthStart = Carbon::create($this->yearFilter, $this->monthFilter, 1);
        $monthEnd = $monthStart->copy()->endOfMonth();

        $events = WorkSchedule::query()
            ->with(['user', 'participants'])
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('start_date', [$monthStart, $monthEnd])
                    ->orWhere(function ($q2) use ($monthStart) {
                        $q2->where('start_date', '<', $monthStart)
                            ->where(function ($q3) use ($monthStart) {
                                $q3->where('end_date', '>=', $monthStart)
                                    ->orWhereNull('end_date');
                            });
                    });
            })
            ->where(function ($q) {
                $q->where('is_private', false)
                    ->orWhere('user_id', auth()->id())
                    ->orWhereHas('participants', function ($pq) {
                        $pq->where('users.id', auth()->id());
                    });
            })->get();

        if ($this->showGreecoSchedules) {
            $greecoRepo = app(GreecoWorkScheduleRepository::class);
            $greecoStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
            $greecoEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
            $greecoItems = $greecoRepo->getEventsInRange($greecoStart, $greecoEnd);
            foreach ($greecoItems as $item) {
                $events->push($greecoRepo->toWorkScheduleModel($item));
            }
        }

        $splitEvents = collect();
        foreach ($events as $event) {
            $startDate = $event->start_date;
            $endDate = $event->effective_end_date;

            if ($endDate->gt($startDate)) {
                $eventPeriod = CarbonPeriod::create($startDate, $endDate);
                foreach ($eventPeriod as $date) {
                    $clone = $event->replicate();
                    $clone->id = $event->id;
                    $clone->start_date = $date->copy();
                    $clone->end_date = null;
                    if ($event->relationLoaded('user')) {
                        $clone->setRelation('user', $event->user);
                    }
                    if ($event->relationLoaded('participants')) {
                        $clone->setRelation('participants', $event->participants);
                    }
                    $splitEvents->push($clone);
                }
            } else {
                $splitEvents->push($event);
            }
        }
        $events = $splitEvents;

        $startOfCalendar = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);
        $period = CarbonPeriod::create($startOfCalendar, $endOfCalendar);

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

        $weeksLayout = $this->buildWeeksLayout($events, $monthStart);

        $allUsers = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $calendarDates = collect(CarbonPeriod::create(
            $monthStart->copy()->startOfWeek(Carbon::MONDAY),
            $monthStart->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY)
        ));

        $mobileEventDays = $calendarDates->filter(function ($date) use ($calendarData) {
            return $date->month == $this->monthFilter && collect($calendarData[$date->format('Y-m-d')] ?? [])->isNotEmpty();
        });

        return view('livewire.admin.work-schedules.work-schedule-manager', [
            'calendarData' => $calendarData,
            'calendarDates' => $calendarDates,
            'mobileEventDays' => $mobileEventDays,
            'totalEvents' => $totalEvents,
            'allUsers' => $allUsers,
            'weeksLayout' => $weeksLayout,
        ])->layout('admin.layouts.app', [
            'title' => 'Lịch công tác',
            'fullWidth' => true,
        ]);
    }

    private function buildWeeksLayout($events, Carbon $monthStart): array
    {
        $startOfCal = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCal = $monthStart->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $weeksLayout = [];
        $currentWeekStart = $startOfCal->copy();

        while ($currentWeekStart->lte($endOfCal)) {
            $weekEnd = $currentWeekStart->copy()->addDays(6);
            $weekDates = [];
            for ($i = 0; $i < 7; $i++) {
                $weekDates[] = $currentWeekStart->copy()->addDays($i)->format('Y-m-d');
            }

            // Events overlapping this week
            $weekEvents = $events->filter(function ($event) use ($currentWeekStart, $weekEnd) {
                $effectiveEnd = $event->end_date ?? $event->start_date;

                return $event->start_date->lte($weekEnd) && $effectiveEnd->gte($currentWeekStart);
            })->sortBy([
                fn ($event) => $event->start_date->format('Y-m-d'),
                fn ($event) => $event->formatted_start_time ?? '00:00',
            ])->values();

            // Build placement data for each event
            $placements = [];
            foreach ($weekEvents as $event) {
                $effectiveEnd = $event->end_date ?? $event->start_date;
                $clippedStart = $event->start_date->lt($currentWeekStart)
                    ? $currentWeekStart->copy()
                    : $event->start_date->copy();
                $clippedEnd = $effectiveEnd->gt($weekEnd)
                    ? $weekEnd->copy()
                    : $effectiveEnd->copy();
                $startCol = (int) $currentWeekStart->diffInDays($clippedStart) + 1;
                $endCol = (int) $currentWeekStart->diffInDays($clippedEnd) + 1;
                $span = $endCol - $startCol + 1;

                $placements[] = [
                    'id' => $event->id,
                    'title' => $event->title,
                    'timeLabel' => $event->time_range_label,
                    'rangeLabel' => $event->start_date->format('d/m').' - '.$effectiveEnd->format('d/m'),
                    'isMultiDay' => $effectiveEnd->ne($event->start_date),
                    'color' => $event->color,
                    'participants' => collect($event->combined_participants)->pluck('name')->join(', '),
                    'startCol' => $startCol,
                    'endCol' => $endCol,
                    'span' => $span,
                    'startDate' => $event->start_date->format('Y-m-d'),
                ];
            }

            // Sort: earlier start first; longer span first within same start
            usort($placements, fn ($a, $b) => $a['startCol'] !== $b['startCol']
                ? $a['startCol'] - $b['startCol']
                : $b['span'] - $a['span']);

            // Greedy lane assignment (no two events in the same lane overlap columns)
            $laneAssignments = [];
            foreach ($placements as &$placement) {
                $assignedLane = null;
                foreach ($laneAssignments as $laneIdx => $lanePlacements) {
                    $overlaps = false;
                    foreach ($lanePlacements as $existing) {
                        if ($placement['startCol'] <= $existing['endCol'] && $placement['endCol'] >= $existing['startCol']) {
                            $overlaps = true;
                            break;
                        }
                    }
                    if (! $overlaps) {
                        $assignedLane = $laneIdx;
                        break;
                    }
                }
                if ($assignedLane === null) {
                    $assignedLane = count($laneAssignments);
                    $laneAssignments[$assignedLane] = [];
                }
                $placement['lane'] = $assignedLane;
                $laneAssignments[$assignedLane][] = $placement;
            }
            unset($placement);

            // Visible lanes (capped at MAX_EVENT_LANES)
            $visibleLanes = array_slice($laneAssignments, 0, self::MAX_EVENT_LANES, true);

            // Per-day overflow: count events hidden beyond MAX_EVENT_LANES
            $overflowPerDay = array_fill_keys($weekDates, 0);
            if (count($laneAssignments) > self::MAX_EVENT_LANES) {
                foreach (array_slice($laneAssignments, self::MAX_EVENT_LANES) as $lanePlacements) {
                    foreach ($lanePlacements as $p) {
                        for ($col = $p['startCol']; $col <= $p['endCol']; $col++) {
                            $overflowPerDay[$weekDates[$col - 1]]++;
                        }
                    }
                }
            }

            $weeksLayout[] = [
                'dates' => $weekDates,
                'lanes' => $visibleLanes,
                'overflowPerDay' => $overflowPerDay,
            ];

            $currentWeekStart->addWeek();
        }

        return $weeksLayout;
    }

    private function normalizeTimeForStorage(?string $time): ?string
    {
        if ($time === null || trim($time) === '') {
            return null;
        }

        return Carbon::createFromFormat('H:i', trim($time))->format('H:i:s');
    }

    public function getGreecoUsers(): array
    {
        $apiUrl = rtrim((string) config('services.greeco.api_url'), '/');
        $apiToken = (string) config('services.greeco.api_token');

        if ($apiUrl === '' || $apiToken === '') {
            return [];
        }

        $cacheKey = 'greeco_users_list';

        return cache()->remember($cacheKey, 300, function () use ($apiUrl, $apiToken) {
            try {
                $response = Http::timeout(5)
                    ->get("{$apiUrl}/api/users", [
                        'token' => $apiToken,
                    ]);

                if (! $response->successful()) {
                    return [];
                }

                $data = $response->json('data', []);

                return is_array($data) ? $data : [];
            } catch (\Throwable $e) {
                Log::warning('Greeco API users request failed: '.$e->getMessage());

                return [];
            }
        });
    }
}
