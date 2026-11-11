<?php

/**
 * TEST ARTISAN COMMAND đăng bài hẹn giờ và Task Scheduling (nhóm E).
 */

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PublishScheduledPostsTest extends TestCase
{
    use RefreshDatabase;

    // [THÀNH CÔNG] Bài hẹn giờ đã đến hạn được đăng và hiện ở trang chủ.
    public function test_command_publishes_due_scheduled_posts(): void
    {
        $due = Post::factory()->scheduled(now()->subMinute())->create(['title' => 'Bai den han']);

        $this->artisan('posts:publish-scheduled')->expectsOutput('Đã đăng 1 bài hẹn giờ.')->assertSuccessful();

        $this->assertSame(Post::PUBLISHED, $due->fresh()->status);
        $this->get('/')->assertSee('Bai den han');
    }

    // [THẤT BẠI] Bài chưa đến hạn và bài nháp không bị đụng tới.
    public function test_command_does_not_touch_future_scheduled_posts_or_drafts(): void
    {
        $future = Post::factory()->scheduled(now()->addHour())->create();
        $draft = Post::factory()->draft()->create();

        $this->artisan('posts:publish-scheduled')->expectsOutput('Đã đăng 0 bài hẹn giờ.')->assertSuccessful();

        $this->assertSame(Post::SCHEDULED, $future->fresh()->status);
        $this->assertSame(Post::DRAFT, $draft->fresh()->status);
    }

    // [THÀNH CÔNG] Lệnh đã được đăng ký trong Task Scheduling.
    public function test_command_is_registered_in_scheduler(): void
    {
        Artisan::call('schedule:list');

        $this->assertStringContainsString('posts:publish-scheduled', Artisan::output());
    }
}
