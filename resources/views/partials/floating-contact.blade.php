{{-- Nút nổi Gọi điện / Zalo / Messenger ở góc màn hình. Chỉ hiện nút nào đã được cài đặt trong Admin. --}}
@php
    $__phone = $site->defaultPhone();
    $__zalo = $site->zaloUrl();
    $__mess = $site->messengerUrl();
@endphp
@if($__phone || $__zalo || $__mess)
<style>
    .fc-wrap { position: fixed; right: 16px; bottom: 20px; z-index: 9990; display: flex; flex-direction: column; gap: 14px; }
    .fc-btn { position: relative; display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 9999px; color: #fff;
              box-shadow: 0 6px 16px rgba(0,0,0,.25); transition: transform .2s; text-decoration: none; }
    .fc-btn:hover { transform: scale(1.12); }
    .fc-btn svg { width: 28px; height: 28px; animation: fc-shake 2.4s ease-in-out infinite; transform-origin: 50% 50%; }
    .fc-btn::before { content: ''; position: absolute; inset: 0; border-radius: 9999px; background: inherit; opacity: .5; z-index: -1; animation: fc-pulse 2s ease-out infinite; }
    .fc-call { background: #16a34a; } .fc-zalo { background: #0068ff; } .fc-mess { background: linear-gradient(135deg,#00b2ff,#006aff 55%,#a334fa); }
    .fc-zalo span { font: 800 15px/1 Roboto, Arial, sans-serif; letter-spacing: -.3px; animation: fc-shake 2.4s ease-in-out infinite .4s; }
    .fc-call svg { animation-delay: 0s; } .fc-mess svg { animation-delay: .8s; }
    .fc-tip { position: absolute; right: 62px; top: 50%; transform: translateY(-50%); white-space: nowrap; background: #111827; color: #fff; font-size: 13px; font-weight: 600;
              padding: 6px 12px; border-radius: 8px; opacity: 0; pointer-events: none; transition: opacity .15s; }
    .fc-btn:hover .fc-tip { opacity: 1; }
    @keyframes fc-shake { 0%, 55%, 100% { transform: rotate(0) scale(1); } 60% { transform: rotate(-18deg) scale(1.1); } 68% { transform: rotate(18deg) scale(1.1); }
                          76% { transform: rotate(-14deg) scale(1.1); } 84% { transform: rotate(14deg) scale(1.1); } 92% { transform: rotate(-6deg); } }
    @keyframes fc-pulse { 0% { transform: scale(1); opacity: .5; } 100% { transform: scale(1.7); opacity: 0; } }
    @media (prefers-reduced-motion: reduce) { .fc-btn svg, .fc-zalo span, .fc-btn::before { animation: none; } }
    @media (max-width: 640px) { .fc-wrap { right: 8px; bottom: 12px; gap: 10px; } .fc-btn { width: 44px; height: 44px; } .fc-btn svg { width: 24px; height: 24px; } }
</style>
<div class="fc-wrap">
    @if($__mess)
        <a href="{{ $__mess }}" target="_blank" rel="noopener" class="fc-btn fc-mess" aria-label="Nhắn tin Messenger">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.36 2 2 6.13 2 11.7c0 2.91 1.19 5.42 3.14 7.16.16.14.26.35.27.57l.05 1.78c.02.57.6.94 1.12.71l1.98-.87c.17-.07.36-.09.53-.04.91.25 1.87.38 2.91.38 5.64 0 10-4.13 10-9.69S17.64 2 12 2zm6 7.46l-2.94 4.67c-.47.74-1.47.93-2.17.4l-2.34-1.75a.6.6 0 00-.72 0l-3.16 2.4c-.42.32-.97-.19-.69-.64l2.94-4.67c.47-.74 1.47-.93 2.17-.4l2.34 1.75c.21.16.5.16.72 0l3.16-2.4c.42-.32.97.19.69.64z"/></svg>
            <span class="fc-tip">Nhắn tin Messenger</span>
        </a>
    @endif
    @if($__zalo)
        <a href="{{ $__zalo }}" target="_blank" rel="noopener" class="fc-btn fc-zalo" aria-label="Chat Zalo">
            <span>Zalo</span>
            <span class="fc-tip">Chat qua Zalo</span>
        </a>
    @endif
    @if($__phone)
        <a href="{{ \App\Support\SiteSettings::telHref($__phone) }}" class="fc-btn fc-call" aria-label="Gọi điện {{ $__phone }}">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1C10.61 21 3 13.39 3 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/></svg>
            <span class="fc-tip">Gọi ngay {{ $__phone }}</span>
        </a>
    @endif
</div>
@endif
