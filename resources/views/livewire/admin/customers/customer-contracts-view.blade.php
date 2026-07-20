<div>
    @section('title', 'Hợp đồng của ' . $customer->name)
    @section('page_title', 'Hợp đồng của ' . $customer->name)

    <div class="row g-3 mt-1 px-2 px-md-0">
        {{-- Thông tin khách hàng --}}
        <div class="col-12">
            <div class="card border border-light-subtle shadow-sm rounded-3 bg-body p-3 p-md-4">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                        <div class="wh-48 rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0 fs-4 fw-bold shadow-sm">
                            <i class="fa-solid fa-building text-primary"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="fw-bold fs-5 text-body mb-1">{{ $customer->name }}</div>
                            <div class="text-muted small d-flex flex-wrap gap-3">
                                @if($customer->tax_code)
                                    <span><i class="fa-solid fa-hashtag me-1 text-primary"></i>MST: <strong>{{ $customer->tax_code }}</strong></span>
                                @endif
                                @if($customer->province)
                                    <span class="d-none d-sm-inline"><i class="fa-solid fa-location-dot me-1 text-danger"></i>{{ $customer->province }}</span>
                                @endif
                                @if($customer->representative)
                                    <span class="d-none d-sm-inline"><i class="fa-solid fa-user me-1 text-info"></i>Đại diện: {{ $customer->representative }}</span>
                                @endif
                                @if($customer->address)
                                    <span class="d-none d-md-inline"><i class="fa-solid fa-map-pin me-1 text-secondary"></i>{{ $customer->address }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-3 ms-sm-auto align-items-center">
                        <div class="text-center px-3 py-1 bg-body-tertiary rounded-3 border border-light-subtle">
                            <div class="fw-bold fs-5 text-primary mb-0">{{ $totalContracts }}</div>
                            <div class="text-muted small">Hợp đồng</div>
                        </div>
                        <div class="text-center px-3 py-1 bg-body-tertiary rounded-3 border border-light-subtle">
                            <div class="fw-bold fs-6 text-success mb-0">{{ number_format($totalValue, 0, ',', '.') }}đ</div>
                            <div class="text-muted small">Tổng giá trị</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bảng hợp đồng --}}
        <div class="col-12">
            <div class="card border border-light-subtle shadow-sm rounded-3 bg-body overflow-hidden">
                <div class="card-header bg-body-tertiary border-bottom border-light-subtle p-3 p-md-4 d-flex flex-column gap-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h3 class="h5 fw-bold text-body m-0">
                            <i class="fa-solid fa-file-contract text-primary me-2"></i>Tất cả hợp đồng
                        </h3>
                        <a href="{{ route('app.customers.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
                        </a>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="d-flex align-items-center gap-1">
                            <label class="form-label mb-0 small fw-semibold text-muted text-nowrap">Từ ngày</label>
                            <input type="date" class="form-control form-control-sm border-light-subtle" wire:model.live="dateFrom">
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <label class="form-label mb-0 small fw-semibold text-muted text-nowrap">Đến ngày</label>
                            <input type="date" class="form-control form-control-sm border-light-subtle" wire:model.live="dateTo">
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <label class="form-label mb-0 small fw-semibold text-muted text-nowrap">Sắp xếp</label>
                            <select class="form-select form-select-sm border-light-subtle mnw-110px" wire:model.live="sortField">
                                <option value="signed_at">Ngày ký</option>
                                <option value="value">Giá trị</option>
                                <option value="shd_bc">Số HĐ BC</option>
                            </select>
                        </div>
                        <button class="btn btn-outline-secondary btn-sm px-2" wire:click="toggleDir">
                            @if($sortDir === 'desc')
                                <i class="fa-solid fa-sort-down me-1"></i><span class="small">Mới → Cũ</span>
                            @else
                                <i class="fa-solid fa-sort-up me-1"></i><span class="small">Cũ → Mới</span>
                            @endif
                        </button>
                        @if($dateFrom || $dateTo)
                        <button class="btn btn-outline-secondary btn-sm" wire:click="resetFilter">
                            <i class="fa-solid fa-xmark-lg"></i>
                        </button>
                        @endif
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle table-hover mb-0">
                            <thead class="bg-body-tertiary border-bottom border-light-subtle">
                                <tr>
                                    <th class="mnw-160px">Loại HĐ</th>
                                    <th>
                                        <button class="btn btn-link btn-sm p-0 text-body fw-bold text-decoration-none"
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
                                                wire:click="sortBy('value')">
                                            Giá trị (VNĐ)
                                            @if($sortField === 'value')
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
                                <tr class="cursor-pointer" onclick="window.location='{{ route($contract->contract_route) }}'">
                                    <td>
                                        <span class="d-inline-block rounded-2 bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 small fw-semibold text-wrap text-start lh-sm">{{ $contract->type_label }}</span>
                                    </td>
                                    <td class="fw-semibold text-body">
                                        {{ $contract->shd_bc ?: '—' }}
                                        {{-- Mobile extras --}}
                                        <div class="d-sm-none mt-1">
                                            @if($contract->handler)
                                                <div class="text-muted small text-wrap mxw-140px text-wrap" >{{ $contract->handler }}</div>
                                            @endif
                                            @if($contract->status)
                                                <span class="badge px-2 py-1 fw-semibold mt-1"
                                                      style="font-size:0.65rem;background:{{ $this->statusColor($contract->status)['bg'] }};color:{{ $this->statusColor($contract->status)['text'] }};white-space:normal;">
                                                    {{ $contract->status }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell text-body">{{ $contract->shd_cxl ?: '—' }}</td>
                                    <td class="text-wrap d-none d-sm-table-cell max-w-180px text-body">{{ $contract->handler }}</td>
                                    <td class="d-none d-md-table-cell text-body">{{ $contract->signed_at ? $contract->signed_at->format('d/m/Y') : '—' }}</td>
                                    <td class="text-end fw-bold text-body">{{ $contract->value ? number_format($contract->value, 0, ',', '.') : '—' }}</td>
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
                <div class="card-footer bg-body border-top border-light-subtle px-4 py-3">
                    {{ $contracts->links('livewire.admin.users.pagination') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal chi tiết hợp đồng --}}
    <div wire:ignore.self class="modal fade" id="contractDetailModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content overflow-hidden border-0 shadow-lg rounded-3">
                <div class="modal-header bg-body border-bottom border-light-subtle p-3">
                    <h5 class="modal-title fw-bold text-body">
                        Thông tin Hợp Đồng
                        @if($selectedContractLabel)
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle ms-2 fw-semibold fs-6">{{ $selectedContractLabel }}</span>
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body p-0">
                    @if ($selectedContract)
                        {{-- Tabs Navigation --}}
                        <ul class="nav nav-tabs px-4 pt-3" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-semibold" data-bs-toggle="tab"
                                    data-bs-target="#tab-info-{{ $selectedContractType }}-{{ $selectedContract->id }}" type="button">
                                    <i class="fa-solid fa-circle-info me-1"></i>Thông tin HĐ
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-semibold" data-bs-toggle="tab"
                                    data-bs-target="#tab-progress-{{ $selectedContractType }}-{{ $selectedContract->id }}" type="button">
                                    <i class="fa-solid fa-sitemap me-1"></i>Tiến độ hoàn thành
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
                                                <th class="bg-light fw-bold px-4 py-3 w-25" >Ghi chú</th>
                                                <td class="px-4 py-3">{{ $selectedContract->notes ?? $selectedContract->note }}</td>
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
