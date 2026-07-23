@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa vai trò')
@section('page_title', 'Chỉnh sửa Vai trò: ' . $role->name)

@section('content')
    <form action="{{ route('app.roles.update', $role) }}" method="POST" id="roleEditForm">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Sticky Sidebar: Role Basic Info -->
            <div class="col-12 col-lg-4">
                <div class="card border border-secondary-subtle rounded-3 shadow-sm bg-body position-sticky" style="top: 80px;">
                    <div class="card-header bg-body-tertiary border-bottom p-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-primary-subtle text-primary p-2">
                                <i class="fa-solid fa-user-shield fs-5"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold text-body mb-0">Thông tin vai trò</h6>
                                <span class="small text-muted">Mã ID hệ thống: #{{ $role->id }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-body small mb-1">Tên định danh (Mã hệ thống) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-body-tertiary border-secondary-subtle fw-bold text-primary @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required placeholder="Ví dụ: kinh-doanh, ke-toan-vien">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted small mt-1">
                                <i class="fa-solid fa-circle-info me-1 text-primary"></i>Tên mã dùng phân quyền hệ thống.
                            </div>
                        </div>

                        <!-- Progress / Counter Box -->
                        <div class="p-3 bg-primary-subtle border border-primary-subtle rounded-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold text-primary">
                                    <i class="fa-solid fa-key me-1"></i>Đã chọn:
                                </span>
                                <span class="badge bg-primary text-white rounded-pill px-3 py-1.5 fw-bold" id="selectedPermCount">0</span>
                            </div>
                            <div class="progress bg-white" style="height: 6px;">
                                <div class="progress-bar bg-primary" id="selectedPermProgress" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 small text-primary fw-medium opacity-75">
                                <span>Tỷ lệ phân quyền</span>
                                <span id="selectedPermPercent">0%</span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-bold py-2 rounded-3 shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-1.5"></i>Lưu thay đổi
                            </button>
                            <a href="{{ route('app.roles.index') }}" class="btn btn-outline-secondary py-2 rounded-3">
                                <i class="fa-solid fa-arrow-left me-1.5"></i>Quay lại danh sách
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Permission Matrix -->
            <div class="col-12 col-lg-8">
                <!-- Search & Master Controls Card -->
                <div class="card border border-secondary-subtle rounded-3 shadow-sm bg-body mb-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                            <div>
                                <h6 class="fw-bold text-body mb-0">
                                    <i class="fa-solid fa-sliders text-primary me-2"></i>Ma trận phân quyền chi tiết
                                </h6>
                                <span class="text-muted small">Tích chọn các quyền hạn gán cho vai trò này</span>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input cursor-pointer" type="checkbox" id="checkAllMaster">
                                <label class="form-check-label fw-bold text-body small cursor-pointer" for="checkAllMaster">Chọn tất cả quyền</label>
                            </div>
                        </div>

                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary border-secondary-subtle text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" id="permSearchInput" class="form-control bg-body-tertiary border-secondary-subtle" placeholder="Lọc tìm quyền hạn (gõ tên quyền, module)...">
                            <span class="input-group-text bg-body-tertiary border-secondary-subtle text-muted small" id="searchMatchCount">Đang xem tất cả</span>
                        </div>
                    </div>
                </div>

                <!-- Module Cards -->
                <div id="modulesWrapper">
                    @foreach($permissions as $module => $modulePermissions)
                        <div class="card border border-secondary-subtle rounded-3 shadow-sm bg-body mb-3 module-card">
                            <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle p-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        <i class="{{ \App\Support\RolePermissionViewData::moduleIcon($module) }} me-1"></i>
                                        {{ count($modulePermissions) }} quyền
                                    </span>
                                    <h6 class="fw-bold text-body mb-0">
                                        {{ \App\Support\RolePermissionViewData::moduleName($module) }}
                                    </h6>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input module-toggle cursor-pointer" type="checkbox" id="mod_toggle_{{ $module }}">
                                    <label class="form-check-label small text-muted cursor-pointer" for="mod_toggle_{{ $module }}">Chọn phân hệ</label>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2.5">
                                    @foreach($modulePermissions as $permission)
                                        @php
                                            $checked = (is_array(old('permissions')) && in_array($permission->name, old('permissions')))
                                                || (!old('permissions') && in_array($permission->name, $rolePermissions));
                                        @endphp
                                        <div class="col-12 col-sm-6 col-md-4 perm-item-col">
                                            <label class="d-block h-100 cursor-pointer user-select-none">
                                                <div class="perm-tile p-2.5 rounded-3 border transition-all h-100 d-flex align-items-start gap-2 {{ $checked ? 'bg-primary-subtle border-primary text-primary shadow-sm' : 'bg-body-tertiary border-secondary-subtle text-body' }}">
                                                    <input class="form-check-input perm-checkbox mt-1 me-0 flex-shrink-0 cursor-pointer"
                                                           type="checkbox"
                                                           name="permissions[]"
                                                           value="{{ $permission->name }}"
                                                           {{ $checked ? 'checked' : '' }}>
                                                    <div class="flex-grow-1 min-w-0">
                                                        <div class="fw-bold small lh-sm">
                                                            {{ \App\Support\RolePermissionViewData::actionLabel($permission->name) }}
                                                        </div>
                                                        <div class="text-muted font-monospace opacity-75 mt-0.5" style="font-size: 0.72rem;">
                                                            {{ $permission->name }}
                                                        </div>
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
            const searchInput = document.getElementById('permSearchInput');
            const searchMatchCount = document.getElementById('searchMatchCount');
            const selectedPermCount = document.getElementById('selectedPermCount');
            const selectedPermProgress = document.getElementById('selectedPermProgress');
            const selectedPermPercent = document.getElementById('selectedPermPercent');

            const permCheckboxes = Array.from(document.querySelectorAll('.perm-checkbox'));
            const totalPerms = permCheckboxes.length;

            function updateTileStyle(cb) {
                const tile = cb.closest('.perm-tile');
                if (!tile) return;
                if (cb.checked) {
                    tile.classList.remove('bg-body-tertiary', 'border-secondary-subtle', 'text-body');
                    tile.classList.add('bg-primary-subtle', 'border-primary', 'text-primary', 'shadow-sm');
                } else {
                    tile.classList.remove('bg-primary-subtle', 'border-primary', 'text-primary', 'shadow-sm');
                    tile.classList.add('bg-body-tertiary', 'border-secondary-subtle', 'text-body');
                }
            }

            function updateSummary() {
                const checkedCount = permCheckboxes.filter(cb => cb.checked).length;
                if (selectedPermCount) selectedPermCount.textContent = checkedCount;

                const percent = totalPerms > 0 ? Math.round((checkedCount / totalPerms) * 100) : 0;
                if (selectedPermProgress) selectedPermProgress.style.width = percent + '%';
                if (selectedPermPercent) selectedPermPercent.textContent = percent + '%';

                // Sync module toggles
                document.querySelectorAll('.module-card').forEach(modCard => {
                    const modCbs = Array.from(modCard.querySelectorAll('.perm-checkbox'));
                    const modToggle = modCard.querySelector('.module-toggle');
                    if (modToggle && modCbs.length > 0) {
                        modToggle.checked = modCbs.every(cb => cb.checked);
                    }
                });

                // Sync master toggle
                if (masterCheck && totalPerms > 0) {
                    masterCheck.checked = permCheckboxes.every(cb => cb.checked);
                }
            }

            // Checkbox change events
            permCheckboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    updateTileStyle(this);
                    updateSummary();
                });
            });

            // Module Toggles
            document.querySelectorAll('.module-toggle').forEach(modToggle => {
                modToggle.addEventListener('change', function () {
                    const modCard = this.closest('.module-card');
                    if (!modCard) return;
                    const modCbs = modCard.querySelectorAll('.perm-checkbox');
                    modCbs.forEach(cb => {
                        cb.checked = this.checked;
                        updateTileStyle(cb);
                    });
                    updateSummary();
                });
            });

            // Master Checkbox
            if (masterCheck) {
                masterCheck.addEventListener('change', function () {
                    const isChecked = this.checked;
                    permCheckboxes.forEach(cb => {
                        cb.checked = isChecked;
                        updateTileStyle(cb);
                    });
                    document.querySelectorAll('.module-toggle').forEach(mt => mt.checked = isChecked);
                    updateSummary();
                });
            }

            // Search Filter
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    let matchCount = 0;

                    document.querySelectorAll('.module-card').forEach(modCard => {
                        let hasVisibleTile = false;
                        const itemCols = modCard.querySelectorAll('.perm-item-col');

                        itemCols.forEach(col => {
                            const text = col.textContent.toLowerCase();
                            if (query === '' || text.includes(query)) {
                                col.style.display = '';
                                hasVisibleTile = true;
                                matchCount++;
                            } else {
                                col.style.display = 'none';
                            }
                        });

                        modCard.style.display = hasVisibleTile ? '' : 'none';
                    });

                    if (searchMatchCount) {
                        searchMatchCount.textContent = query === ''
                            ? 'Đang xem tất cả (' + totalPerms + ' quyền)'
                            : 'Khớp ' + matchCount + ' / ' + totalPerms + ' quyền';
                    }
                });
            }

            // Initial sync
            permCheckboxes.forEach(cb => updateTileStyle(cb));
            updateSummary();
        });
    </script>
    @endpush
@endsection
