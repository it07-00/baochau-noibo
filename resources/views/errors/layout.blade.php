<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error {{ $code }} | Conca</title>

    <link rel="shortcut icon" href="{{ asset('assets/img/logo/favicon.png') }}" type="image/x-icon">
    <link id="bootstrap-css" rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/conca.css') }}">
</head>

<body>
    <div class="min-vh-100 row justify-content-center my-auto g-0 bg-error-page">
        <div class="col-xl-9 col-md-10 col-11 my-auto">
            <div class="px-5 py-14 error-img text-center rounded">
                <h1 class="display-2 fw-bold mb-10">Oops!</h1>

                <div class="mb-4">
                    <svg class="mw-100" width="380" height="140" viewBox="0 0 380 140" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Error {{ $code }}">
                        <text x="20" y="110" font-size="120" font-weight="700" fill="#C1B9FF">{{ $code }}</text>
                    </svg>
                </div>

                <h2 class="display-7 fw-semibold text-uppercase">{{ $title }}</h2>
                <p class="lead mb-7">{{ $message }}</p>
                <a href="{{ route('app.dashboard') }}" class="btn btn-primary">Quay ve trang chu</a>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/js/conca-sidebar.js') }}"></script>
    <script src="{{ asset('assets/js/conca.js') }}"></script>
</body>

</html>
