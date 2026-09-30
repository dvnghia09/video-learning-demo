{{-- Cửa sổ hướng dẫn liên hệ Zalo (quên mật khẩu / nâng cấp gói).
     Mở bằng bất kỳ phần tử nào có data-help="password" hoặc data-help="upgrade" (href chỉ là dự phòng khi tắt JavaScript). --}}
@php
    $__u = auth()->user();
    $__cfg = [
        'zalo' => $site->zaloUrl(),
        'phone' => $site->defaultPhone(),
        'phoneHref' => $site->defaultPhone() ? \App\Support\SiteSettings::telHref($site->defaultPhone()) : null,
        'contact' => route('contact.index'),
        'login' => route('login'),
        'register' => route('register'),
        'brand' => $site->brand(),
        'user' => $__u ? ['name' => $__u->name, 'phone' => $__u->phone] : null,
    ];
@endphp
<style>
    /* Thanh cuộn của cửa sổ: mảnh, bo tròn, tách khỏi mép để không đè lên góc bo */
    #helpModal .help-scroll { scrollbar-width: thin; scrollbar-color: #d6d3d1 transparent; }
    #helpModal .help-scroll::-webkit-scrollbar { width: 12px; }
    #helpModal .help-scroll::-webkit-scrollbar-track { background: transparent; margin: 16px 0; }
    #helpModal .help-scroll::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 9999px; border: 3px solid transparent; background-clip: content-box; }
    #helpModal .help-scroll::-webkit-scrollbar-thumb:hover { background: #a8a29e; background-clip: content-box; border: 3px solid transparent; }
</style>
<div id="helpModal" class="fixed inset-0 z-[300] hidden items-center justify-center p-3 sm:p-6 bg-stone-950/70 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="helpTitle" style="opacity:0;transition:opacity .2s">
    <div class="relative w-full max-w-xl md:max-w-4xl bg-white rounded-3xl shadow-2xl border-2 border-stone-200 overflow-hidden">
        <button type="button" data-help-close class="absolute top-3 right-3 z-10 w-11 h-11 rounded-full bg-white/90 hover:bg-stone-200 text-stone-800 text-2xl font-bold leading-none shadow" aria-label="Đóng">&times;</button>

        <div class="help-scroll max-h-[calc(100dvh-1.5rem)] sm:max-h-[calc(100dvh-3rem)] overflow-y-auto overscroll-contain">
            <div class="help-content md:grid md:grid-cols-[1.05fr_1fr]">

                {{-- Cột trái: tiêu đề + các bước --}}
                <div class="px-6 sm:px-8 pt-8 pb-6 md:pb-8">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-4xl mb-4" id="helpIcon">💬</div>
                    <h2 id="helpTitle" class="text-2xl sm:text-[1.7rem] font-extrabold text-stone-900 leading-snug pr-10 md:pr-0"></h2>
                    <p id="helpLead" class="mt-2 text-lg text-stone-700 leading-relaxed"></p>
                    <ol id="helpSteps" class="mt-5 space-y-4"></ol>
                </div>

                {{-- Cột phải: tin nhắn mẫu + các nút liên hệ --}}
                <div class="px-6 sm:px-8 pt-6 pb-6 bg-stone-50 border-t-2 md:border-t-0 md:border-l-2 border-stone-200 md:pt-8">
                    <div id="helpForm" class="space-y-3">
                        <div id="helpWho" class="hidden text-base text-stone-800 rounded-xl bg-white border border-stone-200 px-4 py-3"></div>
                        <div id="helpInputs" class="grid sm:grid-cols-2 gap-3">
                            <label class="block"><span class="block text-sm font-semibold text-stone-700 mb-1">Họ và tên của bạn</span>
                                <input id="helpName" type="text" autocomplete="name" class="w-full px-3 py-2.5 rounded-xl border border-stone-300 bg-white text-base focus:outline-none focus:ring-2 focus:ring-amber-600" placeholder="VD: Nguyễn Thị Lan"></label>
                            <label class="block"><span class="block text-sm font-semibold text-stone-700 mb-1">Số điện thoại đăng ký</span>
                                <input id="helpPhone" type="tel" inputmode="numeric" autocomplete="tel" class="w-full px-3 py-2.5 rounded-xl border border-stone-300 bg-white text-base focus:outline-none focus:ring-2 focus:ring-amber-600" placeholder="VD: 0912345678"></label>
                        </div>
                        <div>
                            <div class="flex items-center justify-between gap-3 mb-1.5">
                                <p class="text-sm font-semibold text-stone-700">Tin nhắn mẫu (sao chép rồi dán vào Zalo):</p>
                                <button type="button" id="helpCopy" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-stone-900 hover:bg-stone-800 text-white text-sm font-semibold transition">📋 <span>Sao chép</span></button>
                            </div>
                            <p id="helpMsg" class="p-3 rounded-xl bg-white border border-stone-300 text-stone-900 text-[15px] leading-snug select-all"></p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-2.5">
                        <p id="helpAuthLabel" class="hidden text-base font-bold text-stone-900">① Có tài khoản trước:</p>
                        <div id="helpAuth" class="hidden grid-cols-2 gap-3">
                            <a id="helpLogin" href="#" class="text-center px-4 py-3 rounded-2xl bg-amber-700 hover:bg-amber-800 text-white text-lg font-bold transition">Đăng nhập</a>
                            <a id="helpRegister" href="#" class="text-center px-4 py-3 rounded-2xl border-2 border-amber-700 text-amber-800 hover:bg-amber-50 text-lg font-bold transition">Đăng ký</a>
                        </div>
                        <p id="helpZaloLabel" class="hidden text-base font-bold text-stone-900 pt-1">② Rồi nhắn Zalo cho admin:</p>
                        <a id="helpZalo" href="#" target="_blank" rel="noopener" class="hidden items-center justify-center gap-2 w-full px-6 py-3.5 rounded-2xl bg-[#0068ff] hover:bg-blue-700 text-white text-lg font-extrabold shadow-lg transition">💬 Nhắn Zalo cho admin</a>
                        <a id="helpCall" href="#" class="hidden items-center justify-center gap-2 w-full px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-lg font-bold transition"></a>
                        <a id="helpContact" href="#" class="hidden items-center justify-center gap-2 w-full px-6 py-3 rounded-2xl bg-amber-700 hover:bg-amber-800 text-white text-lg font-bold transition">✉ Gửi lời nhắn cho admin</a>
                        <button type="button" data-help-close class="w-full px-6 py-2 rounded-2xl text-stone-600 hover:bg-stone-200/70 font-semibold transition">Để sau</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const C = @js($__cfg);
    const $ = (id) => document.getElementById(id);
    const modal = $('helpModal');
    let kind = 'upgrade', lastFocus = null;

    const TEXT = {
        upgrade: {
            icon: '⭐', iconBg: 'bg-amber-100',
            title: 'Nâng cấp gói để xem đầy đủ bài học',
            lead: 'Video này dành cho thành viên VIP. Bạn cần có tài khoản, rồi liên hệ quản trị viên qua Zalo để được nâng cấp. Rất đơn giản:',
            steps: (loggedIn) => [
                loggedIn ? '✅ Bạn đã đăng nhập, tài khoản của bạn hiện ở bên dưới.' : '<b>Đăng ký</b> tài khoản mới (miễn phí) hoặc <b>đăng nhập</b> nếu bạn đã có. <b>Việc này cần làm trước</b>, để admin biết nâng cấp cho tài khoản nào.',
                'Sau khi có tài khoản, bấm nút xanh <b>“Nhắn Zalo cho admin”</b>, rồi gửi tin nhắn mẫu (bấm “Sao chép” và dán vào Zalo).',
                'Quản trị viên sẽ nâng cấp tài khoản cho bạn. Sau đó bạn <b>tải lại trang</b> hoặc đăng nhập lại là xem được tất cả bài học.',
            ],
            msg: (n, p) => `Xin chào admin ${C.brand}, tôi là ${n || '[họ tên]'}, số điện thoại đăng ký ${p || '[số điện thoại]'}. Tôi muốn nâng cấp gói VIP để xem toàn bộ bài học. Cảm ơn admin!`,
        },
        password: {
            icon: '🔑', iconBg: 'bg-sky-100',
            title: 'Quên mật khẩu?',
            lead: 'Đừng lo! Để bảo mật, mật khẩu sẽ do quản trị viên đặt lại giúp bạn qua Zalo:',
            steps: () => [
                'Điền <b>họ tên</b> và <b>số điện thoại đã đăng ký</b> vào ô bên dưới để soạn sẵn tin nhắn.',
                'Bấm nút xanh <b>“Nhắn Zalo cho admin”</b>, rồi gửi tin nhắn mẫu (bấm “Sao chép” và dán vào Zalo).',
                'Admin xác nhận và gửi bạn <b>mật khẩu mới</b>. Bạn đăng nhập, rồi vào <b>Tài khoản của tôi → Đổi mật khẩu</b> để đặt lại mật khẩu bạn thích.',
            ],
            msg: (n, p) => `Xin chào admin ${C.brand}, tôi quên mật khẩu. Họ tên: ${n || '[họ tên]'}, số điện thoại đăng ký: ${p || '[số điện thoại]'}. Nhờ admin đặt lại mật khẩu giúp tôi. Cảm ơn admin!`,
        },
    };

    function stepsHtml(list) {
        return list.map((t, i) => `<li class="flex gap-4"><span class="w-9 h-9 shrink-0 rounded-full bg-amber-700 text-white font-extrabold flex items-center justify-center">${i + 1}</span><span class="text-lg text-stone-800 leading-relaxed pt-0.5">${t}</span></li>`).join('');
    }
    function setShow(el, on, display = 'flex') { el.classList.toggle('hidden', !on); el.classList.toggle(display, on); }

    function render() {
        const t = TEXT[kind], u = C.user;
        $('helpIcon').className = 'w-16 h-16 rounded-2xl flex items-center justify-center text-4xl mb-4 ' + t.iconBg;
        $('helpIcon').textContent = t.icon;
        $('helpTitle').textContent = t.title;
        $('helpLead').textContent = t.lead;
        $('helpSteps').innerHTML = stepsHtml(t.steps(!!u));

        // Người đã đăng nhập (nâng cấp gói): dùng luôn thông tin tài khoản; còn lại cho tự điền
        const known = kind === 'upgrade' && u;
        setShow($('helpInputs'), !known, 'grid');
        const who = $('helpWho');
        who.classList.toggle('hidden', !known);
        if (known) who.innerHTML = '✅ Tài khoản của bạn: <b></b> · <b></b>', who.querySelectorAll('b')[0].textContent = u.name, who.querySelectorAll('b')[1].textContent = u.phone;
        if (known) { $('helpName').value = u.name; $('helpPhone').value = u.phone; }
        else if (u && !$('helpName').value) { $('helpName').value = u.name; $('helpPhone').value = u.phone; }
        updateMsg();

        // Nút liên hệ: Zalo là chính; không có Zalo thì gọi điện / gửi lời nhắn
        const z = $('helpZalo');
        setShow(z, !!C.zalo, 'flex'); if (C.zalo) z.href = C.zalo;
        const call = $('helpCall');
        setShow(call, !!C.phone, 'flex'); if (C.phone) { call.href = C.phoneHref; call.textContent = '📞 Gọi điện: ' + C.phone; }
        const ct = $('helpContact');
        setShow(ct, !C.zalo, 'flex'); ct.href = C.contact;
        // Nâng cấp mà chưa có tài khoản: gợi ý đăng nhập / đăng ký
        const guestUpgrade = kind === 'upgrade' && !u;
        setShow($('helpAuth'), guestUpgrade, 'grid');
        $('helpAuthLabel').classList.toggle('hidden', !guestUpgrade);
        $('helpZaloLabel').classList.toggle('hidden', !guestUpgrade);
        $('helpLogin').href = C.login; $('helpRegister').href = C.register;
    }
    function updateMsg() { $('helpMsg').textContent = TEXT[kind].msg($('helpName').value.trim(), $('helpPhone').value.trim()); if (!modal.classList.contains('hidden')) fit(); }
    ['helpName', 'helpPhone'].forEach(id => $(id).addEventListener('input', updateMsg));

    // Máy tính: nếu nội dung cao hơn khung nhìn thì thu nhỏ đúng mức cần thiết để vừa một màn hình (khỏi phải cuộn)
    function fit() {
        const content = modal.querySelector('.help-content'), sc = modal.querySelector('.help-scroll');
        content.style.zoom = '';
        if (innerWidth < 768) return;               // điện thoại: cuộn bình thường
        const natural = sc.scrollHeight, avail = sc.clientHeight;
        if (natural > avail + 1) content.style.zoom = Math.max(0.7, avail / natural - 0.005);
    }
    addEventListener('resize', () => { if (!modal.classList.contains('hidden')) fit(); });

    function open(k, from) {
        kind = TEXT[k] ? k : 'upgrade'; lastFocus = from || document.activeElement;
        // Trang đăng nhập: lấy sẵn số điện thoại đã gõ
        const typed = document.getElementById('phone');
        if (kind === 'password' && typed && typed.value && !$('helpPhone').value) $('helpPhone').value = typed.value;
        render();
        modal.classList.remove('hidden'); modal.classList.add('flex');
        requestAnimationFrame(() => { modal.style.opacity = 1; });
        setTimeout(() => { modal.style.opacity = 1; }, 30);
        document.documentElement.style.overflow = 'hidden';
        fit();
        (C.zalo ? $('helpZalo') : modal.querySelector('[data-help-close]')).focus({ preventScroll: true });
    }
    function close() {
        modal.style.opacity = 0;
        setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 200);
        document.documentElement.style.overflow = '';
        lastFocus && lastFocus.focus && lastFocus.focus({ preventScroll: true });
    }

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-help]');
        if (trigger) { e.preventDefault(); open(trigger.dataset.help, trigger); return; }
        if (e.target.closest('[data-help-close]') || e.target === modal) close();
    });
    document.addEventListener('keydown', (e) => {
        if (modal.classList.contains('hidden')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'Tab') {   // giữ tiêu điểm trong cửa sổ
            const f = [...modal.querySelectorAll('a:not(.hidden),button,input')].filter(x => x.offsetParent !== null);
            if (!f.length) return;
            const first = f[0], last = f[f.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });

    // Sao chép tin nhắn mẫu
    $('helpCopy').addEventListener('click', async () => {
        const text = $('helpMsg').textContent, label = $('helpCopy').querySelector('span');
        try { await navigator.clipboard.writeText(text); }
        catch (e) { const r = document.createRange(); r.selectNodeContents($('helpMsg')); const s = getSelection(); s.removeAllRanges(); s.addRange(r); document.execCommand('copy'); s.removeAllRanges(); }
        label.textContent = 'Đã chép ✓'; setTimeout(() => label.textContent = 'Sao chép', 2200);
    });
    window.openHelp = open;   // cho phép mở từ script khác
})();
</script>
