<?php

/**
 * TEST BÌNH LUẬN (nhóm C) và EMAIL QUA QUEUE (nhóm E).
 */

namespace Tests\Feature;

use App\Mail\NewCommentMail;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    // [THÀNH CÔNG] Thành viên bình luận dưới bài đã đăng.
    public function test_member_can_comment_on_published_post(): void
    {
        Mail::fake();
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('comments.store', $post), ['body' => 'Bai hay qua!'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('comments', ['post_id' => $post->id, 'user_id' => $user->id, 'body' => 'Bai hay qua!']);
    }

    // [THẤT BẠI] Khách không bình luận được.
    public function test_guest_cannot_comment(): void
    {
        $post = Post::factory()->create();

        $this->post(route('comments.store', $post), ['body' => 'Khach binh luan'])->assertRedirect(route('login'));
        $this->assertDatabaseCount('comments', 0);
    }

    // [THẤT BẠI] Không bình luận được dưới bài nháp/hẹn giờ (404).
    public function test_cannot_comment_on_draft_or_scheduled_post(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('comments.store', Post::factory()->draft()->create()), ['body' => 'Hello'])->assertNotFound();
        $this->actingAs($user)->post(route('comments.store', Post::factory()->scheduled()->create()), ['body' => 'Hello'])->assertNotFound();
        $this->assertDatabaseCount('comments', 0);
    }

    // [THẤT BẠI] Bình luận rỗng hoặc quá 2000 ký tự -> báo lỗi.
    public function test_comment_validation_fails_for_empty_or_too_long_body(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('comments.store', $post), ['body' => ''])->assertSessionHasErrors('body');
        $this->actingAs($user)->post(route('comments.store', $post), ['body' => str_repeat('a', 2001)])->assertSessionHasErrors('body');
        $this->assertDatabaseCount('comments', 0);
    }

    // ---- Xóa bình luận ----

    // [THÀNH CÔNG] Người viết, tác giả bài và admin đều xóa được bình luận.
    public function test_commenter_post_author_and_admin_can_delete_comment(): void
    {
        $post = Post::factory()->create();
        $commenter = User::factory()->create();
        $admin = User::factory()->admin()->create();

        foreach ([$commenter, $post->user, $admin] as $actor) {
            $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $commenter->id]);

            $this->actingAs($actor)->delete(route('comments.destroy', $comment))->assertSessionHasNoErrors();
            $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
        }
    }

    // [THẤT BẠI] Thành viên không liên quan xóa bình luận -> 403.
    public function test_unrelated_member_cannot_delete_comment(): void
    {
        $comment = Comment::factory()->create();

        $this->actingAs(User::factory()->create())->delete(route('comments.destroy', $comment))->assertForbidden();
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    // [THẤT BẠI] Khách xóa bình luận -> chuyển tới trang đăng nhập.
    public function test_guest_cannot_delete_comment(): void
    {
        $comment = Comment::factory()->create();

        $this->delete(route('comments.destroy', $comment))->assertRedirect(route('login'));
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    // ---- Email qua Queue ----

    // [THÀNH CÔNG] Có bình luận mới -> email được đưa vào Queue gửi cho tác giả.
    public function test_email_is_queued_to_post_author_on_new_comment(): void
    {
        Mail::fake();
        $post = Post::factory()->create();

        $this->actingAs(User::factory()->create())->post(route('comments.store', $post), ['body' => 'Co mail khong?']);

        Mail::assertQueued(NewCommentMail::class, fn ($mail) => $mail->hasTo($post->user->email));
    }

    // [THẤT BẠI] Tác giả tự bình luận, hoặc bình luận không hợp lệ -> không gửi email.
    public function test_no_email_when_author_comments_on_own_post_or_validation_fails(): void
    {
        Mail::fake();
        $post = Post::factory()->create();

        $this->actingAs($post->user)->post(route('comments.store', $post), ['body' => 'Tu binh luan']);
        $this->actingAs(User::factory()->create())->post(route('comments.store', $post), ['body' => '']);

        Mail::assertNothingQueued();
    }

    // [THÀNH CÔNG] Nội dung email có bình luận và link bài viết.
    public function test_mail_content_contains_comment_and_post_link(): void
    {
        $comment = Comment::factory()->create(['body' => 'Noi dung binh luan']);

        $mail = new NewCommentMail($comment);

        $mail->assertSeeInHtml('Noi dung binh luan');
        $mail->assertSeeInHtml(route('posts.show', $comment->post));
    }
}
