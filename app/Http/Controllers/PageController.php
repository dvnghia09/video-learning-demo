<?php
namespace App\Http\Controllers;
use App\Models\Page;

class PageController extends Controller
{
    public function about()
    {
        $page = Page::where('slug', 'about')->first();
        return view('page', compact('page'));
    }

    public function show(Page $page)
    {
        return view('page', compact('page'));
    }
}
