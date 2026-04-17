@extends('admin.layouts.app')

@section('title', 'Bảng điều khiển')
@section('page_title', 'Bảng điều khiển')

@php
    $breadcrumbs = [
        ['label' => 'Quản trị', 'url' => route('app.dashboard')],
        ['label' => 'Bảng điều khiển'],
    ];
    $monthNames = ['','Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6',
                   'Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
@endphp

@section('content')
    {{-- KPI Cards --}}
    <div class="row g-3 mt-1">
        <div class="col-xxl-3 col-md-6">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body p-0 position-relative z-1">
                    <div class="d-flex align-items-start gap-3 p-6">
                        <div class="btn-icon bg-glow-primary rounded-pill flex-shrink-0"></div>
                        <div class="flex-fill">
                            <span class="fz-13px fw-medium d-block mb-1 text-muted">Tổng khách hàng</span>
                            <h3 class="fs-5 mb-0 fw-bold">{{ number_format($totalCustomers) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body p-0 position-relative z-1">
                    <div class="d-flex align-items-start gap-3 p-6">
                        <div class="btn-icon bg-glow-orange rounded-pill btn-lg flex-shrink-0"></div>
                        <div class="flex-fill">
                            <span class="fz-13px fw-medium d-block mb-1 text-muted">Doanh số tháng {{ $month }}/{{ $year }}</span>
                            <h3 class="fs-5 mb-0 fw-bold">{{ $salesThisMonth > 0 ? number_format($salesThisMonth, 0, ',', '.') . ' đ' : '—' }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body p-0 position-relative z-1">
                    <div class="d-flex align-items-start gap-3 p-6">
                        <div class="btn-icon bg-glow-success rounded-pill btn-lg flex-shrink-0"></div>
                        <div class="flex-fill">
                            <span class="fz-13px fw-medium d-block mb-1 text-muted">HĐ ký năm {{ $year }}</span>
                            <h3 class="fs-5 mb-0 fw-bold">{{ number_format($contractsThisYear) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body p-0 position-relative z-1">
                    <div class="d-flex align-items-start gap-3 p-6">
                        <div class="btn-icon bg-glow-info rounded-pill btn-lg flex-shrink-0"></div>
                        <div class="flex-fill">
                            <span class="fz-13px fw-medium d-block mb-1 text-muted">Doanh số năm {{ $year }}</span>
                            <h3 class="fs-5 mb-0 fw-bold">{{ $salesThisYear > 0 ? number_format($salesThisYear, 0, ',', '.') . ' đ' : '—' }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Doanh số theo tháng + Top nhân viên --}}
    <div class="row g-3 mt-1">
        <div class="col-xxl-7 col-xl-12">
            <div class="card shadow-custom rounded-custom h-100">
                <div class="card-body p-6">
                    <h3 class="h6 mb-4 fs-4 fw-semibold">Doanh số theo tháng — {{ $year }}</h3>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:100px">Tháng</th>
                                    <th>Biểu đồ</th>
                                    <th class="text-end">Doanh số</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthly as $m => $val)
                                <tr class="{{ $m == $month ? 'table-warning' : '' }}">
                                    <td class="fw-semibold ">
                                        {{ $monthNames[$m] }}
                                        @if($m == $month)<span class="badge bg-warning text-dark ms-1" style="font-size:10px">Hiện tại</span>@endif
                                    </td>
                                    <td>
                                        @if($val > 0)
                                        <div class="progress" style="height:8px;border-radius:4px;">
                                            <div class="progress-bar bg-primary" style="width:{{ round($val / $maxMonthly * 100) }}%"></div>
                                        </div>
                                        @else
                                        <span class="text-muted ">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end  fw-semibold {{ $val > 0 ? 'text-primary' : 'text-muted' }}">
                                        {{ $val > 0 ? number_format($val, 0, ',', '.') . ' đ' : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr>
                                    <td>Tổng</td>
                                    <td></td>
                                    <td class="text-end text-primary">{{ number_format($salesThisYear, 0, ',', '.') }} đ</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-5 col-xl-12">
            <div class="card shadow-custom rounded-custom h-100">
                <div class="card-body p-6">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h6 mb-0 fs-4 fw-semibold">Top nhân viên kinh doanh — {{ $year }}</h3>
                        <a href="{{ route('app.rankings') }}" class="btn btn-sm btn-outline-primary">Xem thêm</a>
                    </div>
                    @if($topStaff->isEmpty())
                        <p class="text-muted text-center py-4">Chưa có dữ liệu</p>
                    @else
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:40px">#</th>
                                <th>Nhân viên</th>
                                <th class="text-end">Doanh số</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topStaff as $i => $row)
                            @php
                                $medal = match($i) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => $i + 1 };
                            @endphp
                            <tr>
                                <td class="text-center fw-bold">{{ $medal }}</td>
                                <td class="fw-semibold">{{ $row['name'] }}</td>
                                <td class="text-end  fw-semibold text-primary">
                                    {{ $row['total'] > 0 ? number_format($row['total'], 0, ',', '.') . ' đ' : '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Hợp đồng ký gần đây --}}
    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom">
                <div class="pure-card-header d-flex flex-wrap align-items-center justify-content-between gap-7">
                    <h3 class="pure-card-title d-flex align-items-center gap-2 m-0">Hợp đồng ký gần đây</h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('app.contracts.waste.index') }}" class="btn btn-sm btn-outline-primary">HĐ Chất thải</a>
                        <a href="{{ route('app.contracts.consulting.index') }}" class="btn btn-sm btn-outline-success">HĐ Tư vấn</a>
                    </div>
                </div>
                <div class="pure-card-body pb-3">
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-medium">#</th>
                                    <th class="fw-medium">Loại</th>
                                    <th class="fw-medium">Số HĐ</th>
                                    <th class="fw-medium">Khách hàng</th>
                                    <th class="fw-medium">Nhân viên</th>
                                    <th class="fw-medium text-end">Giá trị</th>
                                    <th class="fw-medium text-center">Ngày ký</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentContracts as $i => $c)
                                <tr>
                                    <td class="text-muted ">{{ $i + 1 }}</td>
                                    <td><span class="badge fs-3 {{ $c['badge'] }}">{{ $c['type'] }}</span></td>
                                    <td class="fw-medium text-custom-body">{{ $c['contract_no'] }}</td>
                                    <td class="text-custom-body" style="max-width:220px;overflow:hidden;text-overflow:ellipsis;">{{ $c['customer'] }}</td>
                                    <td class="text-custom-body ">{{ $c['staff'] }}</td>
                                    <td class="text-end fw-semibold text-primary ">
                                        {{ $c['value'] > 0 ? number_format($c['value'], 0, ',', '.') . ' đ' : '—' }}
                                    </td>
                                    <td class="text-center  text-muted">
                                        {{ $c['signed_at'] ? \Carbon\Carbon::parse($c['signed_at'])->format('d/m/Y') : '—' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Chưa có hợp đồng nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
