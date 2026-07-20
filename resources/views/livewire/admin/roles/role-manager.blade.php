<div>
    @section('title', 'Vai trò và quyền')
    @section('page_title', 'Vai trò và quyền')

    <header class="mb-4">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
            <div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 mb-2"><i class="fa-solid fa-shield-halved me-1"></i>Phân quyền</span>
                <h4 class="fw-bold text-body mb-1">Vai trò và quyền</h4>
                <p class="text-secondary mb-0">Kiểm soát phạm vi truy cập theo từng nhóm người dùng.</p>
            </div>
            @can('roles.create')
                <a href="{{ route('app.roles.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3" wire:navigate><i class="fa-solid fa-plus"></i>Tạo vai trò</a>
            @endcan
        </div>
        <div class="d-flex align-items-end gap-2 mt-4">
            <div class="h4 fw-bold text-body mb-0">{{ number_format($totalRoles) }}</div>
            <div class="small text-secondary pb-1">vai trò trong hệ thống</div>
        </div>
    </header>

    @if (session('status'))<div class="alert alert-success" role="alert">{{ session('status') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif

    <section class="card border shadow-none overflow-hidden" aria-labelledby="roles-list-title">
        <div class="card-header bg-body p-3 border-bottom">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md">
                    <h6 id="roles-list-title" class="fw-bold text-body mb-1">Danh sách vai trò</h6>
                    <p class="text-secondary small mb-0">Số người dùng và quyền đang được gán cho từng vai trò.</p>
                </div>
                <div class="col-12 col-md-5 col-xl-4">
                    <label for="role-search" class="visually-hidden">Tìm vai trò</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-tertiary border-end-0"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                        <input id="role-search" wire:model.live.debounce.300ms="search" type="search" class="form-control bg-body-tertiary border-start-0 ps-0" placeholder="Tìm theo tên vai trò...">
                    </div>
                </div>
                <div class="col-auto"><span wire:loading wire:target="search" class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Đang tải</span></span></div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="table-light text-secondary small">
                    <tr>
                        <th class="ps-3">Vai trò</th>
                        <th>Người dùng</th>
                        <th>Quyền hạn</th>
                        <th>Mã hệ thống</th>
                        @canany(['roles.edit', 'roles.delete'])<th class="text-end pe-3">Thao tác</th>@endcanany
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr wire:key="role-{{ $role->id }}">
                            <td class="ps-3 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary p-3"><i class="fa-solid fa-shield"></i></span>
                                    <div>
                                        <div class="fw-bold text-body">{{ $role->display_name }}</div>
                                        <div class="small text-secondary">Vai trò #{{ $role->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="fw-bold text-body">{{ number_format($role->users_count) }}</span><span class="text-secondary ms-1">người</span></td>
                            <td><span class="fw-bold text-body">{{ number_format($role->permissions_count) }}</span><span class="text-secondary ms-1">quyền</span></td>
                            <td><code class="text-secondary bg-body-tertiary border rounded px-2 py-1">{{ $role->name }}</code></td>
                            @canany(['roles.edit', 'roles.delete'])
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        @can('roles.edit')
                                            <a href="{{ route('app.roles.edit', $role) }}" class="btn btn-outline-primary btn-sm" wire:navigate><i class="fa-solid fa-pen me-1"></i>Chỉnh sửa</a>
                                        @endcan
                                        @can('roles.delete')
                                            <button type="button" wire:click="deleteRole({{ $role->id }})" wire:confirm="Bạn có chắc chắn muốn xóa vai trò {{ $role->name }}?" class="btn btn-outline-danger btn-sm" @disabled($role->users_count > 0) title="{{ $role->users_count > 0 ? 'Vai trò đang được sử dụng' : 'Xóa vai trò' }}"><i class="fa-solid fa-trash"></i><span class="visually-hidden">Xóa</span></button>
                                        @endcan
                                    </div>
                                </td>
                            @endcanany
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-5"><i class="fa-solid fa-shield-circle-exclamation fs-2 text-secondary d-block mb-3"></i><h6 class="fw-bold text-body mb-1">Không tìm thấy vai trò</h6><p class="text-secondary mb-0">Thử thay đổi từ khóa tìm kiếm.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roles->hasPages())<div class="card-footer bg-body border-top px-3 py-3">{{ $roles->links() }}</div>@endif
    </section>
</div>
