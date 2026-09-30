{{-- Tự cập nhật trạng thái video đang xử lý (không cần tải lại trang).
     Phần tử cần theo dõi: data-vstatus-id="ID" data-status="pending|processing|ready|failed".
     Nhãn "Đang xử lý": data-vstatus-label="ID" (sẽ tự biến mất khi video xong).
     Khi trạng thái đổi sang ready/failed, phát sự kiện "video-status" trên document. --}}
<script data-poller>
(function () {
    const URL_STATUS = @js(route('videos.status'));
    let ids = [...new Set([...document.querySelectorAll('[data-vstatus-id]')]
        .filter(el => ['pending', 'processing'].includes(el.dataset.status))
        .map(el => el.dataset.vstatusId))];
    if (!ids.length) return;

    let delay = 4000, timer = null;
    const startedAt = Date.now();

    async function tick() {
        if (document.hidden) return schedule(); // tab đang ẩn: tạm dừng, không tốn request
        try {
            const res = await fetch(URL_STATUS + '?ids=' + ids.join(','), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!res.ok) throw new Error(res.status);
            const data = await res.json();

            ids = ids.filter(id => {
                const st = data[id];
                if (!st) return false;
                document.querySelectorAll(`[data-vstatus-id="${id}"]`).forEach(el => { el.dataset.status = st; });
                if (st === 'ready' || st === 'failed') {
                    document.querySelectorAll(`[data-vstatus-label="${id}"]`).forEach(el => el.remove());
                    document.dispatchEvent(new CustomEvent('video-status', { detail: { id: Number(id), status: st } }));
                    return false;
                }
                return true;
            });
            delay = Date.now() - startedAt > 120000 ? 15000 : 4000; // sau 2 phút hỏi thưa hơn
        } catch (e) {
            delay = Math.min(delay * 2, 30000); // lỗi mạng: giãn dần
        }
        schedule();
    }
    function schedule() { if (ids.length) timer = setTimeout(tick, delay); }

    // Quay lại tab: hỏi ngay
    document.addEventListener('visibilitychange', () => { if (!document.hidden && ids.length) { clearTimeout(timer); tick(); } });
    schedule();
})();
</script>
