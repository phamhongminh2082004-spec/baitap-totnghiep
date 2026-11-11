<?php

/**
 * FACTORY tạo danh mục giả.
 */

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            // numerify tạo số ngẫu nhiên để tên luôn khác nhau (cột name là UNIQUE).
            'name' => 'Danh mục '.fake()->unique()->numerify('######'),
            // Slug sinh từ tên vừa tạo (closure nhận mảng thuộc tính đã sinh).
            'slug' => fn (array $a) => Str::slug($a['name']),
        ];
    }
}
