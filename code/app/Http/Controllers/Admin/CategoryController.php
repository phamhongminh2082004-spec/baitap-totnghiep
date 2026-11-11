<?php

/**
 * QUẢN LÝ DANH MỤC (nhóm D): thêm, sửa, xóa.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\Slugger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('posts')->orderBy('name')->get(),
        ]);
    }

    // Form thêm nằm ngay trang danh sách nên chuyển hướng về index.
    public function create(): RedirectResponse
    {
        return redirect()->route('admin.categories.index');
    }

    // Thêm: tên bắt buộc và không trùng; slug tự sinh từ tên.
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
        ]);

        $category = Category::create([
            'name' => $data['name'],
            'slug' => Slugger::unique(Category::class, $data['name'], null, 'danh-muc'),
        ]);

        Log::info('Thêm danh mục', ['category_id' => $category->id, 'admin_id' => $request->user()->id]);

        return redirect()->route('admin.categories.index')->with('success', 'Đã thêm danh mục.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            // ignore($category): khi sửa, không tính trùng với chính nó.
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category)],
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => Slugger::unique(Category::class, $data['name'], $category->id, 'danh-muc'),
        ]);

        Log::info('Sửa danh mục', ['category_id' => $category->id, 'admin_id' => $request->user()->id]);

        return redirect()->route('admin.categories.index')->with('success', 'Đã cập nhật danh mục.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $category->delete(); // bài viết thuộc danh mục này sẽ có category_id = NULL (nullOnDelete)

        Log::warning('Xóa danh mục', ['category_id' => $category->id, 'admin_id' => $request->user()->id]);

        return redirect()->route('admin.categories.index')->with('success', 'Đã xóa danh mục.');
    }
}
