<?php

/**
 * FACTORY tạo người dùng giả (dùng trong test và seeder).
 */

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            // Mật khẩu mặc định của mọi user mẫu là 'password' (băm 1 lần cho nhanh).
            'password' => static::$password ??= Hash::make('password'),
            'role' => User::ROLE_MEMBER,
            'remember_token' => Str::random(10),
        ];
    }

    // State: User::factory()->admin() tạo quản trị viên.
    public function admin(): static
    {
        return $this->state(['role' => User::ROLE_ADMIN]);
    }
}
