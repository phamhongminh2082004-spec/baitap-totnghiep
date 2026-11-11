{{-- THÙNG RÁC: khôi phục hoặc xóa vĩnh viễn bài của mình. --}}
@extends('layouts.app')
@section('title', 'Thùng rác')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Thùng rác</h1>
    <a href="{{ route('member.posts.index') }}" class="btn btn-outline-secondary">Quay lại bài viết</a>
</div>

<div class="table-responsive card border-0 shadow-sm">
    <table class="table align-middle mb-0">
        <thead><tr><th>Tiêu đề</th><th>Đã xóa lúc</th><th class="text-end">Thao tác</th></tr></thead>
        <tbody>
        @forelse ($posts as $post)
            <tr>
                <td>{{ $post->title }}</td>
                <td>{{ $post->deleted_at->format('d/m/Y H:i') }}</td>
                <td class="text-end">
                    <form action="{{ route('member.posts.restore', $post) }}" method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-success" type="submit">Khôi phục</button>
                    </form>
                    <form action="{{ route('member.posts.force', $post) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa vĩnh viễn? Không thể hoàn tác.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" type="submit">Xóa vĩnh viễn</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center text-muted py-4">Thùng rác trống.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $posts->links() }}</div>
@endsection
