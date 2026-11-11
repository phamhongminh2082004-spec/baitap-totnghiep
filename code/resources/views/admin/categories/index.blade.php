{{-- QUẢN LÝ DANH MỤC: form thêm + bảng danh mục. --}}
@extends('layouts.app')
@section('title', 'Quản lý danh mục')

@section('content')
<h1 class="h3 mb-3">Quản trị</h1>
@include('admin._nav')

<form method="POST" action="{{ route('admin.categories.store') }}" class="row g-2 mb-4">
    @csrf
    <div class="col-sm-6 col-md-4">
        <input name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Tên danh mục mới" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-auto"><button class="btn btn-success" type="submit">Thêm danh mục</button></div>
</form>

<div class="card border-0 shadow-sm">
    <table class="table align-middle mb-0">
        <thead><tr><th>Tên</th><th>Slug</th><th>Số bài</th><th class="text-end">Thao tác</th></tr></thead>
        <tbody>
        @forelse ($categories as $category)
            <tr>
                <td>{{ $category->name }}</td>
                <td class="text-muted">{{ $category->slug }}</td>
                <td>{{ $category->posts_count }}</td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-success" href="{{ route('admin.categories.edit', $category) }}">Sửa</a>
                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa danh mục này? Các bài thuộc danh mục sẽ không còn danh mục.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" type="submit">Xóa</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-4">Chưa có danh mục nào. Hãy thêm danh mục đầu tiên ở trên.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
