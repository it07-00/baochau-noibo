<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Bảng theo dõi doanh số</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Bảng theo dõi doanh số</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Tóm tắt --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-primary d-flex align-items-center justify-content-center" style="width:42px;height:42px;flex-shrink:0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <div>
                        <div class="small text-muted">DS Báo giá</div>
                        <div class="fw-bold text-primary">{{ number_format($qTotal, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-success d-flex align-items-center justify-content-center" style="width:42px;height:42px;flex-shrink:0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>
                    </div>
                    <div>
                        <div class="small text-muted">DS Tái ký</div>
                        <div class="fw-bold text-success">{{ number_format($rTotal, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-soft-warning d-flex align-items-center justify-content-center" style="width:42px;height:42px;flex-shrink:0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-warning" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                    </div>
                    <div>
                        <div class="small text-muted">DS Theo tiến độ</div>
                        <div class="fw-bold text-warning">{{ number_format($pTotal, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 small">Năm</label>
                    <select wire:model.live="year" class="form-select form-select-sm">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 small">Tháng</label>
                    <select wire:model.live="filter_month" class="form-select form-select-sm">
                        <option value="">Tất cả tháng</option>
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}">Tháng {{ $m }}</option>
                        @endfor
                    </select>
                </div>
                @can('roles.view')
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1 small">Nhân viên</label>
                    <select wire:model.live="filter_staff" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endcan
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 small">Trạng thái</label>
                    <select wire:model.live="filter_status" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        <option value="pending">Đang xử lý</option>
                        <option value="won">Thành công</option>
                        <option value="lost">Thất bại</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom pt-3 pb-0">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <button wire:click="$set('active_tab','quotation')"
                        class="nav-link {{ $active_tab === 'quotation' ? 'active fw-semibold' : '' }}">
                        Doanh số báo giá
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="$set('active_tab','renewal')"
                        class="nav-link {{ $active_tab === 'renewal' ? 'active fw-semibold' : '' }}">
                        Doanh số tái ký
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="$set('active_tab','progressive')"
                        class="nav-link {{ $active_tab === 'progressive' ? 'active fw-semibold' : '' }}">
                        Doanh số tiến độ
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                @if($active_tab === 'quotation')
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tháng</th>
                            <th>Mã số</th>
                            <th>Nhân viên</th>
                            <th>Công ty</th>
                            <th>Dịch vụ</th>
                            <th class="text-end">Giá trị</th>
                            <th class="text-end">Doanh số</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="text-muted small">{{ $item->sales_month->format('m/Y') }}</td>
                            <td class="small">{{ $item->quotation_number ?: '—' }}</td>
                            <td>{{ $item->staff?->name ?? '—' }}</td>
                            <td>{{ $item->company_name }}</td>
                            <td class="small text-muted">{{ $item->service ?: '—' }}</td>
                            <td class="text-end small">{{ number_format($item->value_ext_vat, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold text-primary">{{ number_format($item->sales_amount, 0, ',', '.') }} đ</td>
                            <td>
                                @if($item->status)
                                    <span class="badge bg-soft-secondary text-secondary small">{{ $item->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>

                @elseif($active_tab === 'renewal')
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tháng</th>
                            <th>Số hợp đồng</th>
                            <th>Nhân viên</th>
                            <th class="text-end">Giá trị HĐ</th>
                            <th class="text-end">% DS</th>
                            <th class="text-end">Doanh số</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="text-muted small">{{ $item->sales_month->format('m/Y') }}</td>
                            <td class="small">{{ $item->contract_number ?: '—' }}</td>
                            <td>{{ $item->creator?->name ?? '—' }}</td>
                            <td class="text-end small">{{ number_format($item->sales_value, 0, ',', '.') }}</td>
                            <td class="text-end small">{{ $item->sales_percentage }}%</td>
                            <td class="text-end fw-semibold text-success">{{ number_format($item->sales_amount, 0, ',', '.') }} đ</td>
                            <td>
                                @if($item->status)
                                    <span class="badge bg-soft-secondary text-secondary small">{{ $item->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>

                @elseif($active_tab === 'progressive')
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tháng</th>
                            <th>Số hợp đồng</th>
                            <th>Nhân viên</th>
                            <th>Mốc thanh toán</th>
                            <th class="text-end">%</th>
                            <th class="text-end">Doanh số</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="text-muted small">{{ $item->sales_month->format('m/Y') }}</td>
                            <td class="small">{{ $item->contract_number ?: '—' }}</td>
                            <td>{{ $item->creator?->name ?? '—' }}</td>
                            <td>{{ $item->milestone_name ?: '—' }}</td>
                            <td class="text-end small">{{ $item->percentage }}%</td>
                            <td class="text-end fw-semibold text-warning">{{ number_format($item->amount, 0, ',', '.') }} đ</td>
                            <td>
                                @if($item->status)
                                    <span class="badge bg-soft-secondary text-secondary small">{{ $item->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @endif
            </div>

            @if($items->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $items->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
