<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Báo cáo chung tư vấn</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Báo cáo Tư vấn — Chung</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 ">Năm</label>
                    <select wire:model.live="year" class="form-select form-select-sm">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold mb-1 ">Loại dịch vụ</label>
                    <select wire:model.live="filter_service" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        @foreach($serviceTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Tóm tắt theo loại dịch vụ --}}
    <div class="row g-3 mb-4">
        @foreach($byService as $svc)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class=" text-muted mb-1">{{ $svc->loai_dich_vu ?: 'Chưa phân loại' }}</div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div>
                            <div class="fw-bold fs-5 text-primary">{{ $svc->count }} HĐ</div>
                            <div class=" text-muted">{{ number_format($svc->total_value, 0, ',', '.') }} đ</div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-soft-success text-success">{{ $svc->completed }} hoàn thành</span><br>
                            <span class="badge bg-soft-info text-info mt-1">{{ $svc->active }} đang thực hiện</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Bảng theo tháng --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom py-3">
            <h6 class="mb-0 fw-bold">
                Theo dõi theo tháng — Năm {{ $year }}
                @if($filter_service) <span class="text-muted  fw-normal">/ {{ $filter_service }}</span> @endif
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tháng</th>
                            <th class="text-center">Số HĐ</th>
                            <th class="text-center text-success">Hoàn thành</th>
                            <th class="text-center text-info">Đang thực hiện</th>
                            <th class="text-end">Giá trị HĐ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthly as $m => $data)
                        <tr class="{{ $data['count'] == 0 ? 'text-muted' : '' }}">
                            <td class="fw-semibold">Tháng {{ $m }}</td>
                            <td class="text-center">{{ $data['count'] > 0 ? $data['count'] : '—' }}</td>
                            <td class="text-center text-success">{{ $data['completed'] > 0 ? $data['completed'] : '—' }}</td>
                            <td class="text-center text-info">{{ $data['active'] > 0 ? $data['active'] : '—' }}</td>
                            <td class="text-end fw-semibold">{{ $data['value'] > 0 ? number_format($data['value'], 0, ',', '.') . ' đ' : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td>Tổng năm {{ $year }}</td>
                            <td class="text-center">{{ $totals['count'] }}</td>
                            <td class="text-center text-success">{{ $totals['completed'] }}</td>
                            <td class="text-center text-info">{{ $totals['active'] }}</td>
                            <td class="text-end">{{ number_format($totals['value'], 0, ',', '.') }} đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
