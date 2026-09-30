<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    /** Các ảnh cài đặt: khoá => [thư mục, kích thước tối đa KB, định dạng] */
    private const IMAGES = [
        'logo' => ['png,jpg,jpeg,webp,gif', 2048],
        'favicon' => ['png,ico,jpg,jpeg,webp', 512],
        'og_image' => ['png,jpg,jpeg,webp', 4096],
    ];

    public function edit(): View
    {
        return view('admin.settings.edit', ['s' => Setting::bag()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [
            'brand_name' => ['nullable', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'messenger_link' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'zalo_url' => ['nullable', 'string', 'max:255', function ($attr, $value, $fail) {
                if ($value && ! preg_match('#^https?://\S+$#i', $value) && ! preg_match('/^\d[\d\s.\-]{7,}$/', $value)) {
                    $fail('Zalo phải là đường link (https://zalo.me/...) hoặc số điện thoại.');
                }
            }],
            'phones_number' => ['nullable', 'array', 'max:10'],
            'phones_number.*' => ['nullable', 'regex:/^[0-9 .+()\-]{6,20}$/'],
            'phones_label' => ['nullable', 'array', 'max:10'],
            'phones_label.*' => ['nullable', 'string', 'max:60'],
            'meta_title' => ['nullable', 'string', 'max:120'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'google_analytics' => ['nullable', 'regex:/^(G|GT|GTM|AW)-[A-Z0-9]{4,}$/i'],
            'og_title' => ['nullable', 'string', 'max:120'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'fanpage_iframe' => ['nullable', 'string', 'max:3000'],
            'map_iframe' => ['nullable', 'string', 'max:3000'],
        ];
        foreach (self::IMAGES as $key => [$mimes, $kb]) {
            $rules[$key] = ['nullable', 'file', "mimes:$mimes", "max:$kb"];
        }

        $data = $request->validate($rules, [
            'phones_number.*.regex' => 'Số điện thoại không hợp lệ.',
            'google_analytics.regex' => 'Google Analytics ID phải có dạng G-XXXXXXXXXX.',
            'messenger_link.url' => 'Link Messenger phải là đường link đầy đủ (https://m.me/...).',
            'facebook_url.url' => 'URL Facebook phải là đường link đầy đủ.',
        ]);

        $values = collect($data)->only([
            'brand_name', 'company_name', 'email', 'address', 'messenger_link', 'facebook_url',
            'meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description',
        ])->map(fn ($v) => is_string($v) ? trim($v) : $v)->all();

        $values['zalo_url'] = trim((string) ($data['zalo_url'] ?? ''));
        $values['google_analytics'] = strtoupper(trim((string) ($data['google_analytics'] ?? '')));

        // Danh sách điện thoại: bỏ dòng trống, số đầu tiên là số mặc định
        $phones = [];
        foreach ((array) ($data['phones_number'] ?? []) as $i => $number) {
            if (trim((string) $number) !== '') {
                $phones[] = ['number' => trim($number), 'label' => trim((string) ($data['phones_label'][$i] ?? ''))];
            }
        }
        $values['phones'] = $phones ? json_encode($phones, JSON_UNESCAPED_UNICODE) : null;

        // Iframe: chỉ nhận Facebook Page Plugin và Google Maps, dựng lại thẻ an toàn (không lưu HTML thô)
        $errors = [];
        foreach (['fanpage_iframe' => ['facebook.com'], 'map_iframe' => ['google.com', 'google.com.vn']] as $key => $hosts) {
            $raw = trim((string) ($data[$key] ?? ''));
            if ($raw === '') {
                $values[$key] = null;

                continue;
            }
            $clean = $this->safeIframe($raw, $hosts, $key === 'map_iframe');
            if ($clean === null) {
                $errors[$key] = $key === 'map_iframe'
                    ? 'Mã nhúng không hợp lệ. Chỉ chấp nhận iframe từ Google Maps (https://www.google.com/maps/...).'
                    : 'Mã nhúng không hợp lệ. Chỉ chấp nhận iframe từ Facebook (https://www.facebook.com/plugins/...).';
            }
            $values[$key] = $clean;
        }
        if ($errors) {
            return back()->withInput()->withErrors($errors);
        }

        // Ảnh: thay ảnh cũ khi tải ảnh mới hoặc khi tick "Xoá"
        foreach (array_keys(self::IMAGES) as $key) {
            $old = Setting::bag()->get($key);
            if ($request->hasFile($key)) {
                $values[$key] = $request->file($key)->store('settings', 'public');
                $old && Storage::disk('public')->delete($old);
            } elseif ($request->boolean("remove_$key")) {
                $values[$key] = null;
                $old && Storage::disk('public')->delete($old);
            }
        }

        Setting::putMany($values);

        return redirect()->route('admin.settings.edit')->with('success', 'Đã lưu tất cả cài đặt.');
    }

    /**
     * Lấy src (và kích thước) từ mã nhúng dán vào, chỉ chấp nhận https + tên miền cho phép,
     * rồi dựng lại thẻ iframe sạch. Trả về null nếu không hợp lệ.
     */
    private function safeIframe(string $raw, array $allowedHosts, bool $isMap): ?string
    {
        if (preg_match('/^https:\/\/\S+$/i', $raw)) {
            $src = $raw;
        } elseif (preg_match('/<iframe\b[^>]*\bsrc\s*=\s*["\']([^"\']+)["\']/i', $raw, $m)) {
            $src = html_entity_decode($m[1]);
        } else {
            return null;
        }

        $parts = parse_url($src);
        $host = strtolower($parts['host'] ?? '');
        $host = preg_replace('/^(www|m|web)\./', '', $host);
        if (($parts['scheme'] ?? '') !== 'https' || ! in_array($host, $allowedHosts, true)) {
            return null;
        }
        $path = $parts['path'] ?? '';
        if ($isMap && ! str_starts_with($path, '/maps')) {
            return null;
        }
        if (! $isMap && ! str_starts_with($path, '/plugins/')) {
            return null;
        }

        $size = fn (string $attr, string $default) => preg_match('/\b'.$attr.'\s*=\s*["\']?(\d{2,4}%?)/i', $raw, $mm) ? $mm[1] : $default;
        $width = $size('width', $isMap ? '100%' : '340');
        $height = $size('height', $isMap ? '350' : '500');

        return sprintf(
            '<iframe src="%s" width="%s" height="%s" style="border:0;max-width:100%%" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>',
            e($src), e($width), e($height)
        );
    }
}
