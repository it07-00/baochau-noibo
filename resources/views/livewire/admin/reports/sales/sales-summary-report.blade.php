<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Bảng tổng kết doanh số</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Bảng tổng kết doanh số</li>
                </ol>
            </nav>
        </div>
    </div>

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
                    <label class="form-label fw-semibold mb-1 small">Nhân viên</label>
                    <select wire:model.live="filter_staff" class="form-select form-select-sm">
                        <option value="">Tất cả nhân viên</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng tổng kết --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold">Tổng kết doanh số năm {{ $year }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tháng</th>
                            <th class="text-end">DS Báo giá</th>
                            <th class="text-end">DS Tái ký</th>
                            <th class="text-end">DS Theo tiến độ</th>
                            <th class="text-end fw-bold">Tổng tháng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($months as $m => $data)
                            @php $rowTotal = $data['quotation'] + $data['renewal'] + $data['progressive']; @endphp
                            <tr class="{{ $rowTotal > 0 ? '' : 'text-muted' }}">
                                <td class="fw-semibold">Tháng {{ $m }}</td>
                                <td class="text-end">
                                    @if($data['quotation'] > 0)
                                        <span class="text-primary">{{ number_format($data['quotation'], 0, ',', '.') }}</span>
                                    @else —
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($data['renewal'] > 0)
                                        <span class="text-success">{{ number_format($data['renewal'], 0, ',', '.') }}</span>
                                    @else —
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($data['progressive'] > 0)
                                        <span class="text-warning">{{ number_format($data['progressive'], 0, ',', '.') }}</span>
                                    @else —
                                    @endif
                                </td>
                                <td class="text-end fw-bold">
                                    {{ $rowTotal > 0 ? number_format($rowTotal, 0, ',', '.') . ' đ' : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td>Tổng năm {{ $year }}</td>
                            <td class="text-end text-primary">{{ number_format($totals['quotation'], 0, ',', '.') }} đ</td>
                            <td class="text-end text-success">{{ number_format($totals['renewal'], 0, ',', '.') }} đ</td>
                            <td class="text-end text-warning">{{ number_format($totals['progressive'], 0, ',', '.') }} đ</td>
                            <td class="text-end fs-6">{{ number_format($totals['grand'], 0, ',', '.') }} đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Tóm tắt card --}}
    <div class="row g-3 mt-2">
        <div class="col-md-3">
            <div class="card border-0 bg-soft-primary text-primary h-100">
                <div class="card-body">
                    <div class="small fw-semibold mb-1">DS Báo giá</div>
                    <div class="fw-bold fs-6">{{ number_format($totals['quotation'], 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-soft-success text-success h-100">
                <div class="card-body">
                    <div class="small fw-semibold mb-1">DS Tái ký</div>
                    <div class="fw-bold fs-6">{{ number_format($totals['renewal'], 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-soft-warning text-warning h-100">
                <div class="card-body">
                    <div class="small fw-semibold mb-1">DS Theo tiến độ</div>
                    <div class="fw-bold fs-6">{{ number_format($totals['progressive'], 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-soft-dark text-dark h-100">
                <div class="card-body">
                    <div class="small fw-semibold mb-1">Tổng doanh số</div>
                    <div class="fw-bold fs-6">{{ number_format($totals['grand'], 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
    </div>
</div>
