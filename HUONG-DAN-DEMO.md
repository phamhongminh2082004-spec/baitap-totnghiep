# Bản đầy đủ để chạy demo

Cần cài sẵn: **PHP 8.3+** (bật extension `gd`, `pdo_sqlite`, `mbstring`), **Composer**.

## Cách nhanh (tự động)
- Windows: chạy `setup.bat` → vào thư mục `blog` → chạy `run-demo.bat`
- Mac/Linux: `./setup.sh` → `cd blog` → `./run-demo.sh`

Mở http://localhost:8000

## Cách thủ công
```bash
composer create-project laravel/laravel blog
# chép TOÀN BỘ nội dung thư mục code/ vào blog/ (ghi đè)
cd blog
php artisan migrate --seed
php artisan storage:link
php artisan serve              # terminal 1
php artisan queue:work         # terminal 2
php artisan schedule:work      # terminal 3
php artisan test               # chạy test
```
(`.env` mặc định của Laravel đã dùng SQLite, `QUEUE_CONNECTION=database`, `MAIL_MAILER=log` nên không cần sửa.)

## Tài khoản (mật khẩu: `password`)
- Quản trị viên: admin@example.com
- Thành viên: member@example.com

## Kịch bản demo gợi ý
1. Trang chủ: phân trang, tìm kiếm "Laravel", lọc danh mục/tag.
2. Đăng nhập member → Viết bài (ảnh, nhiều tag) → thử trạng thái nháp / hẹn giờ (đặt 1–2 phút sau, xem cửa sổ scheduler tự đăng).
3. Xóa bài → Thùng rác → khôi phục / xóa vĩnh viễn.
4. Bình luận bài của người khác → mở `storage/logs/laravel.log` thấy email do Queue gửi.
5. Thử sửa bài người khác bằng URL (`/my/posts/<slug>/edit`) → trang 403.
6. Đăng nhập admin → /admin: thống kê, danh mục, xóa bài/bình luận.
7. `php artisan test` → toàn bộ test xanh.
