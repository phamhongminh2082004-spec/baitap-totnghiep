{{-- NỘI DUNG EMAIL gửi cho tác giả khi có bình luận mới (dùng cùng với app/Mail/NewCommentMail.php). --}}
<!doctype html>
<html lang="vi">
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #222;">
    <p>Xin chào {{ $comment->post->user->name }},</p>
    <p>
        <strong>{{ $comment->user->name }}</strong> vừa bình luận dưới bài viết
        <a href="{{ route('posts.show', $comment->post) }}">{{ $comment->post->title }}</a>:
    </p>
    <blockquote style="border-left: 4px solid #ccc; margin: 0; padding-left: 12px;">
        {{ $comment->body }}
    </blockquote>
    <p><a href="{{ route('posts.show', $comment->post) }}">Xem bình luận</a></p>
</body>
</html>
