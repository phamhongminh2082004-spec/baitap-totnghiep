<?php

/**
 * MODEL TAG (nhãn). Một bài có nhiều tag, một tag gắn cho nhiều bài.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    // Quan hệ NHIỀU-NHIỀU với bài viết, thông qua bảng trung gian post_tag.
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}
