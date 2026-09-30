<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $blogs = Blog::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.addcslashes(mb_substr($q, 0, 100), '%_\\').'%';
                $query->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('content', 'like', $like));
            })
            ->latest()->paginate(9)->withQueryString();

        return view('blogs', compact('blogs', 'q'));
    }

    /** Địa chỉ dạng /bai-viet/{slug}. Địa chỉ cũ dùng số thứ tự (/bai-viet/12) được chuyển hướng 301 sang slug. */
    public function show(string $slug): View|RedirectResponse
    {
        $blog = Blog::where('slug', $slug)->first();

        if (! $blog && ctype_digit($slug) && ($old = Blog::find($slug))) {
            return redirect()->route('blogs.show', $old->slug, 301);
        }

        abort_unless($blog, 404);

        return view('blog-detail', compact('blog'));
    }
}
