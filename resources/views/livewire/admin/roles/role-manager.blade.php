<div>
    @section('title', 'Vai trò và Quyền')
    @section('page_title', 'Quản lý Vai trò')

    <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white p-3"><i class="fa-solid fa-shield-halved fs-5"></i></span>
            <div>
                <h4 class="fw-bold text-body mb-1">Vai trò và quyền</h4>
                <p class="text-secondary mb-0">Quản lý nhóm quyền và phạm vi truy cập của người dùng.</p>
            </div>
        </div>
        @can('roles.create')
            <a href="{{ route('app.roles.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2" wire:navigate><i class="fa-solid fa-plus"></i>Tạo vai trò</a>
        @endcan
    </div>

    @if (session('status'))
        <div class="alert alert-success mt-1 shadow-sm border-0">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mt-1 shadow-sm border-0">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header bg-body border-bottom p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h6 class="fw-bold text-body mb-1"><i class="fa-solid fa-list-check text-primary me-2"></i>Danh sách vai trò</h6>
                        <p class="text-secondary small mb-0">{{ number_format($totalRoles) }} vai trò trong hệ thống</p>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <!-- Ô tìm kiếm realtime -->
                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary">
                                <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="search" class="form-control" aria-label="Tìm vai trò" placeholder="Tìm vai trò...">
                        </div>
                        <div wire:loading wire:target="search" class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Đang tải</span></div>
                    </div>
                </div>

                <div class="card-body p-0 position-relative">
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Tên vai trò</th>
                                    <th>Số nhân viên</th>
                                    <th>Số quyền hạn</th>
                                    @canany(['roles.edit', 'roles.delete'])
                                    <th class="text-end">Hành động</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                <tr wire:key="role-{{ $role->id }}">
                                    <td>{{ $role->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-light-primary text-primary rounded me-3 d-flex align-items-center justify-content-center icon-36" >
                                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4a2.5 2.5 0 11-5 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $role->display_name }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info px-2 py-1"><i class="fs-7 me-1 text-info fas fa-users"></i> {{ $role->users_count }} users</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-warning px-2 py-1"><i class="fs-7 me-1 text-warning fas fa-key"></i> {{ $role->permissions_count }} permissions</span>
                                    </td>
                                    @canany(['roles.edit', 'roles.delete'])
                                    <td class="text-end">
                                        @can('roles.edit')
                                        <a href="{{ route('app.roles.edit', $role) }}" class="btn btn-sm btn-icon btn-light text-primary rounded-pill me-1" title="Sửa" wire:navigate>
                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        @endcan

                                        @can('roles.delete')
                                        <button
                                            @if($role->users_count > 0)
                                                onclick="Swal.fire('Không thể xóa', 'Vai trò này đang được gán cho {{ $role->users_count }} người dùng.', 'error')"
                                            @else
                                                wire:click="deleteRole({{ $role->id }})"
                                                wire:confirm="Bạn có chắc chắn muốn xóa vai trò {{ $role->name }}?"
                                            @endif
                                            class="btn btn-sm btn-icon btn-light text-danger rounded-pill" title="Xóa" {{ $role->users_count > 0 ? 'disabled' : '' }}>
                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        @endcan
                                    </td>
                                    @endcanany
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Không có vai trò nào khớp với tìm kiếm.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($roles->hasPages())
                <div class="card-footer bg-body border-top px-4 py-3">
                    {{ $roles->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
