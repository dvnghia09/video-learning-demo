<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.head-meta')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
    <script src="{{ asset('vendor/alpine/alpine.min.js') }}" defer></script>
    <style>body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }</style>
</head>
<body class="min-h-screen bg-paper text-stone-800 flex flex-col">
    <main class="flex-grow flex items-center justify-center p-4 py-10">
        <div class="w-full max-w-md">
            <a href="{{ route('home') }}" class="flex flex-col items-center mb-6" aria-label="{{ $site->brand() }}">
                @if($site->logoUrl())
                    <img src="{{ $site->logoUrl() }}" alt="{{ $site->brand() }}" class="max-h-14 w-auto object-contain">
                @else
                    <span class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-rose-500 text-white flex items-center justify-center text-2xl shadow-lg shadow-amber-500/30">🌸</span>
                    <span class="mt-3 text-2xl font-extrabold text-stone-900 tracking-tight">{{ $site->brand() }}</span>
                @endif
            </a>

            <div class="bg-white rounded-3xl shadow-xl shadow-stone-900/5 border border-stone-300 p-6 sm:p-8">
                {{-- Chuyển đổi Đăng nhập / Đăng ký --}}
                <div class="grid grid-cols-2 bg-stone-100 rounded-full p-1 mb-6 text-center text-sm font-semibold">
                    <a href="{{ route('login') }}" class="py-2.5 rounded-full transition {{ request()->routeIs('login') ? 'bg-white text-amber-700 shadow' : 'text-stone-700 hover:text-stone-800' }}">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="py-2.5 rounded-full transition {{ request()->routeIs('register') ? 'bg-white text-amber-700 shadow' : 'text-stone-700 hover:text-stone-800' }}">Đăng ký</a>
                </div>
                @yield('content')
            </div>

            <p class="text-center mt-6 text-sm text-stone-600"><a href="{{ route('home') }}" class="hover:text-amber-700 transition">&larr; Về trang chủ</a></p>
        </div>
    </main>
    @include('partials.help-modal')
    @include('partials.floating-contact')
    @include('partials.form-validate')
</body>
</html>
