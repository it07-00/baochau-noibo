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
            <i class="fa-solid fa-download"></i>
            Xuất CSV
        </button>
    </div>

    <div class="card border-0 shadow-sm mb-4 report-filter-card rounded-12px overflow-hidden">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-xl-2 col-md-4">
                    <label class="form-label fw-semibold">Thời gian</label>
                    <select wire:model.live="period" class="form-select border-light-subtle rounded-8px shadow-sm">
                        @foreach($periodOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label fw-semibold">Từ ngày</label>
                    <input type="date" wire:model.live="dateFrom" class="form-control border-light-subtle rounded-8px shadow-sm">
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label fw-semibold">Đến ngày</label>
                    <input type="date" wire:model.live="dateTo" class="form-control border-light-subtle rounded-8px shadow-sm">
                </div>
                @if($canViewAllStaff)
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label fw-semibold">Nhân sự phụ trách</label>
                        <select wire:model.live="staffId" class="form-select border-light-subtle rounded-8px shadow-sm">
                            <option value="">Toàn bộ đội ngũ</option>
                            @foreach($filterOptions['staffs'] as $staff)
                                <option value="{{ $staff['id'] }}">{{ $staff['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-xl-3 col-md-6">
                    <label class="form-label fw-semibold">Dịch vụ</label>
                    <select wire:model.live="service" class="form-select border-light-subtle rounded-8px shadow-sm">
                        <option value="">Tất cả dịch vụ</option>
                        @foreach($filterOptions['services'] as $serviceOption)
                            <option value="{{ $serviceOption }}">{{ $serviceOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-3 col-md-4">
                    <label class="form-label fw-semibold">Khu vực</label>
                    <select wire:model.live="province" class="form-select border-light-subtle rounded-8px shadow-sm">
                        <option value="">Tất cả tỉnh/thành</option>
                        @foreach($filterOptions['provinces'] as $provinceOption)
                            <option value="{{ $provinceOption }}">{{ $provinceOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-3 col-md-4">
                    <label class="form-label fw-semibold">Trạng thái báo giá</label>
                    <select wire:model.live="status" class="form-select border-light-subtle rounded-8px shadow-sm">
                        <option value="">Tất cả trạng thái</option>
                        @foreach($filterOptions['statuses'] as $statusOption)
                            <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-3 col-md-4">
                    <button type="button" wire:click="resetFilters" class="btn btn-outline-secondary border-light-subtle w-100 rounded-8px shadow-sm fw-semibold">
                        <i class="fa-solid fa-rotate-left me-1"></i> Đặt lại bộ lọc
                    </button>
                </div>
            </div>
            @unless($canViewAllStaff)
                <div class="small text-muted mt-3">
                    Báo cáo đã khóa theo dữ liệu của bạn.
                </div>
            @endunless
        </div>
    </div>

    <div wire:loading.flex wire:target="period,dateFrom,dateTo,staffId,service,province,status,resetFilters" class="report-loading align-items-center gap-2 mb-3">
        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
        <span>Đang tổng hợp lại báo cáo...</span>
    </div>

    @if($canViewAllStaff && $report['data_quality']['total_issues'] > 0)
        <div class="alert alert-warning border-0 d-flex align-items-start gap-3 mb-4">
            <i class="fa-solid fa-triangle-exclamation fs-5"></i>
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
            ['key' => 'opportunities', 'label' => 'Báo giá', 'icon' => 'fa-solid fa-briefcase', 'type' => 'number'],
            ['key' => 'customers', 'label' => 'Khách hàng', 'icon' => 'fa-solid fa-users', 'type' => 'number'],
            ['key' => 'new_customers', 'label' => 'Khách hàng mới', 'icon' => 'fa-solid fa-user-plus', 'type' => 'number'],
            ['key' => 'conversion_rate', 'label' => 'Tỷ lệ chuyển đổi', 'icon' => 'fa-solid fa-filter', 'type' => 'percent'],
            ['key' => 'potential_value', 'label' => 'Giá trị tiềm năng', 'icon' => 'fa-solid fa-star', 'type' => 'money'],
            ['key' => 'revenue', 'label' => 'Doanh số ghi nhận', 'icon' => 'fa-solid fa-chart-line', 'type' => 'money'],
            ['key' => 'signed_contracts', 'label' => 'Hợp đồng đã ký', 'icon' => 'fa-solid fa-file-contract', 'type' => 'number'],
            ['key' => 'returning_customers', 'label' => 'Khách hàng quay lại', 'icon' => 'fa-solid fa-rotate', 'type' => 'number'],
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

                // Dynamic colors for each card to create a premium visual system
                $theme = match($card['key']) {
                    'opportunities' => ['bg' => '#2563eb', 'soft' => 'rgba(37, 99, 235, 0.05)'],
                    'customers' => ['bg' => '#6366f1', 'soft' => 'rgba(99, 102, 241, 0.05)'],
                    'new_customers' => ['bg' => '#0ea5e9', 'soft' => 'rgba(14, 165, 233, 0.05)'],
                    'conversion_rate' => ['bg' => '#10b981', 'soft' => 'rgba(16, 185, 129, 0.05)'],
                    'potential_value' => ['bg' => '#d97706', 'soft' => 'rgba(217, 119, 6, 0.05)'],
                    'revenue' => ['bg' => '#dc2626', 'soft' => 'rgba(220, 38, 38, 0.05)'],
                    'signed_contracts' => ['bg' => '#0d9488', 'soft' => 'rgba(13, 148, 136, 0.05)'],
                    'returning_customers' => ['bg' => '#475569', 'soft' => 'rgba(71, 85, 105, 0.05)'],
                    default => ['bg' => '#2563eb', 'soft' => 'rgba(37, 99, 235, 0.05)'],
                };
            @endphp
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 report-kpi-card" style="background: linear-gradient({{ $theme['soft'] }}, {{ $theme['soft'] }}), var(--bs-tertiary-bg) !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="report-kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.72rem; letter-spacing: 0.02em;">{{ $card['label'] }}</div>
                            <span class="rounded-circle shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 2rem; height: 2rem; background-color: var(--bs-card-bg) !important; color: {{ $theme['bg'] }} !important; border: 1px solid var(--bs-border-color-translucent);"><i class="{{ $card['icon'] }}"></i></span>
                        </div>
                        <div class="report-kpi-value text-dark fw-bold mt-2" style="font-size: 1.5rem; line-height: 1.2;">{{ $formattedValue }}</div>
                        <div class="mt-2.5 d-flex align-items-center flex-wrap gap-1">
                            @if($growth === null)
                                <span class="text-muted small" style="font-size: 0.72rem;">Chưa có kỳ đối chiếu</span>
                            @else
                                @if($growth >= 0)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 fw-bold" style="font-size: 0.7rem;">
                                        <i class="fa-solid fa-arrow-trend-up me-1"></i>{{ number_format(abs($growth), 1, ',', '.') }}%
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 fw-bold" style="font-size: 0.7rem;">
                                        <i class="fa-solid fa-arrow-trend-down me-1"></i>{{ number_format(abs($growth), 1, ',', '.') }}%
                                    </span>
                                @endif
                                <span class="text-muted small ms-1" style="font-size: 0.72rem;">so với kỳ trước</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100 rounded-12px overflow-hidden">
                <div class="card-header bg-transparent py-3.5 border-bottom border-light-subtle">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-chart-line text-primary fs-5"></i>
                        <h6 class="mb-0 fw-bold text-dark">Xu hướng báo giá và doanh số</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div id="potentialTrendConfig" class="d-none" data-chart='@json($report['trend'])'></div>
                    @if(array_sum($report['trend']['opportunities']) + array_sum($report['trend']['revenue']) > 0)
                        <div class="report-chart report-chart-lg"><canvas id="potentialTrendChart" wire:ignore></canvas></div>
                    @else
                        <div class="report-empty-state">Chưa có báo giá hoặc doanh số trong khoảng thời gian này.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100 rounded-12px overflow-hidden">
                <div class="card-header bg-transparent py-3.5 border-bottom border-light-subtle">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-indigo fs-5"></i>
                        <h6 class="mb-0 fw-bold text-dark">Cơ cấu trạng thái báo giá</h6>
                    </div>
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
            <div class="card border-0 shadow-sm h-100 rounded-12px overflow-hidden">
                <div class="card-header bg-transparent py-3.5 border-bottom border-light-subtle d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-cube text-primary fs-5"></i>
                        <h6 class="mb-0 fw-bold text-dark">Dịch vụ tiềm năng</h6>
                    </div>
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
            <div class="card border-0 shadow-sm h-100 rounded-12px overflow-hidden">
                <div class="card-header bg-transparent py-3.5 border-bottom border-light-subtle d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-location-dot text-success fs-5"></i>
                        <h6 class="mb-0 fw-bold text-dark">Khu vực tiềm năng</h6>
                    </div>
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
            <div class="card border-0 shadow-sm h-100 rounded-12px overflow-hidden">
                <div class="card-header bg-transparent py-3.5 border-bottom border-light-subtle">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-user-gear text-primary fs-5"></i>
                        <h6 class="mb-0 fw-bold text-dark">Hiệu suất nhân sự</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div id="potentialStaffConfig" class="d-none" data-chart='@json($report['staff_performance'])'></div>
                    @if(count($report['staff_performance']) > 0)
                        <div class="ratio ratio-21x9"><canvas id="potentialStaffChart" wire:ignore></canvas></div>
                    @else
                        <div class="report-empty-state">Chưa có dữ liệu hiệu suất.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100 rounded-12px overflow-hidden">
                <div class="card-header bg-transparent py-3.5 border-bottom border-light-subtle">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-lightbulb text-warning fs-5"></i>
                        <h6 class="mb-0 fw-bold text-dark">Định hướng đề xuất</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div id="potentialRecommendationsConfig" class="d-none" data-chart='@json($report['recommendations'])'></div>
                    @if(count($report['recommendations']) > 0)
                        <div class="ratio ratio-21x9"><canvas id="potentialRecommendationsChart" wire:ignore></canvas></div>
                    @else
                        <div class="report-empty-state">Chưa có đề xuất phù hợp với dữ liệu hiện tại.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-12px overflow-hidden">
        <div class="card-header bg-transparent py-3.5 border-bottom border-light-subtle d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-secondary fs-5"></i>
                <h6 class="mb-0 fw-bold text-dark">Báo giá gần nhất</h6>
            </div>
            <span class="badge bg-body-secondary text-secondary border border-light-subtle rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">Cập nhật {{ \Carbon\Carbon::parse($report['generated_at'])->format('H:i d/m/Y') }}</span>
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
                        text: dark ? '#94a3b8' : '#64748b',
                        subtext: dark ? '#64748b' : '#94a3b8',
                        grid: dark ? 'rgba(148,163,184,.10)' : 'rgba(100,116,139,.10)',
                        tooltipBg: dark ? '#1e293b' : '#ffffff',
                        tooltipText: dark ? '#f1f5f9' : '#0f172a',
                        tooltipBorder: dark ? 'rgba(148,163,184,.15)' : 'rgba(0,0,0,.06)'
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
                                { label: 'Báo giá', data: payload.opportunities || [], backgroundColor: 'rgba(37,99,235,.75)', borderRadius: 6, borderSkipped: false, yAxisID: 'y' },
                                { label: 'Ký hợp đồng', data: payload.won || [], backgroundColor: 'rgba(16,185,129,.75)', borderRadius: 6, borderSkipped: false, yAxisID: 'y' },
                                { label: 'Doanh số', type: 'line', data: payload.revenue || [], borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.10)', tension: .4, pointRadius: 4, pointBackgroundColor: '#f59e0b', fill: true, yAxisID: 'y1' }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: { duration: 500, easing: 'easeOutQuart' },
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { labels: { color: colors.text, usePointStyle: true, pointStyleWidth: 10, font: { size: 12 } } },
                                tooltip: {
                                    backgroundColor: colors.tooltipBg,
                                    titleColor: colors.tooltipText,
                                    bodyColor: colors.text,
                                    borderColor: colors.tooltipBorder,
                                    borderWidth: 1,
                                    padding: 10,
                                    callbacks: { label: ctx => ctx.dataset.yAxisID === 'y1' ? ctx.dataset.label + ': ' + money(ctx.raw) : ctx.dataset.label + ': ' + ctx.raw }
                                }
                            },
                            scales: {
                                x: { ticks: { color: colors.text, maxRotation: 0, autoSkip: true, font: { size: 11 } }, grid: { display: false } },
                                y: { beginAtZero: true, ticks: { color: colors.text, precision: 0, font: { size: 11 } }, grid: { color: colors.grid } },
                                y1: { beginAtZero: true, position: 'right', ticks: { color: colors.subtext, callback: value => money(value), font: { size: 11 } }, grid: { drawOnChartArea: false } }
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
                                hoverOffset: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            animation: { duration: 500, easing: 'easeOutQuart' },
                            plugins: {
                                legend: { position: 'bottom', labels: { color: colors.text, usePointStyle: true, pointStyleWidth: 10, padding: 16, font: { size: 12 } } },
                                tooltip: {
                                    backgroundColor: colors.tooltipBg,
                                    titleColor: colors.tooltipText,
                                    bodyColor: colors.text,
                                    borderColor: colors.tooltipBorder,
                                    borderWidth: 1,
                                    padding: 10
                                }
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
                            animation: { duration: 500, easing: 'easeOutQuart' },
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { labels: { color: colors.text, usePointStyle: true, pointStyleWidth: 10, font: { size: 12 } } },
                                tooltip: {
                                    backgroundColor: colors.tooltipBg,
                                    titleColor: colors.tooltipText,
                                    bodyColor: colors.text,
                                    borderColor: colors.tooltipBorder,
                                    borderWidth: 1,
                                    padding: 10,
                                    callbacks: settings.tooltipCallbacks || {}
                                }
                            },
                            scales: {
                                x: { beginAtZero: true, ticks: { color: colors.text, font: { size: 11 } }, grid: { color: colors.grid } },
                                y: { ticks: { color: colors.text, autoSkip: false, font: { size: 11 } }, grid: { display: false } }
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
                            { label: 'Báo giá', data: payload.map(item => item.opportunities), backgroundColor: 'rgba(37,99,235,.78)', borderRadius: 6 },
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
                window.addEventListener('potential-report-updated', () => setTimeout(window.renderPotentialReportCharts, 150));

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
