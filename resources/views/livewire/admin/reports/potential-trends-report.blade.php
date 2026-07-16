<div class="potential-report" x-data x-init="setTimeout(() => window.renderPotentialReportCharts?.(), 120)" @potential-report-updated.window="setTimeout(() => window.renderPotentialReportCharts?.(), 120)">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/potential-trends-report.css') }}?v={{ config('app.version') }}">
    @endpush

    <div class="report-page-header d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h4 class="mb-1">Báo cáo xu hướng tiềm năng</h4>
            <p class="text-muted mb-0">
                Cơ hội từ báo giá, doanh số từ 6 nhóm hợp đồng và định hướng dựa trên dữ liệu thực tế.
            </p>
        </div>
        <button type="button" wire:click="exportCsv" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-download"></i>
            Xuất CSV
        </button>
    </div>

    <div class="card border-0 shadow-sm mb-4 report-filter-card">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-xl-2 col-md-4">
                    <label class="form-label fw-semibold">Thời gian</label>
                    <select wire:model.live="period" class="form-select">
                        @foreach($periodOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label fw-semibold">Từ ngày</label>
                    <input type="date" wire:model.live="dateFrom" class="form-control">
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label fw-semibold">Đến ngày</label>
                    <input type="date" wire:model.live="dateTo" class="form-control">
                </div>
                @if($canViewAllStaff)
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label fw-semibold">Nhân sự phụ trách</label>
                        <select wire:model.live="staffId" class="form-select">
                            <option value="">Toàn bộ đội ngũ</option>
                            @foreach($filterOptions['staffs'] as $staff)
                                <option value="{{ $staff['id'] }}">{{ $staff['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-xl-3 col-md-6">
                    <label class="form-label fw-semibold">Dịch vụ</label>
                    <select wire:model.live="service" class="form-select">
                        <option value="">Tất cả dịch vụ</option>
                        @foreach($filterOptions['services'] as $serviceOption)
                            <option value="{{ $serviceOption }}">{{ $serviceOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-3 col-md-4">
                    <label class="form-label fw-semibold">Khu vực</label>
                    <select wire:model.live="province" class="form-select">
                        <option value="">Tất cả tỉnh/thành</option>
                        @foreach($filterOptions['provinces'] as $provinceOption)
                            <option value="{{ $provinceOption }}">{{ $provinceOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-3 col-md-4">
                    <label class="form-label fw-semibold">Trạng thái báo giá</label>
                    <select wire:model.live="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        @foreach($filterOptions['statuses'] as $statusOption)
                            <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-3 col-md-4">
                    <button type="button" wire:click="resetFilters" class="btn btn-light border w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Đặt lại bộ lọc
                    </button>
                </div>
                <div class="col-xl-3 d-flex align-items-center">
                    <div class="small text-muted">
                        Dữ liệu {{ \Carbon\Carbon::parse($report['period']['from'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($report['period']['to'])->format('d/m/Y') }}
                    </div>
                </div>
            </div>
            <div class="small text-muted mt-3">
                Trạng thái chỉ lọc dữ liệu báo giá. Doanh số luôn lấy từ hợp đồng có ngày xuất hóa đơn trong kỳ.
                @unless($canViewAllStaff)
                    Báo cáo đã khóa theo dữ liệu của bạn.
                @endunless
            </div>
        </div>
    </div>

    <div wire:loading.flex wire:target="period,dateFrom,dateTo,staffId,service,province,status,resetFilters" class="report-loading align-items-center gap-2 mb-3">
        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
        <span>Đang tổng hợp lại báo cáo...</span>
    </div>

    @if($canViewAllStaff && $report['data_quality']['total_issues'] > 0)
        <div class="alert alert-warning border-0 d-flex align-items-start gap-3 mb-4">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>
                <div class="fw-bold mb-1">Có {{ number_format($report['data_quality']['total_issues']) }} điểm thiếu dữ liệu trong kỳ</div>
                <div class="small">
                    Báo giá: {{ number_format($report['data_quality']['quotations']['missing_service']) }} thiếu dịch vụ,
                    {{ number_format($report['data_quality']['quotations']['missing_province']) }} thiếu khu vực,
                    {{ number_format($report['data_quality']['quotations']['missing_customer']) }} thiếu khách hàng.
                    Hợp đồng: {{ number_format($report['data_quality']['contracts']['missing_service']) }} thiếu loại dịch vụ,
                    {{ number_format($report['data_quality']['contracts']['missing_province']) }} thiếu khu vực.
                </div>
            </div>
        </div>
    @endif

    @php
        $kpiCards = [
            ['key' => 'opportunities', 'label' => 'Cơ hội báo giá', 'icon' => 'bi-briefcase', 'type' => 'number'],
            ['key' => 'customers', 'label' => 'Khách hàng', 'icon' => 'bi-people', 'type' => 'number'],
            ['key' => 'new_customers', 'label' => 'Khách hàng mới', 'icon' => 'bi-person-plus', 'type' => 'number'],
            ['key' => 'conversion_rate', 'label' => 'Tỷ lệ chuyển đổi', 'icon' => 'bi-funnel', 'type' => 'percent'],
            ['key' => 'potential_value', 'label' => 'Giá trị tiềm năng', 'icon' => 'bi-stars', 'type' => 'money'],
            ['key' => 'revenue', 'label' => 'Doanh số ghi nhận', 'icon' => 'bi-graph-up-arrow', 'type' => 'money'],
            ['key' => 'signed_contracts', 'label' => 'Hợp đồng đã ký', 'icon' => 'bi-file-earmark-check', 'type' => 'number'],
            ['key' => 'returning_customers', 'label' => 'Khách hàng quay lại', 'icon' => 'bi-arrow-repeat', 'type' => 'number'],
        ];
    @endphp
    <div class="row g-3 mb-4">
        @foreach($kpiCards as $card)
            @php
                $metric = $report['kpis'][$card['key']];
                $growth = $metric['growth'];
                $formattedValue = match($card['type']) {
                    'money' => number_format($metric['value'] / 1000000, 1, ',', '.') . ' tr',
                    'percent' => number_format($metric['value'], 2, ',', '.') . '%',
                    default => number_format($metric['value']),
                };
            @endphp
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 report-kpi-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="report-kpi-label">{{ $card['label'] }}</div>
                            <span class="report-kpi-icon"><i class="bi {{ $card['icon'] }}"></i></span>
                        </div>
                        <div class="report-kpi-value">{{ $formattedValue }}</div>
                        <div class="small mt-2">
                            @if($growth === null)
                                <span class="text-muted">Chưa có kỳ đối chiếu</span>
                            @else
                                <span class="{{ $growth >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                                    <i class="bi {{ $growth >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                    {{ number_format(abs($growth), 2, ',', '.') }}%
                                </span>
                                <span class="text-muted"> so với kỳ trước</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-1 fw-bold">Xu hướng cơ hội và doanh số</h6>
                    <div class="small text-muted">Tự động nhóm theo ngày, tuần, tháng hoặc quý dựa trên khoảng lọc.</div>
                </div>
                <div class="card-body">
                    <div id="potentialTrendConfig" class="d-none" data-chart='@json($report['trend'])'></div>
                    @if(array_sum($report['trend']['opportunities']) + array_sum($report['trend']['revenue']) > 0)
                        <div class="report-chart report-chart-lg"><canvas id="potentialTrendChart" wire:ignore></canvas></div>
                    @else
                        <div class="report-empty-state">Chưa có cơ hội hoặc doanh số trong khoảng thời gian này.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-1 fw-bold">Cơ cấu trạng thái báo giá</h6>
                    <div class="small text-muted">Tỷ trọng các cơ hội trong kỳ.</div>
                </div>
                <div class="card-body">
                    <div id="potentialStatusConfig" class="d-none" data-chart='@json($report['status_breakdown'])'></div>
                    @if(collect($report['status_breakdown'])->sum('count') > 0)
                        <div class="report-chart report-chart-lg"><canvas id="potentialStatusChart" wire:ignore></canvas></div>
                    @else
                        <div class="report-empty-state">Chưa có dữ liệu trạng thái báo giá.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 fw-bold">Dịch vụ tiềm năng</h6>
                        <div class="small text-muted">Điểm từ lượng cơ hội, giá trị, chuyển đổi và tăng trưởng.</div>
                    </div>
                    <i class="bi bi-grid text-primary fs-5"></i>
                </div>
                <div class="card-body">
                    <div id="potentialServicesConfig" class="d-none" data-chart='@json($report['services'])'></div>
                    @if(count($report['services']) > 0)
                        <div class="report-chart report-chart-ranking"><canvas id="potentialServicesChart" wire:ignore></canvas></div>
                    @else
                        <div class="report-empty-state">Chưa có dữ liệu dịch vụ.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 fw-bold">Khu vực tiềm năng</h6>
                        <div class="small text-muted">Kết hợp cơ hội báo giá và doanh số hợp đồng theo tỉnh/thành.</div>
                    </div>
                    <i class="bi bi-geo-alt text-primary fs-5"></i>
                </div>
                <div class="card-body">
                    <div id="potentialRegionsConfig" class="d-none" data-chart='@json($report['regions'])'></div>
                    @if(count($report['regions']) > 0)
                        <div class="report-chart report-chart-ranking"><canvas id="potentialRegionsChart" wire:ignore></canvas></div>
                    @else
                        <div class="report-empty-state">Chưa có dữ liệu khu vực.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-1 fw-bold">Hiệu suất nhân sự</h6>
                    <div class="small text-muted">Doanh số chỉ tính hợp đồng có ngày xuất hóa đơn.</div>
                </div>
                <div class="card-body">
                    <div id="potentialStaffConfig" class="d-none" data-chart='@json($report['staff_performance'])'></div>
                    @if(count($report['staff_performance']) > 0)
                        <div class="report-chart report-chart-ranking"><canvas id="potentialStaffChart" wire:ignore></canvas></div>
                    @else
                        <div class="report-empty-state">Chưa có dữ liệu hiệu suất.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-1 fw-bold">Định hướng đề xuất</h6>
                    <div class="small text-muted">Sinh tự động bằng quy tắc có thể kiểm chứng.</div>
                </div>
                <div class="card-body">
                    <div id="potentialRecommendationsConfig" class="d-none" data-chart='@json($report['recommendations'])'></div>
                    <div class="report-chart report-chart-ranking"><canvas id="potentialRecommendationsChart" wire:ignore></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1 fw-bold">Cơ hội gần nhất</h6>
                <div class="small text-muted">12 báo giá mới nhất trong phạm vi bộ lọc.</div>
            </div>
            <span class="badge bg-body-secondary text-body border">Cập nhật {{ \Carbon\Carbon::parse($report['generated_at'])->format('H:i d/m/Y') }}</span>
        </div>
        <div class="card-body">
            <div id="potentialRecentConfig" class="d-none" data-chart='@json($report['recent_opportunities'])'></div>
            @if(count($report['recent_opportunities']) > 0)
                <div class="report-chart report-chart-recent"><canvas id="potentialRecentChart" wire:ignore></canvas></div>
            @else
                <div class="report-empty-state">Không có báo giá phù hợp bộ lọc.</div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}?v={{ config('app.version') }}"></script>
        <script>
            (function () {
                function theme() {
                    const dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                    return {
                        text: dark ? '#cbd5e1' : '#64748b',
                        grid: dark ? 'rgba(148,163,184,.16)' : 'rgba(100,116,139,.12)',
                        tooltipBg: dark ? '#111827' : '#ffffff',
                        tooltipText: dark ? '#f8fafc' : '#0f172a'
                    };
                }

                function destroy(id) {
                    const canvas = document.getElementById(id);
                    if (canvas?._chartInstance) {
                        canvas._chartInstance.destroy();
                        canvas._chartInstance = null;
                    }
                }

                function money(value) {
                    return new Intl.NumberFormat('vi-VN', { notation: 'compact', maximumFractionDigits: 1 }).format(value) + ' đ';
                }

                function renderTrend() {
                    const config = document.getElementById('potentialTrendConfig');
                    const canvas = document.getElementById('potentialTrendChart');
                    if (!config || !canvas || typeof Chart === 'undefined') return;
                    destroy('potentialTrendChart');
                    const payload = JSON.parse(config.dataset.chart || '{}');
                    const colors = theme();
                    canvas._chartInstance = new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: payload.labels || [],
                            datasets: [
                                { label: 'Cơ hội', data: payload.opportunities || [], backgroundColor: 'rgba(37,99,235,.72)', borderRadius: 5, yAxisID: 'y' },
                                { label: 'Ký hợp đồng', data: payload.won || [], backgroundColor: 'rgba(16,185,129,.72)', borderRadius: 5, yAxisID: 'y' },
                                { label: 'Doanh số', type: 'line', data: payload.revenue || [], borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.14)', tension: .35, pointRadius: 3, yAxisID: 'y1' }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { labels: { color: colors.text, usePointStyle: true } },
                                tooltip: {
                                    backgroundColor: colors.tooltipBg,
                                    titleColor: colors.tooltipText,
                                    bodyColor: colors.tooltipText,
                                    callbacks: { label: ctx => ctx.dataset.yAxisID === 'y1' ? ctx.dataset.label + ': ' + money(ctx.raw) : ctx.dataset.label + ': ' + ctx.raw }
                                }
                            },
                            scales: {
                                x: { ticks: { color: colors.text, maxRotation: 0, autoSkip: true }, grid: { display: false } },
                                y: { beginAtZero: true, ticks: { color: colors.text, precision: 0 }, grid: { color: colors.grid } },
                                y1: { beginAtZero: true, position: 'right', ticks: { color: colors.text, callback: value => money(value) }, grid: { drawOnChartArea: false } }
                            }
                        }
                    });
                }

                function renderStatus() {
                    const config = document.getElementById('potentialStatusConfig');
                    const canvas = document.getElementById('potentialStatusChart');
                    if (!config || !canvas || typeof Chart === 'undefined') return;
                    destroy('potentialStatusChart');
                    const payload = JSON.parse(config.dataset.chart || '[]');
                    const colors = theme();
                    canvas._chartInstance = new Chart(canvas, {
                        type: 'doughnut',
                        data: {
                            labels: payload.map(item => item.label),
                            datasets: [{
                                data: payload.map(item => item.count),
                                backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#7c3aed', '#0ea5e9'],
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '68%',
                            plugins: {
                                legend: { position: 'bottom', labels: { color: colors.text, usePointStyle: true, padding: 16 } },
                                tooltip: { backgroundColor: colors.tooltipBg, titleColor: colors.tooltipText, bodyColor: colors.tooltipText }
                            }
                        }
                    });
                }

                function renderHorizontalChart(configId, canvasId, settings) {
                    const config = document.getElementById(configId);
                    const canvas = document.getElementById(canvasId);
                    if (!config || !canvas || typeof Chart === 'undefined') return;
                    destroy(canvasId);
                    const payload = JSON.parse(config.dataset.chart || '[]');
                    const colors = theme();
                    canvas._chartInstance = new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: payload.map(settings.label),
                            datasets: settings.datasets(payload)
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { labels: { color: colors.text, usePointStyle: true } },
                                tooltip: {
                                    backgroundColor: colors.tooltipBg,
                                    titleColor: colors.tooltipText,
                                    bodyColor: colors.tooltipText,
                                    callbacks: settings.tooltipCallbacks || {}
                                }
                            },
                            scales: {
                                x: { beginAtZero: true, ticks: { color: colors.text }, grid: { color: colors.grid } },
                                y: { ticks: { color: colors.text, autoSkip: false }, grid: { display: false } }
                            }
                        }
                    });
                }

                function renderServices() {
                    renderHorizontalChart('potentialServicesConfig', 'potentialServicesChart', {
                        label: item => item.label,
                        datasets: payload => [
                            { label: 'Điểm tiềm năng', data: payload.map(item => item.score), backgroundColor: 'rgba(37,99,235,.78)', borderRadius: 6 },
                            { label: 'Tỷ lệ chuyển đổi (%)', data: payload.map(item => item.conversion_rate), backgroundColor: 'rgba(16,185,129,.68)', borderRadius: 6 }
                        ]
                    });
                }

                function renderRegions() {
                    renderHorizontalChart('potentialRegionsConfig', 'potentialRegionsChart', {
                        label: item => item.label,
                        datasets: payload => [
                            { label: 'Điểm tiềm năng', data: payload.map(item => item.score), backgroundColor: 'rgba(37,99,235,.78)', borderRadius: 6 },
                            { label: 'Tỷ lệ chuyển đổi (%)', data: payload.map(item => item.conversion_rate), backgroundColor: 'rgba(245,158,11,.72)', borderRadius: 6 }
                        ],
                        tooltipCallbacks: { afterBody: items => 'Doanh số: ' + money(payloadValue(items, 'revenue')) }
                    });
                }

                function payloadValue(items, key) {
                    const configId = items?.[0]?.chart?.canvas?.id === 'potentialRegionsChart' ? 'potentialRegionsConfig' : 'potentialStaffConfig';
                    const payload = JSON.parse(document.getElementById(configId)?.dataset.chart || '[]');
                    return payload[items?.[0]?.dataIndex]?.[key] || 0;
                }

                function renderStaff() {
                    renderHorizontalChart('potentialStaffConfig', 'potentialStaffChart', {
                        label: item => item.name,
                        datasets: payload => [
                            { label: 'Cơ hội', data: payload.map(item => item.opportunities), backgroundColor: 'rgba(37,99,235,.78)', borderRadius: 6 },
                            { label: 'Đã ký', data: payload.map(item => item.won), backgroundColor: 'rgba(16,185,129,.72)', borderRadius: 6 }
                        ],
                        tooltipCallbacks: { afterBody: items => 'Doanh số: ' + money(payloadValue(items, 'revenue')) }
                    });
                }

                function renderRecommendations() {
                    const priorityScore = { 'Cao': 3, 'Trung bình': 2, 'Thấp': 1 };
                    renderHorizontalChart('potentialRecommendationsConfig', 'potentialRecommendationsChart', {
                        label: item => item.title,
                        datasets: payload => [{
                            label: 'Mức ưu tiên',
                            data: payload.map(item => priorityScore[item.priority] || 1),
                            backgroundColor: payload.map(item => item.priority === 'Cao' ? 'rgba(220,53,69,.78)' : (item.priority === 'Trung bình' ? 'rgba(245,158,11,.76)' : 'rgba(37,99,235,.72)')),
                            borderRadius: 6
                        }],
                        tooltipCallbacks: {
                            label: context => 'Ưu tiên: ' + (['', 'Thấp', 'Trung bình', 'Cao'][context.raw] || 'Thấp')
                        }
                    });
                }

                function renderRecent() {
                    renderHorizontalChart('potentialRecentConfig', 'potentialRecentChart', {
                        label: item => `${item.customer} · ${item.date || 'Chưa có ngày'}`,
                        datasets: payload => [{
                            label: 'Giá trị báo giá',
                            data: payload.map(item => item.value),
                            backgroundColor: 'rgba(37,99,235,.76)',
                            borderRadius: 6
                        }],
                        tooltipCallbacks: { label: context => 'Giá trị: ' + money(context.raw) }
                    });
                }

                window.renderPotentialReportCharts = function () {
                    renderTrend();
                    renderStatus();
                    renderServices();
                    renderRegions();
                    renderStaff();
                    renderRecommendations();
                    renderRecent();
                };

                document.addEventListener('DOMContentLoaded', () => setTimeout(window.renderPotentialReportCharts, 120));
                document.addEventListener('livewire:navigated', () => setTimeout(window.renderPotentialReportCharts, 120));

                if (!window.__potentialReportThemeListener) {
                    window.__potentialReportThemeListener = true;
                    document.addEventListener('click', event => {
                        if (event.target.closest('[data-bs-theme-value]')) {
                            setTimeout(window.renderPotentialReportCharts, 180);
                        }
                    });
                }
            })();
        </script>
    @endpush
</div>
