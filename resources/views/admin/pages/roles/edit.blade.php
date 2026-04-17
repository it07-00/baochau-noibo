@extends('admin.layouts.app')

@section('title', 'Sửa vai trò')
@section('page_title', 'Chỉnh sửa Vai trò: ' . $role->name)

@php
    $breadcrumbs = [
        ['label' => 'Quản trị', 'url' => route('app.dashboard')],
        ['label' => 'Vai trò', 'url' => route('app.roles.index')],
        ['label' => 'Chỉnh sửa'],
    ];
    $moduleNames = [
        // Quản trị hệ thống
        'users'                    => 'Người dùng',
        'roles'                    => 'Vai trò & Phân quyền',
        'departments'              => 'Phòng ban',
        'settings'                 => 'Cài đặt hệ thống',
        'master-data'              => 'Dữ liệu chuẩn',

        // Dữ liệu nền
        'handlers'                 => 'Chủ xử lý',
        'customers'                => 'Khách hàng',

        // Hợp đồng
        'contracts-waste'          => 'Hợp đồng chất thải',
        'contracts-consulting'     => 'Hợp đồng tư vấn',
        'contracts-project'        => 'Hợp đồng dự án',
        'contracts-commercial'     => 'Hợp đồng thương mại',
        'contracts-sustainability' => 'HĐ Phát triển bền vững',
        'contracts-energy'         => 'HĐ Giảm phát thải & NL',

        // Hóa đơn
        'invoices'                 => 'Hóa đơn Bảo Châu',
        'handler-invoices'         => 'Hóa đơn chủ xử lý',

        // Kinh doanh
        'sales-renewal'            => 'Doanh số tái ký',
        'sales-progressive'        => 'Doanh số tiến độ',
        'quotation-tracking'       => 'Theo dõi báo giá',
        'quotations'               => 'Báo giá',

        // Tài chính
        'commissions'              => 'Yêu cầu hoa hồng',
        'advance-requests'         => 'Yêu cầu ứng tiền',

        // Vận hành
        'waste-requests'           => 'Yêu cầu chất thải',
        'consulting-requests'      => 'Yêu cầu tư vấn',
        'project-requests'         => 'Yêu cầu dự án',
        'commercial-requests'      => 'Yêu cầu thương mại',
        'technical-requests'       => 'Yêu cầu kỹ thuật',

        // Chuyển phát
        'mail-delivery'            => 'Chuyển phát thư',

        // Thống kê & Báo cáo
        'rankings'                 => 'Bảng xếp hạng',
        'statistics'               => 'Bảng thống kê',
        'reports'                  => 'Báo cáo',
        'daily-reports'            => 'Báo cáo ngày',

        // Nội bộ & Marketing
        'internal-docs'            => 'Tài liệu nội bộ',
        'articles'                 => 'Bài viết / Marketing',
    ];
@endphp

@section('content')
    <form action="{{ route('app.roles.update', $role) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4 mt-1">
            <div class="col-12 col-xl-4">
                <div class="pure-card rounded-custom card-bg shadow-custom mb-4 position-sticky" style="top: 100px;">
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
                            <h6 class="fw-bold mb-3 text-primary">{{ $moduleNames[$module] ?? strtoupper($module) }}</h6>
                            <div class="row g-3">
                                @foreach($modulePermissions as $permission)
                                    @php
                                        // Rút gọn tên permission để hiển thị đẹp hơn
                                        $displayParts = explode('.', $permission->name);
                                        $action = isset($displayParts[1]) ? $displayParts[1] : $displayParts[0];
                                        $actionLabels = [
                                            'view' => 'Xem danh sách',
                                            'create' => 'Thêm mới',
                                            'edit' => 'Chỉnh sửa',
                                            'delete' => 'Xóa',
                                            'approve' => 'Phê duyệt',
                                            'export' => 'Xuất dữ liệu',
                                            'report' => 'Xem báo cáo'
                                        ];
                                        $displayAction = $actionLabels[$action] ?? ucfirst($action);

                                        $isChecked = (is_array(old('permissions')) && in_array($permission->name, old('permissions')))
                                            || (!old('permissions') && in_array($permission->name, $rolePermissions));
                                    @endphp
                                    <div class="col-md-4 col-sm-6">
                                        <div class="form-check custom-checkbox">
                                            <input class="form-check-input perm-check" type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permission->name }}"
                                                id="perm_{{ $permission->id }}"
                                                {{ $isChecked ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                {{ $displayAction }}
                                                <small class="d-block text-muted" style="font-size: 0.75rem">{{ $permission->name }}</small>
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
