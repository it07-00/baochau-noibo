<div>
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
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 small">Tháng</label>
                    <select wire:model.live="filter_month" class="form-select form-select-sm">
                        <option value="">Cả năm</option>
                        @foreach($months as $m)
                            <option value="{{ $m }}">Tháng {{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" wire:click="$refresh" class="btn btn-success btn-sm w-100">Thống Kê</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Biểu đồ cột --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold">
                Biểu đồ thành tích doanh số —
                {{ $filter_month ? 'Tháng ' . $filter_month . '/' . $year : 'Năm ' . $year }}
            </h6>
        </div>
        <div class="card-body p-3">
            @if($chartHasData)
                <div id="achievementChartConfig" class="d-none"
                     data-labels='@json($chartLabels)'
                     data-values='@json($chartValues)'></div>
                <div style="height: 420px;">
                    <div id="achievementBarChart" wire:ignore style="height: 100%;"></div>
                </div>
            @else
                <div class="text-center text-muted py-5">Không có dữ liệu để hiển thị biểu đồ.</div>
            @endif
        </div>
    </div>

    {{-- Danh sách chi tiết --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold">Chi tiết xếp hạng</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px" class="text-center">Hạng</th>
                            <th>Nhân viên</th>
                            <th class="text-end">DS Tái ký</th>
                            <th class="text-end">DS Tiến độ</th>
                            <th class="text-end fw-bold">Tổng doanh số</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rankings as $i => $row)
                            @php
                                $rank = $i + 1;
                                $medal = match($rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => $rank };
                            @endphp
                            <tr class="{{ $row['total'] == 0 ? 'text-muted' : '' }}">
                                <td class="text-center fw-bold fs-6">{{ $medal }}</td>
                                <td class="fw-semibold">{{ $row['name'] }}</td>
                                <td class="text-end text-success">
                                    {{ $row['renewal'] > 0 ? number_format($row['renewal'], 0, ',', '.') : '—' }}
                                </td>
                                <td class="text-end text-warning">
                                    {{ $row['progressive'] > 0 ? number_format($row['progressive'], 0, ',', '.') : '—' }}
                                </td>
                                <td class="text-end fw-bold {{ $row['total'] > 0 ? 'text-dark' : '' }}">
                                    {{ $row['total'] > 0 ? number_format($row['total'], 0, ',', '.') . ' đ' : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/apexcharts.js') }}"></script>
<script>
(function () {
    let achievementChartInstance = null;

    function compactCurrency(value) {
        if (value >= 1000000000) return (value / 1000000000).toFixed(1) + 'B';
        if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
        if (value >= 1000) return (value / 1000).toFixed(1) + 'K';
        return value;
    }

    function renderAchievementBarChart() {
        const configEl = document.getElementById('achievementChartConfig');
        const chartEl = document.getElementById('achievementBarChart');
        if (!configEl || !chartEl || typeof ApexCharts === 'undefined') return;

        const labels = JSON.parse(configEl.dataset.labels || '[]');
        const values = JSON.parse(configEl.dataset.values || '[]').map(v => Number(v || 0));

        if (achievementChartInstance) {
            achievementChartInstance.destroy();
            achievementChartInstance = null;
        }

        const options = {
            chart: {
                type: 'bar',
                height: 420,
                toolbar: { show: false },
                animations: { enabled: true },
            },
            series: [{
                name: 'Tổng doanh số',
                data: values,
            }],
            colors: ['#16a34a'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '55%',
                },
            },
            dataLabels: { enabled: false },
            legend: { show: false },
            xaxis: {
                categories: labels,
                labels: {
                    rotate: -45,
                    trim: true,
                    style: { fontSize: '12px' },
                },
            },
            yaxis: {
                min: 0,
                labels: {
                    formatter: function(value) {
                        return compactCurrency(value) + ' vnd';
                    },
                },
            },
            grid: {
                borderColor: 'rgba(148, 163, 184, 0.25)',
                strokeDashArray: 2,
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value || 0) + ' đ';
                    },
                },
            },
        };

        achievementChartInstance = new ApexCharts(chartEl, options);
        achievementChartInstance.render();
    }

    document.addEventListener('DOMContentLoaded', renderAchievementBarChart);
    document.addEventListener('livewire:navigated', renderAchievementBarChart);
    window.addEventListener('achievement-chart-updated', renderAchievementBarChart);
})();
</script>
@endpush
