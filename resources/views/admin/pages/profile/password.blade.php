@extends('admin.layouts.app')

@section('title', 'Đổi mật khẩu')
@section('page_title', 'Đổi mật khẩu')

@section('content')
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mt-1 mb-4">
        <div class="d-flex align-items-center gap-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary flex-shrink-0 wh-44">
                <i class="fa-solid fa-shield-halved fs-5"></i>
            </span>
            <div>
                <h4 class="fw-bold mb-1">Bảo mật tài khoản</h4>
                <p class="text-muted mb-0">Thay đổi mật khẩu đăng nhập của bạn.</p>
            </div>
        </div>
        <div class="btn-group" role="navigation" aria-label="Điều hướng cài đặt tài khoản">
            <a href="{{ route('app.profile.index') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-address-card me-1"></i>Hồ sơ
            </a>
            <a href="{{ route('app.password.index') }}" class="btn btn-primary" aria-current="page">
                <i class="fa-solid fa-lock me-1"></i>Mật khẩu
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
            <i class="fa-solid fa-circle-exclamation mt-1"></i>
            <div>
                <div class="fw-semibold">Chưa thể cập nhật mật khẩu</div>
                <div>Vui lòng kiểm tra lại các trường được đánh dấu bên dưới.</div>
            </div>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xl-7 col-lg-8">
            <div class="card border-0 shadow-sm" x-data="{ currentVisible: false, newVisible: false, confirmVisible: false }">
                <div class="card-header bg-transparent border-bottom p-4">
                    <h5 class="fw-bold mb-1">Đổi mật khẩu</h5>
                    <p class="text-muted small mb-0">Bạn sẽ dùng mật khẩu mới cho lần đăng nhập tiếp theo.</p>
                </div>
                <form method="POST" action="{{ route('app.password.update') }}">
                    @csrf
                    <div class="card-body p-4">
                        <div class="alert alert-primary d-flex gap-3 align-items-start mb-4">
                            <i class="fa-solid fa-circle-info mt-1"></i>
                            <div class="small">Mật khẩu mới cần có ít nhất 8 ký tự, khác mật khẩu hiện tại và không nên dùng chung với dịch vụ khác.</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="current_password">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input :type="currentVisible ? 'text' : 'password'" id="current_password" name="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       autocomplete="current-password" required autofocus>
                                <button class="btn btn-outline-secondary" type="button" @click="currentVisible = !currentVisible"
                                        :aria-label="currentVisible ? 'Ẩn mật khẩu hiện tại' : 'Hiện mật khẩu hiện tại'">
                                    <i class="fa-solid" :class="currentVisible ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                                @error('current_password') <div class="invalid-feedback" role="alert">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="password">Mật khẩu mới <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input :type="newVisible ? 'text' : 'password'" id="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       autocomplete="new-password" minlength="8" required aria-describedby="password-help">
                                <button class="btn btn-outline-secondary" type="button" @click="newVisible = !newVisible"
                                        :aria-label="newVisible ? 'Ẩn mật khẩu mới' : 'Hiện mật khẩu mới'">
                                    <i class="fa-solid" :class="newVisible ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                                @error('password') <div class="invalid-feedback" role="alert">{{ $message }}</div> @enderror
                            </div>
                            <div id="password-help" class="form-text">Tối thiểu 8 ký tự và khác mật khẩu hiện tại.</div>
                        </div>

                        <div>
                            <label class="form-label fw-semibold" for="password_confirmation">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input :type="confirmVisible ? 'text' : 'password'" id="password_confirmation" name="password_confirmation"
                                       class="form-control" autocomplete="new-password" minlength="8" required>
                                <button class="btn btn-outline-secondary" type="button" @click="confirmVisible = !confirmVisible"
                                        :aria-label="confirmVisible ? 'Ẩn mật khẩu xác nhận' : 'Hiện mật khẩu xác nhận'">
                                    <i class="fa-solid" :class="confirmVisible ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top p-4 d-flex flex-column-reverse flex-sm-row justify-content-end gap-2">
                        <a href="{{ route('app.profile.index') }}" class="btn btn-outline-secondary min-h-42px">Quay lại hồ sơ</a>
                        <button class="btn btn-primary min-h-42px px-4" type="submit">
                            <i class="fa-solid fa-shield-check me-1"></i>Cập nhật mật khẩu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
