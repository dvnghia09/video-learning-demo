<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\Html;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $items = Page::orderBy('title')->paginate(10);

        return view('admin.pages.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(), $this->messages());
        $data['content'] = Html::extractInlineImages($data['content'] ?? null);
        Page::create($data);

        return redirect()->route('admin.pages.index')->with('success', 'Đã tạo trang mới.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate($this->rules($page), $this->messages());
        $data['content'] = Html::extractInlineImages($data['content'] ?? null);
        $page->update($data);

        return redirect()->route('admin.pages.index')->with('success', 'Đã cập nhật trang.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Đã xoá trang.');
    }

    private function rules(?Page $page = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            // slug chỉ gồm chữ thường, số, gạch ngang — dùng làm đường dẫn /trang/{slug}
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('pages', 'slug')->ignore($page?->id)],
            'content' => ['nullable', 'string'],
        ];
    }

    private function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập tiêu đề trang.',
            'slug.required' => 'Vui lòng nhập slug (đường dẫn).',
            'slug.regex' => 'Slug chỉ gồm chữ thường không dấu, số và dấu gạch ngang (VD: chinh-sach-bao-mat).',
            'slug.unique' => 'Slug này đã được dùng cho trang khác.',
        ];
    }
}
