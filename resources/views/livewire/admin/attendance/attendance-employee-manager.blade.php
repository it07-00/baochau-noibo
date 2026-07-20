<div>
    @section('title', 'Quản lý nhân viên chấm công')
    @section('page_title', 'Nhân viên chấm công')

    <header class="mb-4">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
            <div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 mb-2"><i class="fa-solid fa-fingerprint me-1"></i>Máy chấm công</span>
                <h4 class="fw-bold text-body mb-1">Nhân viên chấm công</h4>
                <p class="text-secondary mb-0">Đối chiếu mã máy, phòng ban và trạng thái đồng bộ nhân viên.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('app.attendance.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"><i class="fa-solid fa-arrow-left"></i>Bảng chấm công</a>
                <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-2" wire:click="openSyncModal"><i class="fa-solid fa-rotate"></i>Đồng bộ file</button>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" wire:click="openCreate"><i class="fa-solid fa-user-plus"></i>Thêm nhân viên</button>
            </div>
        </div>
        <div class="d-flex align-items-center gap-4 mt-4 flex-wrap">
            <div><div class="h4 fw-bold text-body mb-0">{{ number_format($totalEmployees) }}</div><div class="small text-secondary">Tổng nhân viên</div></div>
            <div class="vr"></div>
            <div><div class="h4 fw-bold text-success mb-0">{{ number_format($activeEmployees) }}</div><div class="small text-secondary">Đang hoạt động</div></div>
            <div class="vr"></div>
            <div><div class="h4 fw-bold text-danger mb-0">{{ number_format($blockedEmployees) }}</div><div class="small text-secondary">Đã chặn</div></div>
        </div>
    </header>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border shadow-none overflow-hidden">
                <div class="card-header bg-body border-bottom p-3">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                        <div><h6 class="fw-bold text-body mb-1">Danh sách nhân viên</h6><p class="text-secondary small mb-0">{{ number_format($employees->total()) }} kết quả phù hợp</p></div>
                        <span wire:loading wire:target="search,filterDepartment,showInactive" class="text-primary small fw-semibold" role="status"><span class="spinner-border spinner-border-sm me-1"></span>Đang lọc</span>
                    </div>
                    <div class="row g-3 align-items-end">
                        {{-- Search --}}
                        <div class="col-12 col-lg-6">
                            <label for="attendance-employee-search" class="form-label small fw-semibold">Tìm kiếm</label>
                            <div class="input-group">
                            <span class="input-group-text bg-body-tertiary border-end-0">
                                <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                            </span>
                            <input id="attendance-employee-search" wire:model.live.debounce.300ms="search" type="search"
                                   class="form-control bg-body-tertiary border-start-0 ps-0"
                                   placeholder="Tên hoặc mã máy chấm công">
                            </div>
                        </div>

                        {{-- Department filter --}}
                        <div class="col-md-6 col-lg-3">
                            <label for="attendance-department" class="form-label small fw-semibold">Phòng ban</label>
                            <select id="attendance-department" wire:model.live="filterDepartment" class="form-select">
                            <option value="">Tất cả phòng ban</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                            </select>
                        </div>

                        {{-- Toggle đã nghỉ --}}
                        <div class="col-md-6 col-lg-3">
                        <div class="border rounded-3 px-3 py-2 d-flex align-items-center gap-2 bg-body-tertiary">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       wire:model.live="showInactive" id="toggleInactive">
                            </div>
                            <label class="form-check-label small" for="toggleInactive">Hiện nhân viên đã nghỉ/bị chặn</label>
                        </div>
                        </div>
                    </div>
                </div>

                {{-- Table --}}
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0 text-nowrap">
                            <thead class="table-light text-secondary small">
                                <tr>
                                    <th class="ps-3 py-3">Mã máy</th>
                                    <th class="py-3">Nhân viên</th>
                                    <th class="py-3">Phòng ban</th>
                                    <th class="py-3">Ngày tạo</th>
                                    <th class="pe-3 py-3 text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $emp)
                                    <tr wire:key="emp-{{ $emp->id }}"
                                        class="{{ !$emp->is_active ? 'opacity-75' : '' }}"
                                        >
                                        <td class="ps-3 py-3">
                                            <code class="bg-body-tertiary text-body border rounded px-2 py-1">#{{ $emp->device_uid }}</code>
                                        </td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span class="fw-semibold text-body">{{ $emp->name }}</span>
                                                @if($emp->is_blocked)
                                                    <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle">
                                                        <i class="fa-solid fa-ban me-1"></i>Bị chặn
                                                    </span>
                                                @elseif(!$emp->is_active)
                                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary border">Đã nghỉ</span>
                                                @endif
                                            </div>
                                            @if($emp->department)
                                                <div class="d-sm-none mt-1">
                                                    <span class="badge bg-label-primary px-2 fs-70" >{{ $emp->department }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            @if($emp->department)
                                                <span class="text-body">{{ $emp->department }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-secondary">
                                            {{ $emp->created_at?->format('d/m/Y') }}
                                        </td>
                                        <td class="pe-3 py-3 text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                @if($emp->is_blocked)
                                                    <button class="btn btn-sm btn-outline-warning"
                                                            wire:click="unblock({{ $emp->id }})"
                                                            title="Bỏ chặn" aria-label="Bỏ chặn {{ $emp->name }}">
                                                        <i class="fa-solid fa-unlock me-1"></i>Bỏ chặn
                                                    </button>
                                                @elseif(!$emp->is_active)
                                                    <button class="btn btn-sm btn-outline-success"
                                                            wire:click="reactivate({{ $emp->id }})"
                                                            title="Kích hoạt lại" aria-label="Kích hoạt lại {{ $emp->name }}">
                                                        <i class="fa-solid fa-arrows-rotate me-1"></i>Kích hoạt
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger"
                                                            wire:click="confirmBlock({{ $emp->id }})"
                                                            title="Chặn" aria-label="Chặn {{ $emp->name }}">
                                                        <i class="fa-solid fa-ban"></i><span class="visually-hidden">Chặn</span>
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline-primary"
                                                            wire:click="openEdit({{ $emp->id }})"
                                                            title="Sửa" aria-label="Sửa {{ $emp->name }}">
                                                        <i class="fa-solid fa-pen me-1"></i>Sửa
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger"
                                                            wire:click="confirmBlock({{ $emp->id }})"
                                                            title="Chặn" aria-label="Chặn {{ $emp->name }}">
                                                        <i class="fa-solid fa-ban"></i><span class="visually-hidden">Chặn</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-user-slash d-block mb-3 fs-2rem opacity-50"></i>
                                            <div class="fw-semibold text-body mb-1">Không tìm thấy nhân viên</div>
                                            <small>Thử đổi từ khóa, phòng ban hoặc bật hiển thị nhân viên đã nghỉ.</small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($employees->hasPages())
                    <div class="card-footer bg-body border-top px-3 py-3">
                        {{ $employees->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Thêm/Sửa --}}
    @if($showModal)
        <div class="modal fade show d-block overlay-bg" tabindex="-1"
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
        <div class="modal fade show d-block overlay-bg" tabindex="-1"
             wire:click.self="$set('confirmingBlock', false)" wire:key="modal-confirm-block">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg overflow-hidden">
                    <div class="modal-header bg-warning py-3">
                        <h5 class="modal-title fw-bold">Chặn nhân viên này?</h5>
                        <button type="button" class="btn-close" wire:click="$set('confirmingBlock', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-1">Nhân viên sẽ bị <strong>bỏ qua trong mọi lần import</strong> tiếp theo.</p>
                        <p class="mb-0 text-danger fs-85" ><strong>Toàn bộ lịch sử chấm công sẽ bị xóa vĩnh viễn.</strong></p>
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
        <div class="modal fade show d-block overlay-bg" tabindex="-1"
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
                                @error('syncFile') <div class="text-danger mt-1 fs-85" >{{ $message }}</div> @enderror
                            </div>
                            <div class="alert alert-info py-2 mb-0 fs-83" >
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
