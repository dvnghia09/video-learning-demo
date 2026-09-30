@extends('layouts.admin')
@section('title', 'Chi tiết liên hệ')
@section('header', 'Chi tiết liên hệ')

@php $ico = 'w-5 h-5 shrink-0'; @endphp

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-4 min-w-0">
                <span class="w-12 h-12 shrink-0 rounded-full bg-amber-100 text-amber-700 text-xl font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($contact->name, 0, 1)) }}</span>
                <div class="min-w-0">
                    <h3 class="text-lg font-bold text-slate-900 truncate">{{ $contact->name }}</h3>
                    <p class="text-sm text-slate-500">Gửi lúc {{ $contact->created_at->format('H:i \n\g\à\y d/m/Y') }} · {{ $contact->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @include('admin.contacts._status', ['contact' => $contact])
        </div>

        <dl class="px-6 py-5 grid gap-4 sm:grid-cols-2">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 shrink-0 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center"><svg class="{{ $ico }}" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1C10.61 21 3 13.39 3 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/></svg></span>
                <div><dt class="text-xs font-semibold uppercase text-slate-500">Số điện thoại</dt>
                    <dd class="font-bold text-slate-900">@if($contact->phone)<a href="tel:{{ preg_replace('/[^\d+]/', '', $contact->phone) }}" class="hover:text-emerald-700">{{ $contact->phone }}</a>@else<span class="font-normal text-slate-400">Không có</span>@endif</dd></div>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 shrink-0 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center"><svg class="{{ $ico }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></span>
                <div><dt class="text-xs font-semibold uppercase text-slate-500">Ngày gửi</dt><dd class="font-bold text-slate-900">{{ $contact->created_at->format('d/m/Y H:i') }}</dd></div>
            </div>
        </dl>

        <div class="px-6 pb-6">
            <p class="flex items-center gap-2 text-xs font-semibold uppercase text-slate-500 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>Nội dung lời nhắn
            </p>
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-slate-800 leading-relaxed">{!! nl2br(e($contact->message)) !!}</div>
        </div>
    </div>

    {{-- Hành động --}}
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.contacts.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition">&larr; Quay lại danh sách</a>
        @if($contact->phone)
            <a href="tel:{{ preg_replace('/[^\d+]/', '', $contact->phone) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow transition">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1C10.61 21 3 13.39 3 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/></svg>Gọi khách
            </a>
        @endif
        @if($contact->status !== 'handled')
            <form method="POST" action="{{ route('admin.contacts.status', $contact) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="handled">
                <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Đánh dấu đã liên hệ lại</button></form>
        @else
            <form method="POST" action="{{ route('admin.contacts.status', $contact) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="read">
                <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition">Bỏ đánh dấu</button></form>
        @endif
        @if($contact->status !== 'new')
            <form method="POST" action="{{ route('admin.contacts.status', $contact) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="new">
                <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-rose-700 font-semibold hover:bg-rose-50 transition">Đánh dấu chưa xem</button></form>
        @endif
    </div>
</div>
@endsection
