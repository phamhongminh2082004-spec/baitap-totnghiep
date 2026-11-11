<?php

/**
 * BẢNG ĐỊNH TUYẾN: URL nào -> controller nào, và ai được vào (khách / thành viên / quản trị viên).
 */

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Member;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

// ---------- Công khai ----------
// Trang chủ: danh sách bài đã đăng + tìm kiếm/lọc (ai cũng xem được).
Route::get('/', [HomeController::class, 'index'])->name('home');
// Chi tiết bài viết. {post} được Laravel tự tìm theo SLUG (xem Post::getRouteKeyName).
Route::get('/bai-viet/{post}', [PostController::class, 'show'])->name('posts.show');

// ---------- Khách ----------
// Nhóm route chỉ dành cho KHÁCH (đã đăng nhập mà vào sẽ bị chuyển hướng).
Route::middleware('guest')->group(function () {
    Route::get('/dang-ky', [RegisterController::class, 'create'])->name('register');
    Route::post('/dang-ky', [RegisterController::class, 'store']);
    Route::get('/dang-nhap', [LoginController::class, 'create'])->name('login');
    // throttle:10,1 = tối đa 10 lần/phút, chống dò mật khẩu hàng loạt.
    Route::post('/dang-nhap', [LoginController::class, 'store'])->middleware('throttle:10,1');
});

// ---------- Thành viên ----------
// Nhóm route yêu cầu ĐÃ ĐĂNG NHẬP (chưa đăng nhập -> tự chuyển tới trang đăng nhập).
Route::middleware('auth')->group(function () {
    Route::post('/dang-xuat', [LoginController::class, 'destroy'])->name('logout');

    // Bình luận: thêm / xóa. Quyền xóa được kiểm tra bằng CommentPolicy.
    Route::post('/bai-viet/{post}/binh-luan', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/binh-luan/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Quản lý bài viết của thành viên: các URL dạng /my/posts/...
    Route::prefix('my/posts')->name('member.posts.')->group(function () {
        Route::get('/', [Member\PostController::class, 'index'])->name('index');
        Route::get('/create', [Member\PostController::class, 'create'])->name('create');
        Route::post('/', [Member\PostController::class, 'store'])->name('store');
        // Danh sách thùng rác của tôi.
        Route::get('/trash', [Member\PostController::class, 'trash'])->name('trash');
        Route::get('/{post}/edit', [Member\PostController::class, 'edit'])->name('edit');
        Route::put('/{post}', [Member\PostController::class, 'update'])->name('update');
        Route::delete('/{post}', [Member\PostController::class, 'destroy'])->name('destroy');
        // withTrashed(): cho phép tìm cả bài đã xóa mềm để khôi phục / xóa vĩnh viễn.
        Route::patch('/{post}/restore', [Member\PostController::class, 'restore'])->name('restore')->withTrashed();
        Route::delete('/{post}/force', [Member\PostController::class, 'forceDelete'])->name('force')->withTrashed();
    });
});

// ---------- Quản trị ----------
// Khu vực /admin: phải đăng nhập (auth) VÀ là quản trị viên (admin), nếu không -> lỗi 403.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');
    // resource() tạo sẵn các route index/store/edit/update/destroy cho danh mục.
    Route::resource('categories', Admin\CategoryController::class)->except('show');
    Route::get('/posts', [Admin\PostController::class, 'index'])->name('posts.index');
    Route::delete('/posts/{post}', [Admin\PostController::class, 'destroy'])->name('posts.destroy')->withTrashed();
    Route::get('/comments', [Admin\CommentController::class, 'index'])->name('comments.index');
});
