<?php

/**
 * TRANG CHI TIẾT BÀI VIẾT (nhóm A), truy cập bằng slug.
 */

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function show(Request $request, Post $post): View
    {
        $user = $request->user();

        // Bài chưa công khai: chỉ tác giả hoặc quản trị viên được xem trước, người khác nhận 404.
        if (! $post->isPublished()) {
            abort_unless($user && ($user->id === $post->user_id || $user->isAdmin()), 404);
        }

        $post->load(['user', 'category', 'tags']);

        // Phân trang bình luận 10/trang, mới nhất trước.
        $comments = $post->comments()->with('user')->latest()->paginate(10);
        // Gán sẵn bài cha cho mỗi bình luận để CommentPolicy không phải truy vấn lại.
        $comments->getCollection()->each->setRelation('post', $post);

        return view('posts.show', compact('post', 'comments'));
    }
}
