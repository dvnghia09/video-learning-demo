<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Phát video HLS có kiểm tra quyền. File HLS nằm trên disk private nên không thể tải trực tiếp
 * bằng đường dẫn; mọi playlist và đoạn video đều phải đi qua đây.
 */
class StreamController extends Controller
{
    private const PUBLIC_FILES = ['poster.jpg', 'thumbs.jpg', 'thumbs.vtt'];

    private const TYPES = [
        'm3u8' => 'application/vnd.apple.mpegurl',
        'ts' => 'video/mp2t',
        'vtt' => 'text/vtt; charset=utf-8',
        'jpg' => 'image/jpeg',
    ];

    public function __invoke(Request $request, Video $video, string $path): BinaryFileResponse
    {
        // Chỉ cho phép đúng các tên file do hệ thống tạo ra (chặn ../ và đường dẫn lạ)
        abort_unless(preg_match('#^(master\.m3u8|poster\.jpg|thumbs\.(jpg|vtt)|\d{3,4}p/(index\.m3u8|seg_\d{3,6}\.ts))$#', $path), 404);

        $isPublic = in_array($path, self::PUBLIC_FILES, true); // ảnh bìa / ảnh xem trước: không cần quyền
        abort_unless($isPublic || $video->canBeWatchedBy($request->user()), 403);
        abort_unless($video->status === 'ready' && $video->hls_path, 404);

        $file = Storage::disk('local')->path($video->hlsDirectory().'/'.$path);
        abort_unless(is_file($file), 404);

        $cache = $video->is_free || $isPublic ? 'public' : 'private';
        $ttl = str_ends_with($path, '.ts') ? 86400 : 300;

        // BinaryFileResponse hỗ trợ Range (206) và ETag / Last-Modified
        return response()->file($file, [
            'Content-Type' => self::TYPES[pathinfo($path, PATHINFO_EXTENSION)],
            'Cache-Control' => "{$cache}, max-age={$ttl}",
        ]);
    }
}
