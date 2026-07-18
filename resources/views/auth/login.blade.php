<!DOCTYPE html>
<html lang="vi" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Đăng nhập | {{ config('app.name', 'Môi trường Bảo Châu') }}</title>

    <link rel="icon" href="{{ asset('assets/images/favicon-32x32.png') }}" sizes="32x32" type="image/png">
    <link rel="icon" href="{{ asset('assets/images/favicon-192x192.png') }}" sizes="192x192" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/apple-touch-icon.png') }}">

    <link rel="stylesheet" href="{{ asset('assets/libs/flaticon/css/all/all.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}?v={{ config('app.version') }}">
</head>

<body>
    <div class="auth-main">
        <div class="container-xxl">
            <div class="auth-wrapper auth-basic p-5 min-vh-100 d-flex align-items-center justify-content-center">
                <div class="auth-card py-6">
                    <div class="card shadow-xl">
                        <div class="card-body py-9 px-6 px-sm-12">
                            <div class="mb-7">
                                <div class="d-flex align-items-center justify-content-center mb-5">
                                    <img class="app-main-logo" width="120" src="{{ asset('assets/images/logo.png') }}"
                                        alt="Bảo Châu Environment">
                                </div>
                                <div class="text-center">
                                    <h4 class="mb-1 fw-semibold">Chào mừng bạn</h4>
                                    <p>Đăng nhập bằng tên đăng nhập và mật khẩu để tiếp tục.</p>
                                </div>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login.attempt') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="loginUsername" class="form-label">Tên đăng nhập</label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror"
                                        id="loginUsername" name="username" value="{{ old('username') }}"
                                        placeholder="Nhập tên đăng nhập" required autofocus>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="loginPassword" class="form-label">Mật khẩu</label>
                                    <div class="input-group mb-3">
                                        <input type="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="**********" id="loginPassword" name="password" required>
                                        <span class="input-group-text password-toggle">
                                            <span class="close-eye password-eye">
                                                <svg width="22" height="10" viewBox="0 0 22 10" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M21 1C21 1 17 7 11 7C5 7 1 1 1 1" stroke="currentColor"
                                                        stroke-width="1.5" stroke-linecap="round" />
                                                    <path d="M14 6.5L15.5 9" stroke="currentColor" stroke-width="1.5"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                    <path d="M19 4L21 6" stroke="currentColor" stroke-width="1.5"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                    <path d="M1 6L3 4" stroke="currentColor" stroke-width="1.5"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                    <path d="M8 6.5L6.5 9" stroke="currentColor" stroke-width="1.5"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                            <span class="open-eye password-eye d-none">
                                                <svg width="22" height="16" viewBox="0 0 22 16" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M20.544 7.04498C20.848 7.4713 21 7.68447 21 8C21 8.31553 20.848 8.52869 20.544 8.95501C19.1779 10.8706 15.6892 15 11 15C6.31078 15 2.8221 10.8706 1.45604 8.95502C1.15201 8.5287 1 8.31553 1 8C1 7.68447 1.15201 7.47131 1.45604 7.04499C2.8221 5.12944 6.31078 1 11 1C15.6892 1 19.1779 5.12944 20.544 7.04498Z"
                                                        stroke="currentColor" stroke-width="1.5" />
                                                    <path
                                                        d="M14 8C14 6.34315 12.6569 5 11 5C9.34315 5 8 6.34315 8 8C8 9.65685 9.34315 11 11 11C12.6569 11 14 9.65685 14 8Z"
                                                        stroke="currentColor" stroke-width="1.5" />
                                                </svg>
                                            </span>
                                        </span>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-5">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="loginRemember"
                                                name="remember" value="1" {{ old('remember', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="loginRemember">Ghi nhớ đăng
                                                nhập</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/libs/global/global.min.js') }}?v={{ config('app.version') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}?v={{ config('app.version') }}"></script>
</body>

</html>