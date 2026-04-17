<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Doanh số thực thu</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Doanh số thực thu</li>
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
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 ">Nhân viên</label>
                    <select wire:model.live="filter_staff" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 ">Loại HĐ (6 nhóm)</label>
                    <select wire:model.live="filter_contract_type" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        @foreach($contractTypeOptions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 ">Mới / Tái ký</label>
                    <select wire:model.live="filter_renewal" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        <option value="0">Hợp đồng mới</option>
                        <option value="1">Tái ký</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 bg-soft-primary text-primary h-100">
                <div class="card-body">
                    <div class=" fw-semibold mb-1">DS Hợp đồng mới</div>
                    <div class="fw-bold fs-5">{{ number_format($totals['new'], 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-soft-success text-success h-100">
                <div class="card-body">
                    <div class=" fw-semibold mb-1">DS Tái ký</div>
                    <div class="fw-bold fs-5">{{ number_format($totals['renewal'], 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-soft-warning text-dark h-100">
                <div class="card-body">
                    <div class=" fw-semibold mb-1">Tổng thực thu {{ $year }}</div>
                    <div class="fw-bold fs-5">{{ number_format($totals['total'], 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng tổng kết theo tháng --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold">Doanh số thực thu theo tháng – Năm {{ $year }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tháng</th>
                            <th class="text-end">HĐ mới</th>
                            <th class="text-end">Tái ký</th>
                            <th class="text-end fw-bold">Tổng tháng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($months as $m => $data)
                            <tr class="{{ $data['total'] > 0 ? '' : 'text-muted' }}">
                                <td class="fw-semibold">Tháng {{ $m }}</td>
                                <td class="text-end">
                                    @if($data['new'] > 0)
                                        <span class="text-primary">{{ number_format($data['new'], 0, ',', '.') }}</span>
                                    @else — @endif
                                </td>
                                <td class="text-end">
                                    @if($data['renewal'] > 0)
                                        <span class="text-success">{{ number_format($data['renewal'], 0, ',', '.') }}</span>
                                    @else — @endif
                                </td>
                                <td class="text-end fw-bold">
                                    {{ $data['total'] > 0 ? number_format($data['total'], 0, ',', '.') . ' đ' : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td>Tổng năm {{ $year }}</td>
                            <td class="text-end text-primary">{{ number_format($totals['new'], 0, ',', '.') }} đ</td>
                            <td class="text-end text-success">{{ number_format($totals['renewal'], 0, ',', '.') }} đ</td>
                            <td class="text-end fs-6">{{ number_format($totals['total'], 0, ',', '.') }} đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Bảng chi tiết theo loại HĐ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold">Chi tiết theo 6 loại hợp đồng – Năm {{ $year }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Loại HĐ</th>
                            <th class="text-end">HĐ mới</th>
                            <th class="text-end">Tái ký</th>
                            <th class="text-end fw-bold">Tổng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($totals['by_type'] as $label => $vals)
                            <tr class="{{ $vals['total'] > 0 ? '' : 'text-muted' }}">
                                <td class="fw-semibold">{{ $label }}</td>
                                <td class="text-end">
                                    @if($vals['new'] > 0)
                                        <span class="text-primary">{{ number_format($vals['new'], 0, ',', '.') }}</span>
                                    @else — @endif
                                </td>
                                <td class="text-end">
                                    @if($vals['renewal'] > 0)
                                        <span class="text-success">{{ number_format($vals['renewal'], 0, ',', '.') }}</span>
                                    @else — @endif
                                </td>
                                <td class="text-end fw-bold">
                                    {{ $vals['total'] > 0 ? number_format($vals['total'], 0, ',', '.') . ' đ' : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td>Tổng cộng</td>
                            <td class="text-end text-primary">{{ number_format($totals['new'], 0, ',', '.') }} đ</td>
                            <td class="text-end text-success">{{ number_format($totals['renewal'], 0, ',', '.') }} đ</td>
                            <td class="text-end fs-6">{{ number_format($totals['total'], 0, ',', '.') }} đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Xếp hạng nhân viên theo thực thu --}}
    @if($staffRevenue->isNotEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold">Xếp hạng nhân viên theo doanh số thực thu – Năm {{ $year }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Nhân viên</th>
                            <th class="text-end">Thực thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staffRevenue as $sr)
                            <tr>
                                <td class="fw-semibold">{{ $loop->iteration }}</td>
                                <td>{{ $sr['name'] }}</td>
                                <td class="text-end fw-bold text-success">{{ number_format($sr['total'], 0, ',', '.') }} đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
