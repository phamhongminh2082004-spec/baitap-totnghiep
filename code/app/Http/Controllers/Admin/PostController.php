<?php

/**
 * QUẢN TRỊ BÀI VIẾT (nhóm D): xem mọi bài, xóa bài vi phạm.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PostController extends Controller
{
    /** Danh sách MỌI bài viết (cả nháp, hẹn giờ, đã vào thùng rác). */
    public function index(): View
    {
        // withTrashed(): admin thấy cả bài nháp, hẹn giờ và bài đã vào thùng rác.
        $posts = Post::withTrashed()
            ->with(['user', 'category'])
            ->withCount('comments')
            ->latest()
            ->paginate(15);

        return view('admin.posts.index', compact('posts'));
    }

    /** Xóa bài vi phạm: xóa vĩnh viễn (tác giả không khôi phục lại được). */
    public function destroy(Request $request, Post $post): RedirectResponse
    {
        // Xóa vĩnh viễn: bài vi phạm thì tác giả không khôi phục lại được.
        $post->forceDelete();

        Log::warning('Admin xóa bài vi phạm', ['post_id' => $post->id, 'title' => $post->title, 'admin_id' => $request->user()->id]);

        return back()->with('success', 'Đã xóa bài viết.');
    }
}
