<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">{{ $page_title }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">{{ $page_title }}</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Tóm tắt --}}
    @if($summary)
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="small text-muted">Tổng hợp đồng</div>
                    <div class="fw-bold fs-4 text-primary">{{ $summary->total ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="small text-muted">Đang thực hiện</div>
                    <div class="fw-bold fs-4 text-info">{{ $summary->active ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="small text-muted">Hoàn thành</div>
                    <div class="fw-bold fs-4 text-success">{{ $summary->completed ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 small">Năm</label>
                    <select wire:model.live="year" class="form-select form-select-sm">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1 small">Loại dịch vụ</label>
                    <select wire:model.live="filter_service" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        @foreach($serviceTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 small">Trạng thái</label>
                    <select wire:model.live="filter_status" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        <option value="not_started">Chưa bắt đầu</option>
                        <option value="in_progress">Đang thực hiện</option>
                        <option value="finished">Đã hoàn thành</option>
                        <option value="ĐÃ HỦY">Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1 small">NV Tư vấn</label>
                    <select wire:model.live="filter_staff" class="form-select form-select-sm">
                        @unless($isRestrictedConsultant)
                            <option value="">Tất cả</option>
                        @endunless
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Danh sách --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width:45px;">STT</th>
                            <th>Số HĐ</th>
                            <th>Khách hàng</th>
                            <th>Loại dịch vụ</th>
                            <th>NV kinh doanh</th>
                            <th>NV tư vấn</th>
                            <th>Tỉnh/TP</th>
                            <th style="min-width:180px">Tiến trình</th>
                            <th>Bước hiện tại</th>
                            <th>Ngày ký</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        @php $wp = $workflowProgress[$item->id] ?? ['percent' => 0, 'current_label' => 'Chưa bắt đầu', 'completed_count' => 0, 'total_steps' => 6]; @endphp
                        <tr>
                            <td class="text-center text-muted small fw-semibold">{{ ($items->currentPage() - 1) * $items->perPage() + $loop->iteration }}</td>
                            <td class="fw-semibold small">{{ $item->shd_bc ?: '—' }}</td>
                            <td>{{ $item->customer?->name ?? '—' }}</td>
                            <td class="small text-muted" style="max-width:180px;">{{ $item->loai_dich_vu ?: '—' }}</td>
                            <td class="small">{{ $item->staff?->name ?? '—' }}</td>
                            <td class="small">{{ $item->assignments->pluck('user.name')->filter()->implode(', ') ?: '—' }}</td>
                            <td class="small">{{ $item->province ?: '—' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:8px">
                                        <div class="progress-bar {{ $wp['percent'] == 100 ? 'bg-success' : 'bg-primary' }}"
                                             role="progressbar" style="width:{{ $wp['percent'] }}%"
                                             aria-valuenow="{{ $wp['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <span class="small text-muted" style="white-space:nowrap">{{ $wp['completed_count'] }}/{{ $wp['total_steps'] }}</span>
                                </div>
                            </td>
                            <td class="small">{{ $wp['current_label'] }}</td>
                            <td class="small text-muted">{{ $item->signed_at?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                <span class="badge bg-soft-{{ $item->status_color ?? 'secondary' }} text-{{ $item->status_color ?? 'secondary' }} small">
                                    {{ $item->status_label ?? $item->status ?? '—' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="11" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $items->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
