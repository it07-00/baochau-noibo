@unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
<div class="d-flex align-items-center gap-2 mb-3">
    <span class="text-muted fw-semibold small text-uppercase">Xu hướng &amp; Phân tích</span>
    <hr class="flex-grow-1 m-0 opacity-25">
</div>
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border border-light-subtle shadow-sm h-100 bg-secondary bg-opacity-05">
            <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="min-w-0">
                    <h6 class="mb-0 fw-bold">Tổng quan vận hành theo tháng</h6>
                    <small class="text-muted">
                        {{ $canSeeFinance
                            ? 'Biến động hợp đồng, doanh số và thực thu trong năm ' . $year
                            : 'Biến động số lượng hợp đồng theo tháng trong năm ' . $year }}
                    </small>
                </div>
                <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle px-3 py-2 rounded-pill d-inline-flex align-items-center flex-shrink-0">Năm {{ $year }}</span>
            </div>
            <div class="card-body p-3" x-data="{ render() { if(window.renderStatisticsBoardCharts) window.renderStatisticsBoardCharts(); } }" x-init="setTimeout(() => render(), 100)" @chart-updated.window="render()">
                <div id="monthlyOverviewConfig" class="d-none" data-monthly='@json($monthly)' data-can-see-finance="{{ $canSeeFinance ? 1 : 0 }}"></div>
                <div style="position: relative; height: 220px; width: 100%;">
                    <canvas id="monthlyOverviewChart" wire:ignore></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border border-light-subtle shadow-sm h-100 bg-secondary bg-opacity-05">
            <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="min-w-0">
                    <h6 class="mb-0 fw-bold">Cơ cấu hợp đồng theo dịch vụ</h6>
                    <small class="text-muted">Phân bổ theo 6 nhóm hợp đồng của công ty</small>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 rounded-pill d-inline-flex align-items-center flex-shrink-0">Năm {{ $year }}</span>
            </div>
            <div class="card-body p-3" x-data="{ render() { if(window.renderStatisticsBoardCharts) window.renderStatisticsBoardCharts(); } }" x-init="setTimeout(() => render(), 100)" @chart-updated.window="render()">
                <div id="workloadChartConfig" class="d-none" data-by-type='@json($byType)'></div>
                <div style="position: relative; height: 220px; width: 100%;">
                    <canvas id="teamWorkloadChart" wire:ignore></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endunless

@if($canSeeFinance)
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border border-light-subtle shadow-sm h-100 bg-secondary bg-opacity-05">
            <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="min-w-0">
                    <h6 class="mb-0 fw-bold">TỈ LỆ DOANH SỐ THEO NGUỒN THÔNG TIN</h6>
                    <small class="text-muted">Dựa trên doanh số ghi nhận của tất cả loại hợp đồng</small>
                </div>
            </div>
            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center" x-data="{ render() { if(window.renderStatisticsBoardCharts) window.renderStatisticsBoardCharts(); } }" x-init="setTimeout(() => render(), 100)" @chart-updated.window="render()">
                <div id="sourceSalesConfig" class="d-none" data-chart='@json($sourceSalesChart)'></div>
                <div style="position: relative; height: 220px; width: 100%;">
                    <canvas id="sourceSalesChart" wire:ignore></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border border-light-subtle shadow-sm h-100 bg-secondary bg-opacity-05">
            <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="min-w-0">
                    <h6 class="mb-0 fw-bold">Dịch vụ: báo giá vs ký hợp đồng</h6>
                    <small class="text-muted">Tháng {{ $insightMonth }}/{{ $year }}</small>
                </div>
            </div>
            <div class="card-body p-3" x-data="{ render() { if(window.renderStatisticsBoardCharts) window.renderStatisticsBoardCharts(); } }" x-init="setTimeout(() => render(), 100)" @chart-updated.window="render()">
                <div id="serviceInsightConfig" class="d-none" data-chart='@json($serviceInsightChart)'></div>
                <div style="position: relative; height: 220px; width: 100%;">
                    <canvas id="serviceInsightChart" wire:ignore></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <div class="card border border-light-subtle shadow-sm h-100 bg-secondary bg-opacity-05">
            <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="min-w-0">
                    <h6 class="mb-0 fw-bold">Khu vực: báo giá, ký hợp đồng, doanh số</h6>
                    <small class="text-muted">Tháng {{ $insightMonth }}/{{ $year }}</small>
                </div>
            </div>
            <div class="card-body p-3" x-data="{ render() { if(window.renderStatisticsBoardCharts) window.renderStatisticsBoardCharts(); } }" x-init="setTimeout(() => render(), 100)" @chart-updated.window="render()">
                <div id="regionInsightConfig" class="d-none" data-chart='@json($regionInsightChart)'></div>
                <div style="position: relative; height: 220px; width: 100%;">
                    <canvas id="regionInsightChart" wire:ignore></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
