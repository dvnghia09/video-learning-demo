@extends('layouts.admin')
@section('title', 'Tác phẩm nổi bật')
@section('header', 'Tác phẩm nổi bật')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h3 class="text-xl font-bold text-slate-900">Các tác phẩm nổi bật</h3>
        <p class="text-sm text-slate-500">Hiện ở mục “Các tác phẩm nổi bật” trên trang chủ dạng băng chuyền tự trượt, khách bấm vào ảnh để xem phóng to (tối đa 24 ảnh đang bật). Dùng mũi tên để đổi thứ tự.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('home') }}#tac-pham" target="_blank" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition">Xem trang chủ</a>
        <a href="{{ route('admin.artworks.create') }}" class="inline-flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow transition"><span class="text-lg leading-none">+</span> Thêm ảnh</a>
    </div>
</div>

@forelse($items as $item)
    @if($loop->first)<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">@endif
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col {{ $item->is_active ? '' : 'opacity-70' }}">
        <div class="relative aspect-square bg-slate-100">
            <img src="{{ $item->url() }}" alt="{{ $item->title ?: 'Tác phẩm' }}" loading="lazy" class="w-full h-full object-cover">
            <span class="absolute top-2 left-2 px-2.5 py-1 rounded-full bg-white/95 text-xs font-bold text-slate-700 shadow">#{{ $loop->iteration }}</span>
            <span class="absolute top-2 right-2 px-2.5 py-1 rounded-full text-xs font-semibold shadow {{ $item->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ $item->is_active ? 'Đang hiện' : 'Đang ẩn' }}</span>
        </div>
        <div class="p-4 flex-1 flex flex-col gap-3">
            <p class="font-semibold text-slate-800 truncate">{{ $item->title ?: 'Chưa có tiêu đề' }}</p>
            <div class="mt-auto flex items-center justify-between gap-2">
                <div class="flex gap-1">
                    <form method="POST" action="{{ route('admin.artworks.move', $item) }}">@csrf<input type="hidden" name="dir" value="up">
                        <button class="w-9 h-9 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 disabled:opacity-30" title="Đưa lên trước" @disabled($loop->first)>&larr;</button></form>
                    <form method="POST" action="{{ route('admin.artworks.move', $item) }}">@csrf<input type="hidden" name="dir" value="down">
                        <button class="w-9 h-9 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 disabled:opacity-30" title="Đưa ra sau" @disabled($loop->last)>&rarr;</button></form>
                </div>
                <div class="flex items-center gap-1.5">
                    <form method="POST" action="{{ route('admin.artworks.toggle', $item) }}">@csrf
                        <button class="px-3 py-2 rounded-lg text-sm font-semibold {{ $item->is_active ? 'text-slate-700 bg-slate-100 hover:bg-slate-200' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }} transition">{{ $item->is_active ? 'Ẩn' : 'Hiện' }}</button></form>
                    <a href="{{ route('admin.artworks.edit', $item) }}" class="px-3 py-2 rounded-lg text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition">Sửa</a>
                    <form action="{{ route('admin.artworks.destroy', $item) }}" method="POST" data-confirm="Xoá tác phẩm này? Ảnh cũng bị xoá khỏi máy chủ và không thể hoàn tác.">@csrf @method('DELETE')
                        <button class="px-3 py-2 rounded-lg text-sm font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 transition">Xoá</button></form>
                </div>
            </div>
        </div>
    </div>
    @if($loop->last)</div>@endif
@empty
    <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
        <p class="text-slate-600 mb-4">Chưa có tác phẩm nào. Mục này sẽ tự ẩn trên trang chủ cho đến khi bạn thêm ảnh.</p>
        <a href="{{ route('admin.artworks.create') }}" class="inline-block bg-amber-600 hover:bg-amber-700 text-white font-semibold px-5 py-2.5 rounded-xl">Thêm ảnh đầu tiên</a>
    </div>
@endforelse
@endsection
