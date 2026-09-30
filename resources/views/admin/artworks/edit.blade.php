@extends('layouts.admin')
@section('title', 'Sửa tác phẩm')
@section('header', 'Sửa tác phẩm')

@section('content')
<form action="{{ route('admin.artworks.update', $artwork) }}" method="POST" enctype="multipart/form-data" class="max-w-3xl grid gap-6 md:grid-cols-5"
      x-data="{ preview: @js($artwork->url()), name: '', error: '',
          pick(f) { this.error = ''; if (!f) return;
              if (!/^image\/(jpeg|png|webp)$/.test(f.type)) { this.error = 'Ảnh phải là JPG, PNG hoặc WebP.'; this.$refs.file.value = ''; return; }
              if (f.size > 8 * 1024 * 1024) { this.error = 'Ảnh tối đa 8MB.'; this.$refs.file.value = ''; return; }
              this.preview = URL.createObjectURL(f); this.name = f.name; } }">
    @csrf @method('PUT')

    <div class="md:col-span-2">
        <div class="aspect-square rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 shadow-sm"><img :src="preview" alt="" class="w-full h-full object-cover"></div>
        <input type="file" name="image" x-ref="file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="pick($event.target.files[0])">
        <button type="button" @click="$refs.file.click()" class="mt-3 w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">Đổi ảnh</button>
        <p x-show="name" x-text="name" x-cloak class="mt-1 text-xs text-slate-500 truncate"></p>
        <p x-show="error" x-text="error" x-cloak class="mt-1 text-sm text-rose-600"></p>
        @error('image')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-3 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5 h-fit">
        <div>
            <label for="title" class="block text-sm font-semibold text-slate-700 mb-1.5">Tiêu đề</label>
            <input id="title" name="title" value="{{ old('title', $artwork->title) }}" maxlength="150" placeholder="VD: Lẵng hoa hồng pastel" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition">
            <p class="text-xs text-slate-500 mt-1.5">Hiện khi rê chuột vào ảnh trên trang chủ và làm mô tả ảnh cho người khiếm thị.</p>
        </div>
        <div class="max-w-[10rem]">
            <label for="sort_order" class="block text-sm font-semibold text-slate-700 mb-1.5">Thứ tự</label>
            <input id="sort_order" type="number" min="0" max="9999" name="sort_order" value="{{ old('sort_order', $artwork->sort_order) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition">
            <p class="text-xs text-slate-500 mt-1.5">Số nhỏ hiện trước.</p>
        </div>
        <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 cursor-pointer hover:bg-slate-50 transition">
            <input type="checkbox" name="is_active" value="1" class="h-5 w-5 rounded border-slate-300 text-amber-600 focus:ring-amber-500" @checked(old('is_active', $artwork->is_active))>
            <span class="font-semibold text-slate-800">Hiển thị trên trang chủ</span>
        </label>
        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-1">
            <a href="{{ route('admin.artworks.index') }}" class="px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold text-center hover:bg-slate-50 transition">Quay lại</a>
            <button type="submit" class="px-6 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow transition">Lưu thay đổi</button>
        </div>
    </div>
</form>
@endsection
