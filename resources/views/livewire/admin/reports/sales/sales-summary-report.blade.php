<div>
    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 ">Năm</label>
                    <select wire:model.live="year" class="form-select form-select-sm" style="font-size: 0.85rem;">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                @if(auth()->user()->hasAnyRole([\App\Enums\Role::IT->value, \App\Enums\Role::GIAM_DOC->value, \App\Enums\Role::TP_KINH_DOANH->value]))
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1 ">Nhân viên</label>
                    <select wire:model.live="filter_staff" class="form-select form-select-sm" style="font-size: 0.85rem;">
                        <option value="">Tất cả nhân viên</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Bảng tổng kết --}}
    <div class="card border-0 shadow-sm mb-4">
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
                                <td class="text-end {{ $months[$m]['renewal'] > 0 ? 'text-success' : 'text-muted' }}">
                                    @if($months[$m]['renewal'] > 0)
                                        <div>{{ number_format($months[$m]['renewal'], 0, ',', '.') }}</div>
                                        <div class="text-muted ">{{ $months[$m]['renewal_count'] }} HĐ</div>
                                    @else
                                        —
                                    @endif
                                </td>
                            @endfor
                            <td class="text-end fw-bold text-success">
                                <div>{{ number_format($totals['renewal'], 0, ',', '.') }} đ</div>
                                <div class="text-muted  fw-normal">{{ $totals['renewal_count'] }} HĐ</div>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-warning">DS HĐ mới</td>
                            @for($m = 1; $m <= 12; $m++)
                                <td class="text-end {{ $months[$m]['progressive'] > 0 ? 'text-warning' : 'text-muted' }}">
                                    @if($months[$m]['progressive'] > 0)
                                        <div>{{ number_format($months[$m]['progressive'], 0, ',', '.') }}</div>
                                        <div class="text-muted ">{{ $months[$m]['progressive_count'] }} HĐ</div>
                                    @else
                                        —
                                    @endif
                                </td>
                            @endfor
                            <td class="text-end fw-bold text-warning">
                                <div>{{ number_format($totals['progressive'], 0, ',', '.') }} đ</div>
                                <div class="text-muted  fw-normal">{{ $totals['progressive_count'] }} HĐ</div>
                            </td>
                        </tr>
                        <tr class="table-secondary">
                            <td class="fw-bold">Tổng theo hợp đồng</td>
                            @for($m = 1; $m <= 12; $m++)
                                <td class="text-end fw-bold {{ $months[$m]['contract_total'] > 0 ? 'text-dark' : 'text-muted' }}">
                                    @if($months[$m]['contract_total'] > 0)
                                        <div>{{ number_format($months[$m]['contract_total'], 0, ',', '.') }}</div>
                                        <div class="text-muted  fw-normal">{{ $months[$m]['renewal_count'] + $months[$m]['progressive_count'] }} HĐ</div>
                                    @else
                                        —
                                    @endif
                                </td>
                            @endfor
                            <td class="text-end fw-bold fs-6">
                                <div>{{ number_format($totals['contract_total'], 0, ',', '.') }} đ</div>
                                <div class="text-muted  fw-normal">{{ $totals['renewal_count'] + $totals['progressive_count'] }} HĐ</div>
                            </td>
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

    {{-- Bộ lọc tháng + Chi tiết hợp đồng --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold">
                Chi tiết doanh số
                @if($filter_month > 0)
                    tháng {{ $filter_month }}/{{ $year }}
                @else
                    — chọn tháng để xem
                @endif
            </h6>
            <div class="w-min-160px">
                <select wire:model.live="filter_month" class="form-select form-select-sm" style="font-size: 0.85rem;">
                    <option value="0">-- Chọn tháng --</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}">Tháng {{ $m }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            @if($filter_month === 0)
                <div class="text-center text-muted py-5">
                    <i class="fa-solid fa-calendar-days d-block mb-3 text-muted" style="font-size: 3.5rem;"></i>
                    Chọn tháng để xem danh sách hợp đồng
                </div>
            @elseif($detail->isEmpty())
                <div class="text-center text-muted py-5">
                    Không có hợp đồng nào trong tháng {{ $filter_month }}/{{ $year }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center w-50px" >STT</th>
                                <th>Tên khách hàng</th>
                                <th>Loại hợp đồng</th>
                                <th class="text-end">Doanh số (đ)</th>
                                <th class="text-center w-110px" >Loại</th>
                                <th class="text-center w-130px" >Ngày xuất HĐ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detail as $i => $row)
                                <tr>
                                    <td class="text-center text-muted ">{{ $i + 1 }}</td>
                                    <td class="fw-semibold">{{ $row['customer'] }}</td>
                                    <td class="text-muted ">{{ $row['type'] }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($row['value'], 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if($row['is_renewal'])
                                            <span class="badge bg-success-subtle text-success">Tái ký</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">HĐ mới</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-muted ">
                                        {{ $row['date'] ? \Carbon\Carbon::parse($row['date'])->format('d/m/Y') : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="3" class="text-end">Tổng tháng {{ $filter_month }}</td>
                                <td class="text-end">{{ number_format($detail->sum('value'), 0, ',', '.') }} đ</td>
                                <td colspan="2" class="text-center text-muted ">{{ $detail->count() }} hợp đồng</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>
