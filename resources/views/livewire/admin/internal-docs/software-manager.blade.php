<div class="software-manager w-100 px-2 px-md-3 pb-5">
    {{-- Header --}}
    <div class="card border-0 shadow-sm mb-4 rounded-12px overflow-hidden" >
        <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="mb-0 fw-bold">
                <i class="fa-solid fa-laptop me-2 text-primary"></i>Phần mềm nội bộ
            </h5>

            <div class="d-flex align-items-center gap-3">
                <div class="input-group w-250px" >
                    <span class="input-group-text bg-light border-end-0 rounded-start-2" >
                        <i class="fa-solid fa-magnifying-glass text-muted"></i>
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0 bg-light rounded-end-2 border"
                        placeholder="Tìm kiếm phần mềm..." >
                </div>

                <select class="form-select form-select-sm w-auto" wire:model.live="departmentFilter">
                    <option value="">Tất cả phòng ban</option>
                    <option value="company">Toàn công ty</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>

                @if(auth()->user()->hasRole(\App\Enums\Role::IT->value))
                    <button wire:click="openCreateModal" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2 rounded-8px" >
                        <i class="fa-solid fa-plus-lg"></i> Thêm mới
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="row g-4">
        @forelse($softwares as $sw)
            <div class="col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm h-100 rounded-3 hover-lift cursor-pointer" 
                     onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
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
                                        <span class="badge bg-label-info">{{ $sw->department?->name ?? 'Toàn công ty' }}</span>
                                    </div>
                                </div>
                            </div>

                            @if(auth()->user()->hasRole(\App\Enums\Role::IT->value))
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-8px" >
                                        <li><button wire:click="edit({{ $sw->id }})" class="dropdown-item py-2 fs-85" ><i class="fa-solid fa-pen me-2 text-primary"></i>Chỉnh sửa</button></li>
                                        <li><button wire:click="delete({{ $sw->id }})" wire:confirm="Bạn có chắc muốn xóa phần mềm này?" class="dropdown-item py-2 text-danger fs-85" ><i class="fa-solid fa-trash me-2"></i>Xóa</button></li>
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <p class="card-text text-muted flex-grow-1 fs-90 line-clamp-3" >
                            {{ $sw->description ?: 'Không có mô tả.' }}
                        </p>

                        <div class="mt-3">
                            <a href="{{ $sw->url }}" target="_blank" class="btn btn-outline-primary btn-sm w-100 fw-medium rounded-8px" >
                                <i class="fa-solid fa-link me-1"></i> Truy cập / Tải xuống
                            </a>
                        </div>

                        @if(auth()->user()->hasRole(\App\Enums\Role::IT->value))
                            <div class="mt-2 text-center">
                                <span class="badge {{ $sw->is_active ? 'bg-success' : 'bg-secondary' }} bg-opacity-10 text-{{ $sw->is_active ? 'success' : 'secondary' }} rounded-pill fs-70" >
                                    {{ $sw->is_active ? 'Hoạt động' : 'Đã ẩn' }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
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
    @if(auth()->user()->hasRole(\App\Enums\Role::IT->value))
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
