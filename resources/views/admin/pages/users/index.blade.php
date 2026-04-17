@extends('admin.layouts.app')

@section('title', 'Người dùng')
@section('page_title', 'Người dùng')

@php
    $breadcrumbs = [
        ['label' => 'Quản trị', 'url' => route('app.dashboard')],
        ['label' => 'Người dùng'],
    ];
@endphp

@section('content')
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
                <div class="pure-card-header d-flex align-items-center justify-content-between gap-3">
                    <h3 class="pure-card-title m-0">Danh sách người dùng</h3>
                    <a href="{{ route('app.users.create') }}" class="btn btn-primary btn-sm">Tạo người dùng</a>
                </div>
                <div class="pure-card-body pb-3">
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
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
                                <tr>
                                    <td>{{ $user->id }}</td>
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
                                            <span class="badge bg-label-success">Hoạt động</span>
                                        @else
                                            <span class="badge bg-label-danger">Đã khóa</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('app.users.edit', $user) }}" class="btn btn-sm btn-icon btn-light text-primary rounded-pill me-1" title="Sửa">
                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('app.users.destroy', $user) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-light text-danger rounded-pill" title="Xóa" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Không có người dùng nào</td>
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
@endsection
