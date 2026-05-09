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
                {{-- Header --}}
                <div class="pure-card-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3">
                    <h3 class="pure-card-title m-0 text-nowrap">Nhân viên chấm công</h3>

                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 w-100" style="max-width:860px;">
                        {{-- Search --}}
                        <div class="input-group flex-grow-1" style="min-width:180px;">
                            <span class="input-group-text bg-transparent border-end-0 pe-1">
                                <i class="bi bi-search text-muted" style="font-size:0.85rem;"></i>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                   class="form-control border-start-0 ps-1"
                                   placeholder="Tìm tên hoặc mã...">
                        </div>

                        {{-- Department filter --}}
                        <select wire:model.live="filterDepartment" class="form-select" style="min-width:160px;max-width:220px;">
                            <option value="">Tất cả phòng ban</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>

                        {{-- Toggle đã nghỉ --}}
                        <div class="d-flex align-items-center gap-2 text-nowrap px-1">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       wire:model.live="showInactive" id="toggleInactive"
                                       style="cursor:pointer; width:2.2em; height:1.1em;">
                            </div>
                            <label class="form-check-label text-muted" for="toggleInactive"
                                   style="font-size:0.83rem; cursor:pointer;">Hiện đã nghỉ</label>
                        </div>

                        {{-- Buttons --}}
                        <div class="d-flex gap-2 flex-shrink-0">
                            <button class="btn btn-outline-secondary text-nowrap" wire:click="openSyncModal">
                                <i class="bi bi-arrow-repeat me-1"></i>Đồng bộ
                            </button>
                            <button class="btn btn-primary text-nowrap" wire:click="openCreate">
                                <i class="bi bi-plus-circle me-1"></i>Thêm mới
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Table --}}
                <div class="pure-card-body pb-2 px-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0" style="font-size:0.92rem;">
                            <thead>
                                <tr style="background:var(--bs-light,#f8f9fa); border-bottom:2px solid #e9ecef;">
                                    <th class="px-4 py-3 text-muted fw-semibold" style="width:80px; font-size:0.8rem; letter-spacing:.03em;">MÃ MÁY</th>
                                    <th class="py-3 text-muted fw-semibold" style="font-size:0.8rem; letter-spacing:.03em;">HỌ VÀ TÊN</th>
                                    <th class="py-3 text-muted fw-semibold d-none d-sm-table-cell" style="width:180px; font-size:0.8rem; letter-spacing:.03em;">PHÒNG BAN</th>
                                    <th class="py-3 text-muted fw-semibold text-center d-none d-md-table-cell" style="width:120px; font-size:0.8rem; letter-spacing:.03em;">NGÀY TẠO</th>
                                    <th class="pe-4 py-3 text-end" style="width:110px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $emp)
                                    <tr wire:key="emp-{{ $emp->id }}"
                                        class="{{ !$emp->is_active ? 'opacity-50' : '' }}"
                                        style="border-bottom:1px solid #f0f0f0;">
                                        <td class="px-4 py-3">
                                            <span class="fw-semibold text-muted" style="font-family:monospace; font-size:0.95rem; letter-spacing:.05em;">
                                                {{ $emp->device_uid }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span class="fw-semibold">{{ $emp->name }}</span>
                                                @if($emp->is_blocked)
                                                    <span class="badge rounded-pill text-bg-warning" style="font-size:0.68rem; font-weight:600;">
                                                        <i class="bi bi-slash-circle me-1" style="font-size:0.6rem;"></i>Bị chặn
                                                    </span>
                                                @elseif(!$emp->is_active)
                                                    <span class="badge rounded-pill text-bg-secondary" style="font-size:0.68rem; font-weight:600;">Đã nghỉ</span>
                                                @endif
                                            </div>
                                            @if($emp->department)
                                                <div class="d-sm-none mt-1">
                                                    <span class="badge bg-label-primary px-2" style="font-size:0.7rem;">{{ $emp->department }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3 d-none d-sm-table-cell">
                                            @if($emp->department)
                                                <span class="badge bg-label-primary px-2 py-1" style="font-size:0.78rem;">{{ $emp->department }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-center text-muted d-none d-md-table-cell" style="font-size:0.85rem;">
                                            {{ $emp->created_at?->format('d/m/Y') }}
                                        </td>
                                        <td class="pe-4 py-3 text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                @if($emp->is_blocked)
                                                    <button class="btn btn-sm btn-icon btn-light text-warning rounded-circle"
                                                            wire:click="unblock({{ $emp->id }})"
                                                            title="Bỏ chặn" style="width:32px;height:32px;">
                                                        <i class="bi bi-unlock" style="font-size:0.85rem;"></i>
                                                    </button>
                                                @elseif(!$emp->is_active)
                                                    <button class="btn btn-sm btn-icon btn-light text-success rounded-circle"
                                                            wire:click="reactivate({{ $emp->id }})"
                                                            title="Kích hoạt lại" style="width:32px;height:32px;">
                                                        <i class="bi bi-arrow-repeat" style="font-size:0.85rem;"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-icon btn-light text-danger rounded-circle"
                                                            wire:click="confirmBlock({{ $emp->id }})"
                                                            title="Chặn" style="width:32px;height:32px;">
                                                        <i class="bi bi-slash-circle" style="font-size:0.85rem;"></i>
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-icon btn-light text-primary rounded-circle"
                                                            wire:click="openEdit({{ $emp->id }})"
                                                            title="Sửa" style="width:32px;height:32px;">
                                                        <i class="bi bi-pencil" style="font-size:0.85rem;"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-icon btn-light text-danger rounded-circle"
                                                            wire:click="confirmBlock({{ $emp->id }})"
                                                            title="Chặn" style="width:32px;height:32px;">
                                                        <i class="bi bi-slash-circle" style="font-size:0.85rem;"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-people d-block mb-2" style="font-size:2rem; opacity:.3;"></i>
                                            Không có nhân viên nào.
                                        </td>
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
                                        <input type="text" class="form-control" value="{{ $editDeviceUid }}" disabled>
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
