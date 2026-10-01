<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Contact;
use App\Models\Lesson;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoProgress;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const DAYS = 14;

    public function index(): View
    {
        $from = Carbon::today()->subDays(self::DAYS - 1);

        // Đếm theo ngày (gom trong PHP để chạy được trên cả MySQL lẫn SQLite)
        $perDay = fn ($query) => $query->where('created_at', '>=', $from)->pluck('created_at')
            ->countBy(fn ($d) => Carbon::parse($d)->toDateString());

        $users = $perDay(User::query());
        $contacts = $perDay(Contact::query());

        $labels = $userSeries = $contactSeries = [];
        for ($i = 0; $i < self::DAYS; $i++) {
            $day = $from->copy()->addDays($i);
            $labels[] = $day->format('d/m');
            $userSeries[] = (int) ($users[$day->toDateString()] ?? 0);
            $contactSeries[] = (int) ($contacts[$day->toDateString()] ?? 0);
        }

        $videoStatus = Video::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');
        $accessFree = Video::where('is_free', true)->count();
        $vipUsers = User::where('is_vip', true)->count();
        $totalUsers = User::count();

        $topVideos = VideoProgress::query()->selectRaw('video_id, count(*) as viewers')
            ->groupBy('video_id')->orderByDesc('viewers')->limit(5)->with('video:id,title')->get()
            ->filter(fn ($p) => $p->video)->map(fn ($p) => ['title' => $p->video->title, 'viewers' => (int) $p->viewers])->values();

        return view('admin.dashboard', [
            'stats' => [
                'users' => $totalUsers,
                'vip' => $vipUsers,
                'videos' => Video::count(),
                'lessons' => Lesson::count(),
                'blogs' => Blog::count(),
                'newContacts' => Contact::where('status', 'new')->count(),
            ],
            'charts' => [
                'labels' => $labels,
                'users' => $userSeries,
                'contacts' => $contactSeries,
                'videoStatus' => [
                    'labels' => ['Sẵn sàng', 'Đang xử lý', 'Chờ xử lý', 'Lỗi'],
                    'data' => [
                        (int) ($videoStatus['ready'] ?? 0),
                        (int) ($videoStatus['processing'] ?? 0),
                        (int) ($videoStatus['pending'] ?? 0),
                        (int) ($videoStatus['failed'] ?? 0),
                    ],
                ],
                'access' => ['labels' => ['Miễn phí', 'Cần nâng cấp gói'], 'data' => [$accessFree, max(0, Video::count() - $accessFree)]],
                'members' => ['labels' => ['Thành viên VIP', 'Thành viên thường'], 'data' => [$vipUsers, max(0, $totalUsers - $vipUsers)]],
                'top' => ['labels' => $topVideos->pluck('title')->all(), 'data' => $topVideos->pluck('viewers')->all()],
            ],
            'recentContacts' => Contact::latest()->limit(5)->get(),
        ]);
    }
}
