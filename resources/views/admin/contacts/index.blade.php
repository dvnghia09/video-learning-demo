@extends('layouts.admin')
@section('title', 'Khách liên hệ')
@section('header', 'Khách liên hệ')

@php
    $tabs = ['' => ['Tất cả', $counts->sum()], 'new' => ['Mới', $counts['new'] ?? 0], 'read' => ['Đã xem', $counts['read'] ?? 0], 'handled' => ['Đã liên hệ lại', $counts['handled'] ?? 0]];
    $ico = 'w-4 h-4 shrink-0';
@endphp

@section('content')
<div class="mb-6">
    <h3 class="text-xl font-bold text-slate-900">Khách liên hệ</h3>
    <p class="text-sm text-slate-500">Lời nhắn khách gửi từ trang Liên hệ. Bấm “Xem” để đọc và đánh dấu đã liên hệ lại.</p>
</div>

{{-- Bộ lọc theo trạng thái --}}
<div class="flex gap-2 overflow-x-auto pb-1 mb-4">
    @foreach($tabs as $key => [$label, $n])
        @php $active = ($filter ?? '') === $key; @endphp
        <a href="{{ route('admin.contacts.index', $key ? ['status' => $key] : []) }}"
           class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition {{ $active ? 'bg-amber-600 text-white shadow' : 'bg-white border border-slate-200 text-slate-600 hover:border-amber-300 hover:text-amber-700' }}">
            {{ $label }}
            <span class="px-2 py-0.5 rounded-full text-xs {{ $active ? 'bg-white/25 text-white' : ($key === 'new' && $n ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600') }}">{{ $n }}</span>
        </a>
    @endforeach
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-100 text-sm">
        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-5 py-3 text-left"><span class="inline-flex items-center gap-1.5"><svg class="{{ $ico }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>Khách hàng</span></th>
                <th class="px-5 py-3 text-left"><span class="inline-flex items-center gap-1.5"><svg class="{{ $ico }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.2l-2.26 1.13a11 11 0 005.52 5.52l1.13-2.26a1 1 0 011.2-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z"/></svg>Số điện thoại</span></th>
                <th class="px-5 py-3 text-left"><span class="inline-flex items-center gap-1.5"><svg class="{{ $ico }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>Nội dung</span></th>
                <th class="px-5 py-3 text-left"><span class="inline-flex items-center gap-1.5"><svg class="{{ $ico }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Ngày gửi</span></th>
                <th class="px-5 py-3 text-left">Trạng thái</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($items as $item)
                <tr class="hover:bg-slate-50/70 transition {{ $item->status === 'new' ? 'bg-rose-50/40' : '' }}">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 shrink-0 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($item->name, 0, 1)) }}</span>
                            <span class="font-semibold text-slate-900 {{ $item->status === 'new' ? '' : 'font-medium' }}">{{ $item->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap">
                        @if($item->phone)
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $item->phone) }}" class="inline-flex items-center gap-1.5 text-emerald-700 font-semibold hover:underline">
                                <svg class="{{ $ico }}" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1C10.61 21 3 13.39 3 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/></svg>{{ $item->phone }}
                            </a>
                        @else
                            <span class="text-slate-400">Không có</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 max-w-xs"><p class="text-slate-600 truncate">{{ $item->message }}</p></td>
                    <td class="px-5 py-4 whitespace-nowrap">
                        <p class="text-slate-800">{{ $item->created_at->format('d/m/Y H:i') }}</p>
                        <p class="text-xs text-slate-500">{{ $item->created_at->diffForHumans() }}</p>
                    </td>
                    <td class="px-5 py-4">@include('admin.contacts._status', ['contact' => $item])</td>
                    <td class="px-5 py-4 text-right whitespace-nowrap">
                        <a href="{{ route('admin.contacts.show', $item) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition">
                            <svg class="{{ $ico }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.46 12C3.73 7.94 7.52 5 12 5s8.27 2.94 9.54 7c-1.27 4.06-5.06 7-9.54 7S3.73 16.06 2.46 12z"/></svg>Xem
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-14 text-center text-slate-500">
                    <p class="text-lg mb-1">📭</p>
                    {{ $filter ? 'Không có liên hệ nào ở trạng thái này.' : 'Chưa có khách nào gửi liên hệ.' }}
                </td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="p-4 border-t border-slate-100">{{ $items->links() }}</div>
</div>
@endsection
