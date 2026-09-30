<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index()
    {
        $lessons = Lesson::with('videos')->orderBy('order')->get();

        return view('courses', compact('lessons'));
    }

    /** Trạng thái xử lý của các video (chỉ trả id => status) để trang tự cập nhật, không cần tải lại. */
    public function status(Request $request): JsonResponse
    {
        $ids = array_slice(array_filter(array_map('intval', explode(',', (string) $request->query('ids')))), 0, 50);

        return response()->json(Video::whereIn('id', $ids)->pluck('status', 'id'));
    }

    public function video(Lesson $lesson, Video $video): View
    {
        $lessons = Lesson::with('videos')->orderBy('order')->get();

        // Video trước / sau theo đúng thứ tự trong danh sách bài học
        $flat = $lessons->flatMap(fn ($l) => $l->videos->map(fn ($v) => [$l, $v]))->values();
        $index = $flat->search(fn ($item) => $item[1]->id === $video->id);
        $link = fn ($item) => $item ? ['title' => $item[1]->title, 'url' => route('courses.video', [$item[0], $item[1]->id])] : null;
        $prev = $index > 0 ? $link($flat[$index - 1]) : null;
        $next = $index !== false ? $link($flat[$index + 1] ?? null) : null;

        $poster = $thumbs = null;
        if ($video->hlsDirectory()) {
            $disk = Storage::disk('local');
            $poster = $disk->exists($video->hlsDirectory().'/poster.jpg') ? route('stream', [$video, 'poster.jpg']) : null;
            $thumbs = $disk->exists($video->hlsDirectory().'/thumbs.vtt') ? route('stream', [$video, 'thumbs.vtt']) : null;
        }

        $canWatch = $video->canBeWatchedBy(auth()->user());

        // Người đã đăng nhập: vị trí xem dở lưu trên server (dùng được ở mọi thiết bị)
        $resumeAt = null;
        if (auth()->check() && $canWatch) {
            $p = \App\Models\VideoProgress::where(['user_id' => auth()->id(), 'video_id' => $video->id])->first();
            $resumeAt = $p ? ($p->completed ? 0 : $p->position) : null;
        }

        return view('video-player', compact('lesson', 'video', 'lessons', 'prev', 'next', 'poster', 'thumbs', 'canWatch', 'resumeAt'));
    }
}
