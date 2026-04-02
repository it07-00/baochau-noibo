<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Bảng xếp hạng</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Bảng xếp hạng</li>
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

    <div class="row g-4 mb-4">
        @if($canSeeSales)
        {{-- Xếp hạng nhân viên kinh doanh --}}
        <div class="{{ $canSeeConsulting ? 'col-lg-7' : 'col-12' }}">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Xếp hạng doanh số nhân viên Kinh doanh — Năm {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:50px">Hạng</th>
                                <th>Nhân viên</th>
                                <th class="text-end">DS Tái ký</th>
                                <th class="text-end">DS Tiến độ</th>
                                <th class="text-end">Tổng DS</th>
                                <th class="text-end fw-bold">Thực thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesRankings as $i => $row)
                            @php
                                $rank  = $i + 1;
                                $medal = match($rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => $rank };
                            @endphp
                            <tr class="{{ $row['total'] == 0 && $row['revenue'] == 0 ? 'text-muted' : '' }}">
                                <td class="text-center fw-bold">{{ $medal }}</td>
                                <td class="fw-semibold">{{ $row['name'] }}</td>
                                <td class="text-end small text-success">{{ $row['renewal'] > 0 ? number_format($row['renewal'], 0, ',', '.') : '—' }}</td>
                                <td class="text-end small text-warning">{{ $row['progressive'] > 0 ? number_format($row['progressive'], 0, ',', '.') : '—' }}</td>
                                <td class="text-end">{{ $row['total'] > 0 ? number_format($row['total'], 0, ',', '.') . ' đ' : '—' }}</td>
                                <td class="text-end fw-bold {{ $row['revenue'] > 0 ? 'text-success' : '' }}">
                                    {{ $row['revenue'] > 0 ? number_format($row['revenue'], 0, ',', '.') . ' đ' : '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                            @endforelse
                        </tbody>
                        @if($salesRankings->isNotEmpty())
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="2">Tổng</td>
                                <td class="text-end text-success">{{ number_format($salesRankings->sum('renewal'), 0, ',', '.') }} đ</td>
                                <td class="text-end text-warning">{{ number_format($salesRankings->sum('progressive'), 0, ',', '.') }} đ</td>
                                <td class="text-end">{{ number_format($salesRankings->sum('total'), 0, ',', '.') }} đ</td>
                                <td class="text-end text-success">{{ number_format($salesRankings->sum('revenue'), 0, ',', '.') }} đ</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>


        @endif

        @if($canSeeConsulting)
        {{-- Xếp hạng nhân viên tư vấn --}}
        <div class="{{ $canSeeSales ? 'col-lg-5' : 'col-12' }}">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Xếp hạng nhân viên Tư vấn — Năm {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:50px">Hạng</th>
                                <th>Nhân viên</th>
                                <th class="text-center">Số HĐ</th>
                                <th class="text-center">Hoàn thành</th>
                                <th class="text-end fw-bold">Giá trị HĐ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($consultingRankings as $i => $row)
                            @php
                                $rank  = $i + 1;
                                $medal = match($rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => $rank };
                            @endphp
                            <tr class="{{ $row['value'] == 0 ? 'text-muted' : '' }}">
                                <td class="text-center fw-bold">{{ $medal }}</td>
                                <td class="fw-semibold">{{ $row['name'] }}</td>
                                <td class="text-center">{{ $row['count'] > 0 ? $row['count'] : '—' }}</td>
                                <td class="text-center text-success">{{ $row['completed'] > 0 ? $row['completed'] : '—' }}</td>
                                <td class="text-end fw-bold {{ $row['value'] > 0 ? 'text-dark' : '' }}">
                                    {{ $row['value'] > 0 ? number_format($row['value'], 0, ',', '.') . ' đ' : '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                            @endforelse
                        </tbody>
                        @if($consultingRankings->isNotEmpty())
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="2">Tổng</td>
                                <td class="text-center">{{ $consultingRankings->sum('count') }}</td>
                                <td class="text-center text-success">{{ $consultingRankings->sum('completed') }}</td>
                                <td class="text-end">{{ number_format($consultingRankings->sum('value'), 0, ',', '.') }} đ</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if($canSeeTechnical)
    <div class="row g-4 mb-4">
        {{-- Xếp hạng nhân viên kỹ thuật --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Xếp hạng nhân viên Kỹ thuật — Năm {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:50px">Hạng</th>
                                <th>Nhân viên</th>
                                <th class="text-center">Số HĐ</th>
                                <th class="text-center">Hoàn thành</th>
                                <th class="text-end fw-bold">Giá trị HĐ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($technicalRankings as $i => $row)
                            @php
                                $rank  = $i + 1;
                                $medal = match($rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => $rank };
                            @endphp
                            <tr class="{{ $row['value'] == 0 ? 'text-muted' : '' }}">
                                <td class="text-center fw-bold">{{ $medal }}</td>
                                <td class="fw-semibold">{{ $row['name'] }}</td>
                                <td class="text-center">{{ $row['count'] > 0 ? $row['count'] : '—' }}</td>
                                <td class="text-center text-success">{{ $row['completed'] > 0 ? $row['completed'] : '—' }}</td>
                                <td class="text-end fw-bold {{ $row['value'] > 0 ? 'text-dark' : '' }}">
                                    {{ $row['value'] > 0 ? number_format($row['value'], 0, ',', '.') . ' đ' : '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                            @endforelse
                        </tbody>
                        @if($technicalRankings->isNotEmpty())
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="2">Tổng</td>
                                <td class="text-center">{{ $technicalRankings->sum('count') }}</td>
                                <td class="text-center text-success">{{ $technicalRankings->sum('completed') }}</td>
                                <td class="text-end">{{ number_format($technicalRankings->sum('value'), 0, ',', '.') }} đ</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($canSeeSales)
    <div class="row g-4 mb-4">
        {{-- Tiến độ thu tiền - Donut Chart --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Tiến độ thu tiền — Năm {{ $year }}</h6>
                </div>
                <div class="card-body">
                    @if($paymentStats['due'] > 0)
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div id="paymentDonutChart" wire:ignore></div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-muted">Tổng phải thu</span>
                                    <span class="fw-bold text-danger">{{ number_format($paymentStats['due'], 0, ',', '.') }} đ</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-muted">Đã thu</span>
                                    <span class="fw-bold text-success">{{ number_format($paymentStats['paid'], 0, ',', '.') }} đ</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">Còn phải thu</span>
                                    <span class="fw-bold text-secondary">{{ number_format($paymentStats['due'] - $paymentStats['paid'], 0, ',', '.') }} đ</span>
                                </div>
                            </div>
                            <hr>
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-success">&nbsp;</span> Đã thanh toán</td>
                                        <td class="text-end small">{{ $paymentStats['paid_count'] }} đợt</td>
                                        <td class="text-end small fw-semibold">{{ number_format($paymentStats['paid_amount'], 0, ',', '.') }} đ</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-secondary">&nbsp;</span> Chờ thanh toán</td>
                                        <td class="text-end small">{{ $paymentStats['pending_count'] }} đợt</td>
                                        <td class="text-end small fw-semibold">{{ number_format($paymentStats['pending_amount'], 0, ',', '.') }} đ</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning">&nbsp;</span> Thanh toán 1 phần</td>
                                        <td class="text-end small">{{ $paymentStats['partial_count'] }} đợt</td>
                                        <td class="text-end small fw-semibold">{{ number_format($paymentStats['partial_amount'], 0, ',', '.') }} đ</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-danger">&nbsp;</span> Quá hạn</td>
                                        <td class="text-end small">{{ $paymentStats['overdue_count'] }} đợt</td>
                                        <td class="text-end small fw-semibold">{{ number_format($paymentStats['overdue_amount'], 0, ',', '.') }} đ</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @else
                    <div class="text-center text-muted py-4">Không có dữ liệu thu tiền</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top tỉnh/TP --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Top tỉnh/TP theo tiền thu HĐ — {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    @if($topProvinces->count())
                    <div class="p-3">
                        <div id="provincePieChart"></div>
                    </div>
                    @endif
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:40px">#</th>
                                <th>Tỉnh / TP</th>
                                <th class="text-center">Số HĐ</th>
                                <th class="text-end">Tiền thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProvinces as $i => $row)
                            <tr>
                                <td class="text-center text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $row->province }}</td>
                                <td class="text-center">{{ $row->cnt }}</td>
                                <td class="text-end fw-semibold text-primary">{{ number_format($row->total, 0, ',', '.') }} đ</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Không có dữ liệu</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Top khách hàng --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Top khách hàng theo giá trị HĐ — {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width:40px">#</th>
                                    <th>Khách hàng</th>
                                    <th class="text-center">HĐ CT</th>
                                    <th class="text-end">GT Chất thải</th>
                                    <th class="text-center">HĐ TV</th>
                                    <th class="text-end">GT Tư vấn</th>
                                    <th class="text-end fw-bold">Tổng GT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCustomers as $i => $row)
                                <tr>
                                    <td class="text-center text-muted small">{{ $i + 1 }}</td>
                                    <td class="fw-semibold">{{ $row->name }}</td>
                                    <td class="text-center">{{ $row->waste_count > 0 ? $row->waste_count : '—' }}</td>
                                    <td class="text-end small">{{ $row->waste_value > 0 ? number_format($row->waste_value, 0, ',', '.') : '—' }}</td>
                                    <td class="text-center">{{ $row->consult_count > 0 ? $row->consult_count : '—' }}</td>
                                    <td class="text-end small">{{ $row->consult_value > 0 ? number_format($row->consult_value, 0, ',', '.') : '—' }}</td>
                                    <td class="text-end fw-bold text-dark">{{ number_format($row->waste_value + $row->consult_value, 0, ',', '.') }} đ</td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top dịch vụ --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Top dịch vụ báo giá — {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:40px">#</th>
                                <th>Dịch vụ</th>
                                <th class="text-center">Số BG</th>
                                <th class="text-end">Doanh số</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topServices as $i => $row)
                            <tr>
                                <td class="text-center text-muted">{{ $i + 1 }}</td>
                                <td class="small text-muted" style="max-width:180px;">{{ $row->service }}</td>
                                <td class="text-center">{{ $row->cnt }}</td>
                                <td class="text-end fw-semibold text-success">{{ number_format($row->total, 0, ',', '.') }} đ</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Không có dữ liệu</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="{{ asset('assets/js/apexcharts.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.querySelector('#paymentDonutChart');
    if (!el) return;

    var paid    = {{ $paymentStats['paid_amount'] ?? 0 }};
    var pending = {{ $paymentStats['pending_amount'] ?? 0 }};
    var partial = {{ $paymentStats['partial_amount'] ?? 0 }};
    var overdue = {{ $paymentStats['overdue_amount'] ?? 0 }};

    if (paid + pending + partial + overdue === 0) return;

    new ApexCharts(el, {
        chart: { type: 'donut', height: 280 },
        series: [paid, pending, partial, overdue],
        labels: ['Đã thanh toán', 'Chờ thanh toán', 'TT 1 phần', 'Quá hạn'],
        colors: ['#198754', '#6c757d', '#ffc107', '#dc3545'],
        legend: { position: 'bottom', fontSize: '12px' },
        dataLabels: {
            enabled: true,
            formatter: function(val) { return val.toFixed(1) + '%'; }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
                }
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '55%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Tổng phải thu',
                            formatter: function(w) {
                                var sum = w.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0);
                                return new Intl.NumberFormat('vi-VN').format(sum) + ' đ';
                            }
                        }
                    }
                }
            }
        }
    }).render();

    // ── Province Pie Chart ──
    var provinceEl = document.querySelector('#provincePieChart');
    if (provinceEl) {
        var provinceSeries = [@foreach($topProvinces as $row){{ (float)$row->total }},@endforeach];
        var provinceLabels = [@foreach($topProvinces as $row)'{{ $row->province }}',@endforeach];

        if (provinceSeries.length > 0) {
            new ApexCharts(provinceEl, {
                chart: { type: 'donut', height: 300 },
                series: provinceSeries,
                labels: provinceLabels,
                colors: ['#0d6efd','#6610f2','#6f42c1','#d63384','#dc3545','#fd7e14','#ffc107','#198754','#20c997','#0dcaf0'],
                legend: { position: 'bottom', fontSize: '11px' },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) { return val.toFixed(1) + '%'; }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
                        }
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '50%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Tổng DS',
                                    formatter: function(w) {
                                        var sum = w.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0);
                                        return new Intl.NumberFormat('vi-VN').format(sum) + ' đ';
                                    }
                                }
                            }
                        }
                    }
                }
            }).render();
        }
    }
});
</script>
@endpush
