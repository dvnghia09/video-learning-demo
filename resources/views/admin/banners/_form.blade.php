@php
    $isEdit = isset($banner);
    $existing = $isEdit ? \Illuminate\Support\Facades\Storage::url($banner->image_path) : null;
@endphp

<form action="{{ $isEdit ? route('admin.banners.update', $banner) : route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl space-y-6"
      x-data="{
          current: @js($existing), preview: null, name: '', size: '', dims: '', dragging: false, error: '', active: {{ old('is_active', $banner->is_active ?? true) ? 'true' : 'false' }},
          pick(file) {
              this.error = '';
              if (!file) return;
              if (!/^image\/(jpeg|png|webp)$/.test(file.type)) { this.error = 'Ảnh phải là JPG, PNG hoặc WebP.'; this.$refs.file.value = ''; return; }
              if (file.size > 5 * 1024 * 1024) { this.error = 'Ảnh tối đa 5MB (ảnh này ' + (file.size / 1048576).toFixed(1) + 'MB).'; this.$refs.file.value = ''; return; }
              if (this.preview) URL.revokeObjectURL(this.preview);
              this.preview = URL.createObjectURL(file); this.name = file.name; this.size = (file.size / 1024).toFixed(0) + ' KB'; this.dims = '';
              const img = new Image(); img.onload = () => { this.dims = img.naturalWidth + ' × ' + img.naturalHeight + ' px'; }; img.src = this.preview;
          },
          reset() { if (this.preview) URL.revokeObjectURL(this.preview); this.preview = null; this.name = ''; this.$refs.file.value = ''; this.error = ''; },
          drop(e) { const f = e.dataTransfer.files[0]; if (!f) return; const dt = new DataTransfer(); dt.items.add(f); this.$refs.file.files = dt.files; this.pick(f); },
      }">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
        <div class="flex items-center justify-between gap-3">
            <h3 class="font-bold text-slate-900">Ảnh banner <span class="text-rose-500">*</span></h3>
            <span class="text-xs text-slate-500">Nên dùng ảnh ngang <b>1920×900px</b>, JPG/PNG/WebP, tối đa 5MB</span>
        </div>

        <input type="file" name="image" x-ref="file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="pick($event.target.files[0])" @unless($isEdit) required @endunless>

        {{-- Khung xem trước: ảnh mới chọn (nếu có) hoặc ảnh hiện tại --}}
        <div x-show="preview || current" x-cloak>
            <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 aspect-[1920/900] relative">
                <img :src="preview || current" alt="Xem trước banner" class="w-full h-full object-cover">
                <span x-show="preview" x-cloak class="absolute top-3 left-3 px-3 py-1 rounded-full bg-emerald-600 text-white text-xs font-bold shadow">Ảnh mới chọn</span>
                <span x-show="!preview && current" x-cloak class="absolute top-3 left-3 px-3 py-1 rounded-full bg-slate-900/80 text-white text-xs font-bold shadow">Ảnh hiện tại</span>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <button type="button" @click="$refs.file.click()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition" x-text="preview ? 'Chọn ảnh khác' : 'Đổi ảnh'"></button>
                <button type="button" x-show="preview" x-cloak @click="reset()" class="px-4 py-2.5 rounded-xl text-rose-700 bg-rose-50 hover:bg-rose-100 text-sm font-semibold transition">Bỏ ảnh vừa chọn</button>
                <p x-show="preview" x-cloak class="text-sm text-slate-600 min-w-0 truncate"><span x-text="name"></span> · <span x-text="size"></span><span x-show="dims"> · <span x-text="dims"></span></span></p>
            </div>
            @if($isEdit)<p x-show="preview" x-cloak class="mt-2 text-xs text-slate-500">Bấm “Cập nhật” để thay ảnh cũ bằng ảnh mới. Ảnh cũ sẽ bị xoá khỏi máy chủ.</p>@endif
        </div>

        {{-- Chưa có ảnh nào: vùng kéo thả --}}
        <div x-show="!preview && !current" x-cloak @click="$refs.file.click()" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="dragging = false; drop($event)"
             :class="dragging ? 'border-amber-500 bg-amber-50' : 'border-slate-300 hover:border-amber-400 hover:bg-slate-50'"
             class="cursor-pointer rounded-2xl border-2 border-dashed p-12 text-center transition">
            <div class="mx-auto w-14 h-14 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            </div>
            <p class="font-semibold text-slate-800">Kéo thả ảnh vào đây hoặc bấm để chọn</p>
            <p class="text-sm text-slate-500 mt-1">Ảnh sẽ hiện ở đầu trang chủ</p>
        </div>


        <p x-show="error" x-text="error" x-cloak class="text-sm font-semibold text-rose-600"></p>
        @error('image')<p class="text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror
    </section>

    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <label class="flex items-start gap-4 cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <button type="button" role="switch" :aria-checked="active" @click="active = !active" class="relative shrink-0 mt-0.5 w-12 h-7 rounded-full transition-colors" :class="active ? 'bg-emerald-500' : 'bg-slate-300'">
                <span class="absolute top-0.5 left-0.5 w-6 h-6 rounded-full bg-white shadow transition-transform" :class="active && 'translate-x-5'"></span>
            </button>
            <input type="checkbox" name="is_active" value="1" x-model="active" class="sr-only">
            <span>
                <span class="block font-bold text-slate-900" x-text="active ? 'Đang hiển thị trên trang chủ' : 'Đang ẩn'"></span>
                <span class="block text-sm text-slate-500">Banner được bật sẽ chạy luân phiên ở đầu trang chủ. Tắt để ẩn mà không cần xoá.</span>
            </span>
        </label>
    </section>

    <div class="flex flex-col-reverse sm:flex-row gap-3">
        <a href="{{ route('admin.banners.index') }}" class="px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold text-center hover:bg-slate-50 transition">Quay lại</a>
        <button type="submit" class="px-8 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow transition">{{ $isEdit ? 'Cập nhật' : 'Thêm banner' }}</button>
    </div>
</form>
