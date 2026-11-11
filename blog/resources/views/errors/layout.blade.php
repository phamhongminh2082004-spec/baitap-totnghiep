{{-- KHUNG TRANG LỖI độc lập (không phụ thuộc đăng nhập nên vẫn hiển thị được khi lỗi 500). --}}
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f4; color: #1f2a28; }
        .code { font-size: 7rem; font-weight: 800; line-height: 1; color: #1f6f5c; }
    </style>
</head>
<body class="d-flex align-items-center" style="min-height: 100vh">
    <main class="container text-center">
        <div class="code">@yield('code')</div>
        <h1 class="h3 mt-3">@yield('title')</h1>
        <p class="text-muted">@yield('message')</p>
        <a href="{{ url('/') }}" class="btn btn-success">Về trang chủ</a>
    </main>
</body>
</html>
