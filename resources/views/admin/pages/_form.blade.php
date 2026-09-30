@php
    $isEdit = isset($page);
    $in = 'w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition bg-white';
@endphp

<form action="{{ $isEdit ? route('admin.pages.update', $page) : route('admin.pages.store') }}" method="POST" class="grid gap-6 lg:grid-cols-3 max-w-6xl"
      x-data="{
          title: @js(old('title', $page->title ?? '')),
          slug: @js(old('slug', $page->slug ?? '')),
          touched: {{ $isEdit || old('slug') ? 'true' : 'false' }},
          auto() { if (this.touched) return; this.slug = this.title.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/đ/g, 'd').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, ''); },
      }">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
            <div>
                <label for="title" class="block text-sm font-semibold text-slate-700 mb-1.5">Tiêu đề trang <span class="text-rose-500">*</span></label>
                <input id="title" name="title" x-model="title" @input="auto()" required maxlength="255" class="{{ $in }} text-lg font-semibold" placeholder="VD: Chính sách bảo mật">
                @error('title')<p class="text-rose-600 text-sm mt-1.5">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="slug" class="block text-sm font-semibold text-slate-700 mb-1.5">Đường dẫn (slug) <span class="text-rose-500">*</span></label>
                <input id="slug" name="slug" x-model="slug" @input="touched = true" required maxlength="100" class="{{ $in }} font-mono text-sm" placeholder="chinh-sach-bao-mat">
                <p class="text-xs text-slate-500 mt-1.5">
                    Địa chỉ trang: <span class="font-mono text-slate-700" x-text="slug === 'about' ? '{{ url('/gioi-thieu') }}' : '{{ url('/trang') }}/' + (slug || '…')"></span>
                    <span x-show="slug === 'about'" class="ml-1 font-semibold text-amber-700">(trang “Giới thiệu” trên menu)</span>
                </p>
                @error('slug')<p class="text-rose-600 text-sm mt-1.5">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-baseline justify-between mb-1.5">
                <label for="content" class="block text-sm font-semibold text-slate-700">Nội dung</label>
                <span class="text-xs text-slate-500">Ảnh chèn vào được lưu thành đường link.</span>
            </div>
            <textarea id="content" name="content" rows="14" class="{{ $in }}">{{ old('content', $page->content ?? '') }}</textarea>
            @error('content')<p class="text-rose-600 text-sm mt-1.5">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="space-y-6 lg:sticky lg:top-6 h-fit">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-3">
            <button type="submit" class="w-full px-6 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow transition">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo trang' }}</button>
            <a href="{{ route('admin.pages.index') }}" class="block w-full px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold text-center hover:bg-slate-50 transition">Quay lại</a>
            @if($isEdit)
                <a :href="slug === 'about' ? '{{ url('/gioi-thieu') }}' : '{{ url('/trang') }}/' + slug" target="_blank" class="block text-center text-sm font-semibold text-amber-700 hover:text-amber-800">Xem trang trên website &rarr;</a>
            @endif
        </div>
        <div class="rounded-2xl bg-sky-50 border border-sky-100 p-5 text-sm text-sky-900 space-y-2">
            <p class="font-semibold">Gợi ý</p>
            <p>Slug <b>about</b> là trang <b>Giới thiệu</b> trên menu. Các trang khác hiện ở cuối website (footer) và có địa chỉ dạng <span class="font-mono">/trang/slug</span>.</p>
        </div>
    </div>
</form>

@include('admin.partials.rich-editor', ['selector' => '#content'])
