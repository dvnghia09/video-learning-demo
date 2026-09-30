@extends('layouts.auth')
@section('title', 'Đăng nhập')
@section('robots', 'noindex,nofollow')

@section('content')
<h1 class="text-xl font-bold text-stone-900 mb-1">Chào mừng trở lại</h1>
<p class="text-sm text-stone-700 mb-6">Đăng nhập để tiếp tục học.</p>

<form action="{{ route('login') }}" method="POST" class="space-y-5">
    @csrf
    <div>
        <label for="phone" class="block text-sm font-semibold text-stone-700 mb-1.5">Số điện thoại</label>
        <input id="phone" name="phone" type="tel" inputmode="numeric" autocomplete="tel" required autofocus value="{{ old('phone') }}"
               placeholder="Nhập số điện thoại"
               class="w-full px-4 py-3 rounded-xl border {{ $errors->has('phone') ? 'border-rose-400' : 'border-stone-300' }} focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-base">
        @error('phone')<p class="text-rose-600 text-sm mt-1.5">{{ $message }}</p>@enderror
    </div>

    <div x-data="{ show: false }">
        <label for="password" class="block text-sm font-semibold text-stone-700 mb-1.5">Mật khẩu</label>
        <div class="relative">
            <input id="password" name="password" :type="show ? 'text' : 'password'" autocomplete="current-password" required placeholder="Nhập mật khẩu"
                   class="w-full pl-4 pr-20 py-3 rounded-xl border {{ $errors->has('password') ? 'border-rose-400' : 'border-stone-300' }} focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-base">
            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-amber-700 hover:text-amber-800" x-text="show ? 'Ẩn' : 'Hiện'"></button>
        </div>
        @error('password')<p class="text-rose-600 text-sm mt-1.5">{{ $message }}</p>@enderror
    </div>

    <div class="flex items-center justify-between text-sm">
        <label class="inline-flex items-center gap-2 text-stone-700 cursor-pointer">
            <input type="checkbox" name="remember" value="1" class="rounded border-stone-300 text-amber-700 focus:ring-amber-500"> Ghi nhớ đăng nhập
        </label>
        @php $help = $site->zaloUrl() ?? ($site->defaultPhone() ? \App\Support\SiteSettings::telHref($site->defaultPhone()) : route('contact.index')); @endphp
        <a href="{{ $help }}" data-help="password" class="font-semibold text-amber-800 hover:text-amber-900 underline underline-offset-2">Quên mật khẩu?</a>
    </div>

    <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-700 to-rose-600 text-white font-bold text-base shadow-lg shadow-amber-500/25 hover:shadow-xl hover:-translate-y-0.5 transition">
        Đăng nhập
    </button>
    <p class="text-center text-sm text-stone-600">Chưa có tài khoản? <a href="{{ route('register') }}" class="font-semibold text-amber-700 hover:text-amber-800">Đăng ký ngay</a></p>
</form>
@endsection
