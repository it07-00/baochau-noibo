@extends('admin.layouts.app')

@section('title', 'Hồ sơ của tôi')
@section('page_title', 'Hồ sơ của tôi')

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

    <div class="row g-3 mt-1">
        <div class="col-xl-8">
            <div class="card shadow-custom rounded-custom h-100">
                <div class="card-body p-6">
                    <h3 class="h6 fs-4 fw-semibold mb-5">Chỉnh sửa thông tin cá nhân</h3>

                    <form method="POST" action="{{ route('app.profile.update') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label" for="avatar">Ảnh đại diện</label>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <x-user-avatar :user="auth()->user()" :size="64" />
                                <span class="text-muted">JPG, PNG, WEBP. Tối đa 2MB.</span>
                            </div>
                            <input
                                type="file"
                                id="avatar"
                                name="avatar"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            >
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label" for="username">Tài khoản</label>
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    class="form-control"
                                    value="{{ old('username', auth()->user()->username) }}"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="name">Họ tên</label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="form-control"
                                    value="{{ old('name', auth()->user()->name) }}"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="email">Email</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    value="{{ old('email', auth()->user()->email ?? '') }}"
                                    placeholder="Để trống nếu chưa dùng email"
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="phone">Điện thoại</label>
                                <input
                                    type="text"
                                    id="phone"
                                    name="phone"
                                    class="form-control"
                                    value="{{ old('phone', auth()->user()->phone ?? '') }}"
                                    placeholder="Nhập số điện thoại"
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="gender">Giới tính</label>
                                <select id="gender" name="gender" class="form-select">
                                    <option value="">Chọn giới tính</option>
                                    <option value="female" {{ old('gender', auth()->user()->gender) === 'female' ? 'selected' : '' }}>Nữ</option>
                                    <option value="male" {{ old('gender', auth()->user()->gender) === 'male' ? 'selected' : '' }}>Nam</option>
                                    <option value="other" {{ old('gender', auth()->user()->gender) === 'other' ? 'selected' : '' }}>Khác</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="date_of_birth">Ngày sinh</label>
                                <input
                                    type="date"
                                    id="date_of_birth"
                                    name="date_of_birth"
                                    class="form-control"
                                    value="{{ old('date_of_birth', auth()->user()->date_of_birth?->format('Y-m-d')) }}"
                                >
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="address">Địa chỉ</label>
                                <textarea
                                    id="address"
                                    name="address"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Nhập địa chỉ"
                                >{{ old('address', auth()->user()->address ?? '') }}</textarea>
                            </div>
                        </div>

                        <button class="btn btn-primary mt-4" type="submit">Lưu thay đổi</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow-custom rounded-custom h-100">
                <div class="card-body p-6">
                    <h3 class="h6 fs-4 fw-semibold mb-4">Đổi mật khẩu</h3>
                    <p class="text-muted mb-4">Tính năng đổi mật khẩu đã được tách sang trang riêng để dễ quản lý hơn.</p>
                    <a href="{{ route('app.password.index') }}" class="btn btn-label-primary w-100">Đi tới trang đổi mật khẩu</a>
                </div>
            </div>
        </div>
    </div>
@endsection
