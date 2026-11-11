<?php

/**
 * TEST QUẢN LÝ DANH MỤC (nhóm D): thêm, sửa, xóa.
 */

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    // [THÀNH CÔNG] Admin thêm danh mục, slug tự sinh.
    public function test_admin_can_create_category(): void
    {
        $this->actingAs($this->admin())->post(route('admin.categories.store'), ['name' => 'Công nghệ'])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['name' => 'Công nghệ', 'slug' => 'cong-nghe']);
    }

    // [THẤT BẠI] Thêm danh mục rỗng hoặc trùng tên -> báo lỗi.
    public function test_create_category_fails_when_empty_or_duplicate(): void
    {
        Category::factory()->create(['name' => 'Du lịch', 'slug' => 'du-lich']);

        $this->actingAs($this->admin())->post(route('admin.categories.store'), ['name' => ''])->assertSessionHasErrors('name');
        $this->actingAs($this->admin())->post(route('admin.categories.store'), ['name' => 'Du lịch'])->assertSessionHasErrors('name');

        $this->assertDatabaseCount('categories', 1);
    }

    // [THÀNH CÔNG] Sửa danh mục; giữ nguyên tên cũ không bị báo trùng với chính nó.
    public function test_admin_can_update_category_and_keep_own_name(): void
    {
        $category = Category::factory()->create(['name' => 'Cũ', 'slug' => 'cu']);
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.categories.update', $category), ['name' => 'Mới'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Mới', 'slug' => 'moi']);

        $this->actingAs($admin)->put(route('admin.categories.update', $category), ['name' => 'Mới'])
            ->assertSessionHasNoErrors(); // không tự báo trùng với chính nó
    }

    // [THẤT BẠI] Sửa thành tên đã có ở danh mục khác -> báo lỗi.
    public function test_update_fails_when_name_taken_by_another_category(): void
    {
        Category::factory()->create(['name' => 'A', 'slug' => 'a']);
        $b = Category::factory()->create(['name' => 'B', 'slug' => 'b']);

        $this->actingAs($this->admin())->put(route('admin.categories.update', $b), ['name' => 'A'])
            ->assertSessionHasErrors('name');
    }

    // [THÀNH CÔNG] Xóa danh mục; bài viết còn nguyên, category_id thành NULL.
    public function test_admin_can_delete_category_and_posts_keep_existing(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin())->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertNull($post->fresh()->category_id);
    }

    // [THẤT BẠI] Thành viên thêm/sửa/xóa danh mục -> 403.
    public function test_member_cannot_manage_categories(): void
    {
        $member = User::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($member)->post(route('admin.categories.store'), ['name' => 'X'])->assertForbidden();
        $this->actingAs($member)->put(route('admin.categories.update', $category), ['name' => 'X'])->assertForbidden();
        $this->actingAs($member)->delete(route('admin.categories.destroy', $category))->assertForbidden();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => $category->name]);
    }
}
