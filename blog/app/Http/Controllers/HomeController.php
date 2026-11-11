<?php

/**
 * TRANG CHỦ (nhóm A): bài đã đăng, mới nhất trước, phân trang, tìm kiếm, lọc danh mục/tag.
 */

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        // Lấy từ khóa tìm kiếm từ địa chỉ ?q=...
        $keyword = trim((string) $request->query('q', ''));

        $posts = Post::query()
            ->published()
            // Chỉ lấy bài đã đăng. with() nạp trước quan hệ để tránh lỗi truy vấn N+1; withCount đếm số bình luận.
            ->with(['user', 'category', 'tags'])
            ->withCount('comments')
            // when(): chỉ thêm điều kiện nếu người dùng có nhập. Tìm theo tiêu đề HOẶC nội dung (LIKE).
            ->when($keyword !== '', function ($query) use ($keyword) {
                $like = '%'.$keyword.'%';
                $query->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('body', 'like', $like));
            })
            // Lọc theo danh mục (?category=slug) và theo tag (?tag=slug) bằng whereHas.
            ->when($request->filled('category'), fn ($query) => $query->whereHas(
                'category', fn ($c) => $c->where('slug', $request->query('category'))
            ))
            ->when($request->filled('tag'), fn ($query) => $query->whereHas(
                'tags', fn ($t) => $t->where('slug', $request->query('tag'))
            ))
            // Mới nhất trước, 9 bài/trang. withQueryString() giữ lại ?q=... khi chuyển trang.
            ->latest('published_at')
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        // Gửi dữ liệu sang view. categories/tags dùng cho thanh bên bộ lọc.
        return view('home', [
            'posts' => $posts,
            'keyword' => $keyword,
            'categories' => Category::withCount(['posts' => fn ($q) => $q->published()])->orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }
}
