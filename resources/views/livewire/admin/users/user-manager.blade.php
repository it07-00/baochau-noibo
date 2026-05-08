<div class="user-manager-page">
    @section('title', 'Người dùng')
    @section('page_title', 'Người dùng')

    @push('styles')
    <style>
    .user-manager-page {
        --user-border: rgba(148, 163, 184, 0.22);
        --user-soft: #f8fafc;
        --user-card-radius: 12px;
    }
    .user-summary-grid {
        margin-top: .25rem;
    }
    .user-list-card {
        border-radius: var(--user-card-radius);
        overflow: hidden;
    }
    .user-list-header {
        gap: 14px;
        padding: 18px 20px;
    }
    .user-list-title {
        color: var(--bs-body-color);
        font-size: 1.05rem;
        font-weight: 800;
        letter-spacing: 0;
    }
    .user-toolbar {
        min-width: min(100%, 560px);
    }
    .user-search {
        min-width: 280px;
    }
    .user-search .input-group-text,
    .user-search .form-control {
        min-height: 38px;
        border-color: var(--user-border);
        background: var(--bs-card-bg, #fff);
    }
    .user-search .input-group-text {
        width: 42px;
        justify-content: center;
        color: var(--bs-secondary-color);
        border-right: 0;
        border-top-left-radius: 9px;
        border-bottom-left-radius: 9px;
    }
    .user-search .form-control {
        border-left: 0;
        border-top-right-radius: 9px;
        border-bottom-right-radius: 9px;
        padding-left: .55rem !important;
    }
    .user-search .form-control:focus {
        box-shadow: none;
        border-color: var(--bs-primary);
    }
    .user-search:focus-within .input-group-text {
        border-color: var(--bs-primary);
        color: var(--bs-primary);
    }
    .user-per-page {
        min-height: 38px;
    }
    .user-per-page {
        width: 136px;
        min-width: 136px;
        padding-right: 2.25rem;
    }
    .user-create-btn {
        min-height: 38px;
        border-radius: 9px;
        font-weight: 700;
    }
    .user-table {
        margin-bottom: 0;
    }
    .user-table thead th {
        background: var(--user-soft);
        border-bottom: 1px solid var(--user-border);
        color: var(--bs-secondary-color);
        font-size: .76rem;
        letter-spacing: 0;
        padding: 12px 16px;
        text-transform: uppercase;
    }
    .user-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
    }
    .user-name-block {
        min-width: 0;
    }
    .user-name-text {
        max-width: 240px;
        color: var(--bs-body-color);
        font-weight: 800;
    }
    .user-email-text,
    .user-meta-text {
        max-width: 260px;
    }
    .user-role-stack {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        max-width: 240px;
    }
    .user-action-group {
        gap: 6px;
    }
    .user-action-group .btn-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .user-mobile-list {
        display: grid;
        gap: 12px;
    }
    .user-mobile-card {
        border: 1px solid var(--user-border);
        border-radius: 12px;
        background: var(--bs-card-bg, #fff);
        padding: 14px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
    }
    .user-mobile-main {
        min-width: 0;
        width: 100%;
    }
    .user-mobile-name {
        color: var(--bs-body-color);
        font-size: .98rem;
        font-weight: 800;
    }
    .user-mobile-email {
        color: var(--bs-secondary-color);
        font-size: .82rem;
    }
    .user-mobile-meta {
        display: grid;
        gap: 8px;
        margin-top: 12px;
    }
    .user-mobile-meta-row {
        display: grid;
        grid-template-columns: minmax(82px, auto) minmax(0, 1fr);
        gap: 12px;
        color: var(--bs-secondary-color);
        font-size: .82rem;
    }
    .user-mobile-meta-row strong {
        color: var(--bs-body-color);
        font-weight: 700;
        text-align: right;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .user-mobile-actions {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        margin-top: 14px;
    }
    .user-mobile-actions .btn {
        min-height: 38px;
        border-radius: 9px;
    }
    .user-empty-state {
        padding: 42px 16px;
    }
    @media (max-width: 767.98px) {
        .user-summary-grid {
            padding-left: .5rem;
            padding-right: .5rem;
        }
        .user-list-header {
            align-items: stretch !important;
            padding: 16px;
        }
        .user-toolbar {
            width: 100%;
            min-width: 0;
        }
        .user-search {
            min-width: 0;
            width: 100%;
        }
        .user-per-page {
            width: 100%;
            min-width: 0;
        }
        .user-create-btn {
            flex: 1 1 auto;
            justify-content: center;
        }
        .user-list-body {
            padding: 14px !important;
            overflow: hidden;
        }
        .user-mobile-list,
        .user-mobile-card {
            max-width: 100%;
            min-width: 0;
        }
        .user-mobile-actions {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .user-mobile-card {
            overflow: hidden;
        }
        .user-mobile-card > .d-flex {
            align-items: flex-start !important;
        }
        .user-mobile-main > .d-flex {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr);
        }
        .user-mobile-main > .d-flex > .badge {
            justify-self: start;
            margin-top: 8px;
        }
        .user-mobile-actions .btn {
            min-width: 0;
        }
    }
    [data-bs-theme="dark"] .user-manager-page {
        --user-border: rgba(255, 255, 255, 0.12);
        --user-soft: #151c26;
    }
    [data-bs-theme="dark"] .user-mobile-card {
        background: #1f2631;
        box-shadow: none;
    }
    [data-bs-theme="dark"] .user-search .input-group-text,
    [data-bs-theme="dark"] .user-search .form-control {
        background: #14161a;
        border-color: rgba(255, 255, 255, 0.14);
    }
    [data-bs-theme="dark"] .user-search .form-control::placeholder {
        color: #9aa3b2;
    }
    [data-bs-theme="dark"] .user-table thead th {
        background: #151c26;
    }
    </style>
    @endpush

    @php
        $breadcrumbs = [
            ['label' => 'Quản trị', 'url' => route('app.dashboard')],
            ['label' => 'Người dùng']
        ];
    @endphp

    <div class="row g-3 user-summary-grid px-2 px-md-0">
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

    <div class="row g-3 mt-2 px-2 px-md-0">
        <div class="col-12">
            <div class="pure-card user-list-card rounded-custom card-bg shadow-custom">
                <div class="pure-card-header user-list-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between">
                    <div>
                        <h3 class="user-list-title m-0">Danh sách người dùng</h3>
                        <div class="text-muted small mt-1">Quản lý tài khoản, trạng thái và quyền truy cập.</div>
                    </div>

                    <div class="user-toolbar d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 mt-1 mt-lg-0">
                        <div class="input-group input-group-sm user-search">
                            <span class="input-group-text bg-transparent border-end-0">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 ps-0" placeholder="Tìm tên, email, tài khoản...">
                        </div>

                        <div class="d-flex gap-2">
                            <select wire:model.live="perPage" class="form-select form-select-sm user-per-page">
                                <option value="10">10 dòng</option>
                                <option value="25">25 dòng</option>
                                <option value="50">50 dòng</option>
                            </select>

                            <a href="{{ route('app.users.create') }}" class="btn btn-primary btn-sm text-nowrap user-create-btn d-inline-flex align-items-center gap-1" wire:navigate>
                                <span>+</span> Tạo mới
                            </a>
                        </div>
                    </div>
                </div>

                <div class="pure-card-body user-list-body pb-3 position-relative">
                    <div class="table-responsive d-none d-md-block">
                        <table class="table user-table text-nowrap align-middle table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:58px;">STT</th>
                                    <th>Người dùng</th>
                                    <th>Tên đăng nhập</th>
                                    <th class="d-none d-lg-table-cell">Phòng ban</th>
                                    <th>Vai trò</th>
                                    <th>Trạng thái</th>
                                    @canany(['users.edit', 'users.delete'])
                                    <th class="text-end">Hành động</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr wire:key="user-row-{{ $user->id }}">
                                    <td class="text-center text-muted fw-semibold">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <x-user-avatar :user="$user" :size="36" class="me-2 flex-shrink-0" />
                                            <div class="user-name-block">
                                                <h6 class="user-name-text mb-0 text-truncate">{{ $user->name }}</h6>
                                                <small class="user-email-text text-muted d-block text-truncate">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $user->username }}</span>
                                        @if($user->phone)
                                            <small class="user-meta-text text-muted d-block text-truncate">{{ $user->phone }}</small>
                                        @endif
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <span class="badge bg-label-info">{{ $user->department?->name ?? 'Không có' }}</span>
                                    </td>
                                    <td>
                                        <div class="user-role-stack">
                                            @forelse($user->roles as $role)
                                                <span class="badge bg-label-primary">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-muted small">Chưa gán</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-label-success">Đang hoạt động</span>
                                        @else
                                            <span class="badge bg-label-danger">Đã khóa</span>
                                        @endif
                                    </td>
                                    @canany(['users.edit', 'users.delete'])
                                    <td class="text-end">
                                        <div class="user-action-group d-flex align-items-center justify-content-end">
                                            @can('users.edit')
                                            <button
                                                wire:click="resetPassword({{ $user->id }})"
                                                wire:confirm="Mật khẩu của tài khoản {{ $user->username }} sẽ được đưa về mặc định của hệ thống!"
                                                class="btn btn-sm btn-icon btn-light text-warning rounded-pill" title="Reset mật khẩu">
                                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                </svg>
                                            </button>

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
                                            @endcan

                                            @can('users.delete')
                                            <button
                                                wire:click="deleteUser({{ $user->id }})"
                                                wire:confirm="Bạn có chắc chắn muốn xóa {{ $user->name }}?"
                                                class="btn btn-sm btn-icon btn-light text-danger rounded-pill" title="Xóa" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                    @endcanany
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="user-empty-state text-center text-muted">Không tìm thấy người dùng.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="user-mobile-list d-md-none">
                        @forelse($users as $user)
                            <article class="user-mobile-card" wire:key="user-card-{{ $user->id }}">
                                <div class="d-flex align-items-start gap-3">
                                    <x-user-avatar :user="$user" :size="42" class="flex-shrink-0" />
                                    <div class="user-mobile-main flex-grow-1">
                                        <div class="d-flex align-items-start justify-content-between gap-2">
                                            <div class="min-w-0">
                                                <div class="user-mobile-name text-truncate">{{ $user->name }}</div>
                                                <div class="user-mobile-email text-truncate">{{ $user->email }}</div>
                                            </div>
                                            @if($user->is_active)
                                                <span class="badge bg-label-success flex-shrink-0">Hoạt động</span>
                                            @else
                                                <span class="badge bg-label-danger flex-shrink-0">Khóa</span>
                                            @endif
                                        </div>

                                        <div class="user-mobile-meta">
                                            <div class="user-mobile-meta-row">
                                                <span>Tài khoản</span>
                                                <strong>{{ $user->username }}</strong>
                                            </div>
                                            <div class="user-mobile-meta-row">
                                                <span>Phòng ban</span>
                                                <strong>{{ $user->department?->name ?? 'Không có' }}</strong>
                                            </div>
                                            <div class="d-flex flex-wrap gap-1">
                                                @forelse($user->roles as $role)
                                                    <span class="badge bg-label-primary">{{ $role->name }}</span>
                                                @empty
                                                    <span class="text-muted small">Chưa gán vai trò</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @canany(['users.edit', 'users.delete'])
                                <div class="user-mobile-actions">
                                    @can('users.edit')
                                    <button
                                        wire:click="resetPassword({{ $user->id }})"
                                        wire:confirm="Mật khẩu của tài khoản {{ $user->username }} sẽ được đưa về mặc định của hệ thống!"
                                        class="btn btn-sm btn-light text-warning fw-semibold">
                                        Reset
                                    </button>
                                    @if($user->is_active)
                                        <button
                                            wire:click="lockAccount({{ $user->id }})"
                                            wire:confirm="Người dùng {{ $user->name }} sẽ không thể đăng nhập!"
                                            class="btn btn-sm btn-light text-secondary fw-semibold" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                            Khóa
                                        </button>
                                    @else
                                        <button wire:click="unlockAccount({{ $user->id }})" class="btn btn-sm btn-light text-success fw-semibold">
                                            Mở khóa
                                        </button>
                                    @endif
                                    <a href="{{ route('app.users.edit', $user) }}" class="btn btn-sm btn-light text-primary fw-semibold" wire:navigate>Sửa</a>
                                    @endcan

                                    @can('users.delete')
                                    <button
                                        wire:click="deleteUser({{ $user->id }})"
                                        wire:confirm="Bạn có chắc chắn muốn xóa {{ $user->name }}?"
                                        class="btn btn-sm btn-light text-danger fw-semibold" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                        Xóa
                                    </button>
                                    @endcan
                                </div>
                                @endcanany
                            </article>
                        @empty
                            <div class="user-empty-state text-center text-muted">Không tìm thấy người dùng.</div>
                        @endforelse
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
