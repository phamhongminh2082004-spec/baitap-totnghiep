<?php

/**
 * KIỂM TRA DỮ LIỆU + PHÂN QUYỀN khi tạo/sửa bài. Chạy TRƯỚC controller.
 */

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    /**
     * Khi SỬA bài: chặn ngay ở server nếu không phải tác giả (trả 403 trước cả bước validate).
     */
    public function authorize(): bool
    {
        $post = $this->route('post');

        // Route có {post} = đang SỬA -> kiểm tra PostPolicy::update (chặn ở server). Không có = đang TẠO -> chỉ cần đăng nhập.
        return $post instanceof Post
            ? (bool) $this->user()?->can('update', $post)
            : $this->user() !== null;
    }

    // Quy tắc kiểm tra dùng chung cho cả TẠO và SỬA bài.
    public function rules(): array
    {
        $scheduled = $this->input('status') === Post::SCHEDULED;

        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10'],
            'category_id' => ['required', 'exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'new_tags' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(Post::STATUSES)],
            // Chỉ bắt buộc và phải ở tương lai khi chọn trạng thái 'hẹn giờ'.
            'published_at' => [
                Rule::requiredIf($scheduled),
                'nullable',
                'date',
                Rule::when($scheduled, ['after:now']),
            ],
            // Ảnh: phải là file ảnh, tối đa 2048 KB (2MB).
            'image' => ['nullable', 'image', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }
}
