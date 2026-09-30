@extends('layouts.admin')
@section('title', 'Thêm tác phẩm')
@section('header', 'Thêm tác phẩm')

@section('content')
<form action="{{ route('admin.artworks.store') }}" method="POST" enctype="multipart/form-data" class="max-w-3xl"
      x-data="{
          files: [], dragging: false, error: '',
          add(list) {
              this.error = '';
              const ok = [];
              for (const f of list) {
                  if (!/^image\/(jpeg|png|webp)$/.test(f.type)) { this.error = 'Bỏ qua “' + f.name + '”: chỉ nhận JPG, PNG, WebP.'; continue; }
                  if (f.size > 8 * 1024 * 1024) { this.error = 'Bỏ qua “' + f.name + '”: lớn hơn 8MB.'; continue; }
                  ok.push({ file: f, url: URL.createObjectURL(f) });
              }
              this.files = this.files.concat(ok).slice(0, 12);
              this.sync();
          },
          remove(i) { URL.revokeObjectURL(this.files[i].url); this.files.splice(i, 1); this.sync(); },
          sync() { const dt = new DataTransfer(); this.files.forEach(x => dt.items.add(x.file)); this.$refs.input.files = dt.files; },
      }">
    @csrf
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        <div>
            <p class="text-sm font-semibold text-slate-700 mb-2">Chọn ảnh tác phẩm <span class="text-rose-500">*</span></p>
            <input type="file" name="images[]" multiple accept="image/png,image/jpeg,image/webp" x-ref="input" class="hidden" @change="add($event.target.files)">

            <div @click="$refs.input.click()" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="dragging = false; add($event.dataTransfer.files)"
                 :class="dragging ? 'border-amber-500 bg-amber-50' : 'border-slate-300 hover:border-amber-400 hover:bg-slate-50'"
                 class="cursor-pointer rounded-2xl border-2 border-dashed p-10 text-center transition">
                <div class="mx-auto w-14 h-14 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                </div>
                <p class="font-semibold text-slate-800">Kéo thả ảnh vào đây hoặc bấm để chọn</p>
                <p class="text-sm text-slate-500 mt-1">Chọn được nhiều ảnh cùng lúc (tối đa 12) · JPG, PNG, WebP · mỗi ảnh tối đa 8MB · ảnh vuông đẹp nhất</p>
            </div>
            <p x-show="error" x-text="error" x-cloak class="mt-2 text-sm text-rose-600"></p>
            @error('images')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            @error('images.*')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div x-show="files.length" x-cloak>
            <p class="text-sm font-semibold text-slate-700 mb-2"><span x-text="files.length"></span> ảnh đã chọn</p>
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                <template x-for="(f, i) in files" :key="f.url">
                    <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                        <img :src="f.url" class="w-full h-full object-cover" alt="">
                        <button type="button" @click="remove(i)" class="absolute top-1.5 right-1.5 w-7 h-7 rounded-full bg-white/95 text-rose-600 font-bold shadow hover:bg-rose-50" title="Bỏ ảnh này">&times;</button>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="files.length === 1" x-cloak>
            <label for="title" class="block text-sm font-semibold text-slate-700 mb-1.5">Tiêu đề (không bắt buộc)</label>
            <input id="title" name="title" maxlength="150" placeholder="VD: Lẵng hoa hồng pastel" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition">
            <p class="text-xs text-slate-500 mt-1.5">Chỉ nhập được khi thêm 1 ảnh. Thêm nhiều ảnh thì đặt tiêu đề sau bằng nút “Sửa”.</p>
        </div>

        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2">
            <a href="{{ route('admin.artworks.index') }}" class="px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold text-center hover:bg-slate-50 transition">Quay lại</a>
            <button type="submit" :disabled="!files.length" :class="!files.length && 'opacity-50 cursor-not-allowed'" class="px-6 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow transition">Thêm vào trang chủ</button>
        </div>
    </div>
</form>
@endsection
