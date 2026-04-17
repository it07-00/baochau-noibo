<div>
    @section('title', 'Người dùng')
    @section('page_title', 'Người dùng')

    @php
        $breadcrumbs = [
            ['label' => 'Quản trị', 'url' => route('app.dashboard')],
            ['label' => 'Người dùng']
        ];
    @endphp

    <div class="row g-3 mt-1">
        <div class="col-lg-4 col-md-6">
            <x-admin.summary-card title="Tổng người dùng" value="{{ $totalUsers }}" badge="Tổng hệ thống" iconClass="bg-glow-primary" />
        </div>
        <div class="col-lg-4 col-md-6">
            <x-admin.summary-card title="Đang hoạt động" value="{{ $activeUsers }}" badge="Tài khoản kích hoạt" iconClass="bg-glow-success" />
        </div>
        <div class="col-lg-4 col-md-6">
            <x-admin.summary-card title="Tài khoản khóa" value="{{ $totalUsers - $activeUsers }}" badge="Ngừng hoạt động" iconClass="bg-glow-danger" />
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success mt-3 shadow-sm border-0">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mt-3 shadow-sm border-0">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom">
                <div class="pure-card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h3 class="pure-card-title m-0">Danh sách người dùng</h3>

                    <div class="d-flex align-items-center gap-2">
                        <!-- Ô tìm kiếm realtime -->
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <span class="input-group-text bg-transparent border-end-0">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 ps-0" placeholder="Tìm kiếm...">
                        </div>

                        <!-- Lọc số lượng -->
                        <select wire:model.live="perPage" class="form-select form-select-sm w-auto">
                            <option value="10">10 dòng</option>
                            <option value="25">25 dòng</option>
                            <option value="50">50 dòng</option>
                        </select>

                        <a href="{{ route('app.users.create') }}" class="btn btn-primary btn-sm" wire:navigate>Tạo mới</a>
                    </div>
                </div>

                <div class="pure-card-body pb-3 position-relative">
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width:45px;">STT</th>
                                    <th>Họ tên</th>
                                    <th>Tên đăng nhập</th>
                                    <th>Phòng ban</th>
                                    <th>Vai trò</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr wire:key="user-{{ $user->id }}">
                                    <td class="text-center text-muted  fw-semibold">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <x-user-avatar :user="$user" :size="32" class="me-2" />
                                            <div>
                                                <h6 class="mb-0">{{ $user->name }}</h6>
                                                < class="text-muted">{{ $user->email }}</>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->username }}</td>
                                    <td>
                                        <span class="badge bg-label-info">{{ $user->department?->name ?? 'Không có' }}</span>
                                    </td>
                                    <td>
                                        @foreach($user->roles as $role)
                                            <span class="badge bg-label-primary">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-label-success">Đang hoạt động</span>
                                        @else
                                            <span class="badge bg-label-danger">Đã khóa</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end gap-1">
                                            <!-- Reset Password -->
                                            <button
                                                wire:click="resetPassword({{ $user->id }})"
                                                wire:confirm="Mật khẩu của tài khoản {{ $user->username }} sẽ được đưa về mặc định của hệ thống!"
                                                class="btn btn-sm btn-icon btn-light text-warning rounded-pill" title="Reset mật khẩu">
                                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                </svg>
                                            </button>

                                            <!-- Lock/Unlock -->
                                            @if($user->is_active)
                                                <button
                                                    wire:click="lockAccount({{ $user->id }})"
                                                    wire:confirm="Người dùng {{ $user->name }} sẽ không thể đăng nhập!"
                                                    class="btn btn-sm btn-icon btn-light text-secondary rounded-pill" title="Khóa" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                    </svg>
                                                </button>
                                            @else
                                                <button wire:click="unlockAccount({{ $user->id }})" class="btn btn-sm btn-icon btn-light text-success rounded-pill" title="Mở khóa">
                                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                                    </svg>
                                                </button>
                                            @endif

                                            <a href="{{ route('app.users.edit', $user) }}" class="btn btn-sm btn-icon btn-light text-primary rounded-pill" title="Sửa" wire:navigate>
                                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </a>

                                            <button
                                                wire:click="deleteUser({{ $user->id }})"
                                                wire:confirm="Bạn có chắc chắn muốn xóa {{ $user->name }}?"
                                                class="btn btn-sm btn-icon btn-light text-danger rounded-pill" title="Xóa" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Trống... Không tìm thấy người dùng.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($users->hasPages())
                <div class="pure-card-footer border-top px-4 py-3">
                    {{ $users->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
