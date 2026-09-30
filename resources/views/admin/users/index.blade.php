@extends('layouts.admin')
@section('title', 'Quản lý người dùng')
@section('header', 'Người dùng')

@php
    $tabs = ['' => ['Tất cả', $counts['all']], 'vip' => ['⭐ VIP', $counts['vip']], 'free' => ['Miễn phí', $counts['free']], 'admin' => ['Quản trị viên', $counts['admin']]];
    $ico = 'w-4 h-4 shrink-0';
@endphp

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h3 class="text-xl font-bold text-slate-900">Người dùng</h3>
        <p class="text-sm text-slate-500"><b>Thành viên VIP</b> xem được toàn bộ video. Tài khoản <b>Miễn phí</b> chỉ xem các video miễn phí.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow transition"><span class="text-lg leading-none">+</span> Thêm người dùng</a>
</div>

{{-- Tìm kiếm + lọc --}}
<div class="flex flex-col lg:flex-row lg:items-center gap-3 mb-4">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex-1 flex items-center gap-2 bg-white rounded-xl border border-slate-300 px-3 py-1.5 focus-within:ring-2 focus-within:ring-amber-200 focus-within:border-amber-500 transition">
        @if($filter)<input type="hidden" name="type" value="{{ $filter }}">@endif
        <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="search" name="q" value="{{ $q }}" placeholder="Tìm theo tên, số điện thoại hoặc email…" class="flex-1 min-w-0 py-2 bg-transparent focus:outline-none text-slate-800 placeholder:text-slate-400">
        <button class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold transition">Tìm</button>
    </form>
    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach($tabs as $key => [$label, $n])
            @php $active = ($filter ?? '') === $key; @endphp
            <a href="{{ route('admin.users.index', array_filter(['type' => $key, 'q' => $q])) }}"
               class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition {{ $active ? 'bg-amber-600 text-white shadow' : 'bg-white border border-slate-200 text-slate-600 hover:border-amber-300 hover:text-amber-700' }}">
                {{ $label }} <span class="px-2 py-0.5 rounded-full text-xs {{ $active ? 'bg-white/25 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $n }}</span>
            </a>
        @endforeach
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-100 text-sm">
        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-5 py-3 text-left">Người dùng</th>
                <th class="px-5 py-3 text-left">Liên hệ</th>
                <th class="px-5 py-3 text-left">Vai trò</th>
                <th class="px-5 py-3 text-left">Gói học</th>
                <th class="px-5 py-3 text-left">Tham gia</th>
                <th class="px-5 py-3 text-right">Thao tác</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($items as $item)
                @php $me = $item->is(auth()->user()); @endphp
                <tr class="hover:bg-slate-50/70 transition">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 shrink-0 rounded-full overflow-hidden bg-gradient-to-br from-amber-500 to-rose-500 text-white font-bold flex items-center justify-center">
                                @if($item->avatarUrl())<img src="{{ $item->avatarUrl() }}" alt="" class="w-full h-full object-cover" loading="lazy">@else{{ $item->initial() }}@endif
                            </span>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900 truncate">{{ $item->name }} @if($me)<span class="ml-1 px-1.5 py-0.5 rounded bg-slate-200 text-slate-600 text-[11px] font-semibold">Bạn</span>@endif</p>
                                <p class="text-xs text-slate-500">Mã #{{ $item->id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <a href="tel:{{ $item->phone }}" class="inline-flex items-center gap-1.5 font-semibold text-emerald-700 hover:underline whitespace-nowrap">
                            <svg class="{{ $ico }}" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1C10.61 21 3 13.39 3 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/></svg>{{ $item->phone }}
                        </a>
                        <p class="text-xs text-slate-500 mt-0.5 truncate max-w-[14rem]">{{ $item->email ?: 'Chưa có email' }}</p>
                    </td>
                    <td class="px-5 py-4">
                        @if($item->role === 'admin')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200 whitespace-nowrap">
                                <svg class="{{ $ico }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.62-4.02A11.96 11.96 0 0112 2.94a11.96 11.96 0 01-8.62 3.04A12 12 0 003 9c0 5.59 3.82 10.29 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.05-.13-2.07-.38-3.04z"/></svg>Quản trị viên
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 ring-1 ring-slate-200 whitespace-nowrap">
                                <svg class="{{ $ico }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>Học viên
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        @if($item->is_vip)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 ring-1 ring-amber-300 whitespace-nowrap">⭐ Thành viên VIP</span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 ring-1 ring-slate-200 whitespace-nowrap">Miễn phí</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap">
                        <p class="text-slate-800">{{ $item->created_at->format('d/m/Y') }}</p>
                        <p class="text-xs text-slate-500">{{ $item->created_at->diffForHumans() }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                            @if($item->is_vip)
                                <form method="POST" action="{{ route('admin.users.vip', $item) }}" data-confirm="Chuyển “{{ $item->name }}” về tài khoản miễn phí? Người này sẽ không xem được các video dành cho VIP nữa." data-confirm-title="Hạ về miễn phí" data-confirm-ok="Hạ gói" data-confirm-type="warning">@csrf
                                    <button class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">Hạ về miễn phí</button></form>
                            @else
                                <form method="POST" action="{{ route('admin.users.vip', $item) }}">@csrf
                                    <button class="px-3 py-2 rounded-lg text-sm font-semibold text-amber-800 bg-amber-100 hover:bg-amber-200 transition">⭐ Nâng cấp VIP</button></form>
                            @endif
                            <a href="{{ route('admin.users.edit', $item) }}" class="px-3 py-2 rounded-lg text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition">Sửa</a>
                            @unless($me)
                                <form action="{{ route('admin.users.destroy', $item) }}" method="POST" data-confirm="Xoá người dùng “{{ $item->name }}”? Toàn bộ tiến độ học của họ cũng bị xoá và không thể hoàn tác.">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-2 rounded-lg text-sm font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 transition">Xoá</button>
                                </form>
                            @endunless
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-14 text-center text-slate-500">
                    <p class="text-lg mb-1">🔍</p>{{ ($q || $filter) ? 'Không tìm thấy người dùng phù hợp.' : 'Chưa có người dùng nào.' }}
                </td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="p-4 border-t border-slate-100">{{ $items->links() }}</div>
</div>
@endsection
