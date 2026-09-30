<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Banner;
use App\Models\Lesson;
use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::where('is_active', true)->orderBy('id')->get();
        $lessons = Lesson::with('videos')->orderBy('order')->get();
        $artworks = Artwork::visible()->ordered()->limit(24)->get();

        // Số liệu hiển thị trên trang chủ (cache 10 phút cho nhẹ)
        $stats = Cache::remember('home_stats', 600, fn () => [
            'lessons' => Lesson::count(),
            'videos' => Video::where('status', 'ready')->count(),
            'students' => User::where('role', 'user')->count(),
        ]);

        return view('index', compact('banners', 'lessons', 'stats', 'artworks'));
    }
}
