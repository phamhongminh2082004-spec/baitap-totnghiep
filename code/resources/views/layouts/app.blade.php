{{-- KHUNG GIAO DIỆN CHUNG: thanh điều hướng, thông báo flash, chân trang. Mọi trang đều @extends file này. --}}
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Trang chủ') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;700&family=Newsreader:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --ink: #1f2a28; --accent: #1f6f5c; --paper: #f4f6f4; }
        body { background: var(--paper); color: var(--ink); font-family: 'Be Vietnam Pro', system-ui, sans-serif; }
        h1, h2, h3, .brand { font-family: 'Newsreader', Georgia, serif; }
        .brand { font-size: 1.6rem; font-weight: 700; color: var(--accent) !important; }
        .btn-success, .badge.bg-success { --bs-btn-bg: var(--accent); --bs-btn-border-color: var(--accent); --bs-btn-hover-bg: #17594a; --bs-btn-hover-border-color: #17594a; }
        a { color: var(--accent); }
        .post-cover { height: 180px; object-fit: cover; background: #dfe7e3; }
        .post-body { font-size: 1.1rem; line-height: 1.8; max-width: 72ch; }
        .post-title a { color: var(--ink); text-decoration: none; }
        .post-title a:hover { color: var(--accent); }
        :focus-visible { outline: 3px solid #f0b429; outline-offset: 2px; }
    </style>
</head>
<body>
{{-- Thanh điều hướng: @auth / @else hiển thị menu khác nhau tùy đã đăng nhập hay chưa. --}}
<nav class="navbar navbar-expand-lg bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand brand" href="{{ route('home') }}">{{ config('app.name') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Mở menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <form class="d-flex ms-lg-3 my-2 my-lg-0" action="{{ route('home') }}" method="GET" role="search">
                <input class="form-control me-2" type="search" name="q" value="{{ request('q') }}" placeholder="Tìm bài viết" aria-label="Tìm bài viết">
                <button class="btn btn-outline-success" type="submit">Tìm</button>
            </form>
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                @auth
                    <li class="nav-item"><a class="nav-link" href="{{ route('member.posts.index') }}">Bài của tôi</a></li>
                    <li class="nav-item"><a class="btn btn-success btn-sm" href="{{ route('member.posts.create') }}">Viết bài</a></li>
                    {{-- Chỉ quản trị viên thấy link Quản trị (server vẫn chặn riêng bằng middleware admin). --}}
                    @if (auth()->user()->isAdmin())
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Quản trị</a></li>
                    @endif
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-link nav-link" type="submit">Đăng xuất ({{ auth()->user()->name }})</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Đăng nhập</a></li>
                    <li class="nav-item"><a class="btn btn-success btn-sm" href="{{ route('register') }}">Đăng ký</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    @endif
    {{-- Nội dung riêng của từng trang được chèn vào đây. --}}
    @yield('content')
</main>

<footer class="container py-4 text-muted small border-top">
    &copy; {{ date('Y') }} {{ config('app.name') }}
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
