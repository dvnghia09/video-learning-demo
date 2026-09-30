@extends('layouts.public')
@section('title', $page ? $page->title : 'Giới thiệu')

@section('content')
@include('partials.page-header', ['title' => $page ? $page->title : 'Giới thiệu'])

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
    <div class="bg-white rounded-2xl border border-stone-300 shadow-md p-6 sm:p-10">
        @if($page)
            <div class="prose prose-stone prose-lg max-w-none prose-a:text-amber-700 prose-img:rounded-2xl">{!! \App\Support\Html::clean($page->content) !!}</div>
        @else
            <div class="text-center py-10 text-stone-600">Trang đang được cập nhật…</div>
        @endif
    </div>
</div>
@endsection
