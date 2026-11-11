<?php

/**
 * UNIT TEST hàm tạo slug (không cần gọi route/controller).
 */

namespace Tests\Unit;

use App\Models\Post;
use App\Support\Slugger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SluggerTest extends TestCase
{
    use RefreshDatabase;

    // [THÀNH CÔNG] Tiêu đề tiếng Việt -> slug không dấu.
    public function test_vietnamese_title_becomes_ascii_slug(): void
    {
        $this->assertSame('xin-chao-viet-nam', Slugger::base('Xin chào Việt Nam!'));
    }

    // [THẤT BẠI] Tiêu đề toàn ký tự đặc biệt -> dùng slug dự phòng.
    public function test_falls_back_when_title_has_no_usable_characters(): void
    {
        $this->assertSame('bai-viet', Slugger::base('!!! ???'));
        $this->assertSame('danh-muc', Slugger::base('***', 'danh-muc'));
    }

    // [THÀNH CÔNG] Slug trùng (kể cả bài trong thùng rác) -> thêm hậu tố số.
    public function test_unique_slug_gets_numeric_suffix_when_taken_even_by_trashed_post(): void
    {
        Post::factory()->create(['slug' => 'bai-viet-moi'])->delete();
        Post::factory()->create(['slug' => 'bai-viet-moi-2']);

        $this->assertSame('bai-viet-moi-3', Slugger::unique(Post::class, 'Bài viết mới'));
        $this->assertSame('mot-bai-khac', Slugger::unique(Post::class, 'Một bài khác'));
    }
}
