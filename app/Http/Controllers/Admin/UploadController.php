<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    /** Tải ảnh từ trình soạn thảo: lưu thành file và trả về đường link (không nhúng base64 vào bài). */
    public function image(Request $request): JsonResponse
    {
        $request->validate(
            ['file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120']],
            ['file.required' => 'Chưa chọn ảnh.', 'file.image' => 'File phải là hình ảnh.', 'file.mimes' => 'Ảnh phải là JPG, PNG, WebP hoặc GIF.', 'file.max' => 'Ảnh tối đa 5MB.'],
        );

        $path = $request->file('file')->store('uploads/content', 'public');

        // Trả đường dẫn tương đối (/storage/...) để không phụ thuộc tên miền
        return response()->json(['location' => parse_url(asset('storage/'.$path), PHP_URL_PATH)]);
    }
}
