@php $isEdit = isset($lesson); @endphp
<form action="{{ $isEdit ? route('admin.lessons.update', $lesson) : route('admin.lessons.store') }}" method="POST" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div>
        <label for="title" class="block text-sm font-semibold text-slate-700 mb-1">Tên bài học <span class="text-rose-500">*</span></label>
        <input type="text" id="title" name="title" value="{{ old('title', $lesson->title ?? '') }}" required maxlength="255"
               placeholder="Ví dụ: Bài 1 - Cắm hoa hồng cơ bản"
               class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition">
    </div>

    <div>
        <label for="description" class="block text-sm font-semibold text-slate-700 mb-1">Mô tả ngắn</label>
        <textarea id="description" name="description" rows="4" maxlength="2000" placeholder="Bài học này nói về điều gì? (không bắt buộc)"
                  class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition">{{ old('description', $lesson->description ?? '') }}</textarea>
    </div>

    <div class="max-w-xs">
        <label for="order" class="block text-sm font-semibold text-slate-700 mb-1">Thứ tự hiển thị</label>
        <input type="number" id="order" name="order" min="0" value="{{ old('order', $lesson->order ?? $nextOrder ?? 0) }}"
               class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition">
        <p class="text-xs text-slate-500 mt-1">Số nhỏ hiển thị trước.</p>
    </div>

    <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2">
        <a href="{{ route('admin.lessons.index') }}" class="px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold text-center hover:bg-slate-50 transition">Quay lại</a>
        <button type="submit" class="px-6 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow transition">
            {{ $isEdit ? 'Lưu thay đổi' : 'Tạo bài học' }}
        </button>
    </div>
</form>
