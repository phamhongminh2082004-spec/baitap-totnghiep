{{-- FORM SỬA DANH MỤC. --}}
@extends('layouts.app')
@section('title', 'Sửa danh mục')

@section('content')
<h1 class="h3 mb-3">Quản trị</h1>
@include('admin._nav')

<form method="POST" action="{{ route('admin.categories.update', $category) }}" class="card card-body border-0 shadow-sm" style="max-width: 480px">
    @csrf @method('PUT')
    <label class="form-label" for="name">Tên danh mục</label>
    <input id="name" name="name" value="{{ old('name', $category->name) }}" class="form-control @error('name') is-invalid @enderror" required>
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <div class="mt-3 d-flex gap-2">
        <button class="btn btn-success" type="submit">Lưu thay đổi</button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Hủy</a>
    </div>
</form>
@endsection
