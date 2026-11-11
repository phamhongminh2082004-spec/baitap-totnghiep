{{-- FORM ĐĂNG NHẬP. --}}
@extends('layouts.app')
@section('title', 'Đăng nhập')

@section('content')
<div class="mx-auto" style="max-width: 420px">
    <h1 class="h3 mb-3">Đăng nhập</h1>
    <form method="POST" action="{{ route('login') }}" class="card card-body border-0 shadow-sm">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Mật khẩu</label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
        </div>
        <button class="btn btn-success" type="submit">Đăng nhập</button>
    </form>
    <p class="mt-3 text-muted">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a></p>
</div>
@endsection
