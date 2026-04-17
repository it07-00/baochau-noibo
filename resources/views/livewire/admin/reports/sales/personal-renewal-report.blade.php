<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Bảng theo dõi tái ký cá nhân</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Bảng theo dõi tái ký cá nhân</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 ">Năm</label>
                    <select wire:model.live="year" class="form-select form-select-sm">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                @can('roles.view')
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1 ">Nhân viên</label>
                    <select wire:model.live="filter_staff" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endcan
            </div>
        </div>
    </div>

    @if($isSingle && $userDetail)
    {{-- Breakdown theo tháng --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold">Doanh số tái ký của <span class="text-success">{{ $userDetail->name }}</span> — Năm {{ $year }}</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tháng</th>
                        <th class="text-center">Số hợp đồng</th>
                        <th class="text-end">Giá trị HĐ</th>
                        <th class="text-end">Doanh số tái ký</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $data)
                    <tr class="{{ $data['count'] == 0 ? 'text-muted' : '' }}">
                        <td class="fw-semibold">Tháng {{ $m }}</td>
                        <td class="text-center">{{ $data['count'] > 0 ? $data['count'] : '—' }}</td>
                        <td class="text-end">{{ $data['sales_value'] > 0 ? number_format($data['sales_value'], 0, ',', '.') : '—' }}</td>
                        <td class="text-end fw-semibold text-success">{{ $data['sales_amount'] > 0 ? number_format($data['sales_amount'], 0, ',', '.') . ' đ' : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <td>Tổng</td>
                        <td class="text-center">{{ $totals['count'] }}</td>
                        <td class="text-end">{{ number_format($totals['sales_value'], 0, ',', '.') }} đ</td>
                        <td class="text-end text-success">{{ number_format($totals['sales_amount'], 0, ',', '.') }} đ</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @else
    {{-- Tổng hợp tất cả người dùng --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold">Doanh số tái ký theo nhân viên — Năm {{ $year }}</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nhân viên</th>
                        <th class="text-center">Số hợp đồng tái ký</th>
                        <th class="text-end">Giá trị HĐ</th>
                        <th class="text-end">Doanh số</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allUsers as $i => $row)
                    <tr>
                        <td class="text-muted ">{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $row['name'] }}</td>
                        <td class="text-center">{{ $row['count'] }}</td>
                        <td class="text-end">{{ number_format($row['sales_value'], 0, ',', '.') }}</td>
                        <td class="text-end fw-bold text-success">{{ number_format($row['sales_amount'], 0, ',', '.') }} đ</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Không có dữ liệu tái ký</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
