<?php

/**
 * MODEL NGƯỜI DÙNG: thành viên và quản trị viên dùng chung bảng users, phân biệt bằng cột role.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Hai vai trò trong hệ thống. 'Khách' là người chưa đăng nhập nên không có bản ghi.
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MEMBER = 'member';

    // Cố ý KHÔNG đưa `role` vào fillable để không ai tự nâng quyền qua form.
    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    // 'hashed': mật khẩu tự động được băm (bcrypt) khi gán, không bao giờ lưu dạng chữ thường.
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Dùng ở middleware, policy và giao diện để kiểm tra quyền quản trị.
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    // Quan hệ: 1 người dùng có nhiều bài viết và nhiều bình luận.
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
