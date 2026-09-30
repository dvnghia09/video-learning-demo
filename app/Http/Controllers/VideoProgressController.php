<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoProgressController extends Controller
{
    /** Lưu vị trí đang xem của người dùng đã đăng nhập (gọi định kỳ từ trình phát). */
    public function store(Request $request, Video $video): JsonResponse
    {
        abort_unless($video->canBeWatchedBy($request->user()), 403);

        $data = $request->validate([
            'position' => ['required', 'numeric', 'min:0', 'max:86400'],
            'duration' => ['required', 'numeric', 'min:0', 'max:86400'],
            'completed' => ['nullable', 'boolean'],
        ]);

        $position = (int) floor($data['position']);
        $duration = (int) floor($data['duration']);
        $completed = $request->boolean('completed') || ($duration > 20 && $position >= $duration - 8);

        $row = VideoProgress::firstOrNew(['user_id' => $request->user()->id, 'video_id' => $video->id]);
        $wasCompleted = $row->exists && $row->completed;

        $row->duration = $duration;
        if ($completed) {
            $row->completed = true;
            $row->position = 0;          // xem xong: lần sau bắt đầu lại từ đầu
        } else {
            $row->position = $duration > 0 ? min($position, $duration) : $position;
            // xem lại video đã hoàn thành: chỉ bỏ dấu "hoàn thành" khi đã xem thêm ít nhất 30 giây
            if ($wasCompleted && $position >= 30) {
                $row->completed = false;
            }
        }
        $row->save();
        $row->touch(); // luôn cập nhật thời điểm xem gần nhất

        return response()->json(['ok' => true, 'percent' => $row->percent()]);
    }
}
