@unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
<div class="d-flex align-items-center gap-2 mb-3 mt-1">
    <span class="text-muted fw-semibold small text-uppercase">Tổng quan kinh doanh</span>
    <hr class="flex-grow-1 m-0 opacity-25">
</div>
<div class="row g-3 mb-4 row-cols-2 row-cols-xl-4">
    <div class="col">
        <div class="card bg-body border shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="avatar bg-secondary text-white rounded-3 flex-shrink-0">
                    <i class="fi fi-rr-user"></i>
                </div>
                <div class="min-w-0"><h3 class="fw-bold text-body mb-1">{{ number_format($totalCustomers) }}</h3>
                <div class="mb-0 text-secondary fw-semibold small">
                    Tổng KH
                    @if($month !== '')
                        - T{{ $month }}/{{ $year }}
                    @else
                        - Năm {{ $year }}
                    @endif
                </div></div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-body border shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="avatar bg-info text-white rounded-3 flex-shrink-0">
                    <i class="fi fi-rr-file"></i>
                </div>
                <div class="min-w-0"><h3 class="fw-bold text-body mb-1">{{ number_format($totalContracts) }}</h3>
                <div class="mb-0 text-secondary fw-semibold small">
                    Hợp đồng
                    @if($month !== '')
                        - T{{ $month }}/{{ $year }}
                    @else
                        - Năm {{ $year }}
                    @endif
                </div></div>
            </div>
        </div>
    </div>
    @if($canSeeFinance)
    <div class="col">
        <div class="card bg-body border shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="avatar bg-success text-white rounded-3 flex-shrink-0">
                    <i class="fi fi-rr-usd-circle"></i>
                </div>
                <div class="min-w-0"><h3 class="fw-bold text-body mb-1 text-nowrap">{{ number_format($totalContractValue/1000000, 2) }} Tr</h3>
                <div class="mb-0 text-secondary fw-semibold small">
                    Giá trị HĐ
                    @if($month !== '')
                        - T{{ $month }}/{{ $year }}
                    @endif
                </div></div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-body border shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="avatar bg-warning text-dark rounded-3 flex-shrink-0">
                    <i class="fi fi-rr-chart-pie-alt"></i>
                </div>
                <div class="min-w-0"><h3 class="fw-bold text-body mb-1 text-nowrap">{{ number_format($totalSales/1000000, 2) }} Tr</h3>
                <div class="mb-0 text-secondary fw-semibold small">
                    Doanh số ghi nhận
                    @if($month !== '')
                        - T{{ $month }}/{{ $year }}
                    @endif
                </div></div>
            </div>
        </div>
    </div>
    @endif
</div>
@endunless
