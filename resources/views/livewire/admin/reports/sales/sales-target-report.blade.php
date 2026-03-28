<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Bảng doanh số cam kết</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Bảng doanh số cam kết</li>
                </ol>
            </nav>
        </div>
        @if($canEdit)
        <button wire:click="saveTargets" class="btn btn-primary d-flex align-items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
            Lưu mục tiêu
        </button>
        @endif
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
                    <label class="form-label fw-semibold mb-1 small">Nhân viên <span class="text-muted">(trống = công ty)</span></label>
                    <select wire:model.live="filter_staff" class="form-select form-select-sm">
                        <option value="">Mục tiêu công ty</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if(!$canEdit)
                <div class="col-md-5">
                    <div class="alert alert-info py-2 mb-0 small">Chỉ Quản lý / IT có thể chỉnh sửa mục tiêu.</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Bảng mục tiêu --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:120px">Tháng</th>
                            <th class="text-end" style="width:200px">Mục tiêu (đ)</th>
                            <th class="text-end">Thực tế (đ)</th>
                            <th class="text-end">% Đạt</th>
                            <th class="text-center">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($months as $m => $data)
                            @php
                                $pct = $data['target'] > 0 ? round($data['actual'] / $data['target'] * 100, 1) : null;
                            @endphp
                            <tr>
                                <td class="fw-semibold">Tháng {{ $m }}</td>
                                <td class="text-end">
                                    @if($canEdit)
                                        <input type="text" wire:model="targets.{{ $m }}"
                                            class="form-control form-control-sm text-end"
                                            style="width:160px;display:inline-block"
                                            placeholder="0">
                                    @else
                                        {{ number_format($data['target'], 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="text-end fw-semibold {{ $data['actual'] > 0 ? 'text-success' : 'text-muted' }}">
                                    {{ $data['actual'] > 0 ? number_format($data['actual'], 0, ',', '.') : '—' }}
                                </td>
                                <td class="text-end">
                                    @if($pct !== null)
                                        <span class="{{ $pct >= 100 ? 'text-success fw-bold' : ($pct >= 70 ? 'text-warning' : 'text-danger') }}">
                                            {{ $pct }}%
                                        </span>
                                    @else —
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($pct === null)
                                        <span class="badge bg-soft-secondary text-secondary small">Chưa có mục tiêu</span>
                                    @elseif($pct >= 100)
                                        <span class="badge bg-soft-success text-success small">Đạt</span>
                                    @elseif($pct >= 70)
                                        <span class="badge bg-soft-warning text-warning small">Gần đạt</span>
                                    @else
                                        <span class="badge bg-soft-danger text-danger small">Chưa đạt</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td>Tổng năm {{ $year }}</td>
                            <td class="text-end">{{ number_format($totals['target'], 0, ',', '.') }} đ</td>
                            <td class="text-end text-success">{{ number_format($totals['actual'], 0, ',', '.') }} đ</td>
                            <td class="text-end">
                                @if($totals['target'] > 0)
                                    @php $totalPct = round($totals['actual'] / $totals['target'] * 100, 1); @endphp
                                    <span class="{{ $totalPct >= 100 ? 'text-success' : ($totalPct >= 70 ? 'text-warning' : 'text-danger') }}">
                                        {{ $totalPct }}%
                                    </span>
                                @else —
                                @endif
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
