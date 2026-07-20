<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Báo cáo hiện trường</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Báo cáo hiện trường kỹ thuật</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Tóm tắt --}}
    @if($summary)
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm rounded-3 bg-body">
                <div class="card-body text-center">
                    <div class=" text-muted">Tổng hợp đồng</div>
                    <div class="fw-bold fs-4 text-primary">{{ $summary->total ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm rounded-3 bg-body">
                <div class="card-body text-center">
                    <div class=" text-muted">Đang thực hiện</div>
                    <div class="fw-bold fs-4 text-info">{{ $summary->active ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm rounded-3 bg-body">
                <div class="card-body text-center">
                    <div class=" text-muted">Hoàn thành</div>
                    <div class="fw-bold fs-4 text-success">{{ $summary->completed ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm rounded-3 bg-body">
                <div class="card-body text-center">
                    <div class=" text-muted">Tổng giá trị</div>
                    <div class="fw-bold text-body ">{{ number_format($summary->total_value ?? 0, 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Bộ lọc --}}
    <div class="card border border-light-subtle shadow-sm mb-4 rounded-3 bg-body">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 ">Năm</label>
                    <select wire:model.live="year" class="form-select form-select-sm border-light-subtle">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1 ">Loại quan trắc</label>
                    <select wire:model.live="filter_service" class="form-select form-select-sm border-light-subtle">
                        <option value="">Tất cả</option>
                        @foreach($monitoringTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 ">Trạng thái</label>
                    <select wire:model.live="filter_status" class="form-select form-select-sm border-light-subtle">
                        <option value="">Tất cả</option>
                        <option value="ĐANG THỰC HIỆN">Đang thực hiện</option>
                        <option value="HOÀN THÀNH">Hoàn thành</option>
                        <option value="ĐÃ HỦY">Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1 ">Tư vấn viên</label>
                    <select wire:model.live="filter_staff" class="form-select form-select-sm border-light-subtle">
                        <option value="">Tất cả</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Danh sách --}}
    <div class="card border border-light-subtle shadow-sm rounded-3 overflow-hidden bg-body">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary border-bottom border-light-subtle">
                        <tr>
                            <th class="text-center w-45px" >STT</th>
                            <th>Số HĐ</th>
                            <th>Khách hàng</th>
                            <th>Loại quan trắc</th>
                            <th>Tư vấn viên</th>
                            <th>Tỉnh/TP</th>
                            <th class="text-end">Giá trị</th>
                            <th>Ngày ký</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="text-center text-muted  fw-semibold">{{ ($items->currentPage() - 1) * $items->perPage() + $loop->iteration }}</td>
                            <td class="fw-semibold ">{{ $item->shd_bc ?: '—' }}</td>
                            <td>{{ $item->customer?->name ?? '—' }}</td>
                            <td class=" text-muted">{{ $item->loai_dich_vu ?: '—' }}</td>
                            <td>{{ $item->consultant?->name ?? '—' }}</td>
                            <td class="">{{ $item->province ?: '—' }}</td>
                            <td class="text-end ">{{ number_format($item->value, 0, ',', '.') }}</td>
                            <td class=" text-muted">{{ $item->signed_at?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                <span class="badge bg-soft-{{ $item->status_color }} text-{{ $item->status_color }} ">
                                    {{ $item->status_label }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
            <div class="px-4 py-3 border-top border-light-subtle bg-body">
                {{ $items->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
