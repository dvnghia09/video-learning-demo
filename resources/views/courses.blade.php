@extends('layouts.public')
@section('title', 'Bài học')
@section('meta_description', 'Danh sách các bài học và video hướng dẫn cắm hoa từng bước, dễ hiểu cho mọi lứa tuổi.')

@section('content')
@include('partials.page-header', ['title' => 'Bài học', 'subtitle' => 'Chọn bài học và xem video hướng dẫn từng bước.'])

@php
    $canAll = fn ($v) => $v->canBeWatchedBy(auth()->user());
    $totalVideos = $lessons->sum(fn ($l) => $l->videos->count());
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-10" x-data="{ q: '', open: {{ $lessons->first()?->id ?? 0 }} }">

    {{-- Tìm kiếm --}}
    <form @submit.prevent class="flex items-center gap-2 bg-white rounded-2xl border border-stone-300 p-1.5 shadow-md focus-within:ring-4 focus-within:ring-amber-100 focus-within:border-amber-500 transition mb-3">
        <svg class="w-5 h-5 text-stone-500 ml-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="search" x-model="q" placeholder="Nhập tên bài học hoặc video cần tìm…" class="flex-1 min-w-0 py-2.5 bg-transparent focus:outline-none text-stone-800 placeholder:text-stone-500">
        <button type="button" @click="q = q.trim()" class="px-5 py-2.5 rounded-xl bg-amber-700 hover:bg-amber-800 text-white font-semibold transition">Tìm kiếm</button>
    </form>
    <p class="text-sm text-stone-700 mb-6">{{ $lessons->count() }} bài học · {{ $totalVideos }} video</p>

    <div class="space-y-4">
        @forelse($lessons as $lesson)
            @php $haystack = mb_strtolower($lesson->title.' '.$lesson->videos->pluck('title')->implode(' ')); @endphp
            <section x-show="!q.trim() || @js($haystack).includes(q.trim().toLowerCase())" class="bg-white rounded-2xl border border-stone-300 shadow-md overflow-hidden">
                <button type="button" @click="open = open === {{ $lesson->id }} ? 0 : {{ $lesson->id }}"
                        class="w-full px-5 sm:px-6 py-4 flex items-center justify-between gap-4 text-left hover:bg-amber-50/60 transition">
                    <span class="flex items-center gap-4 min-w-0">
                        <span class="w-10 h-10 shrink-0 rounded-xl bg-amber-100 text-amber-700 font-bold flex items-center justify-center">{{ $loop->iteration }}</span>
                        <span class="min-w-0">
                            <span class="block font-bold text-stone-900 text-lg truncate">{{ $lesson->title }}</span>
                            <span class="block text-sm text-stone-600">{{ $lesson->videos->count() }} video</span>
                        </span>
                    </span>
                    <svg class="w-5 h-5 text-stone-500 transition-transform shrink-0" :class="open === {{ $lesson->id }} || q.trim() ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="open === {{ $lesson->id }} || q.trim()" x-collapse class="border-t border-stone-100">
                    @if($lesson->description)<p class="px-6 pt-4 text-stone-600">{{ $lesson->description }}</p>@endif
                    <ul class="p-3 sm:p-4 space-y-2">
                        @forelse($lesson->videos as $video)
                            @php $ok = $canAll($video); @endphp
                            <li data-vstatus-id="{{ $video->id }}" data-status="{{ $video->status }}" x-show="!q.trim() || @js(mb_strtolower($video->title.' '.$lesson->title)).includes(q.trim().toLowerCase())">
                                <a href="{{ route('courses.video', [$lesson, $video]) }}" @unless($ok) data-help="upgrade" @endunless
                                   class="group flex items-center gap-4 p-3 rounded-xl border border-transparent hover:border-amber-300 hover:bg-amber-50/60 transition">
                                    <span class="w-11 h-11 shrink-0 rounded-full flex items-center justify-center {{ $ok ? 'bg-amber-100 text-amber-700 group-hover:bg-amber-700 group-hover:text-white' : 'bg-stone-100 text-stone-500' }} transition">
                                        @if($ok)
                                            <svg class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.84A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.27l9.34-5.89a1.5 1.5 0 000-2.54L6.3 2.84z"/></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                        @endif
                                    </span>
                                    <span class="flex-1 min-w-0">
                                        <span class="block font-semibold text-stone-800 truncate">{{ $video->title }}</span>
                                        <span class="mt-1 flex flex-wrap gap-1.5 text-sm font-semibold">
                                            @if($video->is_free)
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Miễn phí</span>
                                            @elseif($ok)
                                                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">VIP</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700">🔒 {{ auth()->check() ? 'Cần nâng cấp gói · liên hệ Zalo' : 'VIP · cần đăng nhập rồi nâng cấp gói' }}</span>
                                            @endif
                                            @if($video->status !== 'ready')<span data-vstatus-label="{{ $video->id }}" class="px-2 py-0.5 rounded-full bg-stone-100 text-stone-600">Đang xử lý</span>@endif
                                        </span>
                                    </span>
                                    <span class="hidden sm:inline-flex shrink-0 px-4 py-2 rounded-full text-sm font-semibold {{ $ok ? 'bg-amber-700 text-white group-hover:bg-amber-800' : 'bg-stone-100 text-stone-600' }} transition">{{ $ok ? 'Học ngay' : 'Cách nâng cấp' }}</span>
                                </a>
                            </li>
                        @empty
                            <li class="text-center text-stone-700 py-6">Chưa có video nào trong bài này.</li>
                        @endforelse
                    </ul>
                </div>
            </section>
        @empty
            <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-stone-300 text-stone-600">Chưa có bài học nào.</div>
        @endforelse
    </div>
</div>
@include('partials.video-status-poller')
@endsection
