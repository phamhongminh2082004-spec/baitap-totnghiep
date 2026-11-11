<?php

/**
 * TEST KIỂM DUYỆT của admin (nhóm D): xóa bài và bình luận vi phạm.
 */

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModerationTest extends TestCase
{
    use RefreshDatabase;

    // [THÀNH CÔNG] Admin thấy mọi bài: công khai, nháp, hẹn giờ, thùng rác.
    public function test_admin_sees_all_posts_including_drafts_and_trashed(): void
    {
        Post::factory()->create(['title' => 'Bai cong khai']);
        Post::factory()->draft()->create(['title' => 'Bai nhap']);
        Post::factory()->scheduled()->create(['title' => 'Bai hen gio']);
        Post::factory()->create(['title' => 'Bai trong thung rac'])->delete();

        $this->actingAs(User::factory()->admin()->create())->get(route('admin.posts.index'))
            ->assertOk()
            ->assertSee('Bai cong khai')
            ->assertSee('Bai nhap')
            ->assertSee('Bai hen gio')
            ->assertSee('Bai trong thung rac');
    }

    // [THÀNH CÔNG] Admin xóa vĩnh viễn bài vi phạm.
    public function test_admin_can_delete_violating_post_permanently(): void
    {
        $post = Post::factory()->create();

        $this->actingAs(User::factory()->admin()->create())->delete(route('admin.posts.destroy', $post))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    // [THẤT BẠI] Thành viên (kể cả tác giả) gọi route xóa của admin -> 403.
    public function test_member_cannot_delete_post_via_admin_route(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($post->user)->delete(route('admin.posts.destroy', $post))->assertForbidden();
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    // [THÀNH CÔNG] Admin xem và xóa bất kỳ bình luận nào.
    public function test_admin_can_list_and_delete_any_comment(): void
    {
        $comment = Comment::factory()->create(['body' => 'Binh luan vi pham']);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.comments.index'))->assertOk()->assertSee('Binh luan vi pham');
        $this->actingAs($admin)->delete(route('comments.destroy', $comment))->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    // [THẤT BẠI] Thành viên xem danh sách bình luận của admin -> 403.
    public function test_member_cannot_see_admin_comment_list(): void
    {
        Comment::factory()->create();

        $this->actingAs(User::factory()->create())->get(route('admin.comments.index'))->assertForbidden();
    }
}
