<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Bảng tổng kết Marketing</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Bảng tổng kết Marketing</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Tóm tắt --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border border-light-subtle shadow-sm rounded-3 bg-body">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-primary d-flex align-items-center justify-content-center icon-42" >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                    </div>
                    <div>
                        <div class=" text-muted">Tổng số báo giá</div>
                        <div class="fw-bold text-primary">{{ $totals['count'] }} báo giá</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border border-light-subtle shadow-sm rounded-3 bg-body">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-warning d-flex align-items-center justify-content-center icon-42" >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-warning" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <div>
                        <div class=" text-muted">Giá trị (chưa VAT)</div>
                        <div class="fw-bold text-warning">{{ number_format($totals['value'], 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border border-light-subtle shadow-sm rounded-3 bg-body">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-success d-flex align-items-center justify-content-center icon-42" >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline></svg>
                    </div>
                    <div>
                        <div class=" text-muted">Doanh số</div>
                        <div class="fw-bold text-success">{{ number_format($totals['sales'], 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="card border border-light-subtle shadow-sm mb-4 rounded-3 bg-body">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 ">Năm</label>
                    <select wire:model.live="year" class="form-select form-select-sm border-light-subtle">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 ">Tháng</label>
                    <select wire:model.live="filter_month" class="form-select form-select-sm border-light-subtle">
                        <option value="">Cả năm</option>
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}">Tháng {{ $m }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Bảng theo tháng --}}
        <div class="col-lg-7">
            <div class="card border border-light-subtle shadow-sm h-100 rounded-3 overflow-hidden bg-body">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Tổng kết theo tháng — Năm {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-body-tertiary border-bottom border-light-subtle">
                                <tr>
                                    <th>Tháng</th>
                                    <th class="text-center">Số BG</th>
                                    <th class="text-end">Giá trị</th>
                                    <th class="text-end">Doanh số</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthly as $m => $data)
                                <tr class="{{ $data['count'] == 0 ? 'text-muted' : '' }}">
                                    <td class="fw-semibold">Tháng {{ $m }}</td>
                                    <td class="text-center">{{ $data['count'] > 0 ? $data['count'] : '—' }}</td>
                                    <td class="text-end ">{{ $data['value'] > 0 ? number_format($data['value'], 0, ',', '.') : '—' }}</td>
                                    <td class="text-end fw-semibold text-primary">{{ $data['sales'] > 0 ? number_format($data['sales'], 0, ',', '.') . ' đ' : '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr>
                                    <td>Tổng</td>
                                    <td class="text-center">{{ $totals['count'] }}</td>
                                    <td class="text-end">{{ number_format($totals['value'], 0, ',', '.') }} đ</td>
                                    <td class="text-end text-primary">{{ number_format($totals['sales'], 0, ',', '.') }} đ</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bảng theo dịch vụ --}}
        <div class="col-lg-5">
            <div class="card border border-light-subtle shadow-sm h-100 rounded-3 overflow-hidden bg-body">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Theo loại dịch vụ</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-body-tertiary border-bottom border-light-subtle">
                                <tr>
                                    <th>Dịch vụ</th>
                                    <th class="text-center">Số BG</th>
                                    <th class="text-end">Doanh số</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($byService as $svc)
                                <tr>
                                    <td class=" text-muted mxw-160px" >{{ $svc->service ?: '—' }}</td>
                                    <td class="text-center">{{ $svc->count }}</td>
                                    <td class="text-end fw-semibold text-primary">{{ number_format($svc->total_sales, 0, ',', '.') }} đ</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
