@extends('layouts.admin')
@section('title', 'Tổng quan')
@section('header', 'Tổng quan')

@section('content')
@php
    $cards = [
        ['Thành viên', $stats['users'], 'from-sky-500 to-blue-600', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', route('admin.users.index'), $stats['vip'].' thành viên VIP'],
        ['Video', $stats['videos'], 'from-emerald-500 to-teal-600', 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z', route('admin.videos.index'), 'trong '.$stats['lessons'].' bài học'],
        ['Bài viết', $stats['blogs'], 'from-amber-500 to-orange-600', 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2zM9 8h4m-4 4h6m-6 4h6', route('admin.blogs.index'), 'đang đăng trên website'],
        ['Liên hệ chưa đọc', $stats['newContacts'], 'from-rose-500 to-pink-600', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', route('admin.contacts.index', ['status' => 'new']), 'bấm để xem ngay'],
    ];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 mb-6">
    @foreach($cards as [$label, $value, $grad, $icon, $href, $sub])
        <a href="{{ $href }}" class="group relative overflow-hidden rounded-2xl p-5 sm:p-6 text-white shadow-lg bg-gradient-to-br {{ $grad }} hover:-translate-y-1 hover:shadow-xl transition">
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 opacity-15 group-hover:scale-110 transition duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}"/></svg>
            <p class="text-sm font-semibold text-white/90">{{ $label }}</p>
            <p class="mt-1 text-4xl font-extrabold tracking-tight" data-count="{{ $value }}">{{ $value }}</p>
            <p class="mt-1 text-sm text-white/80">{{ $sub }}</p>
        </a>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6 mb-6">
    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="font-bold text-slate-900">Hoạt động 14 ngày gần đây</h3>
                <p class="text-sm text-slate-500">Thành viên đăng ký mới và tin nhắn liên hệ mỗi ngày</p>
            </div>
        </div>
        <div class="relative h-72"><canvas id="chActivity" aria-label="Biểu đồ hoạt động 14 ngày" role="img"></canvas></div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
        <h3 class="font-bold text-slate-900">Thành viên</h3>
        <p class="text-sm text-slate-500 mb-4">VIP và thường</p>
        <div class="relative h-56"><canvas id="chMembers" aria-label="Biểu đồ thành viên" role="img"></canvas></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
        <h3 class="font-bold text-slate-900">Trạng thái video</h3>
        <p class="text-sm text-slate-500 mb-4">Xử lý xong hay còn đang chờ</p>
        <div class="relative h-56"><canvas id="chStatus" aria-label="Biểu đồ trạng thái video" role="img"></canvas></div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
        <h3 class="font-bold text-slate-900">Miễn phí và cần nâng cấp</h3>
        <p class="text-sm text-slate-500 mb-4">Tỷ lệ video theo gói</p>
        <div class="relative h-56"><canvas id="chAccess" aria-label="Biểu đồ video theo gói" role="img"></canvas></div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
        <h3 class="font-bold text-slate-900">Video được xem nhiều</h3>
        <p class="text-sm text-slate-500 mb-4">Số người đã bắt đầu xem</p>
        @if(count($charts['top']['data']))
            <div class="relative h-56"><canvas id="chTop" aria-label="Biểu đồ video xem nhiều" role="img"></canvas></div>
        @else
            <div class="h-56 flex items-center justify-center text-center text-slate-500 text-sm">Chưa có ai xem video.<br>Số liệu sẽ hiện khi có người học.</div>
        @endif
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 sm:px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <h3 class="font-bold text-slate-900">Liên hệ mới nhất</h3>
        <a href="{{ route('admin.contacts.index') }}" class="text-sm font-semibold text-amber-700 hover:text-amber-800">Xem tất cả &rarr;</a>
    </div>
    @forelse($recentContacts as $c)
        <a href="{{ route('admin.contacts.show', $c) }}" class="flex items-center gap-4 px-5 sm:px-6 py-3 hover:bg-slate-50 transition {{ $loop->last ? '' : 'border-b border-slate-100' }}">
            <span class="w-10 h-10 shrink-0 rounded-full bg-gradient-to-br from-amber-400 to-rose-500 text-white font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($c->name, 0, 1)) }}</span>
            <span class="min-w-0 flex-1">
                <span class="block font-semibold text-slate-900 truncate">{{ $c->name }} <span class="font-normal text-slate-500">· {{ $c->phone }}</span></span>
                <span class="block text-sm text-slate-500 truncate">{{ $c->message }}</span>
            </span>
            <span class="shrink-0 text-xs text-slate-400">{{ $c->created_at->diffForHumans() }}</span>
        </a>
    @empty
        <p class="px-6 py-10 text-center text-slate-500">Chưa có liên hệ nào.</p>
    @endforelse
</div>

<script src="{{ asset('vendor/chart/chart.umd.js') }}"></script>
<script>
    (function () {
        const d = @js($charts);
        Chart.defaults.font.family = 'ui-sans-serif, system-ui, sans-serif';
        Chart.defaults.color = '#64748b';
        const legend = { position: 'bottom', labels: { usePointStyle: true, padding: 16 } };
        const doughnut = (id, data, colors) => new Chart(document.getElementById(id), {
            type: 'doughnut',
            data: { labels: data.labels, datasets: [{ data: data.data, backgroundColor: colors, borderWidth: 3, borderColor: '#fff', hoverOffset: 8 }] },
            options: { maintainAspectRatio: false, cutout: '66%', plugins: { legend } },
        });

        const ctx = document.getElementById('chActivity').getContext('2d');
        const grad = (c1, c2) => { const g = ctx.createLinearGradient(0, 0, 0, 280); g.addColorStop(0, c1); g.addColorStop(1, c2); return g; };
        new Chart(ctx, {
            type: 'line',
            data: { labels: d.labels, datasets: [
                { label: 'Thành viên mới', data: d.users, borderColor: '#0ea5e9', backgroundColor: grad('rgba(14,165,233,.35)', 'rgba(14,165,233,0)'), fill: true, tension: .4, pointRadius: 3, pointHoverRadius: 6, borderWidth: 3 },
                { label: 'Liên hệ', data: d.contacts, borderColor: '#f43f5e', backgroundColor: grad('rgba(244,63,94,.3)', 'rgba(244,63,94,0)'), fill: true, tension: .4, pointRadius: 3, pointHoverRadius: 6, borderWidth: 3 },
            ] },
            options: { maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                plugins: { legend: { ...legend, position: 'top', align: 'end' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } },
        });

        doughnut('chMembers', d.members, ['#f59e0b', '#cbd5e1']);
        doughnut('chStatus', d.videoStatus, ['#10b981', '#38bdf8', '#fbbf24', '#f43f5e']);
        doughnut('chAccess', d.access, ['#0ea5e9', '#8b5cf6']);

        const top = document.getElementById('chTop');
        if (top) new Chart(top, {
            type: 'bar',
            data: { labels: d.top.labels.map(t => t.length > 22 ? t.slice(0, 21) + '…' : t), datasets: [{ data: d.top.data, borderRadius: 8, backgroundColor: ['#f59e0b', '#fb923c', '#f43f5e', '#a855f7', '#6366f1'] }] },
            options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, y: { grid: { display: false } } } },
        });

        // Số trên thẻ đếm tăng dần cho sinh động
        document.querySelectorAll('[data-count]').forEach(el => {
            const end = Number(el.dataset.count); if (!end || matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            const t0 = performance.now();
            (function tick(now) { const p = Math.min(1, (now - t0) / 900); el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(tick); })(t0);
        });
    })();
</script>
@endsection
