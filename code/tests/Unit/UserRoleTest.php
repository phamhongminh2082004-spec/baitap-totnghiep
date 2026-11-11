<?php

/**
 * UNIT TEST vai trò người dùng.
 */

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    // [THÀNH CÔNG] role=admin -> isAdmin() = true.
    public function test_user_with_admin_role_is_admin(): void
    {
        $this->assertTrue((new User)->forceFill(['role' => 'admin'])->isAdmin());
    }

    // [THẤT BẠI] member hoặc chưa có role -> isAdmin() = false.
    public function test_member_or_user_without_role_is_not_admin(): void
    {
        $this->assertFalse((new User)->forceFill(['role' => 'member'])->isAdmin());
        $this->assertFalse((new User)->isAdmin());
    }

    // [THẤT BẠI] Không thể gán role qua mass-assignment.
    public function test_role_cannot_be_mass_assigned(): void
    {
        $user = new User(['name' => 'A', 'role' => 'admin']);

        $this->assertFalse($user->isAdmin());
    }
}
