@php
    $isEdit = isset($blog);
    $in = 'w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition bg-white';
    $existing = $isEdit && $blog->image_path ? \Illuminate\Support\Facades\Storage::url($blog->image_path) : null;
@endphp

<form action="{{ $isEdit ? route('admin.blogs.update', $blog) : route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data"
      class="grid gap-6 lg:grid-cols-3 max-w-6xl"
      x-data="{
          title: @js(old('title', $blog->title ?? '')), slug: @js(old('slug', $blog->slug ?? '')), touched: {{ ($isEdit || old('slug')) ? 'true' : 'false' }},
          auto() { if (this.touched) return; this.slug = this.title.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 80); },
          preview: @js($existing), name: '', size: '', removed: false, dragging: false, error: '',
          pick(file) {
              this.error = '';
              if (!file) return;
              if (!/^image\/(jpeg|png|webp)$/.test(file.type)) { this.error = 'Ảnh phải là JPG, PNG hoặc WebP.'; this.$refs.file.value = ''; return; }
              if (file.size > 5 * 1024 * 1024) { this.error = 'Ảnh tối đa 5MB (ảnh này ' + (file.size / 1048576).toFixed(1) + 'MB).'; this.$refs.file.value = ''; return; }
              if (this.preview && this.preview.startsWith('blob:')) URL.revokeObjectURL(this.preview);
              this.preview = URL.createObjectURL(file); this.name = file.name; this.size = (file.size / 1024).toFixed(0) + ' KB'; this.removed = false;
          },
          clear() { this.preview = null; this.name = ''; this.$refs.file.value = ''; this.removed = true; },
          drop(e) { const f = e.dataTransfer.files[0]; if (!f) return; const dt = new DataTransfer(); dt.items.add(f); this.$refs.file.files = dt.files; this.pick(f); },
      }">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- ===== Cột trái: tiêu đề + nội dung ===== --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <label for="title" class="block text-sm font-semibold text-slate-700 mb-1.5">Tiêu đề bài viết <span class="text-rose-500">*</span></label>
            <input id="title" name="title" x-model="title" @input="auto()" required maxlength="255" class="{{ $in }} text-lg font-semibold" placeholder="Nhập tiêu đề hấp dẫn cho bài viết">
            @error('title')<p class="text-rose-600 text-sm mt-1.5">{{ $message }}</p>@enderror

            <label for="slug" class="block text-sm font-semibold text-slate-700 mt-5 mb-1.5">Đường dẫn bài viết (slug) <span class="font-normal text-slate-500">— tốt cho SEO</span></label>
            <input id="slug" name="slug" x-model="slug" @input="touched = true" maxlength="100" class="{{ $in }} font-mono text-sm" placeholder="tu-dong-tao-tu-tieu-de">
            <p class="text-xs text-slate-500 mt-1.5">Địa chỉ bài: <span class="font-mono text-slate-700" x-text="'{{ url('/bai-viet') }}/' + (slug || '…')"></span>
                @if($isEdit)<span class="block mt-0.5 text-amber-700">Đổi đường dẫn sẽ làm liên kết cũ đã chia sẻ không còn dùng được.</span>@endif</p>
            @error('slug')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-baseline justify-between mb-1.5">
                <label for="content" class="block text-sm font-semibold text-slate-700">Nội dung <span class="text-rose-500">*</span></label>
                <span class="text-xs text-slate-500">Ảnh chèn vào bài được lưu thành đường link, không làm nặng database.</span>
            </div>
            <textarea id="content" name="content" rows="14" class="{{ $in }}">{{ old('content', $blog->content ?? '') }}</textarea>
            @error('content')<p class="text-rose-600 text-sm mt-1.5">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- ===== Cột phải: ảnh đại diện + đăng bài ===== --}}
    <div class="space-y-6 lg:sticky lg:top-6 h-fit">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <p class="block text-sm font-semibold text-slate-700 mb-3">Ảnh đại diện</p>

            <input type="file" name="image" x-ref="file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="pick($event.target.files[0])">
            <input type="hidden" name="remove_image" :value="removed && !name ? 1 : 0">

            {{-- Có ảnh: xem trước --}}
            <div x-show="preview" x-cloak>
                <div class="aspect-[16/10] rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                    <img :src="preview" alt="Xem trước ảnh đại diện" class="w-full h-full object-cover">
                </div>
                <p class="mt-2 text-sm text-slate-600 truncate" x-show="name" x-text="name + ' · ' + size"></p>
                <div class="mt-3 flex gap-2">
                    <button type="button" @click="$refs.file.click()" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">Đổi ảnh</button>
                    <button type="button" @click="clear()" class="px-4 py-2.5 rounded-xl bg-rose-50 text-rose-700 text-sm font-semibold hover:bg-rose-100 transition">Bỏ ảnh</button>
                </div>
            </div>

            {{-- Chưa có ảnh: vùng kéo thả --}}
            <div x-show="!preview" @click="$refs.file.click()" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="dragging = false; drop($event)"
                 :class="dragging ? 'border-amber-500 bg-amber-50' : 'border-slate-300 hover:border-amber-400 hover:bg-slate-50'"
                 class="cursor-pointer rounded-xl border-2 border-dashed p-8 text-center transition">
                <div class="mx-auto w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.6-4.6a2 2 0 012.8 0L16 16m-2-2l1.6-1.6a2 2 0 012.8 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <p class="font-semibold text-slate-800 text-sm">Kéo thả ảnh vào đây<br>hoặc bấm để chọn</p>
                <p class="text-xs text-slate-500 mt-1">JPG, PNG, WebP · tối đa 5MB · tỉ lệ 16:10 đẹp nhất</p>
            </div>

            <p x-show="error" x-text="error" x-cloak class="mt-3 text-sm text-rose-600"></p>
            @error('image')<p class="mt-3 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-3">
            <button type="submit" class="w-full px-6 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow transition">{{ $isEdit ? 'Lưu thay đổi' : 'Đăng bài' }}</button>
            <a href="{{ route('admin.blogs.index') }}" class="block w-full px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold text-center hover:bg-slate-50 transition">Quay lại</a>
            @if($isEdit)
                <a href="{{ route('blogs.show', $blog->slug) }}" target="_blank" class="block text-center text-sm font-semibold text-amber-700 hover:text-amber-800">Xem bài trên website &rarr;</a>
            @endif
        </div>
    </div>
</form>

@include('admin.partials.rich-editor', ['selector' => '#content'])
