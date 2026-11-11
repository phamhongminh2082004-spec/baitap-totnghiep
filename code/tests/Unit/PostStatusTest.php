<?php

/**
 * UNIT TEST logic trạng thái và trích đoạn của model Post.
 */

namespace Tests\Unit;

use App\Models\Post;
use Tests\TestCase;

class PostStatusTest extends TestCase
{
    // [THÀNH CÔNG] Bài đã đăng, ngày ở quá khứ -> isPublished() = true.
    public function test_published_post_with_past_date_is_published(): void
    {
        $post = new Post(['status' => Post::PUBLISHED, 'published_at' => now()->subMinute()]);

        $this->assertTrue($post->isPublished());
        $this->assertFalse($post->isScheduled());
        $this->assertSame('Đã đăng', $post->statusLabel());
    }

    // [THẤT BẠI] Nháp, hẹn giờ, hoặc ngày ở tương lai -> isPublished() = false.
    public function test_draft_scheduled_or_future_dated_post_is_not_published(): void
    {
        $draft = new Post(['status' => Post::DRAFT]);
        $scheduled = new Post(['status' => Post::SCHEDULED, 'published_at' => now()->addDay()]);
        $futurePublished = new Post(['status' => Post::PUBLISHED, 'published_at' => now()->addDay()]);

        $this->assertFalse($draft->isPublished());
        $this->assertFalse($scheduled->isPublished());
        $this->assertTrue($scheduled->isScheduled());
        $this->assertFalse($futurePublished->isPublished());
    }

    // Trích đoạn bỏ thẻ HTML và cắt ngắn đúng độ dài.
    public function test_excerpt_strips_html_and_truncates(): void
    {
        $post = new Post(['body' => '<b>'.str_repeat('a', 300).'</b>']);

        $this->assertStringNotContainsString('<b>', $post->excerpt(50));
        $this->assertLessThanOrEqual(53, mb_strlen($post->excerpt(50)));
        $this->assertSame('Ngan', (new Post(['body' => 'Ngan']))->excerpt());
    }
}
