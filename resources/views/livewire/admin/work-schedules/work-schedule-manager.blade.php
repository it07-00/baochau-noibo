<div class="work-schedule-manager w-100 px-2 px-md-3 pb-5" x-data="{
        showForm: @entangle('showFormModal'),
        showDetail: @entangle('showDayDetailModal'),
    }">

    {{-- Toolbar Header --}}
    <div class="ws-calendar-toolbar card border-0 shadow-sm mb-4">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3 py-3 px-4 bg-body-tertiary">
            <div class="d-flex align-items-center gap-2">
                <button wire:click="previousMonth"
                    class="btn btn-light btn-sm border border-light-subtle rounded-3 wh-36 d-flex align-items-center justify-content-center"
                    title="Tháng trước">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button wire:click="nextMonth"
                    class="btn btn-light btn-sm border border-light-subtle rounded-3 wh-36 d-flex align-items-center justify-content-center"
                    title="Tháng sau">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                <button wire:click="goToToday"
                    class="btn btn-light btn-sm border border-light-subtle rounded-3 px-3 py-2 fw-semibold ms-1"
                    title="Hôm nay">
                    Hôm nay
                </button>
                <div class="vr mx-2 opacity-25"></div>
                <div class="form-check form-switch mb-0 ms-2 d-flex align-items-center gap-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="showGreecoSwitch"
                        wire:model.live="showGreecoSchedules">
                    <label class="form-check-label text-muted small fw-semibold user-select-none" for="showGreecoSwitch">
                        Hiện lịch Greeco
                    </label>
                </div>
            </div>

            <div class="text-center">
                <h3 class="mb-0 fw-bold fs-4 text-body">
                    Tháng {{ $monthFilter }} Năm {{ $yearFilter }}
                </h3>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-3 py-2 fw-semibold">
                    {{ $totalEvents }} sự kiện
                </span>
                <button class="btn btn-primary btn-sm px-3 rounded-3 fw-semibold pe-none">
                    Tháng
                </button>
                <button wire:click="openCreateModal"
                    class="btn btn-outline-primary btn-sm px-3 rounded-3 fw-semibold d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Thêm sự kiện
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Agenda --}}
    <div class="ws-mobile-agenda d-md-none">
        @forelse($mobileEventDays as $currentDate)
            <section class="ws-agenda-day bg-body shadow-sm">
                <button type="button" class="ws-agenda-day-header"
                    wire:click="openDayDetail('{{ $this->calendarDayKey($currentDate) }}')">
                    <span class="ws-agenda-date {{ $currentDate->isToday() ? 'is-today' : '' }}">
                        <span class="ws-agenda-date-number">{{ $currentDate->format('d') }}</span>
                        <span class="ws-agenda-date-meta">
                            {{ Str::ucfirst($currentDate->locale('vi')->translatedFormat('l')) }}
                            <small>{{ $currentDate->format('d/m/Y') }}</small>
                        </span>
                    </span>
                    <span class="ws-agenda-count">{{ count($this->eventsForDate($calendarData, $currentDate)) }} sự kiện</span>
                </button>

                <div class="ws-agenda-events">
                    @foreach($this->eventsForDate($calendarData, $currentDate) as $evt)
                        <button type="button" class="ws-agenda-event ws-chip-{{ $evt->color }}"
                            wire:click="openDayDetail('{{ $this->calendarDayKey($currentDate) }}')">
                            <span class="ws-agenda-event-title">{{ $evt->title }}</span>
                            <span class="ws-agenda-event-people">{{ $evt->time_range_label }}</span>
                            <span class="ws-agenda-event-people">{{ collect($evt->combined_participants)->pluck('name')->join(', ') }}</span>
                        </button>
                    @endforeach
                </div>

                @if(!$currentDate->lt(today()))
                    <button type="button" class="ws-agenda-add btn btn-sm btn-outline-primary"
                        wire:click="openCreateModal('{{ $this->calendarDayKey($currentDate) }}')">
                        <i class="fa-solid fa-plus me-1"></i> Thêm vào ngày này
                    </button>
                @endif
            </section>
        @empty
            <div class="ws-agenda-empty bg-body shadow-sm">
                <i class="fa-solid fa-calendar-week"></i>
                <div class="fw-semibold">Chưa có sự kiện trong tháng này</div>
                <button wire:click="openCreateModal" class="btn btn-primary btn-sm mt-2">
                    <i class="fa-solid fa-plus me-1"></i> Thêm sự kiện
                </button>
            </div>
        @endforelse
    </div>

    {{-- Calendar Grid --}}
    <div class="calendar-container ws-desktop-calendar shadow-sm bg-body d-none d-md-block mb-4">
        <div class="calendar-header-grid bg-body-tertiary border-bottom border-secondary-subtle" data-bs-theme="light">
            @foreach($this->weekdayShortNames() as $dow)
                <div class="calendar-header-cell fw-semibold text-body text-center py-2 border-start border-secondary-subtle">
                    {{ $dow }}
                </div>
            @endforeach
        </div>

        <div class="calendar-body-grid">
            @foreach ($calendarDates as $currentDate)
            @php($eventsForDay = $this->eventsForDate($calendarData, $currentDate))
            <div class="calendar-day-cell position-relative border-start border-bottom border-light-subtle d-flex flex-column
                    @if(!$this->isInsideCurrentMonth($currentDate)) bg-light opacity-50
                    @else bg-body @if($currentDate->isWeekend()) bg-sunday @endif
                    @endif"
                @if($currentDate->isToday()) style="border-top: 3px solid var(--bs-primary) !important;" @endif>
                <div class="ws-day-top d-flex justify-content-between align-items-center mb-2">
                    <span class="ws-day-number fw-bold d-inline-flex align-items-center justify-content-center
                            @if($currentDate->isToday()) is-today @endif
                            @if(!$this->isInsideCurrentMonth($currentDate)) is-muted @endif">
                        {{ $currentDate->day }}
                    </span>

                    <div class="d-flex align-items-center gap-2">
                        @if($this->isInsideCurrentMonth($currentDate) && count($eventsForDay) > 0)
                            <span class="badge bg-body-secondary text-muted rounded-pill fs-8">{{ count($eventsForDay) }}</span>
                        @endif

                        @if($this->canAddInCalendarDate($currentDate))
                            <button wire:click.stop="openCreateModal('{{ $this->calendarDayKey($currentDate) }}')"
                                class="ws-add-btn btn btn-sm p-0 d-flex align-items-center justify-content-center"
                                title="Thêm sự kiện">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="calendar-day-content flex-grow-1" @if($this->isInsideCurrentMonth($currentDate) && count($eventsForDay) > 0) style="cursor: pointer;"
                wire:click="openDayDetail('{{ $this->calendarDayKey($currentDate) }}')" @endif>
                    @if($this->isInsideCurrentMonth($currentDate) && count($eventsForDay) > 0)
                        @foreach($eventsForDay as $evt)
                            <div class="ws-event-chip ws-chip-{{ $evt->color }}"
                                wire:click.stop="openDayDetail('{{ $this->calendarDayKey($currentDate) }}')">
                                <div class="d-flex flex-column mb-1">
                                    <span class="ws-event-author text-truncate fw-semibold">{{ Str::limit($evt->user?->name ?? 'Hệ thống', 15, '...') }}</span>
                                    <span class="ws-event-time fw-medium">{{ $evt->time_range_label }}</span>
                                </div>
                                <div class="ws-event-title fw-bold text-truncate mb-0">
                                    @if($evt->is_private)
                                        <i class="fa-solid fa-lock text-warning me-1"></i>
                                    @endif
                                    {{ $evt->title }}
                                </div>
                                @if(collect($evt->combined_participants)->isNotEmpty() && !($evt->user_id && collect($evt->combined_participants)->count() === 1 && (int) collect($evt->combined_participants)->first()['id'] === (int) $evt->user_id && collect($evt->combined_participants)->first()['system'] === 'baochau'))
                                    <div class="text-muted text-truncate fs-8 mt-1"
                                        title="{{ collect($evt->combined_participants)->pluck('name')->join(', ') }}">
                                        •
                                        @if(collect($evt->combined_participants)->count() > 2)
                                            {{ collect($evt->combined_participants)->take(2)->pluck('name')->join(', ') }}
                                            +{{ collect($evt->combined_participants)->count() - 2 }}
                                        @else
                                            {{ collect($evt->combined_participants)->pluck('name')->join(', ') }}
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Day Detail Modal --}}
    <div x-show="showDetail" x-cloak class="fixed-overlay-9999"
        @keydown.escape.window="showDetail && $wire.closeDayDetail()">
        <div class="modal-overlay-dark" wire:click="closeDayDetail"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 92%; max-width: 660px; max-height: 85vh; overflow-y: auto; background: var(--ws-modal-bg, #fff); border-radius: 16px; box-shadow: 0 25px 50px rgba(0,0,0,0.2);"
            @click.stop>
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-body-tertiary">
                <div class="d-flex align-items-center gap-3">
                    <div class="wh-40 rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fs-5 flex-shrink-0">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-body">Lịch công tác</h5>
                        <div class="text-muted small fw-semibold">
                            {{ $detailDate ? Str::ucfirst(\Carbon\Carbon::parse($detailDate)->locale('vi')->translatedFormat('l, \n\gà\y d/m/Y')) : '' }}
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if(count($detailEvents) > 0)
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold fs-85">
                            {{ count($detailEvents) }} sự kiện
                        </span>
                    @endif
                    <button wire:click="closeDayDetail" class="btn-close ms-2"></button>
                </div>
            </div>
            <div class="p-4">
                @if(count($detailEvents) > 0)
                    <div class="d-flex flex-column gap-3">
                    @foreach($detailEvents as $evt)
                        <div class="p-3 rounded-3 border ws-detail-item ws-detail-{{ $evt['color'] }}">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                        <h6 class="fw-bold text-body mb-0 fs-6">{{ $evt['title'] }}</h6>
                                        @if(!empty($evt['department']))
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle rounded-pill px-2 py-1 fs-8">{{ $evt['department'] }}</span>
                                        @endif
                                        @if(!empty($evt['is_private']))
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fs-8">
                                                <i class="fa-solid fa-lock me-1"></i>Riêng tư
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1 flex-shrink-0 ms-2">
                                    @if($evt['is_owner'] && !$evt['is_past'])
                                        <button wire:click="edit({{ $evt['id'] }})"
                                            class="btn btn-sm btn-outline-primary border-0 p-1 rounded-2"
                                            title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    @endif
                                    @if($evt['is_owner'])
                                        <button wire:click="delete({{ $evt['id'] }})"
                                            wire:confirm="Bạn chắc chắn muốn xóa sự kiện này?"
                                            class="btn btn-sm btn-outline-danger border-0 p-1 rounded-2"
                                            title="Xóa sự kiện">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Metadata Row using standard Bootstrap 5 utilities -->
                            <div class="d-flex align-items-center flex-wrap gap-3 my-2 text-muted small">
                                <span><i class="fa-solid fa-user text-primary me-2"></i><strong class="text-body">{{ $evt['user_name'] }}</strong></span>
                                <span><i class="fa-regular fa-clock text-warning me-2"></i><span class="text-body fw-semibold">{{ $evt['time_label'] }}</span></span>
                                @if($evt['is_multi_day'])
                                    <span><i class="fa-regular fa-calendar text-info me-2"></i><span class="text-body">{{ $evt['start_date'] }} → {{ $evt['end_date'] }}</span></span>
                                @endif
                            </div>

                            @if(!empty($evt['participants']))
                                <div class="mt-2 text-muted small d-flex align-items-start gap-2 pt-2 border-top border-light-subtle">
                                    <i class="fa-solid fa-users text-primary opacity-75 mt-1 flex-shrink-0"></i>
                                    <div class="min-w-0 flex-grow-1">
                                        <span class="text-muted me-1">Người tham gia:</span>
                                        <strong class="text-body">{{ $evt['participants'] }}</strong>
                                    </div>
                                </div>
                            @endif

                            @if($evt['description'])
                                <div class="mt-2 text-body small text-preline d-flex align-items-start gap-2 pt-2 border-top border-light-subtle">
                                    <i class="fa-solid fa-align-left text-muted opacity-50 mt-1 flex-shrink-0"></i>
                                    <div class="flex-grow-1">{{ $evt['description'] }}</div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                    </div>
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                        <i class="fa-solid fa-calendar-xmark fs-2 mb-2 opacity-50"></i>
                        <div class="fw-semibold">Không có sự kiện nào trong ngày này.</div>
                    </div>
                @endif

                @if($this->canAddForDetailDate($detailDate))
                    <div class="text-center mt-4 pt-3 border-top border-light-subtle">
                        <button wire:click="openCreateModal('{{ $detailDate }}')"
                            class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-plus me-1"></i> Thêm sự kiện vào ngày này
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create/Edit Form Modal --}}
    <div x-show="showForm" x-cloak class="fixed-overlay-10000"
        @keydown.escape.window="showForm && $wire.closeFormModal()">
        <div class="modal-overlay-dark" wire:click="closeFormModal"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 92%; max-width: 580px; max-height: 90vh; overflow-y: auto; background: var(--ws-modal-bg, #fff); border-radius: 16px; box-shadow: 0 25px 50px rgba(0,0,0,0.18);"
            @click.stop>
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-body-tertiary">
                <div class="d-flex align-items-center gap-3">
                    <div class="wh-40 rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fs-5 flex-shrink-0">
                        <i class="fa-solid fa-{{ $editingId ? 'pen-to-square' : 'calendar-plus' }}"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-body">{{ $editingId ? 'Chỉnh sửa sự kiện' : 'Thêm sự kiện mới' }}</h5>
                        <div class="text-muted small">Nhập thông tin chi tiết lịch công tác</div>
                    </div>
                </div>
                <button wire:click="closeFormModal" class="btn-close"></button>
            </div>
            <form wire:submit.prevent="save" class="p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold text-body small">Tiêu đề công tác <span class="text-danger">*</span></label>
                    <input type="text" wire:model="title" class="form-control border-light-subtle rounded-3 py-2"
                        placeholder="Ví dụ: Họp giao ban, Công tác Hà Nội, Gặp khách hàng...">
                    @error('title') <span class="text-danger fs-85 mt-1 d-block">{{ $message }}</span> @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-body small">Ngày bắt đầu <span class="text-danger">*</span></label>
                        <input type="date" wire:model="startDate" class="form-control border-light-subtle rounded-3 py-2"
                            min="{{ today()->format('Y-m-d') }}">
                        @error('startDate') <span class="text-danger fs-85 mt-1 d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-body small">Ngày kết thúc <span class="fw-normal text-muted">(tùy chọn)</span></label>
                        <input type="date" wire:model="endDate" class="form-control border-light-subtle rounded-3 py-2"
                            min="{{ $startDate ?: today()->format('Y-m-d') }}">
                        @error('endDate') <span class="text-danger fs-85 mt-1 d-block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-body small">Giờ bắt đầu <span class="fw-normal text-muted">(tùy chọn)</span></label>
                        <input type="time" wire:model="startTime" class="form-control border-light-subtle rounded-3 py-2">
                        @error('startTime') <span class="text-danger fs-85 mt-1 d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-body small">Giờ kết thúc <span class="fw-normal text-muted">(tùy chọn)</span></label>
                        <input type="time" wire:model="endTime" class="form-control border-light-subtle rounded-3 py-2">
                        @error('endTime') <span class="text-danger fs-85 mt-1 d-block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-body small">Màu nhãn phân loại</label>
                    <div class="d-flex gap-2 flex-wrap p-3 rounded-3 bg-body-tertiary border border-light-subtle">
                        @foreach(\App\Models\WorkSchedule::COLORS as $key => $c)
                            <label class="ws-color-picker {{ $color === $key ? 'active' : '' }} cursor-pointer p-1 rounded-circle">
                                <input type="radio" wire:model.live="color" value="{{ $key }}" class="d-none">
                                <span class="d-block rounded-circle border-2"
                                    style="width: 28px; height: 28px; background: {{ $c['hex'] }}; border: 3px solid {{ $color === $key ? $c['hex'] : 'transparent' }}; box-shadow: {{ $color === $key ? '0 0 0 2px var(--bs-body-bg), 0 0 0 4px ' . $c['hex'] : 'none' }};"
                                    title="{{ $c['label'] }}"></span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-body small">Mô tả nội dung <span class="fw-normal text-muted">(tùy chọn)</span></label>
                    <textarea wire:model="description" class="form-control border-light-subtle rounded-3" rows="3"
                        placeholder="Ghi chú chi tiết công việc, địa điểm..."></textarea>
                    @error('description') <span class="text-danger fs-85 mt-1 d-block">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3 p-3 rounded-3 bg-body-tertiary border border-light-subtle">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="isPrivateSwitch"
                            wire:model="isPrivate">
                        <label class="form-check-label fw-bold text-body small" for="isPrivateSwitch">
                            <i class="fa-solid fa-lock me-1 text-warning"></i> Sự kiện riêng tư (chỉ người tham gia mới xem được)
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-body small"><i class="fa-solid fa-users me-1 text-primary"></i>Danh sách người tham gia <span class="fw-normal text-muted">(tùy chọn)</span></label>
                    <div class="border rounded-3 p-3 border-light-subtle bg-body"
                        style="max-height: 220px; overflow-y: auto;">
                        <div class="small fw-bold text-primary mb-2 pb-1 border-bottom d-flex align-items-center gap-2">
                            <i class="fa-solid fa-building"></i> Công ty Bảo Châu
                        </div>
                        @foreach($allUsers as $u)
                            <label class="d-flex align-items-center gap-2 py-1 px-2 rounded-2 cursor-pointer fs-88 mb-1"
                                onmouseover="this.style.background='var(--bs-secondary-bg)'"
                                onmouseout="this.style.background='transparent'">
                                <input type="checkbox" wire:model="selectedParticipants" value="{{ $u->id }}"
                                    class="form-check-input m-0 rounded-1">
                                <span>{{ $u->name }}@if($u->id === auth()->id()) <strong class="text-primary">(Bạn)</strong> @endif</span>
                            </label>
                        @endforeach

                        @if(!empty($greecoUsers))
                            <div class="small fw-bold text-success mt-3 mb-2 pb-1 border-bottom d-flex align-items-center gap-2">
                                <i class="fa-solid fa-leaf"></i> Công ty Greeco
                            </div>
                            @foreach($greecoUsers as $gu)
                                <label class="d-flex align-items-center gap-2 py-1 px-2 rounded-2 cursor-pointer fs-88 mb-1"
                                    onmouseover="this.style.background='var(--bs-secondary-bg)'"
                                    onmouseout="this.style.background='transparent'">
                                    <input type="checkbox" wire:model="selectedParticipants" value="greeco_{{ $gu['id'] }}"
                                        class="form-check-input m-0 rounded-1">
                                    <span>{{ $gu['name'] }} <small class="text-muted">({{ $gu['department'] ?? 'Greeco' }})</small></span>
                                </label>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-2 border-top border-light-subtle">
                    <button type="button" wire:click="closeFormModal"
                        class="btn btn-light border border-light-subtle px-4 py-2 rounded-3 fw-semibold">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-{{ $editingId ? 'check' : 'plus' }}"></i>
                        {{ $editingId ? 'Cập nhật' : 'Thêm sự kiện' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/work-schedule.css') }}?v={{ config('app.version') }}">
    @endpush
</div>
