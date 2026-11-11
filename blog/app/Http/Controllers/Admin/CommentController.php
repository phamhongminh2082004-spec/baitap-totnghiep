<?php

/**
 * QUẢN TRỊ BÌNH LUẬN (nhóm D): xem danh sách. Việc xóa dùng chung CommentController::destroy.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(): View
    {
        // Nạp trước người viết và bài viết để hiển thị, tránh truy vấn N+1.
        $comments = Comment::with(['user', 'post'])->latest()->paginate(20);

        return view('admin.comments.index', compact('comments'));
    }
}
