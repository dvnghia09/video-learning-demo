<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artwork;
use App\Support\Img;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArtworkController extends Controller
{
    public function index(): View
    {
        $items = Artwork::ordered()->get();

        return view('admin.artworks.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.artworks.create');
    }

    /** Có thể chọn nhiều ảnh một lần: mỗi ảnh là một tác phẩm mới, xếp cuối danh sách. */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:12'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'title' => ['nullable', 'string', 'max:150'],
        ], [
            'images.required' => 'Vui lòng chọn ít nhất một ảnh.',
            'images.max' => 'Mỗi lần chỉ tải tối đa 12 ảnh.',
            'images.*.image' => 'File phải là hình ảnh.',
            'images.*.mimes' => 'Ảnh phải là JPG, PNG hoặc WebP.',
            'images.*.max' => 'Mỗi ảnh tối đa 8MB.',
        ]);

        $order = (int) Artwork::max('sort_order');
        $files = $request->file('images');
        foreach ($files as $file) {
            Artwork::create([
                'title' => count($files) === 1 ? $request->input('title') : null,
                'image_path' => Img::store($file, 'artworks', 1600),
                'sort_order' => ++$order,
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.artworks.index')->with('success', 'Đã thêm '.count($files).' tác phẩm.');
    }

    public function edit(Artwork $artwork): View
    {
        return view('admin.artworks.edit', compact('artwork'));
    }

    public function update(Request $request, Artwork $artwork): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ], ['image.image' => 'File phải là hình ảnh.', 'image.mimes' => 'Ảnh phải là JPG, PNG hoặc WebP.', 'image.max' => 'Ảnh tối đa 8MB.']);

        $update = [
            'title' => $data['title'] ?? null,
            'sort_order' => $data['sort_order'] ?? $artwork->sort_order,
            'is_active' => $request->boolean('is_active'),
        ];
        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($artwork->image_path);
            $update['image_path'] = Img::store($request->file('image'), 'artworks', 1600);
        }
        $artwork->update($update);

        return redirect()->route('admin.artworks.index')->with('success', 'Đã cập nhật tác phẩm.');
    }

    /** Bật/tắt hiển thị nhanh ngay trên danh sách. */
    public function toggle(Artwork $artwork): RedirectResponse
    {
        $artwork->update(['is_active' => ! $artwork->is_active]);

        return back()->with('success', $artwork->is_active ? 'Đã bật hiển thị tác phẩm.' : 'Đã ẩn tác phẩm khỏi trang chủ.');
    }

    /** Đổi thứ tự lên/xuống bằng cách hoán đổi với tác phẩm liền kề. */
    public function move(Request $request, Artwork $artwork): RedirectResponse
    {
        $items = Artwork::ordered()->get()->values();
        $i = $items->search(fn ($a) => $a->id === $artwork->id);
        $j = $request->input('dir') === 'up' ? $i - 1 : $i + 1;

        if ($i !== false && isset($items[$j])) {
            // gán lại thứ tự liên tục 1..n để tránh trùng số
            $list = $items->all();
            [$list[$i], $list[$j]] = [$list[$j], $list[$i]];
            foreach ($list as $pos => $a) {
                $a->update(['sort_order' => $pos + 1]);
            }
        }

        return back();
    }

    public function destroy(Artwork $artwork): RedirectResponse
    {
        Storage::disk('public')->delete($artwork->image_path);
        $artwork->delete();

        return redirect()->route('admin.artworks.index')->with('success', 'Đã xoá tác phẩm.');
    }
}
