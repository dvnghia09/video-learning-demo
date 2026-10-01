@extends('layouts.admin')
@section('title', 'Quản lý video')
@section('header', 'Video')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h3 class="text-xl font-bold text-slate-900">Danh sách video</h3>
        <p class="text-sm text-slate-500">Video mới tải lên sẽ được xử lý tự động trong nền, bạn có thể rời khỏi trang.</p>
    </div>
    <a href="{{ route('admin.videos.create') }}" class="inline-flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow transition">
        <span class="text-lg leading-none">+</span> Tải video lên
    </a>
</div>

@forelse($items as $item)
    @if($loop->first)<div class="space-y-3">@endif
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
        @include('admin.videos._thumb', ['video' => $item, 'class' => 'w-full sm:w-44', 'eager' => $loop->index < 8])
        <div class="flex-1 min-w-0">
            <h4 class="font-bold text-slate-900 truncate">{{ $item->title }}</h4>
            <p class="text-sm text-slate-500 truncate">{{ $item->lesson?->title ?? 'Chưa gắn bài học' }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span data-status="{{ $item->id }}">@include('admin.videos._status', ['video' => $item])</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $item->is_free ? 'bg-sky-100 text-sky-700' : 'bg-violet-100 text-violet-700' }}">
                    {{ $item->is_free ? 'Miễn phí' : 'Cần nâng cấp gói' }}
                </span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a data-watch="{{ $item->id }}" href="{{ $item->status === 'ready' ? route('courses.video', [$item->lesson, $item]) : '#' }}" target="_blank"
               class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition {{ $item->status === 'ready' ? '' : 'hidden' }}">Xem</a>
            <a href="{{ route('admin.videos.edit', $item) }}" class="px-3 py-2 rounded-lg text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition">Sửa</a>
            <form action="{{ route('admin.videos.destroy', $item) }}" method="POST"
                  data-confirm="Xoá video “{{ $item->title }}”? File video cũng sẽ bị xoá khỏi máy chủ và không thể hoàn tác.">
                @csrf @method('DELETE')
                <button type="submit" class="px-3 py-2 rounded-lg text-sm font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 transition">Xoá</button>
            </form>
        </div>
    </div>
    @if($loop->last)</div>@endif
@empty
    <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
        <p class="text-slate-600 mb-4">Chưa có video nào.</p>
        <a href="{{ route('admin.videos.create') }}" class="inline-block bg-amber-600 hover:bg-amber-700 text-white font-semibold px-5 py-2.5 rounded-xl">Tải video đầu tiên</a>
    </div>
@endforelse

<div class="mt-6">{{ $items->links() }}</div>

@if($pendingIds->isNotEmpty())
<script src="{{ asset('vendor/axios/axios.min.js') }}"></script>
<script>
    // Có video đang xử lý: hỏi trạng thái bằng AJAX, chỉ cập nhật nhãn, không tải lại trang
    (function () {
        let pending = @js($pendingIds);
        const url = @js(route('admin.videos.status'));
        let delay = 4000, timer;

        // Xử lý xong: đặt ảnh thu nhỏ ngay, không cần tải lại trang
        function setThumb(id, url) {
            const box = document.querySelector(`[data-thumb="${id}"]`);
            if (!box || box.querySelector('img')) return;
            const img = new Image();
            img.className = 'absolute inset-0 w-full h-full object-contain';
            img.alt = ''; img.onerror = () => img.remove(); img.src = url;
            box.appendChild(img);
        }

        async function poll() {
            try {
                const { data } = await axios.get(url, { params: { ids: pending.join(',') }, headers: { 'Accept': 'application/json' } });
                pending = pending.filter(id => {
                    const v = data[id];
                    if (!v) return false;
                    const badge = document.querySelector(`[data-status="${id}"]`);
                    if (badge) badge.innerHTML = v.html;
                    if (v.poster) setThumb(id, v.poster);
                    if (v.watch_url) {
                        const a = document.querySelector(`[data-watch="${id}"]`);
                        if (a) { a.href = v.watch_url; a.classList.remove('hidden'); }
                        Alpine.store('ui').toast('Video đã xử lý xong và sẵn sàng.', 'success');
                    } else if (v.status === 'failed') {
                        Alpine.store('ui').toast('Có video xử lý thất bại.', 'error');
                    }
                    return v.status === 'pending' || v.status === 'processing';
                });
                delay = 4000;
            } catch (e) {
                delay = Math.min(delay * 2, 30000); // lỗi mạng: giãn dần thời gian hỏi lại
            }
            if (pending.length) timer = setTimeout(poll, delay);
        }
        timer = setTimeout(poll, delay);
    })();
</script>
@endif
@endsection
