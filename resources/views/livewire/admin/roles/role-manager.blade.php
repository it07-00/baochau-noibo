<div>
    @section('title', 'Vai trò và quyền')
    @section('page_title', 'Vai trò và quyền')

    <header class="mb-4">
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 mb-2">
                    <i class="fa-solid fa-shield-halved me-1"></i>Phân quyền hệ thống
                </span>
                <h4 class="fw-bold text-body mb-1">Quản lý Vai trò và Quyền hạn</h4>
                <p class="text-secondary mb-0">Kiểm soát danh mục vai trò và phạm vi phân quyền truy cập người dùng trong hệ thống.</p>
            </div>
            @can('roles.create')
                <a href="{{ route('app.roles.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm">
                    <i class="fa-solid fa-plus"></i>Tạo vai trò mới
                </a>
            @endcan
        </div>

        <div class="row g-3 mt-2">
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card border rounded-3 shadow-sm bg-body h-100">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary small fw-medium">Tổng vai trò</div>
                            <div class="h3 fw-bold text-body mb-0 mt-1">{{ number_format($totalRoles) }}</div>
                        </div>
                        <div class="rounded-3 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-user-shield fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card border rounded-3 shadow-sm bg-body h-100">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary small fw-medium">Quyền hạn hệ thống</div>
                            <div class="h3 fw-bold text-body mb-0 mt-1">{{ number_format($totalPermissions) }}</div>
                        </div>
                        <div class="rounded-3 bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-key fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card border rounded-3 shadow-sm bg-body h-100">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary small fw-medium">Người dùng đã gán</div>
                            <div class="h3 fw-bold text-body mb-0 mt-1">{{ number_format($totalUsers) }}</div>
                        </div>
                        <div class="rounded-3 bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-users fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    @if (session('status'))<div class="alert alert-success border-0 shadow-sm rounded-3 mb-3" role="alert"><i class="fa-solid fa-circle-check me-2"></i>{{ session('status') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger border-0 shadow-sm rounded-3 mb-3" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}</div>@endif

    <section class="card border rounded-3 shadow-sm overflow-hidden bg-body" aria-labelledby="roles-list-title">
        <div class="card-header bg-body border-bottom p-3">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md">
                    <h6 id="roles-list-title" class="fw-bold text-body mb-1">Danh sách vai trò</h6>
                    <p class="text-secondary small mb-0">Thống kê số lượng tài khoản và số quyền được phân gán cho từng vai trò.</p>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label for="role-search" class="visually-hidden">Tìm vai trò</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-tertiary border-end-0 text-secondary"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input id="role-search" wire:model.live.debounce.300ms="search" type="search" class="form-control bg-body-tertiary border-start-0 ps-0" placeholder="Tìm theo tên vai trò...">
                    </div>
                </div>
                <div class="col-auto">
                    <span wire:loading wire:target="search" class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="table-light text-secondary small">
                    <tr>
                        <th class="ps-3 py-3" style="width: 30%;">Tên vai trò / Nhãn hiển thị</th>
                        <th style="width: 25%;">Mã hệ thống</th>
                        <th style="width: 15%;">Tài khoản</th>
                        <th style="width: 15%;">Số quyền hạn</th>
                        @canany(['roles.edit', 'roles.delete'])
                            <th class="text-end pe-3" style="width: 15%;">Thao tác</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr wire:key="role-{{ $role->id }}">
                            <td class="ps-3 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center @if($role->name === 'it') bg-danger-subtle text-danger @elseif($role->name === 'giam-doc') bg-primary-subtle text-primary @elseif($role->name === 'tp-kinh-doanh') bg-info-subtle text-info @elseif($role->name === 'ke-toan') bg-warning-subtle text-warning @else bg-secondary-subtle text-secondary @endif" style="width: 40px; height: 40px;">
                                        <i class="fa-solid fa-shield-halved fs-6"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-body fs-6">{{ $role->display_name }}</div>
                                        <div class="small text-secondary">ID: #{{ $role->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code class="text-primary bg-body-tertiary border rounded px-2 py-1 small">{{ $role->name }}</code>
                            </td>
                            <td>
                                <span class="badge bg-body-tertiary text-body border px-2.5 py-1.5 rounded-2">
                                    <i class="fa-solid fa-users text-secondary me-1.5"></i>
                                    <span class="fw-bold">{{ number_format($role->users_count) }}</span> người
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1.5 rounded-2">
                                    <i class="fa-solid fa-key me-1.5"></i>
                                    <span class="fw-bold">{{ number_format($role->permissions_count) }}</span> quyền
                                </span>
                            </td>
                            @canany(['roles.edit', 'roles.delete'])
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex align-items-center gap-1.5">
                                        @can('roles.edit')
                                            <a href="{{ route('app.roles.edit', $role) }}" class="btn btn-outline-primary btn-sm rounded-2 d-inline-flex align-items-center gap-1">
                                                <i class="fa-solid fa-pen-to-square"></i> Sửa
                                            </a>
                                        @endcan
                                        @can('roles.delete')
                                            <button type="button" wire:click="deleteRole({{ $role->id }})" wire:confirm="Bạn có chắc chắn muốn xóa vai trò '{{ $role->display_name }}' ({{ $role->name }})?" class="btn btn-outline-danger btn-sm rounded-2 d-inline-flex align-items-center justify-content-center" @disabled($role->users_count > 0) title="{{ $role->users_count > 0 ? 'Không thể xóa vai trò đang có người dùng' : 'Xóa vai trò' }}">
                                                <i class="fa-solid fa-trash-can"></i>
                                                <span class="visually-hidden">Xóa</span>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            @endcanany
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fa-solid fa-shield-cat fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                                    <h6 class="fw-bold text-body mb-1">Không tìm thấy vai trò nào</h6>
                                    <p class="text-secondary small mb-0">Thử thay đổi từ khóa tìm kiếm hoặc bấm tạo vai trò mới.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roles->hasPages())
            <div class="card-footer bg-body border-top p-3">
                {{ $roles->links() }}
            </div>
        @endif
    </section>
</div>
