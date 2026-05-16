<div class="work-schedule-manager w-100 px-2 px-md-3 pb-5"
    x-data="{
        showForm: @entangle('showFormModal'),
        showDetail: @entangle('showDayDetailModal'),
    }">

    {{-- Header --}}
    <div class="ws-calendar-toolbar card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h5 class="ws-toolbar-title mb-0 fw-bold">
                    <span class="ws-title-icon"><i class="bi bi-calendar2-week"></i></span>
                    <span>Lịch công tác</span>
                </h5>

                <div class="vr mx-1 d-none d-md-block h-24px" ></div>

                <div class="ws-calendar-controls d-flex align-items-center gap-2">
                    <button wire:click="previousMonth"
                        class="ws-nav-button btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
                        title="Tháng trước">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <select wire:model.live="monthFilter" class="form-select form-select-sm border-light-subtle">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">Tháng {{ $m }}</option>
                        @endfor
                    </select>
                    <select wire:model.live="yearFilter" class="form-select form-select-sm border-light-subtle">
                        @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                    <button wire:click="nextMonth"
                        class="ws-nav-button btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
                        title="Tháng sau">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="ws-toolbar-actions d-flex align-items-center gap-3">
                <span class="ws-event-total badge bg-soft-info text-info px-3 py-2 rounded-pill fw-normal">
                    {{ $totalEvents }} sự kiện
                </span>
                <button wire:click="openCreateModal" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i> Thêm sự kiện
                </button>
            </div>
        </div>
    </div>

    @php
        $monthStart = \Carbon\Carbon::create($yearFilter, $monthFilter, 1);
        $startOfCalendar = $monthStart->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endOfCalendar = $monthStart->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
        $calendarDates = collect(\Carbon\CarbonPeriod::create($startOfCalendar, $endOfCalendar));
        $mobileEventDays = $calendarDates->filter(function ($date) use ($monthFilter, $calendarData) {
            $dayKey = $date->format('Y-m-d');

            return $date->month == $monthFilter && collect($calendarData[$dayKey] ?? [])->isNotEmpty();
        });
    @endphp

    {{-- Mobile Agenda --}}
    <div class="ws-mobile-agenda d-md-none">
        @forelse($mobileEventDays as $currentDate)
            @php
                $dayKey = $currentDate->format('Y-m-d');
                $dayEvents = collect($calendarData[$dayKey] ?? []);
                $isToday = $currentDate->isToday();
                $canAddHere = !$currentDate->lt(today());
            @endphp

            <section class="ws-agenda-day bg-white shadow-sm">
                <button type="button"
                    class="ws-agenda-day-header"
                    wire:click="openDayDetail('{{ $dayKey }}')">
                    <span class="ws-agenda-date {{ $isToday ? 'is-today' : '' }}">
                        <span class="ws-agenda-date-number">{{ $currentDate->format('d') }}</span>
                        <span class="ws-agenda-date-meta">
                            {{ $currentDate->translatedFormat('l') }}
                            <small>{{ $currentDate->format('d/m/Y') }}</small>
                        </span>
                    </span>
                    <span class="ws-agenda-count">{{ $dayEvents->count() }} sự kiện</span>
                </button>

                <div class="ws-agenda-events">
                    @foreach($dayEvents as $evt)
                        <button type="button"
                            class="ws-agenda-event ws-chip-{{ $evt->color }}"
                            wire:click="openDayDetail('{{ $dayKey }}')">
                            <span class="ws-agenda-event-title">{{ $evt->title }}</span>
                            <span class="ws-agenda-event-people">{{ $evt->time_range_label }}</span>
                            <span class="ws-agenda-event-people">{{ $evt->participants->pluck('name')->join(', ') }}</span>
                        </button>
                    @endforeach
                </div>

                @if($canAddHere)
                    <button type="button"
                        class="ws-agenda-add btn btn-sm btn-outline-primary"
                        wire:click="openCreateModal('{{ $dayKey }}')">
                        <i class="bi bi-plus-lg me-1"></i> Thêm vào ngày này
                    </button>
                @endif
            </section>
        @empty
            <div class="ws-agenda-empty bg-white shadow-sm">
                <i class="bi bi-calendar2-week"></i>
                <div class="fw-semibold">Chưa có sự kiện trong tháng này</div>
                <button wire:click="openCreateModal" class="btn btn-primary btn-sm mt-2">
                    <i class="bi bi-plus-lg me-1"></i> Thêm sự kiện
                </button>
            </div>
        @endforelse
    </div>

    {{-- Calendar Grid --}}
    <div class="calendar-container ws-desktop-calendar shadow-sm bg-white d-none d-md-block">
        <div class="calendar-header-grid bg-white border-bottom border-light-subtle">
            @php $daysOfWeek = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN']; @endphp
            @foreach($daysOfWeek as $dow)
                <div class="calendar-header-cell fw-bold text-muted text-center">{{ $dow }}</div>
            @endforeach
        </div>

        <div class="calendar-body-grid">
            @foreach ($calendarDates as $currentDate)
                @php
                    $dayKey = $currentDate->format('Y-m-d');
                    $dayNum = $currentDate->day;
                    $isInsideMonth = $currentDate->month == $monthFilter;
                    $dayEvents = collect($calendarData[$dayKey] ?? []);
                    $isToday = $currentDate->isToday();
                    $isWeekend = $currentDate->isWeekend();
                    $isPast = $currentDate->lt(today());
                    $canAddHere = !$isPast && $isInsideMonth;
                @endphp

                <div class="calendar-day-cell position-relative
                    @if(!$isInsideMonth) bg-light opacity-50
                    @else bg-white @if($isWeekend) bg-sunday @endif
                    @endif
                    border-start border-bottom border-light-subtle
                    @if($isInsideMonth && $dayEvents->isNotEmpty()) cursor-pointer @endif"
                    @if($isInsideMonth && $dayEvents->isNotEmpty())
                        wire:click="openDayDetail('{{ $dayKey }}')"
                    @endif
                >
                    <div class="ws-day-top d-flex justify-content-between align-items-start mb-2">
                        <span class="ws-day-number fw-bold {{ $isToday ? 'is-today' : '' }} {{ !$isInsideMonth ? 'is-muted' : '' }}">
                            {{ $dayNum }}
                        </span>

                        <div class="d-flex align-items-center gap-1">
                            @if($isInsideMonth && $dayEvents->isNotEmpty())
                                <span class="ws-day-event-count">{{ $dayEvents->count() }}</span>
                            @endif

                            @if($canAddHere)
                                <button wire:click.stop="openCreateModal('{{ $dayKey }}')"
                                    class="ws-add-btn btn btn-sm p-0 d-flex align-items-center justify-content-center"
                                    title="Thêm sự kiện">
                                    <i class="bi bi-plus"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="calendar-day-content">
                        @if($isInsideMonth && $dayEvents->isNotEmpty())
                            @foreach($dayEvents->take(4) as $evt)
                                <div class="ws-event-chip ws-chip-{{ $evt->color }}"
                                    wire:click.stop="openDayDetail('{{ $dayKey }}')">
                                    <div class="ws-event-title fw-bold text-truncate">{{ $evt->title }}</div>
                                    <div class="ws-event-author text-truncate">{{ $evt->time_range_label }} • {{ $evt->participants->pluck('name')->join(', ') }}</div>
                                </div>
                            @endforeach
                            @if($dayEvents->count() > 4)
                                <div class="ws-more-events text-muted text-center">
                                    +{{ $dayEvents->count() - 4 }} sự kiện khác
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Day Detail Modal --}}
    <div x-show="showDetail" x-cloak
         class="fixed-overlay-9999"
         @keydown.escape.window="showDetail && $wire.closeDayDetail()">
        <div class="modal-overlay-dark" wire:click="closeDayDetail"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 700px; max-height: 80vh; overflow-y: auto; background: var(--ws-modal-bg, #fff); border-radius: 16px; box-shadow: 0 25px 50px rgba(0,0,0,0.15);"
             @click.stop>
            <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-calendar-event me-2"></i> Lịch công tác ngày {{ $detailDate ? \Carbon\Carbon::parse($detailDate)->format('d/m/Y') : '' }}
                </h5>
                <button wire:click="closeDayDetail" class="btn-close"></button>
            </div>
            <div class="p-4">
                @if(count($detailEvents) > 0)
                    @foreach($detailEvents as $evt)
                        <div class="mb-3 p-3 rounded-3 border ws-detail-item ws-detail-{{ $evt['color'] }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="fw-bold text-body fs-5">{{ $evt['title'] }}</span>
                                    <span class="ws-dept-badge badge ms-2 fw-normal fs-85" >{{ $evt['department'] ?: 'Nhân viên' }}</span>
                                </div>
                                <div class="d-flex gap-2">
                                    @if($evt['is_owner'] && !$evt['is_past'])
                                        <button wire:click="edit({{ $evt['id'] }})"
                                            class="btn btn-sm btn-outline-primary px-2 rounded-2" >
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif
                                    @if($evt['is_owner'])
                                        <button wire:click="delete({{ $evt['id'] }})"
                                            wire:confirm="Bạn chắc chắn muốn xóa sự kiện này?"
                                            class="btn btn-sm btn-outline-danger px-2 rounded-2" >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2 fs-095" >
                                <span class="text-muted"><i class="bi bi-person me-1"></i>{{ $evt['user_name'] }}</span>
                                <span class="text-muted">•</span>
                                <span class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    {{ $evt['start_date'] }}
                                    @if($evt['is_multi_day'])
                                        → {{ $evt['end_date'] }}
                                    @endif
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2 fs-095">
                                <span class="text-muted"><i class="bi bi-clock me-1"></i>{{ $evt['time_label'] }}</span>
                            </div>
                            @if(!empty($evt['participants']))
                                <div class="mb-2 fs-095" >
                                    <i class="bi bi-people me-1 text-muted"></i>
                                    <span class="text-muted">Người tham gia:</span>
                                    <span class="fw-semibold">{{ $evt['participants'] }}</span>
                                </div>
                            @endif
                            @if($evt['description'])
                                <div class="text-body mt-2 text-preline" >{{ $evt['description'] }}</div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="text-muted text-center py-4">Không có sự kiện nào.</div>
                @endif

                @php $canAddToday = $detailDate && !\Carbon\Carbon::parse($detailDate)->lt(today()); @endphp
                @if($canAddToday)
                    <div class="text-center mt-4">
                        <button wire:click="openCreateModal('{{ $detailDate }}')"
                            class="btn btn-outline-primary px-4 py-2 fw-semibold rounded-10px fs-6" >
                            <i class="bi bi-plus-lg me-2"></i> Thêm sự kiện vào ngày này
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create/Edit Form Modal --}}
    <div x-show="showForm" x-cloak
         class="fixed-overlay-10000"
         @keydown.escape.window="showForm && $wire.closeFormModal()">
        <div class="modal-overlay-dark" wire:click="closeFormModal"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 550px; background: var(--ws-modal-bg, #fff); border-radius: 16px; box-shadow: 0 25px 50px rgba(0,0,0,0.15);"
             @click.stop>
            <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-{{ $editingId ? 'pencil-square' : 'calendar-plus' }} me-2"></i>
                    {{ $editingId ? 'Chỉnh sửa sự kiện' : 'Thêm sự kiện mới' }}
                </h5>
                <button wire:click="closeFormModal" class="btn-close"></button>
            </div>
            <form wire:submit.prevent="save" class="p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted">Tiêu đề *</label>
                    <input type="text" wire:model="title" class="form-control border-light-subtle rounded-8px"
                         placeholder="Ví dụ: Họp giao ban, Công tác Hà Nội...">
                    @error('title') <span class="text-danger fs-85" >{{ $message }}</span> @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted">Ngày bắt đầu *</label>
                        <input type="date" wire:model="startDate" class="form-control border-light-subtle rounded-8px"
                            
                            min="{{ today()->format('Y-m-d') }}">
                        @error('startDate') <span class="text-danger fs-85" >{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted">Ngày kết thúc <span class="fw-normal text-muted">(tùy chọn)</span></label>
                        <input type="date" wire:model="endDate" class="form-control border-light-subtle rounded-8px"
                            
                            min="{{ $startDate ?: today()->format('Y-m-d') }}">
                        @error('endDate') <span class="text-danger fs-85" >{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted">Giờ bắt đầu <span class="fw-normal text-muted">(tùy chọn)</span></label>
                        <input type="time" wire:model="startTime" class="form-control border-light-subtle rounded-8px">
                        @error('startTime') <span class="text-danger fs-85">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted">Giờ kết thúc <span class="fw-normal text-muted">(tùy chọn)</span></label>
                        <input type="time" wire:model="endTime" class="form-control border-light-subtle rounded-8px">
                        @error('endTime') <span class="text-danger fs-85">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted">Màu nhãn</label>
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach(\App\Models\WorkSchedule::COLORS as $key => $c)
                            <label class="ws-color-picker {{ $color === $key ? 'active' : '' }} cursor-pointer"
                                >
                                <input type="radio" wire:model.live="color" value="{{ $key }}" class="d-none">
                                <span class="d-block rounded-circle border-2"
                                    style="width: 28px; height: 28px; background: {{ $c['hex'] }}; border: 3px solid {{ $color === $key ? $c['hex'] : 'transparent' }}; box-shadow: {{ $color === $key ? '0 0 0 2px #fff, 0 0 0 4px '.$c['hex'] : 'none' }};"
                                    title="{{ $c['label'] }}"></span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-muted">Mô tả <span class="fw-normal text-muted">(tùy chọn)</span></label>
                    <textarea wire:model="description" class="form-control border-light-subtle rounded-8px" rows="3"
                         placeholder="Ghi chú thêm..."></textarea>
                    @error('description') <span class="text-danger fs-85" >{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-muted"><i class="bi bi-people me-1"></i>Người tham gia <span class="fw-normal text-muted">(tùy chọn)</span></label>
                    <div class="border rounded-3 p-2 border-light-subtle" style="max-height: 160px; overflow-y: auto; border-radius: 8px !important;">
                        @foreach($allUsers as $u)
                            <label class="d-flex align-items-center gap-2 py-1 px-2 rounded-2 cursor-pointer fs-88" 
                                onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" wire:model="selectedParticipants" value="{{ $u->id }}"
                                    class="form-check-input m-0 rounded-1" >
                                <span>{{ $u->name }}@if($u->id === auth()->id()) (Bạn) @endif</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" wire:click="closeFormModal" class="btn btn-light px-4 rounded-8px" >Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-8px" >
                        <i class="bi bi-{{ $editingId ? 'check-lg' : 'plus-lg' }} me-1"></i>
                        {{ $editingId ? 'Cập nhật' : 'Thêm sự kiện' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/work-schedule.css') }}">
    @endpush
</div>
