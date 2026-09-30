<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Ghi nhớ trang người dùng đang xem trước khi bấm Đăng nhập / Đăng ký,
 * để sau khi xong quay lại đúng trang đó (thông qua redirect()->intended()).
 */
class ReturnTo
{
    /** Các đường dẫn không nên quay lại (trang đăng nhập/đăng ký, đăng xuất, tài nguyên...) */
    private const SKIP = ['dang-nhap', 'dang-ky', 'dang-xuat', 'quen-mat-khau', 'dat-lai-mat-khau*', 'xac-nhan-mat-khau', 'xac-thuc-email*',
        'tai-khoan/mat-khau', 'up', 'stream/*', 'storage/*', 'videos/status', 'admin/uploads/*', 'sitemap.xml', 'robots.txt'];

    public static function remember(Request $request): void
    {
        $previous = url()->previous(); // lấy từ header Referer, mặc định là trang chủ nếu không có

        $parts = parse_url($previous);
        $sameHost = ($parts['host'] ?? null) === $request->getHost();
        $path = trim($parts['path'] ?? '/', '/');

        if (! $sameHost || $path === '') {
            return; // ngoài website hoặc là trang chủ: để redirect mặc định về trang chủ
        }
        foreach (self::SKIP as $pattern) {
            if (\Illuminate\Support\Str::is($pattern, $path)) {
                return; // đang từ trang đăng nhập/đăng ký sang nhau: giữ nguyên trang đã nhớ trước đó
            }
        }

        session(['url.intended' => $previous]);
    }
}
