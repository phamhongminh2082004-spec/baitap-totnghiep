{{-- DANH SÁCH BÀI CỦA TÔI: lọc theo trạng thái, nút sửa / xóa (vào thùng rác). --}}
@extends('layouts.app')
@section('title', 'Bài viết của tôi')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="h3 mb-0">Bài viết của tôi</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('member.posts.trash') }}" class="btn btn-outline-secondary">Thùng rác ({{ $trashCount }})</a>
        <a href="{{ route('member.posts.create') }}" class="btn btn-success">Viết bài mới</a>
    </div>
</div>

<form method="GET" class="mb-3">
    <select name="status" class="form-select w-auto d-inline-block" onchange="this.form.submit()" aria-label="Lọc trạng thái">
        <option value="">Tất cả trạng thái</option>
        <option value="draft" @selected(request('status') === 'draft')>Nháp</option>
        <option value="published" @selected(request('status') === 'published')>Đã đăng</option>
        <option value="scheduled" @selected(request('status') === 'scheduled')>Hẹn giờ</option>
    </select>
</form>

<div class="table-responsive card border-0 shadow-sm">
    <table class="table align-middle mb-0">
        <thead><tr><th>Tiêu đề</th><th>Danh mục</th><th>Trạng thái</th><th>Bình luận</th><th class="text-end">Thao tác</th></tr></thead>
        <tbody>
        @forelse ($posts as $post)
            <tr>
                <td>
                    <a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
                    @if ($post->isScheduled())
                        <div class="small text-muted">Đăng lúc {{ $post->published_at->format('d/m/Y H:i') }}</div>
                    @endif
                </td>
                <td>{{ $post->category?->name ?? '—' }}</td>
                <td><span class="badge text-bg-{{ $post->statusBadge() }}">{{ $post->statusLabel() }}</span></td>
                <td>{{ $post->comments_count }}</td>
                <td class="text-end">
                    <a href="{{ route('member.posts.edit', $post) }}" class="btn btn-sm btn-outline-success">Sửa</a>
                    <form action="{{ route('member.posts.destroy', $post) }}" method="POST" class="d-inline" onsubmit="return confirm('Chuyển bài này vào thùng rác?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" type="submit">Xóa</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">Bạn chưa có bài viết nào. <a href="{{ route('member.posts.create') }}">Viết bài đầu tiên</a></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $posts->links() }}</div>
@endsection
