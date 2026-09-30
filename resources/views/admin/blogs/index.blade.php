@extends('layouts.admin')
@section('title', 'Quản lý bài viết')
@section('header', 'Bài viết')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h3 class="text-xl font-bold text-slate-900">Bài viết <span class="ml-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-sm align-middle">{{ $total }}</span></h3>
        <p class="text-sm text-slate-500">Các bài viết hiện ở mục “Bài viết” trên website, bài mới nhất nằm trên cùng.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('blogs.index') }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition">Xem trên website</a>
        <a href="{{ route('admin.blogs.create') }}" class="inline-flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow transition"><span class="text-lg leading-none">+</span> Viết bài mới</a>
    </div>
</div>

<form method="GET" action="{{ route('admin.blogs.index') }}" class="mb-4 flex items-center gap-2 bg-white rounded-xl border border-slate-300 px-3 py-1.5 focus-within:ring-2 focus-within:ring-amber-200 focus-within:border-amber-500 transition">
    <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <input type="search" name="q" value="{{ $q }}" placeholder="Tìm bài viết theo tiêu đề hoặc nội dung…" class="flex-1 min-w-0 py-2 bg-transparent focus:outline-none text-slate-800 placeholder:text-slate-400">
    @if($q)<a href="{{ route('admin.blogs.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-800 px-2">Xoá tìm kiếm</a>@endif
    <button class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold transition">Tìm</button>
</form>

@forelse($items as $item)
    @if($loop->first)<div class="space-y-3">@endif
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row gap-4 hover:shadow-md transition">
        {{-- Ảnh đại diện --}}
        <div class="w-full sm:w-44 shrink-0 aspect-[16/10] rounded-xl overflow-hidden bg-gradient-to-br from-amber-100 to-rose-100 ring-1 ring-slate-200 flex items-center justify-center">
            @if($item->image_path)
                <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->title }}" loading="{{ $loop->index < 6 ? 'eager' : 'lazy' }}" class="w-full h-full object-cover">
            @else
                <span class="text-3xl" title="Bài chưa có ảnh đại diện">🌸</span>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <h4 class="font-bold text-slate-900 text-lg leading-snug line-clamp-2">{{ $item->title }}</h4>
            <p class="mt-1 text-sm text-slate-600 line-clamp-2">{{ \App\Support\Html::excerpt($item->content, 170) ?: 'Bài viết chưa có nội dung.' }}</p>
            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Đăng {{ $item->created_at->format('d/m/Y') }}</span>
                @if($item->updated_at->gt($item->created_at->copy()->addMinute()))
                    <span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.58m15.36 2A8 8 0 004.58 9m0 0H9m11 11v-5h-.58m0 0a8 8 0 01-15.36-2m15.36 2H15"/></svg>Sửa {{ $item->updated_at->diffForHumans() }}</span>
                @endif
                <span class="inline-flex items-center gap-1.5 {{ $item->image_path ? 'text-emerald-700' : 'text-amber-700' }}">{{ $item->image_path ? '✓ Có ảnh đại diện' : '⚠ Chưa có ảnh đại diện' }}</span>
            </div>
        </div>

        <div class="flex sm:flex-col items-center sm:items-stretch justify-end gap-2 shrink-0">
            <a href="{{ route('blogs.show', $item->slug) }}" target="_blank" class="px-3 py-2 rounded-lg text-sm font-semibold text-center text-slate-700 bg-slate-100 hover:bg-slate-200 transition">Xem</a>
            <a href="{{ route('admin.blogs.edit', $item) }}" class="px-3 py-2 rounded-lg text-sm font-semibold text-center text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition">Sửa</a>
            <form action="{{ route('admin.blogs.destroy', $item) }}" method="POST" data-confirm="Xoá bài viết “{{ $item->title }}”? Ảnh đại diện cũng bị xoá và không thể hoàn tác.">
                @csrf @method('DELETE')
                <button type="submit" class="w-full px-3 py-2 rounded-lg text-sm font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 transition">Xoá</button>
            </form>
        </div>
    </div>
    @if($loop->last)</div>@endif
@empty
    <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
        <p class="text-3xl mb-2">📝</p>
        <p class="text-slate-600 mb-4">{{ $q ? 'Không tìm thấy bài viết phù hợp.' : 'Chưa có bài viết nào.' }}</p>
        @unless($q)<a href="{{ route('admin.blogs.create') }}" class="inline-block bg-amber-600 hover:bg-amber-700 text-white font-semibold px-5 py-2.5 rounded-xl">Viết bài đầu tiên</a>@endunless
    </div>
@endforelse

<div class="mt-6">{{ $items->links() }}</div>
@endsection
