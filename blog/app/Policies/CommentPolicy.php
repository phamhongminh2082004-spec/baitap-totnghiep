<?php

/**
 * PHÂN QUYỀN BÌNH LUẬN (yêu cầu nhóm C): ai được xóa bình luận.
 */

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

/** Người viết bình luận, tác giả bài viết và quản trị viên được xóa bình luận. */
class CommentPolicy
{
    public function delete(User $user, Comment $comment): bool
    {
        // 3 nhóm được xóa: người viết bình luận, tác giả bài viết, quản trị viên.
        return $user->id === $comment->user_id
            || $user->id === $comment->post->user_id
            || $user->isAdmin();
    }
}
