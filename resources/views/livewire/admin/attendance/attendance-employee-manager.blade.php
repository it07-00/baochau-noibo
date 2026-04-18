<div>
    @section('title', 'Quản lý nhân viên chấm công')
    @section('page_title', 'Nhân viên chấm công')

    @php
        $breadcrumbs = [
            ['label' => 'Quản trị', 'url' => route('app.dashboard')],
            ['label' => 'Chấm công', 'url' => route('app.attendance.index')],
            ['label' => 'Nhân viên'],
        ];
    @endphp

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom">
                <div class="pure-card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h3 class="pure-card-title m-0">Nhân viên chấm công</h3>

                    <div class="d-flex align-items-center gap-3">
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text bg-transparent border-end-0">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                   class="form-control border-start-0 ps-0" placeholder="Tìm tên hoặc mã...">
                        </div>

                        <select wire:model.live="filterDepartment" class="form-select" style="width:200px;">
                            <option value="">Tất cả phòng ban</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>

                        <button class="btn btn-primary" wire:click="openCreate">
                            <i class="bi bi-plus-circle me-1"></i>Thêm mới
                        </button>
                    </div>
                </div>

                <div class="pure-card-body pb-3">
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th width="70">Mã máy</th>
                                    <th>Họ và tên</th>
                                    <th width="180">Phòng ban</th>
                                    <th width="110" class="text-center">Ngày tạo</th>
                                    <th width="100" class="text-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $emp)
                                    <tr wire:key="emp-{{ $emp->id }}">
                                        <td class="fw-bold">{{ str_pad($emp->device_uid, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td class="fw-bold">{{ $emp->name }}</td>
                                        <td>
                                            @if($emp->department)
                                                <span class="badge bg-label-primary px-2 py-1">{{ $emp->department }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center text-muted">{{ $emp->created_at?->format('d/m/Y') }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-icon btn-light text-primary rounded-pill me-1"
                                                    wire:click="openEdit({{ $emp->id }})" title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-icon btn-light text-danger rounded-pill"
                                                    wire:click="confirmDelete({{ $emp->id }})" title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Không có nhân viên nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($employees->hasPages())
                    <div class="pure-card-footer border-top px-4 py-3">
                        {{ $employees->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Thêm/Sửa --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);"
             wire:click.self="$set('showModal', false)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg overflow-hidden">
                    <div class="modal-header bg-primary py-3">
                        <h5 class="modal-title fw-bold text-white">{{ $isCreating ? 'Thêm nhân viên' : 'Cập nhật nhân viên' }}</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                    </div>

                    <form wire:submit="save">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                @if($isCreating)
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Mã máy chấm công <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="editDeviceUid"
                                               class="form-control @error('editDeviceUid') is-invalid @enderror" min="1"
                                               placeholder="VD: 14">
                                        @error('editDeviceUid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="editName"
                                               class="form-control @error('editName') is-invalid @enderror"
                                               placeholder="Nhập họ và tên">
                                        @error('editName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                @else
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-muted">Mã máy</label>
                                        <input type="text" class="form-control" value="{{ str_pad($editDeviceUid, 5, '0', STR_PAD_LEFT) }}" disabled>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="editName"
                                               class="form-control @error('editName') is-invalid @enderror"
                                               placeholder="Nhập họ và tên">
                                        @error('editName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                @endif

                                <div class="col-12">
                                    <label class="form-label fw-bold">Phòng ban</label>
                                    <select wire:model="editDepartment"
                                            class="form-select @error('editDepartment') is-invalid @enderror">
                                        <option value="">— Chưa gán phòng ban —</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept }}">{{ $dept }}</option>
                                        @endforeach
                                    </select>
                                    @error('editDepartment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary"
                                    wire:click="$set('showModal', false)">Hủy</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                                {{ $isCreating ? 'Thêm' : 'Lưu' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirm Delete --}}
    @if($confirmingDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);"
             wire:click.self="$set('confirmingDelete', false)">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg overflow-hidden">
                    <div class="modal-header bg-danger py-3">
                        <h5 class="modal-title fw-bold text-white">Xác nhận xóa</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('confirmingDelete', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Xóa nhân viên này sẽ xóa luôn toàn bộ lịch sử chấm công. Bạn chắc chắn?</p>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary"
                                wire:click="$set('confirmingDelete', false)">Hủy</button>
                        <button type="button" class="btn btn-danger" wire:click="delete"
                                wire:loading.attr="disabled">
                            <span wire:loading wire:target="delete" class="spinner-border spinner-border-sm me-1"></span>
                            Xóa
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
