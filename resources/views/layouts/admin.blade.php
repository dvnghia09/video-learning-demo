<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    @include('partials.favicon')
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Custom scrollbar for sidebar */
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #4B5563; border-radius: 4px; }
    </style>
    <style>[x-cloak]{display:none !important}</style>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('ui', {
                dialog: { open: false, type: 'info', title: '', message: '', confirmText: 'Đồng ý', cancelText: 'Huỷ', hideCancel: false, actions: null },
                _resolve: null,
                toasts: [],

                // Hiện popup, trả về Promise<boolean> (true = người dùng bấm nút chính)
                ask(opts = {}) {
                    this.dialog = Object.assign(
                        { open: true, type: 'info', title: '', message: '', confirmText: 'Đồng ý', cancelText: 'Huỷ', hideCancel: false },
                        opts, { open: true });
                    return new Promise(res => { this._resolve = res; });
                },
                // Popup thông báo (chỉ 1 nút)
                notify(opts = {}) {
                    return this.ask(Object.assign({ hideCancel: true, confirmText: 'Đã hiểu' }, opts));
                },
                close(result) {
                    this.dialog.open = false;
                    if (this._resolve) { this._resolve(result); this._resolve = null; }
                },
                toast(message, type = 'success') {
                    const id = Date.now() + Math.random();
                    this.toasts.push({ id, message, type });
                    setTimeout(() => this.dismiss(id), 4500);
                },
                dismiss(id) { this.toasts = this.toasts.filter(t => t.id !== id); },
            });
        });

        // Form có data-confirm sẽ hiện popup xác nhận trước khi gửi
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (!form.dataset || !form.dataset.confirm) return;
            e.preventDefault();
            Alpine.store('ui').ask({
                type: form.dataset.confirmType || 'danger',
                title: form.dataset.confirmTitle || 'Xác nhận xoá',
                message: form.dataset.confirm,
                confirmText: form.dataset.confirmOk || 'Xoá',
                cancelText: 'Huỷ',
            }).then(ok => { if (ok) form.submit(); });
        });
    </script>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden text-slate-800" x-data="{ mobileSidebarOpen: false, desktopSidebarCollapsed: false }">

    <!-- Mobile sidebar backdrop -->
    <div x-show="mobileSidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/80 backdrop-blur-sm lg:hidden" @click="mobileSidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside :class="[
            mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full',
            desktopSidebarCollapsed ? 'lg:w-20' : 'lg:w-64'
        ]" class="fixed z-50 inset-y-0 left-0 bg-slate-900 text-slate-300 flex-shrink-0 flex flex-col transition-all duration-300 lg:static lg:translate-x-0 shadow-2xl">
        
        <!-- Logo -->
        <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800/60 bg-slate-950/50">
            <div class="flex items-center space-x-3 overflow-hidden whitespace-nowrap">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shrink-0">F</div>
                <h1 x-show="!desktopSidebarCollapsed" x-transition.opacity.duration.300ms class="text-xl font-bold text-white tracking-wide">Floral<span class="text-amber-500">Admin</span></h1>
            </div>
            <button @click="mobileSidebarOpen = false" class="lg:hidden p-1 text-slate-400 hover:text-white rounded-md hover:bg-slate-800 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Nav Links -->
        <nav class="flex-1 px-3 py-6 space-y-1.5 overflow-y-auto sidebar-scroll">
            <a href="{{ route('admin.dashboard') }}" class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-amber-600 text-white shadow-md shadow-amber-900/20' : 'hover:bg-slate-800 hover:text-white' }}" :title="desktopSidebarCollapsed ? 'Dashboard' : ''">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span x-show="!desktopSidebarCollapsed" class="ml-3 font-medium">Dashboard</span>
            </a>
            
            <a href="{{ route('admin.users.index') }}" class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-amber-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}" :title="desktopSidebarCollapsed ? 'Quản lý Users' : ''">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span x-show="!desktopSidebarCollapsed" class="ml-3 font-medium">Quản lý Users</span>
            </a>

            <a href="{{ route('admin.banners.index') }}" class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.banners.*') ? 'bg-amber-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}" :title="desktopSidebarCollapsed ? 'Quản lý Banners' : ''">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span x-show="!desktopSidebarCollapsed" class="ml-3 font-medium">Quản lý Banners</span>
            </a>

            <div class="pt-4 pb-1">
                <p x-show="!desktopSidebarCollapsed" class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Học Tập</p>
                <div x-show="desktopSidebarCollapsed" class="px-3 border-t border-slate-700 my-2"></div>
            </div>

            <a href="{{ route('admin.lessons.index') }}" class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.lessons.*') ? 'bg-amber-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}" :title="desktopSidebarCollapsed ? 'Bài học (Lessons)' : ''">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <span x-show="!desktopSidebarCollapsed" class="ml-3 font-medium">Bài học (Lessons)</span>
            </a>

            <a href="{{ route('admin.videos.index') }}" class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.videos.*') ? 'bg-amber-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}" :title="desktopSidebarCollapsed ? 'Quản lý Videos' : ''">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                <span x-show="!desktopSidebarCollapsed" class="ml-3 font-medium">Quản lý Videos</span>
            </a>

            <div class="pt-4 pb-1">
                <p x-show="!desktopSidebarCollapsed" class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nội Dung</p>
                <div x-show="desktopSidebarCollapsed" class="px-3 border-t border-slate-700 my-2"></div>
            </div>

            <a href="{{ route('admin.artworks.index') }}" class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.artworks.*') ? 'bg-amber-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}" :title="desktopSidebarCollapsed ? 'Tác phẩm' : ''">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span x-show="!desktopSidebarCollapsed" class="ml-3 font-medium">Tác phẩm nổi bật</span>
            </a>

            <a href="{{ route('admin.blogs.index') }}" class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.blogs.*') ? 'bg-amber-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}" :title="desktopSidebarCollapsed ? 'Bài viết (Blogs)' : ''">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span x-show="!desktopSidebarCollapsed" class="ml-3 font-medium">Bài viết (Blogs)</span>
            </a>

            <a href="{{ route('admin.pages.index') }}" class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.pages.*') ? 'bg-amber-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}" :title="desktopSidebarCollapsed ? 'Trang tĩnh (Pages)' : ''">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span x-show="!desktopSidebarCollapsed" class="ml-3 font-medium">Trang tĩnh (Pages)</span>
            </a>

            <a href="{{ route('admin.contacts.index') }}" class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.contacts.*') ? 'bg-amber-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}" :title="desktopSidebarCollapsed ? 'Liên hệ' : ''">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <span x-show="!desktopSidebarCollapsed" class="ml-3 font-medium">Khách liên hệ</span>
                @php $__newContacts = \App\Models\Contact::where('status', 'new')->count(); @endphp
                @if($__newContacts > 0)<span x-show="!desktopSidebarCollapsed" class="ml-auto min-w-[1.5rem] px-1.5 py-0.5 rounded-full bg-rose-500 text-white text-xs font-bold text-center">{{ $__newContacts }}</span>@endif
            </a>
            
            <div class="pt-4 pb-1">
                <p x-show="!desktopSidebarCollapsed" class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Hệ thống</p>
                <div x-show="desktopSidebarCollapsed" class="px-3 border-t border-slate-700 my-2"></div>
            </div>

            <a href="{{ route('admin.settings.edit') }}" class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.settings.*') ? 'bg-amber-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}" :title="desktopSidebarCollapsed ? 'Cài đặt' : ''">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span x-show="!desktopSidebarCollapsed" class="ml-3 font-medium">Cài đặt website</span>
            </a>

        </nav>
        
        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-800/60 bg-slate-950/30">
            <a href="{{ route('home') }}" class="group flex items-center justify-center px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors" :title="desktopSidebarCollapsed ? 'Về Web' : ''">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span x-show="!desktopSidebarCollapsed" class="ml-2 text-sm font-medium">Về Website</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden w-full relative bg-slate-50/50">
        
        <!-- Header -->
        <header class="h-16 bg-white shadow-sm border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-10 shrink-0">
            <div class="flex items-center">
                <!-- Mobile toggle -->
                <button @click="mobileSidebarOpen = true" class="text-slate-500 hover:text-slate-700 focus:outline-none lg:hidden mr-4 p-2 rounded-lg hover:bg-slate-100 transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <!-- Desktop toggle -->
                <button @click="desktopSidebarCollapsed = !desktopSidebarCollapsed" class="hidden lg:flex text-slate-500 hover:text-slate-700 focus:outline-none mr-4 p-2 rounded-lg hover:bg-slate-100 transition">
                    <svg x-show="!desktopSidebarCollapsed" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                    <svg x-show="desktopSidebarCollapsed" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                
                <h2 class="text-lg sm:text-xl font-bold text-slate-800 truncate tracking-tight">@yield('header')</h2>
            </div>
            
            <div class="flex items-center space-x-3 sm:space-x-5">
                <div class="flex items-center gap-3 pl-4 border-l border-slate-200">
                    <div class="hidden sm:flex flex-col items-end">
                        <span class="text-sm font-semibold text-slate-700">{{ auth()->user()->name ?? 'Admin' }}</span>
                        <span class="text-xs text-slate-500">Quản trị viên</span>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold shadow-inner">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="ml-2">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Đăng xuất">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- Page Content -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 w-full">
            <div class="max-w-7xl mx-auto">
                @if(session('success'))
                    <script>document.addEventListener('alpine:initialized', () => Alpine.store('ui').toast(@js(session('success')), 'success'));</script>
                @endif
                @if(session('error'))
                    <script>document.addEventListener('alpine:initialized', () => Alpine.store('ui').toast(@js(session('error')), 'error'));</script>
                @endif
                @if($errors->any())
                    <div class="mb-6 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-lg shadow-sm flex items-start">
                        <svg class="w-5 h-5 text-rose-500 mt-0.5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div class="text-rose-700 text-sm font-medium">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </main>


    <!-- Popup xác nhận / thông báo -->
    <div x-data x-cloak x-show="$store.ui.dialog.open" x-transition.opacity
         @keydown.escape.window="$store.ui.dialog.open && $store.ui.close(false)"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         @click.self="$store.ui.close(false)">
        <div x-show="$store.ui.dialog.open" x-transition.scale.origin.center class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 text-center">
            <div class="mx-auto mb-4 w-14 h-14 rounded-full flex items-center justify-center"
                 :class="{'bg-rose-100 text-rose-600': $store.ui.dialog.type==='danger' || $store.ui.dialog.type==='error',
                          'bg-emerald-100 text-emerald-600': $store.ui.dialog.type==='success',
                          'bg-amber-100 text-amber-600': $store.ui.dialog.type==='info' || $store.ui.dialog.type==='warning'}">
                <template x-if="$store.ui.dialog.type==='success'">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="$store.ui.dialog.type==='danger'">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </template>
                <template x-if="$store.ui.dialog.type!=='success' && $store.ui.dialog.type!=='danger'">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                </template>
            </div>
            <h3 class="text-lg font-bold text-slate-900" x-text="$store.ui.dialog.title"></h3>
            <p class="mt-2 text-sm text-slate-600 whitespace-pre-line" x-text="$store.ui.dialog.message"></p>
            <div class="mt-6 flex gap-3 justify-center">
                <button type="button" x-show="!$store.ui.dialog.hideCancel" @click="$store.ui.close(false)"
                        class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition" x-text="$store.ui.dialog.cancelText"></button>
                <button type="button" @click="$store.ui.close(true)"
                        class="px-5 py-2.5 rounded-xl text-white font-semibold shadow transition"
                        :class="($store.ui.dialog.type==='danger' || $store.ui.dialog.type==='error') ? 'bg-rose-600 hover:bg-rose-700' : 'bg-amber-600 hover:bg-amber-700'"
                        x-text="$store.ui.dialog.confirmText"></button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div x-data x-cloak class="fixed top-4 right-4 z-[110] space-y-3 w-80 max-w-[calc(100vw-2rem)]">
        <template x-for="t in $store.ui.toasts" :key="t.id">
            <div x-transition class="flex items-start gap-3 rounded-xl shadow-lg border p-4 bg-white"
                 :class="t.type==='error' ? 'border-rose-200' : 'border-emerald-200'">
                <span class="mt-0.5 shrink-0" :class="t.type==='error' ? 'text-rose-500' : 'text-emerald-500'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" :d="t.type==='error' ? 'M6 18L18 6M6 6l12 12' : 'M5 13l4 4L19 7'"/></svg>
                </span>
                <p class="text-sm font-medium text-slate-700 flex-1" x-text="t.message"></p>
                <button @click="$store.ui.dismiss(t.id)" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
        </template>
    </div>

    @include('partials.form-validate')
</body>
</html>
