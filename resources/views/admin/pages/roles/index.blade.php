@extends('admin.layouts.app')

@section('title', 'Vai trò và Quyền')
@section('page_title', 'Quản lý Vai trò')

@php
    $breadcrumbs = [
        ['label' => 'Quản trị', 'url' => route('admin.dashboard')],
        ['label' => 'Vai trò'],
    ];
@endphp

@section('content')
    @if (session('status'))
        <div class="alert alert-success mt-1 shadow-sm border-0">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mt-1 shadow-sm border-0">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom">
                <div class="pure-card-header d-flex align-items-center justify-content-between gap-3">
                    <h3 class="pure-card-title m-0">Danh sách vai trò</h3>
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">Tạo vai trò mới</a>
                </div>
                <div class="pure-card-body pb-3">
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">ID</th>
                                    <th>Cấp độ / Tên vai trò</th>
                                    <th>Số nhân viên</th>
                                    <th>Số quyền hạn</th>
                                    <th class="text-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                <tr>
                                    <td>{{ $role->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-light-primary text-primary rounded me-3 d-flex align-items-center justify-content-center" style="width:36px; height:36px">
                                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4a2.5 2.5 0 11-5 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $role->name }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info px-2 py-1"><i class="fs-7 me-1 text-info fas fa-users"></i> {{ $role->users_count }} users</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-warning px-2 py-1"><i class="fs-7 me-1 text-warning fas fa-key"></i> {{ $role->permissions_count }} permissions</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-icon btn-light text-primary rounded-pill me-1" title="Sửa">
                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Xóa vai trò này sẽ ảnh hưởng đến nhân viên. Bạn có chắc chắn?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-light text-danger rounded-pill" title="Xóa" {{ $role->users_count > 0 ? 'disabled' : '' }}>
                                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Không có vai trò nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
