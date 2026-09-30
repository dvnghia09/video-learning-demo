@php $isEdit = isset($video); @endphp
<div>
    <label for="title" class="block text-sm font-semibold text-slate-700 mb-1">Tên video <span class="text-rose-500">*</span></label>
    <input type="text" id="title" name="title" required maxlength="255" @if(!$isEdit) x-model="title" @endif
           value="{{ old('title', $video->title ?? '') }}" placeholder="Ví dụ: Cách cắm hoa hồng bằng bình thấp"
           class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition">
</div>

<div>
    <label for="lesson_id" class="block text-sm font-semibold text-slate-700 mb-1">Thuộc bài học <span class="text-rose-500">*</span></label>
    @if($lessons->isEmpty())
        <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
            Chưa có bài học nào. <a href="{{ route('admin.lessons.create') }}" class="font-bold underline">Tạo bài học trước</a> rồi quay lại tải video.
        </div>
    @else
        <select id="lesson_id" name="lesson_id" required class="w-full rounded-xl border border-slate-300 px-4 py-3 bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition">
            @foreach($lessons as $lesson)
                <option value="{{ $lesson->id }}" @selected(old('lesson_id', $video->lesson_id ?? $selectedLesson ?? null) == $lesson->id)>{{ $lesson->title }}</option>
            @endforeach
        </select>
    @endif
</div>

<label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 cursor-pointer hover:bg-slate-50 transition">
    <input type="checkbox" name="is_free" value="1" class="mt-1 h-5 w-5 rounded border-slate-300 text-amber-600 focus:ring-amber-500"
           @checked(old('is_free', $video->is_free ?? false))>
    <span>
        <span class="block font-semibold text-slate-800">Cho xem miễn phí</span>
        <span class="block text-sm text-slate-500">Bỏ chọn nếu chỉ thành viên VIP mới được xem video này.</span>
    </span>
</label>
