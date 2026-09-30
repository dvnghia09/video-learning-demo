@extends('layouts.public')
@section('title', $heading)
@section('robots', 'noindex,follow')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-16 sm:py-24 text-center">
    <p class="text-7xl sm:text-8xl mb-4" aria-hidden="true">{{ $icon }}</p>
    <p class="text-lg font-bold text-amber-800">{{ $code }}</p>
    <h1 class="mt-1 text-3xl sm:text-4xl font-extrabold text-stone-900">{{ $heading }}</h1>
    <p class="mt-4 text-xl text-stone-700 leading-relaxed">{{ $text }}</p>
    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ route('home') }}" class="inline-flex justify-center px-8 py-3.5 rounded-full bg-gradient-to-r from-amber-700 to-rose-600 text-white font-bold text-lg shadow-lg hover:shadow-xl transition">Về trang chủ</a>
        <a href="{{ route('courses.index') }}" class="inline-flex justify-center px-8 py-3.5 rounded-full border-2 border-amber-700 text-amber-800 font-bold text-lg hover:bg-amber-50 transition">Xem bài học</a>
    </div>
</div>
@endsection
