<?php

/**
 * PHÂN QUYỀN BÀI VIẾT (yêu cầu nhóm B): chỉ TÁC GIẢ được sửa/xóa/khôi phục bài của mình.
 */

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

/** Chỉ TÁC GIẢ được sửa / xóa / khôi phục bài của mình (kể cả quản trị viên không sửa hộ). */
class PostPolicy
{
    // So sánh id người đang đăng nhập với user_id của bài: khớp mới được qua, không thì 403.
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
