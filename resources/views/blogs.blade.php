@extends('layouts.public')
@section('title', 'Bài viết')
@if($q !== '')@section('robots', 'noindex,follow')@endif
@section('meta_description', 'Chia sẻ kinh nghiệm, mẹo và cảm hứng cắm hoa mỗi ngày.')

@section('content')
@include('partials.page-header', ['title' => 'Bài viết', 'subtitle' => 'Kinh nghiệm, mẹo hay và cảm hứng cắm hoa.'])

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    <form action="{{ route('blogs.index') }}" method="GET" role="search" class="mb-8 flex flex-col sm:flex-row gap-3 max-w-2xl mx-auto">
        <label for="blog-q" class="sr-only">Tìm bài viết</label>
        <input id="blog-q" type="search" name="q" value="{{ $q }}" maxlength="100" placeholder="Nhập từ khoá, ví dụ: hoa hồng, bình cắm…"
               class="flex-1 rounded-full border-2 border-stone-300 bg-white px-6 py-3.5 text-lg text-stone-900 focus:border-amber-600 focus:ring-4 focus:ring-amber-200 outline-none">
        <button type="submit" class="px-8 py-3.5 rounded-full bg-gradient-to-r from-amber-700 to-rose-600 text-white text-lg font-bold shadow-md hover:shadow-lg transition">Tìm kiếm</button>
    </form>
    @if($q !== '')
        <p class="mb-6 text-lg text-stone-700 text-center">Tìm thấy <strong>{{ $blogs->total() }}</strong> bài viết cho “<strong>{{ $q }}</strong>”. <a href="{{ route('blogs.index') }}" class="font-semibold text-amber-800 underline">Xoá tìm kiếm</a></p>
    @endif
    @forelse($blogs as $blog)
        @if($loop->first)<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@endif
        <a href="{{ route('blogs.show', $blog->slug) }}" class="group flex flex-col bg-white rounded-2xl border border-stone-300 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition overflow-hidden">
            <div class="aspect-[16/10] bg-gradient-to-br from-amber-100 to-rose-100 overflow-hidden">
                @if($blog->image_path)
                    <img src="{{ asset('storage/'.$blog->image_path) }}" alt="{{ $blog->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                    <div class="w-full h-full flex items-center justify-center text-5xl">🌸</div>
                @endif
            </div>
            <div class="p-5 flex flex-col flex-1">
                <time class="text-sm font-semibold text-amber-700">{{ $blog->created_at->format('d/m/Y') }}</time>
                <h2 class="mt-1 text-lg font-bold text-stone-900 leading-snug group-hover:text-amber-700 transition line-clamp-2">{{ $blog->title }}</h2>
                <p class="mt-2 text-stone-700 text-sm line-clamp-3">{{ \App\Support\Html::excerpt($blog->content, 140) }}</p>
                <span class="mt-auto pt-4 text-sm font-semibold text-amber-700">Đọc tiếp &rarr;</span>
            </div>
        </a>
        @if($loop->last)</div>@endif
    @empty
        <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-stone-300 text-stone-600">{{ $q !== '' ? 'Không tìm thấy bài viết phù hợp. Hãy thử từ khoá khác nhé.' : 'Chưa có bài viết nào.' }}</div>
    @endforelse

    <div class="mt-8">{{ $blogs->links() }}</div>
</div>
@endsection
