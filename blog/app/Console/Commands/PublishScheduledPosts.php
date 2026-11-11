<?php

/**
 * ARTISAN COMMAND tự đăng bài hẹn giờ (nhóm E). Được Task Scheduling gọi mỗi phút (xem routes/console.php).
 */

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledPosts extends Command
{
    // Tên lệnh để gọi: php artisan posts:publish-scheduled
    protected $signature = 'posts:publish-scheduled';

    protected $description = 'Đăng các bài hẹn giờ đã đến hạn';

    public function handle(): int
    {
        $count = 0;

        // Tìm các bài đang ở trạng thái 'hẹn giờ'.
        Post::where('status', Post::SCHEDULED)
            // ...mà đã đến (hoặc qua) giờ hẹn.
            ->where('published_at', '<=', now())
            // Xử lý theo lô 100 bài để tiết kiệm bộ nhớ.
            ->chunkById(100, function ($posts) use (&$count) {
                foreach ($posts as $post) {
                    // Chuyển 'hẹn giờ' -> 'đã đăng'. published_at giữ nguyên giờ đã hẹn.
                    $post->update(['status' => Post::PUBLISHED]);
                    Log::info('Tự động đăng bài hẹn giờ', ['post_id' => $post->id]);
                    $count++;
                }
            });

        $this->info("Đã đăng {$count} bài hẹn giờ.");

        return self::SUCCESS;
    }
}
