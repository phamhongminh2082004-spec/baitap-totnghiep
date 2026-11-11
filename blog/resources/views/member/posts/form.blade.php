{{-- FORM TẠO / SỬA BÀI dùng chung: ảnh, danh mục, nhiều tag, 3 trạng thái, giờ hẹn đăng. --}}
@extends('layouts.app')
@php $isEdit = $post->exists; @endphp
@section('title', $isEdit ? 'Sửa bài viết' : 'Viết bài mới')

@section('content')
<h1 class="h3 mb-3">{{ $isEdit ? 'Sửa bài viết' : 'Viết bài mới' }}</h1>

<form method="POST" enctype="multipart/form-data"
      action="{{ $isEdit ? route('member.posts.update', $post) : route('member.posts.store') }}"
      class="card card-body border-0 shadow-sm">
    @csrf
    {{-- HTML form chỉ có GET/POST nên giả lập PUT bằng @method('PUT') khi sửa. --}}
    @if ($isEdit) @method('PUT') @endif

    <div class="mb-3">
        <label class="form-label" for="title">Tiêu đề</label>
        <input id="title" name="title" value="{{ old('title', $post->title) }}" class="form-control @error('title') is-invalid @enderror" required>
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="body">Nội dung</label>
        <textarea id="body" name="body" rows="12" class="form-control @error('body') is-invalid @enderror" required>{{ old('body', $post->body) }}</textarea>
        @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="category_id">Danh mục</label>
            <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">-- Chọn danh mục --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $post->category_id) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label" for="image">Ảnh bìa (tối đa 2MB)</label>
            <input id="image" type="file" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
            @if ($isEdit && $post->imageUrl())
                <div class="mt-2 d-flex align-items-center gap-3">
                    <img src="{{ $post->imageUrl() }}" alt="" height="60" class="rounded">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                        <label class="form-check-label" for="remove_image">Xóa ảnh hiện tại</label>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @php $selectedTags = collect(old('tags', $post->exists ? $post->tags->pluck('id')->all() : []))->map(fn ($v) => (int) $v); @endphp
    <div class="mb-3">
        <label class="form-label d-block">Tag (chọn nhiều)</label>
        @foreach ($tags as $tag)
            <div class="form-check form-check-inline">
                {{-- Mỗi checkbox gửi 1 id vào mảng tags[] (chọn nhiều tag). --}}
                <input class="form-check-input" type="checkbox" name="tags[]" value="{{ $tag->id }}" id="tag{{ $tag->id }}" @checked($selectedTags->contains($tag->id))>
                <label class="form-check-label" for="tag{{ $tag->id }}">{{ $tag->name }}</label>
            </div>
        @endforeach
        @error('tags.*') <div class="text-danger small">{{ $message }}</div> @enderror
        <input name="new_tags" value="{{ old('new_tags') }}" class="form-control mt-2" placeholder="Hoặc thêm tag mới, cách nhau bằng dấu phẩy">
    </div>

    @php $status = old('status', $post->status ?: 'draft'); @endphp
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="status">Trạng thái</label>
            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                <option value="draft" @selected($status === 'draft')>Nháp</option>
                <option value="published" @selected($status === 'published')>Đăng ngay</option>
                <option value="scheduled" @selected($status === 'scheduled')>Hẹn giờ đăng</option>
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 mb-3" id="publishedAtBox" style="{{ $status === 'scheduled' ? '' : 'display:none' }}">
            <label class="form-label" for="published_at">Thời điểm đăng</label>
            <input id="published_at" type="datetime-local" name="published_at"
                   value="{{ old('published_at', $post->isScheduled() ? $post->published_at->format('Y-m-d\TH:i') : '') }}"
                   class="form-control @error('published_at') is-invalid @enderror">
            @error('published_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-success" type="submit">Lưu bài viết</button>
        <a href="{{ route('member.posts.index') }}" class="btn btn-outline-secondary">Hủy</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Chỉ hiện ô chọn giờ khi trạng thái là 'Hẹn giờ'.
    const statusSelect = document.getElementById('status');
    const box = document.getElementById('publishedAtBox');
    statusSelect.addEventListener('change', () => {
        box.style.display = statusSelect.value === 'scheduled' ? '' : 'none';
    });
</script>
@endpush
