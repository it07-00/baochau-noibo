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

    <div class="row g-3 mt-1 px-2 px-md-0">
        {{-- Thông tin khách hàng --}}
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom p-3 p-md-4">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                        <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:48px;height:48px;">
                            <i class="bi bi-person-lines-fill fs-5 text-success"></i>
                        </div>
                        <div style="min-width:0;">
                            <div class="fw-bold fs-6">{{ $customer->name }}</div>
                            <div class="text-muted small d-flex flex-wrap gap-2">
                                @if($customer->tax_code)
                                    <span><i class="bi bi-hash me-1"></i>{{ $customer->tax_code }}</span>
                                @endif
                                @if($customer->province)
                                    <span class="d-none d-sm-inline"><i class="bi bi-geo-alt me-1"></i>{{ $customer->province }}</span>
                                @endif
                                @if($customer->representative)
                                    <span class="d-none d-sm-inline"><i class="bi bi-person me-1"></i>{{ $customer->representative }}</span>
                                @endif
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
                        <a href="{{ route('app.customers.index') }}" class="btn btn-light btn-sm">
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
                                <option value="shd_bc">Số HĐ BC</option>
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
                                                wire:click="sortBy('shd_bc')">
                                            Số HĐ BC
                                            @if($sortField === 'shd_bc')
                                                <i class="bi bi-arrow-{{ $sortDir === 'asc' ? 'up' : 'down' }}-short ms-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="d-none d-md-table-cell">Số HĐ NTP</th>
                                    <th class="d-none d-sm-table-cell">Nhà thầu phụ</th>
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
                                    <th style="width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contracts as $contract)
                                <tr style="cursor:pointer;" onclick="window.location='{{ route($contract->contract_route) }}'">
                                    <td>
                                        <span class="badge bg-label-info px-2">{{ $contract->type_label }}</span>
                                    </td>
                                    <td class="fw-semibold">
                                        {{ $contract->shd_bc ?: '—' }}
                                        {{-- Mobile extras --}}
                                        <div class="d-sm-none mt-1">
                                            @if($contract->handler)
                                                <div class="text-muted small text-wrap" style="max-width:140px;white-space:normal;">{{ $contract->handler }}</div>
                                            @endif
                                            @if($contract->status)
                                                @php
                                                    $s = $contract->status;
                                                    $sk = mb_strtolower(trim($s));
                                                    $statusColor = match(true) {
                                                        in_array($sk, ['hoàn thành', 'đã hoàn thành', 'đã hoàn thành kh ký trước']) => ['bg' => '#d1e7dd', 'text' => '#198754'],
                                                        in_array($sk, ['hợp đồng hủy', 'đã hủy', 'hủy bỏ']) => ['bg' => '#f8d7da', 'text' => '#dc3545'],
                                                        in_array($sk, ['đang thực hiện', 'pth đang kiểm tra']) => ['bg' => '#cfe2ff', 'text' => '#0d6efd'],
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
                                    <td class="d-none d-md-table-cell">{{ $contract->shd_cxl ?: '—' }}</td>
                                    <td class="text-wrap d-none d-sm-table-cell" style="max-width:180px;">{{ $contract->handler }}</td>
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
                                    <td onclick="event.stopPropagation()" class="text-center pe-3">
                                        <button class="btn btn-sm p-0 text-primary"
                                                wire:click.stop="viewDetail({{ $contract->contract_id }}, '{{ addslashes($contract->model_class) }}')"
                                                onclick="event.stopPropagation()"
                                                title="Xem chi tiết">
                                            <i class="bi bi-eye fs-5"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Không có hợp đồng nào.</td>
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

    {{-- Modal chi tiết hợp đồng --}}
    <div wire:ignore.self class="modal fade" id="contractDetailModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-dark py-3">
                    <h5 class="modal-title fw-bold text-white">
                        Thông tin Hợp Đồng
                        @if($selectedContractLabel)
                            <span class="badge bg-secondary ms-2 fw-normal fs-6">{{ $selectedContractLabel }}</span>
                        @endif
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if ($selectedContract)
                        {{-- Tabs Navigation --}}
                        <ul class="nav nav-tabs px-4 pt-3" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-semibold" data-bs-toggle="tab"
                                    data-bs-target="#tab-info-{{ $selectedContractType }}-{{ $selectedContract->id }}" type="button">
                                    <i class="bi bi-info-circle me-1"></i>Thông tin HĐ
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-semibold" data-bs-toggle="tab"
                                    data-bs-target="#tab-progress-{{ $selectedContractType }}-{{ $selectedContract->id }}" type="button">
                                    <i class="bi bi-diagram-3 me-1"></i>Tiến độ hoàn thành
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            {{-- Tab 1: Thông tin HĐ --}}
                            <div class="tab-pane fade show active"
                                id="tab-info-{{ $selectedContractType }}-{{ $selectedContract->id }}"
                                role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3" style="width: 25%;">Ghi chú</th>
                                                <td class="px-4 py-3">{{ $selectedContract->note }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Khách hàng</th>
                                                <td class="px-4 py-3">{{ $selectedContract->customer?->name }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Nhà thầu phụ</th>
                                                <td class="px-4 py-3">{{ $selectedContract->handler?->name }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Số hợp đồng BC</th>
                                                <td class="px-4 py-3">{{ $selectedContract->shd_bc }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Nội dung</th>
                                                <td class="px-4 py-3">{{ $selectedContract->content }}</td>
                                            </tr>
                                            @unless (auth()->user()->hasAnyRole([\App\Enums\Role::TU_VAN->value, \App\Enums\Role::KY_THUAT->value]))
                                                <tr>
                                                    <th class="bg-light fw-bold px-4 py-3">Giá trị hợp đồng</th>
                                                    <td class="px-4 py-3 fw-bold text-danger">
                                                        {{ number_format($selectedContract->value) }}đ</td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-light fw-bold px-4 py-3">Hoa hồng</th>
                                                    <td class="px-4 py-3 fw-bold text-danger">
                                                        {{ number_format($selectedContract->commission) }}đ</td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-light fw-bold px-4 py-3">Doanh số</th>
                                                    <td class="px-4 py-3 fw-bold text-danger">
                                                        {{ number_format($selectedContract->revenue) }}đ</td>
                                                </tr>
                                            @endunless
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Phương thức thanh toán</th>
                                                <td class="px-4 py-3">{{ $selectedContract->payment_method }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Nhân viên chăm sóc</th>
                                                <td class="px-4 py-3">{{ $selectedContract->staff?->name }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Phòng ban</th>
                                                <td class="px-4 py-3">{{ $selectedContract->department?->name }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Nguồn thông tin</th>
                                                <td class="px-4 py-3">{{ $selectedContract->source }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Ngày ký hợp đồng</th>
                                                <td class="px-4 py-3">
                                                    {{ $selectedContract->signed_at ? $selectedContract->signed_at->format('d/m/Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Ngày hiệu lực</th>
                                                <td class="px-4 py-3">
                                                    {{ $selectedContract->effective_at ? $selectedContract->effective_at->format('d/m/Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Ngày kết thúc</th>
                                                <td class="px-4 py-3">
                                                    {{ $selectedContract->end_at ? $selectedContract->end_at->format('d/m/Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Ngày xuất hóa đơn</th>
                                                <td class="px-4 py-3 text-danger">
                                                    {{ $selectedContract->submitted_at ? $selectedContract->submitted_at->format('d/m/Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Địa chỉ xuất hóa đơn</th>
                                                <td class="px-4 py-3">{{ $selectedContract->billing_address }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Địa chỉ thực hiện</th>
                                                <td class="px-4 py-3">{{ $selectedContract->execution_address }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Địa chỉ gửi thư</th>
                                                <td class="px-4 py-3">{{ $selectedContract->mailing_address }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Số hợp đồng CXL</th>
                                                <td class="px-4 py-3 fw-bold">{{ $selectedContract->shd_cxl }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Người được giao</th>
                                                <td class="px-4 py-3">
                                                    @if ($selectedContract->assignments && $selectedContract->assignments->count() > 0)
                                                        @foreach ($selectedContract->assignments as $assign)
                                                            <div class="mb-1">
                                                                <span class="badge bg-primary me-1">{{ $assign->user?->name }}</span>
                                                                <small class="text-muted">— giao bởi
                                                                    {{ $assign->assigner?->name }} lúc
                                                                    {{ $assign->created_at?->format('d/m/Y H:i') }}</small>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">Chưa giao việc</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3 align-middle" colspan="2">
                                                    <i class="bi bi-journal-text me-1"></i> Ghi chú tiến độ
                                                </th>
                                            </tr>
                                            @if ($selectedProgressNotes && count($selectedProgressNotes) > 0)
                                                @foreach ($selectedProgressNotes as $pNote)
                                                    <tr>
                                                        <td colspan="2" class="py-2 ps-4">
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-bold text-primary">{{ $pNote->user?->name }}
                                                                    <span class="text-muted fw-normal">—
                                                                        {{ $pNote->created_at?->format('d/m/Y H:i') }}</span>
                                                                </span>
                                                                <span class="mt-1">{{ $pNote->note }}</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="2" class="text-muted ps-4 py-2">Chưa có ghi chú tiến độ nào.</td>
                                                </tr>
                                            @endif
                                            @if (auth()->user()->hasAnyRole([\App\Enums\Role::TU_VAN->value, \App\Enums\Role::KY_THUAT->value]))
                                                <tr>
                                                    <td colspan="2" class="px-4 pb-3 pt-2">
                                                        <textarea class="form-control form-control-sm mb-2" rows="2"
                                                            wire:model="progressNote"
                                                            placeholder="Nhập ghi chú tiến độ..."></textarea>
                                                        @error('progressNote')
                                                            <div class="text-danger mb-1">{{ $message }}</div>
                                                        @enderror
                                                        <button class="btn btn-sm btn-primary"
                                                            wire:click="addProgressNote({{ $selectedContract->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="addProgressNote">
                                                            <span wire:loading wire:target="addProgressNote"
                                                                class="spinner-border spinner-border-sm me-1"></span>
                                                            <i class="bi bi-plus me-1"></i> Thêm ghi chú
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Tab 2: Tiến độ --}}
                            <div class="tab-pane fade"
                                id="tab-progress-{{ $selectedContractType }}-{{ $selectedContract->id }}"
                                role="tabpanel">
                                <livewire:admin.contracts.contract-workflow-progress
                                    :contractType="$selectedContractType"
                                    :contractId="$selectedContract->id"
                                    :key="'progress-' . $selectedContractType . '-' . $selectedContract->id" />
                            </div>

                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('open-contract-detail-modal', () => {
            const el = document.getElementById('contractDetailModal');
            if (el) bootstrap.Modal.getOrCreateInstance(el).show();
        });
    });
</script>
@endpush
