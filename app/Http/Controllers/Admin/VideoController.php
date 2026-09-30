<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessVideo;
use App\Models\Lesson;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(): View
    {
        $items = Video::with('lesson')->latest()->paginate(10);
        $pendingIds = $items->filter(fn ($v) => in_array($v->status, ['pending', 'processing']))->pluck('id')->values();

        return view('admin.videos.index', compact('items', 'pendingIds'));
    }

    public function status(Request $request): JsonResponse
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $request->query('ids'))));

        $data = Video::with('lesson')->whereIn('id', $ids)->get()->mapWithKeys(fn (Video $v) => [
            $v->id => [
                'status' => $v->status,
                'html' => view('admin.videos._status', ['video' => $v])->render(),
                'watch_url' => $v->status === 'ready' ? route('courses.video', [$v->lesson, $v]) : null,
                'poster' => $v->posterUrl(),
            ],
        ]);

        return response()->json($data);
    }

    public function create(Request $request): View
    {
        $lessons = Lesson::orderBy('order')->orderBy('id')->get();
        $selectedLesson = (int) $request->query('lesson_id', $lessons->first()?->id);
        $maxUploadMb = (int) (min(
            $this->iniToBytes(ini_get('upload_max_filesize')),
            $this->iniToBytes(ini_get('post_max_size'))
        ) / 1048576);

        return view('admin.videos.create', compact('lessons', 'selectedLesson', 'maxUploadMb'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'lesson_id' => 'required|exists:lessons,id',
            'video_file' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm,video/x-matroska',
        ], [
            'title.required' => 'Vui lòng nhập tên video.',
            'lesson_id.required' => 'Vui lòng chọn bài học.',
            'lesson_id.exists' => 'Bài học đã chọn không tồn tại.',
            'video_file.required' => 'Vui lòng chọn file video.',
            'video_file.mimetypes' => 'File phải là video (MP4, MOV, AVI, WEBM hoặc MKV).',
            'video_file.uploaded' => 'Tải file lên thất bại, có thể file vượt quá dung lượng cho phép của máy chủ.',
        ]);

        $path = $request->file('video_file')->store('videos_raw', 'local');

        $video = Video::create([
            'title' => $request->title,
            'lesson_id' => $request->lesson_id,
            'video_path' => $path,
            'is_free' => $request->boolean('is_free'),
            'status' => 'pending',
        ]);

        ProcessVideo::dispatch($video);

        $message = 'Video đã được tải lên và đang được xử lý (mã hoá HLS).';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('admin.videos.index'),
            ]);
        }

        return redirect()->route('admin.videos.index')->with('success', $message);
    }

    public function edit(Video $video): View
    {
        $lessons = Lesson::orderBy('order')->orderBy('id')->get();

        return view('admin.videos.edit', compact('video', 'lessons'));
    }

    public function update(Request $request, Video $video): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'lesson_id' => 'required|exists:lessons,id',
            'order' => 'nullable|integer|min:0',
        ]);

        $video->update([
            'title' => $data['title'],
            'lesson_id' => $data['lesson_id'],
            'order' => $data['order'] ?? $video->order,
            'is_free' => $request->boolean('is_free'),
        ]);

        return redirect()->route('admin.videos.index')->with('success', 'Đã cập nhật video.');
    }

    public function destroy(Video $video): RedirectResponse
    {
        foreach (['local', 'public'] as $disk) {
            if ($video->video_path) {
                Storage::disk($disk)->delete($video->video_path);
            }
            if ($video->hlsDirectory()) {
                Storage::disk($disk)->deleteDirectory($video->hlsDirectory());
            }
            Storage::disk($disk)->deleteDirectory('videos/'.$video->id.'_hls');
        }

        $video->delete();

        return redirect()->route('admin.videos.index')->with('success', 'Đã xoá video.');
    }

    private function iniToBytes(string|false $value): int
    {
        $value = trim((string) $value);
        $n = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $n * 1073741824,
            'm' => $n * 1048576,
            'k' => $n * 1024,
            default => $n,
        };
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
        $data = $request->validate(['ids' => ['required', 'array', 'min:1'], 'ids.*' => ['integer', 'distinct', 'exists:videos,id']]);
        $ids = array_map('intval', $data['ids']);
        $lessonIds = Video::whereIn('id', $ids)->pluck('lesson_id')->unique();
        abort_if($lessonIds->count() !== 1, 422, 'Chỉ sắp xếp video trong cùng một bài học.');
        $this->applyOrder(Video::where('lesson_id', $lessonIds->first()), $ids);

        return response()->json(['ok' => true]);
    }
}
