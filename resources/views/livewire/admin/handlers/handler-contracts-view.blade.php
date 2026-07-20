<div>
    @section('title', 'Hợp đồng của ' . $handler->name)
    @section('page_title', 'Hợp đồng của ' . $handler->name)

    <div class="row g-4 mt-2 px-2 px-md-0">
        {{-- Thông tin nhà thầu phụ --}}
        <div class="col-12">
            <div class="card border border-light-subtle shadow-sm rounded-3 bg-body">
                <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center gap-4 p-3 p-md-4">
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                        <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 icon-48">
                            <i class="fa-solid fa-building-shield fs-5 text-primary"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="small fw-semibold text-primary mb-1">NHÀ THẦU PHỤ</div>
                            <h2 class="h5 fw-bold text-body mb-1">{{ $handler->name }}</h2>
                            @if($handler->phone)
                                <div class="text-muted small mb-1"><i class="fa-solid fa-phone me-2"></i>{{ $handler->phone }}</div>
                            @endif
                            @if($handler->address)
                                <div class="text-muted small"><i class="fa-solid fa-location-dot me-2"></i>{{ $handler->address }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2 ms-lg-auto">
                        <div class="d-flex align-items-center gap-3 border border-secondary-subtle bg-body-tertiary rounded-3 px-3 py-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                                <i class="fa-solid fa-file-signature"></i>
                            </span>
                            <div>
                                <div class="h5 fw-bold text-body mb-0">{{ number_format($totalContracts) }}</div>
                                <div class="text-muted small text-nowrap">Hợp đồng</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 border border-danger-subtle bg-danger bg-opacity-10 rounded-3 px-3 py-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-danger bg-opacity-10 text-danger p-2">
                                <i class="fa-solid fa-wallet"></i>
                            </span>
                            <div>
                                <div class="h5 fw-bold text-danger mb-0">{{ number_format($totalCommission, 0, ',', '.') }}đ</div>
                                <div class="text-muted small text-nowrap">Tổng chi nhà thầu phụ</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bảng hợp đồng --}}
        <div class="col-12">
            <div class="card border border-light-subtle shadow-sm overflow-hidden rounded-3 bg-body">
                <div class="card-header bg-body-tertiary border-bottom border-light-subtle p-3 p-md-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="h5 fw-bold text-body mb-1">Chi phí theo hợp đồng</h3>
                            <p class="small text-muted mb-0">Theo dõi khoản hoa hồng trả cho nhà thầu phụ trên từng hợp đồng.</p>
                        </div>
                        <a href="{{ route('app.handlers.index') }}" class="btn btn-outline-secondary btn-sm text-nowrap">
                            <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
                        </a>
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-6 col-md-3 col-xl-2">
                            <label class="form-label small fw-semibold text-body mb-1">Từ ngày</label>
                            <input type="date" class="form-control form-control-sm" wire:model.live="dateFrom">
                        </div>
                        <div class="col-6 col-md-3 col-xl-2">
                            <label class="form-label small fw-semibold text-body mb-1">Đến ngày</label>
                            <input type="date" class="form-control form-control-sm" wire:model.live="dateTo">
                        </div>
                        <div class="col-7 col-md-3 col-xl-2">
                            <label class="form-label small fw-semibold text-body mb-1">Sắp xếp theo</label>
                            <select class="form-select form-select-sm" wire:model.live="sortField">
                                <option value="signed_at">Ngày ký</option>
                                <option value="commission">Chi nhà thầu phụ</option>
                                <option value="shd_cxl">Số HĐ NTP</option>
                            </select>
                        </div>
                        <div class="col-5 col-md-3 col-xl-auto d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm flex-fill text-nowrap" wire:click="toggleDir">
                                @if($sortDir === 'desc')
                                    <i class="fa-solid fa-arrow-down-wide-short me-1"></i>Mới → Cũ
                                @else
                                    <i class="fa-solid fa-arrow-up-wide-short me-1"></i>Cũ → Mới
                                @endif
                            </button>
                            @if($dateFrom || $dateTo)
                            <button class="btn btn-outline-secondary btn-sm" wire:click="resetFilter" title="Xóa bộ lọc">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle table-hover mb-0">
                            <thead class="bg-body-tertiary text-uppercase text-secondary" style="font-size: 0.75rem;">
                                <tr>
                                    <th width="110">Loại HĐ</th>
                                    <th>
                                        <button class="btn btn-link btn-sm p-0 text-body fw-bold text-decoration-none"
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
                                        <button class="btn btn-link btn-sm p-0 text-body fw-bold text-decoration-none"
                                                wire:click="sortBy('signed_at')">
                                            Ngày ký
                                            @if($sortField === 'signed_at')
                                                <i class="bi bi-arrow-{{ $sortDir === 'asc' ? 'up' : 'down' }}-short ms-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="text-end">
                                        <button class="btn btn-link btn-sm p-0 text-body fw-bold text-decoration-none"
                                                wire:click="sortBy('commission')">
                                            Chi NTP (VNĐ)
                                            @if($sortField === 'commission')
                                                <i class="bi bi-arrow-{{ $sortDir === 'asc' ? 'up' : 'down' }}-short ms-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="d-none d-sm-table-cell">Trạng thái</th>
                                    <th class="w-42px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contracts as $contract)
                                <tr class="cursor-pointer"
                                    wire:click="viewDetail({{ $contract->contract_id }}, '{{ addslashes($contract->model_class) }}')">
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle px-2">{{ $contract->type_label }}</span>
                                    </td>
                                    <td class="fw-semibold">
                                        {{ $contract->shd_cxl ?: '—' }}
                                        {{-- Mobile extras --}}
                                        <div class="d-sm-none mt-1">
                                            @if($contract->customer)
                                                <div class="text-muted small text-wrap mxw-150px text-wrap" >{{ $contract->customer }}</div>
                                            @endif
                                            @if($contract->status)
                                                <span class="badge px-2 py-1 fw-semibold mt-1"
                                                      style="font-size:0.65rem;background:{{ $this->statusColor($contract->status)['bg'] }};color:{{ $this->statusColor($contract->status)['text'] }};white-space:normal;">
                                                    {{ $contract->status }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $contract->shd_bc ?: '—' }}</td>
                                    <td class="text-wrap d-none d-sm-table-cell mxw-200px" >{{ $contract->customer }}</td>
                                    <td class="d-none d-md-table-cell">{{ $contract->signed_at ? $contract->signed_at->format('d/m/Y') : '—' }}</td>
                                    <td class="text-end fw-bold text-danger">{{ number_format((float) $contract->commission, 0, ',', '.') }}</td>
                                    <td class="d-none d-sm-table-cell">
                                        @if($contract->status)
                                            <span class="badge px-2 py-1 fw-semibold"
                                                  style="font-size:0.75rem; background:{{ $this->statusColor($contract->status)['bg'] }}; color:{{ $this->statusColor($contract->status)['text'] }};">
                                                {{ $contract->status }}
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
                                            <i class="fa-solid fa-eye fs-5"></i>
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
                <div class="card-footer border-top bg-body px-4 py-3">
                    {{ $contracts->links('livewire.admin.users.pagination') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal chi tiết hợp đồng --}}
    <div wire:ignore.self class="modal fade" id="handlerContractDetailModal" tabindex="-1">
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
                        <ul class="nav nav-tabs px-4 pt-3" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-semibold" data-bs-toggle="tab"
                                    data-bs-target="#htab-info-{{ $selectedContractType }}-{{ $selectedContract->id }}" type="button">
                                    <i class="fa-solid fa-circle-info me-1"></i>Thông tin HĐ
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-semibold" data-bs-toggle="tab"
                                    data-bs-target="#htab-progress-{{ $selectedContractType }}-{{ $selectedContract->id }}" type="button">
                                    <i class="fa-solid fa-sitemap me-1"></i>Tiến độ hoàn thành
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            {{-- Tab 1: Thông tin HĐ --}}
                            <div class="tab-pane fade show active"
                                id="htab-info-{{ $selectedContractType }}-{{ $selectedContract->id }}"
                                role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3 w-25" >Khách hàng</th>
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
                                                <th class="bg-light fw-bold px-4 py-3">Số hợp đồng CXL</th>
                                                <td class="px-4 py-3 fw-bold">{{ $selectedContract->shd_cxl }}</td>
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
                                                <tr>
                                                    <th class="bg-light fw-bold px-4 py-3">Chi nhà cung cấp</th>
                                                    <td class="px-4 py-3 fw-bold text-danger">
                                                        {{ number_format($selectedContract->ncc_payment ?? 0) }}đ</td>
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
                                                <th class="bg-light fw-bold px-4 py-3">Ngày ký hợp đồng</th>
                                                <td class="px-4 py-3">
                                                    {{ $selectedContract->signed_at ? $selectedContract->signed_at->format('d/m/Y') : '—' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Ngày xuất hóa đơn</th>
                                                <td class="px-4 py-3 text-danger">
                                                    {{ $selectedContract->submitted_at ? $selectedContract->submitted_at->format('d/m/Y') : '—' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Trạng thái</th>
                                                <td class="px-4 py-3">{{ $selectedContract->status }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Ghi chú</th>
                                                <td class="px-4 py-3">{{ $selectedContract->notes ?? $selectedContract->note }}</td>
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
                                                    <i class="fa-solid fa-book me-1"></i> Ghi chú tiến độ
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
                                                            <i class="fa-solid fa-plus me-1"></i> Thêm ghi chú
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
                                id="htab-progress-{{ $selectedContractType }}-{{ $selectedContract->id }}"
                                role="tabpanel">
                                <livewire:admin.contracts.contract-workflow-progress
                                    :contractType="$selectedContractType"
                                    :contractId="$selectedContract->id"
                                    :key="'hprogress-' . $selectedContractType . '-' . $selectedContract->id" />
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
            const el = document.getElementById('handlerContractDetailModal');
            if (el) bootstrap.Modal.getOrCreateInstance(el).show();
        });
    });
</script>
@endpush
