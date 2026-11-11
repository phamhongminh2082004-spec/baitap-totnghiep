{{-- MENU của khu vực quản trị (đánh dấu tab đang mở). --}}
<ul class="nav nav-pills mb-4">
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active bg-success' : '' }}" href="{{ route('admin.dashboard') }}">Thống kê</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active bg-success' : '' }}" href="{{ route('admin.categories.index') }}">Danh mục</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.posts.*') ? 'active bg-success' : '' }}" href="{{ route('admin.posts.index') }}">Bài viết</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.comments.*') ? 'active bg-success' : '' }}" href="{{ route('admin.comments.index') }}">Bình luận</a></li>
</ul>
