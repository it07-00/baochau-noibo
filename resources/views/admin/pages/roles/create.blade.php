@extends('admin.layouts.app')

@section('title', 'Thêm vai trò mới')
@section('page_title', 'Tạo vai trò mới')

@section('content')
    <form action="{{ route('app.roles.store') }}" method="POST" id="roleCreateForm">
        @csrf

        <div class="row g-4">
            <!-- Left Sticky Sidebar: Role Basic Info -->
            <div class="col-12 col-lg-4">
                <div class="card border border-secondary-subtle rounded-3 shadow-sm bg-body position-sticky"
                    style="top: 80px;">
                    <div class="card-header bg-body-tertiary border-bottom p-3">
                        <div class="d-flex align-items-center gap-2.5">
                            <span
                                class="d-inline-flex align-items-center justify-content-center rounded-2 bg-primary-subtle text-primary p-2">
                                <i class="fa-solid fa-plus-circle fs-5"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold text-body mb-0">Tạo vai trò mới</h6>
                                <span class="small text-muted">Khai báo mã vai trò và cấp quyền</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3.5">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body small mb-1.5">Tên định danh (Mã hệ thống) <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" id="role_name_input"
                                class="form-control form-control-lg bg-body-tertiary border-secondary-subtle fw-bold fs-6 text-primary @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required placeholder="Ví dụ: kinh-doanh, ke-toan-vien">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted small mt-2">
                                <i class="fa-solid fa-circle-info me-1 text-primary"></i>Tên mã dùng trong phân quyền ứng
                                dụng (VD: <code>tp-ky-thuat</code>).
                            </div>
                        </div>

                        <!-- Selected Counter Progress Box -->
                        <div class="p-3 bg-primary-subtle border border-primary-subtle rounded-3 mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold text-primary">
                                    <i class="fa-solid fa-key me-1"></i>Số quyền đã chọn:
                                </span>
                                <span class="badge bg-primary text-white rounded-pill px-3 py-1.5 fw-bold fs-6"
                                    id="selectedPermCount">0</span>
                            </div>
                            <div class="progress bg-white" style="height: 6px;">
                                <div class="progress-bar bg-primary" id="selectedPermProgress" role="progressbar"
                                    style="width: 0%;"></div>
                            </div>
                            <div
                                class="d-flex justify-content-between align-items-center mt-2 small text-primary fw-medium opacity-75">
                                <span>Tiến độ phân quyền</span>
                                <span id="selectedPermPercent">0%</span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit"
                                class="btn btn-primary py-2.5 fw-bold rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i>Tạo mới vai trò
                            </button>
                            <a href="{{ route('app.roles.index') }}"
                                class="btn btn-outline-secondary py-2.5 rounded-3 d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-solid fa-arrow-left"></i>Quay lại danh sách
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Permission Matrix -->
            <div class="col-12 col-lg-8">
                <!-- Search Box & Master Controls Header -->
                <div class="card border border-secondary-subtle rounded-3 shadow-sm bg-body mb-4">
                    <div class="card-body p-3.5">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                            <div>
                                <h5 class="fw-bold text-body mb-1">
                                    <i class="fa-solid fa-sliders text-primary me-2"></i>Ma trận phân quyền chi tiết
                                </h5>
                                <p class="text-muted small mb-0">Bật/tắt các quyền truy cập để thiết lập phạm vi thao tác
                                    cho vai trò mới.</p>
                            </div>
                            <div
                                class="form-check form-switch m-0 bg-body-tertiary border border-secondary-subtle rounded-pill px-3 py-2">
                                <input class="form-check-input cursor-pointer me-2 ms-0" type="checkbox"
                                    id="checkAllMaster">
                                <label class="form-check-label fw-bold text-body small cursor-pointer"
                                    for="checkAllMaster">Chọn tất cả quyền</label>
                            </div>
                        </div>

                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-body-tertiary border-secondary-subtle text-muted ps-3"><i
                                    class="fa-solid fa-magnifying-glass fs-6"></i></span>
                            <input type="search" id="permissionSearchInput"
                                class="form-control bg-body-tertiary border-secondary-subtle border-start-0 ps-1 fs-6"
                                placeholder="Lọc nhanh quyền hạn (gõ: người dùng, báo giá, xem, sửa, xóa)...">
                            <span class="input-group-text bg-body-tertiary border-secondary-subtle text-muted small pe-3"
                                id="visiblePermCountText">Đang xem tất cả</span>
                        </div>
                    </div>
                </div>

                <!-- Module Bento Cards -->
                <div id="permissionModulesContainer">
                    @foreach($permissions as $module => $modulePermissions)
                        <div class="card border border-secondary-subtle rounded-3 shadow-sm bg-body mb-4 module-group overflow-hidden"
                            data-module="{{ \Illuminate\Support\Str::lower($module) }} {{ \Illuminate\Support\Str::lower(\App\Support\RolePermissionViewData::moduleName($module)) }}">
                            <div
                                class="card-header bg-body-tertiary border-bottom border-secondary-subtle p-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2.5">
                                    <span
                                        class="d-inline-flex align-items-center justify-content-center rounded-2 bg-primary-subtle text-primary px-2.5 py-1.5">
                                        <i class="{{ \App\Support\RolePermissionViewData::moduleIcon($module) }} me-1.5"></i>
                                        <span class="fw-bold small">{{ count($modulePermissions) }} quyền</span>
                                    </span>
                                    <h6 class="fw-bold text-body mb-0 fs-6">
                                        {{ \App\Support\RolePermissionViewData::moduleName($module) }}
                                    </h6>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input module-check-all cursor-pointer me-1.5" type="checkbox"
                                        id="mod_all_{{ $module }}">
                                    <label class="form-check-label small text-muted fw-bold cursor-pointer"
                                        for="mod_all_{{ $module }}">Chọn phân hệ</label>
                                </div>
                            </div>

                            <div class="card-body p-3.5 bg-body">
                                <div class="row g-3">
                                    @foreach($modulePermissions as $permission)
                                        @php
                                            $isChecked = is_array(old('permissions')) && in_array($permission->name, old('permissions'));
                                        @endphp
                                        <div class="col-12 col-sm-6 col-md-4 perm-item"
                                            data-perm-name="{{ \Illuminate\Support\Str::lower($permission->name) }}"
                                            data-perm-label="{{ \Illuminate\Support\Str::lower(\App\Support\RolePermissionViewData::actionLabel($permission->name)) }}">
                                            <label for="perm_{{ $permission->id }}"
                                                class="perm-card-label w-100 h-100 cursor-pointer user-select-none">
                                                <div
                                                    class="perm-card-inner p-3 rounded-3 border transition-all h-100 d-flex align-items-start gap-2.5 {{ $isChecked ? 'bg-primary-subtle border-primary text-primary shadow-sm' : 'bg-body-tertiary border-secondary-subtle text-body' }}">
                                                    <input
                                                        class="form-check-input perm-check mt-1 ms-0 flex-shrink-0 cursor-pointer"
                                                        type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                                        id="perm_{{ $permission->id }}" {{ $isChecked ? 'checked' : '' }}>
                                                    <div class="flex-grow-1 min-w-0">
                                                        <div class="fw-bold small lh-sm text-truncate-2">
                                                            {{ \App\Support\RolePermissionViewData::actionLabel($permission->name) }}
                                                        </div>
                                                        <span class="d-block text-muted font-monospace opacity-75 mt-1"
                                                            style="font-size: 0.72rem; font-family: var(--bs-font-monospace);">
                                                            {{ $permission->name }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const masterCheck = document.getElementById('checkAllMaster');
                const permChecks = document.querySelectorAll('.perm-check');
                const moduleChecks = document.querySelectorAll('.module-check-all');
                const searchInput = document.getElementById('permissionSearchInput');
                const selectedCountBadge = document.getElementById('selectedPermCount');
                const selectedPermProgress = document.getElementById('selectedPermProgress');
                const selectedPermPercent = document.getElementById('selectedPermPercent');
                const visiblePermCountText = document.getElementById('visiblePermCountText');
                const totalPermsInSystem = permChecks.length;

                function updateCardStyle(checkbox) {
                    const cardInner = checkbox.closest('.perm-card-inner');
                    if (cardInner) {
                        if (checkbox.checked) {
                            cardInner.classList.remove('bg-body-tertiary', 'border-secondary-subtle', 'text-body');
                            cardInner.classList.add('bg-primary-subtle', 'border-primary', 'text-primary', 'shadow-sm');
                        } else {
                            cardInner.classList.remove('bg-primary-subtle', 'border-primary', 'text-primary', 'shadow-sm');
                            cardInner.classList.add('bg-body-tertiary', 'border-secondary-subtle', 'text-body');
                        }
                    }
                }

                function updateCount() {
                    const checkedCount = document.querySelectorAll('.perm-check:checked').length;
                    if (selectedCountBadge) {
                        selectedCountBadge.textContent = checkedCount;
                    }

                    const percent = totalPermsInSystem > 0 ? Math.round((checkedCount / totalPermsInSystem) * 100) : 0;
                    if (selectedPermProgress) {
                        selectedPermProgress.style.width = percent + '%';
                    }
                    if (selectedPermPercent) {
                        selectedPermPercent.textContent = percent + '%';
                    }

                    // Update module checks status
                    document.querySelectorAll('.module-group').forEach(group => {
                        const groupPerms = group.querySelectorAll('.perm-check');
                        const groupChecked = group.querySelectorAll('.perm-check:checked');
                        const modCheck = group.querySelector('.module-check-all');
                        if (modCheck && groupPerms.length > 0) {
                            modCheck.checked = (groupPerms.length === groupChecked.length);
                        }
                    });

                    // Update master check
                    if (masterCheck && permChecks.length > 0) {
                        masterCheck.checked = (permChecks.length === document.querySelectorAll('.perm-check:checked').length);
                    }
                }

                // Master Check All
                if (masterCheck) {
                    masterCheck.addEventListener('change', function () {
                        permChecks.forEach(cb => {
                            cb.checked = this.checked;
                            updateCardStyle(cb);
                        });
                        moduleChecks.forEach(cb => { cb.checked = this.checked; });
                        updateCount();
                    });
                }

                // Module Check All
                moduleChecks.forEach(modCheck => {
                    modCheck.addEventListener('change', function () {
                        const group = this.closest('.module-group');
                        if (group) {
                            const groupPerms = group.querySelectorAll('.perm-check');
                            groupPerms.forEach(cb => {
                                cb.checked = this.checked;
                                updateCardStyle(cb);
                            });
                            updateCount();
                        }
                    });
                });

                // Individual Check Item
                permChecks.forEach(cb => {
                    cb.addEventListener('change', function () {
                        updateCardStyle(this);
                        updateCount();
                    });
                });

                // Search filter
                if (searchInput) {
                    searchInput.addEventListener('input', function () {
                        const q = this.value.toLowerCase().trim();
                        let visibleCount = 0;

                        document.querySelectorAll('.module-group').forEach(group => {
                            const moduleData = group.getAttribute('data-module') || '';
                            let hasVisiblePerm = false;

                            group.querySelectorAll('.perm-item').forEach(item => {
                                const pName = item.getAttribute('data-perm-name') || '';
                                const pLabel = item.getAttribute('data-perm-label') || '';

                                if (q === '' || pName.includes(q) || pLabel.includes(q) || moduleData.includes(q)) {
                                    item.style.display = '';
                                    hasVisiblePerm = true;
                                    visibleCount++;
                                } else {
                                    item.style.display = 'none';
                                }
                            });

                            group.style.display = hasVisiblePerm ? '' : 'none';
                        });

                        if (visiblePermCountText) {
                            visiblePermCountText.textContent = q === ''
                                ? 'Đang xem tất cả (' + totalPermsInSystem + ' quyền)'
                                : 'Khớp ' + visibleCount + ' / ' + totalPermsInSystem + ' quyền';
                        }
                    });
                }

                // Initial counter & card styles sync
                permChecks.forEach(cb => updateCardStyle(cb));
                updateCount();
            });
        </script>
    @endpush
@endsection