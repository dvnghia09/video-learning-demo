<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.head-meta')
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] }, colors: { paper: '#f1ebe1', cream: '#f8f4ec' } } } }</script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap&subset=vietnamese" rel="stylesheet">
    <style>
        html { font-size: 17px; scroll-behavior: smooth; }
        @media (min-width: 1024px) { html { font-size: 17.5px; } }
        a:focus-visible, button:focus-visible, input:focus-visible, textarea:focus-visible, summary:focus-visible { outline: 3px solid #1d4ed8; outline-offset: 2px; }
        body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-paper text-stone-800 flex flex-col min-h-screen font-sans antialiased" x-data="{ mobileMenuOpen: false }">
    
    <!-- Header -->
    <nav class="bg-white border-b border-stone-300 shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 relative">
                
                <!-- Left: Logo -->
                <div class="flex items-center z-10">
                    <a href="{{ route('home') }}" class="flex items-center text-2xl font-extrabold text-amber-700 tracking-tight" aria-label="{{ $site->brand() }}">
                        @if($site->logoUrl())
                            <img src="{{ $site->logoUrl() }}" alt="{{ $site->brand() }}" class="max-h-[50px] w-auto object-contain">
                        @else
                            {{ $site->brand() }}
                        @endif
                    </a>
                </div>

                <!-- Center: Desktop Menu (Absolutely Centered) -->
                <div class="hidden lg:flex flex-1 items-center justify-center px-4 min-w-0">
                    <div class="flex space-x-6 xl:space-x-8">
                        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-amber-700 font-bold border-b-2 border-amber-600' : 'text-gray-600 hover:text-amber-700 font-medium border-b-2 border-transparent hover:border-amber-300' }} py-5 transition">Trang chủ</a>
                        <a href="{{ route('courses.index') }}" class="{{ request()->routeIs('courses.*') ? 'text-amber-700 font-bold border-b-2 border-amber-600' : 'text-gray-600 hover:text-amber-700 font-medium border-b-2 border-transparent hover:border-amber-300' }} py-5 transition">Bài học</a>
                        <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'text-amber-700 font-bold border-b-2 border-amber-600' : 'text-gray-600 hover:text-amber-700 font-medium border-b-2 border-transparent hover:border-amber-300' }} py-5 transition">Giới thiệu</a>
                        <a href="{{ route('blogs.index') }}" class="{{ request()->routeIs('blogs.*') ? 'text-amber-700 font-bold border-b-2 border-amber-600' : 'text-gray-600 hover:text-amber-700 font-medium border-b-2 border-transparent hover:border-amber-300' }} py-5 transition">Bài viết</a>
                        <a href="{{ route('contact.index') }}" class="{{ request()->routeIs('contact.*') ? 'text-amber-700 font-bold border-b-2 border-amber-600' : 'text-gray-600 hover:text-amber-700 font-medium border-b-2 border-transparent hover:border-amber-300' }} py-5 transition">Liên hệ</a>
                    </div>
                </div>

                <!-- Right: Auth & CTA -->
                <div class="flex items-center space-x-2 sm:space-x-4 z-10">
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-block text-gray-600 hover:text-amber-700 px-2 py-2 font-medium transition text-sm sm:text-base">Quản trị</a>
                        @endif
                        <div class="relative hidden sm:block" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                            <button type="button" @click="open = !open" class="flex items-center gap-2 pl-1 pr-3 py-1 rounded-full hover:bg-amber-50 border border-transparent hover:border-amber-200 transition" aria-haspopup="true" :aria-expanded="open">
                                <span class="w-9 h-9 rounded-full overflow-hidden bg-gradient-to-br from-amber-600 to-rose-600 text-white font-bold flex items-center justify-center shrink-0">
                                    @if(auth()->user()->avatarUrl())<img src="{{ auth()->user()->avatarUrl() }}" alt="" class="w-full h-full object-cover">@else{{ auth()->user()->initial() }}@endif
                                </span>
                                <span class="text-stone-900 font-bold max-w-[9rem] truncate">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 text-stone-600 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-cloak x-transition.origin.top.right class="absolute right-0 mt-2 w-64 bg-white border-2 border-stone-200 rounded-2xl shadow-xl py-2 z-50">
                                <div class="px-4 py-2 border-b border-stone-200 mb-1">
                                    <p class="font-bold text-stone-900 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-sm text-stone-600">{{ auth()->user()->phone }}{{ auth()->user()->is_vip ? ' · ⭐ VIP' : '' }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-stone-800 font-semibold hover:bg-amber-50">👤 Tài khoản của tôi</a>
                                <a href="{{ route('profile.edit') }}#hoc-tap" class="block px-4 py-2.5 text-stone-800 font-semibold hover:bg-amber-50">📚 Bài đang học</a>
                                <a href="{{ route('profile.edit') }}#mat-khau" class="block px-4 py-2.5 text-stone-800 font-semibold hover:bg-amber-50">🔒 Đổi mật khẩu</a>
                                <form method="POST" action="{{ route('logout') }}" class="border-t border-stone-200 mt-1 pt-1">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2.5 text-rose-800 font-semibold hover:bg-rose-50">↩ Đăng xuất</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-block text-gray-600 hover:text-amber-700 px-2 py-2 rounded-md font-medium transition text-sm sm:text-base whitespace-nowrap">Đăng nhập</a>
                    @endauth

                    @if($site->defaultPhone())
                        <a href="{{ \App\Support\SiteSettings::telHref($site->defaultPhone()) }}" class="hidden xl:inline-flex items-center gap-1.5 text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 px-3 py-2 rounded-full font-bold text-sm whitespace-nowrap transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1C10.61 21 3 13.39 3 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/></svg>
                            Gọi ngay
                        </a>
                    @endif

                    <a href="{{ route('courses.index') }}" class="hidden sm:inline-block bg-gradient-to-r from-amber-700 to-rose-600 text-white px-4 py-2 sm:px-5 sm:py-2.5 rounded-full font-bold text-sm sm:text-base hover:shadow-lg hover:scale-105 transition transform duration-200 whitespace-nowrap">Học ngay</a>

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 rounded-md text-gray-600 hover:text-amber-700 hover:bg-amber-50 focus:outline-none focus:bg-amber-50 focus:text-amber-700 transition">
                        <svg x-show="!mobileMenuOpen" class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <svg x-show="mobileMenuOpen" class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-collapse class="lg:hidden bg-white border-t border-gray-100" style="display: none;">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 shadow-inner">
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('home') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:text-amber-700 hover:bg-amber-50' }}">Trang chủ</a>
                <a href="{{ route('courses.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('courses.*') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:text-amber-700 hover:bg-amber-50' }}">Bài học</a>
                <a href="{{ route('about') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('about') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:text-amber-700 hover:bg-amber-50' }}">Giới thiệu</a>
                <a href="{{ route('blogs.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('blogs.*') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:text-amber-700 hover:bg-amber-50' }}">Bài viết</a>
                <a href="{{ route('contact.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('contact.*') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:text-amber-700 hover:bg-amber-50' }}">Liên hệ</a>
            </div>
            
            <div class="pt-4 pb-4 border-t border-gray-200">
                @auth
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-5 mb-3">
                        <span class="w-12 h-12 rounded-full overflow-hidden bg-gradient-to-br from-amber-600 to-rose-600 text-white text-lg font-bold flex items-center justify-center shrink-0">
                            @if(auth()->user()->avatarUrl())<img src="{{ auth()->user()->avatarUrl() }}" alt="" class="w-full h-full object-cover">@else{{ auth()->user()->initial() }}@endif
                        </span>
                        <span class="min-w-0">
                            <span class="block text-base font-bold text-stone-900 truncate">{{ auth()->user()->name }}</span>
                            <span class="block text-sm font-medium text-stone-600">{{ auth()->user()->phone }} · Tài khoản của tôi &rsaquo;</span>
                        </span>
                    </a>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="block px-5 py-2 text-base font-medium text-gray-700 hover:text-amber-700 hover:bg-amber-50">Quản trị Admin</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-5 py-2 text-base font-medium text-gray-700 hover:text-amber-700 hover:bg-amber-50">Đăng xuất</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-5 py-2 text-base font-medium text-gray-700 hover:text-amber-700 hover:bg-amber-50">Đăng nhập</a>
                    <a href="{{ route('courses.index') }}" class="block px-5 py-2 mt-1 text-base font-medium text-amber-700 hover:bg-amber-50">Bắt đầu học ngay</a>
                @endauth
            </div>
        </div>
    </nav>
    
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-stone-900 text-stone-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <h3 class="text-2xl font-bold text-white mb-4">{{ $site->brand() }}</h3>
                    @if($site->get('company_name'))<p class="text-base text-gray-300 font-semibold mb-2">{{ $site->get('company_name') }}</p>@endif
                    @if($site->get('address'))<p class="text-base text-stone-500 max-w-sm mb-2">{{ $site->get('address') }}</p>@endif
                    @if($site->get('email'))<p class="text-base text-stone-500 mb-4"><a href="mailto:{{ $site->get('email') }}" class="hover:text-amber-500 transition">{{ $site->get('email') }}</a></p>@endif
                    <p class="text-base text-stone-500 max-w-sm">Chia sẻ đam mê và lan tỏa nghệ thuật cắm hoa đến mọi gia đình Việt Nam.</p>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-6 text-lg">Liên kết</h4>
                    <ul class="text-base space-y-3">
                        <li><a href="{{ route('home') }}" class="hover:text-amber-500 transition">Trang chủ</a></li>
                        <li><a href="{{ route('courses.index') }}" class="hover:text-amber-500 transition">Bài học</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-amber-500 transition">Về chúng tôi</a></li>
                        <li><a href="{{ route('blogs.index') }}" class="hover:text-amber-500 transition">Bài viết (Blog)</a></li>
                        <li><a href="{{ route('contact.index') }}" class="hover:text-amber-500 transition">Liên hệ</a></li>
                        @foreach(\App\Models\Page::footerLinks() as $fp)
                            <li><a href="{{ route('pages.show', $fp['slug']) }}" class="hover:text-amber-500 transition">{{ $fp['title'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-6 text-lg">Liên hệ</h4>
                    <ul class="text-base space-y-3">
                        @foreach($site->phones() as $ph)
                            <li>{{ $ph['label'] ?: 'Hotline' }}: <a href="{{ \App\Support\SiteSettings::telHref($ph['number']) }}" class="text-amber-500 font-bold hover:text-amber-400 transition">{{ $ph['number'] }}</a></li>
                        @endforeach
                        @if($site->zaloUrl())<li>Zalo: <a href="{{ $site->zaloUrl() }}" target="_blank" rel="noopener" class="text-amber-500 font-bold hover:text-amber-400 transition">Nhắn tin Zalo</a></li>@endif
                        @if($site->get('facebook_url'))<li>Facebook: <a href="{{ $site->get('facebook_url') }}" target="_blank" rel="noopener" class="text-amber-500 font-bold hover:text-amber-400 transition">Fanpage</a></li>@endif
                        @if($site->messengerUrl())<li>Messenger: <a href="{{ $site->messengerUrl() }}" target="_blank" rel="noopener" class="text-amber-500 font-bold hover:text-amber-400 transition">Nhắn tin</a></li>@endif
                    </ul>
                    @if($site->get('fanpage_iframe'))
                        <div class="mt-6 overflow-hidden rounded-lg">{!! $site->get('fanpage_iframe') !!}</div>
                    @endif
                </div>
            </div>
            <div class="border-t border-stone-800 mt-10 pt-8 text-sm text-center text-stone-600">
                &copy; {{ date('Y') }} {{ $site->get('company_name', $site->brand()) }}. Tất cả bản quyền thuộc về nhà sáng lập.
            </div>
        </div>
    </footer>

    @include('partials.help-modal')
    @include('partials.floating-contact')
    @include('partials.form-validate')
</body>
</html>
