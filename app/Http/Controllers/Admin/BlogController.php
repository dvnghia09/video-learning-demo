<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Support\Html;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $items = Blog::query()
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('title', 'like', "%{$q}%")->orWhere('content', 'like', "%{$q}%")))
            ->latest()->paginate(10)->withQueryString();
        $total = Blog::count();

        return view('admin.blogs.index', compact('items', 'q', 'total'));
    }

    public function create(): View
    {
        return view('admin.blogs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(), $this->messages());

        $blog = ['title' => $data['title'], 'content' => Html::extractInlineImages($data['content'])];
        if (! empty($data['slug'])) {
            $blog['slug'] = $data['slug'];   // để trống thì tự tạo từ tiêu đề
        }
        if ($request->hasFile('image')) {
            $blog['image_path'] = $request->file('image')->store('uploads', 'public');
        }
        Blog::create($blog);

        return redirect()->route('admin.blogs.index')->with('success', 'Đã đăng bài viết mới.');
    }

    public function edit(Blog $blog): View
    {
        return view('admin.blogs.edit', compact('blog'));
    }

    public function update(Request $request, Blog $blog): RedirectResponse
    {
        $data = $request->validate($this->rules($blog), $this->messages());

        $blog->title = $data['title'];
        if (! empty($data['slug'])) {
            $blog->slug = $data['slug'];     // để trống thì giữ nguyên đường dẫn cũ
        }
        $blog->content = Html::extractInlineImages($data['content']);
        if ($request->hasFile('image')) {
            $blog->image_path && Storage::disk('public')->delete($blog->image_path);
            $blog->image_path = $request->file('image')->store('uploads', 'public');
        } elseif ($request->boolean('remove_image') && $blog->image_path) {
            Storage::disk('public')->delete($blog->image_path);
            $blog->image_path = null;
        }
        $blog->save();

        return redirect()->route('admin.blogs.index')->with('success', 'Đã cập nhật bài viết.');
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        $blog->image_path && Storage::disk('public')->delete($blog->image_path);
        $blog->delete();

        return redirect()->route('admin.blogs.index')->with('success', 'Đã xoá bài viết.');
    }

    private function rules(?Blog $blog = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('blogs', 'slug')->ignore($blog?->id)],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    private function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập tiêu đề bài viết.',
            'slug.regex' => 'Đường dẫn chỉ gồm chữ thường không dấu, số và dấu gạch ngang (VD: cach-cam-hoa-hong).',
            'slug.unique' => 'Đường dẫn này đã dùng cho bài viết khác.',
            'content.required' => 'Vui lòng nhập nội dung bài viết.',
            'image.image' => 'File phải là hình ảnh.',
            'image.mimes' => 'Ảnh phải là JPG, PNG hoặc WebP.',
            'image.max' => 'Ảnh tối đa 5MB.',
        ];
    }
}
