<div>
    @section('title', 'Tiến độ dự án TV-KT')
    @section('page_title', 'Tiến độ dự án TV-KT')

    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3 mt-2 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                    <i class="fa-solid fa-diagram-project"></i>
                </span>
                <h2 class="h4 fw-bold text-body mb-0">Tiến độ dự án TV-KT</h2>
            </div>
            <p class="text-muted small mb-0">Theo dõi tiến độ 6 bước, nhân sự thực hiện và tài liệu của từng hợp đồng.</p>
        </div>
        <span class="d-inline-flex align-items-center gap-2 rounded-3 bg-primary bg-opacity-10 text-primary px-3 py-2 small fw-semibold align-self-start">
            <i class="fa-regular fa-calendar"></i>Tháng {{ $month }}/{{ $year }}
        </span>
    </div>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/sales-project-progress-report.css') }}?v={{ config('app.version') }}">
    @endpush

    <div class="card border border-light-subtle shadow-sm rounded-3 mb-3 bg-body">
        <div class="card-body p-3">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="fa-solid fa-layer-group text-primary"></i>
                <h3 class="h6 fw-bold text-body mb-0">Nhóm hợp đồng</h3>
            </div>
            <nav class="d-flex flex-wrap gap-2" aria-label="Nhóm hợp đồng">
                @foreach($contractTypes as $typeKey => $typeName)
                    <button
                        type="button"
                        wire:click="selectContractType('{{ $typeKey }}')"
                        class="btn btn-sm rounded-3 {{ $filter_contract_type === $typeKey ? 'btn-primary' : 'btn-light text-body' }}"
                        aria-pressed="{{ $filter_contract_type === $typeKey ? 'true' : 'false' }}"
                    >
                        {{ $typeName }}
                    </button>
                @endforeach
            </nav>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="card border border-light-subtle shadow-sm rounded-3 mb-4 overflow-hidden bg-body">
        <div class="card-body p-3 p-lg-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                    <i class="fa-solid fa-filter"></i>
                </span>
                <div>
                    <h3 class="h6 fw-bold text-body mb-0">Bộ lọc báo cáo</h3>
                    <small class="text-muted">Chọn tháng, năm, nhân sự và loại dịch vụ cần theo dõi</small>
                </div>
            </div>
            <div class="row g-3 align-items-end">
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="project-progress-month" class="form-label small fw-semibold text-body mb-1">Tháng</label>
                    <select id="project-progress-month" wire:model.live="month" class="form-select border-light-subtle">
                        @foreach(range(1, 12) as $monthNumber)
                            <option value="{{ $monthNumber }}">Tháng {{ $monthNumber }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="project-progress-year" class="form-label small fw-semibold text-body mb-1">Năm</label>
                    <select id="project-progress-year" wire:model.live="year" class="form-select border-light-subtle">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 col-xl-2">
                    <label for="project-progress-staff" class="form-label small fw-semibold text-body mb-1">Nhân sự thực hiện</label>
                    <select id="project-progress-staff" wire:model.live="filter_staff_id" class="form-select border-light-subtle">
                        <option value="all">Tất cả nhân sự TV/KT</option>
                        @foreach($assignedStaffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-7 col-xl-3">
                    <label for="project-progress-service" class="form-label small fw-semibold text-body mb-1">Loại dịch vụ</label>
                    <select id="project-progress-service" wire:model.live="filter_service" class="form-select border-light-subtle">
                        <option value="all">Tất cả loại dịch vụ</option>
                        @foreach($serviceOptions as $serviceOption)
                            <option value="{{ $serviceOption }}">{{ $serviceOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-5 col-xl-3">
                    <div class="bg-body-tertiary rounded-3 px-3 py-2 border border-light-subtle">
                        <div class="small text-muted">Đang hiển thị</div>
                        <div class="fw-semibold text-body">{{ number_format($items->total()) }} hợp đồng · Tháng {{ $month }}/{{ $year }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @php
            $statistics = [
                ['label' => 'Tổng hợp đồng', 'value' => $summary->total, 'sub' => 'theo bộ lọc hiện tại', 'icon' => 'fa-file-contract', 'accent' => 'bg-primary-subtle text-primary'],
                ['label' => 'Chưa bắt đầu', 'value' => $summary->not_started, 'sub' => 'đang chờ tiếp nhận', 'icon' => 'fa-hourglass-start', 'accent' => 'bg-secondary-subtle text-secondary'],
                ['label' => 'Đang thực hiện', 'value' => $summary->active, 'sub' => 'đã phát sinh tiến độ', 'icon' => 'fa-list-check', 'accent' => 'bg-warning-subtle text-warning'],
                ['label' => 'Đã hoàn thành', 'value' => $summary->completed, 'sub' => 'hoàn tất đủ 6 bước', 'icon' => 'fa-circle-check', 'accent' => 'bg-success-subtle text-success'],
                ['label' => 'Tiến độ trung bình', 'value' => number_format($summary->progress, 1, ',', '.').'%', 'sub' => 'trên toàn bộ hợp đồng', 'icon' => 'fa-chart-line', 'accent' => 'bg-info-subtle text-info'],
            ];
        @endphp
        @foreach($statistics as $statistic)
            <div class="col-6 col-lg-4 col-xl">
                <div class="card border border-light-subtle shadow-sm rounded-3 h-100 bg-body">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <span class="icon-42 d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0 {{ $statistic['accent'] }}">
                            <i class="fa-solid {{ $statistic['icon'] }}"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="small text-muted text-truncate">{{ $statistic['label'] }}</div>
                            <div class="h5 fw-bold text-body mb-0 lh-sm">{{ $statistic['value'] }}</div>
                            <small class="text-muted text-truncate d-block">{{ $statistic['sub'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pipeline tiến độ hợp đồng --}}
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <div>
            <h3 class="h5 fw-bold text-body mb-1">Pipeline tiến độ hợp đồng</h3>
            <span class="text-muted small">Mỗi hợp đồng được xếp theo bước đang thực hiện.</span>
        </div>
        <span class="d-inline-flex align-items-center gap-1 text-muted small"><i class="fa-solid fa-arrows-left-right"></i>Cuộn ngang để xem đủ 6 bước</span>
    </div>
    <div class="d-flex align-items-start gap-3 overflow-x-auto pb-3 mb-4">
        @foreach(\App\Models\ContractWorkflowStep::STEP_KEYS as $stepKey)
            <section class="mnw-260px flex-shrink-0">
                <div class="card border border-light-subtle shadow-sm rounded-3 bg-body-tertiary">
                    <div class="card-header border-0 bg-transparent p-3 d-flex justify-content-between align-items-start gap-2">
                        <div class="d-flex align-items-start gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary p-2 small fw-bold">{{ $loop->iteration }}</span>
                            <h4 class="h6 fw-bold text-body mb-0 pt-1">{{ \App\Models\ContractWorkflowStep::STEPS[$stepKey] }}</h4>
                        </div>
                        <span class="d-inline-flex rounded-3 bg-secondary bg-opacity-10 text-secondary px-2 py-1 small fw-semibold">{{ count($pipeline[$stepKey]) }}</span>
                    </div>
                    <div class="card-body px-3 pt-0 pb-3">
                        @forelse($pipeline[$stepKey] as $contract)
                            <article class="card border border-light-subtle shadow-sm rounded-3 mb-3 bg-body" wire:key="pipeline-{{ $contract['source_key'] }}-{{ $contract['id'] }}">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between gap-2 align-items-start">
                                        <div class="min-w-0">
                                            <h6 class="fw-bold mb-1 text-truncate" title="{{ $contract['customer'] }}">{{ $contract['customer'] }}</h6>
                                            <small class="text-muted">{{ $contract['shd'] }} · {{ $contract['signed_at']?->format('d/m/Y') ?? 'Chưa có ngày ký' }}</small>
                                        </div>
                                        <span class="d-inline-flex rounded-3 bg-primary bg-opacity-10 text-primary px-2 py-1 small fw-semibold text-nowrap">{{ $contract['workflow_progress']['completed_count'] }}/6</span>
                                    </div>
                                    <div class="rounded-3 bg-body-tertiary p-2 small mt-3">{{ $contract['type'] }}</div>
                                    <div class="d-flex justify-content-between gap-2 small mt-3">
                                        <span class="text-muted text-truncate" title="{{ $contract['assigned_staff'] }}">{{ $contract['assigned_staff'] }}</span>
                                        <span class="text-muted text-nowrap">{{ $contract['workflow_progress']['percent'] }}%</span>
                                    </div>
                                    <div class="progress h-6px mt-2">
                                        <div class="progress-bar {{ $contract['workflow_progress']['percent'] >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $contract['workflow_progress']['percent'] }}%"></div>
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <button type="button" class="btn btn-light btn-sm flex-grow-1" wire:click="showDetails('{{ $contract['source_key'] }}', {{ $contract['id'] }})">
                                            <i class="fa-solid fa-eye me-1"></i> Chi tiết
                                        </button>
                                        @if($this->canAssign())
                                            <button type="button" class="btn btn-success btn-sm flex-grow-1" wire:click="openAssign('{{ $contract['source_key'] }}', {{ $contract['id'] }})">
                                                <i class="fa-solid fa-user-check me-1"></i> Giao việc
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-3 bg-body p-4 text-center text-muted small">
                                <i class="fa-solid fa-inbox d-block fs-4 opacity-25 mb-2"></i>Chưa có hợp đồng
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        @endforeach
    </div>

    @if($items->hasPages())
        <div class="card border-0 shadow-sm rounded-12px mb-4">
            <div class="card-body px-3 py-2 d-flex justify-content-center">
                {{ $items->links('livewire.admin.users.pagination') }}
            </div>
        </div>
    @endif

    {{-- Danh sách dạng bảng cũ được giữ ẩn để không ảnh hưởng modal và phân trang --}}
    <div class="card mb-3 overflow-hidden d-none">
        <div class="card-header bg-light d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h6 class="mb-1 fw-bold">
                    <i class="fa-solid fa-briefcase me-1 text-primary"></i>
                    {{ $contractTypes[$filter_contract_type] }}
                </h6>
                <small class="text-muted">Theo dõi 6 bước thực hiện hợp đồng</small>
            </div>
            <div class="workflow-summary">
                <div class="workflow-summary-item">
                    <span>Tổng hợp đồng</span>
                    <strong class="text-primary">{{ number_format($summary->total) }}</strong>
                </div>
                <div class="workflow-summary-item">
                    <span>Đang thực hiện</span>
                    <strong class="text-warning">{{ number_format($summary->active) }}</strong>
                </div>
                <div class="workflow-summary-item">
                    <span>Hoàn thành</span>
                    <strong class="text-success">{{ number_format($summary->completed) }}</strong>
                </div>
            </div>
        </div>
        <div class="workflow-overall-progress">
            <div class="progress">
                <div class="progress-bar {{ $summary->progress >= 100 ? 'bg-success' : 'bg-primary' }}" role="progressbar" style="width: {{ min($summary->progress, 100) }}%" aria-valuenow="{{ $summary->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <small class="text-muted">Tổng tiến độ: {{ number_format($summary->progress, 1, ',', '.') }}%</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0 workflow-matrix">
                    <thead class="table-light">
                        <tr>
                            <th class="workflow-contract-column">Thông tin hợp đồng</th>
                            @foreach(\App\Models\ContractWorkflowStep::STEP_KEYS as $stepKey)
                                <th class="workflow-step-column">
                                    <span class="workflow-step-number">{{ $loop->iteration }}</span>
                                    <span>{{ \App\Models\ContractWorkflowStep::STEPS[$stepKey] }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="workflow-contract-column">
                                <div class="d-flex flex-column gap-2">
                                    <a href="{{ $item['customer_slug'] ? route('app.customers.contracts', $item['customer_slug']) : '#' }}" class="fw-bold text-primary text-decoration-none lh-sm">
                                        {{ $item['customer'] }}
                                    </a>
                                    <div class="workflow-contract-meta">
                                        <span>{{ $item['shd'] }}</span>
                                        @if($item['signed_at'])<span>{{ $item['signed_at']->format('d/m/Y') }}</span>@endif
                                    </div>
                                    <div class="small fw-semibold">{{ $item['type'] }}</div>
                                    <div class="small text-muted">{{ $item['assigned_staff'] }}</div>
                                    <div class="workflow-contract-progress">
                                        <div class="progress" role="progressbar" aria-label="Tiến độ hợp đồng" aria-valuenow="{{ $item['workflow_progress']['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar {{ $item['workflow_progress']['percent'] >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $item['workflow_progress']['percent'] }}%"></div>
                                        </div>
                                        <span>{{ $item['workflow_progress']['completed_count'] }}/{{ $item['workflow_progress']['total_steps'] }}</span>
                                    </div>
                                    <button type="button" wire:click="showDetails('{{ $item['source_key'] }}', {{ $item['id'] }})" class="workflow-detail-button">
                                        Xem chi tiết <i class="fa-solid fa-arrow-right-long ms-1"></i>
                                    </button>
                                </div>
                            </td>
                            @foreach($item['workflow_steps'] as $step)
                                <td class="text-center {{ $step['state'] === 'current' ? 'bg-primary-subtle' : '' }}">
                                    @if($step['state'] === 'completed')
                                        <span class="badge bg-success-subtle text-success px-2 py-1">
                                            <i class="fa-solid fa-check me-1"></i>Hoàn thành
                                        </span>
                                    @elseif($step['state'] === 'current')
                                        <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1">
                                            Đang thực hiện
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                    @if($step['completed_at'])
                                        <div class="workflow-state-meta">{{ $step['completed_at']->format('d/m/Y') }}</div>
                                        @if($step['completed_by'])
                                            <div class="workflow-state-person" title="Hoàn thành bởi {{ $step['completed_by'] }}">{{ $step['completed_by'] }}</div>
                                        @endif
                                    @elseif($step['state'] === 'current')
                                        <div class="workflow-state-meta">Chờ cập nhật</div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                Chưa có hợp đồng nào trong nhóm và bộ lọc đã chọn
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
            <div class="px-4 py-3 border-top d-flex justify-content-end">
                {{ $items->links('livewire.admin.users.pagination') }}
            </div>
            @endif
        </div>
    </div>

    {{-- Detail Modal --}}
    <div wire:ignore.self class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-12px overflow-hidden">
                @if($selectedContract)
                <div class="modal-header bg-body border-bottom p-3">
                    <h5 class="h6 modal-title fw-bold text-body d-flex align-items-center gap-2" id="detailModalLabel">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                            <i class="fa-solid fa-list-check"></i>
                        </span>
                        Chi tiết tiến độ: {{ $selectedContract->shd_bc ?: $selectedContract->shd_cxl ?: 'Chưa cập nhật số HĐ' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body p-0">
                    <div x-data="{ tab: @entangle('detailActiveTab') }">
                        {{-- Tabs --}}
                        <ul class="nav nav-tabs px-3 pt-2 bg-body-tertiary border-bottom" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link fw-bold border-bottom-0" :class="{ 'active text-primary': tab === 'info' }" @click="tab = 'info'" type="button">
                                    <i class="fa-solid fa-circle-info me-1"></i>Thông tin hợp đồng
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold border-bottom-0" :class="{ 'active text-primary': tab === 'progress' }" @click="tab = 'progress'" type="button">
                                    <i class="fa-solid fa-list-check me-1"></i>Tiến độ thực hiện
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold border-bottom-0" :class="{ 'active text-primary': tab === 'docs' }" @click="tab = 'docs'" type="button">
                                    <i class="fa-solid fa-paperclip me-1"></i>Tài liệu đính kèm
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content p-4">
                            {{-- Tab 1: Info --}}
                            <div class="tab-pane fade" :class="{ 'show active': tab === 'info' }">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="fa-solid fa-building me-2"></i>Thông tin chung</h6>
                                        <table class="table table-sm table-borderless align-middle mb-0">
                                            <tbody>
                                                <tr>
                                                    <td class="text-muted w-150px fw-semibold">Khách hàng:</td>
                                                    <td class="fw-bold">{{ $selectedContract->customer?->name ?? '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted fw-semibold">Loại dịch vụ:</td>
                                                    <td>{{ $selectedContract->loai_dich_vu ?: '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted fw-semibold">Ngày ký HĐ:</td>
                                                    <td>{{ $selectedContract->signed_at ? $selectedContract->signed_at->format('d/m/Y') : '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted fw-semibold">Tỉnh thành:</td>
                                                    <td class="fw-bold">{{ $selectedContract->province ?: '—' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="fa-solid fa-user-gear me-2"></i>Nhân sự phụ trách</h6>
                                        <table class="table table-sm table-borderless align-middle mb-0">
                                            <tbody>
                                                <tr>
                                                    <td class="text-muted w-150px fw-semibold">Nhân viên Kinh doanh:</td>
                                                    <td class="fw-bold">{{ $selectedContract->staff?->name ?? '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted fw-semibold">Nhân sự thực hiện:</td>
                                                    <td>
                                                        @php
                                                            $assignees = $selectedContract->assignments->pluck('user.name')->filter()->unique()->toArray();
                                                        @endphp
                                                        {{ count($assignees) > 0 ? implode(', ', $assignees) : 'Chưa phân công' }}
                                                        @if($this->canAssign())
                                                            <button type="button" class="btn btn-link btn-sm text-success p-0 ms-2 text-decoration-none" wire:click="openAssign('{{ $selectedContractSourceKey }}', {{ $selectedContract->id }})">
                                                                <i class="fa-solid fa-user-check me-1"></i> Giao việc
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted fw-semibold">Trạng thái hiện tại:</td>
                                                    <td>
                                                        <span class="badge bg-soft-{{ $selectedContract->status_color }} text-{{ $selectedContract->status_color }}">
                                                            {{ $selectedContract->status_label ?? $selectedContract->status ?? '—' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="fa-solid fa-comment-dots me-2"></i>Ghi chú hợp đồng</h6>
                                        <div class="p-3 bg-light rounded text-muted" style="white-space: pre-line;">
                                            {{ $selectedContract->notes ?: 'Không có ghi chú nào cho hợp đồng này.' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tab 2: Workflow Progress --}}
                            <div class="tab-pane fade" :class="{ 'show active': tab === 'progress' }">
                                @php
                                    $completedSteps = $selectedContract->workflowSteps->pluck('step_name')->unique()->toArray();
                                    $workflowDetails = $this->getContractProgress($selectedContract, $completedSteps);
                                    $steps = $workflowDetails['step_labels'];
                                    $stepKeys = $workflowDetails['step_keys'];
                                    $stepsMap = $selectedContract->workflowSteps->groupBy('step_name');
                                @endphp

                                <div class="mb-4">
                                    <h6 class="fw-bold text-dark mb-2">Tiến trình làm việc: {{ $workflowDetails['percent'] }}%</h6>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $workflowDetails['percent'] }}%"></div>
                                    </div>
                                </div>

                                <div class="timeline-container mt-4">
                                    <div class="list-group list-group-flush">
                                        @foreach($stepKeys as $key)
                                            @php
                                                $records = $stepsMap->get($key, collect());
                                                $isDone = in_array($key, $completedSteps);
                                                $stepFiles = $selectedContract->milestoneFiles->where('milestone', $key);
                                            @endphp
                                            <div class="list-group-item d-flex align-items-start py-3 border-0 border-bottom border-light">
                                                <div class="me-3 mt-1">
                                                    @if($isDone)
                                                        <span class="badge rounded-circle bg-success p-2"><i class="fa-solid fa-check fs-6 text-white w-15px h-15px text-center"></i></span>
                                                    @else
                                                        <span class="badge rounded-circle bg-light p-2 border border-secondary border-opacity-25"><i class="fa-solid fa-hourglass-start fs-6 text-muted w-15px h-15px text-center"></i></span>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <h6 class="mb-0 fw-bold {{ $isDone ? 'text-success' : 'text-muted' }}">{{ $steps[$key] }}</h6>
                                                        @if($isDone && $records->count() > 0)
                                                            <small class="text-muted fw-semibold">{{ $records->last()->created_at->format('d/m/Y H:i') }}</small>
                                                        @endif
                                                    </div>
                                                    @if($isDone && $records->count() > 0)
                                                        @php $lastRecord = $records->last(); @endphp
                                                        <p class="mb-1">
                                                            <strong>Thực hiện bởi:</strong> {{ $lastRecord->user?->name ?? 'Hệ thống' }}
                                                        </p>
                                                        @if($lastRecord->comment)
                                                            <div class="bg-light p-2 rounded text-muted mt-1 mb-2 border-start border-4 border-success">
                                                                {{ $lastRecord->comment }}
                                                            </div>
                                                        @endif
                                                    @else
                                                        <p class="mb-0 text-muted">Bước này chưa được thực hiện.</p>
                                                    @endif

                                                    @if($stepFiles->count() > 0)
                                                        <div class="mt-2">
                                                            <div class="d-flex flex-column gap-1">
                                                                @foreach($stepFiles as $file)
                                                                    @php
                                                                        $ext = pathinfo($file->original_name, PATHINFO_EXTENSION);
                                                                        $iconClass = match(strtolower($ext)) {
                                                                            'pdf' => 'fa-file-pdf text-danger',
                                                                            'xls', 'xlsx' => 'fa-file-excel text-success',
                                                                            'doc', 'docx' => 'fa-file-word text-primary',
                                                                            'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp' => 'fa-file-image text-info',
                                                                            'zip', 'rar' => 'fa-file-zipper text-warning',
                                                                            default => 'fa-file text-secondary'
                                                                        };
                                                                    @endphp
                                                                    <div class="d-flex align-items-center gap-2 bg-light p-1 px-2 rounded border border-light" style="font-size: 0.8rem;">
                                                                        <i class="fa-regular {{ $iconClass }} fs-6"></i>
                                                                        <a href="{{ $file->file_url }}" target="_blank" class="text-primary text-decoration-none fw-semibold text-truncate" style="max-width: 350px;" title="{{ $file->original_name }}">
                                                                            {{ $file->original_name }}
                                                                        </a>
                                                                        <span class="text-muted ms-auto" style="font-size: 0.75rem;">
                                                                            (Tải lên bởi: {{ $file->uploader?->name ?? 'Hệ thống' }})
                                                                        </span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Tab 3: Contract Files --}}
                            <div class="tab-pane fade" :class="{ 'show active': tab === 'docs' }">
                                @php
                                    $contractFiles = $selectedContract->milestoneFiles->whereNull('milestone');
                                @endphp
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-paperclip me-2"></i>Danh sách tài liệu hợp đồng</h6>
                                @if($contractFiles->count() > 0)
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($contractFiles as $file)
                                            @php
                                                $ext = pathinfo($file->original_name, PATHINFO_EXTENSION);
                                                $iconClass = match(strtolower($ext)) {
                                                    'pdf' => 'fa-file-pdf text-danger',
                                                    'xls', 'xlsx' => 'fa-file-excel text-success',
                                                    'doc', 'docx' => 'fa-file-word text-primary',
                                                    'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp' => 'fa-file-image text-info',
                                                    'zip', 'rar' => 'fa-file-zipper text-warning',
                                                    default => 'fa-file text-secondary'
                                                };
                                            @endphp
                                            <div class="d-flex align-items-center gap-3 border rounded p-3 bg-light">
                                                <i class="fa-regular {{ $iconClass }} fs-4"></i>
                                                <div class="flex-grow-1 min-w-0">
                                                    <a href="{{ $file->file_url }}" target="_blank" class="text-primary text-decoration-none fw-bold text-truncate d-block" title="{{ $file->original_name }}">
                                                        {{ $file->original_name }}
                                                    </a>
                                                    <span class="text-muted text-xs">
                                                        Tải lên bởi: {{ $file->uploader?->name ?? 'Hệ thống' }} · {{ $file->created_at?->format('d/m/Y H:i') }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="border border-dashed rounded-3 p-4 text-center text-muted">Chưa có tài liệu đính kèm cho hợp đồng này.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-body py-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Đóng</button>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Assignment Modal --}}
    <div wire:ignore.self class="modal fade" id="assignModalReport" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" x-data="{ searchQuery: '' }">
                <div class="modal-header bg-success py-3">
                    <h5 class="modal-title fw-bold modal-title-custom text-white"><i class="fa-solid fa-user-check me-1"></i> Giao việc hợp đồng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-3 fs-90">Chọn nhân viên để giao việc (có thể chọn nhiều):</p>

                    <!-- Search Box -->
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-body border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" placeholder="Tìm nhanh nhân viên..." x-model="searchQuery">
                        </div>
                    </div>

                    <!-- User Grouping -->
                    <div class="mh-320-scroll pe-1" style="max-height: 280px; overflow-y: auto;">
                        @php
                            $groupedUsers = $assignable_users->groupBy(function($user) {
                                $roleName = $user->roles->first()?->name ?? '';
                                return \App\Enums\Role::tryFrom($roleName)?->label() ?? 'Khác';
                            });
                        @endphp

                        @foreach ($groupedUsers as $roleLabel => $users)
                            <div class="mb-3 role-group-section"
                                 x-show="searchQuery === '' || {{ json_encode($users->pluck('name')->map(fn($n) => mb_strtolower($n))->toArray()) }}.some(name => name.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(searchQuery.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'')))">
                                <div class="fw-bold text-success border-bottom pb-1 mb-2 fs-80 d-flex align-items-center justify-content-between">
                                    <span>{{ $roleLabel }}</span>
                                    <span class="badge bg-success bg-opacity-10 text-success fs-75 rounded-pill">{{ count($users) }}</span>
                                </div>
                                <div class="list-group list-group-flush rounded-3 border mb-2">
                                    @foreach ($users as $u)
                                        @php
                                            $uRole = \App\Enums\Role::tryFrom($u->roles->first()?->name ?? '')?->label() ?? '';
                                        @endphp
                                        <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 px-3 user-item"
                                               x-show="searchQuery === '' || {{ json_encode(mb_strtolower($u->name)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(searchQuery.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))">
                                            <input class="form-check-input flex-shrink-0" type="checkbox"
                                                value="{{ $u->id }}" wire:model="assignUserIds">
                                            <div class="d-flex flex-column lh-sm">
                                                <span class="fw-semibold text-body fs-90">{{ $u->name }}</span>
                                                @if($uRole)
                                                    <span class="text-muted fs-75 mt-0.5">{{ $uRole }}</span>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold fs-90">Người ngoài công ty</label>
                        <input type="text" class="form-control" wire:model="assignExternal"
                            placeholder="Tên người ngoài (nếu có)">
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-semibold fs-90">Hạn chót</label>
                        <input type="date" class="form-control" wire:model="assignDeadline">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-success" wire:click="saveAssign"
                        wire:loading.attr="disabled" wire:target="saveAssign">
                        <span wire:loading wire:target="saveAssign"
                            class="spinner-border spinner-border-sm me-1"></span>
                        Lưu giao việc
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.addEventListener('open-detail-modal', () => {
                setTimeout(() => {
                    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('detailModal'));
                    modal.show();
                }, 200);
            });
            window.addEventListener('openAssignModal', () => {
                let detailEl = document.getElementById('detailModal');
                let detailModal = bootstrap.Modal.getInstance(detailEl);
                if (detailModal) {
                    detailModal.hide();
                }

                setTimeout(() => {
                    let modalElement = document.getElementById('assignModalReport');
                    let modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                    modal.show();
                }, 200);
            });
            Livewire.on('closeAssignModal', () => {
                let modalElement = document.getElementById('assignModalReport');
                let modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
            });
        </script>
    @endpush
</div>
