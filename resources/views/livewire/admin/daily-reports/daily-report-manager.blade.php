<div class="daily-report-manager {{ in_array($activeTab, ['management', 'history', 'support'], true) ? 'w-100 px-2 px-md-3' : 'container-fluid px-2 px-md-3' }} pb-5"
    x-data="{ activeTab: @entangle('activeTab') }">
    <div class="row">
        <div class="{{ in_array($activeTab, ['management', 'history', 'support'], true) ? 'col-12' : 'col-lg-8 mx-auto' }}">
            <!-- Unified Tab Navigation -->
            <ul class="nav nav-pills mb-3 bg-body p-2 border border-light-subtle shadow-sm d-flex flex-wrap rounded-3">
                @if ($canSubmitOwnReport)
                    <li class="nav-item">
                        <button wire:click="$set('activeTab', 'form')"
                            class="nav-link nav-pill-btn {{ $activeTab === 'form' ? 'active bg-dark' : 'text-muted' }} rounded-3">
                            <i class="fa-solid fa-pen-to-square me-2"></i><span class="nav-pill-label fw-semibold">Gửi báo cáo</span>
                        </button>
                    </li>
                    <li class="nav-item ms-1 ms-md-2">
                        <button wire:click="$set('activeTab', 'history')"
                            class="nav-link nav-pill-btn {{ $activeTab === 'history' ? 'active bg-dark' : 'text-muted' }} rounded-3">
                            <i class="fa-solid fa-calendar-days me-2"></i><span class="nav-pill-label fw-semibold">Lịch sử cá nhân</span>
                        </button>
                    </li>
                @endif
                @if ($isManager)
                    <li class="nav-item ms-1 ms-md-2">
                        <button wire:click="$set('activeTab', 'management')"
                            class="nav-link nav-pill-btn {{ $activeTab === 'management' ? 'active bg-dark' : 'text-muted' }} rounded-3">
                            <i class="fa-solid fa-chart-line me-2"></i><span class="nav-pill-label fw-semibold">Quản lý chung</span>
                        </button>
                    </li>
                    <li class="nav-item ms-1 ms-md-2">
                        <button wire:click="$set('activeTab', 'support')"
                            class="nav-link nav-pill-btn {{ $activeTab === 'support' ? 'active bg-dark' : 'text-muted' }} rounded-3">
                            <i class="fa-solid fa-life-ring me-2"></i><span class="nav-pill-label fw-semibold">Hỗ trợ</span>
                            @if (($supportStats['pending'] ?? 0) + ($supportStats['in_progress'] ?? 0) > 0)
                                <span class="badge bg-danger text-white rounded-pill ms-2">
                                    {{ ($supportStats['pending'] ?? 0) + ($supportStats['in_progress'] ?? 0) }}
                                </span>
                            @endif
                        </button>
                    </li>
                @endif
            </ul>

            @if ($canSubmitOwnReport && $activeTab === 'form')
                <!-- Daily Report Form -->
                <div class="card border border-light-subtle shadow-sm mb-4 rounded-3 overflow-hidden bg-body">
                    <div class="card-header bg-body-tertiary border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="mb-0 fw-bold text-body">Báo cáo ngày của tôi</h5>
                            @if ($isEditing)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold">
                                    <i class="fa-solid fa-circle-check me-1"></i> Đã gửi
                                </span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small fw-semibold">Ngày báo cáo:</span>
                            <input type="date" wire:model.live="reportDate" max="{{ now()->format('Y-m-d') }}"
                                class="form-control form-control-sm border-light-subtle rounded-8px py-1.5 px-3 fs-90 shadow-sm w-auto">
                        </div>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <form wire:submit.prevent="save">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary mb-2">Hôm nay bạn đã làm được những gì? *</label>
                                <div wire:ignore wire:key="daily-report-main-editor"
                                    class="editor-container daily-report-main-editor rounded-12px overflow-hidden border border-light-subtle shadow-sm">
                                    <div id="content-editor">{!! $content !!}</div>
                                </div>
                                @error('content')
                                    <div class="text-danger small mt-2 fw-semibold">
                                        <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-md-6 d-flex flex-column gap-3">
                                    <div>
                                        <label class="form-label fw-bold text-secondary mb-2">Kết quả tổng thể hôm nay</label>
                                        <select wire:model.live.debounce.500ms="status" class="form-select border-light-subtle rounded-8px py-2 px-3 shadow-sm">
                                            <option value="Hoàn thành đúng kế hoạch">🟢 Hoàn thành đúng kế hoạch</option>
                                            <option value="Hoàn thành một phần">🟡 Hoàn thành một phần</option>
                                            <option value="Gặp vấn đề, cần hỗ trợ">🔴 Gặp vấn đề, cần hỗ trợ</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label fw-bold text-secondary mb-2">Vấn đề / Khó khăn / Đề xuất hỗ trợ <span class="fw-normal text-muted">(không bắt buộc)</span></label>
                                        <textarea wire:model.live.debounce.500ms="issues" class="form-control border-light-subtle rounded-8px py-2 px-3 shadow-sm" rows="3"
                                            placeholder="Nếu có khó khăn cần cấp trên biết hoặc hỗ trợ, ghi tại đây..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div>
                                        <label class="form-label fw-bold text-secondary mb-2">Kế hoạch ngày mai <span class="fw-normal text-muted">(không bắt buộc)</span></label>
                                        <textarea wire:model.live.debounce.500ms="plan" class="form-control border-light-subtle rounded-8px py-2 px-3 shadow-sm" rows="6"
                                            placeholder="Bạn dự kiến sẽ thực hiện công việc gì vào ngày mai..."></textarea>
                                        @error('plan')
                                            <div class="text-danger small mt-2 fw-semibold">
                                                <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Formatting Tips -->
                            <div class="p-3 bg-light bg-opacity-50 rounded-12px border border-light-subtle mb-4">
                                <div class="d-flex gap-3">
                                    <span class="text-warning mt-1 fs-5"><i class="fa-regular fa-lightbulb"></i></span>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-body small">Mẹo viết báo cáo nhanh & chuyên nghiệp:</h6>
                                        <ul class="mb-0 text-muted ps-3 small">
                                            <li>Sử dụng các **Dấu đầu dòng (Bullet points)** để liệt kê đầu mục công việc cụ thể.</li>
                                            <li>**In đậm (Bold)** những kết quả quan trọng hoặc tên khách hàng/dự án nổi bật.</li>
                                            <li>Nêu rõ các nút thắt kỹ thuật hoặc phát sinh ngoài ý muốn để nhận phản hồi hỗ trợ.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Warning Alert -->
                            <div class="alert alert-warning border-0 shadow-sm py-3 rounded-12px bg-warning bg-opacity-10 text-warning-emphasis fw-medium mb-4">
                                <p class="mb-0">
                                    <i class="fa-solid fa-circle-info me-2 text-warning"></i> Nếu chọn trạng thái <strong>"Gặp vấn đề, cần hỗ trợ"</strong>, hệ thống sẽ tự động gửi thông báo trực tiếp đến Trưởng phòng và Ban giám đốc.
                                </p>
                            </div>

                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mt-4 border-top pt-4">
                                <div class="d-flex align-items-center gap-3">
                                    <button type="submit" class="btn btn-dark px-4 py-2.5 rounded-8px d-inline-flex align-items-center gap-2 shadow-sm">
                                        <i class="fa-solid {{ $isEditing ? 'fa-square-check' : 'fa-paper-plane' }}"></i>
                                        <span class="fw-semibold">{{ $isEditing ? 'Cập Nhật Báo Cáo' : 'Gửi Báo Cáo Ngày' }}</span>
                                    </button>
                                    @if ($isEditing)
                                        <span class="text-success fw-semibold small">
                                            <i class="fa-solid fa-circle-info me-1"></i> Bạn đang chỉnh sửa báo cáo ngày {{ \Carbon\Carbon::parse($reportDate)->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted small">Nội dung sẽ tự động lưu nháp khi bạn nhập thông tin</span>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @elseif($canSubmitOwnReport && $activeTab === 'history')
                <!-- Employee Personal History View -->
                <div class="card border-0 shadow-sm mb-4 rounded-12px overflow-hidden">
                    <div class="card-header daily-report-history-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="daily-report-history-main d-flex align-items-center gap-3">
                            <h5 class="daily-report-history-title mb-0 fw-bold">Lịch sử báo cáo tháng {{ $monthFilter }}/{{ $yearFilter }}</h5>
                            <div class="daily-report-history-filters d-flex align-items-center gap-2">
                                <select wire:model.live="monthFilter" class="daily-report-history-select form-select form-select-sm border-light-subtle rounded-2">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">Tháng {{ $m }}</option>
                                    @endfor
                                </select>
                                <select wire:model.live="yearFilter" class="daily-report-history-select form-select form-select-sm border-light-subtle rounded-2">
                                    @for ($y = date('Y') - 1; $y <= date('Y'); $y++)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="daily-report-history-summary">
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold">Đã gửi {{ $reportStats['total'] }} báo cáo</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($activeTab === 'management' && $isManager)
        <!-- MANAGER VIEW -->
        <div class="row g-0">
            <div class="col-12 px-3">
                <div class="{{ $viewType === 'month' ? 'border-0 border-bottom' : 'card border border-light-subtle shadow-sm mb-4 rounded-3 overflow-hidden bg-body' }}">
                    <div class="card-header daily-report-management-header bg-body-tertiary border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-4">
                        <div class="daily-report-management-controls d-flex align-items-center gap-3 flex-wrap">
                            <div class="daily-report-view-switch p-1 bg-body-tertiary rounded-pill d-inline-flex border border-light-subtle">
                                <button type="button" wire:click="$set('viewType', 'day')"
                                    class="btn rounded-pill px-4 py-2 {{ $viewType === 'day' ? 'bg-body shadow-sm fw-bold text-primary' : 'border-0 text-muted fw-semibold' }} fs-095">
                                    Xem theo ngày
                                </button>
                                <button type="button" wire:click="$set('viewType', 'month')"
                                    class="btn rounded-pill px-4 py-2 {{ $viewType === 'month' ? 'bg-body shadow-sm fw-bold text-primary' : 'border-0 text-muted fw-semibold' }} fs-095">
                                    Xem theo tháng
                                </button>
                            </div>

                            <div class="vr mx-1 d-none d-md-block h-24px"></div>

                            @if ($viewType === 'day')
                                <div class="daily-report-filter-group d-flex align-items-center gap-2">
                                    <span class="daily-report-filter-label text-muted fw-bold">Ngày:</span>
                                    <input type="date" wire:model.live="dateFilter"
                                        class="daily-report-filter-control form-control form-control-sm border-light-subtle badge-auto">
                                </div>
                            @else
                                <div class="daily-report-filter-group d-flex align-items-center gap-2">
                                    <span class="daily-report-filter-label text-muted fw-bold">Kỳ báo cáo:</span>
                                    <select wire:model.live="monthFilter"
                                        class="daily-report-filter-control form-select form-select-sm border-light-subtle badge-auto">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ sprintf('%02d', $m) }}">Tháng {{ $m }}</option>
                                        @endfor
                                    </select>
                                    <select wire:model.live="yearFilter"
                                        class="daily-report-filter-control form-select form-select-sm border-light-subtle badge-auto">
                                        @for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            @endif

                            <div class="daily-report-filter-group daily-report-people-filters d-flex align-items-center gap-2">
                                <span class="daily-report-filter-label text-muted fw-bold">Bộ lọc:</span>
                                <select wire:model.live="deptIdFilter"
                                    class="daily-report-filter-control form-select form-select-sm border-light-subtle badge-auto">
                                    <option value="">Tất cả phòng ban</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>

                                <select wire:model.live="userIdFilter"
                                    class="daily-report-filter-control form-select form-select-sm border-light-subtle badge-auto">
                                    <option value="">Tất cả nhân viên</option>
                                    @foreach ($users as $user)
                                        @if (!$deptIdFilter || $user->department_id == $deptIdFilter)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="daily-report-management-actions d-flex align-items-center gap-3">
                            <div class="daily-report-stat-badges d-flex gap-2 align-items-center">
                                @if ($viewType === 'day')
                                    @if ($reportStats['issues'] > 0)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-semibold">{{ $reportStats['issues'] }} cần hỗ trợ</span>
                                    @endif
                                    @if ($reportStats['missing'] > 0)
                                        <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fw-semibold">{{ $reportStats['missing'] }} chưa báo cáo</span>
                                    @endif
                                    @if (($reportStats['late'] ?? 0) > 0)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-semibold">{{ $reportStats['late'] }} nộp trễ</span>
                                    @endif
                                @else
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">{{ $reportStats['total'] }} báo cáo tổng cộng</span>
                                @endif
                            </div>
                            <button wire:click="export" wire:loading.attr="disabled"
                                class="btn btn-success px-4 py-2 shadow-sm d-flex align-items-center gap-2 fw-semibold rounded-2 fs-095">
                                <i class="fa-solid fa-file-excel"></i> Xuất dữ liệu
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-3 p-lg-4 bg-body-tertiary">
                        <div class="d-flex flex-column gap-3">
                            @if ($viewType === 'day')
                                @foreach ($reports as $item)
                                    @if ($item->report)
                                        <!-- Reported -->
                                        @php
                                            $hasLongContent = \Illuminate\Support\Str::length(strip_tags($item->report->content)) > 220;
                                        @endphp
                                        <div class="daily-report-entry card border border-light-subtle shadow-sm rounded-3 p-3 p-lg-4 bg-body transition-all {{ $item->report->status === 'Gặp vấn đề, cần hỗ trợ' ? 'border-danger-subtle bg-danger-subtle bg-opacity-25' : '' }}"
                                            x-data="{ expanded: false }">
                                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center wh-38" style="width: 38px; height: 38px;">
                                                        {{ strtoupper(substr($item->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                                            {{ $item->user->name }}
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal rounded-pill fs-75">{{ $item->user->department?->name ?? 'Nhân viên' }}</span>
                                                        </h6>
                                                        <div class="d-flex flex-wrap gap-2 mt-1">
                                                            <small class="text-muted">Gửi lúc {{ $item->report->created_at?->format('d/m/Y H:i') }}</small>
                                                            @if ($this->reportLateDays($item->report) > 0)
                                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle fw-semibold fs-75">Nộp trễ {{ $this->reportLateDays($item->report) }} ngày</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    @php
                                                        $statusClass = match($item->report->status) {
                                                            'Gặp vấn đề, cần hỗ trợ' => 'bg-danger bg-opacity-10 text-danger border border-danger-subtle',
                                                            'Hoàn thành một phần'     => 'bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle',
                                                            default                   => 'bg-success bg-opacity-10 text-success border border-success-subtle',
                                                        };
                                                    @endphp
                                                    <span class="badge rounded-pill px-3 py-1.5 fw-semibold {{ $statusClass }}">{{ $item->report->status }}</span>
                                                </div>
                                            </div>
                                            <div class="daily-report-entry-body mb-1">
                                                <div class="text-body riched-content" @if ($hasLongContent) :class="expanded ? '' : 'limit-height'" @endif>
                                                    {!! $item->report->content !!}
                                                </div>
                                                @if ($hasLongContent)
                                                    <button @click="expanded = !expanded" class="btn btn-link btn-sm p-0 mt-2 text-primary text-decoration-none fw-bold">
                                                        <span x-show="!expanded"><i class="fa-solid fa-chevron-down me-1"></i> Xem đầy đủ báo cáo</span>
                                                        <span x-show="expanded"><i class="fa-solid fa-chevron-up me-1"></i> Thu gọn lại</span>
                                                    </button>
                                                @endif
                                                @if ($item->report->issues || $item->report->plan)
                                                    <div class="daily-report-detail-grid mt-3">
                                                        @if ($item->report->issues)
                                                            <div class="daily-report-note daily-report-note-issue">
                                                                <strong><i class="fa-solid fa-triangle-exclamation me-1"></i> Vấn đề / Hỗ trợ</strong>
                                                                <span>{{ $item->report->issues }}</span>
                                                            </div>
                                                        @endif
                                                        @if ($item->report->plan)
                                                            <div class="daily-report-note daily-report-note-plan">
                                                                <strong><i class="fa-regular fa-calendar-check me-1"></i> Kế hoạch ngày mai</strong>
                                                                <span>{!! nl2br(e($item->report->plan)) !!}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <!-- Not Reported -->
                                        @php
                                            $meta = $this->lateMissingMeta($this->daysDiffFromDateFilter());
                                        @endphp
                                        <div class="daily-report-missing-entry card border border-light-subtle shadow-sm rounded-3 px-3 px-lg-4 py-3 bg-body transition-all {{ $meta['itemClass'] }}" style="{{ $meta['itemStyle'] }}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center wh-38 {{ $meta['avatarClass'] }}" style="width: 38px; height: 38px;">
                                                        {{ strtoupper(substr($item->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 {{ $meta['nameClass'] }} d-flex align-items-center gap-2">
                                                            {{ $item->user->name }}
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal rounded-pill fs-75">{{ $item->user->department?->name ?? 'Nhân viên' }}</span>
                                                            <span class="fw-normal opacity-75">&mdash; chưa gửi báo cáo ngày</span>
                                                        </h6>
                                                    </div>
                                                </div>
                                                <span class="badge rounded-pill px-3 py-1.5 fw-semibold {{ $meta['badgeClass'] }}">{{ $meta['badgeText'] }}</span>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($activeTab === 'support' && $isManager)
        <div class="row g-0">
            <div class="col-12 px-3">
                <div class="card border border-light-subtle shadow-sm mb-4 rounded-3 overflow-hidden">
                    <div class="card-header bg-body-tertiary border-bottom p-3 p-lg-4">
                        <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
                            <div>
                                <h5 class="mb-1 fw-bold text-body"><i class="fa-solid fa-life-ring text-danger me-2"></i>Hỗ trợ báo cáo ngày</h5>
                                <div class="text-muted small">Tiếp nhận, theo dõi và phản hồi các khó khăn nhân viên gửi trong báo cáo.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-3 py-2 rounded-pill">{{ $supportStats['pending'] }} chờ xử lý</span>
                                <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill">{{ $supportStats['in_progress'] }} đang xử lý</span>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-2 rounded-pill">{{ $supportStats['resolved'] }} đã xử lý</span>
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-12 col-md-4 col-xl-3">
                                <select wire:model.live="supportStatusFilter" class="form-select border-light-subtle rounded-3">
                                    <option value="open">Chưa hoàn tất</option>
                                    <option value="pending">Chờ xử lý</option>
                                    <option value="in_progress">Đang xử lý</option>
                                    <option value="resolved">Đã xử lý</option>
                                    <option value="all">Tất cả trạng thái</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4 col-xl-3">
                                <select wire:model.live="deptIdFilter" class="form-select border-light-subtle rounded-3">
                                    <option value="">Tất cả phòng ban</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4 col-xl-3">
                                <select wire:model.live="userIdFilter" class="form-select border-light-subtle rounded-3">
                                    <option value="">Tất cả nhân viên</option>
                                    @foreach ($users as $user)
                                        @if (!$deptIdFilter || $user->department_id == $deptIdFilter)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-xl-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-body border-light-subtle"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                    <input wire:model.live.debounce.400ms="supportSearch" type="search" class="form-control border-light-subtle" placeholder="Tìm nhân viên, nội dung...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3 p-lg-4 bg-body-tertiary">
                        <div class="d-flex flex-column gap-3">
                            @forelse ($supportReports as $supportReport)
                                @php
                                    $rawStatus = $supportReport->support_status;
                                    $supportStatus = $rawStatus instanceof \App\Enums\DailyReportSupportStatus
                                        ? $rawStatus
                                        : (is_string($rawStatus) ? \App\Enums\DailyReportSupportStatus::tryFrom($rawStatus) : null);
                                    $supportColor = $supportStatus?->color() ?? 'secondary';
                                    $statusValue = $supportStatus?->value ?? (is_string($rawStatus) ? $rawStatus : '');
                                @endphp
                                <div wire:key="support-report-{{ $supportReport->id }}" class="card border border-light-subtle shadow-sm rounded-3 p-3 p-lg-4 bg-body">
                                    {{-- Header Row --}}
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <strong class="text-body fs-6">{{ $supportReport->user->name }}</strong>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-normal">{{ $supportReport->user->department?->name ?? 'Chưa có phòng ban' }}</span>
                                            <span class="text-muted small ms-1"><i class="fa-regular fa-calendar me-1"></i>Báo cáo ngày {{ $supportReport->date->format('d/m/Y') }}</span>
                                        </div>
                                        <span class="badge bg-{{ $supportColor }} bg-opacity-10 text-{{ $supportColor }} border border-{{ $supportColor }}-subtle rounded-pill px-2.5 py-1">{{ $supportStatus?->label() }}</span>
                                    </div>

                                    {{-- Content Row: Nội dung hỗ trợ + 2 Nút thao tác cùng hàng --}}
                                    <div class="d-flex flex-column flex-md-row align-items-stretch gap-3">
                                        <div class="alert alert-danger border border-danger-subtle bg-danger-subtle text-danger-emphasis shadow-sm p-3 rounded-3 mb-0 flex-grow-1 min-w-0">
                                            <div class="small fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1 text-danger"></i>Nội dung cần hỗ trợ</div>
                                            <div class="text-break">{{ $supportReport->issues ?: strip_tags($supportReport->content) }}</div>
                                        </div>

                                        <div class="d-flex flex-row flex-md-column gap-2 align-items-stretch justify-content-center flex-shrink-0 min-w-140px">
                                            @if ($statusValue === \App\Enums\DailyReportSupportStatus::PENDING->value)
                                                <button wire:click="startSupport({{ $supportReport->id }})" wire:loading.attr="disabled" wire:target="startSupport({{ $supportReport->id }})" class="btn btn-warning fw-semibold text-dark text-nowrap shadow-sm rounded-2">
                                                    <span wire:loading.remove wire:target="startSupport({{ $supportReport->id }})"><i class="fa-solid fa-hand me-1"></i> Tiếp nhận</span>
                                                    <span wire:loading wire:target="startSupport({{ $supportReport->id }})"><span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...</span>
                                                </button>
                                            @endif
                                            @if ($statusValue !== \App\Enums\DailyReportSupportStatus::RESOLVED->value)
                                                <button wire:click="openSupportModal({{ $supportReport->id }})" wire:loading.attr="disabled" wire:target="openSupportModal({{ $supportReport->id }})" class="btn btn-success fw-semibold text-nowrap shadow-sm rounded-2">
                                                    <span wire:loading.remove wire:target="openSupportModal({{ $supportReport->id }})"><i class="fa-solid fa-check me-1"></i> Hoàn tất</span>
                                                    <span wire:loading wire:target="openSupportModal({{ $supportReport->id }})"><span class="spinner-border spinner-border-sm me-1"></span> Đang tải...</span>
                                                </button>
                                            @else
                                                <button wire:click="reopenSupport({{ $supportReport->id }})" wire:loading.attr="disabled" wire:target="reopenSupport({{ $supportReport->id }})" class="btn btn-outline-secondary fw-semibold text-nowrap rounded-2">
                                                    <span wire:loading.remove wire:target="reopenSupport({{ $supportReport->id }})"><i class="fa-solid fa-rotate-left me-1"></i> Mở lại</span>
                                                    <span wire:loading wire:target="reopenSupport({{ $supportReport->id }})"><span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Handler Info & Response --}}
                                    @if ($supportReport->support_handler_id)
                                        <div class="small text-muted mt-3 pt-2 border-top border-light-subtle">
                                            <i class="fa-solid fa-user-check me-1 text-success"></i>Người xử lý: <strong class="text-body">{{ $supportReport->supportHandler?->name ?? 'Tài khoản đã xóa' }}</strong>
                                            @if ($supportReport->support_started_at)
                                                · tiếp nhận {{ $supportReport->support_started_at->format('d/m/Y H:i') }}
                                            @endif
                                        </div>
                                    @endif

                                    @if ($supportReport->support_response)
                                        <div class="alert alert-success border border-success-subtle bg-success-subtle text-success-emphasis shadow-sm p-3 rounded-3 mt-3 mb-0">
                                            <div class="small fw-bold mb-1"><i class="fa-solid fa-circle-check me-1 text-success"></i>Kết quả hỗ trợ</div>
                                            <div class="text-break">{!! nl2br(e($supportReport->support_response)) !!}</div>
                                            @if ($supportReport->support_resolved_at)
                                                <div class="small text-muted mt-1">Hoàn tất {{ $supportReport->support_resolved_at->format('d/m/Y H:i') }}</div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted bg-body rounded-3 border">
                                    <i class="fa-solid fa-circle-check fs-2 text-success mb-3 d-block"></i>
                                    Không có yêu cầu hỗ trợ phù hợp bộ lọc.
                                </div>
                            @endforelse
                        </div>

                        @if ($supportReports instanceof \Illuminate\Pagination\LengthAwarePaginator && $supportReports->hasPages())
                            <div class="mt-4 d-flex justify-content-center">
                                {{ $supportReports->links('livewire.admin.users.pagination') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- SHARED CALENDAR GRID -->
    @if ($this->shouldRenderCalendar())
        @include('livewire.admin.daily-reports.partials.calendar')
    @endif

    <!-- Day Detail Modal -->
    <div x-data="{ open: false, date: '', reports: [] }"
        @open-day-detail.window="open = true; date = $event.detail.date; reports = $event.detail.reports"
        x-show="open" class="fixed-overlay-9999" style="display: none;" @keydown.escape.window="open = false">
        <div class="modal-overlay-dark" @click="open = false"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 94%; max-width: 920px; max-height: 90vh; overflow-y: auto; background: var(--bs-body-bg); border-radius: 16px; box-shadow: 0 25px 50px rgba(0,0,0,0.15);"
            @click.stop>
            <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-calendar-day me-2"></i> Báo cáo ngày <span x-text="date"></span></h5>
                <button @click="open = false" class="btn-close"></button>
            </div>
            <div class="p-4">
                <template x-for="(r, idx) in reports" :key="idx">
                    <div class="mb-4 p-3 rounded-3 border transition-all"
                        :class="r.status === 'Gặp vấn đề, cần hỗ trợ' ? 'bg-danger-subtle border-danger-subtle text-danger' : (r.status === 'Hoàn thành một phần' ? 'bg-warning-subtle border-warning-subtle text-warning' : 'bg-success-subtle border-success-subtle text-success')">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="fw-bold text-body" x-text="r.name"></span>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-2 fw-normal"
                                    x-text="r.department"></span>
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
                                <span class="fw-bold"
                                    :class="r.status === 'Gặp vấn đề, cần hỗ trợ' ? 'text-danger' : (r.status === 'Hoàn thành một phần' ? 'text-warning' : 'text-success')"
                                    x-text="r.status"></span>
                                <template x-if="r.user_id === {{ auth()->id() }}">
                                    <div class="d-flex gap-1 ms-2">
                                        <button @click="$wire.openReportModal(r.date); open = false"
                                            class="btn btn-sm btn-outline-primary py-0 px-2 fs-75 rounded-2"
                                            title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button
                                            @click="if(confirm('Bạn chắc chắn muốn xóa báo cáo này?')) { $wire.deleteReport(r.id); open = false }"
                                            class="btn btn-sm btn-outline-danger py-0 px-2 fs-75 rounded-2"
                                            title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="riched-content text-body text-break" x-html="r.content"></div>
                        <template x-if="r.plan">
                            <div class="mt-2 text-muted"><span class="text-danger opacity-75 fw-bold">Kế hoạch mai:</span> <span x-text="r.plan"></span></div>
                        </template>
                        <template x-if="r.issues">
                            <div class="mt-2 text-danger"><strong>Vấn đề/Hỗ trợ:</strong> <span x-text="r.issues"></span></div>
                        </template>
                    </div>
                </template>
                <div x-show="reports.length === 0" class="text-muted text-center py-4">Không có báo cáo.</div>
            </div>
        </div>
    </div>

    <!-- Quick Report Modal (Add/Edit) -->
    <div x-data="{ open: @entangle('showReportModal') }" x-init="$watch('open', value => { if (value) { setTimeout(initModalEditor, 100); } })" x-show="open" class="fixed-overlay-10000"
        style="display: none;" @keydown.escape.window="open = false">
        <div class="modal-overlay-blur" @click="open = false"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 95%; max-width: 800px; background: var(--bs-body-bg); border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); overflow: hidden;"
            @click.stop>
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>
                    {{ $isEditing ? 'Cập nhật báo cáo' : 'Gửi báo cáo mới' }}
                </h5>
                <button @click="open = false" class="btn-close shadow-none"></button>
            </div>

            <div class="p-4 mxh-70vh overflow-y-auto">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold text-muted mb-0">Ngày báo cáo</label>
                        <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill">
                            {{ \Carbon\Carbon::parse($reportDate)->format('d/m/Y') }}
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted">Nội dung công việc *</label>
                    <div wire:ignore wire:key="daily-report-modal-editor"
                        class="editor-container daily-report-modal-editor rounded-8px overflow-hidden border border-light-subtle">
                        <div id="modal-content-editor">{!! $content !!}</div>
                    </div>
                    @error('content')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label fw-bold text-muted">Trạng thái hoàn thành</label>
                        <select wire:model="status" class="form-select border-light-subtle rounded-12px">
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
                <button @click="open = false" class="btn btn-light px-4 py-2 flex-grow-1 rounded-10px">Hủy</button>
                <button wire:click="save" class="btn btn-dark px-4 py-2 flex-grow-1 rounded-10px" wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="fa-solid fa-circle-check me-1"></i> Lưu báo cáo</span>
                    <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span> Đang lưu...</span>
                </button>
            </div>
        </div>
    </div>

    <div x-data="{ open: @entangle('showSupportModal') }" x-show="open" class="fixed-overlay-10000" style="display: none;" @keydown.escape.window="$wire.closeSupportModal()">
        <div class="modal-overlay-blur" @click="$wire.closeSupportModal()"></div>
        <div class="position-fixed top-50 start-50 translate-middle bg-body rounded-4 shadow-lg overflow-hidden" style="width: 94%; max-width: 560px;" @click.stop>
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold"><i class="fa-solid fa-circle-check text-success me-2"></i>Hoàn tất hỗ trợ</h5>
                    <div class="small text-muted">Ghi lại kết quả để nhân viên nắm được cách xử lý.</div>
                </div>
                <button type="button" wire:click="closeSupportModal" class="btn-close"></button>
            </div>
            <div class="p-4">
                <label class="form-label fw-bold">Nội dung đã hỗ trợ *</label>
                <textarea wire:model="supportResolution" class="form-control rounded-8px" rows="6" placeholder="Mô tả hướng xử lý, kết quả hoặc việc nhân viên cần làm tiếp..."></textarea>
                @error('supportResolution')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>
            <div class="p-4 bg-light-subtle border-top d-flex gap-2 justify-content-end">
                <button type="button" wire:click="closeSupportModal" class="btn btn-light px-4">Hủy</button>
                <button type="button" wire:click="resolveSupport" wire:loading.attr="disabled" class="btn btn-success px-4 fw-semibold">
                    <span wire:loading.remove wire:target="resolveSupport"><i class="fa-solid fa-check me-1"></i>Xác nhận hoàn tất</span>
                    <span wire:loading wire:target="resolveSupport"><span class="spinner-border spinner-border-sm me-1"></span>Đang lưu...</span>
                </button>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/daily-report.css') }}?v={{ config('app.version') }}">
    @endpush

    <script>
        // Initialize buffer with server-side content on load only if not already set
        if (typeof window.__dailyReportContentBuffer === 'undefined') {
            window.__dailyReportContentBuffer = @js($content);
        }

        let isSettingData = false;

        function safeSetData(editor, content) {
            if (!editor || isSettingData) return;
            try {
                if (typeof editor.getData === 'function' && editor.getData() !== content) {
                    isSettingData = true;
                    editor.setData(content || '');
                }
            } catch (e) {
                console.warn('CKEditor setData exception caught safely:', e);
            } finally {
                setTimeout(() => { isSettingData = false; }, 50);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            initAllEditors();
        });

        function initAllEditors() {
            if (typeof ClassicEditor === 'undefined') return;

            // Content Editor
            const contentEl = document.querySelector('#content-editor');
            const contentContainer = contentEl?.closest('.editor-container');
            const hasRenderedEditor = contentContainer?.querySelector('.ck-editor');

            if (contentEl && !hasRenderedEditor && !contentEl.classList.contains('ck-editor-initialized')) {
                // Mark initialized synchronously to block race conditions
                contentEl.classList.add('ck-editor-initialized');

                ClassicEditor.create(contentEl, {
                    placeholder: 'Chi tiết công việc đã làm...',
                    toolbar: ['bold', 'italic', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo']
                }).then(editor => {
                    window.contentEditor = editor;

                    // Sync to Livewire and global buffer
                    editor.model.document.on('change:data', () => {
                        if (isSettingData) return;
                        const data = editor.getData();
                        @this.set('content', data);
                        window.__dailyReportContentBuffer = data;
                    });

                    // Apply buffered content when editor is initialized safely.
                    if (window.__dailyReportContentBuffer !== undefined) {
                        safeSetData(editor, window.__dailyReportContentBuffer || '');
                    }
                }).catch(err => {
                    contentEl.classList.remove('ck-editor-initialized');
                    console.error(err);
                });
            }
        }

        function initModalEditor() {
            if (typeof ClassicEditor === 'undefined') return;

            // Modal Content Editor
            const modalContentEl = document.querySelector('#modal-content-editor');
            if (modalContentEl && !modalContentEl.classList.contains('ck-editor-initialized')) {
                // Mark initialized synchronously to block race conditions
                modalContentEl.classList.add('ck-editor-initialized');

                // If there's an existing dangling editor, destroy it first
                if (window.modalContentEditor) {
                    try {
                        window.modalContentEditor.destroy();
                    } catch (e) {
                        console.error('Failed to destroy modalContentEditor:', e);
                    }
                    window.modalContentEditor = null;
                }

                ClassicEditor.create(modalContentEl, {
                    placeholder: 'Hôm nay bạn đã hoàn thành những việc gì?',
                    toolbar: ['bold', 'italic', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo']
                }).then(editor => {
                    window.modalContentEditor = editor;

                    // Sync to Livewire and global buffer
                    editor.model.document.on('change:data', () => {
                        if (isSettingData) return;
                        const data = editor.getData();
                        @this.set('content', data);
                        window.__dailyReportContentBuffer = data;
                    });

                    // Apply buffered content when editor is initialized safely.
                    if (window.__dailyReportContentBuffer !== undefined) {
                        safeSetData(editor, window.__dailyReportContentBuffer || '');
                    }
                }).catch(err => {
                    modalContentEl.classList.remove('ck-editor-initialized');
                    console.error(err);
                });
            }
        }

        // Handle Livewire Navigation/Morphing
        if (!window.__dailyReportEditorListenersRegistered) {
            document.addEventListener('livewire:navigated', initAllEditors);
            document.addEventListener('livewire:init', initAllEditors);
            window.__dailyReportEditorListenersRegistered = true;
        }

        // Sync content from Livewire after save/load without forcing full page refresh.
        if (!window.__dailyReportContentSyncRegistered) {
            window.addEventListener('editor:set-content', (event) => {
                const nextContent = event.detail?.content ?? '';
                window.__dailyReportContentBuffer = nextContent;

                if (window.contentEditor) {
                    safeSetData(window.contentEditor, nextContent);
                }
                if (window.modalContentEditor) {
                    safeSetData(window.modalContentEditor, nextContent);
                }
            });
            window.__dailyReportContentSyncRegistered = true;
        }

        // Polling-style check for tab changes (robust)
        if (!window.__dailyReportEditorInterval) {
            window.__dailyReportEditorInterval = setInterval(() => {
                if (typeof ClassicEditor === 'undefined') return;

                const contentEl = document.querySelector('#content-editor');
                const contentContainer = contentEl?.closest('.editor-container');

                if (contentEl && !contentContainer?.querySelector('.ck-editor') &&
                    !contentEl.classList.contains('ck-editor-initialized')) {
                    initAllEditors();
                }
            }, 1000);
        }
    </script>
</div>
