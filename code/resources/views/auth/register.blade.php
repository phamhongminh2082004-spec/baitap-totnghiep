{{-- FORM ĐĂNG KÝ. --}}
@extends('layouts.app')
@section('title', 'Đăng ký')

@section('content')
<div class="mx-auto" style="max-width: 420px">
    <h1 class="h3 mb-3">Tạo tài khoản</h1>
    <form method="POST" action="{{ route('register') }}" class="card card-body border-0 shadow-sm">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="name">Họ tên</label>
            <input id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required autofocus>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Mật khẩu (tối thiểu 8 ký tự)</label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Nhập lại mật khẩu</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
        </div>
        <button class="btn btn-success" type="submit">Đăng ký</button>
    </form>
</div>
@endsection
