@unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
<div class="d-flex align-items-center gap-2 mb-3 mt-1">
    <span class="text-muted fw-semibold small text-uppercase">Tổng quan kinh doanh</span>
    <hr class="flex-grow-1 m-0 opacity-25">
</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card bg-secondary bg-opacity-05 shadow-none border-0 h-100">
            <div class="card-body p-3 p-md-4">
                <div class="avatar bg-secondary shadow-secondary rounded-circle text-white mb-3">
                    <i class="fi fi-rr-user"></i>
                </div>
                <h3 class="fw-bold mb-1">{{ number_format($totalCustomers) }}</h3>
                <h6 class="mb-0 text-muted fw-semibold fs-7">
                    Tổng KH
                    @if($month !== '')
                        - T{{ $month }}/{{ $year }}
                    @else
                        - Năm {{ $year }}
                    @endif
                </h6>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-info bg-opacity-05 shadow-none border-0 h-100">
            <div class="card-body p-3 p-md-4">
                <div class="avatar bg-info shadow-info rounded-circle text-white mb-3">
                    <i class="fi fi-rr-file"></i>
                </div>
                <h3 class="fw-bold mb-1">{{ number_format($totalContracts) }}</h3>
                <h6 class="mb-0 text-muted fw-semibold fs-7">
                    Hợp đồng
                    @if($month !== '')
                        - T{{ $month }}/{{ $year }}
                    @else
                        - Năm {{ $year }}
                    @endif
                </h6>
            </div>
        </div>
    </div>
    @if($canSeeFinance)
    <div class="col-6 col-md-3">
        <div class="card bg-success bg-opacity-05 shadow-none border-0 h-100">
            <div class="card-body p-3 p-md-4">
                <div class="avatar bg-success shadow-success rounded-circle text-white mb-3">
                    <i class="fi fi-rr-usd-circle"></i>
                </div>
                <h3 class="fw-bold mb-1">{{ number_format($totalContractValue/1000000, 2) }} Tr</h3>
                <h6 class="mb-0 text-muted fw-semibold fs-7">
                    Giá trị HĐ
                    @if($month !== '')
                        - T{{ $month }}/{{ $year }}
                    @endif
                </h6>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-warning bg-opacity-05 shadow-none border-0 h-100">
            <div class="card-body p-3 p-md-4">
                <div class="avatar bg-warning shadow-warning rounded-circle text-white mb-3">
                    <i class="fi fi-rr-chart-pie-alt"></i>
                </div>
                <h3 class="fw-bold mb-1">{{ number_format($totalSales/1000000, 2) }} Tr</h3>
                <h6 class="mb-0 text-muted fw-semibold fs-7">
                    Doanh số ghi nhận
                    @if($month !== '')
                        - T{{ $month }}/{{ $year }}
                    @endif
                </h6>
            </div>
        </div>
    </div>
    @endif
</div>
@endunless
