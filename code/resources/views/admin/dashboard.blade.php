{{-- TRANG THỐNG KÊ: số thành viên, bài đã đăng, bình luận, top 5 bài nhiều bình luận. --}}
@extends('layouts.app')
@section('title', 'Thống kê')

@section('content')
<h1 class="h3 mb-3">Quản trị</h1>
@include('admin._nav')

<div class="row g-3 mb-4">
    @foreach ([['Thành viên', $memberCount], ['Bài đã đăng', $publishedCount], ['Bình luận', $commentCount]] as [$label, $value])
        <div class="col-md-4">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted">{{ $label }}</div>
                <div class="display-6 fw-bold">{{ number_format($value) }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h5">5 bài nhiều bình luận nhất</h2>
        <table class="table mb-0">
            <thead><tr><th>Bài viết</th><th>Tác giả</th><th class="text-end">Bình luận</th></tr></thead>
            <tbody>
            @forelse ($topPosts as $post)
                <tr>
                    <td><a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></td>
                    <td>{{ $post->user->name }}</td>
                    <td class="text-end">{{ $post->comments_count }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-muted text-center">Chưa có dữ liệu.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
