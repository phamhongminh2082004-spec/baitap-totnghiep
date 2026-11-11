<?php

/**
 * TEST TÌM KIẾM & LỌC (nhóm A): từ khóa, danh mục, tag.
 */

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchFilterTest extends TestCase
{
    use RefreshDatabase;

    // [THÀNH CÔNG] Tìm theo từ khóa chỉ ra bài phù hợp.
    public function test_search_by_keyword_finds_matching_posts(): void
    {
        Post::factory()->create(['title' => 'Hoc Laravel co ban']);
        Post::factory()->create(['title' => 'Nau an ngon']);

        $this->get('/?q=Laravel')
            ->assertOk()
            ->assertSee('Hoc Laravel co ban')
            ->assertDontSee('Nau an ngon');
    }

    // [THẤT BẠI] Từ khóa không khớp -> báo không tìm thấy.
    public function test_search_without_match_shows_empty_message(): void
    {
        Post::factory()->create(['title' => 'Hoc Laravel co ban']);

        $this->get('/?q=zzzkhongco')
            ->assertOk()
            ->assertSee('Không tìm thấy bài viết')
            ->assertDontSee('Hoc Laravel co ban');
    }

    // [THÀNH CÔNG + THẤT BẠI] Lọc đúng danh mục; danh mục không tồn tại -> không có bài.
    public function test_filter_by_category(): void
    {
        $a = Category::factory()->create();
        $b = Category::factory()->create();
        Post::factory()->create(['title' => 'Bai thuoc A', 'category_id' => $a->id]);
        Post::factory()->create(['title' => 'Bai thuoc B', 'category_id' => $b->id]);

        $this->get('/?category='.$a->slug)->assertSee('Bai thuoc A')->assertDontSee('Bai thuoc B');
        $this->get('/?category=khong-co')->assertDontSee('Bai thuoc A')->assertDontSee('Bai thuoc B');
    }

    // [THÀNH CÔNG + THẤT BẠI] Lọc đúng tag; tag không tồn tại -> không có bài.
    public function test_filter_by_tag(): void
    {
        $tag = Tag::factory()->create();
        $withTag = Post::factory()->create(['title' => 'Bai co tag']);
        Post::factory()->create(['title' => 'Bai khong tag']);
        $withTag->tags()->attach($tag);

        $this->get('/?tag='.$tag->slug)->assertSee('Bai co tag')->assertDontSee('Bai khong tag');
        $this->get('/?tag=khong-co')->assertDontSee('Bai co tag');
    }

    // [THẤT BẠI] Tìm kiếm không bao giờ lộ bài nháp.
    public function test_search_never_leaks_unpublished_posts(): void
    {
        Post::factory()->draft()->create(['title' => 'Ban nhap bi mat']);

        $this->get('/?q=bi mat')->assertDontSee('Ban nhap bi mat');
    }
}
