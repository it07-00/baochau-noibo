@extends('admin.layouts.app')

@section('title', 'Đổi mật khẩu')
@section('page_title', 'Đổi mật khẩu')

@php
    $breadcrumbs = [
        ['label' => 'Quản trị', 'url' => route('app.dashboard')],
        ['label' => 'Hồ sơ của tôi', 'url' => route('app.profile.index')],
        ['label' => 'Đổi mật khẩu'],
    ];
@endphp

@section('content')
    @if (session('status'))
        <div class="alert alert-success mt-3" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mt-3" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mt-1 justify-content-center">
        <div class="col-xl-6 col-lg-8">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body p-6">
                    <h3 class="h6 fs-4 fw-semibold mb-4">Đổi mật khẩu tài khoản</h3>

                    <form method="POST" action="{{ route('app.password.update') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="current_password">Mật khẩu hiện tại</label>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="form-control"
                                autocomplete="current-password"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Mật khẩu mới</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="password_confirmation">Xác nhận mật khẩu mới</label>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-control"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('app.profile.index') }}" class="btn btn-custom-secondary">Quay lại hồ sơ</a>
                            <button class="btn btn-primary" type="submit">Cập nhật mật khẩu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
