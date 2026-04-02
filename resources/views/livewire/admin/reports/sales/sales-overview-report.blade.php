<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Bảng tổng kết</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Bảng tổng kết</li>
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
            </div>
        </div>
    </div>

    {{-- YoY Summary Cards --}}
    <div class="row g-3 mb-4">
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
        @endphp
        @foreach([
            ['label' => 'DS Tái ký', 'current' => $currentTotals['renewal'], 'prev' => $prevTotals['renewal'], 'change' => $changeR, 'color' => 'success'],
            ['label' => 'DS Tiến độ', 'current' => $currentTotals['progressive'], 'prev' => $prevTotals['progressive'], 'change' => $changeP, 'color' => 'warning'],
            ['label' => 'Tổng', 'current' => $currentTotals['grand'], 'prev' => $prevTotals['grand'], 'change' => $changeG, 'color' => 'dark'],
        ] as $card)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">{{ $card['label'] }}</div>
                    <div class="fw-bold fs-6 text-{{ $card['color'] }}">{{ number_format($card['current'], 0, ',', '.') }} đ</div>
                    <div class="small mt-1 text-muted">
                        {{ $prevYear }}: {{ number_format($card['prev'], 0, ',', '.') }} đ
                        @if($card['change'] !== null)
                            <span class="ms-1 {{ $card['change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $card['change'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($card['change']), 1) }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Bảng theo quý --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold">Doanh số theo quý — Năm {{ $year }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Quý</th>
                            <th class="text-end">DS Tái ký</th>
                            <th class="text-end">DS Tiến độ</th>
                            <th class="text-end fw-bold">Tổng quý</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quarters as $q => $data)
                            @php $qTotal = $data['renewal'] + $data['progressive']; @endphp
                            <tr>
                                <td class="fw-semibold">Quý {{ $q }} <small class="text-muted">(T{{ ($q-1)*3+1 }}–T{{ $q*3 }})</small></td>
                                <td class="text-end text-success">{{ $data['renewal'] > 0 ? number_format($data['renewal'], 0, ',', '.') : '—' }}</td>
                                <td class="text-end text-warning">{{ $data['progressive'] > 0 ? number_format($data['progressive'], 0, ',', '.') : '—' }}</td>
                                <td class="text-end fw-bold">{{ $qTotal > 0 ? number_format($qTotal, 0, ',', '.') . ' đ' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td>Tổng năm {{ $year }}</td>
                            <td class="text-end text-success">{{ number_format($currentTotals['renewal'], 0, ',', '.') }} đ</td>
                            <td class="text-end text-warning">{{ number_format($currentTotals['progressive'], 0, ',', '.') }} đ</td>
                            <td class="text-end fs-6">{{ number_format($currentTotals['grand'], 0, ',', '.') }} đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
