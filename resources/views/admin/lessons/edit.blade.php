@extends('layouts.admin')
@section('title', 'Sửa bài học')
@section('header', 'Sửa bài học')
@section('content')
<div class="grid gap-6 lg:grid-cols-5 max-w-6xl">
    <div class="lg:col-span-3 bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200">
        @include('admin.lessons._form')
    </div>

    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-800">Video trong bài ({{ $lesson->videos->count() }})</h3>
            <a href="{{ route('admin.videos.create', ['lesson_id' => $lesson->id]) }}" class="text-sm font-semibold text-amber-600 hover:text-amber-700">+ Thêm video</a>
        </div>
        @forelse($lesson->videos as $video)
            <a href="{{ route('admin.videos.edit', $video) }}" class="flex items-center justify-between py-2.5 border-t border-slate-100 hover:bg-slate-50 -mx-2 px-2 rounded-lg">
                <span class="text-sm text-slate-700 truncate pr-3">{{ $video->title }}</span>
                @include('admin.videos._status', ['video' => $video])
            </a>
        @empty
            <p class="text-sm text-slate-500">Chưa có video nào. Bấm “Thêm video” để tải lên.</p>
        @endforelse
    </div>
</div>
@endsection
