<div class="daily-report-manager {{ ($activeTab === 'management' || $activeTab === 'history') ? 'w-100 px-2 px-md-3' : 'container-fluid px-2 px-md-3' }} pb-5"
    x-data="{ activeTab: @entangle('activeTab') }">
    <div class="row">
        <div class="{{ ($activeTab === 'management' || $activeTab === 'history') ? 'col-12' : 'col-lg-8 mx-auto' }}">
            <!-- Unified Tab Navigation -->
            <ul class="nav nav-pills mb-3 bg-white p-2 shadow-sm d-flex flex-wrap rounded-12px" >
                @if($canSubmitOwnReport)
                    <li class="nav-item">
                        <button wire:click="$set('activeTab', 'form')"
                            class="nav-link nav-pill-btn {{ $activeTab === 'form' ? 'active bg-dark' : 'text-muted' }} rounded-10px"
                            >
                            <i class="bi bi-pencil-square me-1 me-md-2"></i><span class="nav-pill-label">Gửi báo cáo</span>
                        </button>
                    </li>
                    <li class="nav-item ms-1 ms-md-2">
                        <button wire:click="$set('activeTab', 'history')"
                            class="nav-link nav-pill-btn {{ $activeTab === 'history' ? 'active bg-dark' : 'text-muted' }} rounded-10px"
                            >
                            <i class="bi bi-calendar3 me-1 me-md-2"></i><span class="nav-pill-label">Lịch sử cá nhân</span>
                        </button>
                    </li>
                @endif
                @if($isManager)
                    <li class="nav-item ms-1 ms-md-2">
                        <button wire:click="$set('activeTab', 'management')"
                            class="nav-link nav-pill-btn {{ $activeTab === 'management' ? 'active bg-dark' : 'text-muted' }} rounded-10px"
                            >
                            <i class="bi bi-speedometer2 me-1 me-md-2"></i><span class="nav-pill-label">Quản lý chung</span>
                        </button>
                    </li>
                @endif
            </ul>

            @if($canSubmitOwnReport && $activeTab === 'form')
                <!-- Daily Report Form -->
                <div class="card border-0 shadow-sm mb-4 rounded-12px overflow-hidden" >
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="mb-0 fw-bold">Báo cáo ngày của tôi</h5>
                            @if($isEditing)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill"
                                    >
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
                                class="form-control form-control-sm border-light-subtle w-auto rounded-2"
                                
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
                                    <select wire:model.live.debounce.500ms="status" class="form-select border-light-subtle rounded-8px"
                                        >
                                        <option value="Hoàn thành đúng kế hoạch">Hoàn thành đúng kế hoạch</option>
                                        <option value="Hoàn thành một phần">Hoàn thành một phần</option>
                                        <option value="Gặp vấn đề, cần hỗ trợ">Gặp vấn đề, cần hỗ trợ</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label  fw-bold text-muted">Kế hoạch ngày mai <span
                                            class="fw-normal text-muted">(không bắt buộc)</span></label>
                                    <textarea wire:model.live.debounce.500ms="plan" class="form-control border-light-subtle rounded-8px"
                                        rows="5" 
                                        placeholder="Sẽ làm gì tiếp..."></textarea>
                                    @error('plan') <span class="text-danger ">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Formatting Tips -->
                            <div class="daily-report-tip-box mt-4 p-3 border-0 bg-light-subtle rounded-3 border border-dashed"
                                >
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
                                <textarea wire:model.live.debounce.500ms="issues" class="form-control border-light-subtle rounded-8px"
                                    rows="3" 
                                    placeholder="Nếu có vấn đề cần TPKD biết hoặc hỗ trợ, ghi ở đây..."></textarea>
                            </div>

                            <div class="d-flex align-items-center gap-3 mt-4">
                                <button type="submit" class="btn btn-dark px-4 py-2 rounded-8px" >
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

                <div class="daily-report-help-alert alert alert-warning border-0 shadow-sm py-3 bg-peach rounded-3"
                    >
                    <p class="mb-0  text-body">
                        <i class="bi bi-info-circle me-1"></i> Nếu chọn <strong>"Gặp vấn đề, cần hỗ trợ"</strong> &rarr;
                        TPKD nhận thông báo ngay sau khi bạn gửi.
                    </p>
                </div>

            @elseif($canSubmitOwnReport && $activeTab === 'history')
                <!-- Employee History Header -->
                <div class="card border-0 shadow-sm mb-4 rounded-12px overflow-hidden" >
                    <div
                        class="card-header daily-report-history-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="daily-report-history-main d-flex align-items-center gap-3">
                            <h5 class="daily-report-history-title mb-0 fw-bold">Lịch sử báo cáo tháng {{ $monthFilter }}/{{ $yearFilter }}</h5>
                            <div class="daily-report-history-filters d-flex align-items-center gap-2">
                                <select wire:model.live="monthFilter" class="daily-report-history-select form-select form-select-sm border-light-subtle rounded-2"
                                    >
                                    @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">Tháng {{ $m }}</option> @endfor
                                </select>
                                <select wire:model.live="yearFilter" class="daily-report-history-select form-select form-select-sm border-light-subtle rounded-2"
                                    >
                                    @for($y = date('Y') - 1; $y <= date('Y'); $y++)
                                    <option value="{{ $y }}">{{ $y }}</option> @endfor
                                </select>
                            </div>
                        </div>
                        <div class="daily-report-history-summary">
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
                        class="card-header daily-report-management-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-4">
                        <div class="daily-report-management-controls d-flex align-items-center gap-3 flex-wrap">
                            <div class="daily-report-view-switch p-1 bg-light rounded-pill d-inline-flex border border-light-subtle">
                                <button type="button" wire:click="$set('viewType', 'day')"
                                    class="btn rounded-pill px-4 py-2 {{ $viewType === 'day' ? 'bg-white shadow-sm fw-bold text-primary' : 'border-0 text-muted fw-semibold' }} fs-095" >
                                    Xem theo ngày
                                </button>
                                <button type="button" wire:click="$set('viewType', 'month')"
                                    class="btn rounded-pill px-4 py-2 {{ $viewType === 'month' ? 'bg-white shadow-sm fw-bold text-primary' : 'border-0 text-muted fw-semibold' }} fs-095" >
                                    Xem theo tháng
                                </button>
                            </div>

                            <div class="vr mx-1 d-none d-md-block h-24px" ></div>

                            @if($viewType === 'day')
                                <div class="daily-report-filter-group d-flex align-items-center gap-2">
                                    <span class="daily-report-filter-label text-muted fw-bold">Ngày:</span>
                                    <input type="date" wire:model.live="dateFilter"
                                        class="daily-report-filter-control form-control form-control-sm border-light-subtle badge-auto"
                                        >
                                </div>
                            @else
                                <div class="daily-report-filter-group d-flex align-items-center gap-2">
                                    <span class="daily-report-filter-label text-muted fw-bold">Kỳ báo cáo:</span>
                                    <select wire:model.live="monthFilter" class="daily-report-filter-control form-select form-select-sm border-light-subtle badge-auto"
                                        >
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ sprintf('%02d', $m) }}">Tháng {{ $m }}</option>
                                        @endfor
                                    </select>
                                    <select wire:model.live="yearFilter" class="daily-report-filter-control form-select form-select-sm border-light-subtle badge-auto"
                                        >
                                        @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            @endif

                            <div class="daily-report-filter-group daily-report-people-filters d-flex align-items-center gap-2">
                                <span class="daily-report-filter-label text-muted fw-bold">Bộ lọc:</span>
                                <select wire:model.live="deptIdFilter"
                                    class="daily-report-filter-control form-select form-select-sm border-light-subtle badge-auto"
                                    >
                                    <option value="">Tất cả phòng ban</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>

                                <select wire:model.live="userIdFilter"
                                    class="daily-report-filter-control form-select form-select-sm border-light-subtle badge-auto"
                                    >
                                    <option value="">Tất cả nhân viên</option>
                                    @foreach($users as $user)
                                        @if(!$deptIdFilter || $user->department_id == $deptIdFilter)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="daily-report-management-actions d-flex align-items-center gap-3">
                            <div class="daily-report-stat-badges d-flex gap-2 align-items-center">
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
                                    @if(($reportStats['late'] ?? 0) > 0)
                                        <span
                                            class="badge bg-soft-danger text-danger px-3 py-2 rounded-pill fw-normal">{{ $reportStats['late'] }}
                                            nộp trễ</span>
                                    @endif
                                @else
                                    <span
                                        class="badge bg-soft-info text-info px-3 py-2 rounded-pill fw-normal">{{ $reportStats['total'] }}
                                        báo cáo tổng cộng</span>
                                @endif
                            </div>
                            <button wire:click="export" wire:loading.attr="disabled"
                                class="btn btn-success px-4 py-2 shadow-sm d-flex align-items-center gap-2 fw-semibold rounded-2 fs-095"
                                >
                                <i class="bi bi-file-earmark-excel"></i> Xuất dữ liệu
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @if($viewType === 'day')
                                @php
                                    $daysDiff = (int) \Carbon\Carbon::parse($dateFilter)->startOfDay()
                                        ->diffInDays(now()->startOfDay(), false);
                                    // $daysDiff > 0 = ngày trong quá khứ
                                @endphp
                                @foreach($reports as $item)
                                    @if($item->report)
                                            @php
                                                $submittedLateDays = $item->report->created_at
                                                    ? (int) $item->report->date->copy()->startOfDay()
                                                        ->diffInDays($item->report->created_at->copy()->startOfDay(), false)
                                                    : 0;
                                            @endphp
                                            <!-- Reported -->
                                            <div class="list-group-item p-4 border-light-subtle @if($item->report->status === 'Gặp vấn đề, cần hỗ trợ') bg-soft-danger-light @endif"
                                                x-data="{ expanded: false }">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="rounded-pill bg-light d-flex align-items-center justify-content-center fw-bold text-muted  wh-38"
                                                            >
                                                            {{ strtoupper(substr($item->user->name, 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-bold text-danger">{{ $item->user->name }} <span
                                                                    class="badge bg-soft-success text-success fw-normal rounded-pill ms-2 ">{{ $item->user->department?->name ?? 'Nhân viên' }}</span>
                                                            </h6>
                                                            <div class="d-flex flex-wrap gap-2 mt-1">
                                                                <small class="text-muted">
                                                                    Gửi lúc {{ $item->report->created_at?->format('d/m/Y H:i') }}
                                                                </small>
                                                                @if($submittedLateDays > 0)
                                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-semibold">
                                                                        Nộp trễ {{ $submittedLateDays }} ngày
                                                                    </span>
                                                                @endif
                                                            </div>
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
                                        @php
                                            if ($daysDiff >= 4) {
                                                $lateItemClass   = 'border-danger-subtle';
                                                $lateItemStyle   = 'background: rgba(220,53,69,0.05);';
                                                $lateAvatarClass = 'bg-danger bg-opacity-10 text-danger fw-bold';
                                                $lateNameClass   = 'text-danger fw-semibold';
                                                $lateBadgeClass  = 'text-danger fw-bold';
                                                $lateBadgeText   = "Chậm {$daysDiff} ngày";
                                            } elseif ($daysDiff >= 1) {
                                                $lateItemClass   = 'border-warning-subtle';
                                                $lateItemStyle   = 'background: rgba(255,193,7,0.07);';
                                                $lateAvatarClass = 'bg-warning bg-opacity-10 text-warning fw-bold';
                                                $lateNameClass   = 'text-warning-emphasis fw-semibold';
                                                $lateBadgeClass  = 'text-warning fw-bold';
                                                $lateBadgeText   = "Chậm {$daysDiff} ngày";
                                            } else {
                                                $lateItemClass   = 'border-light-subtle border-dashed';
                                                $lateItemStyle   = '';
                                                $lateAvatarClass = 'bg-light text-muted fw-bold';
                                                $lateNameClass   = 'text-muted opacity-75 fw-normal';
                                                $lateBadgeClass  = 'text-warning fw-bold';
                                                $lateBadgeText   = 'Chưa báo cáo';
                                            }
                                        @endphp
                                        <div class="list-group-item p-4 {{ $lateItemClass }}"
                                            style="{{ $lateItemStyle }}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded-pill d-flex align-items-center justify-content-center {{ $lateAvatarClass }} wh-38"
                                                        >
                                                        {{ strtoupper(substr($item->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 {{ $lateNameClass }}">
                                                            {{ $item->user->name }}
                                                            <span class="fw-normal opacity-75 ms-1">&mdash; chưa gửi báo cáo</span>
                                                        </h6>
                                                    </div>
                                                </div>
                                                <span class="{{ $lateBadgeClass }}">{{ $lateBadgeText }}</span>
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
        @php
            $monthStart = \Carbon\Carbon::create($yearFilter, $monthFilter, 1);
            $mobileReportDays = collect(range(1, $monthStart->daysInMonth))
                ->map(function ($day) use ($monthStart, $calendarData) {
                    $date = $monthStart->copy()->day($day);
                    $reportsForDay = collect($calendarData[$day] ?? []);

                    return [
                        'date' => $date,
                        'reports' => $reportsForDay,
                    ];
                })
                ->filter(fn ($day) => $day['reports']->isNotEmpty())
                ->values();
        @endphp

        <div class="daily-report-mobile-agenda d-md-none">
            <div class="daily-report-mobile-agenda-summary">
                <div>
                    <div class="daily-report-mobile-agenda-title">Tháng {{ (int) $monthFilter }}/{{ $yearFilter }}</div>
                    <div class="text-muted small">{{ $mobileReportDays->count() }} ngày có báo cáo</div>
                </div>
                <span class="badge bg-soft-info text-info rounded-pill">{{ $reportStats['total'] }} báo cáo</span>
            </div>

            @forelse($mobileReportDays as $day)
                @php
                    $dayDate = $day['date'];
                    $dayReports = $day['reports'];
                    $issueCountForDay = $dayReports->where('status', 'Gặp vấn đề, cần hỗ trợ')->count();
                    $lateCountForDay = $dayReports->filter(function ($report) {
                        return $report->created_at
                            && $report->created_at->copy()->startOfDay()->gt($report->date->copy()->startOfDay());
                    })->count();
                    $payload = $dayReports->map(function ($r) {
                        $lateDays = $r->created_at
                            ? (int) $r->date->copy()->startOfDay()->diffInDays($r->created_at->copy()->startOfDay(), false)
                            : 0;

                        return [
                            'id' => $r->id,
                            'user_id' => $r->user_id,
                            'date' => $r->date->format('Y-m-d'),
                            'name' => $r->user->name ?? '',
                            'department' => $r->user->department->name ?? '',
                            'status' => $r->status,
                            'content' => $r->content,
                            'plan' => $r->plan ?? '',
                            'issues' => $r->issues ?? '',
                            'submitted_at' => $r->created_at?->format('d/m/Y H:i'),
                            'late_days' => max(0, $lateDays),
                        ];
                    })->toJson();
                @endphp

                <button type="button"
                    class="daily-report-mobile-day"
                    @click="$dispatch('open-day-detail', { date: '{{ $dayDate->format('d/m/Y') }}', reports: {{ $payload }} })">
                    <div class="daily-report-mobile-date">
                        <span class="daily-report-mobile-weekday">{{ Str::upper($dayDate->isoFormat('dd')) }}</span>
                        <span class="daily-report-mobile-daynum">{{ $dayDate->format('d') }}</span>
                    </div>
                    <div class="daily-report-mobile-day-body">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <span class="fw-semibold text-body">{{ $dayReports->count() }} báo cáo</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                        <div class="daily-report-mobile-chip-row">
                            @if($issueCountForDay > 0)
                                <span class="badge bg-danger-subtle text-danger">{{ $issueCountForDay }} cần hỗ trợ</span>
                            @endif
                            @if($lateCountForDay > 0)
                                <span class="badge bg-danger-subtle text-danger">{{ $lateCountForDay }} nộp trễ</span>
                            @endif
                            @if($issueCountForDay === 0 && $lateCountForDay === 0)
                                <span class="badge bg-success-subtle text-success">Ổn định</span>
                            @endif
                        </div>
                        <div class="daily-report-mobile-names text-muted">
                            {{ $dayReports->pluck('user.name')->filter()->take(3)->join(', ') }}{{ $dayReports->count() > 3 ? '...' : '' }}
                        </div>
                    </div>
                </button>
            @empty
                <div class="daily-report-mobile-empty">
                    <i class="bi bi-calendar2-x"></i>
                    <span>Chưa có báo cáo trong tháng này.</span>
                </div>
            @endforelse
        </div>

        <div class="calendar-container daily-report-desktop-calendar shadow-sm bg-white rounded-3 overflow-hidden border"
            >
            <div class="calendar-header-grid bg-light border-bottom border-light-subtle rounded-top-3" >
                @php $daysOfWeek = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN']; @endphp
                @foreach($daysOfWeek as $dow)
                    <div class="calendar-header-cell fw-bold text-secondary text-center py-3 text-uppercase fs-80 letter-05" >{{ $dow }}</div>
                @endforeach
            </div>

            <div class="calendar-body-grid">
                @php
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
                        $isWeekend = $currentDate->isSunday();
                        $isComplete = $dayReports->isNotEmpty();
                        $hasIssue = $dayReports->where('status', 'Gặp vấn đề, cần hỗ trợ')->isNotEmpty();

                        $dotColor = 'secondary';
                        if ($isInsideMonth) {
                            if ($isComplete) {
                                $dotColor = $hasIssue ? 'danger' : 'success';
                            } else {
                                if ($isWeekend)
                                    $dotColor = 'secondary opacity-25';
                                elseif ($isPast)
                                    $dotColor = 'danger';
                                else
                                    $dotColor = 'warning';
                            }
                        }
                    @endphp

                    <div class="calendar-day-cell position-relative
                        @if(!$isInsideMonth) bg-light opacity-50
                        @elseif($isWeekend) bg-light bg-opacity-50
                        @else bg-white
                        @endif
                        border-start border-bottom border-light-subtle
                        @if($isInsideMonth && $dayReports->isNotEmpty()) cursor-pointer @endif"
                        style="min-height: 180px; transition: background 0.2s; padding: clamp(6px, 1.5vw, 12px); min-width: 0; overflow: hidden;"
                        @if($isInsideMonth && $dayReports->isNotEmpty())
                            @click="$dispatch('open-day-detail', { date: '{{ $currentDate->format('d/m/Y') }}', reports: {{ $dayReports->map(function ($r) {
                                $lateDays = $r->created_at
                                    ? (int) $r->date->copy()->startOfDay()->diffInDays($r->created_at->copy()->startOfDay(), false)
                                    : 0;

                                return [
                                    'id' => $r->id,
                                    'user_id' => $r->user_id,
                                    'date' => $r->date->format('Y-m-d'),
                                    'name' => $r->user->name ?? '',
                                    'department' => $r->user->department->name ?? '',
                                    'status' => $r->status,
                                    'content' => $r->content,
                                    'plan' => $r->plan ?? '',
                                    'issues' => $r->issues ?? '',
                                    'submitted_at' => $r->created_at?->format('d/m/Y H:i'),
                                    'late_days' => max(0, $lateDays),
                                ];
                            })->toJson() }} })"
                        @endif
                    >
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span
                                class="fw-bold {{ $isToday ? 'bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center' : 'text-secondary opacity-75' }}"
                                style="width: clamp(28px, 2.5vw, 32px); height: clamp(28px, 2.5vw, 32px); font-size: clamp(0.85rem, 1vw, 1.1rem); {{ $isToday ? 'box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);' : '' }}">
                                {{ $dayNum }}
                            </span>
                            @if($isInsideMonth)
                                <div class="d-flex align-items-center gap-1">
                                    <span class="status-indicator bg-{{ $dotColor }} shadow-sm"></span>
                                </div>
                            @endif
                        </div>

                        <div class="calendar-day-content mt-1">
                            @if($isInsideMonth && $dayReports->isNotEmpty())
                                @foreach($dayReports as $dr)
                                    @php
                                        $calendarLateDays = $dr->created_at
                                            ? (int) $dr->date->copy()->startOfDay()->diffInDays($dr->created_at->copy()->startOfDay(), false)
                                            : 0;
                                    @endphp
                                    <div class="mb-1 px-2 py-1 rounded cal-report-chip {{ $calendarLateDays > 0 ? 'cal-chip-late' : 'cal-chip-' . ($dr->status === 'Gặp vấn đề, cần hỗ trợ' ? 'issue' : ($dr->status === 'Hoàn thành một phần' ? 'partial' : 'done')) }} fs-clamp-sm cursor-pointer mxw-100 overflow-hidden" >
                                        <div class="d-flex align-items-center gap-1 mxw-100" >
                                            <span class="fw-bold text-truncate">{{ $dr->user->name ?? '' }}</span>
                                            @if($calendarLateDays > 0)
                                                <span class="badge bg-danger-subtle text-danger px-1 py-0 fs-60" >Trễ</span>
                                            @endif
                                        </div>
                                        <div class="riched-content-mini text-truncate-2 fs-clamp-xs opacity-75" >{!! Str::limit(strip_tags($dr->content), 80) !!}</div>
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
         class="fixed-overlay-9999"
         @keydown.escape.window="open = false">
        <div class="modal-overlay-dark" @click="open = false"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 700px; max-height: 80vh; overflow-y: auto; background: var(--daily-report-modal-bg, #fff); border-radius: 16px; box-shadow: 0 25px 50px rgba(0,0,0,0.15);"
             @click.stop>
            <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2"></i> Báo cáo ngày <span x-text="date"></span></h5>
                <button @click="open = false" class="btn-close"></button>
            </div>
            <div class="p-4">
                <template x-for="(r, idx) in reports" :key="idx">
                    <div
                        class="daily-report-modal-item mb-4 p-3 rounded-3 border r.status === 'Gặp vấn đề, cần hỗ trợ' ? 'status-issue' : (r.status === 'Hoàn thành một phần' ? 'status-partial' : 'status-done')"
                        :
                        :style="'background-color:' + (r.status === 'Gặp vấn đề, cần hỗ trợ' ? '#fff5f5' : (r.status === 'Hoàn thành một phần' ? '#fffbeb' : '#f0fdf4'))">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="fw-bold text-body" x-text="r.name"></span>
                                <span class="daily-report-dept-badge badge ms-2 fw-normal " x-text="r.department"></span>
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    <small class="text-muted">Gửi lúc <span x-text="r.submitted_at"></span></small>
                                    <template x-if="r.late_days > 0">
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-semibold">
                                            Nộp trễ <span x-text="r.late_days"></span> ngày
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <span class=" fw-bold r.status === 'Gặp vấn đề, cần hỗ trợ' ? 'text-danger' : (r.status === 'Hoàn thành một phần' ? 'text-warning' : 'text-success')" : x-text="r.status"></span>
                                <template x-if="r.user_id === {{ auth()->id() }}">
                                    <div class="d-flex gap-1 ms-2">
                                        <button @click="$wire.openReportModal(r.date); open = false"
                                            class="btn btn-sm btn-outline-primary py-0 px-2 fs-75 rounded-2"  title="Chỉnh sửa">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button @click="if(confirm('Bạn chắc chắn muốn xóa báo cáo này?')) { $wire.deleteReport(r.id); open = false }"
                                            class="btn btn-sm btn-outline-danger py-0 px-2 fs-75 rounded-2"  title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="riched-content  text-body text-break"  x-html="r.content"></div>
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

    <!-- Quick Report Modal (Add/Edit) -->
    <div x-data="{ open: @entangle('showReportModal') }"
         x-show="open" x-cloak
         class="fixed-overlay-10000"
         @keydown.escape.window="open = false">
        <div class="modal-overlay-blur" @click="open = false"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 95%; max-width: 600px; background: var(--daily-report-modal-bg, #fff); border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); overflow: hidden;"
             @click.stop>
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-pencil-square me-2 text-primary"></i>
                    {{ $isEditing ? 'Cập nhật báo cáo' : 'Gửi báo cáo mới' }}
                </h5>
                <button @click="open = false" class="btn-close shadow-none"></button>
            </div>

            <div class="p-4 mxh-70vh overflow-y-auto" >
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold text-muted mb-0">Ngày báo cáo</label>
                        <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill">{{ \Carbon\Carbon::parse($reportDate)->format('d/m/Y') }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted">Nội dung công việc *</label>
                    <div wire:ignore class="editor-container">
                        <div id="modal-content-editor">{!! $content !!}</div>
                    </div>
                    @error('content') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label fw-bold text-muted">Trạng thái hoàn thành</label>
                        <select wire:model="status" class="form-select border-light-subtle rounded-12px" >
                            <option value="Hoàn thành đúng kế hoạch">Hoàn thành đúng kế hoạch</option>
                            <option value="Hoàn thành một phần">Hoàn thành một phần</option>
                            <option value="Gặp vấn đề, cần hỗ trợ">Gặp vấn đề, cần hỗ trợ</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted">Kế hoạch ngày mai</label>
                    <textarea wire:model="plan" class="form-control border-light-subtle rounded-3 fs-095" rows="3"
                        
                        placeholder="Dự định công việc cho ngày mai..."></textarea>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-bold text-muted">Vấn đề / Hỗ trợ</label>
                    <textarea wire:model="issues" class="form-control border-light-subtle rounded-3 fs-095" rows="2"
                        
                        placeholder="Nếu có khó khăn cần giúp đỡ..."></textarea>
                </div>
            </div>

            <div class="p-4 bg-light-subtle border-top d-flex gap-2">
                <button @click="open = false" class="btn btn-light px-4 py-2 flex-grow-1 rounded-10px" >Hủy</button>
                <button wire:click="save" class="btn btn-dark px-4 py-2 flex-grow-1 rounded-10px"  wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="bi bi-check2-circle me-1"></i> Lưu báo cáo</span>
                    <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span> Đang lưu...</span>
                </button>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/daily-report.css') }}">
    @endpush

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

            // Modal Content Editor
            const modalContentEl = document.querySelector('#modal-content-editor');
            if (modalContentEl && !modalContentEl.classList.contains('ck-editor-initialized')) {
                ClassicEditor.create(modalContentEl, {
                    placeholder: 'Hôm nay bạn đã hoàn thành những việc gì?',
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

                    modalContentEl.classList.add('ck-editor-initialized');
                    window.modalContentEditor = editor;
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
            if (window.modalContentEditor && window.modalContentEditor.getData() !== nextContent) {
                window.modalContentEditor.setData(nextContent);
            }
        });

        // Polling-style check for tab changes (robust)
        setInterval(() => {
            if (document.querySelector('#content-editor') && !document.querySelector('#content-editor').classList.contains('ck-editor-initialized')) {
                initAllEditors();
            }
            if (document.querySelector('#modal-content-editor') && !document.querySelector('#modal-content-editor').classList.contains('ck-editor-initialized')) {
                initAllEditors();
            }
        }, 1000);

        // Listen for save success to potentially clear or sync
        window.addEventListener('swal:success', () => {
            // If we want to clear after save (though loadTodayReport handles it)
        });
    </script>
</div>
