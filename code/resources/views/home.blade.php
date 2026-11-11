{{-- TRANG CHỦ: lưới bài viết + thanh bên lọc theo danh mục/tag. --}}
@extends('layouts.app')
@section('title', 'Trang chủ')

@section('content')
<div class="row g-4">
    <div class="col-lg-9">
        @if (request()->hasAny(['q', 'category', 'tag']))
            <div class="alert alert-light border d-flex justify-content-between align-items-center">
                <span>Đang lọc: {{ $posts->total() }} bài viết phù hợp</span>
                <a href="{{ route('home') }}">Xóa bộ lọc</a>
            </div>
        @endif

        <div class="row g-4">
            {{-- @forelse: lặp danh sách, nếu rỗng thì chạy nhánh @empty (báo không tìm thấy). --}}
            @forelse ($posts as $post)
                <div class="col-md-6 col-xl-4">@include('partials.post-card', ['post' => $post])</div>
            @empty
                <div class="col-12">
                    <div class="card card-body text-center text-muted">Không tìm thấy bài viết nào.</div>
                </div>
            @endforelse
        </div>

        {{-- Nút phân trang (giữ nguyên ?q=, ?category=... nhờ withQueryString). --}}
        <div class="mt-4">{{ $posts->links() }}</div>
    </div>

    <aside class="col-lg-3">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6">Danh mục</h2>
                <ul class="list-unstyled mb-0">
                    @foreach ($categories as $category)
                        <li class="d-flex justify-content-between">
                            <a href="{{ route('home', array_filter(['q' => request('q'), 'category' => $category->slug, 'tag' => request('tag')])) }}">{{ $category->name }}</a>
                            <span class="text-muted small">{{ $category->posts_count }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h6">Tag</h2>
                @foreach ($tags as $tag)
                    <a class="badge text-bg-light text-decoration-none mb-1"
                       href="{{ route('home', array_filter(['q' => request('q'), 'category' => request('category'), 'tag' => $tag->slug])) }}">#{{ $tag->name }}</a>
                @endforeach
            </div>
        </div>
    </aside>
</div>
@endsection
