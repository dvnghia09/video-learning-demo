@extends('layouts.admin')
@section('title', 'Cài đặt website')
@section('header', 'Cài đặt website')

@php
    $in = 'w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-800 placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition bg-white';
    $lb = 'block text-sm font-semibold text-slate-700 mb-1.5';
    $hint = 'text-xs text-slate-500 mt-1.5';
    $file = 'block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:font-semibold file:text-slate-700 hover:file:bg-slate-200';

    $phones = old('phones_number') !== null
        ? collect(old('phones_number'))->map(fn ($n, $i) => ['number' => $n, 'label' => old('phones_label')[$i] ?? ''])->values()->all()
        : $s->phones();
    if (! $phones) { $phones = [['number' => '', 'label' => '']]; }

    $tabs = ['info' => ['🏪', 'Thông tin'], 'contact' => ['📞', 'Liên hệ & nút nổi'], 'seo' => ['🔎', 'SEO'], 'share' => ['📣', 'Chia sẻ'], 'embed' => ['🗺️', 'Bản đồ & Fanpage']];
    $tabOf = fn ($k) => match (true) {
        in_array($k, ['brand_name', 'logo', 'company_name', 'email', 'address']) => 'info',
        in_array($k, ['zalo_url', 'messenger_link', 'facebook_url']) || str_starts_with($k, 'phones') => 'contact',
        in_array($k, ['meta_title', 'meta_description', 'meta_keywords', 'google_analytics']) => 'seo',
        in_array($k, ['favicon', 'og_image', 'og_title', 'og_description']) => 'share',
        default => 'embed',
    };
    $errTabs = collect($errors->keys())->map($tabOf);
    $firstErr = $errTabs->first() ?? 'info';
@endphp

@section('content')
<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="max-w-5xl pb-28"
      x-data="{
          tab: @js($firstErr),
          title: @js(old('meta_title', $s->get('meta_title', ''))),
          desc: @js(old('meta_description', $s->get('meta_description', ''))),
          ogTitle: @js(old('og_title', $s->get('og_title', ''))),
          ogDesc: @js(old('og_description', $s->get('og_description', ''))),
          zalo: @js(old('zalo_url', $s->get('zalo_url', ''))),
          mess: @js(old('messenger_link', $s->get('messenger_link', ''))),
          phones: @js($phones),
          add() { if (this.phones.length < 10) this.phones.push({number: '', label: ''}) },
          remove(i) { this.phones.splice(i, 1); if (!this.phones.length) this.add() },
          makeDefault(i) { this.phones.unshift(this.phones.splice(i, 1)[0]) },
          preview(e, id) { const f = e.target.files[0]; if (f) { const el = document.getElementById(id); el.src = URL.createObjectURL(f); el.classList.remove('hidden'); const ph = document.getElementById(id + '_ph'); if (ph) ph.classList.add('hidden') } },
      }">
    @csrf @method('PUT')

    {{-- ============ Thanh tab ============ --}}
    <div class="flex gap-1.5 overflow-x-auto p-1.5 mb-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
        @foreach($tabs as $key => [$icon, $label])
            <button type="button" @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'bg-amber-600 text-white shadow' : 'text-slate-600 hover:bg-slate-100'"
                    class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-sm transition">
                <span>{{ $icon }}</span>{{ $label }}
                @if($errTabs->contains($key))<span class="w-2 h-2 rounded-full bg-rose-500"></span>@endif
            </button>
        @endforeach
    </div>

    {{-- ============================================================ --}}
    {{-- TAB 1: THÔNG TIN                                              --}}
    {{-- ============================================================ --}}
    <section x-show="tab === 'info'" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm divide-y divide-slate-100">
        <div class="px-6 py-4 flex flex-wrap items-center gap-2 text-xs">
            <span class="font-semibold text-slate-500">📍 Hiển thị ở:</span>
            <span class="px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">Header</span>
            <span class="px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">Footer</span>
            <span class="px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">Trang Liên hệ</span>
            <span class="px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">Tiêu đề trang</span>
        </div>

        <div class="grid md:grid-cols-3 gap-6 px-6 py-6">
            <div><h3 class="font-semibold text-slate-900">Thương hiệu</h3><p class="text-sm text-slate-500 mt-1">Tên và logo hiển thị trên header. Có logo thì chữ tên thương hiệu sẽ được ẩn.</p></div>
            <div class="md:col-span-2 space-y-5">
                <div>
                    <label class="{{ $lb }}" for="brand_name">Tên thương hiệu</label>
                    <input id="brand_name" name="brand_name" value="{{ old('brand_name', $s->get('brand_name')) }}" class="{{ $in }}" placeholder="VD: FloralArt" maxlength="100">
                    @error('brand_name')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $lb }}">Logo</label>
                    <div class="flex items-center gap-4">
                        <div class="w-28 h-20 shrink-0 rounded-xl border border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden">
                            <img id="logo_prev" src="{{ $s->logoUrl() }}" alt="" class="max-w-full max-h-full object-contain {{ $s->logoUrl() ? '' : 'hidden' }}">
                            <span id="logo_prev_ph" class="text-xs text-slate-400 {{ $s->logoUrl() ? 'hidden' : '' }}">Chưa có logo</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/gif" @change="preview($event, 'logo_prev')" class="{{ $file }}">
                            @if($s->logoUrl())
                                <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-600 cursor-pointer"><input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-rose-600"> Xoá logo hiện tại</label>
                            @endif
                            <p class="{{ $hint }}">PNG/WebP nền trong suốt, tối đa 2MB. Hiển thị cao tối đa 50px.</p>
                            @error('logo')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6 px-6 py-6">
            <div><h3 class="font-semibold text-slate-900">Thông tin công ty</h3><p class="text-sm text-slate-500 mt-1">Hiện ở footer và trang Liên hệ.</p></div>
            <div class="md:col-span-2 space-y-5">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $lb }}" for="company_name">Tên công ty</label>
                        <input id="company_name" name="company_name" value="{{ old('company_name', $s->get('company_name')) }}" class="{{ $in }}" placeholder="Công ty TNHH …" maxlength="150">
                    </div>
                    <div>
                        <label class="{{ $lb }}" for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $s->get('email')) }}" class="{{ $in }}" placeholder="ten@congty.vn" maxlength="150">
                        @error('email')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="{{ $lb }}" for="address">Địa chỉ</label>
                    <input id="address" name="address" value="{{ old('address', $s->get('address')) }}" class="{{ $in }}" placeholder="Số nhà, đường, phường/xã, tỉnh/thành" maxlength="255">
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- TAB 2: LIÊN HỆ & NÚT NỔI                                      --}}
    {{-- ============================================================ --}}
    <section x-show="tab === 'contact'" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm divide-y divide-slate-100">
        <div class="px-6 py-4 flex flex-wrap items-center gap-2 text-xs">
            <span class="font-semibold text-slate-500">📍 Hiển thị ở:</span>
            <span class="px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">Nút nổi góc màn hình</span>
            <span class="px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">Nút “Gọi ngay” trên header</span>
            <span class="px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">Footer</span>
            <span class="px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">Trang Liên hệ</span>
        </div>

        <div class="grid md:grid-cols-3 gap-6 px-6 py-6">
            <div><h3 class="font-semibold text-slate-900">Số điện thoại</h3><p class="text-sm text-slate-500 mt-1">Số đầu tiên là số mặc định, dùng cho nút gọi điện. Tối đa 10 số.</p></div>
            <div class="md:col-span-2">
                <div class="hidden sm:grid grid-cols-[2rem_1fr_1fr_5.5rem_2.25rem] gap-2 px-0.5 mb-1.5 text-xs font-semibold text-slate-500">
                    <span></span><span>Số điện thoại</span><span>Nhãn (VD: Kinh doanh, CSKH)</span><span></span><span></span>
                </div>
                <div class="space-y-2.5">
                    <template x-for="(p, i) in phones" :key="i">
                        <div class="grid grid-cols-[2rem_1fr_2.25rem] sm:grid-cols-[2rem_1fr_1fr_5.5rem_2.25rem] gap-2 items-center">
                            <span class="w-7 h-7 rounded-full text-xs font-bold flex items-center justify-center text-white" :class="i === 0 ? 'bg-amber-500' : 'bg-slate-400'" x-text="i + 1"></span>
                            <input name="phones_number[]" x-model="p.number" class="{{ $in }}" placeholder="0919 295 123" maxlength="20" inputmode="tel">
                            <input name="phones_label[]" x-model="p.label" class="{{ $in }} col-start-2 sm:col-start-auto" placeholder="Nhãn" maxlength="60">
                            <div class="hidden sm:block">
                                <span x-show="i === 0" class="block text-center text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg py-2">Mặc định</span>
                                <button type="button" x-show="i > 0" @click="makeDefault(i)" class="w-full text-xs font-semibold text-slate-600 border border-slate-300 rounded-lg py-2 hover:bg-slate-50">Đặt mặc định</button>
                            </div>
                            <button type="button" @click="remove(i)" class="row-start-1 col-start-3 sm:row-start-auto sm:col-start-auto w-9 h-9 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 text-lg leading-none" title="Xoá số này">&times;</button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="add()" x-show="phones.length < 10" class="mt-3 text-sm font-semibold text-amber-700 border border-amber-200 bg-amber-50 hover:bg-amber-100 rounded-lg px-3 py-2">+ Thêm số điện thoại</button>
                @error('phones_number.*')<p class="text-rose-600 text-sm mt-2">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6 px-6 py-6">
            <div><h3 class="font-semibold text-slate-900">Zalo, Messenger, Facebook</h3><p class="text-sm text-slate-500 mt-1">Điền link nào thì nút tương ứng sẽ hiện.</p></div>
            <div class="md:col-span-2 space-y-5">
                <div>
                    <label class="{{ $lb }}" for="zalo_url">Zalo</label>
                    <input id="zalo_url" name="zalo_url" x-model="zalo" class="{{ $in }}" placeholder="https://zalo.me/0912345678 hoặc số điện thoại">
                    @error('zalo_url')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $lb }}" for="messenger_link">Messenger (Facebook)</label>
                    <input id="messenger_link" name="messenger_link" x-model="mess" class="{{ $in }}" placeholder="https://m.me/tenfanpage">
                    @error('messenger_link')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $lb }}" for="facebook_url">Trang Facebook</label>
                    <input id="facebook_url" name="facebook_url" value="{{ old('facebook_url', $s->get('facebook_url')) }}" class="{{ $in }}" placeholder="https://facebook.com/tenfanpage">
                    @error('facebook_url')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6 px-6 py-6 bg-slate-50/70 rounded-b-2xl">
            <div><h3 class="font-semibold text-slate-900">Nút nổi hiện có</h3><p class="text-sm text-slate-500 mt-1">Cập nhật ngay khi bạn nhập.</p></div>
            <div class="md:col-span-2 flex flex-wrap gap-3 text-sm">
                <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full border" :class="phones[0] && phones[0].number ? 'bg-green-50 border-green-200 text-green-800' : 'bg-white border-slate-200 text-slate-400'">
                    <span class="w-2.5 h-2.5 rounded-full" :class="phones[0] && phones[0].number ? 'bg-green-500' : 'bg-slate-300'"></span>Gọi điện <span class="text-xs" x-text="phones[0] && phones[0].number ? '✓' : '(chưa có số)'"></span>
                </span>
                <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full border" :class="zalo.trim() ? 'bg-blue-50 border-blue-200 text-blue-800' : 'bg-white border-slate-200 text-slate-400'">
                    <span class="w-2.5 h-2.5 rounded-full" :class="zalo.trim() ? 'bg-blue-500' : 'bg-slate-300'"></span>Zalo <span class="text-xs" x-text="zalo.trim() ? '✓' : '(chưa có)'"></span>
                </span>
                <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full border" :class="mess.trim() ? 'bg-violet-50 border-violet-200 text-violet-800' : 'bg-white border-slate-200 text-slate-400'">
                    <span class="w-2.5 h-2.5 rounded-full" :class="mess.trim() ? 'bg-violet-500' : 'bg-slate-300'"></span>Messenger <span class="text-xs" x-text="mess.trim() ? '✓' : '(chưa có)'"></span>
                </span>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- TAB 3: SEO                                                    --}}
    {{-- ============================================================ --}}
    <section x-show="tab === 'seo'" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm divide-y divide-slate-100">
        <div class="px-6 py-4 flex flex-wrap items-center gap-2 text-xs">
            <span class="font-semibold text-slate-500">📍 Hiển thị ở:</span>
            <span class="px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">Tiêu đề tab trình duyệt</span>
            <span class="px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">Kết quả tìm kiếm Google</span>
        </div>

        <div class="grid md:grid-cols-3 gap-6 px-6 py-6">
            <div><h3 class="font-semibold text-slate-900">Hiển thị trên Google</h3><p class="text-sm text-slate-500 mt-1">Tiêu đề và mô tả của trang chủ. Các trang khác tự thêm tên thương hiệu sau tiêu đề trang.</p></div>
            <div class="md:col-span-2 space-y-5">
                <div>
                    <div class="flex items-baseline justify-between">
                        <label class="{{ $lb }}" for="meta_title">Meta Title</label>
                        <span class="text-xs" :class="title.length > 60 ? 'text-rose-600 font-semibold' : 'text-slate-500'" x-text="title.length + '/60'"></span>
                    </div>
                    <input id="meta_title" name="meta_title" x-model="title" class="{{ $in }}" placeholder="Tiêu đề trang web hiển thị trên Google" maxlength="120">
                </div>
                <div>
                    <div class="flex items-baseline justify-between">
                        <label class="{{ $lb }}" for="meta_description">Meta Description</label>
                        <span class="text-xs" :class="desc.length > 160 ? 'text-rose-600 font-semibold' : 'text-slate-500'" x-text="desc.length + '/160'"></span>
                    </div>
                    <textarea id="meta_description" name="meta_description" x-model="desc" rows="3" class="{{ $in }}" placeholder="Mô tả ngắn về website" maxlength="320"></textarea>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-bold tracking-wide text-slate-400 mb-2">XEM TRƯỚC TRÊN GOOGLE</p>
                    <p class="text-sm text-emerald-800 truncate">{{ url('/') }}</p>
                    <p class="text-lg text-blue-700 leading-snug truncate" x-text="title || 'Tiêu đề trang web'"></p>
                    <p class="text-sm text-slate-600 line-clamp-2" x-text="desc || 'Mô tả ngắn về website sẽ hiển thị ở đây.'"></p>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6 px-6 py-6">
            <div><h3 class="font-semibold text-slate-900">Từ khoá &amp; theo dõi</h3><p class="text-sm text-slate-500 mt-1">Từ khoá gợi ý và mã theo dõi lượt truy cập.</p></div>
            <div class="md:col-span-2 space-y-5">
                <div>
                    <label class="{{ $lb }}" for="meta_keywords">Meta Keywords</label>
                    <input id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $s->get('meta_keywords')) }}" class="{{ $in }}" placeholder="cắm hoa, học cắm hoa, khoá học" maxlength="500">
                    <p class="{{ $hint }}">Ngăn cách bằng dấu phẩy.</p>
                </div>
                <div class="max-w-sm">
                    <label class="{{ $lb }}" for="google_analytics">Google Analytics ID</label>
                    <input id="google_analytics" name="google_analytics" value="{{ old('google_analytics', $s->get('google_analytics')) }}" class="{{ $in }}" placeholder="G-XXXXXXXXXX" maxlength="30">
                    @error('google_analytics')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- TAB 4: CHIA SẺ                                                --}}
    {{-- ============================================================ --}}
    <section x-show="tab === 'share'" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm divide-y divide-slate-100">
        <div class="px-6 py-4 flex flex-wrap items-center gap-2 text-xs">
            <span class="font-semibold text-slate-500">📍 Hiển thị ở:</span>
            <span class="px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">Biểu tượng tab trình duyệt</span>
            <span class="px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">Khi gửi link lên Facebook / Zalo</span>
        </div>

        <div class="grid md:grid-cols-3 gap-6 px-6 py-6">
            <div><h3 class="font-semibold text-slate-900">Favicon</h3><p class="text-sm text-slate-500 mt-1">Biểu tượng nhỏ trên tab trình duyệt. PNG hoặc ICO, 32×32px.</p></div>
            <div class="md:col-span-2 flex items-center gap-4">
                <div class="w-16 h-16 shrink-0 rounded-xl border border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden">
                    <img id="favicon_prev" src="{{ $s->faviconUrl() }}" alt="" class="max-w-full max-h-full {{ $s->faviconUrl() ? '' : 'hidden' }}">
                    <span id="favicon_prev_ph" class="text-[10px] text-slate-400 {{ $s->faviconUrl() ? 'hidden' : '' }}">Chưa có</span>
                </div>
                <div class="min-w-0 flex-1">
                    <input type="file" name="favicon" accept=".ico,image/png,image/jpeg,image/webp" @change="preview($event, 'favicon_prev')" class="{{ $file }}">
                    @if($s->faviconUrl())<label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-600 cursor-pointer"><input type="checkbox" name="remove_favicon" value="1" class="rounded border-slate-300 text-rose-600"> Xoá favicon hiện tại</label>@endif
                    @error('favicon')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6 px-6 py-6">
            <div><h3 class="font-semibold text-slate-900">Ảnh chia sẻ (OG Image)</h3><p class="text-sm text-slate-500 mt-1">Ảnh hiện khi gửi link lên Facebook, Zalo. Nên dùng 1200×630px.</p></div>
            <div class="md:col-span-2 space-y-4">
                <input type="file" name="og_image" accept="image/png,image/jpeg,image/webp" @change="preview($event, 'og_prev')" class="{{ $file }}">
                @if($s->ogImageUrl())<label class="inline-flex items-center gap-2 text-sm text-slate-600 cursor-pointer"><input type="checkbox" name="remove_og_image" value="1" class="rounded border-slate-300 text-rose-600"> Xoá ảnh hiện tại</label>@endif
                @error('og_image')<p class="text-rose-600 text-sm">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6 px-6 py-6">
            <div><h3 class="font-semibold text-slate-900">Tiêu đề &amp; mô tả khi chia sẻ</h3><p class="text-sm text-slate-500 mt-1">Để trống thì tự dùng Meta Title / Meta Description. Trang bài viết tự dùng tiêu đề và ảnh của bài đó.</p></div>
            <div class="md:col-span-2 space-y-5">
                <div>
                    <label class="{{ $lb }}" for="og_title">OG Title</label>
                    <input id="og_title" name="og_title" x-model="ogTitle" class="{{ $in }}" placeholder="Tiêu đề khi chia sẻ" maxlength="120">
                </div>
                <div>
                    <label class="{{ $lb }}" for="og_description">OG Description</label>
                    <textarea id="og_description" name="og_description" x-model="ogDesc" rows="2" class="{{ $in }}" placeholder="Mô tả khi chia sẻ lên Facebook / Zalo" maxlength="320"></textarea>
                </div>

                <div class="rounded-xl border border-slate-200 overflow-hidden bg-slate-50 max-w-md">
                    <p class="text-[11px] font-bold tracking-wide text-slate-400 px-4 pt-3">XEM TRƯỚC KHI CHIA SẺ</p>
                    <div class="mt-2 aspect-[1200/630] bg-slate-200 flex items-center justify-center overflow-hidden">
                        <img id="og_prev" src="{{ $s->ogImageUrl() }}" alt="" class="w-full h-full object-cover {{ $s->ogImageUrl() ? '' : 'hidden' }}">
                        <span id="og_prev_ph" class="text-sm text-slate-400 {{ $s->ogImageUrl() ? 'hidden' : '' }}">Chưa có ảnh chia sẻ</span>
                    </div>
                    <div class="px-4 py-3 bg-white border-t border-slate-200">
                        <p class="text-[11px] uppercase text-slate-400">{{ parse_url(url('/'), PHP_URL_HOST) }}</p>
                        <p class="font-semibold text-slate-900 truncate" x-text="ogTitle || title || 'Tiêu đề khi chia sẻ'"></p>
                        <p class="text-sm text-slate-500 line-clamp-2" x-text="ogDesc || desc || 'Mô tả khi chia sẻ'"></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- TAB 5: BẢN ĐỒ & FANPAGE                                       --}}
    {{-- ============================================================ --}}
    <section x-show="tab === 'embed'" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm divide-y divide-slate-100">
        <div class="grid md:grid-cols-3 gap-6 px-6 py-6">
            <div>
                <h3 class="font-semibold text-slate-900">Google Maps</h3>
                <p class="text-sm text-slate-500 mt-1">Google Maps → Chia sẻ → Nhúng bản đồ → Sao chép HTML.</p>
                <p class="mt-3 inline-flex items-center gap-1.5 text-xs text-sky-700 bg-sky-50 border border-sky-100 rounded-full px-3 py-1">📍 Trang Liên hệ</p>
            </div>
            <div class="md:col-span-2">
                <label class="{{ $lb }}" for="map_iframe">Mã nhúng bản đồ</label>
                <textarea id="map_iframe" name="map_iframe" rows="5" class="{{ $in }} font-mono text-xs" placeholder="&lt;iframe src=&quot;https://www.google.com/maps/embed?...&quot;&gt;&lt;/iframe&gt;">{{ old('map_iframe', $s->get('map_iframe')) }}</textarea>
                @error('map_iframe')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                <p class="{{ $hint }}">Chỉ chấp nhận iframe từ Google Maps.</p>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6 px-6 py-6">
            <div>
                <h3 class="font-semibold text-slate-900">Facebook Fanpage</h3>
                <p class="text-sm text-slate-500 mt-1">Facebook Page Plugin → chọn kích thước → Lấy mã → IFRAME.</p>
                <p class="mt-3 inline-flex items-center gap-1.5 text-xs text-sky-700 bg-sky-50 border border-sky-100 rounded-full px-3 py-1">📍 Footer mọi trang</p>
            </div>
            <div class="md:col-span-2">
                <label class="{{ $lb }}" for="fanpage_iframe">Mã nhúng Fanpage</label>
                <textarea id="fanpage_iframe" name="fanpage_iframe" rows="5" class="{{ $in }} font-mono text-xs" placeholder="&lt;iframe src=&quot;https://www.facebook.com/plugins/page.php?...&quot;&gt;&lt;/iframe&gt;">{{ old('fanpage_iframe', $s->get('fanpage_iframe')) }}</textarea>
                @error('fanpage_iframe')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                <p class="{{ $hint }}">Chỉ chấp nhận iframe từ facebook.com.</p>
            </div>
        </div>
    </section>

    {{-- ============ Thanh lưu cố định ============ --}}
    <div class="fixed bottom-0 left-0 right-0 lg:left-64 z-30 bg-white/90 backdrop-blur border-t border-slate-200 px-4 sm:px-6 py-3 flex items-center justify-between gap-4">
        <p class="hidden sm:block text-sm text-slate-500">Thay đổi ở mọi tab được lưu cùng lúc.</p>
        <button type="submit" class="ml-auto px-8 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow transition">Lưu tất cả cài đặt</button>
    </div>
</form>
@endsection
