<?php

/**
 * Nơi khai báo TASK SCHEDULING (yêu cầu nhóm E): lệnh nào chạy định kỳ.
 */

use Illuminate\Support\Facades\Schedule;

// Mỗi phút kiểm tra và đăng các bài hẹn giờ đã đến hạn.
// Chạy local: php artisan schedule:work  |  Production: cron `* * * * * php artisan schedule:run`
// Mỗi phút chạy lệnh đăng bài hẹn giờ; withoutOverlapping() tránh 2 lần chạy chồng nhau.
Schedule::command('posts:publish-scheduled')->everyMinute()->withoutOverlapping();
