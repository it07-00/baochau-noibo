<div>
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 12px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.08);
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.12);
        }
        .table-hover tbody tr {
            transition: background-color 0.2s ease;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(30, 41, 59, 0.02) !important;
            cursor: pointer;
        }
        .badge-soft-success {
            background-color: rgba(16, 185, 129, 0.1) !important;
            color: #10b981 !important;
        }
        .badge-soft-primary {
            background-color: rgba(59, 130, 246, 0.1) !important;
            color: #3b82f6 !important;
        }
        .badge-soft-warning {
            background-color: rgba(245, 158, 11, 0.1) !important;
            color: #f59e0b !important;
        }
        .badge-soft-danger {
            background-color: rgba(239, 68, 68, 0.1) !important;
            color: #ef4444 !important;
        }
        .badge-soft-info {
            background-color: rgba(6, 182, 212, 0.1) !important;
            color: #06b6d4 !important;
        }
        .badge-soft-secondary {
            background-color: rgba(100, 116, 139, 0.1) !important;
            color: #64748b !important;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

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

    {{-- Thống kê tổng hợp --}}
    @if($summary)
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 glass-card">
                <div class="card-body text-center py-4">
                    <div class="text-uppercase text-muted fs-7 fw-bold mb-1">Tổng hợp đồng</div>
                    <div class="fw-extrabold fs-2 text-primary">{{ number_format($summary->total) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 glass-card">
                <div class="card-body text-center py-4">
                    <div class="text-uppercase text-muted fs-7 fw-bold mb-1">Đang thực hiện</div>
                    <div class="fw-extrabold fs-2 text-info">{{ number_format($summary->active) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 glass-card">
                <div class="card-body text-center py-4">
                    <div class="text-uppercase text-muted fs-7 fw-bold mb-1">Đã hoàn thành</div>
                    <div class="fw-extrabold fs-2 text-success">{{ number_format($summary->completed) }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Bộ lọc & Tìm kiếm --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-1">
                    <label class="form-label fw-bold mb-1 text-muted small">Năm</label>
                    <select wire:model.live="year" class="form-select form-select-sm">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-1 text-muted small">Bộ phận</label>
                    <select wire:model.live="filter_department" class="form-select form-select-sm">
                        <option value="all">Tất cả bộ phận</option>
                        <option value="consulting">Bộ phận Tư vấn</option>
                        <option value="technical">Bộ phận Kỹ thuật</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-1 text-muted small">Loại dịch vụ / hợp đồng</label>
                    <select wire:model.live="filter_contract_type" class="form-select form-select-sm">
                        <option value="all">Tất cả loại hợp đồng</option>
                        @foreach($contractTypes as $typeKey => $typeName)
                            <option value="{{ $typeKey }}">{{ $typeName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-1 text-muted small">Tiến độ thực hiện</label>
                    <select wire:model.live="filter_status" class="form-select form-select-sm">
                        <option value="all">Tất cả trạng thái</option>
                        <option value="not_started">Chưa bắt đầu</option>
                        <option value="in_progress">Đang thực hiện</option>
                        <option value="finished">Đã hoàn thành</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold mb-1 text-muted small">Tìm kiếm nhanh</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Số HĐ, khách hàng, nhân viên...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Danh sách hợp đồng --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive custom-scrollbar">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light text-uppercase fs-7 text-muted">
                        <tr>
                            <th class="text-center w-50px px-3">STT</th>
                            <th class="px-3">Số HĐ</th>
                            <th class="px-3">Khách hàng</th>
                            <th class="px-3">Loại hợp đồng</th>
                            <th class="px-3">Bộ phận</th>
                            <th class="px-3">Kinh doanh</th>
                            <th class="px-3">Nhân sự thực hiện</th>
                            <th class="px-3 text-center">Ngày ký</th>
                            <th class="px-3" style="min-width: 180px;">Tiến độ thực hiện</th>
                            <th class="px-3 text-center">Trạng thái HĐ</th>
                            <th class="px-3 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="fs-7">
                        @forelse($items as $item)
                        <tr>
                            <td class="text-center text-muted fw-semibold px-3">{{ ($items->currentPage() - 1) * $items->perPage() + $loop->iteration }}</td>
                            <td class="fw-bold text-dark px-3">{{ $item['shd'] }}</td>
                            <td class="px-3 text-wrap" style="max-width: 280px;">{{ $item['customer'] }}</td>
                            <td class="text-muted px-3">{{ $item['type'] }}</td>
                            <td class="px-3">
                                @if($item['department'] === 'Tư vấn')
                                    <span class="badge badge-soft-primary">Tư vấn</span>
                                @elseif($item['department'] === 'Kỹ thuật')
                                    <span class="badge badge-soft-warning">Kỹ thuật</span>
                                @elseif(str_contains($item['department'], '+'))
                                    <span class="badge badge-soft-info">TV + KT</span>
                                @else
                                    <span class="badge badge-soft-secondary">{{ $item['department'] }}</span>
                                @endif
                            </td>
                            <td class="px-3">{{ $item['staff'] }}</td>
                            <td class="px-3 text-wrap text-muted" style="max-width: 220px;">{{ $item['assigned_staff'] }}</td>
                            <td class="text-center text-muted px-3">{{ $item['signed_at'] ? $item['signed_at']->format('d/m/Y') : '—' }}</td>
                            <td class="px-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1 h-6px" style="height: 6px;">
                                        <div class="progress-bar {{ $item['workflow_progress']['percent'] == 100 ? 'bg-success' : 'bg-primary' }}"
                                             role="progressbar" style="width: {{ $item['workflow_progress']['percent'] }}%">
                                        </div>
                                    </div>
                                    <span class="fw-bold text-muted text-nowrap">{{ $item['workflow_progress']['completed_count'] }}/{{ $item['workflow_progress']['total_steps'] }}</span>
                                </div>
                                <div class="fs-8 text-muted mt-1">{{ $item['workflow_progress']['current_label'] }}</div>
                            </td>
                            <td class="text-center px-3">
                                <span class="badge bg-soft-{{ $item['status_color'] }} text-{{ $item['status_color'] }} px-2 py-1">
                                    {{ $item['status_label'] }}
                                </span>
                            </td>
                            <td class="text-center px-3">
                                <button type="button" wire:click="showDetails('{{ $item['source_key'] }}', {{ $item['id'] }})" class="btn btn-xs btn-outline-primary py-1 px-2 border-0">
                                    <i class="fa-solid fa-eye me-1"></i> Xem chi tiết
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-5 fs-6">
                                <i class="fa-regular fa-folder-open fs-2 d-block mb-3 opacity-50"></i>
                                Không tìm thấy hợp đồng nào khớp với bộ lọc
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
            <div class="px-4 py-3 border-top bg-light d-flex justify-content-end">
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
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted fw-semibold">Trạng thái hiện tại:</td>
                                                    <td>
                                                        <span class="badge bg-soft-{{ $selectedContract->status_color }} text-{{ $selectedContract->status_color }} px-2 py-1">
                                                            {{ $selectedContract->status_label ?? $selectedContract->status ?? '—' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="fa-solid fa-comment-dots me-2"></i>Ghi chú hợp đồng</h6>
                                        <div class="p-3 bg-light rounded text-muted fs-7" style="white-space: pre-line;">
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
                                                        <p class="mb-1 text-dark fs-7">
                                                            <strong>Thực hiện bởi:</strong> {{ $lastRecord->user?->name ?? 'Hệ thống' }}
                                                        </p>
                                                        @if($lastRecord->comment)
                                                            <div class="bg-light p-2 rounded text-muted fs-8 mt-1 border-start border-4 border-success">
                                                                {{ $lastRecord->comment }}
                                                            </div>
                                                        @endif
                                                    @else
                                                        <p class="mb-0 text-muted fs-7">Bước này chưa được thực hiện.</p>
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
    </div>

    @push('scripts')
        <script>
            window.addEventListener('open-detail-modal', () => {
                let modal = new bootstrap.Modal(document.getElementById('detailModal'));
                modal.show();
            });
        </script>
    @endpush
</div>
