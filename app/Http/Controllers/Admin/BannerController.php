<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        $items = Banner::orderBy('id')->paginate(12);

        return view('admin.banners.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.banners.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate($this->rules(true), $this->messages());

        Banner::create([
            'image_path' => $request->file('image')->store('banners', 'public'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Đã thêm banner mới.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $request->validate($this->rules(false), $this->messages());

        $data = ['is_active' => $request->boolean('is_active')];
        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->image_path);
            $data['image_path'] = $request->file('image')->store('banners', 'public');
        }
        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'Đã cập nhật banner.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        Storage::disk('public')->delete($banner->image_path);
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Đã xoá banner.');
    }

    private function rules(bool $creating): array
    {
        return ['image' => [$creating ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']];
    }

    private function messages(): array
    {
        return [
            'image.required' => 'Vui lòng chọn ảnh banner.',
            'image.image' => 'File phải là hình ảnh.',
            'image.mimes' => 'Ảnh phải là JPG, PNG hoặc WebP.',
            'image.max' => 'Ảnh tối đa 5MB.',
        ];
    }
}
