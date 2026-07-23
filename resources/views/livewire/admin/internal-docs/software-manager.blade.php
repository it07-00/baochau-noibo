<div class="software-manager pb-4">
    {{-- Header --}}
    <header class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-start gap-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white p-3 shadow-sm"><i class="fa-solid fa-laptop-code fs-4"></i></span>
            <div><h1 class="h4 fw-bold text-body mb-1">Phần mềm nội bộ</h1><p class="text-secondary-emphasis mb-0">Danh mục công cụ và đường dẫn phục vụ công việc nội bộ.</p></div>
        </div>
        @if($canManage)
            <button wire:click="openCreateModal" wire:loading.attr="disabled" wire:target="openCreateModal" class="btn btn-primary text-nowrap"><i class="fa-solid fa-plus me-1"></i> Thêm phần mềm</button>
        @endif
    </header>

    <section class="card border shadow-sm mb-4">
        <div class="card-body p-3 p-lg-4">
            <div class="row g-3 align-items-end">
                <div class="col-lg-7">
                    <label for="software-search" class="form-label fw-semibold small">Tìm kiếm</label>
                    <div class="input-group"><span class="input-group-text bg-body"><i class="fa-solid fa-magnifying-glass"></i></span><input id="software-search" type="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Tên, phiên bản hoặc mô tả phần mềm..."></div>
                </div>
                <div class="col-lg-5">
                    <label for="software-department" class="form-label fw-semibold small">Phòng ban</label>
                    <select id="software-department" class="form-select" wire:model.live="departmentFilter">
                    <option value="">Tất cả phòng ban</option>
                    <option value="company">Toàn công ty</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                    </select>
                </div>
            </div>
            <div wire:loading.flex wire:target="search,departmentFilter" class="align-items-center gap-2 mt-3 small text-primary" role="status"><span class="spinner-border spinner-border-sm"></span>Đang cập nhật...</div>
        </div>
    </section>

    {{-- Content --}}
    <div class="row g-4">
        @forelse($softwares as $sw)
            <div class="col-xl-4 col-md-6">
                <article class="card border shadow-sm h-100 rounded-3">
                     <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center wh-48 fs-5" >
                                    <i class="fa-solid fa-box"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-body">{{ $sw->name }}</h6>
                                    @if($sw->version)
                                        <small class="text-muted">Phiên bản: {{ $sw->version }}</small>
                                    @endif
                                    <div class="mt-1">
                                    <span class="badge text-bg-info">{{ $sw->department?->name ?? 'Toàn công ty' }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($canManage)
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-label="Thao tác {{ $sw->name }}">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><button wire:click="edit({{ $sw->id }})" class="dropdown-item py-2"><i class="fa-solid fa-pen me-2 text-primary"></i>Chỉnh sửa</button></li>
                                        <li><button wire:click="delete({{ $sw->id }})" wire:confirm="Bạn có chắc muốn xóa phần mềm này?" class="dropdown-item py-2 text-danger"><i class="fa-solid fa-trash me-2"></i>Xóa</button></li>
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <p class="card-text text-secondary flex-grow-1 small">
                            {{ $sw->description ?: 'Không có mô tả.' }}
                        </p>

                        <div class="mt-3">
                            <a href="{{ $sw->url }}" target="_blank" rel="noopener" class="btn btn-primary w-100 fw-semibold">
                                <i class="fa-solid fa-link me-1"></i> Truy cập / Tải xuống
                            </a>
                        </div>

                        @if($canManage)
                            <div class="mt-2 text-center">
                                <span class="badge {{ $sw->is_active ? 'text-bg-success' : 'text-bg-secondary' }} rounded-pill">
                                    {{ $sw->is_active ? 'Hoạt động' : 'Đã ẩn' }}
                                </span>
                            </div>
                        @endif
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-laptop text-muted opacity-25 fs-4rem" ></i>
                <p class="text-muted mt-3 mb-0">Chưa có phần mềm nào.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $softwares->links() }}
    </div>

    {{-- Create/Edit Modal --}}
    @if($canManage)
        <div class="modal fade {{ $showModal ? 'show d-block' : '' }} bg-dark-50" tabindex="-1" >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-16px" >
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
                                <label class="form-label fw-bold text-muted">Phòng ban</label>
                                <select wire:model="departmentId" class="form-select">
                                    <option value="">Toàn công ty</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('departmentId') <span class="text-danger small">{{ $message }}</span> @enderror
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
                            <button type="button" class="btn btn-light rounded-8px" wire:click="$set('showModal', false)" >Hủy</button>
                            <button type="submit" class="btn btn-primary rounded-8px" >
                                <i class="fa-solid fa-floppy-disk me-1"></i> Lưu lại
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
