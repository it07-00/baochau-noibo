<div class="software-manager w-100 px-2 px-md-3 pb-5">
    {{-- Header --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-laptop me-2 text-primary"></i>Phần mềm nội bộ
            </h5>

            <div class="d-flex align-items-center gap-3">
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0" style="border-radius: 8px 0 0 8px;">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0 bg-light"
                        placeholder="Tìm kiếm phần mềm..." style="border-radius: 0 8px 8px 0; border: 1px solid #dee2e6;">
                </div>

                @if(auth()->user()->hasRole(\App\Enums\Role::IT->value))
                    <button wire:click="openCreateModal" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2" style="border-radius: 8px;">
                        <i class="bi bi-plus-lg"></i> Thêm mới
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="row g-4">
        @forelse($softwares as $sw)
            <div class="col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; transition: transform 0.2s; cursor: pointer;"
                     onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.5rem;">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $sw->name }}</h6>
                                    @if($sw->version)
                                        <small class="text-muted">Phiên bản: {{ $sw->version }}</small>
                                    @endif
                                </div>
                            </div>

                            @if(auth()->user()->hasRole(\App\Enums\Role::IT->value))
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius: 8px;">
                                        <li><button wire:click="edit({{ $sw->id }})" class="dropdown-item py-2" style="font-size: 0.85rem;"><i class="bi bi-pencil me-2 text-primary"></i>Chỉnh sửa</button></li>
                                        <li><button wire:click="delete({{ $sw->id }})" wire:confirm="Bạn có chắc muốn xóa phần mềm này?" class="dropdown-item py-2 text-danger" style="font-size: 0.85rem;"><i class="bi bi-trash me-2"></i>Xóa</button></li>
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <p class="card-text text-muted flex-grow-1" style="font-size: 0.9rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $sw->description ?: 'Không có mô tả.' }}
                        </p>

                        <div class="mt-3">
                            <a href="{{ $sw->url }}" target="_blank" class="btn btn-outline-primary btn-sm w-100 fw-medium" style="border-radius: 8px;">
                                <i class="bi bi-link-45deg me-1"></i> Truy cập / Tải xuống
                            </a>
                        </div>

                        @if(auth()->user()->hasRole(\App\Enums\Role::IT->value))
                            <div class="mt-2 text-center">
                                <span class="badge {{ $sw->is_active ? 'bg-success' : 'bg-secondary' }} bg-opacity-10 text-{{ $sw->is_active ? 'success' : 'secondary' }} rounded-pill" style="font-size: 0.7rem;">
                                    {{ $sw->is_active ? 'Hoạt động' : 'Đã ẩn' }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-laptop text-muted opacity-25" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3 mb-0">Chưa có phần mềm nào.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $softwares->links() }}
    </div>

    {{-- Create/Edit Modal --}}
    @if(auth()->user()->hasRole(\App\Enums\Role::IT->value))
        <div class="modal fade {{ $showModal ? 'show d-block' : '' }}" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow" style="border-radius: 16px;">
                    <div class="modal-header border-bottom py-3">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-{{ $editingId ? 'pencil-square' : 'plus-circle' }} me-2 text-primary"></i>
                            {{ $editingId ? 'Sửa phần mềm' : 'Thêm phần mềm mới' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Tên phần mềm <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control" placeholder="Tên phần mềm..." required>
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Đường dẫn / Link tải <span class="text-danger">*</span></label>
                                <input type="url" wire:model="url" class="form-control" placeholder="https://..." required>
                                @error('url') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Phiên bản</label>
                                <input type="text" wire:model="version" class="form-control" placeholder="Ví dụ: v1.0.0">
                                @error('version') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Mô tả</label>
                                <textarea wire:model="description" class="form-control" rows="3" placeholder="Thông tin thêm..."></textarea>
                                @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" wire:model="is_active" id="isActiveSwitch">
                                <label class="form-check-label text-muted fw-bold" for="isActiveSwitch">Hiển thị cho mọi người</label>
                            </div>
                        </div>
                        <div class="modal-footer border-top py-3">
                            <button type="button" class="btn btn-light" wire:click="$set('showModal', false)" style="border-radius: 8px;">Hủy</button>
                            <button type="submit" class="btn btn-primary" style="border-radius: 8px;">
                                <i class="bi bi-save me-1"></i> Lưu lại
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
