@extends('layouts.admin')
@section('title', 'Admin Dashboard')
@section('header', 'Tổng quan (Dashboard)')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stat 1 -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center">
        <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 mr-4 shrink-0">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Tổng Users</p>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\User::count() }}</p>
        </div>
    </div>
    
    <!-- Stat 2 -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center">
        <div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 mr-4 shrink-0">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Tổng Videos</p>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Video::count() }}</p>
        </div>
    </div>

    <!-- Stat 3 -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center">
        <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center text-amber-600 mr-4 shrink-0">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Tổng Bài học</p>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Lesson::count() }}</p>
        </div>
    </div>

    <!-- Stat 4 -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center">
        <div class="w-14 h-14 rounded-full bg-purple-50 flex items-center justify-center text-purple-600 mr-4 shrink-0">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Liên hệ mới</p>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Contact::count() }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 text-center mt-12">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 text-amber-600 mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
    </div>
    <h3 class="text-xl font-bold text-slate-800 mb-2">Chào mừng đến với Floral Admin!</h3>
    <p class="text-slate-500 max-w-md mx-auto">Sử dụng thanh menu bên trái để quản lý toàn bộ hệ thống của bạn. Giao diện đã được nâng cấp theo chuẩn thiết kế hiện đại.</p>
</div>
@endsection
