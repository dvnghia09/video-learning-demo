<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(): View
    {
        $items = Lesson::with('videos')->withCount('videos')->orderBy('order')->orderBy('id')->paginate(10);

        return view('admin.lessons.index', compact('items'));
    }

    public function create(): View
    {
        $nextOrder = (int) Lesson::max('order') + 1;

        return view('admin.lessons.create', compact('nextOrder'));
    }

    public function store(Request $request): RedirectResponse
    {
        Lesson::create($this->validated($request));

        return redirect()->route('admin.lessons.index')->with('success', 'Đã thêm bài học mới.');
    }

    public function edit(Lesson $lesson): View
    {
        $lesson->load('videos');

        return view('admin.lessons.edit', compact('lesson'));
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $lesson->update($this->validated($request));

        return redirect()->route('admin.lessons.index')->with('success', 'Đã cập nhật bài học.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $lesson->delete();

        return redirect()->route('admin.lessons.index')->with('success', 'Đã xoá bài học.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['order'] ??= 0;

        return $data;
    }

    /**
     * Sắp xếp lại vị trí: các id gửi lên (có thể chỉ là một trang) được đặt theo thứ tự mới
     * vào đúng những "ô" mà chúng đang chiếm, rồi đánh lại số thứ tự 1..N cho cả nhóm.
     */
    protected function applyOrder($query, array $ids): void
    {
        $all = $query->orderBy('order')->orderBy('id')->pluck('id')->all();
        $slots = array_keys(array_filter($all, fn ($id) => in_array($id, $ids, true)));
        foreach ($slots as $i => $slot) {
            $all[$slot] = $ids[$i];
        }
        foreach ($all as $i => $id) {
            $query->getModel()->newQuery()->whereKey($id)->where('order', '!=', $i + 1)->update(['order' => $i + 1]);
        }
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate(['ids' => ['required', 'array', 'min:1'], 'ids.*' => ['integer', 'distinct', 'exists:lessons,id']]);
        $ids = array_map('intval', $data['ids']);
        $this->applyOrder(Lesson::query(), $ids);

        return response()->json(['ok' => true]);
    }
}
