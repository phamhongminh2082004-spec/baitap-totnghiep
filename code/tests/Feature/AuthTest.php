<?php

/**
 * TEST TÀI KHOẢN (nhóm B): đăng ký, đăng nhập, đăng xuất.
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // [THÀNH CÔNG] Đăng ký hợp lệ -> tạo user (role member) và tự đăng nhập.
    public function test_user_can_register(): void
    {
        $this->post('/dang-ky', [
            'name' => 'Nguyen Van A',
            'email' => 'a@example.com',
            'password' => 'matkhau123',
            'password_confirmation' => 'matkhau123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'a@example.com', 'role' => 'member']);
    }

    // [THẤT BẠI] Đăng ký sai (thiếu tên, trùng email, mật khẩu yếu/không khớp) -> báo lỗi, không tạo user.
    public function test_registration_fails_with_invalid_data(): void
    {
        User::factory()->create(['email' => 'trung@example.com']);

        $this->post('/dang-ky', [
            'name' => '',
            'email' => 'trung@example.com',
            'password' => '123',
            'password_confirmation' => 'khac',
        ])->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    // [THẤT BẠI] Gửi lén role=admin khi đăng ký vẫn chỉ thành member (chống nâng quyền).
    public function test_cannot_register_as_admin_by_sending_role_field(): void
    {
        $this->post('/dang-ky', [
            'name' => 'Hacker', 'email' => 'h@example.com', 'role' => 'admin',
            'password' => 'matkhau123', 'password_confirmation' => 'matkhau123',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'h@example.com', 'role' => 'member']);
    }

    // [THÀNH CÔNG] Đăng nhập đúng email/mật khẩu.
    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create(['email' => 'u@example.com']); // mật khẩu "password"

        $this->post('/dang-nhap', ['email' => 'u@example.com', 'password' => 'password'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    // [THẤT BẠI] Sai mật khẩu -> báo lỗi, vẫn là khách.
    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'u@example.com']);

        $this->from('/dang-nhap')
            ->post('/dang-nhap', ['email' => 'u@example.com', 'password' => 'sai-mat-khau'])
            ->assertRedirect('/dang-nhap')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    // [THÀNH CÔNG] Đăng xuất được; [THẤT BẠI] khách bấm đăng xuất bị chuyển tới trang đăng nhập.
    public function test_user_can_logout_and_guest_cannot(): void
    {
        $this->actingAs(User::factory()->create())->post('/dang-xuat')->assertRedirect(route('home'));
        $this->assertGuest();

        $this->post('/dang-xuat')->assertRedirect(route('login'));
    }
}
