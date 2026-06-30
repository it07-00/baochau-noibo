<div class="sales-target-registration">
    {{-- Page header --}}
    <div class="page-header mb-4">
        <h4 class="mb-1 fw-bold">Bảng doanh số cam kết</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                <li class="breadcrumb-item">Báo cáo kinh doanh</li>
                <li class="breadcrumb-item active">Bảng doanh số cam kết</li>
            </ol>
        </nav>
    </div>

    {{-- Controls bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="d-flex align-items-end flex-wrap gap-3">
                <div>
                    <label class="form-label fw-semibold mb-1 small text-muted text-uppercase">Năm</label>
                    <select wire:model.live="year" class="form-select form-select-sm" style="min-width:115px">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                @if(auth()->user()->hasAnyRole([\App\Enums\Role::IT->value, \App\Enums\Role::GIAM_DOC->value, \App\Enums\Role::TP_KINH_DOANH->value]))
                <div>
                    <label class="form-label fw-semibold mb-1 small text-muted text-uppercase">Nhân viên</label>
                    <select wire:model.live="filter_staff" class="form-select form-select-sm" style="min-width:200px;max-width:280px">
                        <option value="">Tất cả nhân viên KD</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div>
                    <label class="form-label fw-semibold mb-1 small text-muted text-uppercase">Nhân viên</label>
                    <div class="d-flex align-items-center gap-2 form-control form-control-sm bg-light" style="min-width:180px;max-width:260px">
                        <i class="fa-solid fa-user-circle text-muted flex-shrink-0"></i>
                        <span class="fw-semibold text-truncate">{{ auth()->user()->name }}</span>
                    </div>
                </div>
                @endif
                <div class="ms-auto">
                    <label class="form-label mb-1 small text-muted text-uppercase d-block">Chế độ xem</label>
                    <div class="btn-group" role="group">
                        <button type="button" wire:click="switchMode('year')"
                            class="btn btn-sm {{ $viewMode === 'year' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fa-solid fa-table"></i><span class="d-none d-sm-inline ms-1">Theo năm</span>
                        </button>
                        <button type="button" wire:click="switchMode('month')"
                            class="btn btn-sm {{ $viewMode === 'month' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fa-solid fa-calendar-week"></i><span class="d-none d-sm-inline ms-1">Chi tiết tháng</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($viewMode === 'year')
    {{-- Tổng quan nhanh --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Tổng cam kết {{ $year }}</p>
                    <div class="fs-5 fw-bold text-dark">{{ number_format($totals['target'], 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Tổng thực tế đã ký</p>
                    <div class="fs-5 fw-bold text-success">{{ number_format($totals['actual'], 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Chênh lệch so với cam kết</p>
                    <div class="fs-5 fw-bold {{ $this->totalDelta($totals) >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $this->totalDelta($totals) >= 0 ? '+' : '−' }}{{ number_format(abs($this->totalDelta($totals)), 0, ',', '.') }} đ
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Mức độ hoàn thành năm</p>
                    <div class="fs-5 fw-bold {{ $this->pctTextClass($this->totalPct($totals)) }}">
                        {{ $this->totalPct($totals) !== null ? $this->totalPct($totals) . '%' : '—' }}
                    </div>
                    @if($this->totalPct($totals) !== null)
                        <div class="progress mt-2 h-6px">
                            <div class="progress-bar {{ $this->monthMetrics($totals)['progressClass'] }}"
                                style="width:{{ $this->monthMetrics($totals)['progressWidth'] }}%"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng mục tiêu --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <h6 class="mb-0 fw-bold">Chi tiết cam kết theo tháng — {{ $year }}</h6>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">Đạt</span>
                <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1">Gần đạt</span>
                <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">Chưa đạt</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0 sales-target-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 text-nowrap" style="width: 150px;">Tháng</th>
                            <th class="text-end text-nowrap" style="width: 150px;">Mục tiêu (đ)</th>
                            <th class="text-end text-nowrap" style="width: 250px;">Thực tế (Doanh số từ HĐ) (đ)</th>
                            <th class="text-end text-nowrap" style="width: 220px;">Doanh số chưa chắc chắn (đ)</th>
                            <th class="text-end text-nowrap" style="width: 150px;">Chênh lệch (đ)</th>
                            <th class="text-nowrap" style="min-width: 220px;">Tiến độ</th>
                            <th class="text-center text-nowrap" style="width: 130px;">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($months as $m => $data)
                            <tr class="{{ $year === now()->year && $m === now()->month ? 'table-primary bg-opacity-25' : ($this->monthMetrics($data)['target'] == 0 ? 'table-light' : '') }} cursor-pointer"
                                 wire:click="openDetail({{ $m }})">
                                <td class="ps-3 text-nowrap">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($year === now()->year && $m === now()->month)
                                            <span class="badge bg-primary rounded-pill" style="font-size:0.6rem">Hiện tại</span>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">Tháng {{ $m }}</div>
                                            <small class="text-muted">Quý {{ (int) ceil($m / 3) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold {{ $this->monthMetrics($data)['target'] > 0 ? 'text-dark' : 'text-muted' }}">
                                    {{ $this->monthMetrics($data)['target'] > 0 ? number_format($this->monthMetrics($data)['target'], 0, ',', '.') : '—' }}
                                </td>
                                <td class="text-end fw-semibold {{ $this->monthMetrics($data)['actual'] > 0 ? 'text-success' : 'text-muted' }}">
                                    {{ $this->monthMetrics($data)['actual'] > 0 ? number_format($this->monthMetrics($data)['actual'], 0, ',', '.') : '—' }}
                                </td>
                                <td class="text-end">
                                    @if($data['potential'] > 0)
                                        <button type="button"
                                            class="btn btn-link btn-sm p-0 fw-semibold text-warning text-decoration-none"
                                            wire:click.stop="openPotentialDetail({{ $m }})">
                                            {{ number_format($data['potential'], 0, ',', '.') }}
                                        </button>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold {{ $this->monthMetrics($data)['delta'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $this->monthMetrics($data)['target'] > 0 || $this->monthMetrics($data)['actual'] > 0 ? ($this->monthMetrics($data)['delta'] >= 0 ? '+' : '−') . number_format(abs($this->monthMetrics($data)['delta']), 0, ',', '.') : '—' }}
                                </td>
                                <td>
                                    @if($this->monthMetrics($data)['pct'] !== null)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1 h-8px" >
                                                <div class="progress-bar {{ $this->monthMetrics($data)['progressClass'] }}" role="progressbar" style="width: {{ $this->monthMetrics($data)['progressWidth'] }}%"></div>
                                            </div>
                                            <span class=" fw-semibold {{ $this->pctTextClass($this->monthMetrics($data)['pct']) }} mnw-46px text-end" >
                                                {{ $this->monthMetrics($data)['pct'] }}%
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-muted ">Chưa đặt mục tiêu</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($this->monthMetrics($data)['pct'] === null)
                                        <span class="badge bg-soft-secondary text-secondary ">Chưa có mục tiêu</span>
                                    @elseif($this->monthMetrics($data)['pct'] >= 100)
                                        <span class="badge bg-soft-success text-success ">Đạt</span>
                                    @elseif($this->monthMetrics($data)['pct'] >= 70)
                                        <span class="badge bg-soft-warning text-warning ">Gần đạt</span>
                                    @else
                                        <span class="badge bg-soft-danger text-danger ">Chưa đạt</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td class="ps-3 text-nowrap">Tổng năm {{ $year }}</td>
                            <td class="text-end text-nowrap">{{ number_format($totals['target'], 0, ',', '.') }} đ</td>
                            <td class="text-end text-success text-nowrap">{{ number_format($totals['actual'], 0, ',', '.') }} đ</td>
                            <td class="text-end text-warning text-nowrap">{{ number_format($totals['potential'], 0, ',', '.') }} đ</td>
                            <td class="text-end text-nowrap {{ $this->totalDelta($totals) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $this->totalDelta($totals) >= 0 ? '+' : '−' }}{{ number_format(abs($this->totalDelta($totals)), 0, ',', '.') }} đ
                            </td>
                            <td class="text-nowrap">
                                @if($this->totalPct($totals) !== null)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1 h-8px" >
                                            <div class="progress-bar {{ $this->monthMetrics($totals)['progressClass'] }}" role="progressbar" style="width: {{ $this->monthMetrics($totals)['progressWidth'] }}%"></div>
                                        </div>
                                        <span class=" fw-semibold {{ $this->pctTextClass($this->totalPct($totals)) }} mnw-46px text-end" >
                                            {{ $this->totalPct($totals) }}%
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                @if($this->totalPct($totals) === null)
                                    <span class="badge bg-soft-secondary text-secondary ">Chưa có mục tiêu</span>
                                @elseif($this->totalPct($totals) >= 100)
                                    <span class="badge bg-soft-success text-success ">Đạt</span>
                                @elseif($this->totalPct($totals) >= 70)
                                    <span class="badge bg-soft-warning text-warning ">Gần đạt</span>
                                @else
                                    <span class="badge bg-soft-danger text-danger ">Chưa đạt</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($viewMode === 'month')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning bg-opacity-10 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="mb-0 fw-bold">CAM KẾT DOANH SỐ THÁNG {{ str_pad($viewMonth, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</h6>
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 fw-semibold">Tháng:</label>
                    <select wire:model.live="viewMonth" class="form-select form-select-sm" style="width:auto">
                        @for($mi = 1; $mi <= $maxMonth; $mi++)
                            <option value="{{ $mi }}">Tháng {{ str_pad($mi, 2, '0', STR_PAD_LEFT) }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-warning">
                            <tr class="text-center fw-bold small text-nowrap">
                                <th>TÊN NHÂN VIÊN</th>
                                <th>DOANH SỐ MỤC TIÊU</th>
                                <th>DOANH SỐ ĐÃ VỀ</th>
                                <th>BÁO GIÁ TIỀM NĂNG</th>
                                <th>DOANH SỐ TÌM MỚI (CÒN THIẾU)</th>
                                <th>TỶ LỆ HOÀN THÀNH</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-center text-nowrap">
                                <td class="fw-bold">{{ $selectedStaffName }}</td>
                                <td>{{ number_format($monthTarget, 0, ',', '.') }} đ</td>
                                <td class="text-success fw-bold">{{ number_format($monthActual, 0, ',', '.') }} đ</td>
                                <td class="text-warning fw-bold">{{ number_format($monthPotential, 0, ',', '.') }} đ</td>
                                <td class="text-danger fw-bold">{{ number_format($monthRemain, 0, ',', '.') }} đ</td>
                                <td>
                                    @if($monthPct !== null)
                                        <span class="fw-bold {{ $this->pctTextClass($monthPct) }}">{{ $monthPct }}%</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-light py-3">
                <h6 class="mb-0 fw-bold">CỤ THỂ DOANH SỐ THÁNG {{ str_pad($viewMonth, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-xs">
                        <thead class="table-light">
                            <tr class="fw-bold small text-center">
                                <th class="ps-3 text-start">TÊN CÔNG TY</th>
                                <th class="text-start">DỊCH VỤ</th>
                                <th>GIÁ TRỊ HỢP ĐỒNG</th>
                                <th>PTTT</th>
                                <th>CHẮC CHẮN (ĐÃ VỀ)</th>
                                <th class="pe-3">TÌNH HÌNH</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detail as $row)
                                <tr class="text-center">
                                    <td class="ps-3 fw-semibold text-start">{{ $row['customer'] }}</td>
                                    <td class="text-muted small text-start">{{ $row['service'] }}</td>
                                    <td class="text-success fw-semibold">
                                        {{ $row['contract_value'] > 0 ? number_format($row['contract_value'], 0, ',', '.') : '—' }}
                                    </td>
                                    <td>{{ $row['payment_method'] ?: '—' }}</td>
                                    <td class="text-success fw-semibold">
                                        {{ $row['value'] > 0 ? number_format($row['value'], 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="pe-3 small text-muted">{{ $row['notes'] ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Không có hợp đồng nào trong tháng này.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(!empty($detail))
                            <tfoot class="table-secondary fw-bold">
                                <tr class="text-center">
                                    <td colspan="2" class="ps-3 text-start">Tổng tháng {{ $viewMonth }}/{{ $year }}</td>
                                    <td class="text-success">{{ number_format(array_sum(array_column($detail, 'contract_value')), 0, ',', '.') }}</td>
                                    <td></td>
                                    <td class="text-success">{{ number_format(array_sum(array_column($detail, 'value')), 0, ',', '.') }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-warning bg-opacity-10 py-3">
                <h6 class="mb-0 fw-bold text-warning-emphasis">CỤ THỂ BÁO GIÁ TIỀM NĂNG THÁNG {{ str_pad($viewMonth, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-xs">
                        <thead class="table-light">
                            <tr class="fw-bold small text-center text-nowrap">
                                <th class="ps-3 text-start" style="width: 50px;">STT</th>
                                <th class="text-start">TÊN CÔNG TY</th>
                                <th class="text-start">DỊCH VỤ</th>
                                <th>NHÂN VIÊN KD</th>
                                <th>NGUỒN</th>
                                <th class="text-end">GIÁ TRỊ BÁO GIÁ CHƯA VAT (Đ)</th>
                                <th>NGÀY DỰ KIẾN KÝ</th>
                                <th class="pe-3">TÌNH HÌNH / GHI CHÚ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($potentialDetail as $index => $row)
                                <tr class="text-center text-nowrap">
                                    <td class="ps-3 text-start text-muted">{{ $index + 1 }}</td>
                                    <td class="fw-semibold text-start">{{ $row['company'] }}</td>
                                    <td class="text-muted small text-start">{{ $row['service'] }}</td>
                                    <td>{{ $row['staff'] }}</td>
                                    <td>{{ $row['source'] ?: '—' }}</td>
                                    <td class="text-end text-warning fw-semibold">
                                        {{ $row['value'] > 0 ? number_format($row['value'], 0, ',', '.') : '—' }} đ
                                    </td>
                                    <td>{{ $row['date'] }}</td>
                                    <td class="pe-3 small text-muted text-start">{{ $row['notes'] ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Không có báo giá tiềm năng nào trong tháng này.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(!empty($potentialDetail))
                            <tfoot class="table-secondary fw-bold">
                                <tr class="text-center text-nowrap">
                                    <td colspan="5" class="ps-3 text-start">Tổng tháng {{ $viewMonth }}/{{ $year }}</td>
                                    <td class="text-end text-warning">
                                        {{ number_format(array_sum(array_column($potentialDetail, 'value')), 0, ',', '.') }} đ
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal chi tiết hợp đồng theo tháng --}}
    <div wire:ignore.self class="modal fade" id="targetDetailModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary py-3">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fa-solid fa-calendar-days me-2"></i>
                        Chi tiết hợp đồng tháng {{ $filter_month }}/{{ $year }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if(empty($detail))
                        <div class="text-center text-muted py-5">
                            <i class="fa-solid fa-inbox d-block mb-3 text-muted" style="font-size: 3.5rem;"></i>
                            Không có hợp đồng nào
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="text-center w-50px" >STT</th>
                                        <th>Tên khách hàng</th>
                                        <th>Loại hợp đồng</th>
                                        <th>Nhân viên KD</th>
                                        <th class="text-end">Doanh số (đ)</th>
                                        <th class="text-center w-110px" >Loại</th>
                                        <th class="text-center w-130px" >Ngày xuất HĐ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detail as $i => $row)
                                        <tr>
                                            <td class="text-center text-muted">{{ $i + 1 }}</td>
                                            <td class="fw-semibold">{{ $row['customer'] }}</td>
                                            <td class="text-muted">{{ $row['type'] }}</td>
                                            <td class="text-muted">{{ $row['staff'] }}</td>
                                            <td class="text-end fw-semibold">{{ number_format($row['value'], 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @if($row['is_renewal'])
                                                    <span class="badge bg-success-subtle text-success">Tái ký</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning">HĐ mới</span>
                                                @endif
                                            </td>
                                            <td class="text-center text-muted">{{ $row['date'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="4" class="text-end">Tổng tháng {{ $filter_month }}</td>
                                        <td class="text-end">{{ number_format(array_sum(array_column($detail, 'value')), 0, ',', '.') }} đ</td>
                                        <td colspan="2" class="text-center text-muted">{{ count($detail) }} hợp đồng</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal danh sách báo giá tiềm năng theo tháng --}}
    <div wire:ignore.self class="modal fade" id="potentialDetailModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-bolt-charge me-2"></i>
                        Báo giá tiềm năng tháng {{ $filter_month }}/{{ $year }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if(empty($potentialDetail))
                        <div class="text-center text-muted py-5">Không có báo giá tiềm năng</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="text-center">STT</th>
                                        <th>Công ty</th>
                                        <th>Dịch vụ</th>
                                        <th>Nhân viên KD</th>
                                        <th>Nguồn</th>
                                        <th class="text-end">Giá trị báo giá chưa VAT (đ)</th>
                                        <th class="text-center">Ngày dự kiến ký</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($potentialDetail as $i => $row)
                                        <tr>
                                            <td class="text-center text-muted">{{ $i + 1 }}</td>
                                            <td class="fw-semibold">{{ $row['company'] }}</td>
                                            <td>{{ $row['service'] }}</td>
                                            <td>{{ $row['staff'] }}</td>
                                            <td>{{ $row['source'] }}</td>
                                            <td class="text-end fw-semibold text-warning">{{ number_format($row['value'], 0, ',', '.') }}</td>
                                            <td class="text-center">{{ $row['date'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="5" class="text-end">Tổng tháng {{ $filter_month }}</td>
                                        <td class="text-end text-warning">{{ number_format(array_sum(array_column($potentialDetail, 'value')), 0, ',', '.') }} đ</td>
                                        <td class="text-center text-muted">{{ count($potentialDetail) }} báo giá</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('openDetailModal', () => {
                new bootstrap.Modal(document.getElementById('targetDetailModal')).show();
            });
            Livewire.on('openPotentialDetailModal', () => {
                new bootstrap.Modal(document.getElementById('potentialDetailModal')).show();
            });
        });
    </script>
</div>
