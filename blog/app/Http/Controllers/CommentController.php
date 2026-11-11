<?php

/**
 * BÌNH LUẬN (nhóm C) và gửi email thông báo qua Queue (nhóm E).
 */

namespace App\Http\Controllers;

use App\Mail\NewCommentMail;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        // Chỉ được bình luận dưới bài đã đăng.
        abort_unless($post->isPublished(), 404);

        // Kiểm tra dữ liệu: bắt buộc, 2–2000 ký tự. Sai -> tự quay lại form kèm thông báo lỗi.
        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        // Lưu bình luận gắn với người đang đăng nhập.
        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        // Ghi log sự kiện quan trọng vào storage/logs/laravel.log.
        Log::info('Bình luận mới', ['comment_id' => $comment->id, 'post_id' => $post->id, 'user_id' => $request->user()->id]);

        // Gửi mail cho tác giả qua Queue (không gửi nếu tác giả tự bình luận bài của mình).
        if ($post->user_id !== $request->user()->id) {
            Mail::to($post->user)->queue(new NewCommentMail($comment));
        }

        return back()->with('success', 'Đã gửi bình luận.');
    }

    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        // Gọi CommentPolicy::delete. Không đủ quyền sẽ tự văng lỗi 403.
        Gate::authorize('delete', $comment);

        $comment->delete();

        Log::info('Xóa bình luận', ['comment_id' => $comment->id, 'by_user_id' => $request->user()->id]);

        return back()->with('success', 'Đã xóa bình luận.');
    }
}
