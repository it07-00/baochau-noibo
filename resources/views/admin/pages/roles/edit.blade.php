@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa vai trò')
@section('page_title', 'Chỉnh sửa Vai trò: ' . $role->name)

@section('content')
    <form action="{{ route('app.roles.update', $role) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Sticky Sidebar: Role Basic Info -->
            <div class="col-12 col-lg-4">
                <div class="card border rounded-3 shadow-sm bg-body position-sticky" style="top: 80px;">
                    <div class="card-header bg-body border-bottom p-3">
                        <h6 class="fw-bold text-body mb-0">
                            <i class="fa-solid fa-id-card text-primary me-2"></i>Thông tin vai trò
                        </h6>
                    </div>
                    <div class="card-body p-3.5">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-body">Tên định danh (Mã hệ thống) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-body-tertiary @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required placeholder="Ví dụ: kinh-doanh, ke-toan-vien">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small text-secondary mt-2">
                                <i class="fa-solid fa-circle-info me-1"></i>Thay đổi mã vai trò có thể ảnh hưởng tới cấu hình phân quyền hệ thống.
                            </div>
                        </div>

                        <!-- Selected Counter Summary -->
                        <div class="p-3 bg-body-tertiary border rounded-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small fw-semibold text-secondary">Quyền đã gán:</span>
                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 fw-bold fs-6" id="selectedPermCount">0</span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2 fw-semibold rounded-3 shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-1"></i>Lưu thay đổi
                            </button>
                            <a href="{{ route('app.roles.index') }}" class="btn btn-outline-secondary py-2 rounded-3">
                                <i class="fa-solid fa-arrow-left me-1"></i>Hủy bỏ
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Permission Matrix -->
            <div class="col-12 col-lg-8">
                <div class="card border rounded-3 shadow-sm bg-body mb-3">
                    <div class="card-header bg-body border-bottom p-3">
                        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                            <div>
                                <h6 class="fw-bold text-body mb-0">
                                    <i class="fa-solid fa-key text-primary me-2"></i>Phân quyền chi tiết
                                </h6>
                                <span class="small text-secondary">Tích chọn hoặc bỏ chọn các quyền của vai trò này</span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="checkAllMaster">
                                    <label class="form-check-label fw-semibold text-body small" for="checkAllMaster">Chọn tất cả</label>
                                </div>
                            </div>
                        </div>
                        <!-- Search Box for Permissions -->
                        <div class="mt-3">
                            <div class="input-group">
                                <span class="input-group-text bg-body-tertiary border-end-0 text-secondary"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="search" id="permissionSearchInput" class="form-control bg-body-tertiary border-start-0 ps-0" placeholder="Lọc nhanh danh mục hoặc tên quyền hạn...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Module Cards -->
                <div id="permissionModulesContainer">
                    @foreach($permissions as $module => $modulePermissions)
                        <div class="card border rounded-3 shadow-sm bg-body mb-3 module-card" data-module="{{ strtolower($module) }} {{ strtolower(\App\Support\RolePermissionViewData::moduleName($module)) }}">
                            <div class="card-header bg-body-tertiary border-bottom p-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-2 px-2 py-1">
                                        <i class="fa-solid fa-folder me-1"></i>{{ count($modulePermissions) }} quyền
                                    </span>
                                    <h6 class="fw-bold text-body mb-0 module-title">
                                        {{ \App\Support\RolePermissionViewData::moduleName($module) }}
                                    </h6>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input module-check-all" type="checkbox" id="mod_all_{{ $module }}">
                                    <label class="form-check-label small text-secondary fw-medium" for="mod_all_{{ $module }}">Chọn phân hệ</label>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2.5">
                                    @foreach($modulePermissions as $permission)
                                        @php
                                            $isChecked = (is_array(old('permissions')) && in_array($permission->name, old('permissions')))
                                                || (!old('permissions') && in_array($permission->name, $rolePermissions));
                                        @endphp
                                        <div class="col-12 col-sm-6 col-md-4 perm-item" data-perm-name="{{ strtolower($permission->name) }}" data-perm-label="{{ strtolower(\App\Support\RolePermissionViewData::actionLabel($permission->name)) }}">
                                            <div class="form-check p-2.5 border rounded-2 bg-body hover-bg-light position-relative h-100 d-flex align-items-start gap-2">
                                                <input class="form-check-input perm-check mt-1 ms-0 flex-shrink-0" type="checkbox"
                                                    name="permissions[]"
                                                    value="{{ $permission->name }}"
                                                    id="perm_{{ $permission->id }}"
                                                    {{ $isChecked ? 'checked' : '' }}>
                                                <label class="form-check-label w-100 cursor-pointer" for="perm_{{ $permission->id }}">
                                                    <div class="fw-semibold text-body small lh-sm">
                                                        {{ \App\Support\RolePermissionViewData::actionLabel($permission->name) }}
                                                    </div>
                                                    <code class="d-block text-secondary opacity-75 small mt-1" style="font-size: 0.725rem;">{{ $permission->name }}</code>
                                                </label>
                                            </div>
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

            function updateCount() {
                const checkedCount = document.querySelectorAll('.perm-check:checked').length;
                if (selectedCountBadge) {
                    selectedCountBadge.textContent = checkedCount;
                }

                // Update module checks status
                document.querySelectorAll('.module-card').forEach(card => {
                    const cardPerms = card.querySelectorAll('.perm-check');
                    const cardChecked = card.querySelectorAll('.perm-check:checked');
                    const modCheck = card.querySelector('.module-check-all');
                    if (modCheck && cardPerms.length > 0) {
                        modCheck.checked = (cardPerms.length === cardChecked.length);
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
                    permChecks.forEach(cb => { cb.checked = this.checked; });
                    moduleChecks.forEach(cb => { cb.checked = this.checked; });
                    updateCount();
                });
            }

            // Module Check All
            moduleChecks.forEach(modCheck => {
                modCheck.addEventListener('change', function () {
                    const card = this.closest('.module-card');
                    if (card) {
                        const cardPerms = card.querySelectorAll('.perm-check');
                        cardPerms.forEach(cb => { cb.checked = this.checked; });
                        updateCount();
                    }
                });
            });

            // Individual Check Item
            permChecks.forEach(cb => {
                cb.addEventListener('change', updateCount);
            });

            // Search filter
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const q = this.value.toLowerCase().trim();
                    document.querySelectorAll('.module-card').forEach(card => {
                        const moduleData = card.getAttribute('data-module') || '';
                        let hasVisiblePerm = false;

                        card.querySelectorAll('.perm-item').forEach(item => {
                            const pName = item.getAttribute('data-perm-name') || '';
                            const pLabel = item.getAttribute('data-perm-label') || '';

                            if (q === '' || pName.includes(q) || pLabel.includes(q) || moduleData.includes(q)) {
                                item.style.display = '';
                                hasVisiblePerm = true;
                            } else {
                                item.style.display = 'none';
                            }
                        });

                        card.style.display = hasVisiblePerm ? '' : 'none';
                    });
                });
            }

            // Initial counter sync
            updateCount();
        });
    </script>
    @endpush
@endsection
