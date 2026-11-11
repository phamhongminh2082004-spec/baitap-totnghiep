<?php

/**
 * TEST PHÂN QUYỀN SỬA BÀI (nhóm B): chỉ tác giả được sửa, chặn ở server (403).
 */

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(Post $post): array
    {
        return [
            'title' => 'Tieu de moi',
            'body' => 'Noi dung moi du dai de hop le.',
            'category_id' => $post->category_id,
            'status' => 'published',
        ];
    }

    // [THÀNH CÔNG] Tác giả mở form sửa và cập nhật bài của mình.
    public function test_author_can_open_edit_page_and_update_own_post(): void
    {
        $post = Post::factory()->create(['title' => 'Tieu de cu']);

        $this->actingAs($post->user)->get(route('member.posts.edit', $post))->assertOk()->assertSee('Tieu de cu');

        $this->actingAs($post->user)
            ->put(route('member.posts.update', $post), $this->payload($post))
            ->assertRedirect(route('member.posts.index'));

        $this->assertSame('Tieu de moi', $post->fresh()->title);
    }

    // [THẤT BẠI] Thành viên khác sửa bài người ta -> 403, dữ liệu không đổi.
    public function test_other_member_gets_403_when_editing_or_updating(): void
    {
        $post = Post::factory()->create(['title' => 'Tieu de cu']);
        $other = User::factory()->create();

        $this->actingAs($other)->get(route('member.posts.edit', $post))->assertForbidden();
        $this->actingAs($other)->put(route('member.posts.update', $post), $this->payload($post))->assertForbidden();

        $this->assertSame('Tieu de cu', $post->fresh()->title);
    }

    // [THẤT BẠI] Ngay cả khi gửi dữ liệu sai vẫn trả 403 (kiểm quyền trước khi validate).
    public function test_403_is_returned_even_when_payload_is_invalid(): void
    {
        $post = Post::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('member.posts.update', $post), ['title' => ''])
            ->assertForbidden();
    }

    // [THẤT BẠI] Quản trị viên cũng không sửa hộ bài người khác (chỉ tác giả).
    public function test_admin_cannot_edit_someone_elses_post(): void
    {
        $post = Post::factory()->create(['title' => 'Tieu de cu']);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('member.posts.update', $post), $this->payload($post))->assertForbidden();

        $this->assertSame('Tieu de cu', $post->fresh()->title);
    }

    // [THẤT BẠI] Khách vào trang sửa bị chuyển tới trang đăng nhập.
    public function test_guest_is_redirected_to_login(): void
    {
        $post = Post::factory()->create();

        $this->get(route('member.posts.edit', $post))->assertRedirect(route('login'));
        $this->put(route('member.posts.update', $post), $this->payload($post))->assertRedirect(route('login'));
    }
}
