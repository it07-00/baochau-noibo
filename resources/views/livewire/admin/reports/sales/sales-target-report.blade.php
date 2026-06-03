<div>
    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3 px-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3 col-lg-2">
                    <label class="form-label fw-semibold mb-1  text-muted">Năm báo cáo</label>
                    <select wire:model.live="year" class="form-select">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label fw-semibold mb-1  text-muted">Nhân viên kinh doanh</label>
                    <select wire:model.live="filter_staff" class="form-select">
                        <option value="">Tất cả nhân viên KD</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
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
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class=" text-muted fw-semibold mb-2">Mục tiêu năm</div>
                    <div class="fs-5 fw-bold text-dark">{{ number_format($totals['target'], 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class=" text-muted fw-semibold mb-2">Thực tế năm</div>
                    <div class="fs-5 fw-bold text-success">{{ number_format($totals['actual'], 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class=" text-muted fw-semibold mb-2">Chênh lệch</div>
                    <div class="fs-5 fw-bold {{ $this->totalDelta($totals) >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $this->totalDelta($totals) >= 0 ? '+' : '−' }}{{ number_format(abs($this->totalDelta($totals)), 0, ',', '.') }} đ
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class=" text-muted fw-semibold mb-2">Tỷ lệ hoàn thành</div>
                    <div class="fs-5 fw-bold {{ $this->pctTextClass($this->totalPct($totals)) }}">
                        {{ $this->totalPct($totals) !== null ? $this->totalPct($totals) . '%' : '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng mục tiêu --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
            <h6 class="mb-0 fw-bold">Tiến độ cam kết theo tháng</h6>
            <span class="badge bg-soft-primary text-primary">Năm {{ $year }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="w-120px">Tháng</th>
                            <th class="text-end text-truncate-220" >Mục tiêu (đ)</th>
                            <th class="text-end w-230px" >Thực tế (Doanh số từ HĐ) (đ)</th>
                            <th class="text-end text-truncate-220" >Chênh lệch (đ)</th>
                            <th class="mnw-220px">Tiến độ</th>
                            <th class="text-center w-120px" >Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($months as $m => $data)
                            <tr class="{{ $this->monthMetrics($data)['target'] == 0 ? 'table-light' : '' }} cursor-pointer"
                                 wire:click="openDetail({{ $m }})">
                                <td>
                                    <span class="fw-semibold">Tháng {{ $m }}</span>
                                </td>
                                <td class="text-end fw-semibold {{ $this->monthMetrics($data)['target'] > 0 ? 'text-dark' : 'text-muted' }}">
                                    {{ $this->monthMetrics($data)['target'] > 0 ? number_format($this->monthMetrics($data)['target'], 0, ',', '.') : '—' }}
                                </td>
                                <td class="text-end fw-semibold {{ $this->monthMetrics($data)['actual'] > 0 ? 'text-success' : 'text-muted' }}">
                                    {{ $this->monthMetrics($data)['actual'] > 0 ? number_format($this->monthMetrics($data)['actual'], 0, ',', '.') : '—' }}
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
                            <td>Tổng năm {{ $year }}</td>
                            <td class="text-end">{{ number_format($totals['target'], 0, ',', '.') }} đ</td>
                            <td class="text-end text-success">{{ number_format($totals['actual'], 0, ',', '.') }} đ</td>
                            <td class="text-end {{ $this->totalDelta($totals) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $this->totalDelta($totals) >= 0 ? '+' : '−' }}{{ number_format(abs($this->totalDelta($totals)), 0, ',', '.') }} đ
                            </td>
                            <td>
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
                            <td class="text-center">
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

    {{-- Modal chi tiết hợp đồng theo tháng --}}
    <div wire:ignore.self class="modal fade" id="targetDetailModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary py-3">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-calendar3 me-2"></i>
                        Chi tiết hợp đồng tháng {{ $filter_month }}/{{ $year }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if(empty($detail))
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
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

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('openDetailModal', () => {
                new bootstrap.Modal(document.getElementById('targetDetailModal')).show();
            });
        });
    </script>
</div>
