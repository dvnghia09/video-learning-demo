<?php

namespace App\Providers;

use App\Models\Setting;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Cài đặt website: đọc (từ cache) một lần cho mỗi request
        $this->app->singleton(SiteSettings::class, fn () => Setting::bag());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Mật khẩu ở mọi form: tối thiểu 8 ký tự
        Password::defaults(fn () => Password::min(8));

        // Thời gian tương đối hiển thị tiếng Việt: "5 phút trước" thay cho "5 minutes ago"
        \Carbon\Carbon::setLocale('vi');

        // Nội dung đổi thì làm mới sơ đồ trang (sitemap.xml)
        foreach ([\App\Models\Blog::class, \App\Models\Page::class, \App\Models\Video::class, \App\Models\Lesson::class] as $model) {
            $model::saved(fn () => \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\SeoController::CACHE_KEY));
            $model::deleted(fn () => \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\SeoController::CACHE_KEY));
        }

        // $site (cài đặt website) có sẵn ở mọi view
        View::composer('*', fn ($view) => $view->with('site', $this->app->make(SiteSettings::class)));
    }
}
