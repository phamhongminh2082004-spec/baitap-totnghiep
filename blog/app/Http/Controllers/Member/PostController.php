<?php

/**
 * QUẢN LÝ BÀI VIẾT CỦA THÀNH VIÊN (nhóm B): tạo, sửa, xóa vào thùng rác, khôi phục, xóa vĩnh viễn.
 */

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Support\Slugger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    // Danh sách bài của CHÍNH MÌNH, có lọc theo trạng thái (?status=...).
    public function index(Request $request): View
    {
        $posts = Post::where('user_id', $request->user()->id)
            ->with('category')
            ->withCount('comments')
            ->when(in_array($request->query('status'), Post::STATUSES, true),
                fn ($q) => $q->where('status', $request->query('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $trashCount = Post::onlyTrashed()->where('user_id', $request->user()->id)->count();

        return view('member.posts.index', compact('posts', 'trashCount'));
    }

    public function create(): View
    {
        return view('member.posts.form', $this->formData(new Post(['status' => Post::DRAFT])));
    }

    // Tạo bài: dữ liệu đã được PostRequest kiểm tra xong mới vào tới đây.
    public function store(PostRequest $request): RedirectResponse
    {
        $post = new Post();
        $post->user_id = $request->user()->id;
        // Slug tạo 1 lần từ tiêu đề và đảm bảo không trùng.
        $post->slug = Slugger::unique(Post::class, $request->validated('title'));

        $this->fillPost($post, $request);
        $post->save();
        $this->syncTags($post, $request);

        // Ghi log sự kiện tạo bài.
        Log::info('Tạo bài viết', ['post_id' => $post->id, 'status' => $post->status, 'user_id' => $post->user_id]);

        return redirect()->route('member.posts.index')->with('success', 'Đã lưu bài viết.');
    }

    // Form sửa: Gate::authorize kiểm tra tác giả, không phải tác giả -> 403.
    public function edit(Post $post): View
    {
        Gate::authorize('update', $post);

        return view('member.posts.form', $this->formData($post->load('tags')));
    }

    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        // Phòng thủ 2 lớp: PostRequest đã chặn, controller chặn thêm lần nữa.
        Gate::authorize('update', $post); // chặn lần nữa ở tầng controller

        $this->fillPost($post, $request);
        $post->save();
        $this->syncTags($post, $request);

        Log::info('Cập nhật bài viết', ['post_id' => $post->id, 'status' => $post->status, 'user_id' => $request->user()->id]);

        return redirect()->route('member.posts.index')->with('success', 'Đã cập nhật bài viết.');
    }

    /** Xóa = đưa vào thùng rác. */
    public function destroy(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        // delete() trên model dùng SoftDeletes = chỉ gán deleted_at (đưa vào thùng rác).
        $post->delete();

        Log::info('Chuyển bài vào thùng rác', ['post_id' => $post->id, 'user_id' => $request->user()->id]);

        return redirect()->route('member.posts.index')->with('success', 'Đã chuyển bài vào thùng rác.');
    }

    // Thùng rác: chỉ các bài đã xóa mềm CỦA MÌNH (onlyTrashed).
    public function trash(Request $request): View
    {
        $posts = Post::onlyTrashed()
            ->where('user_id', $request->user()->id)
            ->with('category')
            ->latest('deleted_at')
            ->paginate(10);

        return view('member.posts.trash', compact('posts'));
    }

    public function restore(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('restore', $post);
        abort_unless($post->trashed(), 404);

        // Khôi phục: xóa dấu deleted_at, bài trở lại danh sách.
        $post->restore();

        Log::info('Khôi phục bài viết', ['post_id' => $post->id, 'user_id' => $request->user()->id]);

        return redirect()->route('member.posts.trash')->with('success', 'Đã khôi phục bài viết.');
    }

    public function forceDelete(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('forceDelete', $post);
        abort_unless($post->trashed(), 404);

        // Xóa vĩnh viễn khỏi database (sự kiện forceDeleted trong Post sẽ xóa luôn file ảnh).
        $post->forceDelete();

        Log::warning('Xóa vĩnh viễn bài viết', ['post_id' => $post->id, 'user_id' => $request->user()->id]);

        return redirect()->route('member.posts.trash')->with('success', 'Đã xóa vĩnh viễn bài viết.');
    }

    // ---------------------------------------------------------------

    private function formData(Post $post): array
    {
        return [
            'post' => $post,
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ];
    }

    // Gán dữ liệu form vào model, xử lý logic 3 trạng thái và ảnh bìa.
    private function fillPost(Post $post, PostRequest $request): void
    {
        $data = $request->validated();

        $post->title = $data['title'];
        $post->body = $data['body'];
        $post->category_id = $data['category_id'];
        $post->status = $data['status'];

        // Nháp: không có ngày | Hẹn giờ: lấy ngày người dùng chọn | Đã đăng: giữ ngày cũ hoặc lấy giờ hiện tại.
        $post->published_at = match ($data['status']) {
            Post::SCHEDULED => Carbon::parse($data['published_at']),
            // Đã đăng: giữ ngày đăng cũ nếu đã ở quá khứ, ngược lại đăng ngay bây giờ.
            Post::PUBLISHED => ($post->published_at && $post->published_at->isPast()) ? $post->published_at : now(),
            default => null,
        };

        if ($request->boolean('remove_image') && $post->image) {
            Storage::disk('public')->delete($post->image);
            $post->image = null;
        }

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            // Lưu ảnh vào storage/app/public/posts (ảnh cũ đã được xóa ở trên).
            $post->image = $request->file('image')->store('posts', 'public');
        }
    }

    // Đồng bộ tag: tag đã chọn + tag mới nhập (tự tạo nếu chưa có). sync() ghi vào bảng post_tag.
    private function syncTags(Post $post, PostRequest $request): void
    {
        $ids = collect($request->input('tags', []))->map(fn ($id) => (int) $id);

        // Cho phép nhập thêm tag mới, cách nhau bằng dấu phẩy.
        collect(explode(',', (string) $request->input('new_tags')))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique()
            ->each(function (string $name) use ($ids) {
                $slug = Str::slug($name);
                if ($slug === '') {
                    return;
                }
                $ids->push(Tag::firstOrCreate(['slug' => $slug], ['name' => Str::limit($name, 50, '')])->id);
            });

        $post->tags()->sync($ids->unique()->all());
    }
}
