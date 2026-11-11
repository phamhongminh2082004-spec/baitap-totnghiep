{{-- QUẢN TRỊ: danh sách mọi bình luận và nút xóa. --}}
@extends('layouts.app')
@section('title', 'Tất cả bình luận')

@section('content')
<h1 class="h3 mb-3">Quản trị</h1>
@include('admin._nav')

<div class="table-responsive card border-0 shadow-sm">
    <table class="table align-middle mb-0">
        <thead><tr><th>Nội dung</th><th>Người viết</th><th>Bài viết</th><th>Lúc</th><th class="text-end">Thao tác</th></tr></thead>
        <tbody>
        @forelse ($comments as $comment)
            <tr>
                <td style="max-width: 360px">{{ \Illuminate\Support\Str::limit($comment->body, 120) }}</td>
                <td>{{ $comment->user->name }}</td>
                <td>{{ $comment->post->title }}</td>
                <td class="text-muted small">{{ $comment->created_at->format('d/m/Y H:i') }}</td>
                <td class="text-end">
                    <form action="{{ route('comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Xóa bình luận này?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" type="submit">Xóa</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">Chưa có bình luận nào.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $comments->links() }}</div>
@endsection
