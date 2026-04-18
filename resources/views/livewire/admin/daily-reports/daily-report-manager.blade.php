<div class="daily-report-manager {{ ($activeTab === 'management' || $activeTab === 'history') ? 'w-100 px-2 px-md-3' : 'container-fluid px-2 px-md-3' }} pb-5"
    x-data="{ activeTab: @entangle('activeTab') }">
    <div class="row">
        <div class="{{ ($activeTab === 'management' || $activeTab === 'history') ? 'col-12' : 'col-lg-8 mx-auto' }}">
            <!-- Unified Tab Navigation -->
            <ul class="nav nav-pills mb-3 bg-white p-2 shadow-sm d-flex flex-wrap" style="border-radius: 12px;">
                @if($canSubmitOwnReport)
                    <li class="nav-item">
                        <button wire:click="$set('activeTab', 'form')"
                            class="nav-link nav-pill-btn {{ $activeTab === 'form' ? 'active bg-dark' : 'text-muted' }}"
                            style="border-radius: 10px;">
                            <i class="bi bi-pencil-square me-1 me-md-2"></i><span class="nav-pill-label">Gửi báo cáo</span>
                        </button>
                    </li>
                    <li class="nav-item ms-1 ms-md-2">
                        <button wire:click="$set('activeTab', 'history')"
                            class="nav-link nav-pill-btn {{ $activeTab === 'history' ? 'active bg-dark' : 'text-muted' }}"
                            style="border-radius: 10px;">
                            <i class="bi bi-calendar3 me-1 me-md-2"></i><span class="nav-pill-label">Lịch sử cá nhân</span>
                        </button>
                    </li>
                @endif
                @if($isManager)
                    <li class="nav-item ms-1 ms-md-2">
                        <button wire:click="$set('activeTab', 'management')"
                            class="nav-link nav-pill-btn {{ $activeTab === 'management' ? 'active bg-dark' : 'text-muted' }}"
                            style="border-radius: 10px;">
                            <i class="bi bi-speedometer2 me-1 me-md-2"></i><span class="nav-pill-label">Quản lý chung</span>
                        </button>
                    </li>
                @endif
            </ul>

            @if($canSubmitOwnReport && $activeTab === 'form')
                <!-- Daily Report Form -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="mb-0 fw-bold">Báo cáo ngày của tôi</h5>
                            @if($isEditing)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2"
                                    style="border-radius: 20px;">
                                    <i class="bi bi-check-circle-fill me-1"></i> Đã gửi
                                </span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="text-muted  fw-bold mb-0">Ngày báo cáo:</label>
                            <input
                                type="date"
                                wire:model.live="reportDate"
                                max="{{ now()->format('Y-m-d') }}"
                                class="form-control form-control-sm border-light-subtle"
                                style="width: auto; border-radius: 8px;"
                            >
                        </div>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <form wire:submit.prevent="save">
                            <div class="mb-3">
                                <label class="form-label  fw-bold text-muted">Hôm nay đã làm gì *</label>
                                <div wire:ignore class="editor-container">
                                    <div id="content-editor">{!! $content !!}</div>
                                </div>
                                @error('content') <span class="text-danger ">{{ $message }}</span> @enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label  fw-bold text-muted">Kết quả tổng thể hôm nay</label>
                                    <select wire:model.live.debounce.500ms="status" class="form-select border-light-subtle"
                                        style="border-radius: 8px;">
                                        <option value="Hoàn thành đúng kế hoạch">Hoàn thành đúng kế hoạch</option>
                                        <option value="Hoàn thành một phần">Hoàn thành một phần</option>
                                        <option value="Gặp vấn đề, cần hỗ trợ">Gặp vấn đề, cần hỗ trợ</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label  fw-bold text-muted">Kế hoạch ngày mai <span
                                            class="fw-normal text-muted">(không bắt buộc)</span></label>
                                    <textarea wire:model.live.debounce.500ms="plan" class="form-control border-light-subtle"
                                        rows="5" style="border-radius: 8px;"
                                        placeholder="Sẽ làm gì tiếp..."></textarea>
                                    @error('plan') <span class="text-danger ">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Formatting Tips -->
                            <div class="daily-report-tip-box mt-4 p-3 border-0 bg-light-subtle"
                                style="border-radius: 12px; border: 1px dashed #e2e8f0 !important;">
                                <div class="d-flex gap-3">
                                    <div class="text-primary mt-1">
                                        <i class="bi bi-lightbulb fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1  text-body">Mẹo viết báo cáo chuyên nghiệp:</h6>
                                        <ul class="mb-0  text-muted ps-3">
                                            <li>Sử dụng <strong>Dấu đầu dòng (Bullet points)</strong> để liệt kê các công
                                                việc cụ thể.</li>
                                            <li><strong>In đậm (Bold)</strong> những kết quả quan trọng hoặc tên khách
                                                hàng/dự án.</li>
                                            <li>Ghi chú rõ ràng nếu có vấn đề phát sinh để cấp trên hỗ trợ kịp thời.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 mb-4">
                                <label class="form-label  fw-bold text-muted">Vấn đề / Cần hỗ trợ <span
                                        class="fw-normal text-muted">(không bắt buộc)</span></label>
                                <textarea wire:model.live.debounce.500ms="issues" class="form-control border-light-subtle"
                                    rows="3" style="border-radius: 8px;"
                                    placeholder="Nếu có vấn đề cần TPKD biết hoặc hỗ trợ, ghi ở đây..."></textarea>
                            </div>

                            <div class="d-flex align-items-center gap-3 mt-4">
                                <button type="submit" class="btn btn-dark px-4 py-2" style="border-radius: 8px;">
                                    <i class="bi bi-{{ $isEditing ? 'pencil-square' : 'send' }} me-1"></i>
                                    {{ $isEditing ? 'Cập nhật báo cáo' : 'Gửi báo cáo ngày' }}
                                </button>
                                @if($isEditing)
                                    <span class="text-success  fw-bold"><i class="bi bi-info-circle me-1"></i> Bạn đang chỉnh sửa báo cáo ngày {{ \Carbon\Carbon::parse($reportDate)->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-muted ">Nội dung sẽ tự động lưu khi bạn nhập</span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <div class="daily-report-help-alert alert alert-warning border-0 shadow-sm py-3"
                    style="background-color: #fdf5ea; border-radius: 12px;">
                    <p class="mb-0  text-body">
                        <i class="bi bi-info-circle me-1"></i> Nếu chọn <strong>"Gặp vấn đề, cần hỗ trợ"</strong> &rarr;
                        TPKD nhận thông báo ngay sau khi bạn gửi.
                    </p>
                </div>

            @elseif($canSubmitOwnReport && $activeTab === 'history')
                <!-- Employee History Header -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div
                        class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="mb-0 fw-bold">Lịch sử báo cáo tháng {{ $monthFilter }}/{{ $yearFilter }}</h5>
                            <div class="d-flex align-items-center gap-2">
                                <select wire:model.live="monthFilter" class="form-select form-select-sm border-light-subtle"
                                    style="width: auto; border-radius: 6px;">
                                    @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">Tháng {{ $m }}</option> @endfor
                                </select>
                                <select wire:model.live="yearFilter" class="form-select form-select-sm border-light-subtle"
                                    style="width: auto; border-radius: 6px;">
                                    @for($y = date('Y') - 1; $y <= date('Y'); $y++)
                                    <option value="{{ $y }}">{{ $y }}</option> @endfor
                                </select>
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-soft-info text-info px-3 py-2 rounded-pill fw-normal">Đã gửi
                                {{ $reportStats['total'] }} báo cáo</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @php $renderCalendar = true; @endphp
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($activeTab === 'management' && $isManager)
        <!-- MANAGER VIEW -->
        <div class="row g-0">
            <div class="col-12 px-3">
                <div class="{{ ($viewType === 'month') ? 'border-0 border-bottom' : 'card border-0 shadow-sm mb-4' }}"
                    style="{{ ($viewType === 'month') ? '' : 'border-radius: 12px; overflow: hidden;' }}">
                    <div
                        class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-4">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" wire:click="$set('viewType', 'day')"
                                    class="btn {{ $viewType === 'day' ? 'btn-primary' : 'btn-outline-primary' }}">Xem theo
                                    ngày</button>
                                <button type="button" wire:click="$set('viewType', 'month')"
                                    class="btn {{ $viewType === 'month' ? 'btn-primary' : 'btn-outline-primary' }}">Xem theo
                                    tháng</button>
                            </div>

                            <div class="vr mx-1 d-none d-md-block" style="height: 24px;"></div>

                            @if($viewType === 'day')
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted  fw-bold">Ngày:</span>
                                    <input type="date" wire:model.live="dateFilter"
                                        class="form-control form-control-sm border-light-subtle"
                                        style="width: auto; border-radius: 6px;">
                                </div>
                            @else
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted  fw-bold">Kỳ báo cáo:</span>
                                    <select wire:model.live="monthFilter" class="form-select form-select-sm border-light-subtle"
                                        style="width: auto; border-radius: 6px;">
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ sprintf('%02d', $m) }}">Tháng {{ $m }}</option>
                                        @endfor
                                    </select>
                                    <select wire:model.live="yearFilter" class="form-select form-select-sm border-light-subtle"
                                        style="width: auto; border-radius: 6px;">
                                        @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            @endif

                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted  fw-bold">Bộ lọc:</span>
                                <select wire:model.live="deptIdFilter"
                                    class="form-select form-select-sm border-light-subtle"
                                    style="width: auto; border-radius: 6px;">
                                    <option value="">Tất cả phòng ban</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>

                                <select wire:model.live="userIdFilter"
                                    class="form-select form-select-sm border-light-subtle"
                                    style="width: auto; border-radius: 6px;">
                                    <option value="">Tất cả nhân viên</option>
                                    @foreach($users as $user)
                                        @if(!$deptIdFilter || $user->department_id == $deptIdFilter)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex gap-2 align-items-center">
                                @if($viewType === 'day')
                                    @if($reportStats['issues'] > 0)
                                        <span
                                            class="badge bg-soft-danger text-danger px-3 py-2 rounded-pill fw-normal">{{ $reportStats['issues'] }}
                                            cần hỗ trợ</span>
                                    @endif
                                    @if($reportStats['missing'] > 0)
                                        <span
                                            class="badge bg-soft-warning text-warning px-3 py-2 rounded-pill fw-normal">{{ $reportStats['missing'] }}
                                            chưa báo cáo</span>
                                    @endif
                                @else
                                    <span
                                        class="badge bg-soft-info text-info px-3 py-2 rounded-pill fw-normal">{{ $reportStats['total'] }}
                                        báo cáo tổng cộng</span>
                                @endif
                            </div>
                            <button wire:click="export" wire:loading.attr="disabled"
                                class="btn btn-sm btn-success px-3 shadow-sm d-flex align-items-center gap-2"
                                style="border-radius: 6px;">
                                <i class="bi bi-file-earmark-excel"></i> Xuất dữ liệu
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @if($viewType === 'day')
                                @foreach($reports as $item)
                                    @if($item->report)
                                            <!-- Reported -->
                                            <div class="list-group-item p-4 border-light-subtle @if($item->report->status === 'Gặp vấn đề, cần hỗ trợ') bg-soft-danger-light @endif"
                                                x-data="{ expanded: false }">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="rounded-pill bg-light d-flex align-items-center justify-content-center fw-bold text-muted "
                                                            style="width: 38px; height: 38px;">
                                                            {{ strtoupper(substr($item->user->name, 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-bold text-danger">{{ $item->user->name }} <span
                                                                    class="badge bg-soft-success text-success fw-normal rounded-pill ms-2 ">{{ $item->user->department?->name ?? 'Nhân viên' }}</span>
                                                            </h6>
                                                        </div>
                                                    </div>
                                                    <div>
                                        @php
                                            $statusLabelClass = 'text-success';
                                            if ($item->report->status === 'Gặp vấn đề, cần hỗ trợ')
                                                $statusLabelClass = 'text-danger';
                                            elseif ($item->report->status === 'Hoàn thành một phần')
                                                $statusLabelClass = 'text-warning';
                                        @endphp
                                                        <span
                                                            class="{{ $statusLabelClass }}  fw-bold">{{ $item->report->status }}</span>
                                                    </div>
                                                </div>
                                                <div class="mb-2 ps-5 ms-3">
                                                    <div class="text-body riched-content" :class="expanded ? '' : 'limit-height'">
                                                        {!! $item->report->content !!}
                                                    </div>
                                                    <button @click="expanded = !expanded"
                                                        class="btn btn-link btn-sm p-0 mt-2 text-primary text-decoration-none fw-bold">
                                                        <span x-show="!expanded"><i class="bi bi-chevron-down me-1"></i> Xem đầy đủ báo
                                                            cáo</span>
                                                        <span x-show="expanded"><i class="bi bi-chevron-up me-1"></i> Thu gọn lại</span>
                                                    </button>
                                                    @if($item->report->issues)
                                                        <div class="text-danger mt-2 ">
                                                            <strong>Vấn đề/Hỗ trợ:</strong> {{ $item->report->issues }}
                                                        </div>
                                                    @endif
                                                    <div class="text-muted mt-2 ">
                                                        <span class="text-danger opacity-75">Kế hoạch mai:</span>
                                                        {!! nl2br(e($item->report->plan)) !!}
                                                    </div>
                                                </div>
                                            </div>
                                    @else
                                        <!-- Not Reported -->
                                        <div class="list-group-item p-4 border-light-subtle border-dashed">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded-pill bg-light d-flex align-items-center justify-content-center fw-bold text-muted "
                                                        style="width: 38px; height: 38px;">
                                                        {{ strtoupper(substr($item->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-muted opacity-75 fw-normal">{{ $item->user->name }} &mdash;
                                                            chưa gửi báo cáo</h6>
                                                    </div>
                                                </div>
                                                <span class="text-warning  fw-bold">Chưa báo cáo</span>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                @php $renderCalendar = true; @endphp
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- SHARED CALENDAR GRID -->
    @if(isset($renderCalendar) && $renderCalendar)
        <div class="calendar-container shadow-sm bg-white"
            style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
            <div class="calendar-header-grid bg-white border-bottom border-light-subtle">
                @php $daysOfWeek = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN']; @endphp
                @foreach($daysOfWeek as $dow)
                    <div class="calendar-header-cell  fw-bold text-muted text-center py-2">{{ $dow }}</div>
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
                        $dayNum = $currentDate->day;
                        $isInsideMonth = $currentDate->month == $monthFilter;
                        $dayReports = collect($calendarData[$dayNum] ?? []);
                        $isPast = $currentDate->isPast() && !$currentDate->isToday();
                        $isToday = $currentDate->isToday();
                        $isSunday = $currentDate->isSunday();
                        $isComplete = $dayReports->isNotEmpty();
                        $hasIssue = $dayReports->where('status', 'Gặp vấn đề, cần hỗ trợ')->isNotEmpty();

                        $dotColor = 'secondary';
                        if ($isInsideMonth) {
                            if ($isComplete) {
                                $dotColor = $hasIssue ? 'danger' : 'success';
                            } else {
                                if ($isSunday)
                                    $dotColor = 'secondary opacity-25';
                                elseif ($isPast)
                                    $dotColor = 'danger';
                                else
                                    $dotColor = 'warning';
                            }
                        }
                    @endphp

                    <div class="calendar-day-cell @if(!$isInsideMonth) bg-light opacity-25 @elseif($isSunday) bg-sunday @else bg-white @endif border-start border-bottom border-light-subtle @if($isInsideMonth && $dayReports->isNotEmpty()) cursor-pointer @endif"
                        style="min-height: 140px; transition: background 0.2s;padding: clamp(4px, 2vw, 12px);"
                        @if($isInsideMonth && $dayReports->isNotEmpty())
                            @click="$dispatch('open-day-detail', { date: '{{ $currentDate->format('d/m/Y') }}', reports: {{ $dayReports->map(fn($r) => ['name' => $r->user->name ?? '', 'department' => $r->user->department->name ?? '', 'status' => $r->status, 'content' => $r->content, 'plan' => $r->plan ?? '', 'issues' => $r->issues ?? ''])->toJson() }} })"
                        @endif
                    >
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span
                                class=" fw-bold {{ $isToday ? 'bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm' : 'text-muted opacity-75' }}"
                                style="width: 24px; height: 24px; font-size: 0.75rem;">
                                {{ $dayNum }}
                            </span>
                            @if($isInsideMonth)
                                <span class="status-indicator bg-{{ $dotColor }} shadow-sm"></span>
                            @endif
                        </div>

                        <div class="calendar-day-content mt-1">
                            @if($isInsideMonth && $dayReports->isNotEmpty())
                                @foreach($dayReports as $dr)
                                    <div class="mb-1 px-2 py-1 rounded cal-report-chip cal-chip-{{ $dr->status === 'Gặp vấn đề, cần hỗ trợ' ? 'issue' : ($dr->status === 'Hoàn thành một phần' ? 'partial' : 'done') }}" style="font-size: 0.7rem;">
                                        <div class="fw-bold text-truncate" style="max-width: 100%;">{{ $dr->user->name ?? '' }}</div>
                                        <div class="riched-content-mini text-truncate-2">{!! Str::limit(strip_tags($dr->content), 80) !!}</div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Day Detail Modal -->
    <div x-data="{ open: false, date: '', reports: [] }"
         @open-day-detail.window="open = true; date = $event.detail.date; reports = $event.detail.reports"
         x-show="open" x-cloak
         style="position: fixed; inset: 0; z-index: 9999;"
         @keydown.escape.window="open = false">
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.4);" @click="open = false"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 700px; max-height: 80vh; overflow-y: auto; background: var(--daily-report-modal-bg, #fff); border-radius: 16px; box-shadow: 0 25px 50px rgba(0,0,0,0.15);"
             @click.stop>
            <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2"></i> Báo cáo ngày <span x-text="date"></span></h5>
                <button @click="open = false" class="btn-close"></button>
            </div>
            <div class="p-4">
                <template x-for="(r, idx) in reports" :key="idx">
                    <div
                        class="daily-report-modal-item mb-4 p-3 rounded-3 border"
                        :class="r.status === 'Gặp vấn đề, cần hỗ trợ' ? 'status-issue' : (r.status === 'Hoàn thành một phần' ? 'status-partial' : 'status-done')"
                        :style="'background-color:' + (r.status === 'Gặp vấn đề, cần hỗ trợ' ? '#fff5f5' : (r.status === 'Hoàn thành một phần' ? '#fffbeb' : '#f0fdf4'))">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="fw-bold text-body" x-text="r.name"></span>
                                <span class="daily-report-dept-badge badge ms-2 fw-normal " x-text="r.department"></span>
                            </div>
                            <span class=" fw-bold" :class="r.status === 'Gặp vấn đề, cần hỗ trợ' ? 'text-danger' : (r.status === 'Hoàn thành một phần' ? 'text-warning' : 'text-success')" x-text="r.status"></span>
                        </div>
                        <div class="riched-content  text-body" style="word-break: break-word; overflow-wrap: break-word;" x-html="r.content"></div>
                        <template x-if="r.plan">
                            <div class="mt-2  text-muted"><span class="text-danger opacity-75 fw-bold">Kế hoạch mai:</span> <span x-text="r.plan"></span></div>
                        </template>
                        <template x-if="r.issues">
                            <div class="mt-2  text-danger"><strong>Vấn đề/Hỗ trợ:</strong> <span x-text="r.issues"></span></div>
                        </template>
                    </div>
                </template>
                <div x-show="reports.length === 0" class="text-muted text-center py-4">Không có báo cáo.</div>
            </div>
        </div>
    </div>

    <style>
        .calendar-header-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }

        .calendar-body-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }

        .calendar-day-cell:hover {
            background-color: #f8fafc !important;
        }

        .calendar-day-cell.cursor-pointer {
            cursor: pointer;
        }

        .calendar-day-cell.cursor-pointer:hover {
            background-color: #eef2ff !important;
        }

        .nav-pill-btn {
            padding: 6px 12px;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        @media (max-width: 576px) {
            .nav-pill-btn {
                padding: 5px 8px;
                font-size: 0.78rem;
            }
            .calendar-day-cell {
                min-height: 70px !important;
            }
            .calendar-header-cell {
                font-size: 0.72rem;
                padding-top: 4px !important;
                padding-bottom: 4px !important;
            }
            .status-indicator {
                width: 6px;
                height: 6px;
            }
            .cal-report-chip {
                display: none;
            }
            .calendar-day-content::after {
                content: none;
            }
        }

        @media (max-width: 400px) {
            .nav-pill-label {
                display: none;
            }
            .nav-pill-btn {
                padding: 6px 10px;
            }
        }

        .bg-sunday {
            background-color: #f7fafc !important;
        }

        .bg-soft-danger-light {
            background-color: #fff5f5 !important;
        }

        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 11;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .nav-pills .nav-link.active {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .riched-content p {
            margin-bottom: 0.5rem;
        }

        .riched-content ul,
        .riched-content ol {
            margin-bottom: 0.5rem;
            padding-left: 1.5rem;
        }

        .riched-content-mini {
            font-size: 0.65rem !important;
            line-height: 1.3 !important;
            color: #64748b !important;
            max-height: 60px;
            overflow: hidden;
        }

        .riched-content-mini p {
            margin-bottom: 2px;
        }

        .riched-content-mini ul,
        .riched-content-mini ol {
            padding-left: 12px;
            margin-bottom: 2px;
            list-style-type: disc;
        }

        .riched-content-mini li {
            margin-bottom: 0;
        }

        .riched-content-mini strong,
        .riched-content-mini b {
            font-weight: 600;
            color: #475569;
        }

        /* Hide overlay when typing or focused */
        .textarea-overlay-container:focus-within .position-absolute {
            display: none !important;
        }

        /* CKEditor Custom Styles */
        .ck-editor__animated-placeholder {
            font-size: 0.9rem !important;
            color: #94a3b8 !important;
        }

        .ck-editor__editable {
            min-height: 400px !important;
            border-radius: 0 0 8px 8px !important;
            border-color: #e2e8f0 !important;
            font-size: 1rem !important;
            line-height: 1.6 !important;
        }

        .limit-height {
            max-height: 200px;
            overflow: hidden;
            position: relative;
        }

        .limit-height::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background: linear-gradient(transparent, rgba(255, 255, 255, 0.9));
            pointer-events: none;
        }

        .riched-content {
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .daily-report-dept-badge {
            background-color: #edf2f7;
            color: #475569;
            border: 1px solid #d5dee9;
        }

        .daily-report-modal-item {
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .ck-toolbar {
            border-radius: 8px 8px 0 0 !important;
            border-color: #e2e8f0 !important;
            background-color: #f8fafc !important;
        }

        #plan-editor+.ck-editor .ck-editor__editable {
            min-height: 120px !important;
        }

        [data-bs-theme="dark"] .daily-report-manager {
            --daily-report-modal-bg: #1b222c;
        }

        [data-bs-theme="dark"] .daily-report-manager .nav.nav-pills,
        [data-bs-theme="dark"] .daily-report-manager .card-header.bg-white,
        [data-bs-theme="dark"] .daily-report-manager .calendar-container,
        [data-bs-theme="dark"] .daily-report-manager .calendar-header-grid.bg-white {
            background-color: #1f2631 !important;
            color: var(--bs-body-color) !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .calendar-container,
        [data-bs-theme="dark"] .daily-report-manager .calendar-day-cell,
        [data-bs-theme="dark"] .daily-report-manager .calendar-header-grid {
            border-color: rgba(255, 255, 255, 0.12) !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .calendar-day-cell.bg-white {
            background-color: #1a2029 !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .calendar-day-cell.bg-light {
            background-color: #131820 !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .bg-sunday {
            background-color: #212938 !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .calendar-day-cell:hover {
            background-color: #253041 !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .calendar-day-cell.cursor-pointer:hover {
            background-color: #29405f !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .daily-report-tip-box {
            border-color: rgba(255, 255, 255, 0.2) !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .daily-report-help-alert {
            background-color: rgba(245, 158, 11, 0.16) !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .form-control.border-light-subtle,
        [data-bs-theme="dark"] .daily-report-manager .form-select.border-light-subtle {
            background-color: #141b24 !important;
            border-color: rgba(255, 255, 255, 0.16) !important;
            color: var(--bs-body-color) !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .riched-content-mini {
            color: #c5d0df !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .riched-content-mini strong,
        [data-bs-theme="dark"] .daily-report-manager .riched-content-mini b {
            color: #e5ecf8 !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .limit-height::after {
            background: linear-gradient(transparent, rgba(27, 34, 44, 0.94));
        }

        [data-bs-theme="dark"] .daily-report-manager .ck-toolbar {
            border-color: rgba(255, 255, 255, 0.16) !important;
            background-color: #1f2631 !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .ck.ck-toolbar .ck-button,
        [data-bs-theme="dark"] .daily-report-manager .ck.ck-toolbar .ck-button .ck-icon,
        [data-bs-theme="dark"] .daily-report-manager .ck.ck-toolbar .ck-icon {
            color: #dbe7f9 !important;
            fill: #dbe7f9 !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .ck.ck-toolbar .ck-button:hover:not(.ck-disabled),
        [data-bs-theme="dark"] .daily-report-manager .ck.ck-toolbar .ck-button:focus:not(.ck-disabled) {
            background-color: rgba(148, 163, 184, 0.2) !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .ck.ck-toolbar .ck-button.ck-on {
            background-color: rgba(59, 130, 246, 0.3) !important;
            color: #f8fbff !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .ck.ck-toolbar .ck-button.ck-disabled .ck-icon {
            opacity: 0.45;
        }

        [data-bs-theme="dark"] .daily-report-manager .ck-editor__editable {
            background-color: #121821 !important;
            color: var(--bs-body-color) !important;
            border-color: rgba(255, 255, 255, 0.16) !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .daily-report-dept-badge {
            background-color: #2a3545;
            color: #dbe7f6;
            border-color: rgba(255, 255, 255, 0.18);
        }

        [data-bs-theme="dark"] .daily-report-manager .daily-report-modal-item {
            color: var(--bs-body-color);
        }

        [data-bs-theme="dark"] .daily-report-manager .daily-report-modal-item.status-done {
            background-color: rgba(16, 185, 129, 0.11) !important;
            border-color: rgba(16, 185, 129, 0.34) !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .daily-report-modal-item.status-partial {
            background-color: rgba(245, 158, 11, 0.12) !important;
            border-color: rgba(245, 158, 11, 0.34) !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .daily-report-modal-item.status-issue {
            background-color: rgba(239, 68, 68, 0.12) !important;
            border-color: rgba(239, 68, 68, 0.34) !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .daily-report-modal-item .text-muted {
            color: #b9c6d8 !important;
        }

        [data-bs-theme="dark"] .daily-report-manager .daily-report-modal-item .riched-content,
        [data-bs-theme="dark"] .daily-report-manager .daily-report-modal-item .riched-content * {
            color: #e4edf9 !important;
            border-color: rgba(255, 255, 255, 0.24) !important;
            background: transparent !important;
        }

        /* Calendar mini chip colors */
        .cal-report-chip.cal-chip-done    { background-color: #dcfce7; }
        .cal-report-chip.cal-chip-partial { background-color: #fef3c7; }
        .cal-report-chip.cal-chip-issue   { background-color: #fee2e2; }

        [data-bs-theme="dark"] .daily-report-manager .cal-report-chip.cal-chip-done {
            background-color: rgba(16, 185, 129, 0.18) !important;
            color: #a7f3d0;
        }
        [data-bs-theme="dark"] .daily-report-manager .cal-report-chip.cal-chip-partial {
            background-color: rgba(245, 158, 11, 0.18) !important;
            color: #fde68a;
        }
        [data-bs-theme="dark"] .daily-report-manager .cal-report-chip.cal-chip-issue {
            background-color: rgba(239, 68, 68, 0.18) !important;
            color: #fca5a5;
        }
        [data-bs-theme="dark"] .daily-report-manager .cal-report-chip .riched-content-mini {
            color: inherit !important;
            opacity: 0.85;
        }

        [data-bs-theme="dark"] .daily-report-manager .daily-report-modal-item .riched-content a {
            color: #8ab4ff !important;
            text-decoration: underline;
        }

        [data-bs-theme="dark"] .daily-report-manager .daily-report-modal-item .riched-content li::marker {
            color: #9fb4cf;
        }

        [data-bs-theme="dark"] .daily-report-manager .daily-report-modal-item .riched-content .text-muted,
        [data-bs-theme="dark"] .daily-report-manager .daily-report-modal-item .riched-content .text-secondary,
        [data-bs-theme="dark"] .daily-report-manager .daily-report-modal-item .riched-content .text-dark {
            color: #d6e2f1 !important;
            opacity: 1 !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            initAllEditors();
        });

        function initAllEditors() {
            // Content Editor
            const contentEl = document.querySelector('#content-editor');
            if (contentEl && !contentEl.classList.contains('ck-editor-initialized')) {
                ClassicEditor.create(contentEl, {
                    placeholder: 'Chi tiết công việc đã làm...',
                    toolbar: ['bold', 'italic', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo']
                }).then(editor => {
                    // Sync to Livewire
                    editor.model.document.on('change:data', () => {
                        @this.set('content', editor.getData());
                    });

                    // Apply buffered content when editor is initialized.
                    if (window.__dailyReportContentBuffer !== undefined) {
                        editor.setData(window.__dailyReportContentBuffer || '');
                    }

                    contentEl.classList.add('ck-editor-initialized');
                    window.contentEditor = editor;
                });
            }

            // Plan Editor (Not using CKEditor for plan as per user request)
        }

        // Handle Livewire Navigation/Morphing
        document.addEventListener('livewire:navigated', initAllEditors);
        document.addEventListener('livewire:init', initAllEditors);

        // Sync content from Livewire after save/load without forcing full page refresh.
        window.addEventListener('editor:set-content', (event) => {
            const nextContent = event.detail?.content ?? '';
            window.__dailyReportContentBuffer = nextContent;

            if (window.contentEditor && window.contentEditor.getData() !== nextContent) {
                window.contentEditor.setData(nextContent);
            }
        });

        // Polling-style check for tab changes (robust)
        setInterval(() => {
            if (document.querySelector('#content-editor') && !document.querySelector('#content-editor').classList.contains('ck-editor-initialized')) {
                initAllEditors();
            }
        }, 1000);

        // Listen for save success to potentially clear or sync
        window.addEventListener('swal:success', () => {
            // If we want to clear after save (though loadTodayReport handles it)
        });
    </script>
</div>
