<?php

/**
 * TRANG THỐNG KÊ của quản trị viên (nhóm D).
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            // Các con số hiển thị trên trang quản trị: thành viên, bài đã đăng, bình luận.
            'memberCount' => User::where('role', User::ROLE_MEMBER)->count(),
            'publishedCount' => Post::published()->count(),
            'commentCount' => Comment::count(),
            // 5 bài nhiều bình luận nhất: withCount tạo cột comments_count để sắp xếp.
            'topPosts' => Post::published()
                ->with('user')
                ->withCount('comments')
                ->orderByDesc('comments_count')
                ->orderByDesc('published_at')
                ->limit(5)
                ->get(),
        ]);
    }
}
