with open('resources/views/layouts/public.blade.php', 'r') as f:
    content = f.read()

# Current Structure:
# <div class="flex justify-between h-16">
#     <!-- Logo & Desktop Menu -->
#     <div class="flex items-center">
#         <a href="..." >Logo</a>
#         <div class="hidden lg:flex ... ml-10">...links...</div>
#     </div>
#     <!-- Right Side -->
#     <div class="flex items-center ...">...</div>
# </div>

# Let's use regex to replace it
import re

# We will just rewrite the whole `<nav>` section accurately
nav_start = content.find('<nav class="bg-white shadow-md border-b border-gray-100 sticky top-0 z-50">')
nav_end = content.find('</nav>') + 6

new_nav = """<nav class="bg-white shadow-md border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 relative">
                
                <!-- Left: Logo -->
                <div class="flex items-center shrink-0 w-1/4">
                    <a href="{{ route('home') }}" class="text-2xl font-extrabold text-amber-600 tracking-tight">Floral<span class="text-gray-800">Art</span></a>
                </div>

                <!-- Center: Desktop Menu -->
                <div class="hidden lg:flex flex-1 justify-center space-x-6 xl:space-x-8">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-amber-600 font-bold border-b-2 border-amber-600' : 'text-gray-600 hover:text-amber-600 font-medium border-b-2 border-transparent hover:border-amber-300' }} py-5 transition">Trang chủ</a>
                    <a href="{{ route('courses.index') }}" class="{{ request()->routeIs('courses.*') ? 'text-amber-600 font-bold border-b-2 border-amber-600' : 'text-gray-600 hover:text-amber-600 font-medium border-b-2 border-transparent hover:border-amber-300' }} py-5 transition">Khóa học</a>
                    <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'text-amber-600 font-bold border-b-2 border-amber-600' : 'text-gray-600 hover:text-amber-600 font-medium border-b-2 border-transparent hover:border-amber-300' }} py-5 transition">Giới thiệu</a>
                    <a href="{{ route('blogs.index') }}" class="{{ request()->routeIs('blogs.*') ? 'text-amber-600 font-bold border-b-2 border-amber-600' : 'text-gray-600 hover:text-amber-600 font-medium border-b-2 border-transparent hover:border-amber-300' }} py-5 transition">Bài viết</a>
                    <a href="{{ route('contact.index') }}" class="{{ request()->routeIs('contact.*') ? 'text-amber-600 font-bold border-b-2 border-amber-600' : 'text-gray-600 hover:text-amber-600 font-medium border-b-2 border-transparent hover:border-amber-300' }} py-5 transition">Liên hệ</a>
                </div>

                <!-- Right: Auth & CTA -->
                <div class="flex items-center justify-end w-1/4 space-x-2 sm:space-x-4 shrink-0">
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-block text-gray-600 hover:text-amber-600 px-2 py-2 font-medium transition text-sm sm:text-base">Quản trị</a>
                        @endif
                        <div class="relative group hidden sm:block">
                            <span class="text-gray-800 font-bold px-2 py-2 cursor-pointer">{{ auth()->user()->name }}</span>
                            <div class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg py-1 hidden group-hover:block">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-amber-600">Đăng xuất</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-block text-gray-600 hover:text-amber-600 px-2 py-2 rounded-md font-medium transition text-sm sm:text-base whitespace-nowrap">Đăng nhập</a>
                    @endauth

                    <a href="{{ route('courses.index') }}" class="hidden sm:inline-block bg-gradient-to-r from-amber-600 to-rose-500 text-white px-4 py-2 sm:px-5 sm:py-2.5 rounded-full font-bold text-sm sm:text-base hover:shadow-lg hover:scale-105 transition transform duration-200 whitespace-nowrap">Học ngay</a>

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 rounded-md text-gray-600 hover:text-amber-600 hover:bg-amber-50 focus:outline-none focus:bg-amber-50 focus:text-amber-600 transition ml-auto">
                        <svg x-show="!mobileMenuOpen" class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <svg x-show="mobileMenuOpen" class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-collapse class="lg:hidden bg-white border-t border-gray-100" style="display: none;">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 shadow-inner">
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('home') ? 'bg-amber-50 text-amber-600' : 'text-gray-700 hover:text-amber-600 hover:bg-amber-50' }}">Trang chủ</a>
                <a href="{{ route('courses.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('courses.*') ? 'bg-amber-50 text-amber-600' : 'text-gray-700 hover:text-amber-600 hover:bg-amber-50' }}">Khóa học</a>
                <a href="{{ route('about') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('about') ? 'bg-amber-50 text-amber-600' : 'text-gray-700 hover:text-amber-600 hover:bg-amber-50' }}">Giới thiệu</a>
                <a href="{{ route('blogs.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('blogs.*') ? 'bg-amber-50 text-amber-600' : 'text-gray-700 hover:text-amber-600 hover:bg-amber-50' }}">Bài viết</a>
                <a href="{{ route('contact.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('contact.*') ? 'bg-amber-50 text-amber-600' : 'text-gray-700 hover:text-amber-600 hover:bg-amber-50' }}">Liên hệ</a>
            </div>
            
            <div class="pt-4 pb-4 border-t border-gray-200">
                @auth
                    <div class="px-5 mb-3">
                        <div class="text-base font-medium text-gray-800">{{ auth()->user()->name }}</div>
                        <div class="text-sm font-medium text-gray-500">{{ auth()->user()->phone }}</div>
                    </div>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="block px-5 py-2 text-base font-medium text-gray-700 hover:text-amber-600 hover:bg-amber-50">Quản trị Admin</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-5 py-2 text-base font-medium text-gray-700 hover:text-amber-600 hover:bg-amber-50">Đăng xuất</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-5 py-2 text-base font-medium text-gray-700 hover:text-amber-600 hover:bg-amber-50">Đăng nhập</a>
                    <a href="{{ route('courses.index') }}" class="block px-5 py-2 mt-1 text-base font-medium text-amber-600 hover:bg-amber-50">Bắt đầu học ngay</a>
                @endauth
            </div>
        </div>
    </nav>"""

content = content[:nav_start] + new_nav + content[nav_end:]

with open('resources/views/layouts/public.blade.php', 'w') as f:
    f.write(content)

