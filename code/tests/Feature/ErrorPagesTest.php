<?php

/**
 * TEST TRANG LỖI 403 / 404 / 500 riêng (nhóm F).
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    // Trang 404 riêng hiển thị đúng.
    public function test_custom_404_page(): void
    {
        $this->get('/trang-khong-ton-tai')->assertNotFound()->assertSee('Không tìm thấy trang');
    }

    // Trang 403 riêng hiển thị đúng.
    public function test_custom_403_page(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')
            ->assertForbidden()
            ->assertSee('Bạn không có quyền truy cập');
    }

    // Trang 500 riêng hiển thị khi có lỗi máy chủ.
    public function test_custom_500_page(): void
    {
        config(['app.debug' => false]);
        Route::get('/_boom', fn () => throw new \RuntimeException('boom'));

        $this->get('/_boom')->assertStatus(500)->assertSee('Máy chủ gặp sự cố');
    }
}
