@extends('admin.layouts.app')

@section('title', 'Hồ sơ của tôi')
@section('page_title', 'Hồ sơ của tôi')

@section('content')
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mt-1 mb-4">
        <div class="d-flex align-items-center gap-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary flex-shrink-0 wh-44">
                <i class="fa-solid fa-user-gear fs-5"></i>
            </span>
            <div>
                <h4 class="fw-bold mb-1">Cài đặt tài khoản</h4>
                <p class="text-muted mb-0">Quản lý thông tin cá nhân và bảo mật đăng nhập.</p>
            </div>
        </div>
        <div class="btn-group" role="navigation" aria-label="Điều hướng cài đặt tài khoản">
            <a href="{{ route('app.profile.index') }}" class="btn btn-primary" aria-current="page">
                <i class="fa-solid fa-address-card me-1"></i>Hồ sơ
            </a>
            <a href="{{ route('app.password.index') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-lock me-1"></i>Mật khẩu
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2" role="status">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
            <i class="fa-solid fa-circle-exclamation mt-1"></i>
            <div>
                <div class="fw-semibold">Chưa thể lưu thông tin</div>
                <div>Vui lòng kiểm tra lại các trường được đánh dấu bên dưới.</div>
            </div>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xxl-10 col-xl-11">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom p-4">
                    <div class="d-flex align-items-center gap-3">
                        <x-user-avatar :user="auth()->user()" :size="64" />
                        <div class="min-w-0">
                            <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>
                            <div class="d-flex align-items-center flex-wrap gap-2 small text-muted">
                                <span>{{ '@'.auth()->user()->username }}</span>
                                <span>·</span>
                                <span>{{ auth()->user()->department?->name ?? 'Chưa có phòng ban' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('app.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="username">Tài khoản <span class="text-danger">*</span></label>
                                <input type="text" id="username" name="username"
                                       class="form-control @error('username') is-invalid @enderror"
                                       value="{{ old('username', auth()->user()->username) }}" autocomplete="username" required>
                                @error('username') <div class="invalid-feedback" role="alert">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="name">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', auth()->user()->name) }}" autocomplete="name" required>
                                @error('name') <div class="invalid-feedback" role="alert">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="email">Email</label>
                                <input type="email" id="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', auth()->user()->email ?? '') }}" autocomplete="email"
                                       placeholder="ten@congty.vn">
                                @error('email') <div class="invalid-feedback" role="alert">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="phone">Điện thoại</label>
                                <input type="tel" id="phone" name="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', auth()->user()->phone ?? '') }}" autocomplete="tel" inputmode="tel"
                                       placeholder="Nhập số điện thoại">
                                @error('phone') <div class="invalid-feedback" role="alert">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="gender">Giới tính</label>
                                <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                                    <option value="">Chọn giới tính</option>
                                    <option value="female" @selected(old('gender', auth()->user()->gender) === 'female')>Nữ</option>
                                    <option value="male" @selected(old('gender', auth()->user()->gender) === 'male')>Nam</option>
                                    <option value="other" @selected(old('gender', auth()->user()->gender) === 'other')>Khác</option>
                                </select>
                                @error('gender') <div class="invalid-feedback" role="alert">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="date_of_birth">Ngày sinh</label>
                                <input type="date" id="date_of_birth" name="date_of_birth"
                                       class="form-control @error('date_of_birth') is-invalid @enderror"
                                       value="{{ old('date_of_birth', auth()->user()->date_of_birth?->format('Y-m-d')) }}">
                                @error('date_of_birth') <div class="invalid-feedback" role="alert">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="avatar">Ảnh đại diện</label>
                                <input type="file" id="avatar" name="avatar"
                                       class="form-control @error('avatar') is-invalid @enderror"
                                       accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                       aria-describedby="avatar-help">
                                <div id="avatar-help" class="form-text">JPG, PNG hoặc WEBP; tối đa 2MB.</div>
                                @error('avatar') <div class="invalid-feedback" role="alert">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="address">Địa chỉ</label>
                                <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror"
                                          rows="3" maxlength="500" placeholder="Nhập địa chỉ">{{ old('address', auth()->user()->address ?? '') }}</textarea>
                                <div class="form-text">Tối đa 500 ký tự.</div>
                                @error('address') <div class="invalid-feedback" role="alert">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top p-4 d-flex justify-content-end">
                        <button class="btn btn-primary min-h-42px px-4" type="submit">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
