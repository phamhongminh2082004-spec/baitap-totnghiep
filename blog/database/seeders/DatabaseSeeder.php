<?php

/**
 * SEEDER: nạp DỮ LIỆU MẪU từ các file JSON trong database/data/ (đổi nội dung chỉ cần sửa JSON).
 */

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Support\Slugger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Nạp dữ liệu mẫu từ các file JSON trong database/data/.
 * Muốn đổi nội dung chỉ cần sửa file JSON, không cần sửa code.
 */
class DatabaseSeeder extends Seeder
{
    // Đọc 1 file JSON trong database/data/ và chuyển thành mảng PHP.
    private function load(string $file): array
    {
        return json_decode(file_get_contents(database_path("data/{$file}.json")), true);
    }

    public function run(): void
    {
        // Tài khoản demo (mật khẩu: "password")
        // 1) Tạo tài khoản demo: admin và các thành viên.
        User::factory()->admin()->create(['name' => 'Quản trị viên', 'email' => 'admin@example.com']);
        $demo = User::factory()->create(['name' => 'Thành viên demo', 'email' => 'member@example.com']);
        $authors = User::factory(4)->create()->push($demo)->values();

        // 2) Danh mục và tag từ JSON. mapWithKeys để sau này tra cứu theo TÊN.
        $categories = collect($this->load('categories'))
            ->mapWithKeys(fn ($name) => [$name => Category::create(['name' => $name, 'slug' => Str::slug($name)])]);

        $tags = collect($this->load('tags'))
            ->mapWithKeys(fn ($name) => [$name => Tag::create(['name' => $name, 'slug' => Str::slug($name)])]);

        // 3) Bài viết: chia đều cho các tác giả (i % số tác giả).
        foreach ($this->load('posts') as $i => $row) {
            $status = $row['status'];

            $post = Post::create([
                'user_id' => $authors[$i % $authors->count()]->id,
                'category_id' => $categories[$row['category']]->id,
                'title' => $row['title'],
                'slug' => Slugger::unique(Post::class, $row['title']),
                'body' => $row['body'],
                'status' => $status,
                // Ngày đăng: đã đăng = lùi N ngày | hẹn giờ = tiến N ngày | nháp = không có.
                'published_at' => match ($status) {
                    Post::PUBLISHED => now()->subDays($row['days']),
                    Post::SCHEDULED => now()->addDays($row['days']),
                    default => null,
                },
            ]);

            $post->tags()->attach(collect($row['tags'])->map(fn ($n) => $tags[$n]->id));
        }

        // Bình luận mẫu cho các bài đã đăng
        // 4) Bình luận mẫu: mỗi bài đã đăng nhận 0–3 bình luận từ người khác.
        $commentTexts = collect($this->load('comments'));
        $everyone = User::all();

        Post::published()->get()->each(function (Post $post) use ($commentTexts, $everyone) {
            $everyone->where('id', '!=', $post->user_id)->random(rand(0, 3))->each(
                fn (User $user) => Comment::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                    'body' => $commentTexts->random(),
                ])
            );
        });
    }
}
