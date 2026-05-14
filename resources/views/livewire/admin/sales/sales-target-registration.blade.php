<div class="sales-target-registration">

    {{-- ── Page header ────────────────────────────────────────────── --}}
    <div class="page-header mb-4">
        <h4 class="mb-1 fw-bold">Đăng ký mục tiêu doanh số</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                <li class="breadcrumb-item active">Đăng ký mục tiêu doanh số</li>
            </ol>
        </nav>
    </div>

    {{-- ── Controls bar ───────────────────────────────────────────── --}}
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
                <div>
                    <label class="form-label fw-semibold mb-1 small text-muted text-uppercase">Nhân viên</label>
                    @if($isTpkd)
                        <select wire:model.live="selectedStaffId" class="form-select form-select-sm" style="min-width:200px;max-width:280px">
                            <option value="{{ auth()->id() }}">{{ auth()->user()->name }} (tôi)</option>
                            @foreach($staffList as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="d-flex align-items-center gap-2 form-control form-control-sm bg-light" style="min-width:180px;max-width:260px">
                            <i class="bi bi-person-circle text-muted flex-shrink-0"></i>
                            <span class="fw-semibold text-truncate">{{ auth()->user()->name }}</span>
                        </div>
                    @endif
                </div>
                <div class="ms-auto d-flex align-items-end gap-2">
                    <div>
                        <label class="form-label mb-1 small text-muted text-uppercase d-block">Chế độ xem</label>
                        <div class="btn-group" role="group">
                            <button type="button" wire:click="switchMode('year')"
                                class="btn btn-sm {{ $viewMode === 'year' ? 'btn-primary' : 'btn-outline-primary' }}">
                                <i class="bi bi-table me-1"></i>Theo năm
                            </button>
                            <button type="button" wire:click="switchMode('month')"
                                class="btn btn-sm {{ $viewMode === 'month' ? 'btn-primary' : 'btn-outline-primary' }}">
                                <i class="bi bi-calendar2-week me-1"></i>Chi tiết tháng
                            </button>
                        </div>
                    </div>
                    <button wire:click="saveTargets"
                        wire:loading.attr="disabled" wire:target="saveTargets"
                        @if($isTpkd && $selectedStaffId !== auth()->id()) disabled title="Không thể lưu cam kết của nhân viên khác" @endif
                        class="btn btn-sm {{ $isTpkd && $selectedStaffId !== auth()->id() ? 'btn-secondary' : 'btn-primary' }} d-flex align-items-center gap-2 px-3 fw-semibold">
                        <span wire:loading wire:target="saveTargets" class="spinner-border spinner-border-sm"></span>
                        <i wire:loading.remove wire:target="saveTargets" class="bi bi-floppy"></i>
                        Lưu cam kết
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── KPI cards ──────────────────────────────────────────────── --}}
    @if($viewMode === 'year')
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Tổng cam kết {{ $year }}</p>
                        <div class="fs-5 fw-bold">{{ number_format($totals['target'], 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Tổng thực tế đã ký</p>
                        <div class="fs-5 fw-bold text-success">{{ number_format($totals['actual'], 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Còn thiếu để đạt năm</p>
                        <div class="fs-5 fw-bold text-danger">{{ number_format($remaining, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Mức độ hoàn thành năm</p>
                        <div class="fs-5 fw-bold {{ $totalPct !== null && $totalPct >= 100 ? 'text-success' : ($totalPct !== null && $totalPct >= 70 ? 'text-warning' : 'text-danger') }}">
                            {{ $totalPct !== null ? $totalPct.'%' : '—' }}
                        </div>
                        @if($totalPct !== null)
                            <div class="progress mt-2 h-6px">
                                <div class="progress-bar {{ $totalPct >= 100 ? 'bg-success' : ($totalPct >= 70 ? 'bg-warning' : 'bg-danger') }}"
                                    style="width:{{ min($totalPct, 100) }}%"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Cam kết tháng {{ $viewMonth }}</p>
                        <div class="fs-5 fw-bold">{{ number_format($monthTarget, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Đã về tháng {{ $viewMonth }}</p>
                        <div class="fs-5 fw-bold text-success">{{ number_format($monthActual, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Còn thiếu tháng {{ $viewMonth }}</p>
                        <div class="fs-5 fw-bold text-danger">{{ number_format($monthRemain, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Tỷ lệ hoàn thành</p>
                        <div class="fs-5 fw-bold {{ $monthPct !== null && $monthPct >= 100 ? 'text-success' : ($monthPct !== null && $monthPct >= 70 ? 'text-warning' : 'text-danger') }}">
                            {{ $monthPct !== null ? $monthPct.'%' : '—' }}
                        </div>
                        @if($monthPct !== null)
                            <div class="progress mt-2 h-6px">
                                <div class="progress-bar {{ $monthPct >= 100 ? 'bg-success' : ($monthPct >= 70 ? 'bg-warning' : 'bg-danger') }}"
                                    style="width:{{ min($monthPct, 100) }}%"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- MODE: THEO NĂM                                              --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    @if($viewMode === 'year')
        <div class="card border-0 shadow-sm overflow-hidden">
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
                                <th class="ps-3">Tháng</th>
                                <th class="text-end">Cam kết (đ)</th>
                                <th class="text-end">Thực tế đã ký (đ)</th>
                                <th class="text-end">Chênh lệch (đ)</th>
                                <th class="text-end">% Đạt</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center pe-3">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($months as $m => $data)
                                <tr class="{{ $data['isCurrent'] ? 'table-primary bg-opacity-25' : '' }}">
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($data['isCurrent'])
                                                <span class="badge bg-primary rounded-pill" style="font-size:0.6rem">Hiện tại</span>
                                            @endif
                                            <div>
                                                <div class="fw-semibold">Tháng {{ $m }}</div>
                                                <small class="text-muted">Quý {{ $data['q'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="input-group mxw-220px ms-auto">
                                            <input type="text" wire:model.blur="targets.{{ $m }}"
                                                class="form-control text-end fw-semibold money-input"
                                                placeholder="0"
                                                @if($isTpkd && $selectedStaffId !== auth()->id()) readonly @endif>
                                            <span class="input-group-text fw-semibold">đ</span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-semibold {{ $data['a'] > 0 ? 'text-success' : 'text-muted' }}">
                                        {{ $data['a'] > 0 ? number_format($data['a'], 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="text-end fw-semibold {{ $data['v'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        @if($data['t'] > 0 || $data['a'] > 0)
                                            {{ $data['v'] >= 0 ? '+' : '-' }}{{ number_format(abs($data['v']), 0, ',', '.') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($data['p'] !== null)
                                            <div class="fw-semibold {{ $data['p'] >= 100 ? 'text-success' : ($data['p'] >= 70 ? 'text-warning' : 'text-danger') }}">{{ $data['p'] }}%</div>
                                            <div class="progress ms-auto h-6px mxw-140px">
                                                <div class="progress-bar {{ $data['p'] >= 100 ? 'bg-success' : ($data['p'] >= 70 ? 'bg-warning' : 'bg-danger') }}"
                                                    style="width:{{ min($data['p'], 100) }}%"></div>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($data['p'] === null)
                                            <span class="badge bg-secondary-subtle text-body border border-secondary-subtle">Chưa cam kết</span>
                                        @elseif($data['p'] >= 100)
                                            <span class="badge bg-success bg-opacity-10 text-success">Đạt</span>
                                        @elseif($data['p'] >= 70)
                                            <span class="badge bg-warning bg-opacity-10 text-warning">Gần đạt</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger">Chưa đạt</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3">
                                        <button type="button" wire:click="viewMonthDetail({{ $m }})"
                                            class="btn btn-sm p-0 text-primary"
                                            title="Xem chi tiết tháng {{ $m }}">
                                            <i class="bi bi-eye fs-5"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr class="fw-bold">
                                <td class="ps-3">Tổng năm {{ $year }}</td>
                                <td class="text-end">{{ number_format($totals['target'], 0, ',', '.') }} đ</td>
                                <td class="text-end text-success">{{ number_format($totals['actual'], 0, ',', '.') }} đ</td>
                                <td class="text-end {{ ($totals['actual'] - $totals['target']) >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ ($totals['actual'] - $totals['target']) >= 0 ? '+' : '-' }}{{ number_format(abs($totals['actual'] - $totals['target']), 0, ',', '.') }}
                                </td>
                                <td class="text-end {{ $totalPct !== null && $totalPct >= 100 ? 'text-success' : ($totalPct !== null && $totalPct >= 70 ? 'text-warning' : 'text-danger') }}">
                                    {{ $totalPct !== null ? $totalPct.'%' : '—' }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- MODE: CHI TIẾT THÁNG                                        --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    @if($viewMode === 'month')

        {{-- Summary table --}}
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-warning bg-opacity-10 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="mb-0 fw-bold">CAM KẾT DOANH SỐ THÁNG {{ str_pad($viewMonth, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</h6>
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 fw-semibold">Tháng:</label>
                    <select wire:model.live="viewMonth" class="form-select form-select-sm" style="width:auto">
                        @for($mi = 1; $mi <= 12; $mi++)
                            <option value="{{ $mi }}">Tháng {{ str_pad($mi, 2, '0', STR_PAD_LEFT) }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead class="table-warning">
                        <tr class="text-center fw-bold small">
                            <th>TÊN NHÂN VIÊN</th>
                            <th>DOANH SỐ MỤC TIÊU</th>
                            <th>DOANH SỐ ĐÃ VỀ</th>
                            <th>DOANH SỐ TÌM MỚI (CÒN THIẾU)</th>
                            <th>TỶ LỆ HOÀN THÀNH</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-center">
                            <td class="fw-bold">{{ $selectedStaffName }}</td>
                            <td>{{ number_format($monthTarget, 0, ',', '.') }} đ</td>
                            <td class="text-success fw-bold">{{ number_format($monthActual, 0, ',', '.') }} đ</td>
                            <td class="text-danger fw-bold">{{ number_format($monthRemain, 0, ',', '.') }} đ</td>
                            <td>
                                @if($monthPct !== null)
                                    <span class="fw-bold {{ $monthPct >= 100 ? 'text-success' : ($monthPct >= 70 ? 'text-warning' : 'text-danger') }}">
                                        {{ $monthPct }}%
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Contract detail table --}}
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
                                <th>GIÁ TRỊ HỢP ĐỒNG<br><small class="fw-normal">(KO VAT, KO HH)</small></th>
                                <th>PTTT</th>
                                <th>CHẮC CHẮN (ĐÃ VỀ)</th>
                                <th>DỰ KIẾN (CHỜ VỀ)</th>
                                <th class="pe-3 text-center">TÌNH HÌNH</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monthContracts as $c)
                                <tr class="text-center">
                                    <td class="ps-3 fw-semibold text-start">{{ $c['customer_name'] }}</td>
                                    <td class="text-muted small text-start">{{ $c['service'] }}</td>
                                    <td class="fw-semibold">{{ number_format($c['value'], 0, ',', '.') }}</td>
                                    <td>{{ $c['payment_method'] ?: '—' }}</td>
                                    <td class="text-success fw-semibold">
                                        {{ $c['revenue'] > 0 ? number_format($c['revenue'], 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="text-warning fw-semibold">
                                        {{ $c['expected'] > 0 ? number_format($c['expected'], 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="pe-3" style="min-width:160px">
                                        @if($canEditNote)
                                            <input type="text"
                                                x-data
                                                :value="'{{ addslashes($c['notes'] ?? '') }}'"
                                                @blur="$wire.saveNote({{ $c['model_idx'] }}, {{ $c['id'] }}, $event.target.value)"
                                                class="form-control form-control-sm border-0 bg-transparent px-0 small text-muted text-center"
                                                placeholder="Nhập tình hình...">
                                        @else
                                            <span class="small text-muted">{{ $c['notes'] ?: '—' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Không có hợp đồng nào trong tháng này.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($monthContracts->isNotEmpty())
                            <tfoot class="table-secondary fw-bold">
                                <tr class="text-center">
                                    <td colspan="2" class="ps-3 text-start">Tổng tháng {{ $viewMonth }}/{{ $year }}</td>
                                    <td>{{ number_format($monthContracts->sum('value'), 0, ',', '.') }}</td>
                                    <td></td>
                                    <td class="text-success">{{ number_format($monthContracts->sum('revenue'), 0, ',', '.') }}</td>
                                    <td class="text-warning">{{ number_format($monthContracts->sum('expected'), 0, ',', '.') }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    @endif

</div>
