<?php

/**
 * TEST TRANG CÔNG KHAI (nhóm A): trang chủ, phân trang, chi tiết bài theo slug.
 */

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    // [THÀNH CÔNG] Trang chủ chỉ hiện bài đã đăng, mới nhất trước; nháp và hẹn giờ bị ẩn.
    public function test_home_lists_only_published_posts_newest_first(): void
    {
        Post::factory()->create(['title' => 'Bai cu', 'published_at' => now()->subDays(3)]);
        Post::factory()->create(['title' => 'Bai moi', 'published_at' => now()->subDay()]);
        Post::factory()->draft()->create(['title' => 'Ban nhap an']);
        Post::factory()->scheduled()->create(['title' => 'Bai hen gio an']);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Bai moi', 'Bai cu'])
            ->assertDontSee('Ban nhap an')
            ->assertDontSee('Bai hen gio an');
    }

    // [THÀNH CÔNG] 12 bài -> trang 1 có 9 bài, trang 2 có 3 bài.
    public function test_home_is_paginated(): void
    {
        Post::factory(12)->create();

        $this->get('/')->assertViewHas('posts', fn ($p) => $p->count() === 9 && $p->total() === 12);
        $this->get('/?page=2')->assertViewHas('posts', fn ($p) => $p->count() === 3);
    }

    // [THÀNH CÔNG] Trang chi tiết hiện đủ ảnh, tác giả, danh mục, tag, bình luận.
    public function test_show_page_displays_image_author_category_tags_and_comments(): void
    {
        $post = Post::factory()->create(['title' => 'Hello Laravel', 'image' => 'posts/cover.jpg']);
        $post->tags()->attach(Tag::factory()->create(['name' => 'laravel', 'slug' => 'laravel']));
        Comment::factory()->create(['post_id' => $post->id, 'body' => 'Binh luan dau tien']);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('Hello Laravel')
            ->assertSee('storage/posts/cover.jpg', false)
            ->assertSee($post->user->name)
            ->assertSee($post->category->name)
            ->assertSee('#laravel')
            ->assertSee('Binh luan dau tien');
    }

    // [THẤT BẠI] Khách và người khác vào bài nháp/hẹn giờ -> 404; [THÀNH CÔNG] tác giả xem trước được.
    public function test_draft_and_scheduled_posts_return_404_for_guests_but_author_can_preview(): void
    {
        $draft = Post::factory()->draft()->create();
        $scheduled = Post::factory()->scheduled()->create();

        $this->get(route('posts.show', $draft))->assertNotFound();
        $this->get(route('posts.show', $scheduled))->assertNotFound();
        $this->actingAs(User::factory()->create())->get(route('posts.show', $draft))->assertNotFound();

        $this->actingAs($draft->user)->get(route('posts.show', $draft))->assertOk();
    }

    // [THẤT BẠI] Slug không tồn tại -> 404.
    public function test_unknown_slug_returns_404(): void
    {
        $this->get('/bai-viet/khong-ton-tai')->assertNotFound();
    }
}
