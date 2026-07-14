<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/statistics-board.css') }}?v={{ config('app.version') }}">
    @endpush
    @unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
    <div class="statistics-page-header page-header d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">Bảng thống kê</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">Hệ thống</li>
                    <li class="breadcrumb-item active">Bảng thống kê</li>
                </ol>
            </nav>
        </div>
        <div class="statistics-filter-bar d-flex gap-2 flex-wrap justify-content-end">
            @if($canFilterStaff)
                <select wire:model.live="filter_staff" class="form-select statistics-filter-control mnw-200px min-h-42px" style="font-weight: 500;">
                    <option value="">Tất cả nhân viên</option>
                    @foreach($staffs as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            @endif
            <select wire:model.live="month" class="form-select statistics-filter-control mnw-170px min-h-42px" >
                <option value="">Cả năm</option>
                @for($m = 1; $m <= $this->maximumVisibleMonth(); $m++)
                    <option value="{{ $m }}">Tháng {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                @endfor
            </select>
            <select wire:model.live="year" class="form-select statistics-filter-control mnw-170px min-h-42px" >
                @foreach($years as $y)
                    <option value="{{ $y }}">Năm {{ $y }}</option>
                @endforeach
            </select>
            <input
                type="date"
                wire:model.live="contractDateFrom"
                class="form-control statistics-filter-control statistics-date-control mnw-195px min-h-42px"
                title="Lọc hợp đồng từ ngày ký"
            >
            <input
                type="date"
                wire:model.live="contractDateTo"
                class="form-control statistics-filter-control statistics-date-control mnw-195px min-h-42px"
                title="Lọc hợp đồng đến ngày ký"
            >
            <button
                type="button"
                wire:click="clearContractDateFilter"
                class="btn btn-outline-secondary px-3 statistics-clear-date min-h-42px"
                @disabled($contractDateFrom === '' && $contractDateTo === '')
            >
                Xóa ngày
            </button>
        </div>
    </div>
    @endunless

    @if($dailyReportReminder && !auth()->user()->hasAnyRole(['tu-van', 'ky-thuat', 'kinh-doanh', 'tp-kinh-doanh']))
        <div class="daily-report-reminder-alert alert bg-warning-subtle border-0 shadow-sm mb-4 d-flex align-items-center gap-3 py-3 px-4 rounded-3 border-start border-warning border-4" >
            <div class="rounded-circle bg-warning bg-opacity-25 d-flex align-items-center justify-content-center flex-shrink-0 wh-44" >
                <i class="fa-solid fa-clock text-warning fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-0 fw-bold text-body">Bạn chưa gửi báo cáo ngày hôm nay</h6>
                <p class="mb-0  text-muted">Vui lòng gửi báo cáo trước khi kết thúc ngày làm việc.</p>
            </div>
            <a href="{{ route('app.daily-reports.index') }}" class="btn btn-warning btn-sm px-3 fw-bold shadow-sm rounded-8px" >
                <i class="fa-solid fa-pen-square me-1"></i> Gửi báo cáo
            </a>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4 border-start border-3 border-primary">
        <div class="card-body py-3 px-4 d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <i class="fa-solid fa-calendar-week text-primary fs-5"></i>
                <span class="fw-bold text-body">Lịch công tác của bạn</span>
            </div>
            <div class="vr opacity-25 d-none d-md-block flex-shrink-0"></div>
            <div class="d-flex align-items-center gap-2 flex-wrap flex-grow-1">
                <span class="badge bg-primary text-white px-3 py-2 fs-12px">
                    <i class="fa-solid fa-calendar-check me-1"></i>Hôm nay: {{ number_format($workScheduleSummary['today_total'] ?? 0) }}
                </span>
                <span class="badge bg-warning text-dark px-3 py-2 fs-12px">
                    <i class="fa-solid fa-calendar-day me-1"></i>Ngày mai: {{ number_format($workScheduleSummary['upcoming_tomorrow'] ?? 0) }}
                </span>
                @if(($workScheduleSummary['overdue'] ?? 0) > 0)
                <span class="badge bg-danger text-white px-3 py-2 fs-12px">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>Quá hạn: {{ number_format($workScheduleSummary['overdue']) }}
                </span>
                @endif
                @if($workScheduleRecentItems->isNotEmpty())
                <div class="vr opacity-25 d-none d-md-block flex-shrink-0"></div>
                @endif
                @foreach($workScheduleRecentItems as $item)
                <span class="badge rounded-pill {{ $item['status_class'] }} px-3 py-2 fs-12px text-truncate" style="max-width: 200px;" title="{{ $item['title'] }}">
                    {{ $item['time_label'] !== 'Cả ngày' ? $item['time_label'] . ' · ' : '' }}{{ $item['title'] }}
                </span>
                @endforeach
                @if($workScheduleRecentItems->isEmpty())
                <span class="text-muted fst-italic">Chưa có lịch hôm nay.</span>
                @endif
            </div>
            <a href="{{ route('app.work-schedules.index') }}" class="btn btn-outline-primary btn-sm flex-shrink-0">
                <i class="fa-solid fa-circle-arrow-right me-1"></i> Xem tất cả
            </a>
        </div>
    </div>

    @if($isIT && $itStats)
        {{-- IT Admin Stats Panels --}}
        @include('livewire.admin.statistics-board._it_overview')
    @else
        {{-- ── BUSINESS DASHBOARD VIEW ──────────────────────────────── --}}

        {{-- [2] KPI Tổng quan --}}
        @include('livewire.admin.statistics-board._kpi_cards')

        {{-- [3] Tổng quan vận hành & Lịch --}}
        @include('livewire.admin.statistics-board._operational_overview')

        {{-- [4] Xu hướng & Phân tích (Charts) --}}
        @include('livewire.admin.statistics-board._charts_insights')

        {{-- [5] Vận hành nội bộ --}}
        @if($canSeeConsulting || $canSeeTechnical)
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="text-muted fw-semibold small text-uppercase">Vận hành nội bộ</span>
            <hr class="flex-grow-1 m-0 opacity-25">
        </div>
        @endif

        @if($canSeeConsulting)
        <div class="role-dashboard-panel mb-4">
            <div class="role-dashboard-header role-consulting">
                <div>
                    <h6 class="role-dashboard-title">Bộ phận Tư vấn</h6>
                    <p class="role-dashboard-subtitle">Theo dõi hồ sơ theo nhóm dịch vụ và tiến độ xử lý</p>
                </div>
                <span class="role-dashboard-year">Năm {{ $year }}</span>
            </div>

            <div class="p-3 p-md-4">
                <div class="row g-3 mb-3">
                    <div class="col-xl-3 col-md-6">
                        <div class="role-kpi-card role-consulting-total h-100">
                            <div class="role-kpi-body">
                                <div class="role-kpi-icon"><i class="fa-solid fa-copy"></i></div>
                                <div>
                                    <div class="role-kpi-label">Tổng hồ sơ</div>
                                    <div class="role-kpi-value">{{ number_format($consultingSummary['total'] ?? 0) }}</div>
                                    <p class="role-kpi-note">Tổng hợp hồ sơ tư vấn trong kỳ</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="role-kpi-card role-consulting-completed h-100">
                            <div class="role-kpi-body">
                                <div class="role-kpi-icon"><i class="fa-solid fa-check-circle"></i></div>
                                <div>
                                    <div class="role-kpi-label">Đã hoàn thành</div>
                                    <div class="role-kpi-value">{{ number_format($consultingSummary['completed'] ?? 0) }}</div>
                                    <p class="role-kpi-note">Đã kết thúc quy trình công việc</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="role-kpi-card role-consulting-processing h-100">
                            <div class="role-kpi-body">
                                <div class="role-kpi-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                                <div>
                                    <div class="role-kpi-label">Đang xử lý</div>
                                    <div class="role-kpi-value">{{ number_format($consultingSummary['processing'] ?? 0) }}</div>
                                    <p class="role-kpi-note">Cần tiếp tục giám sát tiến độ</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="role-kpi-card role-consulting-rate h-100">
                            <div class="role-kpi-body">
                                <div class="role-kpi-icon"><i class="fa-solid fa-arrow-trend-up"></i></div>
                                <div class="w-100">
                                    <div class="role-kpi-label">Tỷ lệ hoàn thành</div>
                                    <div class="role-kpi-value">{{ $consultingRate }}%</div>
                                    @if($canSeeFinance)
                                        <p class="role-kpi-note">Giá trị hồ sơ: {{ number_format($consultingSummary['value'] ?? 0, 0, ',', '.') }} đ</p>
                                    @endif
                                    <div class="role-kpi-progress">
                                        <span style="width: {{ $consultingRate }}%"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="role-table-card">
                    <div class="card-header py-3 px-3 px-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h6 class="role-table-title">Chi tiết tiến độ hồ sơ tư vấn</h6>
                            <p class="role-table-subtitle">Thống kê theo từng loại hợp đồng</p>
                        </div>
                        <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">{{ number_format($consultingSummary['total'] ?? 0) }} hồ sơ</span>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0 role-data-table">
                            <thead>
                                <tr>
                                    <th>Loại HĐ</th>
                                    <th class="text-center">Số HĐ</th>
                                    <th class="text-center">Đang xử lý</th>
                                    <th class="text-center">Hoàn thành</th>
                                    <th class="text-center">Tiến độ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($consultingStats as $row)
                                <tr>
                                    <td class="fw-semibold">{{ $row['label'] }}</td>
                                    <td class="text-center fw-bold">{{ number_format($row['count']) }}</td>
                                    <td class="text-center text-warning-emphasis">{{ number_format($row['processing']) }}</td>
                                    <td class="text-center text-success fw-semibold">{{ number_format($row['completed']) }}</td>
                                    <td class="text-center">
                                        <div class="role-progress-inline mx-auto"><span style="width: {{ $row['count'] > 0 ? round(($row['completed'] / $row['count']) * 100) : 0 }}%"></span></div>
                                        <small class="text-muted fw-semibold">{{ $row['count'] > 0 ? round(($row['completed'] / $row['count']) * 100) : 0 }}%</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Chưa có dữ liệu trong kỳ lọc hiện tại</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($canSeeTechnical)
        <div class="role-dashboard-panel mb-4">
            <div class="role-dashboard-header role-technical">
                <div>
                    <h6 class="role-dashboard-title">Bộ phận Kỹ thuật</h6>
                    <p class="role-dashboard-subtitle">Tổng hợp khối lượng được giao và chất lượng hoàn tất</p>
                </div>
                <span class="role-dashboard-year">Năm {{ $year }}</span>
            </div>

            <div class="p-3 p-md-4">
                <div class="row g-3 mb-3">
                    <div class="col-xl-3 col-md-6">
                        <div class="role-kpi-card role-technical-total h-100">
                            <div class="role-kpi-body">
                                <div class="role-kpi-icon"><i class="fa-solid fa-book"></i></div>
                                <div>
                                    <div class="role-kpi-label">Hồ sơ được giao</div>
                                    <div class="role-kpi-value">{{ number_format($technicalSummary['total'] ?? 0) }}</div>
                                    <p class="role-kpi-note">Tổng số đầu việc kỹ thuật đã tiếp nhận</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="role-kpi-card role-technical-completed h-100">
                            <div class="role-kpi-body">
                                <div class="role-kpi-icon"><i class="fa-solid fa-certificate"></i></div>
                                <div>
                                    <div class="role-kpi-label">Đã hoàn thành</div>
                                    <div class="role-kpi-value">{{ number_format($technicalSummary['completed'] ?? 0) }}</div>
                                    <p class="role-kpi-note">Đã đạt trạng thái hoàn tất quy trình</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="role-kpi-card role-technical-processing h-100">
                            <div class="role-kpi-body">
                                <div class="role-kpi-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                                <div>
                                    <div class="role-kpi-label">Đang xử lý</div>
                                    <div class="role-kpi-value">{{ number_format($technicalSummary['processing'] ?? 0) }}</div>
                                    <p class="role-kpi-note">Đang theo dõi để đảm bảo tiến độ</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="role-kpi-card role-technical-rate h-100">
                            <div class="role-kpi-body">
                                <div class="role-kpi-icon"><i class="fa-solid fa-chart-column"></i></div>
                                <div class="w-100">
                                    <div class="role-kpi-label">Tỷ lệ hoàn thành</div>
                                    <div class="role-kpi-value">{{ $technicalRate }}%</div>
                                    @if($canSeeFinance)
                                        <p class="role-kpi-note">Giá trị hồ sơ: {{ number_format($technicalSummary['value'] ?? 0, 0, ',', '.') }} đ</p>
                                    @endif
                                    <div class="role-kpi-progress">
                                        <span style="width: {{ $technicalRate }}%"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="role-table-card">
                    <div class="card-header py-3 px-3 px-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h6 class="role-table-title">Chi tiết tiến độ hồ sơ kỹ thuật</h6>
                            <p class="role-table-subtitle">Theo nhóm công việc được phân công</p>
                        </div>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">{{ number_format($technicalSummary['total'] ?? 0) }} hồ sơ</span>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0 role-data-table">
                            <thead>
                                <tr>
                                    <th>Loại HĐ</th>
                                    <th class="text-center">Số HĐ</th>
                                    <th class="text-center">Đang xử lý</th>
                                    <th class="text-center">Hoàn thành</th>
                                    <th class="text-center">Tiến độ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($technicalStats as $row)
                                <tr>
                                    <td class="fw-semibold">{{ $row['label'] }}</td>
                                    <td class="text-center fw-bold">{{ number_format($row['count']) }}</td>
                                    <td class="text-center text-warning-emphasis">{{ number_format(max(0, $row['count'] - $row['completed'])) }}</td>
                                    <td class="text-center text-success fw-semibold">{{ number_format($row['completed']) }}</td>
                                    <td class="text-center">
                                        <div class="role-progress-inline mx-auto"><span style="width: {{ $row['count'] > 0 ? round(($row['completed'] / $row['count']) * 100) : 0 }}%"></span></div>
                                        <small class="text-muted fw-semibold">{{ $row['count'] > 0 ? round(($row['completed'] / $row['count']) * 100) : 0 }}%</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Chưa có dữ liệu trong kỳ lọc hiện tại</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif

    @push('scripts')
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}?v={{ config('app.version') }}"></script>
    <script>
    (function () {
        const palette = ['#4f7cff', '#2ec27e', '#f5a524', '#f05252', '#7c3aed', '#0ea5e9', '#06b6d4', '#f97316'];

        function compactCurrency(value) {
            if (value >= 1000000000) return (value / 1000000000).toFixed(1) + 'B';
            if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
            if (value >= 1000) return (value / 1000).toFixed(1) + 'K';
            return value;
        }

        function destroyChart(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (canvas && canvas._chartInstance) {
                canvas._chartInstance.destroy();
                canvas._chartInstance = null;
            }
        }

        function getThemeConfig() {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            return {
                isDark,
                textColor: isDark ? '#94a3b8' : '#475569',
                gridColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)',
                tooltipBg: isDark ? '#1e293b' : '#ffffff',
                tooltipBorder: isDark ? 'rgba(255, 255, 255, 0.1)' : '#e2e8f0',
                tooltipText: isDark ? '#f8fafc' : '#0f172a'
            };
        }

        function renderMonthlyOverviewChart() {
            const configEl = document.getElementById('monthlyOverviewConfig');
            const canvas = document.getElementById('monthlyOverviewChart');
            if (!configEl || !canvas) return;
            destroyChart('monthlyOverviewChart');

            const monthly = JSON.parse(configEl.dataset.monthly || '{}');
            const canSeeFinance = String(configEl.dataset.canSeeFinance) === '1';
            const labels = Object.keys(monthly).map(m => 'Tháng ' + m);
            const contractData = Object.values(monthly).map(item => Number(item.contracts || 0));
            const salesData = Object.values(monthly).map(item => Number(item.sales || 0));

            const cfg = getThemeConfig();

            const datasets = [{
                label: 'Hợp đồng ký mới', type: 'bar', data: contractData,
                backgroundColor: 'rgba(79,124,255,0.75)', borderRadius: 4, yAxisID: 'y'
            }];

            const scales = {
                y: {
                    title: { display: true, text: 'Số HĐ', color: cfg.textColor },
                    ticks: { stepSize: 1, color: cfg.textColor },
                    grid: { color: cfg.gridColor }
                },
                x: {
                    ticks: { color: cfg.textColor },
                    grid: { color: cfg.gridColor }
                }
            };

            if (canSeeFinance) {
                datasets.push({ label: 'Doanh số ghi nhận', type: 'bar', data: salesData, backgroundColor: 'rgba(240,82,82,0.7)', borderRadius: 4, yAxisID: 'y1' });
                scales.y1 = {
                    position: 'right',
                    title: { display: true, text: 'Giá trị (VND)', color: cfg.textColor },
                    grid: { drawOnChartArea: false },
                    ticks: { callback: v => compactCurrency(v), color: cfg.textColor }
                };
            }

            canvas._chartInstance = new Chart(canvas, {
                type: 'bar',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { color: cfg.textColor }
                        }
                    },
                    scales
                }
            });
        }

        function renderWorkloadChart() {
            const configEl = document.getElementById('workloadChartConfig');
            const canvas = document.getElementById('teamWorkloadChart');
            if (!configEl || !canvas) return;
            destroyChart('teamWorkloadChart');

            const byType = JSON.parse(configEl.dataset.byType || '{}');
            const entries = Object.entries(byType);
            if (!entries.length) return;

            const cfg = getThemeConfig();

            canvas._chartInstance = new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: entries.map(([label]) => label),
                    datasets: [{ data: entries.map(([, item]) => Number(item.count || 0)), backgroundColor: palette }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { color: cfg.textColor }
                        }
                    }
                }
            });
        }

        function renderSourceSalesChart() {
            const configEl = document.getElementById('sourceSalesConfig');
            const canvas = document.getElementById('sourceSalesChart');
            if (!configEl || !canvas) return;
            destroyChart('sourceSalesChart');

            const payload = JSON.parse(configEl.dataset.chart || '{}');
            const labels = payload.labels || [];
            const data = payload.datasets || [];

            const colorMap = {
                'SALE': '#007bff',
                'KHAI THÁC': '#c084fc',
                'TÁI KÝ': '#ef4444',
                'MARKETING': '#b91c1c',
                'CHUYỂN THÔNG TIN': '#15803d',
                'THÔNG TIN CHUYỂN': '#15803d',
                'CÔNG TY': '#f43f5e',
                'MỚI': '#0ea5e9'
            };
            const fallback = ['#007bff','#c084fc','#facc15','#b91c1c','#15803d','#f43f5e','#0ea5e9','#6366f1','#f97316','#10b981'];
            const colors = labels.map((l, i) => colorMap[l.toUpperCase()] || fallback[i % fallback.length]);

            const cfg = getThemeConfig();

            canvas._chartInstance = new Chart(canvas, {
                type: 'pie',
                data: { labels, datasets: [{ data, backgroundColor: colors }] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: cfg.textColor,
                                font: { weight: '600' }
                            }
                        },
                        tooltip: {
                            backgroundColor: cfg.tooltipBg,
                            titleColor: cfg.tooltipText,
                            bodyColor: cfg.tooltipText,
                            borderColor: cfg.tooltipBorder,
                            borderWidth: 1,
                            callbacks: { label: ctx => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                return ctx.label + ': ' + compactCurrency(ctx.raw) + ' (' + ((ctx.raw / total) * 100).toFixed(1) + '%)';
                            }}
                        }
                    }
                }
            });
        }

        function renderServiceInsightChart() {
            const configEl = document.getElementById('serviceInsightConfig');
            const canvas = document.getElementById('serviceInsightChart');
            if (!configEl || !canvas) return;
            destroyChart('serviceInsightChart');

            const payload = JSON.parse(configEl.dataset.chart || '{}');
            const labels = payload.labels || [];

            const cfg = getThemeConfig();

            canvas._chartInstance = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Đã báo giá', data: payload.quoted || [], backgroundColor: 'rgba(245,158,11,0.75)', borderRadius: 4 },
                        { label: 'Đã ký hợp đồng', data: payload.signed || [], backgroundColor: 'rgba(37,99,235,0.75)', borderRadius: 4 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { color: cfg.textColor }
                        }
                    },
                    scales: {
                        y: {
                            title: { display: true, text: 'Số lượng hồ sơ', color: cfg.textColor },
                            ticks: { stepSize: 1, color: cfg.textColor },
                            grid: { color: cfg.gridColor }
                        },
                        x: {
                            ticks: { color: cfg.textColor },
                            grid: { color: cfg.gridColor }
                        }
                    }
                }
            });
        }

        function renderRegionInsightChart() {
            const configEl = document.getElementById('regionInsightConfig');
            const canvas = document.getElementById('regionInsightChart');
            if (!configEl || !canvas) return;
            destroyChart('regionInsightChart');

            const payload = JSON.parse(configEl.dataset.chart || '{}');
            const labels = payload.labels || [];

            const cfg = getThemeConfig();

            canvas._chartInstance = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Báo giá', type: 'bar', data: payload.quoted || [], backgroundColor: 'rgba(245,158,11,0.75)', borderRadius: 4, yAxisID: 'y' },
                        { label: 'Ký hợp đồng', type: 'bar', data: payload.signed || [], backgroundColor: 'rgba(37,99,235,0.75)', borderRadius: 4, yAxisID: 'y' },
                        { label: 'Doanh số (HĐ)', type: 'line', data: payload.revenue || [], borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,0.12)', fill: false, tension: 0.4, pointRadius: 4, yAxisID: 'y1' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { color: cfg.textColor }
                        }
                    },
                    scales: {
                        y: {
                            title: { display: true, text: 'Số lượng hồ sơ', color: cfg.textColor },
                            ticks: { stepSize: 1, color: cfg.textColor },
                            grid: { color: cfg.gridColor }
                        },
                        y1: {
                            position: 'right',
                            title: { display: true, text: 'VND', color: cfg.textColor },
                            grid: { drawOnChartArea: false },
                            ticks: { callback: v => compactCurrency(v), color: cfg.textColor }
                        },
                        x: {
                            ticks: { color: cfg.textColor },
                            grid: { color: cfg.gridColor }
                        }
                    }
                }
            });
        }

        window.renderStatisticsBoardCharts = function() {
            renderMonthlyOverviewChart();
            renderWorkloadChart();
            renderSourceSalesChart();
            renderServiceInsightChart();
            renderRegionInsightChart();
        };

        // Re-render when theme changes in real-time
        document.querySelectorAll("[data-bs-theme-value]").forEach((toggle) => {
            toggle.addEventListener("click", () => {
                setTimeout(function() { window.renderStatisticsBoardCharts(); }, 150);
            });
        });

        document.addEventListener('livewire:update', function () {
            setTimeout(function() { window.renderStatisticsBoardCharts(); }, 100);
        });

        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function() { window.renderStatisticsBoardCharts(); }, 100);
        });
    })();
    </script>
    @endpush
</div>
