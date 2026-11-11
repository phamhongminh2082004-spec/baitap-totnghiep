<?php

/**
 * TEST TẠO BÀI (nhóm B): ảnh, danh mục, nhiều tag, 3 trạng thái, hẹn giờ.
 */

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostManagementTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'title' => 'Bai viet dau tien',
            'body' => 'Noi dung bai viet kha dai de qua validate.',
            'category_id' => Category::factory()->create()->id,
            'status' => 'published',
        ], $override);
    }

    // [THÀNH CÔNG] Tạo bài có ảnh, danh mục, nhiều tag (cả tag mới nhập).
    public function test_member_can_create_post_with_image_category_and_multiple_tags(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $tags = Tag::factory(2)->create();

        $this->actingAs($user)->post(route('member.posts.store'), $this->payload([
            'tags' => $tags->pluck('id')->all(),
            'new_tags' => 'Tag moi, Mot tag nua',
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ]))->assertRedirect(route('member.posts.index'));

        $post = Post::where('slug', 'bai-viet-dau-tien')->firstOrFail();
        $this->assertSame($user->id, $post->user_id);
        $this->assertCount(4, $post->tags);
        $this->assertNotNull($post->published_at);
        Storage::disk('public')->assertExists($post->image);
    }

    // [THẤT BẠI] Thiếu tiêu đề/nội dung/danh mục, trạng thái lạ -> báo lỗi, không lưu.
    public function test_create_post_fails_with_invalid_data(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('member.posts.store'), ['title' => '', 'body' => '', 'status' => 'bay-ba'])
            ->assertSessionHasErrors(['title', 'body', 'category_id', 'status']);

        $this->assertDatabaseCount('posts', 0);
    }

    // [THẤT BẠI] Upload file PDF thay ảnh, tag không tồn tại -> báo lỗi.
    public function test_create_post_rejects_non_image_and_unknown_tag(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('member.posts.store'), $this->payload([
                'image' => UploadedFile::fake()->create('file.pdf', 10, 'application/pdf'),
                'tags' => [999],
            ]))
            ->assertSessionHasErrors(['image', 'tags.0']);
    }

    // [THẤT BẠI] Khách không tạo được bài, bị chuyển tới trang đăng nhập.
    public function test_guest_cannot_create_post(): void
    {
        $this->post(route('member.posts.store'), $this->payload())->assertRedirect(route('login'));
        $this->get(route('member.posts.create'))->assertRedirect(route('login'));
        $this->assertDatabaseCount('posts', 0);
    }

    // [THÀNH CÔNG] Lưu nháp thành công nhưng không hiện ở trang chủ.
    public function test_draft_post_is_saved_but_not_public(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('member.posts.store'), $this->payload(['status' => 'draft']));

        $this->assertDatabaseHas('posts', ['slug' => 'bai-viet-dau-tien', 'status' => 'draft', 'published_at' => null]);
        $this->get('/')->assertDontSee('Bai viet dau tien');
    }

    // [THẤT BẠI] Hẹn giờ mà thiếu ngày hoặc ngày ở quá khứ -> báo lỗi.
    public function test_scheduled_post_requires_future_date(): void
    {
        $user = $this->actingAs(User::factory()->create());

        $user->post(route('member.posts.store'), $this->payload(['status' => 'scheduled']))
            ->assertSessionHasErrors('published_at');

        $user->post(route('member.posts.store'), $this->payload(['status' => 'scheduled', 'published_at' => now()->subHour()->format('Y-m-d H:i')]))
            ->assertSessionHasErrors('published_at');

        $this->assertDatabaseCount('posts', 0);
    }

    // [THÀNH CÔNG] Hẹn giờ hợp lệ được lưu, chưa hiện công khai cho tới khi đến giờ.
    public function test_scheduled_post_is_saved_and_hidden_until_due(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('member.posts.store'), $this->payload([
                'status' => 'scheduled',
                'published_at' => now()->addDay()->format('Y-m-d H:i'),
            ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('posts', ['slug' => 'bai-viet-dau-tien', 'status' => 'scheduled']);
        $this->get('/')->assertDontSee('Bai viet dau tien');
    }

    // [THÀNH CÔNG] Hai bài trùng tiêu đề -> slug tự thêm hậu tố -2.
    public function test_slug_is_unique_when_titles_repeat(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post(route('member.posts.store'), $this->payload());
        $this->post(route('member.posts.store'), $this->payload());

        $this->assertDatabaseHas('posts', ['slug' => 'bai-viet-dau-tien']);
        $this->assertDatabaseHas('posts', ['slug' => 'bai-viet-dau-tien-2']);
    }
}
