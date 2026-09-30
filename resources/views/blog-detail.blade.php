@extends('layouts.public')
@section('title', $blog->title)
@section('meta_description', \App\Support\Html::excerpt($blog->content, 155))
@section('og_type', 'article')
@if($blog->image_path)
@section('og_image', asset('storage/'.$blog->image_path))
@endif
@push('head')
<meta property="article:published_time" content="{{ $blog->created_at->toAtomString() }}">
<meta property="article:modified_time" content="{{ $blog->updated_at->toAtomString() }}">
@endpush
@push('jsonld')
{!! \App\Support\JsonLd::tag(array_filter([
    '@type' => 'Article',
    'headline' => \Illuminate\Support\Str::limit($blog->title, 110, ''),
    'description' => \App\Support\Html::excerpt($blog->content, 200),
    'image' => $blog->image_path ? [asset('storage/'.$blog->image_path)] : null,
    'datePublished' => $blog->created_at->toAtomString(), 'dateModified' => $blog->updated_at->toAtomString(),
    'inLanguage' => 'vi', 'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('blogs.show', $blog->slug)],
    'author' => ['@type' => 'Organization', 'name' => \App\Models\Setting::bag()->get('company_name', \App\Models\Setting::bag()->brand())],
    'publisher' => array_filter(['@type' => 'Organization', 'name' => \App\Models\Setting::bag()->get('company_name', \App\Models\Setting::bag()->brand()), 'logo' => \App\Models\Setting::bag()->logoUrl() ? ['@type' => 'ImageObject', 'url' => \App\Models\Setting::bag()->logoUrl()] : null]),
])) !!}
@endpush

@section('content')
@include('partials.page-header', ['title' => $blog->title, 'crumbs' => [['Bài viết', route('blogs.index')]]])

<article class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
    <div class="bg-white rounded-2xl border border-stone-300 shadow-md overflow-hidden">
        @if($blog->image_path)
            <img src="{{ asset('storage/'.$blog->image_path) }}" alt="{{ $blog->title }}" class="w-full max-h-[420px] object-cover">
        @endif
        <div class="p-6 sm:p-10">
            <time class="text-sm font-semibold text-amber-700">{{ $blog->created_at->format('d/m/Y') }}</time>
            <div class="prose prose-stone prose-lg max-w-none mt-4 prose-headings:font-bold prose-a:text-amber-700 prose-img:rounded-2xl prose-img:shadow-md">{!! \App\Support\Html::clean($blog->content) !!}</div>
        </div>
    </div>
    <div class="mt-6 flex justify-between">
        <a href="{{ route('blogs.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white border border-stone-300 text-stone-700 font-semibold hover:border-amber-400 hover:text-amber-700 transition">&larr; Tất cả bài viết</a>
    </div>
</article>
@endsection
