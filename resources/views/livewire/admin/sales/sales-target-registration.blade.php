<div class="sales-target-registration">
    @php
        $totalPct = $totals['target'] > 0 ? round($totals['actual'] / $totals['target'] * 100, 1) : null;
        $remaining = max(0, $totals['target'] - $totals['actual']);
    @endphp

    <div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h4 class="mb-1 fw-bold fs-3">Đăng ký mục tiêu doanh số</h4>
            <p class="text-muted mb-2 fs-6">Theo dõi cam kết theo tháng và mức độ hoàn thành thực tế trong năm {{ $year }}.</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Đăng ký mục tiêu doanh số</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-2 ms-auto flex-wrap justify-content-end">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 fw-semibold">Năm {{ $year }}</span>
            <button wire:click="saveTargets" class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 fw-semibold">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                Lưu cam kết
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold mb-2">Năm áp dụng</label>
                            <select wire:model.live="year" class="form-select">
                                @foreach($years as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-semibold mb-2">Nhân viên</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="text-muted mb-1">Tỷ lệ hoàn thành năm</div>
                            @if($totalPct !== null)
                                <div class="h4 mb-0 {{ $totalPct >= 100 ? 'text-success' : ($totalPct >= 70 ? 'text-warning' : 'text-danger') }}">
                                    {{ $totalPct }}%
                                </div>
                            @else
                                <div class="h4 mb-0 text-muted">—</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <p class="text-muted mb-2">Phần còn thiếu để đạt mục tiêu năm</p>
                        <h4 class="mb-1 text-danger">{{ number_format($remaining, 0, ',', '.') }} đ</h4>
                        <small class="text-muted">Đã ký: {{ number_format($totals['actual'], 0, ',', '.') }} / {{ number_format($totals['target'], 0, ',', '.') }} đ</small>
                    </div>
                    <div class="progress mt-3" role="progressbar" aria-label="year-progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $totalPct !== null ? min($totalPct, 100) : 0 }}" style="height: 8px;">
                        <div class="progress-bar {{ $totalPct !== null && $totalPct >= 100 ? 'bg-success' : ($totalPct !== null && $totalPct >= 70 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $totalPct !== null ? min($totalPct, 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted mb-1">Tổng cam kết năm {{ $year }}</div>
                    <div class="kpi-value">{{ number_format($totals['target'], 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted mb-1">Tổng thực tế đã ký</div>
                    <div class="kpi-value text-success">{{ number_format($totals['actual'], 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted mb-1">Mức độ hoàn thành</div>
                    <div class="kpi-value {{ $totalPct !== null && $totalPct >= 100 ? 'text-success' : ($totalPct !== null && $totalPct >= 70 ? 'text-warning' : 'text-danger') }}">
                        {{ $totalPct !== null ? $totalPct.'%' : '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <h6 class="mb-0 fw-bold fs-5">Chi tiết cam kết theo tháng</h6>
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
                            <th style="width: 160px;" class="ps-3">Tháng</th>
                            <th class="text-end" style="width: 230px;">Cam kết (đ)</th>
                            <th class="text-end" style="width: 230px;">Thực tế đã ký (đ)</th>
                            <th class="text-end" style="width: 220px;">Chênh lệch (đ)</th>
                            <th class="text-end" style="width: 190px;">% Đạt</th>
                            <th class="text-center pe-3" style="width: 160px;">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($months as $m => $data)
                            @php
                                $target = (float) ($data['target'] ?? 0);
                                $actual = (float) ($data['actual'] ?? 0);
                                $variance = $actual - $target;
                                $pct = $target > 0 ? round($actual / $target * 100, 1) : null;
                                $quarter = (int) ceil($m / 3);
                            @endphp
                            <tr>
                                <td class="ps-3 month-cell">
                                    <div class="fw-semibold">Tháng {{ $m }}</div>
                                    <small class="text-muted">Quý {{ $quarter }}</small>
                                </td>
                                <td class="text-end">
                                    <div class="input-group" style="max-width: 220px; margin-left: auto;">
                                        <input
                                            type="text"
                                            wire:model.blur="targets.{{ $m }}"
                                            class="form-control text-end fw-semibold money-input"
                                            placeholder="0"
                                        >
                                        <span class="input-group-text fw-semibold">đ</span>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold {{ $actual > 0 ? 'text-success' : 'text-muted' }}">
                                    {{ $actual > 0 ? number_format($actual, 0, ',', '.') : '—' }}
                                </td>
                                <td class="text-end fw-semibold {{ $variance >= 0 ? 'text-success' : 'text-danger' }}">
                                    @if($target > 0 || $actual > 0)
                                        {{ $variance >= 0 ? '+' : '-' }}{{ number_format(abs($variance), 0, ',', '.') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($pct !== null)
                                        <div class="fw-semibold {{ $pct >= 100 ? 'text-success' : ($pct >= 70 ? 'text-warning' : 'text-danger') }}">{{ $pct }}%</div>
                                        <div class="progress ms-auto" style="height: 6px; max-width: 140px;">
                                            <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : ($pct >= 70 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ min($pct, 100) }}%"></div>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center pe-3">
                                    @if($pct === null)
                                        <span class="badge bg-secondary-subtle text-body border border-secondary-subtle">Chưa có cam kết</span>
                                    @elseif($pct >= 100)
                                        <span class="badge bg-success bg-opacity-10 text-success">Đạt</span>
                                    @elseif($pct >= 70)
                                        <span class="badge bg-warning bg-opacity-10 text-warning">Gần đạt</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger">Chưa đạt</span>
                                    @endif
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
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .sales-target-registration .kpi-value {
        font-size: 1.65rem;
        line-height: 1.1;
        font-weight: 700;
    }

    .sales-target-registration .sales-target-table th,
    .sales-target-registration .sales-target-table td {
        font-size: 1rem;
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
    }

    .sales-target-registration .sales-target-table .month-cell  {
        font-size: 0.92rem;
    }

    .sales-target-registration .sales-target-table .badge {
        font-size: 0.95rem;
        font-weight: 600;
        padding: 0.4rem 0.75rem;
    }

    .sales-target-registration .sales-target-table .progress {
        height: 8px !important;
    }

    @media (max-width: 992px) {
        .sales-target-registration .kpi-value {
            font-size: 1.35rem;
        }

        .sales-target-registration .sales-target-table th,
        .sales-target-registration .sales-target-table td {
            font-size: 0.95rem;
        }
    }
</style>
