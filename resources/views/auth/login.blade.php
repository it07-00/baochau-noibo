<!DOCTYPE html>
<html lang="vi" dir="ltr" data-bs-theme="light" data-color-theme="blue">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#316aff">
    <title>Đăng nhập | {{ config('app.name', 'Môi trường Bảo Châu') }}</title>

    <link rel="icon" href="{{ asset('assets/images/favicon-32x32.png') }}" sizes="32x32" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/libs/fontawesome/css/all.min.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}?v={{ config('app.version') }}">
</head>
<body>
    <main class="min-vh-100">
        <div class="row g-0 min-vh-100">
            <section class="col-lg-6 d-none d-lg-flex bg-primary text-white align-items-center" aria-label="Giới thiệu hệ thống">
                <div class="p-5 p-xl-7 mx-auto maxw-600px">
                    <div class="d-flex align-items-center gap-3 mb-6">
                        <img src="{{ asset('assets/images/logo.png') }}" width="68" height="68"
                             class="img-fluid" alt="Logo Bảo Châu">
                        <div>
                            <div class="fs-2 fw-bold text-white">BẢO CHÂU</div>
                            <div class="small text-white text-opacity-75">BAO CHAU ENVIRONMENT</div>
                        </div>
                    </div>
                    <h1 class="display-5 fw-bold text-white mb-4">Một nơi để theo dõi toàn bộ hoạt động.</h1>
                    <p class="fs-5 text-white text-opacity-75 mb-5">
                        Quản lý công việc, khách hàng, báo giá, hợp đồng và báo cáo vận hành trong một hệ thống thống nhất.
                    </p>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="border border-white border-opacity-25 rounded-3 p-3 h-100">
                                <i class="fa-solid fa-chart-line fs-4 mb-3"></i>
                                <div class="fw-semibold">Dữ liệu tập trung</div>
                                <small class="text-white text-opacity-75">Theo dõi tiến độ và số liệu mới nhất.</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="border border-white border-opacity-25 rounded-3 p-3 h-100">
                                <i class="fa-solid fa-user-shield fs-4 mb-3"></i>
                                <div class="fw-semibold">Truy cập an toàn</div>
                                <small class="text-white text-opacity-75">Dữ liệu hiển thị theo vai trò và quyền hạn.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="col-lg-6 d-flex align-items-center bg-body">
                <div class="w-100 p-4 p-sm-5 maxw-450px mx-auto">
                    <div class="d-lg-none text-center mb-5">
                        <img src="{{ asset('assets/images/logo-full.svg') }}" width="180" height="50" class="img-fluid" alt="Bảo Châu">
                    </div>

                    <div class="mb-5">
                        <h2 class="fw-bold mb-2">Chào mừng trở lại</h2>
                        <p class="text-dark opacity-75 mb-0">Đăng nhập để tiếp tục vào hệ thống quản trị Bảo Châu.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
                            <i class="fa-solid fa-circle-exclamation mt-1"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.attempt') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="loginUsername" class="form-label fw-semibold text-dark">Tên đăng nhập</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fa-regular fa-user text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-3 text-dark @error('username') is-invalid @enderror"
                                       id="loginUsername" name="username" value="{{ old('username') }}"
                                       placeholder="Nhập tên đăng nhập" autocomplete="username" required autofocus>
                                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="loginPassword" class="form-label fw-semibold text-dark">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fa-solid fa-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 border-end-0 ps-3 text-dark @error('password') is-invalid @enderror"
                                       id="loginPassword" name="password" placeholder="Nhập mật khẩu"
                                       autocomplete="current-password" required>
                                <button type="button" class="btn btn-light border border-start-0 text-primary" id="toggleLoginPassword"
                                        aria-label="Hiện mật khẩu" aria-pressed="false">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="loginRemember" name="remember" value="1"
                                   {{ old('remember', true) ? 'checked' : '' }}>
                            <label class="form-check-label text-dark opacity-75" for="loginRemember">Ghi nhớ đăng nhập trên thiết bị này</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-semibold">
                            Đăng nhập <i class="fa-solid fa-arrow-right ms-2"></i>
                        </button>
                    </form>

                    <div class="text-center text-dark opacity-75 small mt-5">
                        © {{ now()->year }} Công ty TNHH Dịch vụ và Kỹ thuật Môi trường Bảo Châu
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script>
        document.getElementById('toggleLoginPassword')?.addEventListener('click', function () {
            const input = document.getElementById('loginPassword');
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            this.setAttribute('aria-label', visible ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
            this.setAttribute('aria-pressed', visible ? 'false' : 'true');
            this.querySelector('i').className = visible ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
        });
    </script>
</body>
</html>
