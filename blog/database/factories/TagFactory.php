<?php

/**
 * FACTORY tạo tag giả.
 */

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Tag> */
class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            // numerify tạo số ngẫu nhiên để tên luôn khác nhau.
            'name' => 'Tag '.fake()->unique()->numerify('######'),
            // Slug sinh từ tên.
            'slug' => fn (array $a) => Str::slug($a['name']),
        ];
    }
}
