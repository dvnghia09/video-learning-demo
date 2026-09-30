<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/bai-hoc', [CourseController::class, 'index'])->name('courses.index');
Route::get('/bai-hoc/{lesson:slug}/{video:slug}', [CourseController::class, 'video'])->scopeBindings()->name('courses.video');
Route::get('/stream/{video}/{path}', \App\Http\Controllers\StreamController::class)->where('path', '.*')->name('stream');
Route::get('/videos/status', [CourseController::class, 'status'])->middleware('throttle:120,1')->name('videos.status');
Route::get('/trang/{page:slug}', [PageController::class, 'show'])->name('pages.show');
Route::get('/gioi-thieu', [PageController::class, 'about'])->name('about');
Route::get('/lien-he', [ContactController::class, 'index'])->name('contact.index');
Route::post('/lien-he', [ContactController::class, 'store'])->name('contact.store');
Route::get('/bai-viet', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/bai-viet/{slug}', [BlogController::class, 'show'])->name('blogs.show');

// SEO: sơ đồ trang và robots.txt (tự động theo dữ liệu)
Route::get('/sitemap.xml', \App\Http\Controllers\SeoController::class.'@sitemap')->name('sitemap');
Route::get('/robots.txt', \App\Http\Controllers\SeoController::class.'@robots')->name('robots');

// Địa chỉ cũ (tiếng Anh / dùng số thứ tự) -> chuyển hướng vĩnh viễn (301) sang địa chỉ mới để không mất thứ hạng và liên kết cũ
Route::permanentRedirect('/khoa-hoc', '/bai-hoc');
Route::permanentRedirect('/contact', '/lien-he');
Route::permanentRedirect('/login', '/dang-nhap');
Route::permanentRedirect('/register', '/dang-ky');
Route::permanentRedirect('/profile', '/tai-khoan');
Route::get('/khoa-hoc/{lesson}/video/{video}', function (int $lesson, int $video) {
    $v = \App\Models\Video::with('lesson')->where('id', $video)->where('lesson_id', $lesson)->firstOrFail();

    return redirect()->route('courses.video', [$v->lesson, $v], 301);
})->whereNumber(['lesson', 'video']);

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::post('uploads/image', [\App\Http\Controllers\Admin\UploadController::class, 'image'])->name('uploads.image');
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    Route::post('users/{user}/vip', [\App\Http\Controllers\Admin\UserController::class, 'toggleVip'])->name('users.vip');
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::post('artworks/{artwork}/toggle', [\App\Http\Controllers\Admin\ArtworkController::class, 'toggle'])->name('artworks.toggle');
    Route::post('artworks/{artwork}/move', [\App\Http\Controllers\Admin\ArtworkController::class, 'move'])->name('artworks.move');
    Route::resource('artworks', \App\Http\Controllers\Admin\ArtworkController::class)->except(['show']);
    Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class);
    Route::resource('pages', \App\Http\Controllers\Admin\PageController::class);
    Route::patch('contacts/{contact}/status', [\App\Http\Controllers\Admin\ContactController::class, 'updateStatus'])->name('contacts.status');
    Route::resource('contacts', \App\Http\Controllers\Admin\ContactController::class)->only(['index', 'show']);
    Route::post('lessons/reorder', [\App\Http\Controllers\Admin\LessonController::class, 'reorder'])->name('lessons.reorder');
    Route::resource('lessons', \App\Http\Controllers\Admin\LessonController::class);
    Route::get('videos/status', [\App\Http\Controllers\Admin\VideoController::class, 'status'])->name('videos.status');
    Route::post('videos/reorder', [\App\Http\Controllers\Admin\VideoController::class, 'reorder'])->name('videos.reorder');
    Route::resource('videos', \App\Http\Controllers\Admin\VideoController::class);
    Route::resource('blogs', \App\Http\Controllers\Admin\BlogController::class);
});

Route::middleware('auth')->group(function () {
    Route::post('/videos/{video}/progress', [\App\Http\Controllers\VideoProgressController::class, 'store'])->middleware('throttle:90,1')->name('videos.progress');
    Route::get('/tai-khoan', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/tai-khoan', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/tai-khoan', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
