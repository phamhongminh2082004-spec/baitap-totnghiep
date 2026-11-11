<?php

/**
 * MODEL BÀI VIẾT (trung tâm của hệ thống): 3 trạng thái, xóa mềm, quan hệ, scope công khai.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    // SoftDeletes: xóa 'mềm' = chỉ đánh dấu deleted_at, bài vào THÙNG RÁC và khôi phục được.
    use HasFactory, SoftDeletes;

    // 3 trạng thái của bài: nháp / đã đăng / hẹn giờ.
    public const DRAFT = 'draft';
    public const PUBLISHED = 'published';
    public const SCHEDULED = 'scheduled';

    public const STATUSES = [self::DRAFT, self::PUBLISHED, self::SCHEDULED];

    // Các cột được phép gán hàng loạt.
    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'body', 'image', 'status', 'published_at',
    ];

    // published_at tự chuyển thành đối tượng ngày giờ (Carbon).
    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    /** URL bài viết dùng slug thay vì id. */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        // Xóa vĩnh viễn bài => xóa luôn file ảnh.
        static::forceDeleted(function (Post $post) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
        });
    }

    // ----- Quan hệ -----
    // Quan hệ: bài thuộc 1 tác giả, 1 danh mục; có nhiều tag (nhiều-nhiều) và nhiều bình luận.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // ----- Scope -----
    /** Bài đang hiển thị công khai. */
    // Query scope: Post::published() = trạng thái 'đã đăng' VÀ đã đến giờ đăng. Dùng ở trang chủ và thống kê.
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::PUBLISHED)
            ->where('published_at', '<=', now());
    }

    // ----- Trạng thái -----
    // Kiểm tra MỘT bài cụ thể có đang công khai không (dùng ở trang chi tiết và khi bình luận).
    public function isPublished(): bool
    {
        return $this->status === self::PUBLISHED
            && $this->published_at !== null
            && ! $this->published_at->isFuture();
    }

    public function isScheduled(): bool
    {
        return $this->status === self::SCHEDULED;
    }

    // Nhãn tiếng Việt và màu badge Bootstrap cho từng trạng thái (dùng trong view).
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::PUBLISHED => 'Đã đăng',
            self::SCHEDULED => 'Hẹn giờ',
            default => 'Nháp',
        };
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            self::PUBLISHED => 'success',
            self::SCHEDULED => 'warning',
            default => 'secondary',
        };
    }

    // ----- Hiển thị -----
    // Đường dẫn ảnh công khai (cần chạy php artisan storage:link).
    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    // Đoạn trích ngắn cho thẻ bài ở trang chủ (đã bỏ thẻ HTML).
    public function excerpt(int $limit = 150): string
    {
        return Str::limit(trim(strip_tags($this->body)), $limit);
    }
}
