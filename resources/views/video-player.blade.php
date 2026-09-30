@extends('layouts.public')
@section('title', $video->title)
@section('meta_description', 'Xem video "'.$video->title.'" thuộc bài học '.$lesson->title.'.')
@if($poster)
@section('og_image', $poster)
@endif

@push('jsonld')
{!! \App\Support\JsonLd::tag(array_filter([
    '@type' => 'VideoObject',
    'name' => $video->title,
    'description' => \Illuminate\Support\Str::limit(trim(($lesson->description ?: 'Video hướng dẫn cắm hoa thuộc '.$lesson->title.'.')), 300, ''),
    'thumbnailUrl' => $poster ? [$poster] : null,
    'uploadDate' => $video->created_at->toAtomString(),
    'inLanguage' => 'vi', 'isAccessibleForFree' => (bool) $video->is_free,
    'url' => route('courses.video', [$lesson, $video]),
])) !!}
{!! \App\Support\JsonLd::tag(['@type' => 'BreadcrumbList', 'itemListElement' => [
    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => route('home')],
    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Bài học', 'item' => route('courses.index')],
    ['@type' => 'ListItem', 'position' => 3, 'name' => $video->title, 'item' => route('courses.video', [$lesson, $video])],
]]) !!}
@endpush

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.css">
<style>
    :root { --plyr-color-main: #d97706; --plyr-video-background: transparent; --plyr-menu-radius: 12px; --plyr-control-radius: 8px; }
    #stage, #stage .plyr, #stage .plyr__video-wrapper, #stage video { width: 100%; height: 100%; }
    #stage video { object-fit: contain; }
    /* Nền trong suốt để thấy lớp nền mờ ở hai bên video dọc; toàn màn hình thì đen như YouTube */
    #stage .plyr, #stage .plyr--video, #stage .plyr__video-wrapper, #stage .plyr__poster { background-color: transparent; }
    #stage .plyr--fullscreen-active, #stage .plyr--fullscreen-active .plyr__video-wrapper { background-color: #000; }
    .plyr { font-family: 'Roboto', sans-serif; }
    .plyr--video .plyr__control.plyr__tab-focus, .plyr--video .plyr__control:hover, .plyr--video .plyr__control[aria-expanded=true] { background: #d97706; }
    .plyr__menu__container { max-height: min(60vh, 420px); overflow-y: auto; }
    .pl-scroll::-webkit-scrollbar { width: 6px; }
    .pl-scroll::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 6px; }
    /* Thanh âm lượng dọc: hiện phía trên nút loa khi rê chuột */
    /* Plyr đặt nút loa BÊN TRONG .plyr__volume: chỉ ẩn thanh trượt ngang cũ, giữ nút loa */
    .plyr__controls .plyr__volume { min-width: 0; width: auto; max-width: none; }
    .plyr__controls .plyr__volume input[data-plyr='volume'] { display: none !important; }
    .vol-wrap { position: relative; display: inline-flex; align-items: center; }
    .vol-pop { position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); padding-bottom: 8px; z-index: 10;
               opacity: 0; visibility: hidden; transition: opacity .15s, visibility 0s .2s; }
    .vol-wrap:hover .vol-pop, .vol-pop.dragging { opacity: 1; visibility: visible; transition-delay: 0s; }
    .vol-panel { background: rgba(20,20,20,.92); border-radius: 12px; padding: 12px 0; width: 38px; height: 120px; display: flex; justify-content: center; box-shadow: 0 4px 16px rgba(0,0,0,.5); }
    .vol-track { position: relative; width: 6px; height: 100%; background: rgba(255,255,255,.25); border-radius: 6px; cursor: pointer; touch-action: none; }
    .vol-track::before { content: ''; position: absolute; inset: -6px -14px; }
    .vol-fill { position: absolute; left: 0; right: 0; bottom: 0; background: #d97706; border-radius: 6px; }
    .vol-thumb { position: absolute; left: 50%; width: 14px; height: 14px; margin: 0 0 -7px -7px; border-radius: 50%; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.6); }
    /* Ảnh xem trước khi rê chuột trên thanh tua */
    .seek-preview { position: absolute; bottom: calc(100% + 14px); z-index: 20; transform: translateX(-50%); pointer-events: none; display: none; text-align: center; }
    .seek-preview .sp-img { width: 160px; height: 90px; border-radius: 8px; border: 2px solid #fff; background-color: #000; background-repeat: no-repeat; box-shadow: 0 4px 14px rgba(0,0,0,.6); }
    .seek-preview .sp-time { display: inline-block; margin-top: 4px; padding: 1px 8px; border-radius: 6px; background: rgba(0,0,0,.8); color: #fff; font-size: 13px; font-weight: 600; }
    .seek-preview.no-img .sp-img { display: none; }
    kbd { background:#fafaf9; border:1px solid #d6d3d1; border-bottom-width:2px; border-radius:6px; padding:1px 7px; font-size:12px; color:#44403c; }
</style>

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- Đường dẫn --}}
    <nav class="text-sm text-stone-700 mb-4 flex flex-wrap items-center gap-1.5">
        <a href="{{ route('home') }}" class="hover:text-amber-700 transition">Trang chủ</a><span class="text-stone-300">/</span>
        <a href="{{ route('courses.index') }}" class="hover:text-amber-700 transition">Bài học</a><span class="text-stone-300">/</span>
        <span class="text-stone-700 font-medium truncate max-w-[50vw]">{{ $lesson->title }}</span>
    </nav>

    <div class="flex flex-col xl:flex-row gap-6">
        {{-- ================= Khu vực phát video ================= --}}
        <div class="xl:flex-1 min-w-0">
            <div id="playerShell" class="relative rounded-2xl overflow-hidden bg-neutral-950 shadow-xl shadow-stone-900/10 ring-1 ring-stone-900/5 flex justify-center">
                @if(!$canWatch)
                    <div class="relative w-full min-h-[24rem] sm:min-h-0 sm:aspect-video flex flex-col items-center justify-center text-center px-5 py-8 bg-gradient-to-br from-amber-50 to-rose-50">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center mb-3">
                            <svg class="w-9 h-9 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-stone-900 mb-3">Video dành cho thành viên VIP</h2>

                        @guest
                            <p class="text-stone-800 text-lg mb-3 max-w-md">Để xem video này, bạn cần làm <b>2 việc</b>:</p>
                            <ol class="text-left text-lg text-stone-800 space-y-2 mb-5 max-w-md">
                                <li class="flex gap-3"><span class="w-8 h-8 shrink-0 rounded-full bg-amber-700 text-white font-extrabold flex items-center justify-center">1</span><span><b>Đăng nhập</b> hoặc <b>đăng ký</b> tài khoản (miễn phí).</span></li>
                                <li class="flex gap-3"><span class="w-8 h-8 shrink-0 rounded-full bg-amber-700 text-white font-extrabold flex items-center justify-center">2</span><span><b>Liên hệ quản trị viên qua Zalo</b> để được nâng cấp gói.</span></li>
                            </ol>
                            <div class="flex flex-col sm:flex-row gap-3 items-center justify-center">
                                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-amber-700 hover:bg-amber-800 text-white text-lg font-extrabold py-3 px-8 rounded-full shadow-lg transition">Đăng nhập</a>
                                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white border-2 border-amber-700 text-amber-800 hover:bg-amber-50 text-lg font-bold py-3 px-8 rounded-full transition">Đăng ký</a>
                            </div>
                            <button type="button" data-help="upgrade" class="mt-4 text-base font-semibold text-blue-700 hover:text-blue-800 underline underline-offset-2">Xem hướng dẫn nâng cấp qua Zalo</button>
                        @else
                            <p class="text-stone-800 text-lg mb-1 max-w-md">Tài khoản <b>{{ auth()->user()->name }}</b> chưa là thành viên VIP.</p>
                            <p class="text-stone-700 text-lg mb-6 max-w-md">Hãy <b>liên hệ quản trị viên qua Zalo</b> để được nâng cấp gói, sau đó xem được toàn bộ bài học.</p>
                            <button type="button" data-help="upgrade" class="inline-flex items-center gap-2 bg-[#0068ff] hover:bg-blue-700 text-white text-lg font-extrabold py-3.5 px-8 rounded-full shadow-lg transition">💬 Xem cách nâng cấp qua Zalo</button>
                        @endguest
                    </div>
                @elseif($video->status === 'ready' && $video->hls_path)
                    <div id="stage" class="relative overflow-hidden bg-neutral-950" style="aspect-ratio:16/9; width:min(100%, calc(78vh * 16 / 9));">
                        @if($poster)
                            <div class="absolute inset-0 bg-cover bg-center blur-2xl opacity-50 scale-125" style="background-image:url('{{ $poster }}')"></div>
                        @endif
                        <video id="player" playsinline controlsList="nodownload" oncontextmenu="return false;" @if($poster) data-poster="{{ $poster }}" @endif></video>
                        {{-- Màn hình lỗi --}}
                        <div id="errorBox" class="hidden absolute inset-0 z-20 bg-black/85 text-white flex-col items-center justify-center text-center px-6">
                            <p class="text-lg font-semibold mb-1">Không phát được video</p>
                            <p class="text-neutral-300 text-sm mb-4" id="errorMsg">Vui lòng kiểm tra kết nối mạng.</p>
                            <button id="retryBtn" class="bg-amber-700 hover:bg-amber-500 px-6 py-2 rounded-full font-semibold">Thử lại</button>
                        </div>
                    </div>
                @elseif(in_array($video->status, ['pending', 'processing']))
                    <div data-vstatus-id="{{ $video->id }}" data-status="{{ $video->status }}" class="relative w-full aspect-video flex flex-col items-center justify-center text-center px-6 bg-stone-100">
                        <svg class="animate-spin h-10 w-10 text-amber-700 mb-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <h2 class="text-xl font-bold text-stone-900">Video đang được xử lý…</h2>
                        <p class="text-stone-700 mt-2">Trang sẽ tự chuyển sang trình phát ngay khi xử lý xong, bạn không cần tải lại.</p>
                    </div>
                @else
                    <div class="relative w-full aspect-video flex flex-col items-center justify-center text-center px-6 bg-rose-50">
                        <h2 class="text-xl font-bold text-rose-700">Video bị lỗi xử lý</h2>
                        <p class="text-stone-700 mt-2">Quản trị viên sẽ sớm khắc phục.</p>
                    </div>
                @endif
            </div>

            {{-- Tiêu đề + điều hướng --}}
            <div class="mt-5 bg-white rounded-2xl border border-stone-300 shadow-md p-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-stone-900 leading-tight">{{ $video->title }}</h1>
                        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 font-semibold">{{ $lesson->title }}</span>
                            <span class="px-3 py-1 rounded-full font-semibold {{ $video->is_free ? 'bg-emerald-100 text-emerald-700' : 'bg-violet-100 text-violet-700' }}">{{ $video->is_free ? 'Miễn phí' : 'Thành viên' }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        @if($prev)<a href="{{ $prev['url'] }}" title="{{ $prev['title'] }}" class="px-4 py-2.5 rounded-full bg-white border border-stone-300 text-stone-700 hover:border-amber-400 hover:text-amber-700 font-semibold text-sm transition">&larr; Bài trước</a>@endif
                        @if($next)<a id="nextBtn" href="{{ $next['url'] }}" title="{{ $next['title'] }}" class="px-4 py-2.5 rounded-full bg-gradient-to-r from-amber-700 to-rose-600 text-white font-semibold text-sm shadow hover:shadow-md transition">Bài tiếp &rarr;</a>@endif
                    </div>
                </div>

                @if($lesson->description)
                    <div class="mt-5 pt-5 border-t border-stone-100 text-stone-700 leading-relaxed">{!! nl2br(e($lesson->description)) !!}</div>
                @endif
            </div>

            @if($canWatch && $video->status === 'ready')
            <details id="shortcuts" class="mt-4 rounded-2xl bg-white border border-stone-300 p-4 text-sm text-stone-600">
                <summary class="cursor-pointer font-semibold text-stone-700">Phím tắt</summary>
                <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-y-2 gap-x-4">
                    <span><kbd>Space</kbd> / <kbd>K</kbd> Phát / dừng</span>
                    <span><kbd>←</kbd> <kbd>→</kbd> Tua 10 giây</span>
                    <span><kbd>↑</kbd> <kbd>↓</kbd> Âm lượng</span>
                    <span><kbd>F</kbd> Toàn màn hình</span>
                    <span><kbd>M</kbd> Tắt tiếng</span>
                    <span><kbd>&lt;</kbd> <kbd>&gt;</kbd> Đổi tốc độ</span>
                </div>
            </details>
            @endif
        </div>

        {{-- ================= Danh sách bài học ================= --}}
        <aside class="xl:w-[380px] shrink-0">
            <div class="rounded-2xl bg-white border border-stone-300 shadow-md xl:sticky xl:top-24 overflow-hidden">
                <div class="px-5 py-4 border-b border-stone-100 font-bold text-stone-900">Danh sách bài học</div>
                <div class="pl-scroll max-h-[70vh] overflow-y-auto" id="playlist">
                    @foreach($lessons as $l)
                        <details class="group border-b border-stone-100 last:border-0" @if($l->id === $lesson->id) open @endif>
                            <summary class="cursor-pointer list-none flex items-center justify-between px-5 py-3 bg-stone-50 hover:bg-amber-50/70 transition">
                                <span class="font-semibold text-sm text-stone-800">{{ $l->title }}</span>
                                <span class="text-sm text-stone-700 shrink-0 ml-2">{{ $l->videos->count() }} video</span>
                            </summary>
                            <div>
                                @foreach($l->videos as $v)
                                    @php
                                        $active = $v->id === $video->id;
                                        $locked = !$v->canBeWatchedBy(auth()->user());
                                    @endphp
                                    <a href="{{ route('courses.video', [$l, $v]) }}" @if($locked && !$active) data-help="upgrade" title="Video VIP: liên hệ Zalo để nâng cấp gói" @endif data-vstatus-id="{{ $v->id }}" data-status="{{ $v->status }}" @if($active) id="activeItem" @endif
                                       class="flex items-center gap-3 px-5 py-3 transition border-l-4 {{ $active ? 'bg-amber-50 border-amber-500' : 'border-transparent hover:bg-stone-50' }}">
                                        <span class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center {{ $active ? 'bg-amber-700 text-white' : 'bg-stone-100 text-stone-600' }}">
                                            @if($locked)
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                            @else
                                                <svg class="w-4 h-4 ml-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.84A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.27l9.34-5.89a1.5 1.5 0 000-2.54L6.3 2.84z"/></svg>
                                            @endif
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-sm truncate {{ $active ? 'font-semibold text-amber-800' : 'text-stone-700' }}">{{ $v->title }}</span>
                                            @if($v->status !== 'ready')<span data-vstatus-label="{{ $v->id }}" class="block text-sm text-stone-500">Đang xử lý…</span>@endif
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</div>


@if($canWatch && in_array($video->status, ['pending', 'processing']))
<script data-poller>
// Khi video đang xem xử lý xong: thay khung "đang xử lý" bằng trình phát mà không tải lại trang
(function () {
    const VIDEO_ID = {{ $video->id }};
    document.addEventListener('video-status', async (e) => {
        if (e.detail.id !== VIDEO_ID) return;
        if (e.detail.status !== 'ready') return location.reload(); // lỗi: tải lại để hiện thông báo lỗi
        try {
            const html = await (await fetch(location.href, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })).text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const shell = doc.getElementById('playerShell');
            if (!shell || !doc.getElementById('player')) return location.reload();

            // 1) Thay khung phát, danh sách bài và bảng phím tắt bằng bản mới
            const swap = (id) => { const a = document.getElementById(id), b = doc.getElementById(id); if (a && b) a.replaceWith(b); };
            swap('playerShell'); swap('playlist');
            const sc = doc.getElementById('shortcuts');
            if (sc && !document.getElementById('shortcuts')) document.getElementById('playerShell').parentElement.appendChild(sc);
            document.getElementById('playerShell').animate([{ opacity: 0 }, { opacity: 1 }], { duration: 400 });

            // 2) Nạp thư viện (hls.js, Plyr) rồi chạy đoạn khởi tạo trình phát, theo đúng thứ tự
            for (const old of doc.querySelectorAll('main script:not([data-poller])')) {
                await new Promise((resolve) => {
                    const s = document.createElement('script');
                    if (old.src) {
                        if (document.querySelector(`script[src="${old.src}"]`)) return resolve();
                        s.src = old.src; s.onload = resolve; s.onerror = resolve;
                    } else { s.textContent = old.textContent; }
                    document.body.appendChild(s);
                    if (!old.src) resolve();
                });
            }
        } catch (err) { location.reload(); }
    });
})();
</script>
@endif
@include('partials.video-status-poller')

@if($canWatch && $video->status === 'ready' && $video->hls_path)
<script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
<script src="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.polyfilled.js"></script>
<script>
(function () {
    const SRC = @js(route('stream', [$video, 'master.m3u8']));
    const THUMBS = @js($thumbs);
    const PROGRESS_URL = @js(auth()->check() ? route('videos.progress', $video) : null);
    const SERVER_RESUME = @js($resumeAt);
    const CSRF = @js(csrf_token());
    const VIDEO_ID = {{ $video->id }};
    const NEXT_URL = @js($next['url'] ?? null);
    const NEXT_TITLE = @js($next['title'] ?? null);
    const SPEEDS = [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2];

    const video = document.getElementById('player');
    // Chặn chuột phải / kéo thả trên khung video (chỉ là rào cản nhỏ, không phải chống tải thật sự)
    document.getElementById('stage').addEventListener('contextmenu', e => e.preventDefault());
    document.getElementById('stage').addEventListener('dragstart', e => e.preventDefault());
    const stage = document.getElementById('stage');
    const errorBox = document.getElementById('errorBox');
    const store = {
        get: (k, d) => { try { const v = localStorage.getItem('vp:' + k); return v === null ? d : JSON.parse(v); } catch (e) { return d; } },
        set: (k, v) => { try { localStorage.setItem('vp:' + k, JSON.stringify(v)); } catch (e) {} },
    };
    const fmtTime = s => { s = Math.floor(s); const m = Math.floor(s / 60); return m + ':' + String(s % 60).padStart(2, '0'); };

    let hls = null, player = null;

    const baseOptions = {
        controls: ['play-large', 'rewind', 'play', 'fast-forward', 'progress', 'current-time', 'duration', 'mute', 'volume', 'settings', 'pip', 'airplay', 'fullscreen'],
        settings: ['quality', 'speed'],
        seekTime: 10,
        speed: { selected: 1, options: SPEEDS },
        keyboard: { focused: true, global: true },
        tooltips: { controls: true, seek: false },
        
        storage: { enabled: false },
        clickToPlay: true,
        invertTime: false,
        i18n: {
            restart: 'Phát lại', rewind: 'Lùi {seektime}s', play: 'Phát', pause: 'Tạm dừng', fastForward: 'Tua {seektime}s',
            seek: 'Tua', seekLabel: '{currentTime} / {duration}', played: 'Đã phát', buffered: 'Đã tải', currentTime: 'Hiện tại',
            duration: 'Thời lượng', volume: 'Âm lượng', mute: 'Tắt tiếng', unmute: 'Bật tiếng', enterFullscreen: 'Toàn màn hình',
            exitFullscreen: 'Thoát toàn màn hình', settings: 'Cài đặt', speed: 'Tốc độ', normal: 'Chuẩn', quality: 'Chất lượng',
            pip: 'Thu nhỏ (PiP)', menuBack: 'Quay lại', disabled: 'Tắt', enabled: 'Bật',
            qualityLabel: { 0: 'Tự động' },
        },
    };

    // Chỉ ghi nhớ chất lượng khi người xem TỰ chọn trong menu (không lưu khi player tự khởi tạo)
    document.addEventListener('click', (e) => {
        const item = e.target.closest(".plyr__menu__container [data-plyr='quality']");
        if (item) store.set('quality2', parseInt(item.value, 10) || 0);
        const sp = e.target.closest(".plyr__menu__container [data-plyr='speed']");
        if (sp) store.set('speed2', parseFloat(sp.value) || 1);
    }, true); // capture: Plyr chặn sự kiện click nên phải bắt ở pha capture

    function showError(msg) {
        document.getElementById('errorMsg').textContent = msg || 'Vui lòng kiểm tra kết nối mạng.';
        errorBox.classList.remove('hidden'); errorBox.classList.add('flex');
    }
    function hideError() { errorBox.classList.add('hidden'); errorBox.classList.remove('flex'); }

    function initPlayer(extra) {
        player = new Plyr(video, Object.assign({}, baseOptions, extra || {}));
        window.player = player;

        player.on('ready', () => {
            buildVolume();
            buildSeekPreview();
            player.volume = store.get('volume', 1);
            player.muted = store.get('muted', false);
            player.speed = store.get('speed2', 1);
            buildUpNext();
        });
        player.on('volumechange', () => { store.set('volume', player.volume); store.set('muted', player.muted); });

        // Đồng bộ tiến độ lên tài khoản (chỉ khi đã đăng nhập): mỗi 15 giây, khi tạm dừng, khi xong
        let lastSync = 0;
        const sync = (completed = false) => {
            if (!PROGRESS_URL || !player.duration) return;
            lastSync = Date.now();
            fetch(PROGRESS_URL, { method: 'POST', keepalive: true, credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ position: player.currentTime, duration: player.duration, completed }) }).catch(() => {});
        };
        player.on('timeupdate', () => { if (!player.paused && Date.now() - lastSync > 15000 && player.currentTime > 3) sync(); });
        player.on('pause', () => { if (player.currentTime > 3 && !player.ended) sync(); });
        player.on('ended', () => sync(true));
        document.addEventListener('visibilitychange', () => { if (document.hidden && !player.paused) sync(); });

        // Ghi nhớ vị trí đang xem
        let lastSave = 0;
        player.on('timeupdate', () => {
            const now = Date.now();
            if (now - lastSave > 4000 && player.currentTime > 3) { lastSave = now; store.set('pos:' + VIDEO_ID, player.currentTime); }
        });
        player.on('pause', () => { if (player.currentTime > 3 && !player.ended) store.set('pos:' + VIDEO_ID, player.currentTime); });
        player.on('ended', () => { store.set('pos:' + VIDEO_ID, 0); startUpNext(); });

        // Phím < > đổi tốc độ, N sang bài tiếp
        document.addEventListener('keydown', (e) => {
            if (/input|textarea|select/i.test(e.target.tagName) || e.ctrlKey || e.metaKey || e.altKey) return;
            if (e.key === '>' || e.key === '<') {
                const i = SPEEDS.indexOf(player.speed);
                const n = SPEEDS[Math.min(SPEEDS.length - 1, Math.max(0, i + (e.key === '>' ? 1 : -1)))];
                player.speed = n; store.set('speed2', n); flash(n + 'x');
            } else if ((e.key === 'n' || e.key === 'N') && NEXT_URL) { location.href = NEXT_URL; }
        });

        // Thông báo tiếp tục xem
        const saved = SERVER_RESUME !== null ? SERVER_RESUME : store.get('pos:' + VIDEO_ID, 0);
        video.addEventListener('loadedmetadata', function once() {
            video.removeEventListener('loadedmetadata', once);
            if (saved > 5 && video.duration && saved < video.duration - 10) {
                video.currentTime = saved;
                resumeToast(saved);
            }
        });
        return player;
    }

    // ---- Ảnh xem trước khi rê thanh tua (sprite + VTT do server tạo) ----
    async function buildSeekPreview() {
        const bar = player.elements.container.querySelector('.plyr__progress');
        if (!bar) return;

        let cues = [];
        let sprite = null;
        if (THUMBS) {
            try {
                const text = await (await fetch(THUMBS, { credentials: 'same-origin' })).text();
                const base = THUMBS.substring(0, THUMBS.lastIndexOf('/') + 1);
                const toSec = t => { const [h, m, s] = t.split(':'); return (+h) * 3600 + (+m) * 60 + parseFloat(s); };
                const re = /(\d+:\d+:\d+\.\d+) --> (\d+:\d+:\d+\.\d+)\s*\n(\S+?)#xywh=(\d+),(\d+),(\d+),(\d+)/g;
                let m;
                while ((m = re.exec(text))) {
                    sprite = sprite || base + m[3];
                    cues.push({ start: toSec(m[1]), end: toSec(m[2]), x: +m[4], y: +m[5], w: +m[6], h: +m[7] });
                }
            } catch (e) { /* không có ảnh xem trước: chỉ hiện thời gian */ }
        }

        const box = document.createElement('div');
        box.className = 'seek-preview' + (cues.length ? '' : ' no-img');
        box.innerHTML = '<div class="sp-img"></div><span class="sp-time">0:00</span>';
        bar.appendChild(box);
        const img = box.querySelector('.sp-img'), label = box.querySelector('.sp-time');
        if (sprite) img.style.backgroundImage = `url('${sprite}')`;

        bar.addEventListener('mousemove', (e) => {
            const r = bar.getBoundingClientRect();
            const pct = Math.min(1, Math.max(0, (e.clientX - r.left) / r.width));
            const t = pct * (player.duration || 0);
            label.textContent = fmtTime(t);
            const cue = cues.find(c => t >= c.start && t < c.end) || cues[cues.length - 1];
            if (cue) img.style.backgroundPosition = `-${cue.x}px -${cue.y}px`;
            // Giữ hộp xem trước không tràn ra ngoài hai mép player
            const half = 84, cr = player.elements.container.getBoundingClientRect();
            const x = Math.min(Math.max(e.clientX, cr.left + half), cr.right - half) - r.left;
            box.style.left = x + 'px';
            box.style.display = 'block';
        });
        bar.addEventListener('mouseleave', () => { box.style.display = 'none'; });
    }

    // ---- Thanh âm lượng dọc (popup phía trên nút loa) ----
    function buildVolume() {
        const mute = player.elements.container.querySelector("[data-plyr='mute']");
        if (!mute || mute.parentElement.classList.contains('vol-wrap')) return;

        const wrap = document.createElement('div');
        wrap.className = 'vol-wrap';
        mute.parentNode.insertBefore(wrap, mute);
        wrap.appendChild(mute);

        const pop = document.createElement('div');
        pop.className = 'vol-pop';
        pop.innerHTML = '<div class="vol-panel"><div class="vol-track"><div class="vol-fill"></div><div class="vol-thumb"></div></div></div>';
        wrap.appendChild(pop);

        const track = pop.querySelector('.vol-track'), fill = pop.querySelector('.vol-fill'), thumb = pop.querySelector('.vol-thumb');
        const render = () => {
            const v = player.muted ? 0 : player.volume;
            fill.style.height = thumb.style.bottom = (v * 100) + '%';
        };
        const setFrom = (clientY) => {
            const r = track.getBoundingClientRect();
            const v = Math.min(1, Math.max(0, 1 - (clientY - r.top) / r.height));
            player.volume = v;
            player.muted = v === 0;
        };
        track.addEventListener('pointerdown', (e) => {
            e.preventDefault(); e.stopPropagation();
            track.setPointerCapture(e.pointerId);
            pop.classList.add('dragging');
            setFrom(e.clientY);
        });
        track.addEventListener('pointermove', (e) => { if (track.hasPointerCapture(e.pointerId)) setFrom(e.clientY); });
        const end = (e) => { pop.classList.remove('dragging'); if (track.hasPointerCapture(e.pointerId)) track.releasePointerCapture(e.pointerId); };
        track.addEventListener('pointerup', end);
        track.addEventListener('pointercancel', end);
        pop.addEventListener('click', (e) => e.stopPropagation());
        // Lăn chuột trên nút loa để chỉnh âm lượng
        wrap.addEventListener('wheel', (e) => {
            e.preventDefault();
            player.muted = false;
            player.volume = Math.min(1, Math.max(0, player.volume + (e.deltaY < 0 ? 0.05 : -0.05)));
        }, { passive: false });

        player.on('volumechange', render);
        render();
    }

    // ---- Thông báo nổi trong player ----
    function overlay(html, cls) {
        const box = document.createElement('div');
        box.className = 'absolute z-30 ' + cls;
        box.innerHTML = html;
        (player && player.elements.container ? player.elements.container : stage).appendChild(box);
        return box;
    }
    function flash(text) {
        const el = overlay(`<span class="bg-black/70 text-white text-2xl font-bold px-5 py-2 rounded-xl">${text}</span>`, 'top-6 left-1/2 -translate-x-1/2 pointer-events-none');
        setTimeout(() => el.remove(), 900);
    }
    function resumeToast(t) {
        const el = overlay(`<div class="bg-black/80 backdrop-blur text-white text-sm rounded-xl px-4 py-3 flex items-center gap-3">
            <span>Tiếp tục từ <b>${fmtTime(t)}</b></span>
            <button class="text-amber-400 font-semibold hover:text-amber-300">Xem từ đầu</button></div>`, 'left-4 bottom-20');
        el.querySelector('button').onclick = () => { video.currentTime = 0; el.remove(); };
        setTimeout(() => el.remove(), 7000);
    }

    // ---- Tự chuyển bài tiếp theo ----
    let upNext = null, upTimer = null;
    function buildUpNext() {
        if (!NEXT_URL) return;
        upNext = overlay(`<div class="absolute inset-0 bg-black/80 flex flex-col items-center justify-center text-center px-6">
            <p class="text-neutral-400 text-sm mb-1">Bài tiếp theo</p>
            <p class="text-white text-xl font-bold mb-5 max-w-md">${NEXT_TITLE.replace(/</g, '&lt;')}</p>
            <div class="flex gap-3">
                <button data-a="go" class="bg-amber-700 hover:bg-amber-500 text-white font-semibold px-6 py-2.5 rounded-full">Phát ngay (<span data-c>5</span>)</button>
                <button data-a="cancel" class="bg-neutral-700 hover:bg-neutral-600 text-white font-semibold px-6 py-2.5 rounded-full">Huỷ</button>
            </div></div>`, 'inset-0 hidden');
        upNext.querySelector('[data-a=go]').onclick = () => { location.href = NEXT_URL; };
        upNext.querySelector('[data-a=cancel]').onclick = () => { clearInterval(upTimer); upNext.classList.add('hidden'); };
    }
    function startUpNext() {
        if (!upNext) return;
        let n = 5;
        upNext.querySelector('[data-c]').textContent = n;
        upNext.classList.remove('hidden');
        clearInterval(upTimer);
        upTimer = setInterval(() => {
            n--; upNext.querySelector('[data-c]').textContent = n;
            if (n <= 0) { clearInterval(upTimer); location.href = NEXT_URL; }
        }, 1000);
    }

    // ---- Khởi tạo nguồn phát ----
    function start() {
        hideError();
        if (player) { player.destroy(); player = null; }
        if (hls) { hls.destroy(); hls = null; }

        if (window.Hls && Hls.isSupported()) {
            // abrEwmaDefaultEstimate cao: vào thẳng chất lượng cao nếu mạng tốt, hạ dần nếu mạng yếu
            hls = new Hls({ maxBufferLength: 30, capLevelToPlayerSize: false });
            hls.loadSource(SRC);
            hls.attachMedia(video);

            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                // Nhãn chất lượng theo cạnh ngắn (video dọc: 720p = rộng 720)
                const label = l => Math.min(l.width || 0, l.height || 0);
                const labels = [...new Set(hls.levels.map(label))].filter(Boolean).sort((a, b) => b - a);
                const pref = store.get('quality2', 0);

                initPlayer({
                    quality: {
                        default: labels.includes(pref) ? pref : 0,
                        options: [0, ...labels],
                        forced: true,
                        onChange: (q) => {
                            hls.currentLevel = q === 0 ? -1 : hls.levels.findIndex(l => label(l) === q);
                        },
                    },
                });
                if (labels.includes(pref)) hls.currentLevel = hls.levels.findIndex(l => label(l) === pref);
                if (poster = video.dataset.poster) player.poster = poster;
            });

            // Hiện mức đang phát ở mục "Tự động"
            hls.on(Hls.Events.LEVEL_SWITCHED, (_, d) => {
                const l = hls.levels[d.level];
                const span = document.querySelector(".plyr__menu__container [data-plyr='quality'][value='0'] span");
                if (span && l && hls.autoLevelEnabled) span.textContent = 'Tự động (' + Math.min(l.width, l.height) + 'p)';
            });

            hls.on(Hls.Events.ERROR, (_, data) => {
                console.warn('[hls]', data.type, data.details, data.fatal ? 'FATAL' : '');
                if (!data.fatal) return;
                if (data.type === Hls.ErrorTypes.NETWORK_ERROR) { hls.startLoad(); showError('Mất kết nối mạng. Đang thử tải lại…'); setTimeout(hideError, 2500); }
                else if (data.type === Hls.ErrorTypes.MEDIA_ERROR) hls.recoverMediaError();
                else { hls.destroy(); showError('Video gặp lỗi khi phát.'); }
            });
        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            // Safari / iOS phát HLS gốc (không có menu chọn chất lượng)
            video.src = SRC;
            initPlayer();
            if (video.dataset.poster) player.poster = video.dataset.poster;
        } else {
            showError('Trình duyệt của bạn không hỗ trợ phát video này.');
        }
    }

    document.getElementById('retryBtn').onclick = start;
    start();

    // Cuộn danh sách tới bài đang xem
    // (chỉ cuộn bên trong danh sách, không cuộn cả trang)
    const active = document.getElementById('activeItem');
    const list = document.getElementById('playlist');
    if (active && list) list.scrollTop = active.offsetTop - list.clientHeight / 2 + active.clientHeight / 2;
})();
</script>
@endif
@endsection
