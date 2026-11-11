# Giải thích code để trình bày với thầy cô

Mọi file code đều có **chú thích tiếng Việt**: dòng đầu file nói file đó làm gì, bên trong có comment giải thích từng đoạn quan trọng.
Test có nhãn **[THÀNH CÔNG]** / **[THẤT BẠI]** để thấy mỗi chức năng đều được kiểm tra cả hai trường hợp.

## 1. Kiến trúc (mô hình MVC của Laravel)

```
Trình duyệt -> routes/web.php -> Middleware (auth / admin)
           -> Controller (+ FormRequest kiểm tra dữ liệu, Policy kiểm tra quyền)
           -> Model (Eloquent) <-> Database
           -> View (Blade) -> trả HTML về trình duyệt
```

## 2. Yêu cầu của đề -> nằm ở file nào

| Yêu cầu | File chính |
|---|---|
| A. Trang chủ, phân trang, tìm kiếm, lọc | `HomeController.php`, `home.blade.php`, `Post::scopePublished` |
| A. Chi tiết bài theo slug | `PostController.php`, `posts/show.blade.php`, `Post::getRouteKeyName`, `Support/Slugger.php` |
| B. Đăng ký / đăng nhập / đăng xuất | `Auth/RegisterController.php`, `Auth/LoginController.php` |
| B. Tạo bài có ảnh, danh mục, nhiều tag | `Member/PostController.php`, `PostRequest.php`, `member/posts/form.blade.php` |
| B. 3 trạng thái (nháp / đã đăng / hẹn giờ) | `Post.php` (hằng số), `Member/PostController::fillPost` |
| B. Chỉ tác giả được sửa (chặn ở server) | `Policies/PostPolicy.php`, `PostRequest::authorize()`, `Gate::authorize` |
| B. Thùng rác, khôi phục, xóa vĩnh viễn | `SoftDeletes` trong `Post.php`, `Member/PostController` (destroy/restore/forceDelete) |
| C. Bình luận + quyền xóa | `CommentController.php`, `Policies/CommentPolicy.php` |
| D. /admin chỉ quản trị viên | `Middleware/EnsureUserIsAdmin.php`, `routes/web.php` |
| D. Thống kê, danh mục, kiểm duyệt | `Admin/DashboardController`, `Admin/CategoryController`, `Admin/PostController`, `Admin/CommentController` |
| E. Tự đăng bài hẹn giờ | `Console/Commands/PublishScheduledPosts.php` + `routes/console.php` (Task Scheduling) |
| E. Email khi có bình luận (Queue) | `Mail/NewCommentMail.php` (ShouldQueue), `CommentController::store` |
| F. Migration, khóa ngoại, Seeder, Factory | `database/migrations`, `database/seeders`, `database/factories`, `database/data/*.json` |
| F. Trang lỗi 403/404/500 | `resources/views/errors/` |
| F. Ghi log | `Log::info/warning` trong các controller -> `storage/logs/laravel.log` |
| F. Test (>= 8 Feature, >= 2 Unit) | `tests/Feature` (12 file), `tests/Unit` (3 file) |

## 3. Các điểm bảo mật nên nhấn mạnh khi trình bày

- **Phân quyền ở server**: Policy + FormRequest, không chỉ ẩn nút (có test 403).
- **Chống mass-assignment**: `role` không nằm trong `$fillable` nên không tự nâng quyền admin được.
- **Chống XSS**: nội dung in ra bằng `e()` / `{{ }}`.
- **CSRF**: mọi form có `@csrf`.
- **Chống dò mật khẩu**: `throttle:10,1` ở route đăng nhập; mật khẩu băm bằng bcrypt.
- **Session fixation**: `session()->regenerate()` sau khi đăng nhập.

## 4. Về dataset (`database/data/*.json`)
JSON không ghi chú được, nên giải thích ở đây: `categories.json` (5 danh mục), `tags.json` (8 tag),
`posts.json` (15 bài; trường `status` = published/draft/scheduled, `days` = số ngày lùi/tiến so với hôm nay),
`comments.json` (8 câu bình luận mẫu). `DatabaseSeeder` đọc các file này.
