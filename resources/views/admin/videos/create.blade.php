@extends('layouts.admin')
@section('title', 'Tải video lên')
@section('header', 'Tải video lên')

@section('content')
<div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200 max-w-2xl"
     x-data="videoUploader({ maxMb: {{ $maxUploadMb }}, action: @js(route('admin.videos.store')), listUrl: @js(route('admin.videos.index')) })">

    <form id="uploadForm" @submit.prevent="submit" class="space-y-6" novalidate>
        @csrf

        {{-- Vùng chọn file --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">File video <span class="text-rose-500">*</span></label>

            <div x-show="!file" @click="$refs.file.click()"
                 @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="dragging = false; pick($event.dataTransfer.files[0])"
                 :class="dragging ? 'border-amber-500 bg-amber-50' : 'border-slate-300 hover:border-amber-400 hover:bg-slate-50'"
                 class="cursor-pointer rounded-2xl border-2 border-dashed p-10 text-center transition">
                <div class="mx-auto w-14 h-14 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                </div>
                <p class="font-semibold text-slate-800">Kéo thả video vào đây hoặc bấm để chọn file</p>
                <p class="text-sm text-slate-500 mt-1">MP4, MOV, AVI, WEBM, MKV · tối đa {{ $maxUploadMb }} MB</p>
            </div>

            <div x-show="file" x-cloak class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center gap-4">
                <div class="w-12 h-12 shrink-0 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-800 truncate" x-text="file?.name"></p>
                    <p class="text-sm text-slate-500" x-text="fmt(file?.size || 0)"></p>
                </div>
                <button type="button" x-show="!uploading" @click="clearFile()" class="text-sm font-semibold text-rose-600 hover:text-rose-700">Đổi file</button>
            </div>

            <input type="file" x-ref="file" accept="video/*,.mkv" class="hidden" @change="pick($event.target.files[0])">
        </div>

        @include('admin.videos._fields')

        {{-- Tiến trình --}}
        <div x-show="uploading" x-cloak class="rounded-2xl bg-amber-50 border border-amber-200 p-4">
            <div class="flex justify-between text-sm mb-2">
                <span class="font-semibold text-amber-800" x-text="percent < 100 ? 'Đang tải lên…' : 'Máy chủ đang lưu file, vui lòng chờ…'"></span>
                <span class="font-bold text-amber-700" x-text="percent + '%'"></span>
            </div>
            <div class="w-full bg-amber-200 rounded-full h-3 overflow-hidden">
                <div class="bg-amber-600 h-3 rounded-full transition-all duration-200" :style="`width:${percent}%`"></div>
            </div>
            <div class="flex justify-between text-xs text-amber-800/80 mt-2">
                <span x-text="fmt(loaded) + ' / ' + fmt(total)"></span>
                <span x-text="speed ? fmt(speed) + '/s' + (eta ? ' · còn ~' + eta : '') : ''"></span>
            </div>
            <p class="text-xs text-amber-800/80 mt-2">Đừng đóng trang này cho đến khi tải xong.</p>
        </div>

        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2">
            <a x-show="!uploading" href="{{ route('admin.videos.index') }}" class="px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold text-center hover:bg-slate-50 transition">Quay lại</a>
            <button type="button" x-show="uploading" x-cloak @click="cancel()" class="px-6 py-3 rounded-xl border border-rose-300 text-rose-700 font-semibold hover:bg-rose-50 transition">Huỷ tải lên</button>
            <button type="submit" :disabled="uploading || !file" :class="(uploading || !file) && 'opacity-50 cursor-not-allowed'"
                    class="px-6 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow transition"
                    x-text="uploading ? 'Đang tải lên…' : 'Tải lên video'"></button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    function videoUploader({ maxMb, action, listUrl }) {
        return {
            file: null, title: '', dragging: false, uploading: false,
            percent: 0, loaded: 0, total: 0, speed: 0, eta: '', startedAt: 0, controller: null,

            fmt(b) {
                if (b >= 1073741824) return (b / 1073741824).toFixed(2) + ' GB';
                if (b >= 1048576) return (b / 1048576).toFixed(1) + ' MB';
                return Math.max(1, Math.round(b / 1024)) + ' KB';
            },
            problem(msg, title = 'Không thể tải lên') {
                return Alpine.store('ui').notify({ type: 'error', title, message: msg });
            },
            pick(f) {
                if (!f) return;
                const okType = f.type.startsWith('video/') || /\.(mp4|mov|avi|webm|mkv)$/i.test(f.name);
                if (!okType) return this.problem('File này không phải video. Vui lòng chọn file MP4, MOV, AVI, WEBM hoặc MKV.', 'Sai định dạng');
                if (f.size > maxMb * 1048576) {
                    return this.problem(`File nặng ${this.fmt(f.size)}, vượt giới hạn ${maxMb} MB của máy chủ.\nHãy nén video nhỏ lại rồi thử lại.`, 'File quá lớn');
                }
                this.file = f;
                if (!this.title.trim()) this.title = f.name.replace(/\.[^.]+$/, '').replace(/[_-]+/g, ' ');
            },
            clearFile() { this.file = null; this.$refs.file.value = ''; },
            beforeUnload: (e) => { e.preventDefault(); e.returnValue = ''; },

            cancel() {
                Alpine.store('ui').ask({
                    type: 'warning', title: 'Huỷ tải lên?', message: 'Video đang tải sẽ bị dừng và bạn phải tải lại từ đầu.',
                    confirmText: 'Huỷ tải lên', cancelText: 'Tiếp tục tải',
                }).then(ok => { if (ok && this.controller) this.controller.abort(); });
            },

            async submit() {
                const form = document.getElementById('uploadForm');
                if (!this.file) return this.problem('Vui lòng chọn file video trước.', 'Chưa chọn video');
                if (!this.title.trim()) return this.problem('Vui lòng nhập tên video.', 'Thiếu tên video');
                if (!form.lesson_id) return this.problem('Cần có ít nhất một bài học trước khi tải video.', 'Chưa có bài học');

                const data = new FormData(form);
                data.set('video_file', this.file);

                this.uploading = true; this.percent = 0; this.loaded = 0; this.total = this.file.size; this.speed = 0; this.eta = '';
                this.startedAt = Date.now();
                this.controller = new AbortController();
                window.addEventListener('beforeunload', this.beforeUnload);

                try {
                    const res = await axios.post(action, data, {
                        signal: this.controller.signal,
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        onUploadProgress: (e) => {
                            this.loaded = e.loaded; this.total = e.total || this.file.size;
                            this.percent = Math.min(100, Math.round(e.loaded * 100 / this.total));
                            const secs = (Date.now() - this.startedAt) / 1000;
                            this.speed = secs > 0 ? e.loaded / secs : 0;
                            const left = this.speed ? (this.total - e.loaded) / this.speed : 0;
                            this.eta = left > 60 ? Math.ceil(left / 60) + ' phút' : (left > 0 ? Math.ceil(left) + ' giây' : '');
                        },
                    });
                    window.removeEventListener('beforeunload', this.beforeUnload);
                    this.uploading = false;
                    const again = await Alpine.store('ui').ask({
                        type: 'success', title: 'Tải lên thành công!',
                        message: 'Video đang được xử lý trong nền. Khi xong, trạng thái sẽ chuyển thành “Sẵn sàng”.',
                        confirmText: 'Xem danh sách video', cancelText: 'Thêm video khác',
                    });
                    if (again) window.location.href = res.data.redirect || listUrl;
                    else { this.clearFile(); this.title = ''; this.percent = 0; }
                } catch (err) {
                    window.removeEventListener('beforeunload', this.beforeUnload);
                    this.uploading = false;
                    if (axios.isCancel(err)) return Alpine.store('ui').toast('Đã huỷ tải lên.', 'error');
                    const r = err.response;
                    let msg = 'Có lỗi xảy ra khi tải lên. Vui lòng thử lại.';
                    if (r?.status === 413) msg = `File vượt quá dung lượng máy chủ cho phép (${maxMb} MB).`;
                    else if (r?.status === 419) msg = 'Phiên đăng nhập đã hết hạn. Hãy tải lại trang và đăng nhập lại.';
                    else if (r?.status === 422 && r.data?.errors) msg = Object.values(r.data.errors).flat().join('\n');
                    else if (r?.data?.message) msg = r.data.message;
                    this.problem(msg);
                }
            },
        };
    }
</script>
@endsection
