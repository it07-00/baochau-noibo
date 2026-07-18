<div class="d-grid gap-4">
    <div class="d-flex align-items-start align-items-sm-center justify-content-between flex-wrap gap-3 p-3 p-md-4 bg-body border border-light-subtle rounded-3">
        <div>
            <h4 class="mb-1 fw-bold">Bảng xếp hạng</h4>
            <p class="mb-0 small text-muted">Theo dõi kết quả nhân sự, khách hàng và dịch vụ theo năm.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <label for="ranking-year" class="small fw-semibold text-muted">Năm</label>
            <select id="ranking-year" wire:model.live="year" class="form-select form-select-sm border-light-subtle rounded-2" >
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
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
                <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-2 p-2"><i class="fa-solid fa-trophy"></i></span>
                    <h6 class="mb-0 fw-bold">Doanh số nhân viên Kinh doanh — {{ $year }}</h6>
                </div>
                <div class="card-body p-3"
                    x-data="{ render() { if (window.renderRankingsBoardCharts) window.renderRankingsBoardCharts(); } }"
                    x-init="setTimeout(() => render(), 100)">
                    @if($salesRankings->isNotEmpty())
                    <div id="salesRankingChartConfig" class="d-none"
                        data-labels='@json($salesRankings->pluck("name")->values())'
                        data-totals='@json($salesRankings->pluck("total")->map(fn ($v) => (float) $v)->values())'>
                    </div>

                    <div id="salesRankingBarChart" wire:ignore class="mnh-360px"></div>

                    <div class="mt-2">
                        <div class="d-flex align-items-center justify-content-between gap-3 border border-light-subtle rounded-3 px-3 py-2 bg-body-secondary">
                            <div class="small text-muted">Tổng doanh số 6 loại hợp đồng</div>
                            <div class="fw-semibold text-primary">{{ number_format($salesRankings->sum('total'), 0, ',', '.') }} đ</div>
                        </div>
                    </div>
                    @else
                    <div class="text-center text-muted py-4">Không có dữ liệu</div>
                    @endif
                </div>
            </div>
        </div>


        @endif

        @if($canSeeConsulting)
        {{-- Xếp hạng nhân viên tư vấn --}}
        <div class="{{ $canSeeSales ? 'col-lg-5' : 'col-12' }}">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
                <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center bg-info-subtle text-info rounded-2 p-2"><i class="fa-solid fa-user-tie"></i></span>
                    <h6 class="mb-0 fw-bold">Nhân viên Tư vấn — {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th class="text-center w-42px" >Hạng</th>
                                <th>Nhân viên</th>
                                <th class="text-center">Số HĐ</th>
                                <th class="text-center">Hoàn thành</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($consultingRankings as $i => $row)
                            <tr class="{{ $row['count'] == 0 ? 'text-muted' : '' }}">
                                <td class="text-center fw-bold">{{ $this->rankMedal($i) }}</td>
                                <td class="fw-semibold">{{ $row['name'] }}</td>
                                <td class="text-center">{{ $row['count'] > 0 ? $row['count'] : '—' }}</td>
                                <td class="text-center text-success">{{ $row['completed'] > 0 ? $row['completed'] : '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                            @endforelse
                        </tbody>
                        @if($consultingRankings->isNotEmpty())
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="2">Tổng</td>
                                <td class="text-center">{{ $consultingRankings->sum('count') }}</td>
                                <td class="text-center text-success">{{ $consultingRankings->sum('completed') }}</td>
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

    @if($canSeeTechnical)
    <div class="row g-4 mb-4">
        {{-- Xếp hạng nhân viên kỹ thuật --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning rounded-2 p-2"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                    <h6 class="mb-0 fw-bold">Nhân viên Kỹ thuật — {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th class="text-center w-42px" >Hạng</th>
                                <th>Nhân viên</th>
                                <th class="text-center">Số HĐ</th>
                                <th class="text-center">Hoàn thành</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($technicalRankings as $i => $row)
                            <tr class="{{ ($canSeeFinance && $row['value'] == 0) || (!$canSeeFinance && $row['count'] == 0) ? 'text-muted' : '' }}">
                                <td class="text-center fw-bold">{{ $this->rankMedal($i) }}</td>
                                <td class="fw-semibold">{{ $row['name'] }}</td>
                                <td class="text-center">{{ $row['count'] > 0 ? $row['count'] : '—' }}</td>
                                <td class="text-center text-success">{{ $row['completed'] > 0 ? $row['completed'] : '—' }}</td>
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
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($canSeeSales)

    <div class="row g-4">
        {{-- Top khách hàng --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-2 p-2"><i class="fa-solid fa-building"></i></span>
                    <h6 class="mb-0 fw-bold">Top khách hàng theo giá trị HĐ — {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase">
                                <tr>
                                    <th class="text-center w-42px" >#</th>
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
                                    <td class="text-center text-muted ">{{ $i + 1 }}</td>
                                    <td class="fw-semibold">{{ $row->name }}</td>
                                    <td class="text-center">{{ $row->waste_count > 0 ? $row->waste_count : '—' }}</td>
                                    <td class="text-end ">{{ $row->waste_value > 0 ? number_format($row->waste_value, 0, ',', '.') : '—' }}</td>
                                    <td class="text-center">{{ $row->consult_count > 0 ? $row->consult_count : '—' }}</td>
                                    <td class="text-end ">{{ $row->consult_value > 0 ? number_format($row->consult_value, 0, ',', '.') : '—' }}</td>
                                    <td class="text-end fw-bold text-body">{{ number_format($row->waste_value + $row->consult_value, 0, ',', '.') }} đ</td>
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
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-2 p-2"><i class="fa-solid fa-chart-line"></i></span>
                    <h6 class="mb-0 fw-bold">Top dịch vụ báo giá — {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th class="text-center w-42px" >#</th>
                                <th>Dịch vụ</th>
                                <th class="text-center">Số BG</th>
                                <th class="text-end">Doanh số</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topServices as $i => $row)
                            <tr>
                                <td class="text-center text-muted">{{ $i + 1 }}</td>
                                <td class=" text-muted max-w-180px" >{{ $row->service }}</td>
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
    </div>
    @endif
</div>

@push('scripts')
<script src="{{ asset('assets/js/apexcharts.js') }}?v={{ config('app.version') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    window.rankingsBoardCharts = window.rankingsBoardCharts || {};

    function parseJsonData(el, key) {
        if (!el || !el.dataset || !el.dataset[key]) return [];
        try {
            return JSON.parse(el.dataset[key]);
        } catch (e) {
            return [];
        }
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('vi-VN').format(value || 0) + ' đ';
    }

    function destroyChart(chartKey) {
        if (window.rankingsBoardCharts[chartKey]) {
            window.rankingsBoardCharts[chartKey].destroy();
            window.rankingsBoardCharts[chartKey] = null;
        }
    }

    window.renderRankingsBoardCharts = function () {
        if (typeof ApexCharts === 'undefined') return;

        var salesConfig = document.querySelector('#salesRankingChartConfig');
        var salesChartEl = document.querySelector('#salesRankingBarChart');

        if (salesConfig && salesChartEl) {
            var labels = parseJsonData(salesConfig, 'labels');
            var totals = parseJsonData(salesConfig, 'totals');

            destroyChart('salesRanking');
            salesChartEl.innerHTML = '';

            if (labels.length) {
                window.rankingsBoardCharts.salesRanking = new ApexCharts(salesChartEl, {
                    chart: {
                        type: 'bar',
                        height: 360,
                        toolbar: { show: false }
                    },
                    series: [
                        { name: 'Tổng doanh số', data: totals }
                    ],
                    xaxis: {
                        categories: labels,
                        labels: {
                            rotate: -15,
                            trim: true,
                            style: { fontSize: '12px' }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function (val) { return formatCurrency(val); }
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '44%',
                            borderRadius: 4
                        }
                    },
                    colors: ['#0d6efd'],
                    stroke: {
                        show: true,
                        width: 1,
                        colors: ['transparent']
                    },
                    dataLabels: { enabled: false },
                    legend: {
                        show: false
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) { return formatCurrency(val); }
                        }
                    },
                    grid: {
                        borderColor: '#e9ecef'
                    }
                });

                window.rankingsBoardCharts.salesRanking.render();
            }
        }
    };

    window.renderRankingsBoardCharts();

    document.addEventListener('livewire:navigated', function () {
        window.renderRankingsBoardCharts();
    });
});
</script>
@endpush
