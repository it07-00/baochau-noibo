<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Bảng thống kê</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Bảng thống kê tổng quan</li>
                </ol>
            </nav>
        </div>
        <div>
            <select wire:model.live="year" class="form-select form-select-sm" style="width:auto">
                @foreach($years as $y)
                    <option value="{{ $y }}">Năm {{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-primary d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div>
                        <div class="small text-muted">Tổng khách hàng</div>
                        <div class="fw-bold fs-4 text-primary">{{ number_format($totalCustomers) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-success d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <div>
                        <div class="small text-muted">Hợp đồng năm {{ $year }}</div>
                        <div class="fw-bold fs-4 text-success">{{ number_format($totalContracts) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-warning d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-warning" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <div>
                        <div class="small text-muted">Giá trị HĐ năm {{ $year }}</div>
                        <div class="fw-bold text-warning">{{ number_format($totalContractValue, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-info d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-info" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline></svg>
                    </div>
                    <div>
                        <div class="small text-muted">Doanh số năm {{ $year }}</div>
                        <div class="fw-bold text-info">{{ number_format($totalSales, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Thu tiền Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-danger d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-danger" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    </div>
                    <div>
                        <div class="small text-muted">Phải thu năm {{ $year }}</div>
                        <div class="fw-bold text-danger">{{ number_format($totalPaymentDue, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-success d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <div>
                        <div class="small text-muted">Đã thu năm {{ $year }}</div>
                        <div class="fw-bold text-success">{{ number_format($totalPaymentPaid, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-secondary d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-secondary" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <div>
                        <div class="small text-muted">Còn phải thu</div>
                        <div class="fw-bold text-secondary">{{ number_format($totalPaymentDue - $totalPaymentPaid, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Bảng theo tháng --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Diễn biến theo tháng — Năm {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tháng</th>
                                    <th class="text-center">Số HĐ ký</th>
                                    <th class="text-end">Giá trị HĐ</th>
                                    <th class="text-end">Doanh số</th>
                                    <th class="text-end">Phải thu</th>
                                    <th class="text-end">Đã thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthly as $m => $data)
                                @php $hasData = $data['contracts'] > 0 || $data['sales'] > 0 || $data['payment_due'] > 0; @endphp
                                <tr class="{{ !$hasData ? 'text-muted' : '' }}">
                                    <td class="fw-semibold">Tháng {{ $m }}</td>
                                    <td class="text-center">{{ $data['contracts'] > 0 ? $data['contracts'] : '—' }}</td>
                                    <td class="text-end small">{{ $data['value'] > 0 ? number_format($data['value'], 0, ',', '.') . ' đ' : '—' }}</td>
                                    <td class="text-end fw-semibold {{ $data['sales'] > 0 ? 'text-info' : '' }}">
                                        {{ $data['sales'] > 0 ? number_format($data['sales'], 0, ',', '.') . ' đ' : '—' }}
                                    </td>
                                    <td class="text-end small {{ $data['payment_due'] > 0 ? 'text-danger' : '' }}">
                                        {{ $data['payment_due'] > 0 ? number_format($data['payment_due'], 0, ',', '.') . ' đ' : '—' }}
                                    </td>
                                    <td class="text-end small {{ $data['payment_paid'] > 0 ? 'text-success' : '' }}">
                                        {{ $data['payment_paid'] > 0 ? number_format($data['payment_paid'], 0, ',', '.') . ' đ' : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr>
                                    <td>Tổng năm {{ $year }}</td>
                                    <td class="text-center">{{ $totalContracts }}</td>
                                    <td class="text-end">{{ number_format($totalContractValue, 0, ',', '.') }} đ</td>
                                    <td class="text-end text-info">{{ number_format($totalSales, 0, ',', '.') }} đ</td>
                                    <td class="text-end text-danger">{{ number_format($totalPaymentDue, 0, ',', '.') }} đ</td>
                                    <td class="text-end text-success">{{ number_format($totalPaymentPaid, 0, ',', '.') }} đ</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bảng theo loại HĐ --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Hợp đồng theo loại — {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Loại HĐ</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-end">Giá trị</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byType as $label => $data)
                            <tr class="{{ $data['count'] == 0 ? 'text-muted' : '' }}">
                                <td class="fw-semibold small">{{ $label }}</td>
                                <td class="text-center">
                                    @if($data['count'] > 0)
                                        <span class="badge bg-soft-primary text-primary">{{ $data['count'] }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end small">{{ $data['value'] > 0 ? number_format($data['value'], 0, ',', '.') . ' đ' : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td>Tổng</td>
                                <td class="text-center">{{ $totalContracts }}</td>
                                <td class="text-end">{{ number_format($totalContractValue, 0, ',', '.') }} đ</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($canSeeTechnical)
    {{-- Bộ phận kỹ thuật --}}
    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Bộ phận Kỹ thuật — HĐ được phân công năm {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Loại HĐ</th>
                                <th class="text-center">Số HĐ</th>
                                <th class="text-center">Hoàn thành</th>
                                <th class="text-end">Giá trị</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($technicalStats as $row)
                            <tr class="{{ $row['count'] == 0 ? 'text-muted' : '' }}">
                                <td class="fw-semibold small">{{ $row['label'] }}</td>
                                <td class="text-center">
                                    @if($row['count'] > 0)
                                        <span class="badge bg-soft-primary text-primary">{{ $row['count'] }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center text-success">{{ $row['completed'] > 0 ? $row['completed'] : '—' }}</td>
                                <td class="text-end small">{{ $row['value'] > 0 ? number_format($row['value'], 0, ',', '.') . ' đ' : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        @if($technicalStats->sum('count') > 0)
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td>Tổng</td>
                                <td class="text-center">{{ $technicalStats->sum('count') }}</td>
                                <td class="text-center text-success">{{ $technicalStats->sum('completed') }}</td>
                                <td class="text-end">{{ number_format($technicalStats->sum('value'), 0, ',', '.') }} đ</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
