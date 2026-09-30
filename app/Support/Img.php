<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** Lưu ảnh tải lên: thu nhỏ nếu quá lớn và nén lại để trang tải nhanh. */
class Img
{
    public static function store(UploadedFile $file, string $dir, int $max = 1600, string $disk = 'public'): string
    {
        $mime = $file->getMimeType();
        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file->getRealPath()),
            'image/png' => @imagecreatefrompng($file->getRealPath()),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file->getRealPath()) : false,
            default => false,
        };
        if (! $src) {
            return $file->store($dir, $disk); // định dạng lạ hoặc không đọc được: lưu nguyên bản
        }

        $w = imagesx($src);
        $h = imagesy($src);
        if (max($w, $h) > $max) {
            $scale = $max / max($w, $h);
            $src = imagescale($src, (int) round($w * $scale), (int) round($h * $scale), IMG_BICUBIC) ?: $src;
        }

        // Ảnh JPEG/WebP lưu JPEG q82; ảnh PNG giữ PNG (có thể có nền trong suốt)
        ob_start();
        if ($mime === 'image/png') {
            imagesavealpha($src, true);
            imagepng($src, null, 8);
            $ext = 'png';
        } else {
            imagejpeg($src, null, 82);
            $ext = 'jpg';
        }
        $bin = ob_get_clean();

        $path = trim($dir, '/').'/'.\Illuminate\Support\Str::random(40).'.'.$ext;
        Storage::disk($disk)->put($path, $bin);

        return $path;
    }
}
