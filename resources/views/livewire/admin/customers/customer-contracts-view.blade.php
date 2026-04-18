<div>
    @section('title', 'Hợp đồng của ' . $customer->name)
    @section('page_title', 'Hợp đồng của ' . $customer->name)

    @php
        $breadcrumbs = [
            ['label' => 'Quản trị', 'url' => route('app.dashboard')],
            ['label' => 'Khách hàng', 'url' => route('app.customers.index')],
            ['label' => $customer->name],
        ];
    @endphp

    <div class="row g-3 mt-1">
        {{-- Thông tin khách hàng --}}
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom p-4">
                <div class="d-flex flex-wrap align-items-center gap-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                             style="width:52px;height:52px;">
                            <i class="bi bi-person-lines-fill fs-4 text-success"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-5">{{ $customer->name }}</div>
                            <div class="text-muted small d-flex flex-wrap gap-3">
                                @if($customer->tax_code)
                                    <span><i class="bi bi-hash me-1"></i>{{ $customer->tax_code }}</span>
                                @endif
                                @if($customer->phone)
                                    <span><i class="bi bi-telephone me-1"></i>{{ $customer->phone }}</span>
                                @endif
                                @if($customer->province)
                                    <span><i class="bi bi-geo-alt me-1"></i>{{ $customer->province }}</span>
                                @endif
                                @if($customer->representative)
                                    <span><i class="bi bi-person me-1"></i>{{ $customer->representative }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="ms-auto d-flex gap-3">
                        <div class="text-center">
                            <div class="fw-bold fs-5 text-primary">{{ $totalContracts }}</div>
                            <div class="text-muted small">Hợp đồng</div>
                        </div>
                        <div class="vr"></div>
                        <div class="text-center">
                            <div class="fw-bold fs-5 text-success">{{ number_format($totalValue, 0, ',', '.') }}</div>
                            <div class="text-muted small">Tổng giá trị (VNĐ)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bảng hợp đồng --}}
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom">
                <div class="pure-card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h3 class="pure-card-title m-0">Tất cả hợp đồng</h3>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="d-flex align-items-center gap-1">
                            <label class="form-label mb-0 small fw-semibold text-muted text-nowrap">Từ ngày</label>
                            <input type="date" class="form-control form-control-sm" wire:model.live="dateFrom" style="width:145px;">
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <label class="form-label mb-0 small fw-semibold text-muted text-nowrap">Đến ngày</label>
                            <input type="date" class="form-control form-control-sm" wire:model.live="dateTo" style="width:145px;">
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <label class="form-label mb-0 small fw-semibold text-muted text-nowrap">Sắp xếp</label>
                            <select class="form-select form-select-sm" wire:model.live="sortField" style="width:130px;">
                                <option value="signed_at">Ngày ký</option>
                                <option value="value">Giá trị</option>
                                <option value="shd_bc">Số HĐ BC</option>
                            </select>
                        </div>
                        <button class="btn btn-outline-secondary btn-sm px-2" wire:click="toggleDir" title="{{ $sortDir === 'desc' ? 'Mới nhất trước' : 'Cũ nhất trước' }}">
                            @if($sortDir === 'desc')
                                <i class="bi bi-sort-down me-1"></i><span class="small">Mới → Cũ</span>
                            @else
                                <i class="bi bi-sort-up me-1"></i><span class="small">Cũ → Mới</span>
                            @endif
                        </button>
                        @if($dateFrom || $dateTo)
                        <button class="btn btn-outline-secondary btn-sm" wire:click="resetFilter" title="Xóa bộ lọc">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        @endif
                        <a href="{{ route('app.customers.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại
                        </a>
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
                                                wire:click="sortBy('shd_bc')">
                                            Số HĐ BC
                                            @if($sortField === 'shd_bc')
                                                <i class="bi bi-arrow-{{ $sortDir === 'asc' ? 'up' : 'down' }}-short ms-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th>Số HĐ CXL</th>
                                    <th>Nhà thầu phụ</th>
                                    <th>
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
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contracts as $contract)
                                <tr>
                                    <td>
                                        <span class="badge bg-label-info px-2">{{ $contract->type_label }}</span>
                                    </td>
                                    <td class="fw-semibold">{{ $contract->shd_bc ?: '—' }}</td>
                                    <td>{{ $contract->shd_cxl ?: '—' }}</td>
                                    <td class="text-wrap" style="max-width:220px;">{{ $contract->handler }}</td>
                                    <td>{{ $contract->signed_at ? $contract->signed_at->format('d/m/Y') : '—' }}</td>
                                    <td class="text-end">{{ $contract->value ? number_format($contract->value, 0, ',', '.') : '—' }}</td>
                                    <td>
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
