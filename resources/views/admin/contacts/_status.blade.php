{{-- Nhãn trạng thái liên hệ (tiếng Việt + icon). Cần: $contact --}}
@php
    $st = $contact->status;
    $style = match ($st) {
        'new' => 'bg-rose-100 text-rose-700 ring-rose-200',
        'read' => 'bg-sky-100 text-sky-700 ring-sky-200',
        'handled' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        default => 'bg-slate-100 text-slate-600 ring-slate-200',
    };
@endphp
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ring-1 whitespace-nowrap {{ $style }}">
    @if($st === 'new')
        {{-- phong bì đóng + chấm --}}
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    @elseif($st === 'read')
        {{-- phong bì mở --}}
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.66l7-4.67a2 2 0 012.22 0l7 4.67A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5a2 2 0 002.5 0L19 10"/></svg>
    @elseif($st === 'handled')
        {{-- dấu tick trong vòng tròn --}}
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    @else
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.23 9a4 4 0 117.54 1.5c-.6 1.1-1.77 1.5-1.77 3M12 17h.01"/></svg>
    @endif
    {{ $contact->statusLabel() }}
</span>
