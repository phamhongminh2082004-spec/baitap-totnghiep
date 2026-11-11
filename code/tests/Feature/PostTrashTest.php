<?php

/**
 * TEST THÙNG RÁC (nhóm B): xóa mềm, khôi phục, xóa vĩnh viễn.
 */

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostTrashTest extends TestCase
{
    use RefreshDatabase;

    // [THÀNH CÔNG] Xóa bài -> vào thùng rác, biến mất khỏi trang chủ và trang chi tiết.
    public function test_author_can_move_post_to_trash(): void
    {
        $post = Post::factory()->create(['title' => 'Bai se bi xoa']);

        $this->actingAs($post->user)->delete(route('member.posts.destroy', $post))
            ->assertRedirect(route('member.posts.index'));

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
        $this->get('/')->assertDontSee('Bai se bi xoa');
        $this->get(route('posts.show', $post))->assertNotFound();
        $this->actingAs($post->user)->get(route('member.posts.trash'))->assertSee('Bai se bi xoa');
    }

    // [THẤT BẠI] Người khác xóa/khôi phục/xóa vĩnh viễn bài -> 403.
    public function test_other_member_cannot_trash_restore_or_force_delete(): void
    {
        $post = Post::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($other)->delete(route('member.posts.destroy', $post))->assertForbidden();
        $this->assertNotSoftDeleted('posts', ['id' => $post->id]);

        $post->delete();

        $this->actingAs($other)->patch(route('member.posts.restore', $post))->assertForbidden();
        $this->actingAs($other)->delete(route('member.posts.force', $post))->assertForbidden();
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    // [THÀNH CÔNG] Tác giả khôi phục bài từ thùng rác.
    public function test_author_can_restore_post(): void
    {
        $post = Post::factory()->create();
        $post->delete();

        $this->actingAs($post->user)->patch(route('member.posts.restore', $post))
            ->assertRedirect(route('member.posts.trash'));

        $this->assertNotSoftDeleted('posts', ['id' => $post->id]);
    }

    // [THẤT BẠI] Khôi phục bài không nằm trong thùng rác -> 404.
    public function test_restoring_a_post_that_is_not_in_trash_returns_404(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($post->user)->patch(route('member.posts.restore', $post))->assertNotFound();
    }

    // [THÀNH CÔNG] Xóa vĩnh viễn: mất khỏi DB và xóa luôn file ảnh.
    public function test_author_can_force_delete_and_image_is_removed(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('posts/a.jpg', 'x');
        $post = Post::factory()->create(['image' => 'posts/a.jpg']);
        $post->delete();

        $this->actingAs($post->user)->delete(route('member.posts.force', $post))
            ->assertRedirect(route('member.posts.trash'));

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        Storage::disk('public')->assertMissing('posts/a.jpg');
    }

    // [THẤT BẠI] Không xóa vĩnh viễn được bài chưa vào thùng rác.
    public function test_cannot_force_delete_a_post_that_is_not_in_trash(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($post->user)->delete(route('member.posts.force', $post))->assertNotFound();
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }
}
