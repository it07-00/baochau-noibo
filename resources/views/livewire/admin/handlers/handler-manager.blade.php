<div>
    @section('title', 'Quản lý Nhà thầu phụ')
    @section('page_title', 'Danh sách Nhà thầu phụ')

    <div class="row g-3 mt-2 px-2 px-md-0">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden rounded-12px">
                <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 p-md-4 border-bottom">
                    <div>
                        <h2 class="h4 fw-bold text-body mb-1">Danh sách nhà thầu phụ</h2>
                        <p class="small text-muted mb-0">{{ number_format($totalHandlers) }} nhà thầu phụ đang được quản lý</p>
                    </div>

                    <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2 w-100 w-md-auto" style="max-width: 440px;">
                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary border-end-0 text-body-secondary">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="search" class="form-control border-start-0 ps-0" placeholder="Tên, SĐT hoặc địa chỉ...">
                        </div>

                        @can('handlers.create')
                        <button class="btn btn-primary text-nowrap btn-mobile-touch" wire:click="openCreate">
                            <i class="fa-solid fa-plus me-1"></i>Thêm nhà thầu phụ
                        </button>
                        @endcan
                    </div>
                </div>

                <div class="card-body p-0">
                {{-- Desktop: Table --}}
                    <div class="table-responsive d-none d-sm-block">
                        <table class="table text-nowrap align-middle table-hover mb-0">
                            <thead class="bg-body-tertiary text-uppercase text-secondary" style="font-size: 0.75rem;">
                                <tr>
                                    <th width="60">ID</th>
                                    <th>Tên nhà thầu phụ</th>
                                    <th class="d-none d-md-table-cell">Số điện thoại</th>
                                    <th class="d-none d-lg-table-cell">Địa chỉ</th>
                                    <th class="text-center">Hợp đồng</th>
                                    @canany(['handlers.edit', 'handlers.delete'])
                                    <th class="text-end">Hành động</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($handlers as $handler)
                                <tr wire:key="handler-{{ $handler->id }}">
                                    <td class="text-center text-muted fw-semibold">{{ $handler->id }}</td>
                                    <td class="fw-bold">
                                        <a href="{{ route('app.handlers.contracts', $handler) }}" class="text-body text-decoration-none link-hover-primary">
                                            {{ $handler->name }}
                                        </a>
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $handler->phone ?: '—' }}</td>
                                    <td class="d-none d-lg-table-cell mxw-220px" >
                                        @if($handler->address)
                                            <span class="d-block text-truncate" title="{{ $handler->address }}">{{ $handler->address }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($handler->contracts_count > 0)
                                            <a href="{{ route('app.handlers.contracts', $handler) }}"
                                               class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 text-decoration-none">
                                                {{ $handler->contracts_count }} HĐ
                                            </a>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1">0 HĐ</span>
                                        @endif
                                    </td>
                                    @canany(['handlers.edit', 'handlers.delete'])
                                    <td class="text-end">
                                        @can('handlers.edit')
                                        <button class="btn btn-sm btn-outline-primary rounded-8px me-1" wire:click="openEdit({{ $handler->id }})" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        @endcan
                                        @can('handlers.delete')
                                        <button class="btn btn-sm btn-outline-danger rounded-8px"
                                                wire:click="delete({{ $handler->id }})"
                                                wire:confirm="Xác nhận xóa nhà thầu phụ này?"
                                                title="Xóa" {{ $handler->contracts_count > 0 ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        @endcan
                                    </td>
                                    @endcanany
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Không có dữ liệu nhà thầu phụ.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile: Card list --}}
                    <div class="d-sm-none px-3 pb-3">
                        @forelse($handlers as $handler)
                        <div wire:key="handler-card-{{ $handler->id }}" class="card border border-secondary-subtle shadow-sm rounded-12px p-3 mb-3 bg-body">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <a href="{{ route('app.handlers.contracts', $handler) }}" class="fw-bold text-body text-decoration-none fs-90 lh-base" >
                                    {{ $handler->name }}
                                </a>
                                <div class="d-flex gap-1 flex-shrink-0">
                                    @if($handler->contracts_count > 0)
                                        <a href="{{ route('app.handlers.contracts', $handler) }}"
                                           class="badge bg-label-primary px-2 py-1 text-decoration-none">
                                            {{ $handler->contracts_count }} HĐ
                                        </a>
                                    @else
                                        <span class="badge bg-label-secondary px-2 py-1">0 HĐ</span>
                                    @endif
                                </div>
                            </div>
                            @if($handler->phone || $handler->address)
                            <div class="mt-2 d-flex flex-column gap-1 fs-80 text-muted" >
                                @if($handler->phone)
                                    <span><i class="fa-solid fa-phone me-1"></i>{{ $handler->phone }}</span>
                                @endif
                                @if($handler->address)
                                    <span><i class="fa-solid fa-location-dot me-1"></i>{{ $handler->address }}</span>
                                @endif
                            </div>
                            @endif
                            @canany(['handlers.edit', 'handlers.delete'])
                            <div class="mt-2 d-flex gap-2 border-top pt-2">
                                @can('handlers.edit')
                                <button class="btn btn-sm btn-outline-primary flex-fill" wire:click="openEdit({{ $handler->id }})">
                                    <i class="fa-solid fa-pen me-1"></i>Sửa
                                </button>
                                @endcan
                                @can('handlers.delete')
                                <button class="btn btn-sm btn-outline-danger flex-fill"
                                        wire:click="delete({{ $handler->id }})"
                                        wire:confirm="Xác nhận xóa nhà thầu phụ này?"
                                        {{ $handler->contracts_count > 0 ? 'disabled' : '' }}>
                                    <i class="fa-solid fa-trash me-1"></i>Xóa
                                </button>
                                @endcan
                            </div>
                            @endcanany
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">Không có dữ liệu nhà thầu phụ.</div>
                        @endforelse
                    </div>
                </div>

                @if($handlers->hasPages())
                <div class="card-footer border-top bg-body px-4 py-3">
                    {{ $handlers->links('livewire.admin.users.pagination') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="handlerFormModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header align-items-start border-0 bg-body p-4 pb-2">
                    <div class="d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary p-3">
                            <i class="fa-solid fa-handshake fs-5"></i>
                        </span>
                        <div>
                            <h5 class="modal-title fw-bold text-body mb-1">
                                {{ $isEditing ? 'Cập nhật nhà thầu phụ' : 'Thêm nhà thầu phụ mới' }}
                            </h5>
                            <p class="small text-muted mb-0">Thông tin liên hệ phục vụ quản lý và theo dõi hợp đồng.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close mt-1" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="modal-body p-4 pt-3">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-body">Tên nhà thầu phụ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('formData.name') is-invalid @enderror" wire:model.defer="formData.name" placeholder="Ví dụ: Công ty Môi trường An Phát" autocomplete="organization">
                                @error('formData.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-body">Số điện thoại</label>
                                <input type="tel" class="form-control @error('formData.phone') is-invalid @enderror" wire:model.defer="formData.phone" placeholder="Ví dụ: 0901 234 567" autocomplete="tel">
                                @error('formData.phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-body">Địa chỉ</label>
                                <textarea rows="3" class="form-control @error('formData.address') is-invalid @enderror" wire:model.defer="formData.address" placeholder="Nhập địa chỉ văn phòng hoặc cơ sở" autocomplete="street-address"></textarea>
                                @error('formData.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-body px-4 py-3 gap-2">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            <i wire:loading.remove wire:target="save" class="fa-solid fa-floppy-disk me-1"></i>
                            {{ $isEditing ? 'Lưu thay đổi' : 'Thêm nhà thầu phụ' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        window.addEventListener('openHandlerFormModal', () => {
            new bootstrap.Modal(document.getElementById('handlerFormModal')).show();
        });

        window.addEventListener('closeHandlerFormModal', () => {
            const modalElement = document.getElementById('handlerFormModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        });
    </script>
    @endpush
</div>
