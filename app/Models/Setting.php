<?php

namespace App\Models;

use App\Support\SiteSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const CACHE_KEY = 'site_settings';

    protected $fillable = ['key', 'value'];

    protected static function booted(): void
    {
        static::saved(fn () => self::flush());
        static::deleted(fn () => self::flush());
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        app()->forgetInstance(SiteSettings::class);
    }

    /** Toàn bộ cài đặt của website (đã cache), dùng ở mọi trang. */
    public static function bag(): SiteSettings
    {
        return new SiteSettings(Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all()));
    }

    /** Lưu nhiều cài đặt một lần; giá trị null/'' sẽ xoá cài đặt đó. */
    public static function putMany(array $values): void
    {
        foreach ($values as $key => $value) {
            if ($value === null || $value === '') {
                static::where('key', $key)->delete();
            } else {
                static::updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value]);
            }
        }
        self::flush();
    }
}
