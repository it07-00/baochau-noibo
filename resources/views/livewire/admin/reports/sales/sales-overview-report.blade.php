<div>
    @php
        $changeR = $prevTotals['renewal'] > 0
            ? (($currentTotals['renewal'] - $prevTotals['renewal']) / $prevTotals['renewal'] * 100)
            : null;
        $changeP = $prevTotals['progressive'] > 0
            ? (($currentTotals['progressive'] - $prevTotals['progressive']) / $prevTotals['progressive'] * 100)
            : null;
        $changeG = $prevTotals['grand'] > 0
            ? (($currentTotals['grand'] - $prevTotals['grand']) / $prevTotals['grand'] * 100)
            : null;
        $deltaTotal = $currentTotals['grand'] - $prevTotals['grand'];
    @endphp

    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3 px-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3 col-lg-2">
                    <label class="form-label fw-semibold mb-1 small text-muted">Năm báo cáo</label>
                    <select wire:model.live="year" class="form-select">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5 col-lg-4">
                    <div class="small text-muted mb-1">So sánh năm trước</div>
                    <div class="badge bg-soft-primary text-primary px-3 py-2">{{ $prevYear }} vs {{ $year }}</div>
                </div>
                <div class="col-md-3 col-lg-2 ms-lg-auto">
                    <button type="button" wire:click="$refresh" class="btn btn-light border w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i> Làm mới
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tổng quan nhanh --}}
    <div class="row g-3 mb-4">
        @foreach([
            ['label' => 'DS Tái ký', 'current' => $currentTotals['renewal'], 'prev' => $prevTotals['renewal'], 'change' => $changeR, 'color' => 'success'],
            ['label' => 'DS HĐ mới', 'current' => $currentTotals['progressive'], 'prev' => $prevTotals['progressive'], 'change' => $changeP, 'color' => 'warning'],
            ['label' => 'Tổng doanh số', 'current' => $currentTotals['grand'], 'prev' => $prevTotals['grand'], 'change' => $changeG, 'color' => 'dark'],
        ] as $card)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted fw-semibold mb-2">{{ $card['label'] }}</div>
                    <div class="fw-bold fs-4 text-{{ $card['color'] }}">{{ number_format($card['current'], 0, ',', '.') }} đ</div>
                    <div class="small mt-2 text-muted d-flex justify-content-between align-items-center gap-2">
                        <span>{{ $prevYear }}: {{ number_format($card['prev'], 0, ',', '.') }} đ</span>
                        @if($card['change'] !== null)
                            <span class="fw-semibold {{ $card['change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $card['change'] >= 0 ? '+' : '-' }}{{ number_format(abs($card['change']), 1) }}%
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <div class="small text-muted fw-semibold">Chênh lệch tổng năm</div>
                <div class="fw-bold fs-5 {{ $deltaTotal >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $deltaTotal >= 0 ? '+' : '−' }}{{ number_format(abs($deltaTotal), 0, ',', '.') }} đ
                </div>
            </div>
            <div class="text-end small text-muted">
                Tỷ lệ tăng trưởng tổng
                <div class="fw-semibold {{ ($changeG ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $changeG !== null ? (($changeG >= 0 ? '+' : '-') . number_format(abs($changeG), 1) . '%') : '—' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng theo quý --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Doanh số theo quý</h6>
            <span class="badge bg-soft-primary text-primary">Năm {{ $year }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 190px;">Quý</th>
                            <th class="text-end" style="width: 210px;">DS Tái ký</th>
                            <th class="text-end" style="width: 210px;">DS HĐ mới</th>
                            <th class="text-end" style="width: 220px;">Tổng quý</th>
                            <th style="min-width: 220px;">Tỉ trọng năm</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quarters as $q => $data)
                            @php
                                $qTotal = $data['renewal'] + $data['progressive'];
                                $qPct = $currentTotals['grand'] > 0 ? round(($qTotal / $currentTotals['grand']) * 100, 1) : null;
                                $qBar = $qPct !== null ? max(0, min(100, $qPct)) : 0;
                            @endphp
                            <tr class="{{ $qTotal == 0 ? 'table-light' : '' }}">
                                <td class="fw-semibold">Quý {{ $q }} <span class="text-muted small">(T{{ ($q - 1) * 3 + 1 }}-T{{ $q * 3 }})</span></td>
                                <td class="text-end {{ $data['renewal'] > 0 ? 'text-success fw-semibold' : 'text-muted' }}">
                                    {{ $data['renewal'] > 0 ? number_format($data['renewal'], 0, ',', '.') : '—' }}
                                </td>
                                <td class="text-end {{ $data['progressive'] > 0 ? 'text-warning fw-semibold' : 'text-muted' }}">
                                    {{ $data['progressive'] > 0 ? number_format($data['progressive'], 0, ',', '.') : '—' }}
                                </td>
                                <td class="text-end fw-bold {{ $qTotal > 0 ? 'text-dark' : 'text-muted' }}">
                                    {{ $qTotal > 0 ? number_format($qTotal, 0, ',', '.') . ' đ' : '—' }}
                                </td>
                                <td>
                                    @if($qPct !== null)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $qBar }}%"></div>
                                            </div>
                                            <span class="small fw-semibold text-primary" style="min-width:46px; text-align:right;">{{ $qPct }}%</span>
                                        </div>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td>Tổng năm {{ $year }}</td>
                            <td class="text-end text-success">{{ number_format($currentTotals['renewal'], 0, ',', '.') }} đ</td>
                            <td class="text-end text-warning">{{ number_format($currentTotals['progressive'], 0, ',', '.') }} đ</td>
                            <td class="text-end fs-6">{{ number_format($currentTotals['grand'], 0, ',', '.') }} đ</td>
                            <td class="text-primary">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
