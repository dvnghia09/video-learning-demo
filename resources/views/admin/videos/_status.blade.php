@php
    $map = [
        'pending'    => ['Đang chờ xử lý', 'bg-slate-100 text-slate-600'],
        'processing' => ['Đang xử lý…', 'bg-amber-100 text-amber-700'],
        'ready'      => ['Sẵn sàng', 'bg-emerald-100 text-emerald-700'],
        'failed'     => ['Lỗi xử lý', 'bg-rose-100 text-rose-700'],
    ];
    [$label, $cls] = $map[$video->status] ?? [$video->status, 'bg-slate-100 text-slate-600'];
@endphp
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $cls }}">
    @if($video->status === 'processing')<span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>@endif
    {{ $label }}
</span>
