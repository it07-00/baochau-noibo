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

    <div class="row g-3 mt-1 px-2 px-md-0">
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom">
                <div class="pure-card-header d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2">
                    <h3 class="pure-card-title m-0">Nhân viên chấm công</h3>

                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 mt-1 mt-md-0">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                   class="form-control border-start-0 ps-0" placeholder="Tìm tên hoặc mã...">
                        </div>

                        <select wire:model.live="filterDepartment" class="form-select">
                            <option value="">Tất cả phòng ban</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>

                        {{-- Toggle hiển thị NV đã nghỉ --}}
                        <div class="form-check form-switch d-flex align-items-center gap-2 mb-0 ms-1">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   wire:model.live="showInactive" id="toggleInactive"
                                   style="cursor:pointer;">
                            <label class="form-check-label text-muted text-nowrap" for="toggleInactive"
                                   style="font-size:0.85rem;">Hiện đã nghỉ</label>
                        </div>

                        <button class="btn btn-outline-secondary" wire:click="openSyncModal">
                            <i class="bi bi-arrow-repeat me-1"></i>Đồng bộ từ máy
                        </button>
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
                                    <th width="60">Mã máy</th>
                                    <th>Họ và tên</th>
                                    <th width="160" class="d-none d-sm-table-cell">Phòng ban</th>
                                    <th width="110" class="text-center d-none d-md-table-cell">Ngày tạo</th>
                                    <th width="110" class="text-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $emp)
                                    <tr wire:key="emp-{{ $emp->id }}"
                                        class="{{ !$emp->is_active ? 'opacity-50' : '' }}">
                                        <td class="fw-bold">{{ str_pad($emp->device_uid, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold">{{ $emp->name }}</span>
                                                @if($emp->is_blocked)
                                                    <span class="badge bg-warning text-dark" style="font-size:0.65rem;">Bị chặn</span>
                                                @elseif(!$emp->is_active)
                                                    <span class="badge bg-secondary" style="font-size:0.65rem;">Đã nghỉ</span>
                                                @endif
                                            </div>
                                            @if($emp->department)
                                                <div class="d-sm-none">
                                                    <span class="badge bg-label-primary px-2 py-1" style="font-size:0.7rem;">{{ $emp->department }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="d-none d-sm-table-cell">
                                            @if($emp->department)
                                                <span class="badge bg-label-primary px-2 py-1">{{ $emp->department }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center text-muted d-none d-md-table-cell">{{ $emp->created_at?->format('d/m/Y') }}</td>
                                        <td class="text-end">
                                            @if($emp->is_blocked)
                                                {{-- Bị chặn: chỉ cho bỏ chặn --}}
                                                <button class="btn btn-sm btn-icon btn-light text-warning rounded-pill me-1"
                                                        wire:click="unblock({{ $emp->id }})" title="Bỏ chặn">
                                                    <i class="bi bi-unlock"></i>
                                                </button>
                                            @elseif(!$emp->is_active)
                                                {{-- Đã nghỉ tự động: cho kích hoạt lại hoặc chặn hẳn --}}
                                                <button class="btn btn-sm btn-icon btn-light text-success rounded-pill me-1"
                                                        wire:click="reactivate({{ $emp->id }})" title="Kích hoạt lại">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                                <button class="btn btn-sm btn-icon btn-light text-danger rounded-pill"
                                                        wire:click="confirmBlock({{ $emp->id }})" title="Chặn import">
                                                    <i class="bi bi-slash-circle"></i>
                                                </button>
                                            @else
                                                {{-- Đang hoạt động: sửa + chặn --}}
                                                <button class="btn btn-sm btn-icon btn-light text-primary rounded-pill me-1"
                                                        wire:click="openEdit({{ $emp->id }})" title="Sửa">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-icon btn-light text-danger rounded-pill"
                                                        wire:click="confirmBlock({{ $emp->id }})" title="Chặn import">
                                                    <i class="bi bi-slash-circle"></i>
                                                </button>
                                            @endif
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

    {{-- Confirm Block --}}
    @if($confirmingBlock)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);"
             wire:click.self="$set('confirmingBlock', false)" wire:key="modal-confirm-block">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg overflow-hidden">
                    <div class="modal-header bg-warning py-3">
                        <h5 class="modal-title fw-bold">Chặn nhân viên này?</h5>
                        <button type="button" class="btn-close" wire:click="$set('confirmingBlock', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-1">Nhân viên sẽ bị <strong>bỏ qua trong mọi lần import</strong> tiếp theo.</p>
                        <p class="mb-0 text-danger" style="font-size:0.85rem;"><strong>Toàn bộ lịch sử chấm công sẽ bị xóa vĩnh viễn.</strong></p>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm"
                                wire:click="$set('confirmingBlock', false)">Hủy</button>
                        <button type="button" class="btn btn-warning btn-sm" wire:click="block"
                                wire:loading.attr="disabled">
                            <span wire:loading wire:target="block" class="spinner-border spinner-border-sm me-1"></span>
                            Chặn & Xóa dữ liệu
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Đồng bộ từ máy --}}
    @if($showSyncModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);"
             wire:click.self="$set('showSyncModal', false)">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg overflow-hidden">
                    <div class="modal-header bg-secondary py-3">
                        <h5 class="modal-title fw-bold text-white">Đồng bộ danh sách NV</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showSyncModal', false)"></button>
                    </div>
                    <form wire:submit="syncFromDevice">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">File user.dat <span class="text-danger">*</span></label>
                                <input type="file" wire:model="syncFile" class="form-control form-control-sm" accept=".dat,.txt,.csv">
                                <div class="form-text">File danh sách nhân viên xuất từ máy chấm công</div>
                                @error('syncFile') <div class="text-danger mt-1" style="font-size:0.85rem;">{{ $message }}</div> @enderror
                            </div>
                            <div class="alert alert-info py-2 mb-0" style="font-size:0.83rem;">
                                Nhân viên có trong file sẽ được thêm/kích hoạt. Nhân viên không có trong file sẽ bị đánh dấu <strong>đã nghỉ</strong>. Nhân viên bị chặn không bị ảnh hưởng.
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm"
                                    wire:click="$set('showSyncModal', false)">Hủy</button>
                            <button type="submit" class="btn btn-secondary btn-sm"
                                    wire:loading.attr="disabled" wire:target="syncFromDevice">
                                <span wire:loading wire:target="syncFromDevice" class="spinner-border spinner-border-sm me-1"></span>
                                Đồng bộ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
