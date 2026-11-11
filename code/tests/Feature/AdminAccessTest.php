<?php

/**
 * TEST QUYỀN VÀO /admin và THỐNG KÊ (nhóm D).
 */

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    // [THÀNH CÔNG] Admin thấy số thành viên, bài đã đăng, bình luận và top 5 bài nhiều bình luận nhất.
    public function test_admin_sees_statistics_and_top_5_most_commented_posts(): void
    {
        $admin = User::factory()->admin()->create();
        $m1 = User::factory()->create();
        $m2 = User::factory()->create();

        $posts = Post::factory(6)->create(['user_id' => $m1->id]);
        Post::factory()->draft()->create(['user_id' => $m2->id]);
        Comment::factory(3)->create(['post_id' => $posts[2]->id, 'user_id' => $m2->id]);
        Comment::factory(1)->create(['post_id' => $posts[4]->id, 'user_id' => $m2->id]);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertViewHas('memberCount', 2)
            ->assertViewHas('publishedCount', 6)   // bài nháp không tính
            ->assertViewHas('commentCount', 4)
            ->assertViewHas('topPosts', fn ($top) => $top->count() === 5
                && $top[0]->is($posts[2])
                && $top[1]->is($posts[4]));
    }

    // [THẤT BẠI] Thành viên vào mọi trang /admin -> 403.
    public function test_member_gets_403_on_every_admin_page(): void
    {
        $member = User::factory()->create();

        foreach (['/admin', '/admin/categories', '/admin/posts', '/admin/comments'] as $url) {
            $this->actingAs($member)->get($url)->assertForbidden();
        }
    }

    // [THẤT BẠI] Khách vào /admin -> chuyển tới trang đăng nhập.
    public function test_guest_is_redirected_to_login_from_admin(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/admin/categories')->assertRedirect(route('login'));
    }
}
