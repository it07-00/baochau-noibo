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
            <div class="card border border-light-subtle shadow-sm rounded-3 bg-body">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-primary d-flex align-items-center justify-content-center icon-42" >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path></svg>
                    </div>
                    <div>
                        <div class=" text-muted">Tổng hợp đồng</div>
                        <div class="fw-bold text-primary">{{ $summary->count ?? 0 }} HĐ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border border-light-subtle shadow-sm rounded-3 bg-body">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-success d-flex align-items-center justify-content-center icon-42" >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <div>
                        <div class=" text-muted">Hoàn thành</div>
                        <div class="fw-bold text-success">{{ $summary->completed ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border border-light-subtle shadow-sm rounded-3 bg-body">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-warning d-flex align-items-center justify-content-center icon-42" >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-warning" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <div>
                        <div class=" text-muted">Tổng giá trị</div>
                        <div class="fw-bold text-warning">{{ number_format($summary->total_value ?? 0, 0, ',', '.') }} đ</div>
                    </div>
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
                <div class="col-md-4">
                    <label class="form-label fw-semibold mb-1 ">Loại dịch vụ</label>
                    <select wire:model.live="filter_service" class="form-select form-select-sm border-light-subtle">
                        <option value="">Tất cả</option>
                        @foreach($serviceTypes as $type)
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
                    <label class="form-label fw-semibold mb-1 ">Nhân viên / Tư vấn</label>
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

    {{-- Danh sách HĐ --}}
    <div class="card border border-light-subtle shadow-sm rounded-3 overflow-hidden bg-body">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary border-bottom border-light-subtle">
                        <tr>
                            <th class="text-center w-45px" >STT</th>
                            <th>Số HĐ</th>
                            <th>Khách hàng</th>
                            <th>Loại dịch vụ</th>
                            <th>NV kinh doanh</th>
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
                            <td class="fw-semibold ">{{ $item->shd_bc ?: '—' }}</td>
                            <td>{{ $item->customer?->name ?? '—' }}</td>
                            <td class=" text-muted max-w-180px" >{{ $item->loai_dich_vu ?: '—' }}</td>
                            <td class="">{{ $item->staff?->name ?? '—' }}</td>
                            <td class="">{{ $item->consultant?->name ?? '—' }}</td>
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
                        <tr><td colspan="9" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
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
