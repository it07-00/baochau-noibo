@extends('admin.layouts.app')

@section('title', 'Sửa vai trò')
@section('page_title', 'Chỉnh sửa Vai trò: ' . $role->name)

@section('content')
    <form action="{{ route('app.roles.update', $role) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4 mt-1">
            <div class="col-12 col-xl-4">
                <div class="pure-card rounded-custom card-bg shadow-custom mb-4 position-sticky top-100px" >
                    <div class="pure-card-header border-bottom">
                        <h5 class="pure-card-title m-0">Thông tin cơ bản</h5>
                    </div>
                    <div class="pure-card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-medium">Tên vai trò <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary py-2">Lưu thay đổi</button>
                            <a href="{{ route('app.roles.index') }}" class="btn btn-light mt-2 py-2">Hủy bỏ</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-8">
                <div class="pure-card rounded-custom card-bg shadow-custom">
                    <div class="pure-card-header border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="pure-card-title m-0">Phân quyền chi tiết</h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="checkAll">
                            <label class="form-check-label" for="checkAll">Chọn tất cả</label>
                        </div>
                    </div>
                    <div class="pure-card-body p-0">
                        @foreach($permissions as $module => $modulePermissions)
                        <div class="permission-group border-bottom p-4">
                            <h6 class="fw-bold mb-3 text-primary">{{ \App\Support\RolePermissionViewData::moduleName($module) }}</h6>
                            <div class="row g-3">
                                @foreach($modulePermissions as $permission)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="form-check custom-checkbox">
                                            <input class="form-check-input perm-check" type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permission->name }}"
                                                id="perm_{{ $permission->id }}"
                                                {{ (is_array(old('permissions')) && in_array($permission->name, old('permissions'))) || (!old('permissions') && in_array($permission->name, $rolePermissions)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                {{ \App\Support\RolePermissionViewData::actionLabel($permission->name) }}
                                                <small class="d-block text-muted fs-75" >{{ $permission->name }}</small>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        document.getElementById('checkAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.perm-check');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });
    </script>
    @endpush
@endsection
