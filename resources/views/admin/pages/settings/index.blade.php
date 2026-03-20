@extends('admin.layouts.app')

@section('title', 'Cài đặt hệ thống')
@section('page_title', 'Cài đặt hệ thống')

@php
    $breadcrumbs = [
        ['label' => 'Quản trị', 'url' => route('app.dashboard')],
        ['label' => 'Cài đặt hệ thống'],
    ];
@endphp

@section('content')
    <div class="row g-3 mt-1">
        <div class="col-lg-4 col-md-6">
            <x-admin.summary-card title="Điểm bảo mật" value="86/100" badge="Mạnh" iconClass="bg-glow-success" />
        </div>
        <div class="col-lg-4 col-md-6">
            <x-admin.summary-card title="Khóa API" value="6 đang hoạt động" badge="Đổi khóa 12 ngày trước" iconClass="bg-glow-info" />
        </div>
        <div class="col-lg-4 col-md-6">
            <x-admin.summary-card title="Quy tắc thông báo" value="14 quy tắc" badge="3 quy trình tùy chỉnh" iconClass="bg-glow-primary" />
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-xl-7">
            <div class="card shadow-custom rounded-custom h-100">
                <div class="card-body p-6">
                    <h3 class="h6 fs-4 fw-semibold mb-5">Cài đặt chung</h3>

                    <div class="mb-4">
                        <label class="form-label">Tên hệ thống</label>
                        <input type="text" class="form-control" value="Môi trường Bảo Châu">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Số điện thoại hỗ trợ</label>
                        <input type="text" class="form-control" value="0900 000 000">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Múi giờ</label>
                        <select class="form-select">
                            <option selected>Asia/Ho_Chi_Minh</option>
                            <option>Asia/Bangkok</option>
                            <option>Asia/Singapore</option>
                        </select>
                    </div>

                    <button class="btn btn-primary" type="button">Lưu thay đổi</button>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card shadow-custom rounded-custom h-100">
                <div class="card-body p-6">
                    <h3 class="h6 fs-4 fw-semibold mb-5">Tùy chọn thông báo</h3>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" checked>
                        <label class="form-check-label">Cảnh báo hệ thống</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" checked>
                        <label class="form-check-label">Cập nhật sản phẩm</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox">
                        <label class="form-check-label">Báo cáo tuần</label>
                    </div>

                    <button class="btn btn-label-primary" type="button">Cập nhật tùy chọn</button>
                </div>
            </div>
        </div>
    </div>
@endsection
