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

    {{-- Thống kê tổng hợp --}}
    @if($summary)
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="text-muted mb-1">Tổng hợp đồng</div>
                    <div class="fw-bold fs-4 text-primary">{{ number_format($summary->total) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="text-muted mb-1">Đang thực hiện</div>
                    <div class="fw-bold fs-4 text-info">{{ number_format($summary->active) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="text-muted mb-1">Hoàn thành</div>
                    <div class="fw-bold fs-4 text-success">{{ number_format($summary->completed) }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Bộ lọc & Tìm kiếm --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1">Năm</label>
                    <select wire:model.live="year" class="form-select form-select-sm">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold mb-1">Loại hợp đồng</label>
                    <select wire:model.live="filter_contract_type" class="form-select form-select-sm">
                        <option value="all">Tất cả loại hợp đồng</option>
                        @foreach($contractTypes as $typeKey => $typeName)
                            <option value="{{ $typeKey }}">{{ $typeName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1">Trạng thái thực hiện</label>
                    <select wire:model.live="filter_status" class="form-select form-select-sm">
                        <option value="all">Tất cả trạng thái</option>
                        <option value="not_started">Chưa bắt đầu</option>
                        <option value="in_progress">Đang thực hiện</option>
                        <option value="finished">Đã hoàn thành</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1">Tìm kiếm nhanh</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Số HĐ, khách hàng, nhân viên...">
                </div>
            </div>
        </div>
    </div>

    {{-- Danh sách hợp đồng --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-muted fw-bold">
                            <th class="text-center w-45px">STT</th>
                            <th class="ps-3">Thông tin hợp đồng</th>
                            <th>Loại dịch vụ / hợp đồng</th>
                            <th class="mnw-180px">Tiến độ thực hiện</th>
                            <th class="text-center">Trạng thái HĐ</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr class="border-bottom border-light">
                            <td class="text-center text-muted fw-semibold">{{ ($items->currentPage() - 1) * $items->perPage() + $loop->iteration }}</td>
                            <td class="ps-3 py-2">
                                <div class="d-flex flex-column gap-1">
                                    <a href="{{ $item['customer_slug'] ? route('app.customers.contracts', $item['customer_slug']) : '#' }}" class="fw-bold text-primary text-decoration-none lh-sm">
                                        {{ $item['customer'] }}
                                    </a>
                                    <div class="d-flex gap-3 flex-wrap border-top mt-1 pt-1 text-secondary" style="font-size: 0.75rem;">
                                        @if($item['shd'] && $item['shd'] !== '—')<span>HĐ: <span class="fw-semibold text-dark">{{ $item['shd'] }}</span></span>@endif
                                        @if($item['signed_at'])<span>Ký: <span class="fw-semibold text-dark">{{ $item['signed_at']->format('d/m/Y') }}</span></span>@endif
                                        @if($item['staff'] && $item['staff'] !== '—')<span>CS: <span class="fw-semibold text-dark">{{ $item['staff'] }}</span></span>@endif
                                        @if($item['assigned_staff'] && $item['assigned_staff'] !== 'Chưa phân công')
                                            <span>Thực hiện: <span class="fw-semibold text-dark">{{ $item['assigned_staff'] }}</span></span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted text-wrap" style="max-width: 250px;">{{ $item['type'] }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1 h-8px" style="height: 8px;">
                                        <div class="progress-bar {{ $item['workflow_progress']['percent'] == 100 ? 'bg-success' : 'bg-primary' }}"
                                             role="progressbar" style="width: {{ $item['workflow_progress']['percent'] }}%">
                                        </div>
                                    </div>
                                    <span class="text-muted text-nowrap">{{ $item['workflow_progress']['completed_count'] }}/{{ $item['workflow_progress']['total_steps'] }}</span>
                                </div>
                                <div class="small text-muted mt-1">{{ $item['workflow_progress']['current_label'] }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-soft-{{ $item['status_color'] }} text-{{ $item['status_color'] }}">
                                    {{ $item['status_label'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" wire:click="showDetails('{{ $item['source_key'] }}', {{ $item['id'] }})" class="btn btn-sm btn-link text-primary text-decoration-none py-0">
                                    <i class="fa-solid fa-eye me-1"></i>Chi tiết
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Không tìm thấy hợp đồng nào khớp với bộ lọc
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
