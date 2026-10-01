@extends('layouts.admin')
@section('title', 'Quản lý bài học')
@section('header', 'Bài học')

@section('content')
@php $hasPending = collect($items->items())->flatMap(fn ($l) => $l->videos)->contains(fn ($v) => in_array($v->status, ['pending', 'processing'])); @endphp

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h3 class="text-xl font-bold text-slate-900">Danh sách bài học</h3>
        <p class="text-sm text-slate-500">Bấm vào mũi tên để xem video của bài. Giữ biểu tượng ⋮⋮ rồi kéo thả để đổi vị trí bài học hoặc video.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('toggle-all', {detail: true}))" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">Mở tất cả</button>
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('toggle-all', {detail: false}))" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">Thu gọn</button>
        <a href="{{ route('admin.lessons.create') }}" class="inline-flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow transition">
            <span class="text-lg leading-none">+</span> Thêm bài học
        </a>
    </div>
</div>

@forelse($items as $item)
    @if($loop->first)<div class="space-y-3" id="lessonList">@endif
    @php
        $confirmMsg = "Xoá bài học “{$item->title}”?"
            .($item->videos_count ? "\nCả {$item->videos_count} video trong bài này cũng sẽ bị xoá." : '')
            ."\nHành động này không thể hoàn tác.";
    @endphp
    <div data-id="{{ $item->id }}" x-data="{ open: false }" @toggle-all.window="open = $event.detail" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        {{-- ===== Hàng bài học ===== --}}
        <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <span class="drag-handle hidden sm:flex shrink-0 w-9 h-11 -mr-2 items-center justify-center rounded-lg text-slate-400 hover:text-amber-700 hover:bg-amber-50 cursor-grab active:cursor-grabbing touch-none" title="Kéo để đổi vị trí bài học" aria-label="Kéo để đổi vị trí"><svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path d="M7 4a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm6 0a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM7 8.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm6 0a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM7 13a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm6 0a1.5 1.5 0 110 3 1.5 1.5 0 010-3z"/></svg></span>
            <button type="button" @click="open = !open" :aria-expanded="open" aria-label="Mở danh sách video"
                    class="flex items-center gap-4 flex-1 min-w-0 text-left rounded-xl hover:bg-slate-50 -m-2 p-2 transition">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-amber-100 text-amber-700 font-bold flex items-center justify-center">{{ $item->order }}</span>
                <span class="flex-1 min-w-0">
                    <span class="block font-bold text-slate-900 truncate">{{ $item->title }}</span>
                    <span class="block text-sm text-slate-500 truncate">{{ $item->description ?: 'Chưa có mô tả' }}</span>
                    <span class="mt-1 inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        {{ $item->videos_count }} video
                    </span>
                </span>
                <span class="shrink-0 w-9 h-9 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center transition" :class="open && 'bg-amber-100 text-amber-700'">
                    <svg class="w-5 h-5 transition-transform duration-300" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </span>
            </button>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.videos.create', ['lesson_id' => $item->id]) }}" class="px-3 py-2 rounded-lg text-sm font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 transition">+ Video</a>
                <a href="{{ route('admin.lessons.edit', $item) }}" class="px-3 py-2 rounded-lg text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition">Sửa</a>
                <form action="{{ route('admin.lessons.destroy', $item) }}" method="POST" data-confirm="{{ $confirmMsg }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-2 rounded-lg text-sm font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 transition">Xoá</button>
                </form>
            </div>
        </div>

        {{-- ===== Danh sách video của bài (mở/đóng) ===== --}}
        <div x-show="open" x-collapse x-cloak class="border-t border-slate-200 bg-slate-50/70">
            <div class="video-list">
            @forelse($item->videos as $v)
                <div data-id="{{ $v->id }}" class="bg-slate-50 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 px-4 sm:px-5 py-3 border-b border-slate-200/70">
                    <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
                        <span class="drag-handle hidden sm:flex w-7 h-9 shrink-0 items-center justify-center rounded-lg text-slate-400 hover:text-amber-700 hover:bg-amber-100 cursor-grab active:cursor-grabbing touch-none" title="Kéo để đổi vị trí video" aria-label="Kéo để đổi vị trí"><svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path d="M7 4a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm6 0a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM7 8.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm6 0a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM7 13a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm6 0a1.5 1.5 0 110 3 1.5 1.5 0 010-3z"/></svg></span>
                        @include('admin.videos._thumb', ['video' => $v, 'class' => 'w-32 sm:w-40'])
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-slate-900 truncate">{{ $v->title }}</p>
                            <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                <span data-status="{{ $v->id }}" data-current="{{ $v->status }}">@include('admin.videos._status', ['video' => $v])</span>
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $v->is_free ? 'bg-sky-100 text-sky-700' : 'bg-violet-100 text-violet-700' }}">{{ $v->is_free ? 'Miễn phí' : 'Cần nâng cấp gói' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        <a data-watch="{{ $v->id }}" href="{{ $v->status === 'ready' ? route('courses.video', [$item, $v]) : '#' }}" target="_blank"
                           class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-100 transition {{ $v->status === 'ready' ? '' : 'hidden' }}">Xem</a>
                        <a href="{{ route('admin.videos.edit', $v) }}" class="px-3 py-2 rounded-lg text-sm font-semibold text-indigo-700 bg-white border border-indigo-100 hover:bg-indigo-50 transition">Sửa</a>
                        <form action="{{ route('admin.videos.destroy', $v) }}" method="POST" data-confirm="Xoá video “{{ $v->title }}”? File video cũng sẽ bị xoá khỏi máy chủ và không thể hoàn tác.">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-2 rounded-lg text-sm font-semibold text-rose-700 bg-white border border-rose-100 hover:bg-rose-50 transition">Xoá</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-5 py-8 text-center">
                    <p class="text-slate-500 mb-3">Bài học này chưa có video nào.</p>
                    <a href="{{ route('admin.videos.create', ['lesson_id' => $item->id]) }}" class="inline-block px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold transition">Tải video đầu tiên</a>
                </div>
            @endforelse
            </div>
            @if($item->videos->isNotEmpty())
                <div class="px-5 py-3 border-t border-slate-200/70 text-right">
                    <a href="{{ route('admin.videos.create', ['lesson_id' => $item->id]) }}" class="text-sm font-semibold text-amber-700 hover:text-amber-800">+ Thêm video vào bài này</a>
                </div>
            @endif
        </div>
    </div>
    @if($loop->last)</div>@endif
@empty
    <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
        <p class="text-slate-600 mb-4">Chưa có bài học nào.</p>
        <a href="{{ route('admin.lessons.create') }}" class="inline-block bg-amber-600 hover:bg-amber-700 text-white font-semibold px-5 py-2.5 rounded-xl">Tạo bài học đầu tiên</a>
    </div>
@endforelse

<div class="mt-6">{{ $items->links() }}</div>

<script src="{{ asset('vendor/sortable/Sortable.min.js') }}"></script>
<script src="{{ asset('vendor/axios/axios.min.js') }}"></script>
<script>
    // Kéo thả để sắp xếp: lưu ngay bằng AJAX, không tải lại trang. Lỗi thì trả về vị trí cũ.
    (function () {
        const url = { lessons: @js(route('admin.lessons.reorder')), videos: @js(route('admin.videos.reorder')) };
        const toast = (m, t) => window.Alpine && Alpine.store('ui').toast(m, t);
        function make(el, kind) {
            Sortable.create(el, {
                handle: '.drag-handle', animation: 180, ghostClass: 'opacity-40', draggable: '[data-id]',
                onEnd(evt) {
                    if (evt.oldIndex === evt.newIndex) return;
                    const ids = [...el.querySelectorAll(':scope > [data-id]')].map(n => Number(n.dataset.id));
                    axios.post(url[kind], { ids }, { headers: { Accept: 'application/json' } })
                        .then(() => toast(kind === 'lessons' ? 'Đã lưu vị trí bài học.' : 'Đã lưu vị trí video.', 'success'))
                        .catch(() => {
                            const ref = el.children[evt.oldIndex] || null;
                            el.insertBefore(evt.item, evt.oldIndex > evt.newIndex ? ref : (ref ? ref.nextSibling : null));
                            toast('Không lưu được vị trí, vui lòng thử lại.', 'error');
                        });
                },
            });
        }
        const list = document.getElementById('lessonList');
        if (list) make(list, 'lessons');
        document.querySelectorAll('.video-list').forEach(el => make(el, 'videos'));
    })();
</script>

@if($hasPending)
<script>
    // Có video đang xử lý: tự cập nhật nhãn trạng thái và ảnh thu nhỏ khi xong, không cần tải lại trang
    (function () {
        let pending = [...document.querySelectorAll('[data-status][data-current]')]
            .filter(el => ['pending', 'processing'].includes(el.dataset.current)).map(el => Number(el.dataset.status));
        const url = @js(route('admin.videos.status'));
        let delay = 4000;

        function setThumb(id, src) {
            const box = document.querySelector(`[data-thumb="${id}"]`);
            if (!box || box.querySelector('img')) return;
            const img = new Image();
            img.className = 'absolute inset-0 w-full h-full object-contain';
            img.alt = ''; img.onerror = () => img.remove(); img.src = src;
            box.appendChild(img);
        }
        async function poll() {
            try {
                const { data } = await axios.get(url, { params: { ids: pending.join(',') }, headers: { Accept: 'application/json' } });
                pending = pending.filter(id => {
                    const v = data[id];
                    if (!v) return false;
                    const badge = document.querySelector(`[data-status="${id}"]`);
                    if (badge) badge.innerHTML = v.html;
                    if (v.poster) setThumb(id, v.poster);
                    if (v.watch_url) {
                        const a = document.querySelector(`[data-watch="${id}"]`);
                        if (a) { a.href = v.watch_url; a.classList.remove('hidden'); }
                    }
                    return v.status === 'pending' || v.status === 'processing';
                });
                delay = 4000;
            } catch (e) { delay = Math.min(delay * 2, 30000); }
            if (pending.length) setTimeout(poll, delay);
        }
        if (pending.length) setTimeout(poll, delay);
    })();
</script>
@endif
@endsection
