<div class="work-schedule-manager w-100 px-2 px-md-3 pb-5"
    x-data="{
        showForm: @entangle('showFormModal'),
        showDetail: @entangle('showDayDetailModal'),
    }">

    {{-- Header --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-calendar2-week me-2 text-primary"></i>Lịch công tác
                </h5>

                <div class="vr mx-1 d-none d-md-block" style="height: 24px;"></div>

                <div class="d-flex align-items-center gap-2">
                    <button wire:click="$set('monthFilter', {{ $monthFilter == 1 ? 12 : $monthFilter - 1 }})"
                        @if($monthFilter == 1) wire:click="$set('yearFilter', {{ $yearFilter - 1 }})" @endif
                        class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 30px; height: 30px;">
                        <i class="bi bi-chevron-left" style="font-size: 0.7rem;"></i>
                    </button>
                    <select wire:model.live="monthFilter" class="form-select form-select-sm border-light-subtle"
                        style="width: auto; border-radius: 6px;">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">Tháng {{ $m }}</option>
                        @endfor
                    </select>
                    <select wire:model.live="yearFilter" class="form-select form-select-sm border-light-subtle"
                        style="width: auto; border-radius: 6px;">
                        @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                    <button wire:click="$set('monthFilter', {{ $monthFilter == 12 ? 1 : $monthFilter + 1 }})"
                        @if($monthFilter == 12) wire:click="$set('yearFilter', {{ $yearFilter + 1 }})" @endif
                        class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 30px; height: 30px;">
                        <i class="bi bi-chevron-right" style="font-size: 0.7rem;"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-soft-info text-info px-3 py-2 rounded-pill fw-normal">
                    {{ $totalEvents }} sự kiện
                </span>
                <button wire:click="openCreateModal" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2" style="border-radius: 8px;">
                    <i class="bi bi-plus-lg"></i> Thêm sự kiện
                </button>
            </div>
        </div>
    </div>

    {{-- Calendar Grid --}}
    <div class="calendar-container shadow-sm bg-white" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
        <div class="calendar-header-grid bg-white border-bottom border-light-subtle">
            @php $daysOfWeek = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN']; @endphp
            @foreach($daysOfWeek as $dow)
                <div class="calendar-header-cell fw-bold text-muted text-center py-2">{{ $dow }}</div>
            @endforeach
        </div>

        <div class="calendar-body-grid">
            @php
                $monthStart = \Carbon\Carbon::create($yearFilter, $monthFilter, 1);
                $startOfCalendar = $monthStart->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                $endOfCalendar = $monthStart->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
                $period = \Carbon\CarbonPeriod::create($startOfCalendar, $endOfCalendar);
            @endphp

            @foreach ($period as $currentDate)
                @php
                    $dayKey = $currentDate->format('Y-m-d');
                    $dayNum = $currentDate->day;
                    $isInsideMonth = $currentDate->month == $monthFilter;
                    $dayEvents = collect($calendarData[$dayKey] ?? []);
                    $isToday = $currentDate->isToday();
                    $isSunday = $currentDate->isSunday();
                    $isPast = $currentDate->lt(today());
                    $canAddHere = !$isPast && $isInsideMonth;
                @endphp

                <div class="calendar-day-cell position-relative
                    @if(!$isInsideMonth) bg-light opacity-25
                    @elseif($isSunday) bg-sunday
                    @else bg-white
                    @endif
                    border-start border-bottom border-light-subtle
                    @if($isInsideMonth && $dayEvents->isNotEmpty()) cursor-pointer @endif"
                    style="min-height: 180px; transition: background 0.2s; padding: clamp(4px, 2vw, 12px); min-width: 0; overflow: hidden;"
                    @if($isInsideMonth && $dayEvents->isNotEmpty())
                        wire:click="openDayDetail('{{ $dayKey }}')"
                    @endif
                >
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="fw-bold {{ $isToday ? 'bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm' : 'text-muted opacity-75' }}"
                            style="width: 24px; height: 24px; font-size: 0.75rem;">
                            {{ $dayNum }}
                        </span>

                        @if($canAddHere)
                            <button wire:click.stop="openCreateModal('{{ $dayKey }}')"
                                class="ws-add-btn btn btn-sm p-0 d-flex align-items-center justify-content-center"
                                title="Thêm sự kiện"
                                style="width: 20px; height: 20px; border-radius: 50%; font-size: 0.65rem; opacity: 0; transition: opacity 0.2s;">
                                <i class="bi bi-plus"></i>
                            </button>
                        @endif
                    </div>

                    <div class="calendar-day-content mt-1">
                        @if($isInsideMonth && $dayEvents->isNotEmpty())
                            @foreach($dayEvents->take(4) as $evt)
                                <div class="mb-1 px-2 py-1 rounded ws-event-chip ws-chip-{{ $evt->color }}"
                                    style="font-size: 0.7rem; cursor: pointer; max-width: 100%; overflow: hidden;"
                                    wire:click.stop="openDayDetail('{{ $dayKey }}')">
                                    <div class="fw-bold text-truncate" style="max-width: 100%;">{{ $evt->title }}</div>
                                    <div class="ws-event-author text-truncate" style="font-size: 0.6rem; opacity: 0.7; max-width: 100%;">{{ $evt->participants->pluck('name')->join(', ') }}</div>
                                </div>
                            @endforeach
                            @if($dayEvents->count() > 4)
                                <div class="text-muted text-center" style="font-size: 0.65rem;">
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
         style="position: fixed; inset: 0; z-index: 9999;"
         @keydown.escape.window="showDetail && $wire.closeDayDetail()">
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.4);" wire:click="closeDayDetail"></div>
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
                                    <span class="fw-bold text-body fs-6">{{ $evt['title'] }}</span>
                                    <span class="ws-dept-badge badge ms-2 fw-normal">{{ $evt['department'] ?: 'Nhân viên' }}</span>
                                </div>
                                <div class="d-flex gap-1">
                                    @if($evt['is_owner'] && !$evt['is_past'])
                                        <button wire:click="edit({{ $evt['id'] }})"
                                            class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem; border-radius: 6px;">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif
                                    @if($evt['is_owner'])
                                        <button wire:click="delete({{ $evt['id'] }})"
                                            wire:confirm="Bạn chắc chắn muốn xóa sự kiện này?"
                                            class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem; border-radius: 6px;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2" style="font-size: 0.8rem;">
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
                            @if(!empty($evt['participants']))
                                <div class="mb-2" style="font-size: 0.8rem;">
                                    <i class="bi bi-people me-1 text-muted"></i>
                                    <span class="text-muted">Người tham gia:</span>
                                    <span class="fw-semibold">{{ $evt['participants'] }}</span>
                                </div>
                            @endif
                            @if($evt['description'])
                                <div class="text-body" style="white-space: pre-line; font-size: 0.9rem;">{{ $evt['description'] }}</div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="text-muted text-center py-4">Không có sự kiện nào.</div>
                @endif

                @php $canAddToday = $detailDate && !\Carbon\Carbon::parse($detailDate)->lt(today()); @endphp
                @if($canAddToday)
                    <div class="text-center mt-3">
                        <button wire:click="openCreateModal('{{ $detailDate }}')"
                            class="btn btn-outline-primary btn-sm px-4" style="border-radius: 8px;">
                            <i class="bi bi-plus-lg me-1"></i> Thêm sự kiện vào ngày này
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create/Edit Form Modal --}}
    <div x-show="showForm" x-cloak
         style="position: fixed; inset: 0; z-index: 10000;"
         @keydown.escape.window="showForm && $wire.closeFormModal()">
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.4);" wire:click="closeFormModal"></div>
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
                    <input type="text" wire:model="title" class="form-control border-light-subtle"
                        style="border-radius: 8px;" placeholder="Ví dụ: Họp giao ban, Công tác Hà Nội...">
                    @error('title') <span class="text-danger" style="font-size: 0.8rem;">{{ $message }}</span> @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted">Ngày bắt đầu *</label>
                        <input type="date" wire:model="startDate" class="form-control border-light-subtle"
                            style="border-radius: 8px;"
                            min="{{ today()->format('Y-m-d') }}">
                        @error('startDate') <span class="text-danger" style="font-size: 0.8rem;">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted">Ngày kết thúc <span class="fw-normal text-muted">(tùy chọn)</span></label>
                        <input type="date" wire:model="endDate" class="form-control border-light-subtle"
                            style="border-radius: 8px;"
                            min="{{ $startDate ?: today()->format('Y-m-d') }}">
                        @error('endDate') <span class="text-danger" style="font-size: 0.8rem;">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted">Màu nhãn</label>
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach(\App\Models\WorkSchedule::COLORS as $key => $c)
                            <label class="ws-color-picker {{ $color === $key ? 'active' : '' }}"
                                style="cursor: pointer;">
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
                    <textarea wire:model="description" class="form-control border-light-subtle" rows="3"
                        style="border-radius: 8px;" placeholder="Ghi chú thêm..."></textarea>
                    @error('description') <span class="text-danger" style="font-size: 0.8rem;">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-muted"><i class="bi bi-people me-1"></i>Người tham gia <span class="fw-normal text-muted">(tùy chọn)</span></label>
                    <div class="border rounded-3 p-2 border-light-subtle" style="max-height: 160px; overflow-y: auto; border-radius: 8px !important;">
                        @foreach($allUsers as $u)
                            <label class="d-flex align-items-center gap-2 py-1 px-2 rounded-2" style="cursor: pointer; font-size: 0.88rem;"
                                onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" wire:model="selectedParticipants" value="{{ $u->id }}"
                                    class="form-check-input m-0" style="border-radius: 4px;">
                                <span>{{ $u->name }}@if($u->id === auth()->id()) (Bạn) @endif</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" wire:click="closeFormModal" class="btn btn-light px-4" style="border-radius: 8px;">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px;">
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

