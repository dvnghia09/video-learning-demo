<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'title', 'content'];

    protected static function booted(): void
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('footer_pages'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('footer_pages'));
    }

    /** Các trang tĩnh khác ngoài "Giới thiệu", hiện ở footer. Cache dạng mảng thuần (Laravel 13 không cho cache đối tượng). */
    public static function footerLinks()
    {
        return \Illuminate\Support\Facades\Cache::remember('footer_pages', 3600, fn () => static::where('slug', '!=', 'about')->orderBy('title')->limit(6)->get(['slug', 'title'])->map(fn ($p) => ['slug' => $p->slug, 'title' => $p->title])->all());
    }
}
