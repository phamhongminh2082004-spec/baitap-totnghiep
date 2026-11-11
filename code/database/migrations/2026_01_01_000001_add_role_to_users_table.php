<?php

/**
 * MIGRATION: thêm cột 'role' (member/admin) vào bảng users có sẵn của Laravel.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cột vai trò, mặc định 'member'. Quản trị viên được tạo bằng seeder.
            $table->string('role', 20)->default('member')->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
