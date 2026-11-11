<?php

/**
 * Cấu hình chung khi ứng dụng khởi động.
 */

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Dùng giao diện phân trang kiểu Bootstrap 5 cho {{ $posts->links() }}.
        Paginator::useBootstrapFive();
    }
}
