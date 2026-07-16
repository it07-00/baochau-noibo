<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold text-dark">Tiến độ dự án TV-KT</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}" class="text-decoration-none text-muted">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item text-muted">Báo cáo Kinh doanh</li>
                    <li class="breadcrumb-item active fw-semibold text-primary">Tiến độ dự án</li>
                </ol>
            </nav>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/sales-project-progress-report.css') }}?v={{ config('app.version') }}">
    @endpush

    <div class="card mb-3">
        <div class="card-body p-2">
            <nav class="d-flex flex-wrap gap-2" aria-label="Nhóm hợp đồng">
                @foreach($contractTypes as $typeKey => $typeName)
                    <button
                        type="button"
                        wire:click="selectContractType('{{ $typeKey }}')"
                        class="btn btn-sm {{ $filter_contract_type === $typeKey ? 'btn-primary' : 'btn-outline-secondary' }}"
                        aria-pressed="{{ $filter_contract_type === $typeKey ? 'true' : 'false' }}"
                    >
                        {{ $typeName }}
                    </button>
                @endforeach
            </nav>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1">Năm</label>
                    <select wire:model.live="year" class="form-select form-select-sm">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1">Nhân sự thực hiện</label>
                    <select wire:model.live="filter_staff_id" class="form-select form-select-sm">
                        <option value="all">Tất cả nhân sự TV/KT</option>
                        @foreach($assignedStaffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold mb-1">Loại dịch vụ</label>
                    <select wire:model.live="filter_service" class="form-select form-select-sm">
                        <option value="all">Tất cả loại dịch vụ</option>
                        @foreach($serviceOptions as $serviceOption)
                            <option value="{{ $serviceOption }}">{{ $serviceOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 text-md-end">
                    <div class="small text-muted">Đang hiển thị</div>
                    <div class="fw-semibold text-dark">{{ number_format($items->total()) }} hợp đồng · {{ $year }}</div>
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
            <div class="col-6 col-xl">
                <div class="card border-0 shadow-sm h-100 operation-kpi-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="operation-kpi-icon {{ $statistic['accent'] }}">
                            <i class="fa-solid {{ $statistic['icon'] }}"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="text-muted text-xs">{{ $statistic['label'] }}</div>
                            <div class="fw-bold fs-4 lh-sm">{{ $statistic['value'] }}</div>
                            <div class="text-muted text-xs text-truncate">{{ $statistic['sub'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pipeline tiến độ hợp đồng --}}
    @php
        $pipelineColors = ['border-info', 'border-warning', 'border-primary', 'border-warning', 'border-info', 'border-success'];
    @endphp
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <h5 class="mb-0">Pipeline tiến độ hợp đồng</h5>
        <span class="text-muted small">Mỗi hợp đồng được xếp theo bước đang thực hiện.</span>
    </div>
    <div class="workflow-pipeline mb-4">
        @foreach(\App\Models\ContractWorkflowStep::STEP_KEYS as $stepKey)
            <section class="workflow-pipeline-column">
                <div class="card border shadow-sm h-100 operation-pipeline-column {{ $pipelineColors[$loop->index] }}">
                    <div class="card-header border-0 bg-transparent pb-0 d-flex justify-content-between align-items-start gap-2">
                        <h6 class="fw-bold mb-0">{{ \App\Models\ContractWorkflowStep::STEPS[$stepKey] }}</h6>
                        <span class="badge bg-white text-muted border">{{ count($pipeline[$stepKey]) }}</span>
                    </div>
                    <div class="card-body">
                        @forelse($pipeline[$stepKey] as $contract)
                            <article class="card border-0 shadow-sm mb-3 operation-card-hover" wire:key="pipeline-{{ $contract['source_key'] }}-{{ $contract['id'] }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between gap-2 align-items-start">
                                        <div class="min-w-0">
                                            <h6 class="fw-bold mb-1 text-truncate" title="{{ $contract['customer'] }}">{{ $contract['customer'] }}</h6>
                                            <div class="text-muted text-xs">{{ $contract['shd'] }} · {{ $contract['signed_at']?->format('d/m/Y') ?? 'Chưa có ngày ký' }}</div>
                                        </div>
                                        <span class="badge rounded-pill bg-primary-subtle text-primary">{{ $contract['workflow_progress']['completed_count'] }}/6</span>
                                    </div>
                                    <div class="border rounded-3 bg-light p-2 text-sm mt-3">{{ $contract['type'] }}</div>
                                    <div class="d-flex justify-content-between gap-2 text-xs mt-3">
                                        <span class="text-muted text-truncate" title="{{ $contract['assigned_staff'] }}">{{ $contract['assigned_staff'] }}</span>
                                        <span class="text-muted text-nowrap">{{ $contract['workflow_progress']['percent'] }}%</span>
                                    </div>
                                    <div class="progress mt-2 workflow-card-progress">
                                        <div class="progress-bar {{ $contract['workflow_progress']['percent'] >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $contract['workflow_progress']['percent'] }}%"></div>
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <button type="button" class="btn btn-light btn-sm flex-grow-1" wire:click="showDetails('{{ $contract['source_key'] }}', {{ $contract['id'] }})">
                                            <i class="fa-solid fa-eye me-1"></i> Chi tiết
                                        </button>
                                        @if($this->canAssign())
                                            <button type="button" class="btn btn-outline-success btn-sm flex-grow-1" wire:click="openAssign('{{ $contract['source_key'] }}', {{ $contract['id'] }})">
                                                <i class="fa-solid fa-user-check me-1"></i> Giao việc
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="border border-dashed rounded-3 p-4 text-center text-muted">Trống</div>
                        @endforelse
                    </div>
                </div>
            </section>
        @endforeach
    </div>

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
                                    <div class="small fw-semibold text-dark">{{ $item['type'] }}</div>
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
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                @if($selectedContract)
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold" id="detailModalLabel">
                        <i class="fa-solid fa-list-check me-2"></i>Chi tiết tiến độ hợp đồng: {{ $selectedContract->shd_bc ?: $selectedContract->shd_cxl ?: 'Chưa cập nhật số HĐ' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div x-data="{ tab: @entangle('detailActiveTab') }">
                        {{-- Tabs --}}
                        <ul class="nav nav-tabs px-4 pt-3 bg-light border-bottom" role="tablist">
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
                                                    <td class="fw-bold text-dark">{{ $selectedContract->customer?->name ?? '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted fw-semibold">Loại dịch vụ:</td>
                                                    <td class="text-dark">{{ $selectedContract->loai_dich_vu ?: '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted fw-semibold">Ngày ký HĐ:</td>
                                                    <td class="text-dark">{{ $selectedContract->signed_at ? $selectedContract->signed_at->format('d/m/Y') : '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted fw-semibold">Tỉnh thành:</td>
                                                    <td class="text-dark fw-bold">{{ $selectedContract->province ?: '—' }}</td>
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
                                                    <td class="text-dark fw-bold">{{ $selectedContract->staff?->name ?? '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted fw-semibold">Nhân sự thực hiện:</td>
                                                    <td class="text-dark">
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
                                                        <p class="mb-1 text-dark">
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
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Đóng</button>
                </div>
                @endif
            </div>
    </div>

    {{-- Assignment Modal --}}
    <div wire:ignore.self class="modal fade" id="assignModalReport" tabindex="-1" aria-labelledby="assignModalReportLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fw-bold" id="assignModalReportLabel">
                        <i class="fa-solid fa-user-check me-1"></i> Giao việc hợp đồng
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Chọn nhân viên để giao việc (có thể chọn nhiều):</p>
                    <div class="list-group mh-320-scroll overflow-y-auto" style="max-height: 250px;">
                        @foreach ($assignable_users as $u)
                            <label class="list-group-item list-group-item-action d-flex gap-2 align-items-center py-2 px-3">
                                <input class="form-check-input flex-shrink-0" type="checkbox"
                                    value="{{ $u->id }}" wire:model="assignUserIds">
                                <div class="min-w-0">
                                    <div class="fw-semibold text-sm text-dark">{{ $u->name }}</div>
                                    <small class="text-muted text-xs">{{ $this->roleDisplayFromSlug($u->roles->first()?->name ?? '') }}</small>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <label class="form-label small fw-semibold text-dark">Người ngoài công ty</label>
                        <input type="text" class="form-control form-control-sm" wire:model="assignExternal"
                            placeholder="Tên người ngoài (nếu có)">
                    </div>
                    <div class="mt-3">
                        <label class="form-label small fw-semibold text-dark">Hạn chót</label>
                        <input type="date" class="form-control form-control-sm" wire:model="assignDeadline">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-success btn-sm px-3" wire:click="saveAssign"
                        wire:loading.attr="disabled" wire:target="saveAssign">
                        <span wire:loading wire:target="saveAssign" class="spinner-border spinner-border-sm me-1"></span>
                        Lưu giao việc
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.addEventListener('open-detail-modal', () => {
                let modal = new bootstrap.Modal(document.getElementById('detailModal'));
                modal.show();
            });
            window.addEventListener('openAssignModal', () => {
                new bootstrap.Modal(document.getElementById('assignModalReport')).show();
            });
            Livewire.on('closeAssignModal', () => {
                let modalElement = document.getElementById('assignModalReport');
                let modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
            });
        </script>
    @endpush
</div>
