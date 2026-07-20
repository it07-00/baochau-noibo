<div>
    @section('title', 'Người dùng')
    @section('page_title', 'Người dùng')

    <header class="mb-4">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                        <i class="fa-solid fa-user-shield me-1"></i>Quản trị truy cập
                    </span>
                </div>
                <h4 class="fw-bold text-body mb-1">Người dùng</h4>
                <p class="text-secondary mb-0">Quản lý tài khoản, vai trò và trạng thái truy cập hệ thống.</p>
            </div>
            @can('users.create')
                <a href="{{ route('app.users.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3" wire:navigate>
                    <i class="fa-solid fa-user-plus"></i><span>Tạo người dùng</span>
                </a>
            @endcan
        </div>

        <div class="d-flex align-items-center gap-4 mt-4 flex-wrap">
            <div>
                <div class="h4 fw-bold text-body mb-0">{{ number_format($totalUsers) }}</div>
                <div class="small text-secondary">Tổng tài khoản</div>
            </div>
            <div class="vr"></div>
            <div>
                <div class="h4 fw-bold text-success mb-0">{{ number_format($activeUsers) }}</div>
                <div class="small text-secondary">Đang hoạt động</div>
            </div>
            <div class="vr"></div>
            <div>
                <div class="h4 fw-bold text-danger mb-0">{{ number_format($totalUsers - $activeUsers) }}</div>
                <div class="small text-secondary">Đã khóa</div>
            </div>
        </div>
    </header>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2" role="alert"><i class="fa-solid fa-circle-check"></i>{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2" role="alert"><i class="fa-solid fa-circle-exclamation"></i>{{ session('error') }}</div>
    @endif

    <section class="card border border-light-subtle shadow-sm rounded-3 overflow-hidden bg-body" aria-labelledby="users-list-title">
        <div class="card-header bg-body-tertiary p-3 border-bottom border-light-subtle">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-lg">
                    <h6 id="users-list-title" class="fw-bold text-body mb-1">Danh sách tài khoản</h6>
                    <p class="text-secondary small mb-0">Chọn một tài khoản để chỉnh sửa hoặc thay đổi trạng thái.</p>
                </div>
                <div class="col-12 col-md-8 col-lg-5 col-xl-4">
                    <label for="user-search" class="visually-hidden">Tìm người dùng</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-tertiary border-end-0"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                        <input id="user-search" wire:model.live.debounce.300ms="search" type="search" class="form-control bg-body-tertiary border-start-0 ps-0" placeholder="Tìm tên, email, tài khoản...">
                    </div>
                </div>
                <div class="col-8 col-md-3 col-lg-auto">
                    <label for="user-per-page" class="visually-hidden">Số dòng</label>
                    <select id="user-per-page" wire:model.live="perPage" class="form-select">
                        <option value="10">10 dòng</option>
                        <option value="25">25 dòng</option>
                        <option value="50">50 dòng</option>
                    </select>
                </div>
                <div class="col-4 col-md-1 col-lg-auto text-center">
                    <span wire:loading wire:target="search,perPage" class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Đang tải</span></span>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="table-light text-secondary small">
                    <tr>
                        <th class="ps-3">Người dùng</th>
                        <th>Tài khoản</th>
                        <th>Phòng ban</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        @canany(['users.edit', 'users.delete'])<th class="text-end pe-3">Thao tác</th>@endcanany
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr wire:key="user-row-{{ $user->id }}">
                            <td class="ps-3 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <x-user-avatar :user="$user" :size="38" class="flex-shrink-0" />
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-body">{{ $user->name }}</div>
                                        <div class="small text-secondary">{{ $user->email ?: 'Chưa có email' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-body">{{ $user->username }}</div>
                                @if($user->phone)<div class="small text-secondary">{{ $user->phone }}</div>@endif
                            </td>
                            <td><span class="text-body">{{ $user->department?->name ?? 'Chưa phân phòng' }}</span></td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-medium">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-secondary small">Chưa gán</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill"><i class="fa-solid fa-circle-check me-1"></i>Hoạt động</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill"><i class="fa-solid fa-lock me-1"></i>Đã khóa</span>
                                @endif
                            </td>
                            @canany(['users.edit', 'users.delete'])
                                <td class="text-end pe-3">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Thao tác</button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            @can('users.edit')
                                                <li><a href="{{ route('app.users.edit', $user) }}" class="dropdown-item d-flex align-items-center gap-2" wire:navigate><i class="fa-solid fa-pen text-primary"></i>Chỉnh sửa</a></li>
                                                <li><button type="button" wire:click="resetPassword({{ $user->id }})" wire:confirm="Mật khẩu của tài khoản {{ $user->username }} sẽ được đưa về mặc định của hệ thống!" class="dropdown-item d-flex align-items-center gap-2"><i class="fa-solid fa-key text-warning"></i>Đặt lại mật khẩu</button></li>
                                                <li><hr class="dropdown-divider"></li>
                                                @if($user->is_active)
                                                    <li><button type="button" wire:click="lockAccount({{ $user->id }})" wire:confirm="Người dùng {{ $user->name }} sẽ không thể đăng nhập!" class="dropdown-item d-flex align-items-center gap-2" @disabled($user->id === auth()->id())><i class="fa-solid fa-lock text-secondary"></i>Khóa tài khoản</button></li>
                                                @else
                                                    <li><button type="button" wire:click="unlockAccount({{ $user->id }})" class="dropdown-item d-flex align-items-center gap-2"><i class="fa-solid fa-lock-open text-success"></i>Mở khóa</button></li>
                                                @endif
                                            @endcan
                                            @can('users.delete')
                                                <li><button type="button" wire:click="deleteUser({{ $user->id }})" wire:confirm="Bạn có chắc chắn muốn xóa {{ $user->name }}?" class="dropdown-item d-flex align-items-center gap-2 text-danger" @disabled($user->id === auth()->id())><i class="fa-solid fa-trash"></i>Xóa tài khoản</button></li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            @endcanany
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fa-solid fa-user-slash fs-2 text-secondary mb-3 d-block"></i>
                                <h6 class="fw-bold text-body mb-1">Không tìm thấy người dùng</h6>
                                <p class="text-secondary mb-0">Thử thay đổi từ khóa tìm kiếm.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="card-footer bg-body border-top px-3 py-3">{{ $users->links() }}</div>
        @endif
    </section>
</div>
