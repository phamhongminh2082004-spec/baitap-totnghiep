<?php

/**
 * HÀM TIỆN ÍCH tạo slug (đường dẫn thân thiện) từ tiêu đề: 'Xin chào Việt Nam' -> 'xin-chao-viet-nam'.
 */

namespace App\Support;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Slugger
{
    /** "Xin chào Việt Nam!" => "xin-chao-viet-nam". Nếu không còn ký tự nào thì dùng $fallback. */
    public static function base(string $text, string $fallback = 'bai-viet'): string
    {
        // Str::slug() bỏ dấu tiếng Việt và ký tự lạ; nếu kết quả rỗng thì dùng $fallback.
        return Str::slug($text) ?: $fallback;
    }

    /** Tạo slug chưa bị trùng trong bảng của $modelClass (tính cả bản ghi đã xóa mềm). */
    public static function unique(string $modelClass, string $text, ?int $ignoreId = null, string $fallback = 'bai-viet'): string
    {
        $base = self::base($text, $fallback);
        $slug = $base;
        $i = 2;

        // Nếu trùng thì thêm hậu tố -2, -3... cho đến khi duy nhất.
        while (self::exists($modelClass, $slug, $ignoreId)) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private static function exists(string $modelClass, string $slug, ?int $ignoreId): bool
    {
        $query = $modelClass::query();

        // Tính cả bài trong thùng rác, vì cột slug có ràng buộc UNIQUE ở database.
        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            $query->withTrashed();
        }

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->where('slug', $slug)->exists();
    }
}
