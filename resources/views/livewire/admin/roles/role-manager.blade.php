<div>
    @section('title', 'Vai trò và Quyền')
    @section('page_title', 'Quản lý Vai trò')

    @php
        $breadcrumbs = [
            ['label' => 'Quản trị', 'url' => route('app.dashboard')],
            ['label' => 'Vai trò']
        ];
    @endphp

    @if (session('status'))
        <div class="alert alert-success mt-1 shadow-sm border-0">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mt-1 shadow-sm border-0">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom">
                <div class="pure-card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h3 class="pure-card-title m-0">Danh sách vai trò</h3>

                    <div class="d-flex align-items-center gap-2">
                        <!-- Ô tìm kiếm realtime -->
                        <div class="input-group input-group-sm w-250px" >
                            <span class="input-group-text bg-transparent border-end-0">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 ps-0" placeholder="Tìm kiếm vai trò...">
                        </div>

                        <a href="{{ route('app.roles.create') }}" class="btn btn-primary btn-sm" wire:navigate>Tạo mới</a>
                    </div>
                </div>

                <div class="pure-card-body pb-3 position-relative">
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">ID</th>
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
                <div class="pure-card-footer border-top px-4 py-3">
                    {{ $roles->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
