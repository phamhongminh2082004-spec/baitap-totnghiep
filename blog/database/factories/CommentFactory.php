<?php

/**
 * FACTORY tạo bình luận giả.
 */

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Comment> */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Tự tạo bài viết và người viết nếu không chỉ định.
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'body' => fake()->sentence(12),
        ];
    }
}
