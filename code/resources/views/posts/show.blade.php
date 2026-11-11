{{-- TRANG CHI TIẾT BÀI: ảnh, tác giả, danh mục, tag và khu vực bình luận. --}}
@extends('layouts.app')
@section('title', $post->title)

@section('content')
<article class="mx-auto" style="max-width: 820px">
    @unless ($post->isPublished())
        <div class="alert alert-warning">
            Bài viết này chưa được đăng công khai (trạng thái: {{ $post->statusLabel() }}). Chỉ bạn nhìn thấy trang này.
        </div>
    @endunless

    <h1 class="display-6 fw-bold">{{ $post->title }}</h1>
    <p class="text-muted">
        Bởi <strong>{{ $post->user->name }}</strong>
        @if ($post->published_at) &middot; {{ $post->published_at->format('d/m/Y H:i') }} @endif
        @if ($post->category)
            &middot; <a href="{{ route('home', ['category' => $post->category->slug]) }}">{{ $post->category->name }}</a>
        @endif
    </p>

    {{-- @can gọi PostPolicy: chỉ tác giả thấy nút Sửa (server vẫn chặn riêng). --}}
    @can('update', $post)
        <a href="{{ route('member.posts.edit', $post) }}" class="btn btn-sm btn-outline-success mb-3">Sửa bài viết</a>
    @endcan

    @if ($post->imageUrl())
        <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" class="img-fluid rounded mb-4 w-100">
    @endif

    {{-- e() escape HTML để chống XSS, sau đó nl2br đổi xuống dòng thành <br>. --}}
    <div class="post-body">{!! nl2br(e($post->body)) !!}</div>

    <div class="mt-4">
        @foreach ($post->tags as $tag)
            <a class="badge text-bg-light text-decoration-none" href="{{ route('home', ['tag' => $tag->slug]) }}">#{{ $tag->name }}</a>
        @endforeach
    </div>

    <hr class="my-5">

    <section id="comments">
        <h2 class="h4">Bình luận ({{ $comments->total() }})</h2>

        @if ($post->isPublished())
            @auth
                <form action="{{ route('comments.store', $post) }}" method="POST" class="mb-4">
                    @csrf
                    <textarea name="body" rows="3" class="form-control @error('body') is-invalid @enderror" placeholder="Viết bình luận của bạn" required>{{ old('body') }}</textarea>
                    @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <button class="btn btn-success mt-2" type="submit">Gửi bình luận</button>
                </form>
            @else
                <p class="text-muted"><a href="{{ route('login') }}">Đăng nhập</a> để bình luận.</p>
            @endauth
        @endif

        @forelse ($comments as $comment)
            <div class="card border-0 shadow-sm mb-2">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $comment->user->name }}</strong>
                        <span class="text-muted small">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="mb-1">{!! nl2br(e($comment->body)) !!}</p>
                    {{-- Nút xóa bình luận chỉ hiện với người có quyền (CommentPolicy). --}}
                    @can('delete', $comment)
                        <form action="{{ route('comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Xóa bình luận này?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-link btn-sm text-danger p-0" type="submit">Xóa</button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <p class="text-muted">Chưa có bình luận nào.</p>
        @endforelse

        {{ $comments->links() }}
    </section>
</article>
@endsection
