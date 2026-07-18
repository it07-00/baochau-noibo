<div>
    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm mb-4 rounded-12px overflow-hidden">
        <div class="card-body py-3.5 bg-white">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-auto d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-filter fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Bộ lọc báo cáo</h6>
                        <small class="text-muted">Chọn năm và nhân sự để xem số liệu</small>
                    </div>
                </div>
                <div class="col-12 col-md-3 col-lg-2 ms-md-auto">
                    <label class="form-label fw-bold mb-1 small text-secondary">Năm báo cáo</label>
                    <select wire:model.live="year" class="form-select border-light-subtle rounded-8px shadow-sm" style="font-size: 0.85rem; padding: 0.45rem 1rem;">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                @if(auth()->user()->hasAnyRole([\App\Enums\Role::IT->value, \App\Enums\Role::GIAM_DOC->value, \App\Enums\Role::TP_KINH_DOANH->value]))
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label fw-bold mb-1 small text-secondary">Nhân viên kinh doanh</label>
                    <select wire:model.live="filter_staff" class="form-select border-light-subtle rounded-8px shadow-sm" style="font-size: 0.85rem; padding: 0.45rem 1rem;">
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
    <div class="card border-0 shadow-sm mb-4 rounded-12px overflow-hidden">
        <div class="card-header bg-white py-3.5 border-bottom border-light-subtle d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-chart-line text-primary fs-5"></i>
                <h6 class="mb-0 fw-bold text-dark">Tổng kết doanh số theo hợp đồng năm {{ $year }}</h6>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light bg-opacity-70 border-bottom border-light-subtle">
                        <tr>
                            <th class="fw-bold text-secondary py-3 text-nowrap" style="font-size: 0.8rem; letter-spacing: 0.05em; padding-left: 1.25rem; min-width: 150px;">CHỈ TIÊU</th>
                            @for($m = 1; $m <= $maxMonth; $m++)
                                <th class="text-end fw-bold text-secondary py-3 text-nowrap" style="font-size: 0.8rem; letter-spacing: 0.05em; min-width: 120px;">THÁNG {{ $m }}</th>
                            @endfor
                            <th class="text-end fw-bold text-dark py-3 text-nowrap" style="font-size: 0.8rem; letter-spacing: 0.05em; padding-right: 1.25rem; min-width: 150px;">TỔNG NĂM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background-color: rgba(16, 185, 129, 0.035) !important;">
                            <td class="fw-semibold text-success py-3 text-nowrap" style="padding-left: 1.25rem;">
                                <i class="fa-solid fa-circle-arrow-up me-2 opacity-75"></i> DS Tái ký
                            </td>
                            @for($m = 1; $m <= $maxMonth; $m++)
                                <td class="text-end {{ $months[$m]['renewal'] > 0 ? 'text-success fw-semibold' : 'text-muted' }} py-3">
                                    @if($months[$m]['renewal'] > 0)
                                        <div>{{ number_format($months[$m]['renewal'], 0, ',', '.') }}</div>
                                        <div class="text-muted fw-normal" style="font-size: 0.72rem;">{{ $months[$m]['renewal_count'] }} HĐ</div>
                                    @else
                                        <span class="opacity-50">—</span>
                                    @endif
                                </td>
                            @endfor
                            <td class="text-end fw-bold text-success py-3 text-nowrap" style="padding-right: 1.25rem;">
                                <div>{{ number_format($totals['renewal'], 0, ',', '.') }} đ</div>
                                <div class="text-muted fw-normal" style="font-size: 0.72rem;">{{ $totals['renewal_count'] }} HĐ</div>
                            </td>
                        </tr>
                        <tr style="background-color: rgba(245, 158, 11, 0.035) !important;">
                            <td class="fw-semibold text-warning py-3 text-nowrap" style="padding-left: 1.25rem;">
                                <i class="fa-solid fa-circle-plus me-2 opacity-75"></i> DS HĐ mới
                            </td>
                            @for($m = 1; $m <= $maxMonth; $m++)
                                <td class="text-end {{ $months[$m]['progressive'] > 0 ? 'text-warning fw-semibold' : 'text-muted' }} py-3">
                                    @if($months[$m]['progressive'] > 0)
                                        <div>{{ number_format($months[$m]['progressive'], 0, ',', '.') }}</div>
                                        <div class="text-muted fw-normal" style="font-size: 0.72rem;">{{ $months[$m]['progressive_count'] }} HĐ</div>
                                    @else
                                        <span class="opacity-50">—</span>
                                    @endif
                                </td>
                            @endfor
                            <td class="text-end fw-bold text-warning py-3 text-nowrap" style="padding-right: 1.25rem;">
                                <div>{{ number_format($totals['progressive'], 0, ',', '.') }} đ</div>
                                <div class="text-muted fw-normal" style="font-size: 0.72rem;">{{ $totals['progressive_count'] }} HĐ</div>
                            </td>
                        </tr>
                        <tr style="background-color: rgba(37, 99, 235, 0.05) !important;">
                            <td class="fw-bold text-dark py-3 text-nowrap" style="padding-left: 1.25rem;">Tổng theo hợp đồng</td>
                            @for($m = 1; $m <= $maxMonth; $m++)
                                <td class="text-end fw-bold {{ $months[$m]['contract_total'] > 0 ? 'text-dark' : 'text-muted' }} py-3">
                                    @if($months[$m]['contract_total'] > 0)
                                        <div>{{ number_format($months[$m]['contract_total'], 0, ',', '.') }}</div>
                                        <div class="text-muted fw-normal" style="font-size: 0.72rem;">{{ $months[$m]['renewal_count'] + $months[$m]['progressive_count'] }} HĐ</div>
                                    @else
                                        <span class="opacity-50">—</span>
                                    @endif
                                </td>
                            @endfor
                            <td class="text-end fw-bold text-dark py-3 text-nowrap" style="padding-right: 1.25rem; font-size: 0.95rem;">
                                <div>{{ number_format($totals['contract_total'], 0, ',', '.') }} đ</div>
                                <div class="text-muted fw-normal" style="font-size: 0.72rem;">{{ $totals['renewal_count'] + $totals['progressive_count'] }} HĐ</div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="fw-bold border-top border-light-subtle" style="background-color: rgba(37, 99, 235, 0.09) !important;">
                        <tr>
                            <td colspan="{{ $maxMonth + 1 }}" class="text-end py-3" style="padding-left: 1.25rem; font-size: 0.9rem;">Tổng doanh số theo hợp đồng năm {{ $year }}</td>
                            <td class="text-end text-primary py-3" style="padding-right: 1.25rem; font-size: 1rem;">{{ number_format($totals['grand'], 0, ',', '.') }} đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Bộ lọc tháng + Chi tiết hợp đồng --}}
    <div class="card border-0 shadow-sm rounded-12px overflow-hidden">
        <div class="card-header bg-white py-3.5 border-bottom border-light-subtle d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-primary fs-5"></i>
                <h6 class="mb-0 fw-bold text-dark">
                    Chi tiết doanh số
                    @if($filter_month > 0)
                        tháng {{ $filter_month }}/{{ $year }}
                    @else
                        — chọn tháng để xem
                    @endif
                </h6>
            </div>
            <div class="w-min-160px">
                <select wire:model.live="filter_month" class="form-select border-light-subtle rounded-8px shadow-sm" style="font-size: 0.85rem; padding: 0.45rem 1rem;">
                    <option value="0">-- Chọn tháng --</option>
                    @for($m = 1; $m <= $maxMonth; $m++)
                        <option value="{{ $m }}">Tháng {{ $m }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            @if($filter_month === 0)
                <div class="text-center text-muted py-5 my-2">
                    <i class="fa-solid fa-calendar-days d-block mb-3 text-secondary opacity-30" style="font-size: 3.5rem;"></i>
                    <span class="fw-medium text-secondary">Chọn tháng từ bộ lọc phía trên để xem danh sách hợp đồng chi tiết</span>
                </div>
            @elseif($detail->isEmpty())
                <div class="text-center text-muted py-5 my-2">
                    <i class="fa-solid fa-folder-open d-block mb-3 text-secondary opacity-30" style="font-size: 3.5rem;"></i>
                    <span class="fw-medium text-secondary">Không có hợp đồng nào phát sinh doanh số trong tháng {{ $filter_month }}/{{ $year }}</span>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-70 border-bottom border-light-subtle">
                            <tr>
                                <th class="text-center w-60px fw-bold text-secondary py-3" style="font-size: 0.8rem; letter-spacing: 0.05em; padding-left: 1.25rem;">STT</th>
                                <th class="fw-bold text-secondary py-3" style="font-size: 0.8rem; letter-spacing: 0.05em;">TÊN KHÁCH HÀNG</th>
                                <th class="fw-bold text-secondary py-3" style="font-size: 0.8rem; letter-spacing: 0.05em;">LOẠI HỢP ĐỒNG</th>
                                <th class="text-end fw-bold text-secondary py-3" style="font-size: 0.8rem; letter-spacing: 0.05em;">DOANH SỐ (Đ)</th>
                                <th class="text-center w-120px fw-bold text-secondary py-3" style="font-size: 0.8rem; letter-spacing: 0.05em;">PHÂN LOẠI</th>
                                <th class="text-center w-140px fw-bold text-secondary py-3" style="font-size: 0.8rem; letter-spacing: 0.05em; padding-right: 1.25rem;">NGÀY XUẤT HĐ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detail as $index => $row)
                                <tr>
                                    <td class="text-center text-muted py-3" style="padding-left: 1.25rem;">{{ $index + 1 }}</td>
                                    <td class="fw-bold text-dark py-3">{{ $row['customer'] }}</td>
                                    <td class="text-secondary py-3" style="font-size: 0.88rem;">{{ $row['type'] }}</td>
                                    <td class="text-end fw-bold text-primary py-3">{{ number_format($row['value'], 0, ',', '.') }}</td>
                                    <td class="text-center py-3">
                                        @if($row['is_renewal'])
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 0.72rem;">Tái ký</span>
                                        @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 0.72rem;">HĐ mới</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-muted py-3" style="padding-right: 1.25rem;">
                                        {{ $row['date'] ? \Carbon\Carbon::parse($row['date'])->format('d/m/Y') : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="fw-bold border-top border-light-subtle" style="background-color: rgba(37, 99, 235, 0.06) !important;">
                            <tr>
                                <td colspan="3" class="text-end py-3" style="padding-left: 1.25rem; font-size: 0.9rem;">Tổng tháng {{ $filter_month }}</td>
                                <td class="text-end text-primary py-3" style="font-size: 0.95rem;">{{ number_format($detail->sum('value'), 0, ',', '.') }} đ</td>
                                <td colspan="2" class="text-center text-muted py-3" style="padding-right: 1.25rem; font-size: 0.85rem;">{{ $detail->count() }} hợp đồng</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
