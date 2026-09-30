@extends('layouts.admin')
@section('title', 'Sửa video')
@section('header', 'Sửa video')

@section('content')
<div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200 max-w-2xl">
    <div class="mb-6 flex items-center gap-3">
        @include('admin.videos._status', ['video' => $video])
        <span class="text-sm text-slate-500">Muốn đổi file video? Hãy xoá video này và tải lên video mới.</span>
    </div>
    <form action="{{ route('admin.videos.update', $video) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')
        @include('admin.videos._fields')

        <div class="max-w-xs">
            <label for="order" class="block text-sm font-semibold text-slate-700 mb-1">Thứ tự trong bài</label>
            <input type="number" id="order" name="order" min="0" value="{{ old('order', $video->order) }}"
                   class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition">
        </div>

        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2">
            <a href="{{ route('admin.videos.index') }}" class="px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold text-center hover:bg-slate-50 transition">Quay lại</a>
            <button type="submit" class="px-6 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow transition">Lưu thay đổi</button>
        </div>
    </form>
</div>
@endsection
