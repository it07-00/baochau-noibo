<div>
    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1">Năm</label>
                    <select wire:model.live="filterYear" class="form-select form-select-sm">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1">Kỳ</label>
                    <select wire:model.live="filterPeriodType" class="form-select form-select-sm">
                        <option value="year">Cả năm</option>
                        <option value="quarter">Theo quý</option>
                        <option value="month">Theo tháng</option>
                    </select>
                </div>
                @if($filterPeriodType === 'quarter')
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1">Quý</label>
                    <select wire:model.live="filterQuarter" class="form-select form-select-sm">
                        <option value="0">-- Chọn quý --</option>
                        @foreach([1,2,3,4] as $q)
                            <option value="{{ $q }}">Quý {{ $q }}</option>
                        @endforeach
                    </select>
                </div>
                @elseif($filterPeriodType === 'month')
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1">Tháng</label>
                    <select wire:model.live="filterMonth" class="form-select form-select-sm">
                        <option value="0">-- Chọn tháng --</option>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}">Tháng {{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1">Loại hợp đồng</label>
                    <select wire:model.live="filterContractType" class="form-select form-select-sm">
                        <option value="all">Tất cả loại hợp đồng</option>
                        @foreach($contractTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @can('cash-flow.export')
                <div class="col-md-auto ms-auto">
                    <button wire:click="exportExcel" class="btn btn-success btn-sm" wire:loading.attr="disabled">
                        <span wire:loading wire:target="exportExcel" class="spinner-border spinner-border-sm me-1"></span>
                        <i class="bi bi-file-earmark-excel me-1"></i> Xuất Excel
                    </button>
                </div>
                @endcan
            </div>
        </div>
    </div>

    {{-- 4 summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1" style="font-size:13px">Tổng doanh số</p>
                    <h5 class="fw-bold text-primary mb-0">{{ number_format($totals['revenue']) }}đ</h5>
                    <small class="text-muted">{{ $totals['count'] }} hợp đồng</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1" style="font-size:13px">Tổng hoa hồng</p>
                    <h5 class="fw-bold text-warning mb-0">{{ number_format($totals['commission']) }}đ</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1" style="font-size:13px">Tổng chi Nhà Cung Cấp</p>
                    <h5 class="fw-bold text-danger mb-0">{{ number_format($totals['ncc_payment']) }}đ</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #22c55e !important;">
                <div class="card-body">
                    <p class="text-muted mb-1" style="font-size:13px">Tổng thực nhận</p>
                    <h5 class="fw-bold text-success mb-0">{{ number_format($totals['net_received']) }}đ</h5>
                    <small class="text-muted">= DS - Hoa hồng - Chi Nhà Cung Cấp</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng chi tiết --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold">Chi tiết dòng tiền — {{ $periodLabel }}</h6>
            <span class="badge bg-secondary">{{ $totals['count'] }} hợp đồng</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:13px">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width:40px">STT</th>
                            <th>Loại HĐ</th>
                            <th>Số HĐ BC</th>
                            <th>Khách hàng</th>
                            <th>Nhân viên chăm sóc</th>
                            <th class="text-center">Ngày ký</th>
                            <th class="text-end">Doanh số</th>
                            <th class="text-end">Hoa hồng</th>
                            <th class="text-end text-danger">Chi Nhà Cung Cấp</th>
                            <th class="text-end text-success fw-bold">Thực nhận</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $row)
                        <tr>
                            <td class="text-center text-muted">{{ ($rows->currentPage() - 1) * $rows->perPage() + $i + 1 }}</td>
                            <td><span class="badge bg-light text-dark border" style="font-size:11px">{{ $row['type'] }}</span></td>
                            <td class="fw-semibold">{{ $row['shd_bc'] ?: '—' }}</td>
                            <td class="fw-bold text-primary">
                                @if(!empty($row['customer_slug']))
                                    <a href="{{ route('app.customers.contracts', $row['customer_slug']) }}" class="text-decoration-none">
                                        {{ $row['customer'] }}
                                    </a>
                                @else
                                    {{ $row['customer'] ?? '—' }}
                                @endif
                            </td>
                            <td>{{ $row['staff'] ?? '—' }}</td>
                            <td class="text-center">{{ $row['signed_at'] ?? '—' }}</td>
                            <td class="text-end">{{ $row['revenue'] > 0 ? number_format($row['revenue']) : '—' }}</td>
                            <td class="text-end text-warning">{{ $row['commission'] > 0 ? number_format($row['commission']) : '—' }}</td>
                            <td class="text-end text-danger">{{ $row['ncc_payment'] > 0 ? number_format($row['ncc_payment']) : '—' }}</td>
                            <td class="text-end fw-bold {{ $row['net_received'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($row['net_received']) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Không có dữ liệu cho kỳ đã chọn.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($totals['count'] > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="6" class="text-end">Tổng cộng</td>
                            <td class="text-end text-primary">{{ number_format($totals['revenue']) }}</td>
                            <td class="text-end text-warning">{{ number_format($totals['commission']) }}</td>
                            <td class="text-end text-danger">{{ number_format($totals['ncc_payment']) }}</td>
                            <td class="text-end text-success">{{ number_format($totals['net_received']) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            @if ($rows instanceof \Illuminate\Pagination\LengthAwarePaginator && $rows->hasPages())
                <div class="card-footer px-3 border-0 d-flex justify-content-center">
                    {{ $rows->links('livewire.admin.users.pagination') }}
                </div>
            @endif
        </div>
    </div>
</div>
