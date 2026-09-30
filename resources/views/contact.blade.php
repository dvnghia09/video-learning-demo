@extends('layouts.public')
@section('title', 'Liên hệ')
@section('meta_description', 'Liên hệ với chúng tôi để được tư vấn bài học cắm hoa.')

@section('content')
@include('partials.page-header', ['title' => 'Liên hệ', 'subtitle' => 'Để lại lời nhắn, chúng tôi sẽ gọi lại cho bạn sớm nhất.'])

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    @if(session('success'))
        <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-5">
        {{-- Thông tin liên hệ --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-stone-300 shadow-md p-6 sm:p-8 space-y-5 h-fit">
            <h2 class="text-xl font-bold text-stone-900">{{ $site->get('company_name', $site->brand()) }}</h2>

            @if($site->get('address'))
                <div class="flex gap-3"><span class="w-10 h-10 shrink-0 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">📍</span>
                    <div><p class="text-sm font-semibold text-stone-700 uppercase">Địa chỉ</p><p class="text-stone-800">{{ $site->get('address') }}</p></div></div>
            @endif
            @foreach($site->phones() as $ph)
                <div class="flex gap-3"><span class="w-10 h-10 shrink-0 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">📞</span>
                    <div><p class="text-sm font-semibold text-stone-700 uppercase">{{ $ph['label'] ?: 'Hotline' }}</p>
                        <a href="{{ \App\Support\SiteSettings::telHref($ph['number']) }}" class="text-stone-900 font-bold hover:text-amber-700">{{ $ph['number'] }}</a></div></div>
            @endforeach
            @if($site->get('email'))
                <div class="flex gap-3"><span class="w-10 h-10 shrink-0 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center">✉️</span>
                    <div class="min-w-0"><p class="text-sm font-semibold text-stone-700 uppercase">Email</p>
                        <a href="mailto:{{ $site->get('email') }}" class="text-stone-900 font-semibold hover:text-amber-700 break-all">{{ $site->get('email') }}</a></div></div>
            @endif
            @if($site->zaloUrl() || $site->messengerUrl() || $site->get('facebook_url'))
                <div class="flex flex-wrap gap-2 pt-1">
                    @if($site->zaloUrl())<a href="{{ $site->zaloUrl() }}" target="_blank" rel="noopener" class="px-4 py-2 rounded-full bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition">Chat Zalo</a>@endif
                    @if($site->messengerUrl())<a href="{{ $site->messengerUrl() }}" target="_blank" rel="noopener" class="px-4 py-2 rounded-full bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700 transition">Messenger</a>@endif
                    @if($site->get('facebook_url'))<a href="{{ $site->get('facebook_url') }}" target="_blank" rel="noopener" class="px-4 py-2 rounded-full bg-stone-800 text-white text-sm font-semibold hover:bg-stone-900 transition">Facebook</a>@endif
                </div>
            @endif
            @unless($site->get('address') || $site->phones() || $site->get('email'))
                <p class="text-stone-700 text-sm">Thông tin liên hệ đang được cập nhật.</p>
            @endunless
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('contact.store') }}" class="lg:col-span-3 bg-white rounded-2xl border border-stone-300 shadow-md p-6 sm:p-8 space-y-5">
            @csrf
            <h2 class="text-xl font-bold text-stone-900">Gửi lời nhắn</h2>
            @php $f = 'w-full px-4 py-3 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500'; @endphp
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-semibold text-stone-700 mb-1.5">Họ và tên <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required minlength="2" maxlength="100" class="{{ $f }}">
                    @error('name')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="block text-sm font-semibold text-stone-700 mb-1.5">Số điện thoại</label>
                    <input id="phone" name="phone" type="tel" inputmode="numeric" value="{{ old('phone') }}" maxlength="16" data-vn-phone placeholder="VD: 0912345678" class="{{ $f }}">
                    @error('phone')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="message" class="block text-sm font-semibold text-stone-700 mb-1.5">Nội dung <span class="text-rose-500">*</span></label>
                <textarea id="message" name="message" rows="5" required minlength="5" maxlength="5000" class="{{ $f }}">{{ old('message') }}</textarea>
                @error('message')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-amber-700 to-rose-600 text-white font-bold shadow-lg shadow-amber-500/25 hover:shadow-xl hover:-translate-y-0.5 transition">Gửi liên hệ</button>
        </form>
    </div>

    {{-- Bản đồ Google Maps (cài đặt trong Admin → Cài đặt website → Nhúng) --}}
    @if($site->get('map_iframe'))
        <div class="mt-6 rounded-2xl overflow-hidden border border-stone-300 shadow-md bg-white [&_iframe]:w-full [&_iframe]:block">{!! $site->get('map_iframe') !!}</div>
    @endif
</div>
@endsection
