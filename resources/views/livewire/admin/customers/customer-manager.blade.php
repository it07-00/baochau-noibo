<div>
    @section('title', 'Quản lý khách hàng')
    @section('page_title', 'Danh sách khách hàng')

    <div class="row g-3 mt-1 px-2 px-md-0">
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom">
                <div class="pure-card-header d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2">
                    <h3 class="pure-card-title m-0">Danh sách khách hàng</h3>

                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 mt-1 mt-md-0">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent border-end-0">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 ps-0" placeholder="Tìm theo tên, mã số thuế...">
                        </div>

                        @can('customers.create')
                        <button class="btn btn-primary btn-sm text-nowrap" wire:click="openCreate">
                            <i class="fa-solid fa-plus-circle me-1"></i>Tạo mới
                        </button>
                        @endcan
                    </div>
                </div>

                <div class="pure-card-body pb-3">
                    {{-- Desktop: Table --}}
                    <div class="table-responsive d-none d-sm-block">
                        <table class="table text-nowrap align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center w-45px" >STT</th>
                                    <th>Tên khách hàng</th>
                                    <th class="d-none d-md-table-cell">Mã số thuế</th>
                                    <th class="d-none d-lg-table-cell">Tỉnh thành</th>
                                    <th class="d-none d-md-table-cell">Người đại diện</th>
                                    <th class="text-center">Số HĐ</th>
                                    @canany(['customers.edit', 'customers.delete'])
                                    <th class="text-end">Hành động</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $customer)
                                <tr wire:key="customer-{{ $customer->id }}">
                                    <td class="text-center text-muted fw-semibold">{{ ($customers->currentPage() - 1) * $customers->perPage() + $loop->iteration }}</td>
                                    <td class="fw-bold">
                                        <a href="{{ route('app.customers.contracts', $customer) }}" class="text-body text-decoration-none link-hover-primary">
                                            {{ $customer->name }}
                                        </a>
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $customer->tax_code ?: '—' }}</td>
                                    <td class="d-none d-lg-table-cell">{{ $customer->province ?: '—' }}</td>
                                    <td class="d-none d-md-table-cell">{{ $customer->representative ?: '—' }}</td>
                                    <td class="text-center">
                                        @if($this->totalContractsCount($customer) > 0)
                                            <a href="{{ route('app.customers.contracts', $customer) }}"
                                               class="badge bg-label-primary px-2 py-1 text-decoration-none">
                                                {{ $this->totalContractsCount($customer) }}
                                            </a>
                                        @else
                                            <span class="badge bg-label-secondary px-2 py-1">0</span>
                                        @endif
                                    </td>
                                    @canany(['customers.edit', 'customers.delete'])
                                    <td class="text-end">
                                        @can('customers.edit')
                                        <button class="btn btn-sm btn-icon btn-light text-primary rounded-pill me-1" wire:click="openEdit({{ $customer->id }})" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        @endcan
                                        @can('customers.delete')
                                        <button class="btn btn-sm btn-icon btn-light text-danger rounded-pill"
                                                wire:click="delete({{ $customer->id }})"
                                                wire:confirm="Xác nhận xóa khách hàng này?"
                                                title="Xóa" {{ $this->totalContractsCount($customer) > 0 ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        @endcan
                                    </td>
                                    @endcanany
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">Không có dữ liệu khách hàng.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile: Card list --}}
                    <div class="d-sm-none px-3 pb-3">
                        @forelse($customers as $customer)
                        <div wire:key="customer-card-{{ $customer->id }}" class="border rounded-3 p-3 mb-2 bg-body">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <a href="{{ route('app.customers.contracts', $customer) }}" class="fw-bold text-body text-decoration-none fs-90 lh-base" >
                                    {{ $customer->name }}
                                </a>
                                <div class="flex-shrink-0">
                                    @if($this->totalContractsCount($customer) > 0)
                                        <a href="{{ route('app.customers.contracts', $customer) }}"
                                           class="badge bg-label-primary px-2 py-1 text-decoration-none">
                                            {{ $this->totalContractsCount($customer) }} HĐ
                                        </a>
                                    @else
                                        <span class="badge bg-label-secondary px-2 py-1">0 HĐ</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-2 d-flex flex-column gap-1 fs-80 text-muted" >
                                @if($customer->tax_code)
                                    <span><i class="fa-solid fa-address-card me-1"></i>MST: {{ $customer->tax_code }}</span>
                                @endif
                                @if($customer->province)
                                    <span><i class="fa-solid fa-location-dot me-1"></i>{{ $customer->province }}</span>
                                @endif
                                @if($customer->representative)
                                    <span><i class="fa-solid fa-user me-1"></i>{{ $customer->representative }}</span>
                                @endif
                            </div>
                            @canany(['customers.edit', 'customers.delete'])
                            <div class="mt-2 d-flex gap-2 border-top pt-2">
                                @can('customers.edit')
                                <button class="btn btn-sm btn-outline-primary flex-fill" wire:click="openEdit({{ $customer->id }})">
                                    <i class="fa-solid fa-pen me-1"></i>Sửa
                                </button>
                                @endcan
                                @can('customers.delete')
                                <button class="btn btn-sm btn-outline-danger flex-fill"
                                        wire:click="delete({{ $customer->id }})"
                                        wire:confirm="Xác nhận xóa khách hàng này?"
                                        {{ $this->totalContractsCount($customer) > 0 ? 'disabled' : '' }}>
                                    <i class="fa-solid fa-trash me-1"></i>Xóa
                                </button>
                                @endcan
                            </div>
                            @endcanany
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">Không có dữ liệu khách hàng.</div>
                        @endforelse
                    </div>

                @if($customers->hasPages())
                <div class="pure-card-footer border-top px-4 py-3">
                    {{ $customers->links('livewire.admin.users.pagination') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="customerFormModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <div class="modal-header bg-primary py-3">
                    <h5 class="modal-title fw-bold text-white">{{ $isEditing ? 'Cập nhật khách hàng' : 'Thêm khách hàng mới' }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tên khách hàng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('formData.name') is-invalid @enderror" wire:model.defer="formData.name" placeholder="Nhập tên khách hàng">
                                @error('formData.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mã số thuế</label>
                                <input type="text" class="form-control @error('formData.tax_code') is-invalid @enderror" wire:model.defer="formData.tax_code" placeholder="Nhập mã số thuế">
                                @error('formData.tax_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Người đại diện</label>
                                <input type="text" class="form-control @error('formData.representative') is-invalid @enderror" wire:model.defer="formData.representative" placeholder="Nhập tên người đại diện">
                                @error('formData.representative') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tỉnh thành</label>
                                <select class="form-select @error('formData.province') is-invalid @enderror" wire:model.defer="formData.province">
                                    <option value="">-- Chọn tỉnh thành --</option>
                                    @foreach($provinces as $p)
                                        <option value="{{ $p }}" {{ $formData['province'] === $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                                @error('formData.province') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Địa chỉ</label>
                                <textarea rows="2" class="form-control @error('formData.address') is-invalid @enderror" wire:model.defer="formData.address" placeholder="Nhập địa chỉ"></textarea>
                                @error('formData.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            Lưu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        window.addEventListener('openCustomerFormModal', () => {
            new bootstrap.Modal(document.getElementById('customerFormModal')).show();
        });

        window.addEventListener('closeCustomerFormModal', () => {
            const modalElement = document.getElementById('customerFormModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        });
    </script>
    @endpush
</div>
