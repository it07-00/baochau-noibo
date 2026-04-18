<div>
    @section('title', 'Hợp đồng của ' . $handler->name)
    @section('page_title', 'Hợp đồng của ' . $handler->name)

    @php
        $breadcrumbs = [
            ['label' => 'Quản trị', 'url' => route('app.dashboard')],
            ['label' => 'Nhà thầu phụ', 'url' => route('app.handlers.index')],
            ['label' => $handler->name],
        ];
    @endphp

    <div class="row g-3 mt-1 px-2 px-md-0">
        {{-- Thông tin nhà thầu phụ --}}
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom p-3 p-md-4">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:48px;height:48px;">
                            <i class="bi bi-building fs-5 text-primary"></i>
                        </div>
                        <div style="min-width:0;">
                            <div class="fw-bold fs-6 fs-md-5">{{ $handler->name }}</div>
                            <div class="text-muted small">
                                @if($handler->phone) <i class="bi bi-telephone me-1"></i>{{ $handler->phone }} @endif
                                @if($handler->phone && $handler->address) &nbsp;·&nbsp; @endif
                                @if($handler->address) <i class="bi bi-geo-alt me-1"></i>{{ $handler->address }} @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-3 ms-sm-auto">
                        <div class="text-center">
                            <div class="fw-bold fs-5 text-primary">{{ $totalContracts }}</div>
                            <div class="text-muted small">Hợp đồng</div>
                        </div>
                        <div class="vr"></div>
                        <div class="text-center">
                            <div class="fw-bold fs-6 text-success">{{ number_format($totalValue, 0, ',', '.') }}</div>
                            <div class="text-muted small">Tổng giá trị (VNĐ)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bảng hợp đồng --}}
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom">
                <div class="pure-card-header d-flex flex-column gap-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="pure-card-title m-0">Tất cả hợp đồng</h3>
                        <a href="{{ route('app.handlers.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại
                        </a>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="d-flex align-items-center gap-1">
                            <label class="form-label mb-0 small fw-semibold text-muted text-nowrap">Từ ngày</label>
                            <input type="date" class="form-control form-control-sm" wire:model.live="dateFrom">
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <label class="form-label mb-0 small fw-semibold text-muted text-nowrap">Đến ngày</label>
                            <input type="date" class="form-control form-control-sm" wire:model.live="dateTo">
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <label class="form-label mb-0 small fw-semibold text-muted text-nowrap">Sắp xếp</label>
                            <select class="form-select form-select-sm" wire:model.live="sortField" style="min-width:110px;">
                                <option value="signed_at">Ngày ký</option>
                                <option value="value">Giá trị</option>
                                <option value="shd_cxl">Số HĐ NTP</option>
                            </select>
                        </div>
                        <button class="btn btn-outline-secondary btn-sm px-2" wire:click="toggleDir">
                            @if($sortDir === 'desc')
                                <i class="bi bi-sort-down me-1"></i><span class="small">Mới → Cũ</span>
                            @else
                                <i class="bi bi-sort-up me-1"></i><span class="small">Cũ → Mới</span>
                            @endif
                        </button>
                        @if($dateFrom || $dateTo)
                        <button class="btn btn-outline-secondary btn-sm" wire:click="resetFilter">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        @endif
                    </div>
                </div>

                <div class="pure-card-body pb-3">
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="110">Loại HĐ</th>
                                    <th>
                                        <button class="btn btn-link btn-sm p-0 text-dark fw-bold text-decoration-none"
                                                wire:click="sortBy('shd_cxl')">
                                            Số HĐ NTP
                                            @if($sortField === 'shd_cxl')
                                                <i class="bi bi-arrow-{{ $sortDir === 'asc' ? 'up' : 'down' }}-short ms-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="d-none d-md-table-cell">Số HĐ BC</th>
                                    <th class="d-none d-sm-table-cell">Khách hàng</th>
                                    <th class="d-none d-md-table-cell">
                                        <button class="btn btn-link btn-sm p-0 text-dark fw-bold text-decoration-none"
                                                wire:click="sortBy('signed_at')">
                                            Ngày ký
                                            @if($sortField === 'signed_at')
                                                <i class="bi bi-arrow-{{ $sortDir === 'asc' ? 'up' : 'down' }}-short ms-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="text-end">
                                        <button class="btn btn-link btn-sm p-0 text-dark fw-bold text-decoration-none"
                                                wire:click="sortBy('value')">
                                            Giá trị (VNĐ)
                                            @if($sortField === 'value')
                                                <i class="bi bi-arrow-{{ $sortDir === 'asc' ? 'up' : 'down' }}-short ms-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="d-none d-sm-table-cell">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contracts as $contract)
                                <tr>
                                    <td>
                                        <span class="badge bg-label-info px-2">{{ $contract->type_label }}</span>
                                    </td>
                                    <td class="fw-semibold">
                                        {{ $contract->shd_cxl ?: '—' }}
                                        {{-- Mobile extras --}}
                                        <div class="d-sm-none mt-1">
                                            @if($contract->customer)
                                                <div class="text-muted small text-wrap" style="max-width:150px;white-space:normal;">{{ $contract->customer }}</div>
                                            @endif
                                            @if($contract->status)
                                                @php
                                                    $s = $contract->status;
                                                    $sk = mb_strtolower(trim($s));
                                                    $statusColor = match(true) {
                                                        in_array($s,  ['HOÀN THÀNH', 'Đã hoàn thành', 'Đã hoàn thành KH ký trước']) ||
                                                        in_array($sk, ['hoàn thành', 'đã hoàn thành', 'đã hoàn thành kh ký trước'])
                                                            => ['bg' => '#d1e7dd', 'text' => '#198754'],
                                                        in_array($s,  ['Hợp đồng hủy', 'ĐÃ HỦY', 'Đã hủy', 'Hủy bỏ']) ||
                                                        in_array($sk, ['hợp đồng hủy', 'đã hủy', 'hủy bỏ'])
                                                            => ['bg' => '#f8d7da', 'text' => '#dc3545'],
                                                        in_array($s,  ['PTH đang kiểm tra', 'ĐANG THỰC HIỆN', 'ĐANG THỰC HIÊN']) ||
                                                        in_array($sk, ['đang thực hiện', 'pth đang kiểm tra', ''])
                                                            => ['bg' => '#cfe2ff', 'text' => '#0d6efd'],
                                                        default => ['bg' => '#e9ecef', 'text' => '#495057'],
                                                    };
                                                @endphp
                                                <span class="badge px-2 py-1 fw-semibold mt-1"
                                                      style="font-size:0.65rem;background:{{ $statusColor['bg'] }};color:{{ $statusColor['text'] }};white-space:normal;">
                                                    {{ $s }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $contract->shd_bc ?: '—' }}</td>
                                    <td class="text-wrap d-none d-sm-table-cell" style="max-width:200px;">{{ $contract->customer }}</td>
                                    <td class="d-none d-md-table-cell">{{ $contract->signed_at ? $contract->signed_at->format('d/m/Y') : '—' }}</td>
                                    <td class="text-end">{{ $contract->value ? number_format($contract->value, 0, ',', '.') : '—' }}</td>
                                    <td class="d-none d-sm-table-cell">
                                        @if($contract->status)
                                            @php
                                                $s = $contract->status;
                                                $sk = mb_strtolower(trim($s));
                                                $statusColor = match(true) {
                                                    in_array($s,  ['HOÀN THÀNH', 'Đã hoàn thành', 'Đã hoàn thành KH ký trước']) ||
                                                    in_array($sk, ['hoàn thành', 'đã hoàn thành', 'đã hoàn thành kh ký trước'])
                                                        => ['bg' => '#d1e7dd', 'text' => '#198754'],
                                                    in_array($s,  ['Hợp đồng hủy', 'ĐÃ HỦY', 'Đã hủy', 'Hủy bỏ']) ||
                                                    in_array($sk, ['hợp đồng hủy', 'đã hủy', 'hủy bỏ'])
                                                        => ['bg' => '#f8d7da', 'text' => '#dc3545'],
                                                    in_array($s,  ['PTH đang kiểm tra', 'ĐANG THỰC HIỆN', 'ĐANG THỰC HIÊN']) ||
                                                    in_array($sk, ['đang thực hiện', 'pth đang kiểm tra', ''])
                                                        => ['bg' => '#cfe2ff', 'text' => '#0d6efd'],
                                                    in_array($s,  ['Đang trình BGĐ ký']) ||
                                                    in_array($sk, ['đã trình ký nhà thầu phụ', 'đang trình bgđ ký'])
                                                        => ['bg' => '#fff3cd', 'text' => '#b45309'],
                                                    in_array($sk, ['nhà thầu phụ đã gửi về'])
                                                        => ['bg' => '#d1ecf1', 'text' => '#0c5460'],
                                                    in_array($s,  ['Đã gửi khách hàng']) ||
                                                    in_array($sk, ['đã gửi khách hàng'])
                                                        => ['bg' => '#e2d9f3', 'text' => '#6f42c1'],
                                                    in_array($sk, ['tạm dừng'])
                                                        => ['bg' => '#fff8e1', 'text' => '#e65100'],
                                                    default => ['bg' => '#e9ecef', 'text' => '#495057'],
                                                };
                                            @endphp
                                            <span class="badge px-2 py-1 fw-semibold"
                                                  style="font-size:0.75rem; background:{{ $statusColor['bg'] }}; color:{{ $statusColor['text'] }};">
                                                {{ $s }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Không có hợp đồng nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($contracts->hasPages())
                <div class="pure-card-footer border-top px-4 py-3">
                    {{ $contracts->links('livewire.admin.users.pagination') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
