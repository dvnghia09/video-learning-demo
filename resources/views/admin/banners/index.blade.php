@extends('layouts.admin')
@section('title', 'Quản lý Banners')
@section('header', 'Banner trang chủ')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h3 class="text-xl font-bold text-slate-900">Banner trang chủ</h3>
        <p class="text-sm text-slate-500">Các banner đang bật sẽ tự chạy luân phiên trên đầu trang chủ. Nên dùng ảnh ngang <b>1920×900px</b> (JPG/WebP, dưới 500KB).</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition">Xem trang chủ</a>
        <a href="{{ route('admin.banners.create') }}" class="inline-flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow transition"><span class="text-lg leading-none">+</span> Thêm banner</a>
    </div>
</div>

@forelse($items as $item)
    @if($loop->first)<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">@endif
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden {{ $item->is_active ? '' : 'opacity-70' }}">
        <div class="aspect-[1920/900] bg-slate-100">
            <img src="{{ Storage::url($item->image_path) }}" alt="Banner {{ $item->id }}" loading="lazy" class="w-full h-full object-cover">
        </div>
        <div class="p-4 flex items-center justify-between gap-3">
            <div>
                <p class="font-semibold text-slate-800">Banner #{{ $loop->iteration }}</p>
                <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $item->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ $item->is_active ? 'Đang hiển thị' : 'Đang ẩn' }}</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.banners.edit', $item) }}" class="px-3 py-2 rounded-lg text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition">Sửa</a>
                <form action="{{ route('admin.banners.destroy', $item) }}" method="POST" data-confirm="Xoá banner này? Ảnh cũng sẽ bị xoá khỏi máy chủ và không thể hoàn tác.">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-2 rounded-lg text-sm font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 transition">Xoá</button>
                </form>
            </div>
        </div>
    </div>
    @if($loop->last)</div>@endif
@empty
    <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
        <p class="text-slate-600 mb-4">Chưa có banner nào. Trang chủ đang dùng ảnh mặc định.</p>
        <a href="{{ route('admin.banners.create') }}" class="inline-block bg-amber-600 hover:bg-amber-700 text-white font-semibold px-5 py-2.5 rounded-xl">Thêm banner đầu tiên</a>
    </div>
@endforelse

<div class="mt-6">{{ $items->links() }}</div>
@endsection
