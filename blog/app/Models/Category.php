<?php

/**
 * MODEL DANH MỤC (do quản trị viên quản lý).
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    // Quan hệ: 1 danh mục có nhiều bài viết.
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
