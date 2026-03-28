<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Bảng xếp hạng</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Bảng xếp hạng</li>
                </ol>
            </nav>
        </div>
        <div>
            <select wire:model.live="year" class="form-select form-select-sm" style="width:auto">
                @foreach($years as $y)
                    <option value="{{ $y }}">Năm {{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Xếp hạng nhân viên kinh doanh --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Xếp hạng doanh số nhân viên — Năm {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:50px">Hạng</th>
                                <th>Nhân viên</th>
                                <th class="text-end">DS Báo giá</th>
                                <th class="text-end">DS Tái ký</th>
                                <th class="text-end">DS Tiến độ</th>
                                <th class="text-end fw-bold">Tổng DS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesRankings as $i => $row)
                            @php
                                $rank  = $i + 1;
                                $medal = match($rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => $rank };
                            @endphp
                            <tr class="{{ $row['total'] == 0 ? 'text-muted' : '' }}">
                                <td class="text-center fw-bold">{{ $medal }}</td>
                                <td class="fw-semibold">{{ $row['name'] }}</td>
                                <td class="text-end small text-primary">{{ $row['quotation'] > 0 ? number_format($row['quotation'], 0, ',', '.') : '—' }}</td>
                                <td class="text-end small text-success">{{ $row['renewal'] > 0 ? number_format($row['renewal'], 0, ',', '.') : '—' }}</td>
                                <td class="text-end small text-warning">{{ $row['progressive'] > 0 ? number_format($row['progressive'], 0, ',', '.') : '—' }}</td>
                                <td class="text-end fw-bold {{ $row['total'] > 0 ? 'text-dark' : '' }}">
                                    {{ $row['total'] > 0 ? number_format($row['total'], 0, ',', '.') . ' đ' : '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                            @endforelse
                        </tbody>
                        @if($salesRankings->isNotEmpty())
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="2">Tổng</td>
                                <td class="text-end text-primary">{{ number_format($salesRankings->sum('quotation'), 0, ',', '.') }} đ</td>
                                <td class="text-end text-success">{{ number_format($salesRankings->sum('renewal'), 0, ',', '.') }} đ</td>
                                <td class="text-end text-warning">{{ number_format($salesRankings->sum('progressive'), 0, ',', '.') }} đ</td>
                                <td class="text-end">{{ number_format($salesRankings->sum('total'), 0, ',', '.') }} đ</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Top tỉnh/TP --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Top tỉnh/TP theo doanh số — {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:40px">#</th>
                                <th>Tỉnh / TP</th>
                                <th class="text-center">Số BG</th>
                                <th class="text-end">Doanh số</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProvinces as $i => $row)
                            <tr>
                                <td class="text-center text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $row->province }}</td>
                                <td class="text-center">{{ $row->cnt }}</td>
                                <td class="text-end fw-semibold text-primary">{{ number_format($row->total, 0, ',', '.') }} đ</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Không có dữ liệu</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Top khách hàng --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Top khách hàng theo giá trị HĐ — {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width:40px">#</th>
                                    <th>Khách hàng</th>
                                    <th class="text-center">HĐ CT</th>
                                    <th class="text-end">GT Chất thải</th>
                                    <th class="text-center">HĐ TV</th>
                                    <th class="text-end">GT Tư vấn</th>
                                    <th class="text-end fw-bold">Tổng GT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCustomers as $i => $row)
                                <tr>
                                    <td class="text-center text-muted small">{{ $i + 1 }}</td>
                                    <td class="fw-semibold">{{ $row->name }}</td>
                                    <td class="text-center">{{ $row->waste_count > 0 ? $row->waste_count : '—' }}</td>
                                    <td class="text-end small">{{ $row->waste_value > 0 ? number_format($row->waste_value, 0, ',', '.') : '—' }}</td>
                                    <td class="text-center">{{ $row->consult_count > 0 ? $row->consult_count : '—' }}</td>
                                    <td class="text-end small">{{ $row->consult_value > 0 ? number_format($row->consult_value, 0, ',', '.') : '—' }}</td>
                                    <td class="text-end fw-bold text-dark">{{ number_format($row->waste_value + $row->consult_value, 0, ',', '.') }} đ</td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top dịch vụ --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Top dịch vụ báo giá — {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:40px">#</th>
                                <th>Dịch vụ</th>
                                <th class="text-center">Số BG</th>
                                <th class="text-end">Doanh số</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topServices as $i => $row)
                            <tr>
                                <td class="text-center text-muted">{{ $i + 1 }}</td>
                                <td class="small text-muted" style="max-width:180px;">{{ $row->service }}</td>
                                <td class="text-center">{{ $row->cnt }}</td>
                                <td class="text-end fw-semibold text-success">{{ number_format($row->total, 0, ',', '.') }} đ</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Không có dữ liệu</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
