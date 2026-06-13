@unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
<div class="d-flex align-items-center gap-2 mb-3 mt-1">
    <span class="text-muted fw-semibold small text-uppercase">Tổng quan kinh doanh</span>
    <hr class="flex-grow-1 m-0 opacity-25">
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card kpi-modern-card h-100">
            <div class="card-body p-3 p-md-4 d-flex flex-column gap-2">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div class="kpi-modern-title">
                        Tổng KH
                        @if($month !== '')
                            - Tháng {{ $month }}/{{ $year }}
                        @else
                            - Năm {{ $year }}
                        @endif
                    </div>
                    <div class="kpi-modern-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    </div>
                </div>
                <div class="kpi-modern-value">{{ number_format($totalCustomers) }}</div>
                <div class="kpi-modern-sparkline" aria-hidden="true">
                    <svg viewBox="0 0 180 36" preserveAspectRatio="none">
                        <path d="M2 28 C10 30, 14 14, 24 16 C32 17, 36 28, 46 26 C57 24, 62 10, 72 12 C83 14, 89 30, 98 28 C108 26, 112 16, 122 17 C132 18, 137 31, 147 26 C156 22, 162 14, 178 18"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card kpi-modern-card kpi-contracts h-100">
            <div class="card-body p-3 p-md-4 d-flex flex-column gap-2">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div class="kpi-modern-title">
                        Hợp đồng
                        @if($month !== '')
                            - Tháng {{ $month }}/{{ $year }}
                        @else
                            - Năm {{ $year }}
                        @endif
                    </div>
                    <div class="kpi-modern-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                    </div>
                </div>
                <div class="kpi-modern-value">{{ number_format($totalContracts) }}</div>
                <div class="kpi-modern-sparkline" aria-hidden="true">
                    <svg viewBox="0 0 180 36" preserveAspectRatio="none">
                        <path d="M2 27 C11 24, 16 30, 26 27 C35 24, 40 14, 50 16 C60 18, 64 31, 74 29 C84 27, 89 19, 98 21 C108 23, 114 31, 124 27 C134 23, 138 12, 148 14 C158 17, 164 27, 178 23"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    @if($canSeeFinance)
    <div class="col-md-3 col-6">
        <div class="card kpi-modern-card kpi-value h-100">
            <div class="card-body p-3 p-md-4 d-flex flex-column gap-2">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div class="kpi-modern-title">
                        Giá trị HĐ (Triệu)
                        @if($month !== '')
                            - Tháng {{ $month }}/{{ $year }}
                        @endif
                    </div>
                    <div class="kpi-modern-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                </div>
                <div class="kpi-modern-value">{{ number_format($totalContractValue/1000000, 2) }} Tr</div>
                <div class="kpi-modern-sparkline" aria-hidden="true">
                    <svg viewBox="0 0 180 36" preserveAspectRatio="none">
                        <path d="M2 29 C12 27, 18 22, 28 24 C38 27, 42 33, 52 30 C62 26, 67 17, 78 18 C88 19, 92 28, 102 27 C112 26, 117 14, 128 15 C139 16, 144 25, 154 24 C163 23, 168 18, 178 20"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card kpi-modern-card kpi-sales h-100">
            <div class="card-body p-3 p-md-4 d-flex flex-column gap-2">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div class="kpi-modern-title">
                        Doanh số ghi nhận (Triệu)
                        @if($month !== '')
                            - Tháng {{ $month }}/{{ $year }}
                        @endif
                    </div>
                    <div class="kpi-modern-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline></svg>
                    </div>
                </div>
                <div class="kpi-modern-value">{{ number_format($totalSales/1000000, 2) }} Tr</div>
                <div class="kpi-modern-sparkline" aria-hidden="true">
                    <svg viewBox="0 0 180 36" preserveAspectRatio="none">
                        <path d="M2 30 C11 33, 15 19, 25 20 C34 20, 39 27, 49 26 C59 25, 64 13, 74 14 C84 14, 89 22, 99 24 C109 26, 113 15, 123 16 C133 17, 138 27, 148 27 C158 27, 163 15, 178 12"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endunless
