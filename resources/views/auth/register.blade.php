@extends('layouts.auth')
@section('title', 'Đăng ký tài khoản')
@section('robots', 'noindex,nofollow')

@section('content')
<h1 class="text-xl font-bold text-stone-900 mb-1">Tạo tài khoản</h1>
<p class="text-sm text-stone-700 mb-6">Chỉ mất 30 giây để bắt đầu học.</p>

<form action="{{ route('register') }}" method="POST" class="space-y-4" x-data="{ show: false }">
    @csrf
    @php $field = 'w-full px-4 py-3 rounded-xl border focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-base'; @endphp

    <div>
        <label for="name" class="block text-sm font-semibold text-stone-700 mb-1.5">Họ và tên</label>
        <input id="name" name="name" type="text" required minlength="2" maxlength="100" autofocus autocomplete="name" value="{{ old('name') }}" placeholder="Nhập họ và tên"
               class="{{ $field }} {{ $errors->has('name') ? 'border-rose-400' : 'border-stone-300' }}">
        @error('name')<p class="text-rose-600 text-sm mt-1.5">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="phone" class="block text-sm font-semibold text-stone-700 mb-1.5">Số điện thoại</label>
        <input id="phone" name="phone" type="tel" inputmode="numeric" required maxlength="16" data-vn-phone autocomplete="tel" value="{{ old('phone') }}" placeholder="Nhập số điện thoại"
               class="{{ $field }} {{ $errors->has('phone') ? 'border-rose-400' : 'border-stone-300' }}">
        @error('phone')<p class="text-rose-600 text-sm mt-1.5">{{ $message }}</p>@else<p class="text-stone-500 text-sm mt-1.5">Số di động 10 chữ số, ví dụ 0912345678.</p>@enderror
    </div>
    <div>
        <label for="password" class="block text-sm font-semibold text-stone-700 mb-1.5">Mật khẩu</label>
        <div class="relative">
            <input id="password" name="password" :type="show ? 'text' : 'password'" required minlength="8" maxlength="100" autocomplete="new-password" placeholder="Tạo mật khẩu (ít nhất 8 ký tự)"
                   class="{{ $field }} pr-20 {{ $errors->has('password') ? 'border-rose-400' : 'border-stone-300' }}">
            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-amber-700 hover:text-amber-800" x-text="show ? 'Ẩn' : 'Hiện'"></button>
        </div>
        @error('password')<p class="text-rose-600 text-sm mt-1.5">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password_confirmation" class="block text-sm font-semibold text-stone-700 mb-1.5">Nhập lại mật khẩu</label>
        <input id="password_confirmation" name="password_confirmation" data-match="#password" :type="show ? 'text' : 'password'" required minlength="8" maxlength="100" autocomplete="new-password" placeholder="Nhập lại mật khẩu"
               class="{{ $field }} border-stone-300">
    </div>

    <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-700 to-rose-600 text-white font-bold text-base shadow-lg shadow-amber-500/25 hover:shadow-xl hover:-translate-y-0.5 transition">
        Đăng ký tài khoản
    </button>
    <p class="text-center text-sm text-stone-600">Đã có tài khoản? <a href="{{ route('login') }}" class="font-semibold text-amber-700 hover:text-amber-800">Đăng nhập</a></p>
</form>
@endsection
