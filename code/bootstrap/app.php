<?php

/**
 * Khởi tạo ứng dụng: khai báo file route, đăng ký tên middleware 'admin' và nơi cấu hình xử lý lỗi.
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    // Khai báo file route: web (trang web), console (lệnh + lịch chạy nền) và /up (kiểm tra máy chủ còn sống).
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Đặt tên ngắn 'admin' cho middleware kiểm tra quyền quản trị (dùng ở routes/web.php).
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Trang lỗi 403/404/500 nằm ở resources/views/errors/*.blade.php
    })->create();
