<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Page;
use App\Models\Video;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public const CACHE_KEY = 'sitemap_xml';

    /** Sơ đồ trang (sitemap.xml) tự cập nhật theo dữ liệu; lưu cache 1 giờ và xoá khi nội dung thay đổi. */
    public function sitemap(): Response
    {
        $xml = Cache::remember(self::CACHE_KEY, 3600, fn () => $this->buildSitemap());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            '# Khu vực quản trị, tài khoản, đăng nhập và luồng phát video không cần lập chỉ mục',
            'Disallow: /admin',
            'Disallow: /dang-nhap',
            'Disallow: /dang-ky',
            'Disallow: /dang-xuat',
            'Disallow: /tai-khoan',
            'Disallow: /quen-mat-khau',
            'Disallow: /stream/',
            'Disallow: /videos/',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function buildSitemap(): string
    {
        $urls = [];
        $add = function (string $loc, $lastmod = null, string $freq = 'weekly', float $priority = 0.5, array $images = []) use (&$urls) {
            $urls[] = compact('loc', 'lastmod', 'freq', 'priority', 'images');
        };

        $latest = fn ($q) => optional($q->max('updated_at'));

        $add(route('home'), $latest(Video::query()), 'daily', 1.0);
        $add(route('courses.index'), $latest(Video::query()), 'daily', 0.9);
        $add(route('blogs.index'), $latest(Blog::query()), 'daily', 0.8);

        $about = Page::where('slug', 'about')->first();
        $add(route('about'), $about?->updated_at, 'monthly', 0.7);
        $add(route('contact.index'), null, 'yearly', 0.5);

        Page::where('slug', '!=', 'about')->orderBy('id')->get()->each(
            fn (Page $p) => $add(route('pages.show', $p->slug), $p->updated_at, 'monthly', 0.4)
        );

        Video::with('lesson')->where('status', 'ready')->orderBy('id')->get()->each(function (Video $v) use ($add) {
            $add(route('courses.video', [$v->lesson, $v]), $v->updated_at, 'weekly', $v->is_free ? 0.8 : 0.6, array_filter([[
                'loc' => route('stream', ['video' => $v->id, 'path' => 'poster.jpg']),
                'title' => $v->title,
            ]]));
        });

        Blog::orderByDesc('id')->get()->each(function (Blog $b) use ($add) {
            $imgs = $b->image_path ? [['loc' => asset('storage/'.$b->image_path), 'title' => $b->title]] : [];
            $add(route('blogs.show', $b->slug), $b->updated_at, 'monthly', 0.7, $imgs);
        });

        $e = fn (string $v) => htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $out = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";
        foreach ($urls as $u) {
            $out .= "  <url>\n    <loc>{$e($u['loc'])}</loc>\n";
            if ($u['lastmod']) {
                $out .= '    <lastmod>'.$u['lastmod']->toAtomString()."</lastmod>\n";
            }
            $out .= "    <changefreq>{$u['freq']}</changefreq>\n    <priority>".number_format($u['priority'], 1)."</priority>\n";
            foreach ($u['images'] as $img) {
                $out .= "    <image:image>\n      <image:loc>{$e($img['loc'])}</image:loc>\n      <image:title>{$e($img['title'])}</image:title>\n    </image:image>\n";
            }
            $out .= "  </url>\n";
        }

        return $out.'</urlset>'."\n";
    }
}
