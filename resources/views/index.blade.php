@extends('layouts.public')

@section('content')
<style>
    /* ============ Hiệu ứng (nhẹ, chỉ dùng transform/opacity) ============ */
    .hero-slide { opacity: 0; transition: opacity 1.1s ease; }
    .hero-slide.is-active { opacity: 1; z-index: 1; }
    .hero-slide img { transform: scale(1); }
    .hero-slide.is-active img { animation: kenburns 9s ease-out forwards; }
    @keyframes kenburns { from { transform: scale(1); } to { transform: scale(1.09); } }

    .rise { opacity: 0; transform: translateY(26px); animation: rise .8s cubic-bezier(.2,.7,.2,1) forwards; animation-delay: var(--d, 0ms); }
    @keyframes rise { to { opacity: 1; transform: none; } }

    .grad-text { background: linear-gradient(90deg, #fbbf24, #fb7185, #fbbf24); background-size: 200% auto; -webkit-background-clip: text; background-clip: text; color: transparent; animation: shine 4s linear infinite; }
    @keyframes shine { to { background-position: 200% center; } }

    .btn-shine { position: relative; overflow: hidden; }
    .btn-shine::after { content: ''; position: absolute; top: 0; left: -80%; width: 50%; height: 100%; background: linear-gradient(120deg, transparent, rgba(255,255,255,.55), transparent); transform: skewX(-20deg); animation: sweep 3.2s ease-in-out infinite; }
    @keyframes sweep { 0%, 55% { left: -80%; } 100% { left: 140%; } }
    .btn-pulse { animation: pulse-ring 2.4s infinite; }
    @keyframes pulse-ring { 0% { box-shadow: 0 0 0 0 rgba(180, 83, 9, .55); } 70% { box-shadow: 0 0 0 16px rgba(180, 83, 9, 0); } 100% { box-shadow: 0 0 0 0 rgba(180, 83, 9, 0); } }

    .petal { position: absolute; top: -40px; opacity: .0; animation: fall linear infinite; pointer-events: none; z-index: 2; }
    @keyframes fall { 0% { transform: translate3d(0, -40px, 0) rotate(0); opacity: 0; } 10% { opacity: .85; } 100% { transform: translate3d(var(--x, 60px), 780px, 0) rotate(360deg); opacity: 0; } }
    .bob { animation: bob 3s ease-in-out infinite; }
    @keyframes bob { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }

    .hero-progress { transform-origin: left; transform: scaleX(0); }
    .dot.is-active .hero-progress { animation: progress var(--dur, 6500ms) linear forwards; }
    @keyframes progress { to { transform: scaleX(1); } }

    /* Cuộn tới đâu hiện tới đó */
    .js [data-reveal] { opacity: 0; transform: translateY(30px) scale(.98); transition: opacity .7s ease, transform .7s cubic-bezier(.2,.7,.2,1); transition-delay: var(--d, 0ms); }
    .js [data-reveal="left"] { transform: translateX(-40px); }
    .js [data-reveal="right"] { transform: translateX(40px); }
    .js [data-reveal].is-in { opacity: 1; transform: none; }

    .lift { transition: transform .3s ease, box-shadow .3s ease; }
    .lift:hover { transform: translateY(-6px); box-shadow: 0 18px 40px -12px rgba(120, 53, 15, .35); }
    .wiggle:hover .wiggle-icon { animation: wiggle .6s ease; }
    @keyframes wiggle { 0%, 100% { transform: rotate(0); } 25% { transform: rotate(-14deg) scale(1.1); } 75% { transform: rotate(14deg) scale(1.1); } }

    .cta-bg { background: linear-gradient(120deg, #b45309, #be123c, #9d174d, #b45309); background-size: 300% 300%; animation: gradmove 10s ease infinite; }
    @keyframes gradmove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
    .float-a { animation: bob 5s ease-in-out infinite; } .float-b { animation: bob 7s ease-in-out infinite reverse; }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { animation-duration: .001ms !important; animation-iteration-count: 1 !important; transition-duration: .001ms !important; }
        .petal { display: none; } .js [data-reveal] { opacity: 1; transform: none; }
    }
</style>
<script>document.documentElement.classList.add('js');</script>

@php
    $slides = $banners->isNotEmpty()
        ? $banners->map(fn ($b) => \Illuminate\Support\Facades\Storage::url($b->image_path))->all()
        : [asset('images/hero-flower.jpg')];
    $zalo = $site->zaloUrl();
@endphp

{{-- ==================== HERO: SLIDER BANNER ==================== --}}
<section id="hero" class="relative overflow-hidden bg-stone-900 sm:min-h-[680px] lg:min-h-[720px]" aria-roledescription="carousel" aria-label="Banner giới thiệu">
    <div class="absolute inset-0">
        @foreach($slides as $i => $src)
            <div class="hero-slide absolute inset-0 overflow-hidden {{ $i === 0 ? 'is-active' : '' }}" data-slide aria-hidden="{{ $i === 0 ? 'false' : 'true' }}">
                <img src="{{ $src }}" alt="Banner {{ $i + 1 }}" class="w-full h-full object-cover object-[74%_center]" @if($i === 0) fetchpriority="high" @else loading="lazy" @endif decoding="async">
            </div>
        @endforeach
        {{-- Lớp phủ tối để chữ trắng luôn đọc rõ --}}
        <div class="absolute inset-0 z-[2] bg-gradient-to-b from-stone-950/80 via-stone-950/75 to-stone-950/90 sm:bg-gradient-to-r sm:from-stone-950/90 sm:via-stone-950/60 sm:to-stone-950/10"></div>
    </div>

    {{-- Cánh hoa rơi --}}
    @foreach([['🌸',6,'14s','0s',70],['🌷',22,'17s','3s',-50],['🌸',38,'15s','6s',80],['🌺',55,'19s','1s',-70],['🌸',70,'16s','8s',60],['🌷',86,'18s','4s',-60]] as [$em,$left,$dur,$delay,$dx])
        <span class="petal text-2xl sm:text-3xl" style="left:{{ $left }}%; animation-duration:{{ $dur }}; animation-delay:{{ $delay }}; --x:{{ $dx }}px" aria-hidden="true">{{ $em }}</span>
    @endforeach

    <div class="relative z-[3] max-w-7xl mx-auto px-5 sm:px-6 lg:px-8 sm:min-h-[680px] lg:min-h-[720px] flex items-center pt-12 pb-28 sm:pt-16 sm:pb-24">
        <div class="max-w-2xl text-white">
            <span class="rise inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white text-amber-900 text-base font-bold shadow-lg" style="--d:100ms">
                <span class="relative flex h-3 w-3"><span class="absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75 animate-ping"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-600"></span></span>
                Bài học dành cho mọi lứa tuổi
            </span>

            <h1 class="rise mt-5 text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-[1.15] tracking-tight drop-shadow-lg" style="--d:250ms">
                Đánh thức vẻ đẹp từ <span class="grad-text">những đóa hoa</span>
            </h1>

            <p class="rise mt-5 text-lg sm:text-xl text-stone-100 leading-relaxed max-w-xl" style="--d:400ms">
                Chỉ với 15 phút mỗi ngày, tự tay cắm những lẵng hoa tuyệt đẹp cho ngôi nhà của bạn. Video hướng dẫn <b class="text-white">chậm, rõ, dễ làm theo</b>.
            </p>

            <div class="rise mt-8 flex flex-col sm:flex-row gap-4" style="--d:550ms">
                <a href="{{ route('courses.index') }}" class="btn-shine btn-pulse inline-flex items-center justify-center gap-2 px-8 py-4 rounded-full bg-amber-500 hover:bg-amber-400 text-stone-950 font-extrabold text-lg shadow-xl transition hover:-translate-y-0.5">
                    ▶ Vào học ngay
                </a>
                @if($zalo)
                    <a href="{{ $zalo }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-full bg-white hover:bg-stone-100 text-stone-900 font-bold text-lg shadow-xl transition hover:-translate-y-0.5">💬 Tư vấn qua Zalo</a>
                @else
                    <a href="{{ route('contact.index') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-full bg-white hover:bg-stone-100 text-stone-900 font-bold text-lg shadow-xl transition hover:-translate-y-0.5">📞 Nhận tư vấn</a>
                @endif
            </div>

            <ul class="rise mt-8 flex flex-wrap gap-x-6 gap-y-2 text-base font-semibold text-white" style="--d:700ms">
                <li class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-sm">✓</span> Học thử miễn phí</li>
                <li class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-sm">✓</span> Xem mọi lúc, mọi nơi</li>
                <li class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-sm">✓</span> Hỗ trợ trọn đời</li>
            </ul>
        </div>
    </div>

    {{-- Điều khiển slider --}}
    @if(count($slides) > 1)
        <div class="absolute z-[4] bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-3">
            @foreach($slides as $i => $src)
                <button type="button" class="dot group h-10 flex items-center {{ $i === 0 ? 'is-active' : '' }}" data-dot="{{ $i }}" aria-label="Xem banner {{ $i + 1 }}">
                    <span class="block w-12 sm:w-16 h-1.5 rounded-full bg-white/40 group-hover:bg-white/60 overflow-hidden transition"><span class="hero-progress block h-full w-full bg-white rounded-full"></span></span>
                </button>
            @endforeach
        </div>
        <button type="button" data-prev class="hidden md:flex absolute z-[4] left-4 top-1/2 -translate-y-1/2 w-14 h-14 rounded-full bg-white/90 hover:bg-white text-stone-900 items-center justify-center text-2xl shadow-xl transition hover:scale-110" aria-label="Banner trước">&#8249;</button>
        <button type="button" data-next class="hidden md:flex absolute z-[4] right-4 top-1/2 -translate-y-1/2 w-14 h-14 rounded-full bg-white/90 hover:bg-white text-stone-900 items-center justify-center text-2xl shadow-xl transition hover:scale-110" aria-label="Banner sau">&#8250;</button>
    @endif
</section>

{{-- ==================== SỐ LIỆU ==================== --}}
<section class="relative z-10 -mt-14 px-4 sm:px-6">
    <div class="max-w-5xl mx-auto bg-white rounded-3xl border-2 border-stone-300 shadow-2xl shadow-stone-900/15 grid grid-cols-2 md:grid-cols-4 divide-stone-200 divide-y md:divide-y-0 md:divide-x" data-reveal>
        @foreach([
            ['📚', $stats['lessons'], 'Bài học'],
            ['🎬', $stats['videos'], 'Video hướng dẫn'],
            ['👩‍🌾', $stats['students'], 'Học viên'],
            ['⏱️', 15, 'Phút mỗi ngày'],
        ] as [$icon, $n, $label])
            <div class="p-6 text-center {{ $loop->index % 2 === 0 ? 'border-r md:border-r-0' : '' }} border-stone-200">
                <div class="text-3xl mb-1 bob" style="animation-delay: {{ $loop->index * 250 }}ms">{{ $icon }}</div>
                <div class="text-4xl font-extrabold text-amber-800" data-count="{{ $n }}">{{ $n }}</div>
                <div class="mt-1 text-base font-semibold text-stone-700">{{ $label }}</div>
            </div>
        @endforeach
    </div>
</section>

{{-- ==================== VÌ SAO CHỌN ==================== --}}
<section class="pt-20 sm:pt-24 pb-16 sm:pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-stone-900" data-reveal>Học cắm hoa chưa bao giờ dễ đến thế</h2>
        <p class="mt-3 text-lg text-stone-700 max-w-2xl mx-auto" data-reveal style="--d:100ms">Mọi thứ được thiết kế để người lớn tuổi cũng học được ngay từ lần đầu.</p>

        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
            @foreach([
                ['🌸', 'Thực hành thực tế', 'Quay cận cảnh từng bước cắt tỉa, định hình xốp. Rất chậm và chi tiết để các bác lớn tuổi cũng làm theo được.'],
                ['🎨', 'Tư duy phối màu', 'Bí quyết phối màu hoa theo phong cách Hàn Quốc sang trọng, nhẹ nhàng mà không hề lòe loẹt.'],
                ['✨', 'Hỗ trợ trọn đời', 'Cắm xong chụp ảnh gửi qua Zalo, giáo viên sẽ nhận xét và tư vấn cách cắm đẹp hơn, hoàn toàn miễn phí.'],
            ] as [$icon, $title, $text])
                <div class="wiggle lift bg-white rounded-3xl border-2 border-stone-300 p-8 text-left shadow-md" data-reveal style="--d: {{ $loop->index * 140 }}ms">
                    <div class="wiggle-icon w-16 h-16 bg-amber-200 rounded-2xl flex items-center justify-center text-4xl mb-5">{{ $icon }}</div>
                    <h3 class="text-2xl font-bold text-stone-900 mb-3">{{ $title }}</h3>
                    <p class="text-lg text-stone-700 leading-relaxed">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ==================== 3 BƯỚC (theo trạng thái người xem: khách / đã đăng nhập / VIP) ==================== --}}
@php
    $__u = auth()->user();
    $__logged = (bool) $__u;
    $__vip = $__u && ($__u->is_vip || $__u->role === 'admin');
@endphp
<section class="py-16 sm:py-20 bg-white border-y-2 border-stone-300">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-stone-900" data-reveal>Bắt đầu chỉ với 3 bước</h2>
        <p class="mt-3 text-lg text-stone-700 max-w-2xl mx-auto" data-reveal style="--d:100ms">Bài học miễn phí xem được ngay. Muốn xem thêm bài VIP thì mới cần tài khoản.</p>

        <div class="relative mt-14 grid md:grid-cols-3 gap-10 md:gap-6 text-center">
            <div class="hidden md:block absolute top-9 left-[17%] right-[17%] h-1 rounded bg-gradient-to-r from-amber-500 via-rose-500 to-amber-500 opacity-60" data-reveal></div>

            {{-- Bước 1: xem thử miễn phí (không cần tài khoản) --}}
            <div class="relative" data-reveal>
                <div class="mx-auto w-[4.5rem] h-[4.5rem] rounded-full bg-gradient-to-br from-amber-600 to-rose-600 text-white text-3xl font-extrabold flex items-center justify-center shadow-xl ring-8 ring-white relative z-10 bob">1</div>
                <h3 class="mt-5 text-2xl font-bold text-stone-900">Xem thử miễn phí</h3>
                <p class="mt-2 text-lg text-stone-700 max-w-xs mx-auto">Các bài học miễn phí xem được ngay, <b>không cần đăng ký</b>.</p>
                <a href="{{ route('courses.index') }}" class="mt-4 inline-flex px-6 py-2.5 rounded-full border-2 border-amber-700 text-amber-800 font-bold hover:bg-amber-700 hover:text-white transition">Xem bài học miễn phí</a>
            </div>

            {{-- Bước 2: đăng ký / đăng nhập (chỉ khi muốn xem video VIP) --}}
            <div class="relative" data-reveal style="--d:160ms">
                <div class="mx-auto w-[4.5rem] h-[4.5rem] rounded-full text-3xl font-extrabold flex items-center justify-center shadow-xl ring-8 ring-white relative z-10 bob {{ $__logged ? 'bg-emerald-600 text-white' : 'bg-gradient-to-br from-amber-600 to-rose-600 text-white' }}" style="animation-delay:300ms">{{ $__logged ? '✓' : '2' }}</div>
                <h3 class="mt-5 text-2xl font-bold text-stone-900">Đăng ký tài khoản</h3>
                @if($__logged)
                    <p class="mt-2 text-lg text-stone-700 max-w-xs mx-auto">Bạn đã đăng nhập với tài khoản <b>{{ $__u->name }}</b>.</p>
                    <a href="{{ route('profile.edit') }}" class="mt-4 inline-flex px-6 py-2.5 rounded-full bg-emerald-100 text-emerald-900 font-bold hover:bg-emerald-200 transition">✓ Đã có tài khoản</a>
                @else
                    <p class="mt-2 text-lg text-stone-700 max-w-xs mx-auto">Cần khi bạn muốn xem <b>video VIP</b>. Chỉ cần họ tên và số điện thoại, chưa đến 1 phút.</p>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        <a href="{{ route('register') }}" class="inline-flex px-6 py-2.5 rounded-full bg-amber-700 hover:bg-amber-800 text-white font-bold transition">Đăng ký</a>
                        <a href="{{ route('login') }}" class="inline-flex px-6 py-2.5 rounded-full border-2 border-stone-300 text-stone-800 font-bold hover:bg-stone-50 transition">Đăng nhập</a>
                    </div>
                @endif
            </div>

            {{-- Bước 3: nâng cấp gói qua Zalo (chỉ khi đã có tài khoản mà chưa là VIP) --}}
            <div class="relative" data-reveal style="--d:320ms">
                <div class="mx-auto w-[4.5rem] h-[4.5rem] rounded-full text-3xl font-extrabold flex items-center justify-center shadow-xl ring-8 ring-white relative z-10 bob {{ $__vip ? 'bg-emerald-600 text-white' : 'bg-gradient-to-br from-amber-600 to-rose-600 text-white' }}" style="animation-delay:600ms">{{ $__vip ? '✓' : '3' }}</div>
                <h3 class="mt-5 text-2xl font-bold text-stone-900">Nâng cấp gói qua Zalo</h3>
                @if($__vip)
                    <p class="mt-2 text-lg text-stone-700 max-w-xs mx-auto">Bạn đã là <b>thành viên VIP</b>, xem được toàn bộ bài học.</p>
                    <a href="{{ route('courses.index') }}" class="mt-4 inline-flex px-6 py-2.5 rounded-full bg-emerald-100 text-emerald-900 font-bold hover:bg-emerald-200 transition">✓ Vào học ngay</a>
                @else
                    <p class="mt-2 text-lg text-stone-700 max-w-xs mx-auto">{{ $__logged ? 'Tài khoản của bạn chưa là VIP.' : 'Đã có tài khoản mà chưa là VIP?' }} Liên hệ quản trị viên qua <b>Zalo</b> để được nâng cấp và xem toàn bộ bài học.</p>
                    <button type="button" data-help="upgrade" class="mt-4 inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#0068ff] hover:bg-blue-700 text-white font-bold transition">💬 Cách nâng cấp qua Zalo</button>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ==================== TÁC PHẨM: băng chuyền so le chạy chậm + xem phóng to (Admin → Tác phẩm nổi bật) ==================== --}}
@if($artworks->isNotEmpty())
<style>
    .gal-track { scrollbar-width: none; -webkit-overflow-scrolling: touch; cursor: grab; overscroll-behavior-x: contain; }
    .gal-track.is-dragging { cursor: grabbing; user-select: none; }
    .gal-track::-webkit-scrollbar { display: none; }
    .gal-card { flex: 0 0 62%; transition: transform .5s cubic-bezier(.2,.7,.2,1), box-shadow .5s; }
    @media (min-width: 640px)  { .gal-card { flex-basis: calc((100% - 24px) / 2.4); } }
    @media (min-width: 1024px) { .gal-card { flex-basis: calc((100% - 48px) / 3.4); } }
    @media (min-width: 1280px) { .gal-card { flex-basis: calc((100% - 72px) / 4.4); } }
    /* So le: thẻ lẻ thấp hơn, xen kẽ tỉ lệ ảnh và hơi nghiêng ngược chiều nhau */
    .gal-card.is-odd  { margin-top: 2.25rem; transform: rotate(1.6deg); }
    .gal-card.is-even { transform: rotate(-1.6deg); }
    @media (min-width: 640px) { .gal-card.is-odd { margin-top: 3.5rem; } }
    .gal-card:hover, .gal-card:focus-visible { transform: rotate(0deg) translateY(-6px) scale(1.03); box-shadow: 0 24px 45px -12px rgba(120, 53, 15, .45); z-index: 2; }
    #lb { opacity: 0; transition: opacity .25s ease; }
    #lb.is-open { opacity: 1; }
    #lbImg { transition: opacity .2s ease, transform .3s ease; }
    #lbImg.is-loading { opacity: .2; transform: scale(.98); }
    @media (prefers-reduced-motion: reduce) { .gal-card, .gal-card:hover { transform: none; } }
</style>
<section id="tac-pham" class="py-16 sm:py-20 overflow-hidden" data-gallery>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-stone-900" data-reveal>Các tác phẩm nổi bật</h2>
            <p class="mt-3 text-lg text-stone-700" data-reveal style="--d:100ms">Những lẵng hoa mang phong cách hiện đại và đầy nghệ thuật.</p>
        </div>
    </div>

    <div class="relative">
        <div class="gal-track flex items-start gap-4 sm:gap-6 overflow-x-auto pt-4 pb-14 px-4 sm:px-6" data-track>
            @foreach($artworks as $art)
                <button type="button" data-open="{{ $loop->index }}" aria-label="Xem phóng to: {{ $art->title ?: 'Tác phẩm '.$loop->iteration }}"
                        class="gal-card {{ $loop->index % 2 ? 'is-odd aspect-square' : 'is-even aspect-[4/5]' }} group relative overflow-hidden rounded-3xl border-4 border-white shadow-lg bg-stone-200 text-left focus-visible:ring-4 focus-visible:ring-amber-600">
                    <img src="{{ $art->url() }}" alt="{{ $art->title ?: 'Tác phẩm cắm hoa' }}" loading="{{ $loop->index < 4 ? 'eager' : 'lazy' }}" decoding="async" draggable="false"
                         class="w-full h-full object-cover transition duration-700 group-hover:scale-110">
                    <span class="absolute inset-x-0 bottom-0 p-4 pt-12 bg-gradient-to-t from-stone-950/85 to-transparent text-white">
                        <span class="block font-bold text-lg leading-snug">{{ $art->title ?: 'Tác phẩm '.$loop->iteration }}</span>
                        <span class="block text-sm text-white/85">Bấm để xem phóng to</span>
                    </span>
                    <span class="absolute top-3 right-3 w-11 h-11 rounded-full bg-white/95 text-stone-900 flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 group-focus-visible:opacity-100 transition" aria-hidden="true">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-4.35-4.35M11 8v6M8 11h6M19 11a8 8 0 11-16 0 8 8 0 0116 0z"/></svg>
                    </span>
                </button>
            @endforeach
        </div>

        <button type="button" data-gprev class="hidden md:flex absolute left-3 lg:left-6 top-1/2 -translate-y-1/2 z-10 w-14 h-14 rounded-full bg-white text-stone-900 border-2 border-stone-300 shadow-xl items-center justify-center text-3xl hover:bg-amber-50 transition" aria-label="Xem ảnh trước">&#8249;</button>
        <button type="button" data-gnext class="hidden md:flex absolute right-3 lg:right-6 top-1/2 -translate-y-1/2 z-10 w-14 h-14 rounded-full bg-white text-stone-900 border-2 border-stone-300 shadow-xl items-center justify-center text-3xl hover:bg-amber-50 transition" aria-label="Xem ảnh sau">&#8250;</button>
    </div>
    <p class="-mt-6 px-4 text-center text-base text-stone-600" data-ghint>Bấm vào ảnh để xem phóng to · Kéo sang ngang để xem thêm</p>
</section>

{{-- Xem phóng to --}}
<div id="lb" class="fixed inset-0 z-[200] hidden bg-stone-950/95 items-center justify-center p-3 sm:p-8" role="dialog" aria-modal="true" aria-label="Xem ảnh phóng to">
    <button type="button" data-lbclose class="absolute top-3 right-3 sm:top-5 sm:right-5 z-10 w-14 h-14 rounded-full bg-white text-stone-900 text-3xl font-bold shadow-xl hover:bg-amber-100 transition" aria-label="Đóng">&times;</button>
    <button type="button" data-lbprev class="absolute left-2 sm:left-6 top-1/2 -translate-y-1/2 z-10 w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-white/95 text-stone-900 text-4xl shadow-xl hover:bg-amber-100 transition" aria-label="Ảnh trước">&#8249;</button>
    <button type="button" data-lbnext class="absolute right-2 sm:right-6 top-1/2 -translate-y-1/2 z-10 w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-white/95 text-stone-900 text-4xl shadow-xl hover:bg-amber-100 transition" aria-label="Ảnh sau">&#8250;</button>
    <figure class="max-w-5xl w-full max-h-full flex flex-col items-center" data-lbstage>
        <img id="lbImg" src="" alt="" class="max-h-[78vh] max-w-full w-auto rounded-2xl shadow-2xl object-contain select-none" draggable="false">
        <figcaption class="mt-4 text-center text-white">
            <span id="lbCap" class="block text-xl sm:text-2xl font-bold"></span>
            <span id="lbCount" class="block text-base text-white/80 mt-1"></span>
        </figcaption>
    </figure>
</div>
@endif

{{-- ==================== BÀI HỌC ==================== --}}
@if($lessons->isNotEmpty())
<section class="py-16 sm:py-20 bg-white border-y-2 border-stone-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4 mb-10">
            <div data-reveal="left">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-stone-900">Bắt đầu từ đây</h2>
                <p class="text-lg text-stone-700 mt-2">Các bài học được sắp xếp từ dễ đến khó.</p>
            </div>
            <a href="{{ route('courses.index') }}" class="hidden sm:inline-flex px-6 py-3 rounded-full border-2 border-amber-700 text-amber-800 font-bold hover:bg-amber-700 hover:text-white transition whitespace-nowrap" data-reveal="right">Xem tất cả &rarr;</a>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($lessons->take(3) as $lesson)
                @php $first = $lesson->videos->first(); @endphp
                <a href="{{ $first ? route('courses.video', [$lesson, $first]) : route('courses.index') }}"
                   class="group lift block bg-cream rounded-3xl border-2 border-stone-300 p-7" data-reveal style="--d: {{ $loop->index * 140 }}ms">
                    <span class="w-14 h-14 rounded-2xl bg-amber-700 text-white text-2xl font-extrabold flex items-center justify-center mb-5 group-hover:rotate-6 transition">{{ $loop->iteration }}</span>
                    <h3 class="text-2xl font-bold text-stone-900 group-hover:text-amber-800 transition">{{ $lesson->title }}</h3>
                    <p class="mt-2 text-lg text-stone-700 line-clamp-2">{{ $lesson->description ?: 'Xem video hướng dẫn từng bước, dễ làm theo.' }}</p>
                    <p class="mt-5 text-lg font-bold text-amber-800">{{ $lesson->videos->count() }} video · Học ngay <span class="inline-block group-hover:translate-x-1 transition">&rarr;</span></p>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ==================== KÊU GỌI HÀNH ĐỘNG ==================== --}}
<section class="cta-bg relative overflow-hidden py-20 sm:py-24">
    <span class="float-a absolute top-8 left-[8%] text-6xl opacity-30 select-none" aria-hidden="true">🌸</span>
    <span class="float-b absolute bottom-6 right-[10%] text-7xl opacity-30 select-none" aria-hidden="true">🌷</span>
    <span class="float-a absolute top-1/3 right-[22%] text-5xl opacity-25 select-none" aria-hidden="true">🌺</span>
    <div class="relative max-w-3xl mx-auto px-4 text-center text-white" data-reveal>
        <h2 class="text-3xl sm:text-5xl font-extrabold leading-tight drop-shadow">Sẵn sàng cắm bó hoa đầu tiên?</h2>
        <p class="mt-4 text-xl text-white">Đăng ký miễn phí và xem ngay các video hướng dẫn.</p>
        <div class="mt-9 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('courses.index') }}" class="btn-shine inline-flex items-center justify-center px-9 py-4 rounded-full bg-white text-amber-900 font-extrabold text-xl shadow-2xl hover:-translate-y-1 transition">Xem bài học</a>
            @guest<a href="{{ route('register') }}" class="inline-flex items-center justify-center px-9 py-4 rounded-full border-2 border-white text-white font-bold text-xl hover:bg-white hover:text-amber-900 transition">Đăng ký miễn phí</a>@endguest
        </div>
    </div>
</section>

<script>
(function () {
    // ---------- Slider banner ----------
    const slides = [...document.querySelectorAll('[data-slide]')];
    const dots = [...document.querySelectorAll('[data-dot]')];
    const hero = document.getElementById('hero');
    const DUR = 6500;
    let cur = 0, timer = null, paused = false;

    function show(n) {
        cur = (n + slides.length) % slides.length;
        slides.forEach((s, i) => { s.classList.toggle('is-active', i === cur); s.setAttribute('aria-hidden', i === cur ? 'false' : 'true'); });
        dots.forEach((d, i) => { d.classList.remove('is-active'); void d.offsetWidth; d.classList.toggle('is-active', i === cur); });
    }
    function play() { clearInterval(timer); if (slides.length > 1) timer = setInterval(() => { if (!paused && !document.hidden) show(cur + 1); }, DUR); }

    if (slides.length > 1) {
        hero.style.setProperty('--dur', DUR + 'ms');
        dots.forEach(d => d.style.setProperty('--dur', DUR + 'ms'));
        dots.forEach((d, i) => d.addEventListener('click', () => { show(i); play(); }));
        hero.querySelector('[data-prev]')?.addEventListener('click', () => { show(cur - 1); play(); });
        hero.querySelector('[data-next]')?.addEventListener('click', () => { show(cur + 1); play(); });
        hero.addEventListener('mouseenter', () => paused = true);
        hero.addEventListener('mouseleave', () => paused = false);
        let x0 = null; // vuốt trên điện thoại
        hero.addEventListener('touchstart', e => { x0 = e.touches[0].clientX; }, { passive: true });
        hero.addEventListener('touchend', e => { if (x0 === null) return; const dx = e.changedTouches[0].clientX - x0; if (Math.abs(dx) > 50) { show(cur + (dx < 0 ? 1 : -1)); play(); } x0 = null; });
        dots[0]?.classList.add('is-active');
        play();
    }

    // ---------- Hiện dần khi cuộn tới + đếm số ----------
    const countUp = (el) => {
        const end = parseInt(el.dataset.count, 10) || 0, t0 = performance.now(), dur = 1400;
        const step = (t) => { const p = Math.min(1, (t - t0) / dur); el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3))).toLocaleString('vi-VN'); if (p < 1) requestAnimationFrame(step); };
        requestAnimationFrame(step);
    };
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            e.target.classList.add('is-in');
            e.target.querySelectorAll('[data-count]').forEach(countUp);
            io.unobserve(e.target);
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('[data-reveal]').forEach(el => io.observe(el));
})();
</script>

@if($artworks->isNotEmpty())
<script>
(function () {
    const root = document.querySelector('[data-gallery]');
    if (!root) return;
    const track = root.querySelector('[data-track]');
    const originals = [...track.querySelectorAll('[data-open]')];
    const prev = root.querySelector('[data-gprev]'), next = root.querySelector('[data-gnext]');
    const items = @js($artworks->map(fn ($a) => ['src' => $a->url(), 'title' => $a->title ?: ''])->values());
    const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const SPEED = 42;                 // px mỗi giây: chậm, dễ ngắm
    let cycleW = 0, marquee = false, pos = 0, last = 0, holdUntil = 0, hovering = false, dragging = false, moved = 0, lightbox = false, raf = 0;

    // ---------- Vòng lặp vô tận: nhân bản dãy ảnh để nối liền đầu-cuối ----------
    function build() {
        track.querySelectorAll('[data-clone]').forEach(n => n.remove());
        stagger();
        track.scrollLeft = 0; pos = 0; marquee = false; cycleW = 0;

        // Chiều dài của MỘT dãy ảnh gốc (kể cả khoảng cách cuối)
        const last = originals[originals.length - 1];
        const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
        const setW = originals.length > 1 ? (last.offsetLeft + last.offsetWidth - originals[0].offsetLeft + gap) : 0;

        // Chỉ 1 ảnh: đứng yên, căn giữa. Từ 2 ảnh trở lên luôn chạy (dù dãy ngắn hơn màn hình thì nhân bản cho đủ dài)
        if (!setW) { track.classList.add('justify-center'); prev.classList.add('!hidden'); next.classList.add('!hidden'); return; }
        track.classList.remove('justify-center'); prev.classList.remove('!hidden'); next.classList.remove('!hidden');

        // Chu kỳ phải gồm SỐ CHẴN thẻ để kiểu so le (thấp/cao) xen kẽ đúng ở chỗ nối vòng
        const repeat = originals.length % 2 ? 2 : 1;
        // Tổng số dãy cần có trong DOM: đủ chu kỳ + phủ kín chiều rộng khung nhìn
        const totalSets = Math.max(repeat + 1, Math.ceil((setW * repeat + track.clientWidth + setW) / setW));
        for (let r = 1; r < totalSets; r++) {
            originals.forEach(o => {
                const c = o.cloneNode(true);
                c.setAttribute('data-clone', ''); c.setAttribute('aria-hidden', 'true'); c.tabIndex = -1;
                track.appendChild(c);
            });
        }
        cycleW = setW * repeat; marquee = true;
        stagger();
        start();
    }

    // Kiểu so le (thấp/cao, nghiêng trái/phải) tính theo vị trí thật trên cả dải, kể cả bản sao,
    // nên xen kẽ liên tục và không có hai thẻ giống nhau đứng cạnh nhau ở chỗ nối vòng
    function stagger() {
        [...track.children].forEach((c, k) => {
            const odd = k % 2 === 1;
            c.classList.toggle('is-odd', odd); c.classList.toggle('aspect-square', odd);
            c.classList.toggle('is-even', !odd); c.classList.toggle('aspect-[4/5]', !odd);
        });
    }

    // ---------- Chạy chậm liên tục ----------
    function frame(now) {
        raf = requestAnimationFrame(frame);
        const dt = Math.min(64, now - (last || now)); last = now;
        if (!marquee) return;

        // Người dùng vừa tự cuộn/kéo: đồng bộ vị trí, không tranh chấp
        if (Math.abs(track.scrollLeft - pos) > 3) pos = track.scrollLeft;

        const idle = !dragging && !hovering && !lightbox && !document.hidden && now > holdUntil && !reduce;
        if (idle) pos += SPEED * dt / 1000;

        // Nối vòng: khi đi hết một chu kỳ thì trở về đầu, hình ảnh giống hệt nên không thấy giật
        if (pos >= cycleW) pos -= cycleW;
        if (pos < 0) pos += cycleW;
        if (Math.abs(track.scrollLeft - pos) > 0.4) track.scrollLeft = pos;
    }
    function start() { cancelAnimationFrame(raf); last = 0; raf = requestAnimationFrame(frame); }
    const hold = (ms) => { holdUntil = performance.now() + ms; };

    // ---------- Mũi tên: dịch đúng một thẻ, mượt ----------
    function nudge(dir) {
        if (!marquee) return;
        const step = originals[1].offsetLeft - originals[0].offsetLeft;
        if (dir < 0 && pos < step) { pos += cycleW; track.scrollLeft = pos; }    // đang ở đầu: nhảy sang bản sao rồi lùi lại
        hold(3500);
        const from = pos, to = pos + dir * step, t0 = performance.now(), dur = 550;
        (function anim(now) {
            const p = Math.min(1, (now - t0) / dur), e = 1 - Math.pow(1 - p, 3);
            pos = from + (to - from) * e; track.scrollLeft = pos;
            if (p < 1) requestAnimationFrame(anim);
        })(t0);
    }
    prev.addEventListener('click', () => nudge(-1));
    next.addEventListener('click', () => nudge(1));

    // ---------- Tạm dừng khi rê chuột / lấy nét / chạm; kéo bằng chuột ----------
    root.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse') hovering = true; });
    root.addEventListener('pointerleave', () => { hovering = false; dragging = false; track.classList.remove('is-dragging'); });
    root.addEventListener('focusin', () => hold(6000));
    track.addEventListener('touchstart', () => hold(4000), { passive: true });
    track.addEventListener('touchend', () => hold(2500), { passive: true });
    track.addEventListener('wheel', () => hold(2500), { passive: true });

    let dragX = 0, dragLeft = 0;
    track.addEventListener('pointerdown', (e) => {
        if (e.pointerType !== 'mouse' || e.button !== 0) return;
        dragging = true; moved = 0; dragX = e.clientX; dragLeft = track.scrollLeft;
    });
    addEventListener('pointermove', (e) => {
        if (!dragging) return;
        const dx = e.clientX - dragX; moved = Math.max(moved, Math.abs(dx));
        if (moved > 5) track.classList.add('is-dragging');
        track.scrollLeft = dragLeft - dx; pos = track.scrollLeft;
    });
    addEventListener('pointerup', () => { if (dragging) { dragging = false; track.classList.remove('is-dragging'); hold(1500); } });

    // ---------- Xem phóng to ----------
    const lb = document.getElementById('lb'), img = document.getElementById('lbImg'), cap = document.getElementById('lbCap'), count = document.getElementById('lbCount');
    let cur = 0, opener = null;

    function show(i) {
        cur = (i + items.length) % items.length;
        const it = items[cur];
        img.classList.add('is-loading');
        const pre = new Image();
        pre.onload = () => { img.src = it.src; img.alt = it.title || 'Tác phẩm cắm hoa'; img.classList.remove('is-loading'); };
        pre.src = it.src;
        cap.textContent = it.title;
        cap.classList.toggle('hidden', !it.title);
        count.textContent = (cur + 1) + ' / ' + items.length;
        lb.querySelectorAll('[data-lbprev],[data-lbnext]').forEach(b => b.classList.toggle('hidden', items.length < 2));
        [cur + 1, cur - 1].forEach(n => { const p = new Image(); p.src = items[(n + items.length) % items.length].src; });   // tải trước ảnh kế bên
    }
    function open(i, from) {
        opener = from; lightbox = true;
        lb.classList.remove('hidden'); lb.classList.add('flex');
        setTimeout(() => lb.classList.add('is-open'), 10);
        document.documentElement.style.overflow = 'hidden';
        show(i);
        lb.querySelector('[data-lbclose]').focus();
    }
    function close() {
        lightbox = false; lb.classList.remove('is-open');
        setTimeout(() => { lb.classList.add('hidden'); lb.classList.remove('flex'); img.src = ''; }, 250);
        document.documentElement.style.overflow = '';
        opener && opener.isConnected && opener.focus({ preventScroll: true });
        hold(1500);
    }
    // Bấm vào ảnh (kể cả bản sao) — bỏ qua nếu vừa kéo chuột
    track.addEventListener('click', (e) => {
        const c = e.target.closest('[data-open]');
        if (!c || moved > 5) { moved = 0; return; }
        open(Number(c.dataset.open), c);
    });
    lb.querySelector('[data-lbclose]').addEventListener('click', close);
    lb.querySelector('[data-lbprev]').addEventListener('click', () => show(cur - 1));
    lb.querySelector('[data-lbnext]').addEventListener('click', () => show(cur + 1));
    lb.addEventListener('click', (e) => { if (e.target === lb || e.target === lb.querySelector('[data-lbstage]')) close(); });   // bấm ra ngoài ảnh thì đóng
    document.addEventListener('keydown', (e) => {
        if (!lightbox) return;
        if (e.key === 'Escape') close();
        else if (e.key === 'ArrowLeft') show(cur - 1);
        else if (e.key === 'ArrowRight') show(cur + 1);
        else if (e.key === 'Tab') {   // giữ tiêu điểm bên trong hộp xem ảnh
            const f = [...lb.querySelectorAll('button:not(.hidden)')]; const first = f[0], last = f[f.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });
    let x0 = null;   // vuốt trái/phải trong hộp xem ảnh
    lb.addEventListener('touchstart', (e) => { x0 = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', (e) => { if (x0 === null) return; const dx = e.changedTouches[0].clientX - x0; if (Math.abs(dx) > 50) show(cur + (dx < 0 ? 1 : -1)); x0 = null; });

    // Dựng vòng lặp khi ảnh đã có kích thước; dựng lại khi đổi cỡ cửa sổ
    const imgs = [...track.querySelectorAll('img')].slice(0, 4);
    Promise.all(imgs.map(i => i.complete ? 0 : new Promise(r => { i.onload = i.onerror = r; }))).then(build);
    let rz; addEventListener('resize', () => { clearTimeout(rz); rz = setTimeout(build, 250); });
})();
</script>
@endif
@endsection
