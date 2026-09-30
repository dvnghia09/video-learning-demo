@extends('layouts.admin')
@section('title', 'Quản lý trang tĩnh')
@section('header', 'Trang tĩnh')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h3 class="text-xl font-bold text-slate-900">Trang tĩnh</h3>
        <p class="text-sm text-slate-500">Các trang nội dung như Giới thiệu, Chính sách… Trang có slug <b>about</b> là “Giới thiệu” trên menu; các trang khác hiện ở cuối website (footer).</p>
    </div>
    <a href="{{ route('admin.pages.create') }}" class="inline-flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow transition"><span class="text-lg leading-none">+</span> Thêm trang</a>
</div>

@forelse($items as $item)
    @if($loop->first)<div class="space-y-3">@endif
    @php
        $isAbout = $item->slug === 'about';
        $url = $isAbout ? route('about') : route('pages.show', $item->slug);
        $confirm = "Xoá trang “{$item->title}”? Đường dẫn của trang cũng không còn dùng được."
            .($isAbout ? "\nĐây là trang “Giới thiệu” trên menu, xoá xong menu sẽ hiện “Trang đang được cập nhật”." : '');
    @endphp
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 shrink-0 rounded-xl flex items-center justify-center {{ $isAbout ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h4 class="font-bold text-slate-900 text-lg truncate">{{ $item->title }}</h4>
                @if($isAbout)<span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold ring-1 ring-amber-200">Trên menu</span>
                @else<span class="px-2.5 py-0.5 rounded-full bg-sky-100 text-sky-700 text-xs font-semibold ring-1 ring-sky-200">Ở footer</span>@endif
            </div>
            <a href="{{ $url }}" target="_blank" class="mt-0.5 inline-flex items-center gap-1.5 text-sm font-mono text-indigo-700 hover:underline break-all">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>{{ parse_url($url, PHP_URL_PATH) }}
            </a>
            <p class="mt-1 text-sm text-slate-600 line-clamp-1">{{ \App\Support\Html::excerpt($item->content, 140) ?: 'Trang chưa có nội dung.' }}</p>
            <p class="mt-2 text-xs text-slate-500">Cập nhật {{ $item->updated_at->format('d/m/Y H:i') }} · {{ $item->updated_at->diffForHumans() }}</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ $url }}" target="_blank" class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">Xem</a>
            <a href="{{ route('admin.pages.edit', $item) }}" class="px-3 py-2 rounded-lg text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition">Sửa</a>
            <form action="{{ route('admin.pages.destroy', $item) }}" method="POST" data-confirm="{{ $confirm }}">
                @csrf @method('DELETE')
                <button type="submit" class="px-3 py-2 rounded-lg text-sm font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 transition">Xoá</button>
            </form>
        </div>
    </div>
    @if($loop->last)</div>@endif
@empty
    <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
        <p class="text-3xl mb-2">📄</p>
        <p class="text-slate-600 mb-4">Chưa có trang nào.</p>
        <a href="{{ route('admin.pages.create') }}" class="inline-block bg-amber-600 hover:bg-amber-700 text-white font-semibold px-5 py-2.5 rounded-xl">Tạo trang đầu tiên</a>
    </div>
@endforelse

<div class="mt-6">{{ $items->links() }}</div>
@endsection
