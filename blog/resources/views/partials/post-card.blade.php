{{-- THẺ BÀI VIẾT dùng lại ở trang chủ (ảnh, danh mục, tiêu đề, trích đoạn, tác giả). --}}
<article class="card h-100 border-0 shadow-sm">
    @if ($post->imageUrl())
        <img src="{{ $post->imageUrl() }}" class="card-img-top post-cover" alt="{{ $post->title }}">
    @else
        <div class="post-cover card-img-top"></div>
    @endif
    <div class="card-body d-flex flex-column">
        @if ($post->category)
            <a href="{{ route('home', ['category' => $post->category->slug]) }}" class="badge text-bg-light text-decoration-none align-self-start mb-2">{{ $post->category->name }}</a>
        @endif
        <h2 class="h5 post-title"><a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></h2>
        <p class="text-muted small flex-grow-1">{{ $post->excerpt() }}</p>
        <div class="small text-muted">
            {{ $post->user->name }} &middot; {{ optional($post->published_at)->format('d/m/Y') }}
            &middot; {{ $post->comments_count ?? $post->comments()->count() }} bình luận
        </div>
    </div>
</article>
