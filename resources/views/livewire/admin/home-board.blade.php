<div>
    @if($showSales)
        @livewire('admin.reports.sales.sales-achievement-report')
    @endif

    @if($showConsulting)
        @livewire('admin.reports.consulting.consulting-achievement-report')
    @endif

    @if($showTechnical)
        @livewire('admin.reports.technical.technical-achievement-report')
    @endif

    @if(!$showSales && !$showConsulting && !$showTechnical)
        <div class="d-flex align-items-center justify-content-center" style="min-height: 60vh;">
            <div class="text-center text-muted">
                <i class="bi bi-bar-chart-line fs-1 d-block mb-3 opacity-50"></i>
                <p class="mb-0">Không có bảng đường đua nào được phân quyền.</p>
            </div>
        </div>
    @endif
</div>
