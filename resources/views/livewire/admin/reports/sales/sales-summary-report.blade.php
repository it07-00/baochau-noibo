<div>
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
            <h6 class="mb-0 fw-bold">Tổng kết doanh số theo hợp đồng năm {{ $year }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-semibold">Chỉ tiêu</th>
                            @for($m = 1; $m <= 12; $m++)
                                <th class="text-end">Tháng {{ $m }}</th>
                            @endfor
                            <th class="text-end fw-bold">Tổng năm</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-semibold text-success">DS Tái ký</td>
                            @for($m = 1; $m <= 12; $m++)
                                @php $val = $months[$m]['renewal']; @endphp
                                <td class="text-end {{ $val > 0 ? 'text-success' : 'text-muted' }}">
                                    {{ $val > 0 ? number_format($val, 0, ',', '.') : '—' }}
                                </td>
                            @endfor
                            <td class="text-end fw-bold text-success">{{ number_format($totals['renewal'], 0, ',', '.') }} đ</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-warning">DS HĐ mới</td>
                            @for($m = 1; $m <= 12; $m++)
                                @php $val = $months[$m]['progressive']; @endphp
                                <td class="text-end {{ $val > 0 ? 'text-warning' : 'text-muted' }}">
                                    {{ $val > 0 ? number_format($val, 0, ',', '.') : '—' }}
                                </td>
                            @endfor
                            <td class="text-end fw-bold text-warning">{{ number_format($totals['progressive'], 0, ',', '.') }} đ</td>
                        </tr>
                        <tr class="table-secondary">
                            <td class="fw-bold">Tổng theo hợp đồng</td>
                            @for($m = 1; $m <= 12; $m++)
                                @php $val = $months[$m]['contract_total']; @endphp
                                <td class="text-end fw-bold {{ $val > 0 ? 'text-dark' : 'text-muted' }}">
                                    {{ $val > 0 ? number_format($val, 0, ',', '.') : '—' }}
                                </td>
                            @endfor
                            <td class="text-end fw-bold fs-6">{{ number_format($totals['contract_total'], 0, ',', '.') }} đ</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="13" class="text-end">Tổng doanh số theo hợp đồng năm {{ $year }}</td>
                            <td class="text-end fs-6">{{ number_format($totals['grand'], 0, ',', '.') }} đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>
