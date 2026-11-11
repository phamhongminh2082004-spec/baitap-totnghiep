{{-- QUẢN TRỊ: danh sách MỌI bài viết (cả nháp, hẹn giờ, thùng rác) và nút xóa bài vi phạm. --}}
@extends('layouts.app')
@section('title', 'Tất cả bài viết')

@section('content')
<h1 class="h3 mb-3">Quản trị</h1>
@include('admin._nav')

<div class="table-responsive card border-0 shadow-sm">
    <table class="table align-middle mb-0">
        <thead><tr><th>Tiêu đề</th><th>Tác giả</th><th>Danh mục</th><th>Trạng thái</th><th>Bình luận</th><th class="text-end">Thao tác</th></tr></thead>
        <tbody>
        @foreach ($posts as $post)
            <tr>
                <td>
                    @if ($post->trashed())
                        {{ $post->title }}
                    @else
                        <a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
                    @endif
                </td>
                <td>{{ $post->user->name }}</td>
                <td>{{ $post->category?->name ?? '—' }}</td>
                <td>
                    @if ($post->trashed())
                        <span class="badge text-bg-dark">Trong thùng rác</span>
                    @else
                        <span class="badge text-bg-{{ $post->statusBadge() }}">{{ $post->statusLabel() }}</span>
                    @endif
                </td>
                <td>{{ $post->comments_count }}</td>
                <td class="text-end">
                    <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Xóa vĩnh viễn bài viết này?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" type="submit">Xóa bài</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $posts->links() }}</div>
@endsection
