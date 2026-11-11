<?php

/**
 * FACTORY tạo bài viết giả. Có các state tiện dụng: published / draft / scheduled.
 */

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/** @extends Factory<Post> */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Nếu test không chỉ định, factory tự tạo luôn tác giả và danh mục.
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->unique()->sentence(6),
            // Thêm đuôi ngẫu nhiên để slug không bao giờ trùng.
            'slug' => fn (array $a) => Str::slug($a['title']).'-'.Str::lower(Str::random(6)),
            'body' => implode("\n\n", fake()->paragraphs(4)),
            'image' => null,
            'status' => Post::PUBLISHED,
            'published_at' => fake()->dateTimeBetween('-30 days', '-1 minute'),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => Post::PUBLISHED,
            'published_at' => fake()->dateTimeBetween('-30 days', '-1 minute'),
        ]);
    }

    // Các state: Post::factory()->draft() / ->scheduled() / ->published().
    public function draft(): static
    {
        return $this->state(['status' => Post::DRAFT, 'published_at' => null]);
    }

    public function scheduled(?Carbon $at = null): static
    {
        return $this->state(fn () => [
            'status' => Post::SCHEDULED,
            'published_at' => $at ?? now()->addDays(2),
        ]);
    }
}
