<?php

/**
 * MIGRATION: bảng bài viết (khóa ngoại, 3 trạng thái, xóa mềm) và bảng trung gian post_tag.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            // Khóa ngoại tới users. cascadeOnDelete: xóa user thì xóa luôn bài của họ.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Khóa ngoại tới categories. nullOnDelete: xóa danh mục thì bài được giữ lại với category_id = NULL.
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('body');
            $table->string('image')->nullable();
            $table->string('status', 20)->default('draft'); // draft | published | scheduled
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            // Thêm cột deleted_at phục vụ THÙNG RÁC (xóa mềm).
            $table->softDeletes();

            // Chỉ mục giúp truy vấn 'bài đã đăng mới nhất' chạy nhanh.
            $table->index(['status', 'published_at']);
        });

        // Bảng trung gian cho quan hệ nhiều-nhiều giữa bài viết và tag.
        Schema::create('post_tag', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            // Khóa chính kép: một tag chỉ gắn 1 lần cho mỗi bài.
            $table->primary(['post_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('posts');
    }
};
