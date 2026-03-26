<div>
    @section('title', 'Quản lý Chủ xử lý')
    @section('page_title', 'Danh sách Chủ xử lý')

    @php
        $breadcrumbs = [
            ['label' => 'Quản trị', 'url' => route('app.dashboard')],
            ['label' => 'Chủ xử lý']
        ];
    @endphp

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom">
                <div class="pure-card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h3 class="pure-card-title m-0">Danh sách chủ xử lý</h3>

                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: 280px;">
                            <span class="input-group-text bg-transparent border-end-0">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 ps-0" placeholder="Tìm theo tên, SĐT, địa chỉ...">
                        </div>

                        @can('handlers.create')
                        <button class="btn btn-primary btn-sm" wire:click="openCreate">
                            <i class="bi bi-plus-circle me-1"></i>Tạo mới
                        </button>
                        @endcan
                    </div>
                </div>

                <div class="pure-card-body pb-3">
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">ID</th>
                                    <th>Tên Chủ xử lý</th>
                                    <th>Số điện thoại</th>
                                    <th>Địa chỉ</th>
                                    <th class="text-center">Số HĐ đang dùng</th>
                                    <th class="text-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($handlers as $handler)
                                <tr wire:key="handler-{{ $handler->id }}">
                                    <td>{{ $handler->id }}</td>
                                    <td class="fw-bold">{{ $handler->name }}</td>
                                    <td>{{ $handler->phone ?: '—' }}</td>
                                    <td class="text-wrap" style="max-width: 300px;">{{ $handler->address ?: '—' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-label-primary px-2 py-1">{{ $handler->contracts_count }}</span>
                                    </td>
                                    <td class="text-end">
                                        @can('handlers.edit')
                                        <button class="btn btn-sm btn-icon btn-light text-primary rounded-pill me-1" wire:click="openEdit({{ $handler->id }})" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @endcan

                                        @can('handlers.delete')
                                        <button class="btn btn-sm btn-icon btn-light text-danger rounded-pill"
                                                onclick="confirm('Xác nhận xóa Chủ xử lý này?') || event.stopImmediatePropagation()"
                                                wire:click="delete({{ $handler->id }})"
                                                title="Xóa" {{ $handler->contracts_count > 0 ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endcan
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Không có dữ liệu Chủ xử lý.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($handlers->hasPages())
                <div class="pure-card-footer border-top px-4 py-3">
                    {{ $handlers->links('livewire.admin.users.pagination') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="handlerFormModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <div class="modal-header bg-primary py-3">
                    <h5 class="modal-title fw-bold text-white">{{ $isEditing ? 'Cập nhật Chủ xử lý' : 'Thêm Chủ xử lý mới' }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tên Chủ xử lý <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('formData.name') is-invalid @enderror" wire:model.defer="formData.name" placeholder="Nhập tên chủ xử lý">
                                @error('formData.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <input type="text" class="form-control @error('formData.phone') is-invalid @enderror" wire:model.defer="formData.phone" placeholder="Nhập số điện thoại">
                                @error('formData.phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
