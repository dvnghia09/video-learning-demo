{{-- Ảnh thu nhỏ video. Cần: $video. Tuỳ chọn: $class (kích thước), $eager (tải ngay, cho các ảnh đầu danh sách). Ảnh lỗi/thiếu -> hiện khung chờ. --}}
@php $url = $video->posterUrl(); @endphp
<div data-thumb="{{ $video->id }}" class="relative aspect-video shrink-0 rounded-lg overflow-hidden bg-slate-800 ring-1 ring-slate-200 {{ $class ?? 'w-28 sm:w-36' }}">
    <span data-thumb-ph class="absolute inset-0 flex flex-col items-center justify-center gap-1 text-slate-400">
        @if(in_array($video->status, ['pending', 'processing']))
            <svg class="w-5 h-5 animate-spin text-amber-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span class="text-[11px] font-medium">Đang xử lý</span>
        @elseif($video->status === 'failed')
            <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            <span class="text-[11px] font-medium text-rose-300">Lỗi</span>
        @else
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        @endif
    </span>
    @if($url)
        <img src="{{ $url }}" alt="{{ $video->title }}" loading="{{ ($eager ?? false) ? 'eager' : 'lazy' }}" decoding="async" class="absolute inset-0 w-full h-full object-contain" onerror="this.remove()">
    @endif
</div>
