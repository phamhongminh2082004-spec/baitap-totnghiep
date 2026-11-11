<?php

/**
 * MODEL BÌNH LUẬN: thuộc về 1 bài viết và 1 người viết.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    // Các cột được phép gán hàng loạt (chống mass-assignment).
    protected $fillable = ['post_id', 'user_id', 'body'];

    public function post(): BelongsTo
    {
        // withTrashed(): vẫn lấy được bài cha kể cả khi bài đã vào thùng rác.
        return $this->belongsTo(Post::class)->withTrashed();
    }

    // Người viết bình luận.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
