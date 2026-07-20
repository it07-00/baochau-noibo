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
    <div class="card border-0 shadow-sm mb-4 rounded-12px overflow-hidden">
        <div class="card-body py-3.5 bg-white">
            <div class="d-flex align-items-end flex-wrap gap-3">
                <div>
                    <label class="form-label fw-bold mb-1 small text-secondary">NĂM</label>
                    <select wire:model.live="year" class="form-select border-light-subtle rounded-8px shadow-sm" style="min-width:115px; font-size: 0.85rem; padding: 0.45rem 1rem;">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                @if(auth()->user()->hasAnyRole([\App\Enums\Role::IT->value, \App\Enums\Role::GIAM_DOC->value, \App\Enums\Role::TP_KINH_DOANH->value]))
                <div>
                    <label class="form-label fw-bold mb-1 small text-secondary">NHÂN VIÊN</label>
                    <select wire:model.live="filter_staff" class="form-select border-light-subtle rounded-8px shadow-sm" style="min-width:200px; max-width:280px; font-size: 0.85rem; padding: 0.45rem 1rem;">
                        <option value="">Tất cả nhân viên KD</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div>
                    <label class="form-label fw-bold mb-1 small text-secondary">NHÂN VIÊN</label>
                    <div class="d-flex align-items-center gap-2 form-control border-light-subtle rounded-8px shadow-sm bg-light" style="min-width:180px; max-width:260px; font-size: 0.85rem; padding: 0.45rem 1rem;">
                        <i class="fa-solid fa-user-circle text-muted flex-shrink-0"></i>
                        <span class="fw-semibold text-truncate text-dark">{{ auth()->user()->name }}</span>
                    </div>
                </div>
                @endif
                <div class="ms-auto">
                    <label class="form-label fw-bold mb-1 small text-secondary d-block text-md-end">CHẾ ĐỘ XEM</label>
                    <div class="btn-group shadow-sm rounded-8px overflow-hidden" role="group">
                        <button type="button" wire:click="switchMode('year')"
                            class="btn btn-sm px-3 fw-semibold d-inline-flex align-items-center {{ $viewMode === 'year' ? 'btn-primary' : 'btn-outline-secondary bg-white border-light-subtle text-secondary' }}">
                            <i class="fa-solid fa-table me-2"></i><span>Theo năm</span>
                        </button>
                        <button type="button" wire:click="switchMode('month')"
                            class="btn btn-sm px-3 fw-semibold d-inline-flex align-items-center {{ $viewMode === 'month' ? 'btn-primary' : 'btn-outline-secondary bg-white border-light-subtle text-secondary' }}">
                            <i class="fa-solid fa-calendar-week me-2"></i><span>Chi tiết tháng</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($viewMode === 'year')
    {{-- Tổng quan nhanh --}}
    <div class="row g-3 mb-4">
        @php
            $delta = $this->totalDelta($totals);
            $pct = $this->totalPct($totals);

            $deltaTheme = $delta >= 0
                ? ['bg' => 'rgba(16, 185, 129, 0.05)', 'color' => '#10b981', 'icon' => 'fa-solid fa-circle-chevron-up']
                : ['bg' => 'rgba(239, 68, 68, 0.05)', 'color' => '#ef4444', 'icon' => 'fa-solid fa-circle-chevron-down'];

            $pctTheme = $pct === null
                ? ['bg' => 'rgba(100, 116, 139, 0.05)', 'color' => '#64748b', 'icon' => 'fa-solid fa-percent']
                : ($pct >= 100
                    ? ['bg' => 'rgba(16, 185, 129, 0.05)', 'color' => '#10b981', 'icon' => 'fa-solid fa-trophy']
                    : ($pct >= 70
                        ? ['bg' => 'rgba(245, 158, 11, 0.05)', 'color' => '#f59e0b', 'icon' => 'fa-solid fa-award']
                        : ['bg' => 'rgba(239, 68, 68, 0.05)', 'color' => '#ef4444', 'icon' => 'fa-solid fa-percent']));
        @endphp

        <div class="col-6 col-xl">
            <div class="card border-0 shadow-sm h-100 rounded-12px" style="background: linear-gradient(rgba(37, 99, 235, 0.05), rgba(37, 99, 235, 0.05)), var(--bs-tertiary-bg) !important;">
                <div class="card-body p-3.5">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <span class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.02em;">Tổng cam kết {{ $year }}</span>
                        <span class="d-flex align-items-center justify-content-center rounded-circle shadow-sm flex-shrink-0" style="width: 2rem; height: 2rem; background-color: var(--bs-card-bg) !important; color: #2563eb; border: 1px solid var(--bs-border-color-translucent);"><i class="fa-solid fa-bullseye"></i></span>
                    </div>
                    <h5 class="fw-bold text-dark mb-0 text-nowrap" style="font-size: 1.12rem;">{{ number_format($totals['target'], 0, ',', '.') }}&nbsp;đ</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="card border-0 shadow-sm h-100 rounded-12px" style="background: linear-gradient(rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.05)), var(--bs-tertiary-bg) !important;">
                <div class="card-body p-3.5">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <span class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.02em;">Tổng thực tế đã ký</span>
                        <span class="d-flex align-items-center justify-content-center rounded-circle shadow-sm flex-shrink-0" style="width: 2rem; height: 2rem; background-color: var(--bs-card-bg) !important; color: #10b981; border: 1px solid var(--bs-border-color-translucent);"><i class="fa-solid fa-file-contract"></i></span>
                    </div>
                    <h5 class="fw-bold text-success mb-0 text-nowrap" style="font-size: 1.12rem;">{{ number_format($totals['actual'], 0, ',', '.') }}&nbsp;đ</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="card border-0 shadow-sm h-100 rounded-12px" style="background: linear-gradient(rgba(245, 158, 11, 0.05), rgba(245, 158, 11, 0.05)), var(--bs-tertiary-bg) !important;">
                <div class="card-body p-3.5">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <span class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.02em;">Doanh số tiềm năng</span>
                        <span class="d-flex align-items-center justify-content-center rounded-circle shadow-sm flex-shrink-0" style="width: 2rem; height: 2rem; background-color: var(--bs-card-bg) !important; color: #d97706; border: 1px solid var(--bs-border-color-translucent);"><i class="fa-solid fa-star"></i></span>
                    </div>
                    <h5 class="fw-bold text-warning mb-0 text-nowrap" style="font-size: 1.12rem;">{{ number_format($totals['potential'], 0, ',', '.') }}&nbsp;đ</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="card border-0 shadow-sm h-100 rounded-12px" style="background: linear-gradient({{ $deltaTheme['bg'] }}, {{ $deltaTheme['bg'] }}), var(--bs-tertiary-bg) !important;">
                <div class="card-body p-3.5">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <span class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.02em;">Chênh lệch cam kết</span>
                        <span class="d-flex align-items-center justify-content-center rounded-circle shadow-sm flex-shrink-0" style="width: 2rem; height: 2rem; background-color: var(--bs-card-bg) !important; color: {{ $deltaTheme['color'] }}; border: 1px solid var(--bs-border-color-translucent);"><i class="{{ $deltaTheme['icon'] }}"></i></span>
                    </div>
                    <h5 class="fw-bold mb-0 text-nowrap {{ $delta >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 1.12rem;">
                        {{ $delta >= 0 ? '+' : '−' }}{{ number_format(abs($delta), 0, ',', '.') }}&nbsp;đ
                    </h5>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl">
            <div class="card border-0 shadow-sm h-100 rounded-12px" style="background: linear-gradient({{ $pctTheme['bg'] }}, {{ $pctTheme['bg'] }}), var(--bs-tertiary-bg) !important;">
                <div class="card-body p-3.5">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <span class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.02em;">Hoàn thành năm</span>
                        <span class="d-flex align-items-center justify-content-center rounded-circle shadow-sm flex-shrink-0" style="width: 2rem; height: 2rem; background-color: var(--bs-card-bg) !important; color: {{ $pctTheme['color'] }}; border: 1px solid var(--bs-border-color-translucent);"><i class="{{ $pctTheme['icon'] }}"></i></span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h5 class="fw-bold mb-0 text-nowrap {{ $this->pctTextClass($pct) }}" style="font-size: 1.12rem;">
                            {{ $pct !== null ? $pct . '%' : '—' }}
                        </h5>
                    </div>
                    @if($pct !== null)
                        <div class="progress mt-2 h-6px bg-white bg-opacity-50">
                            <div class="progress-bar {{ $this->monthMetrics($totals)['progressClass'] }}"
                                style="width:{{ $this->monthMetrics($totals)['progressWidth'] }}%"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng mục tiêu --}}
    <div class="card border-0 shadow-sm rounded-12px overflow-hidden">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3.5 border-bottom border-light-subtle">
            <h6 class="mb-0 fw-bold text-dark">Chi tiết cam kết theo tháng — {{ $year }}</h6>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.72rem;">Đạt</span>
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.72rem;">Gần đạt</span>
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.72rem;">Chưa đạt</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table align-middle mb-0 sales-target-table table-hover">
                    <thead class="bg-light bg-opacity-70 border-bottom border-light-subtle">
                        <tr class="fw-bold text-secondary text-nowrap" style="font-size: 0.8rem; letter-spacing: 0.05em;">
                            <th class="ps-3 text-start py-3" style="min-width: 110px;">THÁNG</th>
                            <th class="text-end py-3" style="min-width: 140px;">MỤC TIÊU (Đ)</th>
                            <th class="text-end py-3" style="min-width: 180px;">THỰC TẾ ĐÃ KÝ (Đ)</th>
                            <th class="text-end py-3" style="min-width: 180px;">CHƯA CHẮC CHẮN (Đ)</th>
                            <th class="text-end py-3" style="min-width: 150px;">CHÊNH LỆCH (Đ)</th>
                            <th class="text-start py-3" style="min-width: 180px;">TIẾN ĐỘ</th>
                            <th class="text-center py-3" style="min-width: 140px;">TRẠNG THÁI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($months as $m => $data)
                            @php
                                $isCurrentMonth = ($year === now()->year && $m === now()->month);
                            @endphp
                            <tr class="cursor-pointer"
                                 @if($isCurrentMonth) style="background-color: rgba(37, 99, 235, 0.035) !important;" @endif
                                 wire:click="openDetail({{ $m }})">
                                <td class="ps-3 text-nowrap py-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold text-dark">Tháng {{ $m }}</span>
                                            @if($isCurrentMonth)
                                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill border border-primary-subtle" style="font-size: 9px; font-weight: 600; padding: 2px 6px; line-height: 1.2;">Hiện tại</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">Quý {{ (int) ceil($m / 3) }}</small>
                                    </div>
                                </td>
                                <td class="text-end fw-bold py-3 {{ $this->monthMetrics($data)['target'] > 0 ? 'text-dark' : 'text-muted' }}">
                                    {{ $this->monthMetrics($data)['target'] > 0 ? number_format($this->monthMetrics($data)['target'], 0, ',', '.') : '—' }}
                                </td>
                                <td class="text-end fw-bold py-3 {{ $this->monthMetrics($data)['actual'] > 0 ? 'text-success' : 'text-muted' }}">
                                    {{ $this->monthMetrics($data)['actual'] > 0 ? number_format($this->monthMetrics($data)['actual'], 0, ',', '.') : '—' }}
                                </td>
                                <td class="text-end py-3">
                                    @if($data['potential'] > 0)
                                        <button type="button"
                                            class="btn btn-link btn-sm p-0 fw-bold text-warning text-decoration-none"
                                            style="font-size: inherit;"
                                            wire:click.stop="openPotentialDetail({{ $m }})">
                                            {{ number_format($data['potential'], 0, ',', '.') }}
                                        </button>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold py-3 {{ $this->monthMetrics($data)['delta'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $this->monthMetrics($data)['target'] > 0 || $this->monthMetrics($data)['actual'] > 0 ? ($this->monthMetrics($data)['delta'] >= 0 ? '+' : '−') . number_format(abs($this->monthMetrics($data)['delta']), 0, ',', '.') : '—' }}
                                </td>
                                <td class="py-3">
                                    @if($this->monthMetrics($data)['pct'] !== null)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1 h-8px bg-light" style="border-radius: 4px;">
                                                <div class="progress-bar {{ $this->monthMetrics($data)['progressClass'] }}" role="progressbar" style="width: {{ $this->monthMetrics($data)['progressWidth'] }}%; border-radius: 4px;"></div>
                                            </div>
                                            <span class=" fw-bold {{ $this->pctTextClass($this->monthMetrics($data)['pct']) }} mnw-46px text-end" >
                                                {{ $this->monthMetrics($data)['pct'] }}%
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-muted ">Chưa đặt mục tiêu</span>
                                    @endif
                                </td>
                                <td class="text-center py-3 text-nowrap">
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
                    <tfoot class="fw-bold border-top border-light-subtle" style="background-color: rgba(37, 99, 235, 0.08) !important;">
                        <tr>
                            <td class="ps-3 text-nowrap py-3">Tổng năm {{ $year }}</td>
                            <td class="text-end text-nowrap py-3">{{ number_format($totals['target'], 0, ',', '.') }} đ</td>
                            <td class="text-end text-success text-nowrap py-3">{{ number_format($totals['actual'], 0, ',', '.') }} đ</td>
                            <td class="text-end text-warning text-nowrap py-3">{{ number_format($totals['potential'], 0, ',', '.') }} đ</td>
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
        <div class="card border-0 shadow-sm rounded-12px overflow-hidden">
            <div class="card-header bg-white py-3.5 border-bottom border-light-subtle d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="mb-0 fw-bold text-dark">CAM KẾT DOANH SỐ THÁNG {{ str_pad($viewMonth, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</h6>
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 fw-bold small text-secondary">THÁNG:</label>
                    <select wire:model.live="viewMonth" class="form-select border-light-subtle rounded-8px shadow-sm" style="min-width: 135px; font-size: 0.85rem; padding: 0.45rem 2.25rem 0.45rem 0.85rem;">
                        @for($mi = 1; $mi <= $maxMonth; $mi++)
                            <option value="{{ $mi }}">Tháng {{ str_pad($mi, 2, '0', STR_PAD_LEFT) }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="bg-light bg-opacity-70 border-bottom border-light-subtle">
                            <tr class="text-center fw-bold text-secondary text-nowrap" style="font-size: 0.8rem; letter-spacing: 0.05em;">
                                <th class="py-3">TÊN NHÂN VIÊN</th>
                                <th class="py-3">DOANH SỐ MỤC TIÊU</th>
                                <th class="py-3">DOANH SỐ ĐÃ VỀ</th>
                                <th class="py-3">BÁO GIÁ TIỀM NĂNG</th>
                                <th class="py-3">DOANH SỐ TÌM MỚI (CÒN THIẾU)</th>
                                <th class="py-3">TỶ LỆ HOÀN THÀNH</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-center text-nowrap">
                                <td class="fw-bold text-dark py-3">{{ $selectedStaffName }}</td>
                                <td class="py-3 fw-bold">{{ number_format($monthTarget, 0, ',', '.') }} đ</td>
                                <td class="text-success fw-bold py-3">{{ number_format($monthActual, 0, ',', '.') }} đ</td>
                                <td class="text-warning fw-bold py-3">{{ number_format($monthPotential, 0, ',', '.') }} đ</td>
                                <td class="text-danger fw-bold py-3">{{ number_format($monthRemain, 0, ',', '.') }} đ</td>
                                <td class="py-3">
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

        <div class="card border-0 shadow-sm mt-4 rounded-12px overflow-hidden">
            <div class="card-header bg-white py-3.5 border-bottom border-light-subtle">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-list-check text-primary fs-5"></i>
                    <h6 class="mb-0 fw-bold text-dark">CỤ THỂ DOANH SỐ THÁNG {{ str_pad($viewMonth, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-xs">
                        <thead class="bg-light bg-opacity-70 border-bottom border-light-subtle">
                            <tr class="fw-bold text-secondary text-center" style="font-size: 0.8rem; letter-spacing: 0.05em;">
                                <th class="ps-3 text-start py-3 text-nowrap" style="min-width: 200px;">TÊN CÔNG TY</th>
                                <th class="text-start py-3 text-nowrap" style="min-width: 140px;">DỊCH VỤ</th>
                                @if($filter_staff === '')
                                    <th class="py-3 text-nowrap" style="min-width: 140px;">NHÂN VIÊN KD</th>
                                @endif
                                <th class="py-3 text-nowrap" style="min-width: 150px;">GIÁ TRỊ HỢP ĐỒNG</th>
                                <th class="py-3 text-nowrap" style="min-width: 120px;">PTTT</th>
                                <th class="py-3 text-nowrap" style="min-width: 160px;">CHẮC CHẮN (ĐÃ VỀ)</th>
                                <th class="pe-3 py-3 text-nowrap" style="min-width: 240px;">TÌNH HÌNH</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detail as $row)
                                <tr class="text-center">
                                    <td class="ps-3 fw-bold text-start text-dark py-3" style="min-width: 200px; white-space: normal;">{{ $row['customer'] }}</td>
                                    <td class="text-start py-3 text-nowrap">
                                        <span class="badge {{ $this->getServiceBadgeClass($row['service']) }}">{{ $row['service'] }}</span>
                                    </td>
                                    @if($filter_staff === '')
                                        <td class="py-3 text-secondary text-nowrap">{{ $row['staff'] }}</td>
                                    @endif
                                    <td class="text-success fw-bold py-3 text-nowrap">
                                        {{ $row['contract_value'] > 0 ? number_format($row['contract_value'], 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="py-3 text-nowrap">
                                        @if(!empty($row['payment_methods']))
                                            <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                @foreach($row['payment_methods'] as $method)
                                                    <span class="badge {{ $this->getPaymentMethodBadgeClass($method) }}" style="font-size: 11px; font-weight: 500;">
                                                        {{ $method }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-success fw-bold py-3 text-nowrap">
                                        {{ $row['value'] > 0 ? number_format($row['value'], 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="pe-3 small text-muted text-start py-3" style="min-width: 240px; max-width: 320px; white-space: normal; word-break: break-word;">{{ $row['notes'] ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $filter_staff === '' ? 7 : 6 }}" class="text-center py-4 text-muted">Không có hợp đồng nào trong tháng này.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(!empty($detail))
                            <tfoot class="fw-bold border-top border-light-subtle" style="background-color: rgba(37, 99, 235, 0.05) !important;">
                                <tr class="text-center text-nowrap">
                                    <td colspan="{{ $filter_staff === '' ? 3 : 2 }}" class="ps-3 text-start py-3">Tổng tháng {{ $viewMonth }}/{{ $year }}</td>
                                    <td class="text-success py-3">{{ number_format(array_sum(array_column($detail, 'contract_value')), 0, ',', '.') }} đ</td>
                                    <td class="py-3"></td>
                                    <td class="text-success py-3">{{ number_format(array_sum(array_column($detail, 'value')), 0, ',', '.') }} đ</td>
                                    <td class="py-3"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4 rounded-12px overflow-hidden">
            <div class="card-header bg-white py-3.5 border-bottom border-light-subtle d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-star text-warning fs-5"></i>
                    <h6 class="mb-0 fw-bold text-dark">CỤ THỂ BÁO GIÁ TIỀM NĂNG THÁNG {{ str_pad($viewMonth, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-xs">
                        <thead class="bg-light bg-opacity-70 border-bottom border-light-subtle">
                            <tr class="fw-bold text-secondary text-center" style="font-size: 0.8rem; letter-spacing: 0.05em;">
                                <th class="ps-3 text-start py-3 text-nowrap" style="width: 60px;">STT</th>
                                <th class="text-start py-3 text-nowrap" style="min-width: 200px;">TÊN CÔNG TY</th>
                                <th class="text-start py-3 text-nowrap" style="min-width: 140px;">DỊCH VỤ</th>
                                <th class="py-3 text-nowrap" style="min-width: 140px;">NHÂN VIÊN KD</th>
                                <th class="py-3 text-nowrap" style="min-width: 120px;">NGUỒN</th>
                                <th class="text-end py-3 text-nowrap" style="min-width: 180px;">GIÁ TRỊ BÁO GIÁ CHƯA VAT (Đ)</th>
                                <th class="py-3 text-nowrap" style="min-width: 140px;">NGÀY DỰ KIẾN KÝ</th>
                                <th class="pe-3 py-3 text-nowrap" style="min-width: 240px;">TÌNH HÌNH / GHI CHÚ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($potentialDetail as $index => $row)
                                <tr class="text-center">
                                    <td class="ps-3 text-start text-muted py-3 text-nowrap">{{ $index + 1 }}</td>
                                    <td class="fw-bold text-dark text-start py-3" style="min-width: 200px; white-space: normal;">{{ $row['company'] }}</td>
                                    <td class="text-start py-3 text-nowrap">
                                        <span class="badge {{ $this->getServiceBadgeClass($row['service']) }}">{{ $row['service'] }}</span>
                                    </td>
                                    <td class="py-3 text-secondary text-nowrap">{{ $row['staff'] }}</td>
                                    <td class="py-3 text-secondary text-nowrap">{{ $row['source'] ?: '—' }}</td>
                                    <td class="text-end text-warning fw-bold py-3 text-nowrap">
                                        {{ $row['value'] > 0 ? number_format($row['value'], 0, ',', '.') : '—' }} đ
                                    </td>
                                    <td class="py-3 text-secondary text-nowrap">{{ $row['date'] }}</td>
                                    <td class="pe-3 small text-muted text-start py-3" style="min-width: 240px; max-width: 320px; white-space: normal; word-break: break-word;">{{ $row['notes'] ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Không có báo giá tiềm năng nào trong tháng này.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(!empty($potentialDetail))
                            <tfoot class="fw-bold border-top border-light-subtle" style="background-color: rgba(245, 158, 11, 0.05) !important;">
                                <tr class="text-center text-nowrap">
                                    <td colspan="5" class="ps-3 text-start py-3">Tổng tháng {{ $viewMonth }}/{{ $year }}</td>
                                    <td class="text-end text-warning py-3">
                                        {{ number_format(array_sum(array_column($potentialDetail, 'value')), 0, ',', '.') }} đ
                                    </td>
                                    <td class="py-3"></td>
                                    <td class="py-3"></td>
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
            <div class="modal-content border-0 shadow-lg rounded-12px overflow-hidden">
                <div class="modal-header bg-primary py-3 border-0">
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
                                <thead class="bg-light bg-opacity-70 border-bottom border-light-subtle sticky-top">
                                    <tr class="fw-bold text-secondary text-nowrap" style="font-size: 0.8rem; letter-spacing: 0.05em;">
                                        <th class="text-center w-60px py-3" >STT</th>
                                        <th class="py-3">TÊN KHÁCH HÀNG</th>
                                        <th class="py-3">LOẠI HỢP ĐỒNG</th>
                                        <th class="py-3">NHÂN VIÊN KD</th>
                                        <th class="text-end py-3">DOANH SỐ (Đ)</th>
                                        <th class="text-center w-110px py-3" >LOẠI</th>
                                        <th class="text-center w-130px py-3" >NGÀY XUẤT HĐ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detail as $i => $row)
                                        <tr class="text-nowrap">
                                            <td class="text-center text-muted py-3">{{ $i + 1 }}</td>
                                            <td class="fw-bold text-dark py-3">{{ $row['customer'] }}</td>
                                            <td class="text-secondary py-3" style="font-size: 0.88rem;">{{ $row['type'] }}</td>
                                            <td class="text-secondary py-3" style="font-size: 0.88rem;">{{ $row['staff'] }}</td>
                                            <td class="text-end fw-bold text-primary py-3">{{ number_format($row['value'], 0, ',', '.') }}</td>
                                            <td class="text-center py-3">
                                                @if($row['is_renewal'])
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 0.72rem;">Tái ký</span>
                                                @else
                                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 0.72rem;">HĐ mới</span>
                                                @endif
                                            </td>
                                            <td class="text-center text-muted py-3">{{ $row['date'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="fw-bold border-top border-light-subtle" style="background-color: rgba(37, 99, 235, 0.06) !important;">
                                    <tr class="text-nowrap">
                                        <td colspan="4" class="text-end py-3">Tổng tháng {{ $filter_month }}</td>
                                        <td class="text-end text-primary py-3">{{ number_format(array_sum(array_column($detail, 'value')), 0, ',', '.') }} đ</td>
                                        <td colspan="2" class="text-center text-muted py-3">{{ count($detail) }} hợp đồng</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top border-light-subtle py-2.5">
                    <button type="button" class="btn btn-secondary rounded-8px" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal danh sách báo giá tiềm năng theo tháng --}}
    <div wire:ignore.self class="modal fade" id="potentialDetailModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-12px overflow-hidden">
                <div class="modal-header bg-warning py-3 border-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-star me-2"></i>
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
                                <thead class="bg-light bg-opacity-70 border-bottom border-light-subtle sticky-top">
                                    <tr class="fw-bold text-secondary text-nowrap" style="font-size: 0.8rem; letter-spacing: 0.05em;">
                                        <th class="text-center py-3">STT</th>
                                        <th class="py-3">CÔNG TY</th>
                                        <th class="py-3">DỊCH VỤ</th>
                                        <th class="py-3">NHÂN VIÊN KD</th>
                                        <th class="py-3">NGUỒN</th>
                                        <th class="text-end py-3">GIÁ TRỊ BÁO GIÁ CHƯA VAT (Đ)</th>
                                        <th class="text-center py-3">NGÀY DỰ KIẾN KÝ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($potentialDetail as $i => $row)
                                        <tr class="text-nowrap">
                                            <td class="text-center text-muted py-3">{{ $i + 1 }}</td>
                                            <td class="fw-bold text-dark py-3">{{ $row['company'] }}</td>
                                            <td class="py-3">
                                                <span class="badge {{ $this->getServiceBadgeClass($row['service']) }}">{{ $row['service'] }}</span>
                                            </td>
                                            <td class="py-3 text-secondary">{{ $row['staff'] }}</td>
                                            <td class="py-3 text-secondary">{{ $row['source'] }}</td>
                                            <td class="text-end fw-bold text-warning py-3">{{ number_format($row['value'], 0, ',', '.') }} đ</td>
                                            <td class="text-center text-muted py-3">{{ $row['date'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="fw-bold border-top border-light-subtle" style="background-color: rgba(245, 158, 11, 0.05) !important;">
                                    <tr class="text-nowrap">
                                        <td colspan="5" class="text-end py-3">Tổng tháng {{ $filter_month }}</td>
                                        <td class="text-end text-warning py-3">{{ number_format(array_sum(array_column($potentialDetail, 'value')), 0, ',', '.') }} đ</td>
                                        <td class="text-center text-muted py-3">{{ count($potentialDetail) }} báo giá</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top border-light-subtle py-2.5">
                    <button type="button" class="btn btn-secondary rounded-8px" data-bs-dismiss="modal">Đóng</button>
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
