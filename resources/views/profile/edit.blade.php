@extends('layouts.public')
@section('title', 'Tài khoản của tôi')
@section('robots', 'noindex,nofollow')

@php
    $in = 'w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-stone-900 placeholder:text-stone-500 focus:outline-none focus:ring-2 focus:ring-amber-600 focus:border-amber-600 text-base';
    $lb = 'block text-base font-semibold text-stone-800 mb-1.5';
    $card = 'bg-white rounded-3xl border-2 border-stone-300 shadow-md';
@endphp

@section('content')
@include('partials.page-header', ['title' => 'Tài khoản của tôi', 'subtitle' => 'Quản lý thông tin cá nhân, mật khẩu và việc học của bạn.'])

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    @if(session('status') === 'profile-updated')
        <div class="mb-6 rounded-2xl bg-emerald-50 border-2 border-emerald-300 text-emerald-900 px-5 py-4 text-base font-semibold">✓ Đã lưu thông tin của bạn.</div>
    @elseif(session('status') === 'password-updated')
        <div class="mb-6 rounded-2xl bg-emerald-50 border-2 border-emerald-300 text-emerald-900 px-5 py-4 text-base font-semibold">✓ Đã đổi mật khẩu thành công.</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3 items-start">

        {{-- ============ Cột trái: tóm tắt tài khoản ============ --}}
        <aside class="{{ $card }} p-6 lg:sticky lg:top-24 text-center">
            <div class="mx-auto w-28 h-28 rounded-full overflow-hidden border-4 border-amber-200 shadow-lg bg-gradient-to-br from-amber-600 to-rose-600 flex items-center justify-center text-white text-5xl font-extrabold">
                @if($user->avatarUrl())<img src="{{ $user->avatarUrl() }}" alt="Ảnh đại diện" class="w-full h-full object-cover">@else{{ $user->initial() }}@endif
            </div>
            <h2 class="mt-4 text-2xl font-extrabold text-stone-900 break-words">{{ $user->name }}</h2>
            <p class="text-stone-700 font-medium">{{ $user->phone }}</p>

            @if($user->is_vip)
                <p class="mt-3 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-100 text-amber-900 font-bold border border-amber-300">⭐ Thành viên VIP</p>
            @else
                <p class="mt-3 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-stone-100 text-stone-800 font-bold border border-stone-300">Tài khoản miễn phí</p>
                <button type="button" data-help="upgrade" class="mt-3 block px-5 py-3 rounded-full bg-gradient-to-r from-amber-700 to-rose-600 text-white font-bold shadow hover:shadow-lg transition w-full">Nâng cấp qua Zalo</button>
            @endif

            <dl class="mt-6 grid grid-cols-2 gap-3 text-center">
                <div class="rounded-2xl bg-cream border border-stone-300 p-3"><dt class="text-sm font-semibold text-stone-700">Đã hoàn thành</dt><dd class="text-3xl font-extrabold text-emerald-800">{{ $stats['completed'] }}</dd></div>
                <div class="rounded-2xl bg-cream border border-stone-300 p-3"><dt class="text-sm font-semibold text-stone-700">Đang học dở</dt><dd class="text-3xl font-extrabold text-amber-800">{{ $stats['started'] }}</dd></div>
            </dl>
            <p class="mt-4 text-sm text-stone-600">Tham gia từ {{ $user->created_at->format('d/m/Y') }}</p>

            <nav class="mt-6 grid gap-2 text-left text-base font-semibold">
                <a href="#thong-tin" class="px-4 py-2.5 rounded-xl hover:bg-amber-50 text-stone-800">Thông tin cá nhân</a>
                <a href="#hoc-tap" class="px-4 py-2.5 rounded-xl hover:bg-amber-50 text-stone-800">Bài đang học</a>
                <a href="#mat-khau" class="px-4 py-2.5 rounded-xl hover:bg-amber-50 text-stone-800">Đổi mật khẩu</a>
                <a href="#xoa-tai-khoan" class="px-4 py-2.5 rounded-xl hover:bg-rose-50 text-rose-800">Xoá tài khoản</a>
            </nav>
        </aside>

        <div class="lg:col-span-2 space-y-6">

            {{-- ============ Thông tin cá nhân ============ --}}
            <section id="thong-tin" class="{{ $card }} p-6 sm:p-8 scroll-mt-24">
                <h2 class="text-2xl font-extrabold text-stone-900 mb-6">Thông tin cá nhân</h2>
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6"
                      x-data="{ preview: @js($user->avatarUrl()), initial: @js($user->initial()), remove: false, error: '',
                          pick(f) { this.error=''; if(!f) return;
                              if(!/^image\/(jpeg|png|webp)$/.test(f.type)) { this.error='Ảnh phải là JPG, PNG hoặc WebP.'; this.$refs.f.value=''; return; }
                              if(f.size > 4*1024*1024) { this.error='Ảnh tối đa 4MB.'; this.$refs.f.value=''; return; }
                              this.preview = URL.createObjectURL(f); this.remove = false; },
                          clear() { this.preview = null; this.$refs.f.value=''; this.remove = true; } }">
                    @csrf @method('PATCH')

                    <div class="flex items-center gap-5">
                        <div class="w-24 h-24 shrink-0 rounded-full overflow-hidden border-4 border-amber-200 bg-gradient-to-br from-amber-600 to-rose-600 flex items-center justify-center text-white text-4xl font-extrabold">
                            <template x-if="preview"><img :src="preview" alt="" class="w-full h-full object-cover"></template>
                            <template x-if="!preview"><span x-text="initial"></span></template>
                        </div>
                        <div class="min-w-0">
                            <input type="file" name="avatar" x-ref="f" accept="image/png,image/jpeg,image/webp" class="hidden" @change="pick($event.target.files[0])">
                            <input type="hidden" name="remove_avatar" :value="remove ? 1 : 0">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="$refs.f.click()" class="px-4 py-2.5 rounded-xl bg-stone-100 hover:bg-stone-200 border border-stone-300 text-stone-900 font-semibold transition">Chọn ảnh đại diện</button>
                                <button type="button" x-show="preview" x-cloak @click="clear()" class="px-4 py-2.5 rounded-xl text-rose-800 hover:bg-rose-50 font-semibold transition">Bỏ ảnh</button>
                            </div>
                            <p class="mt-2 text-sm text-stone-600">JPG, PNG hoặc WebP, tối đa 4MB.</p>
                            <p x-show="error" x-text="error" x-cloak class="mt-1 text-sm font-semibold text-rose-700"></p>
                            @error('avatar')<p class="mt-1 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="name" class="{{ $lb }}">Họ và tên</label>
                        <input id="name" name="name" value="{{ old('name', $user->name) }}" required minlength="2" maxlength="255" autocomplete="name" class="{{ $in }}">
                        @error('name')<p class="mt-1.5 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="{{ $lb }}">Số điện thoại <span class="font-normal text-stone-600">(dùng để đăng nhập)</span></label>
                            <input id="phone" name="phone" type="tel" inputmode="numeric" value="{{ old('phone', $user->phone) }}" required maxlength="16" data-vn-phone autocomplete="tel" class="{{ $in }}">
                            @error('phone')<p class="mt-1.5 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="{{ $lb }}">Email <span class="font-normal text-stone-600">(không bắt buộc)</span></label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" class="{{ $in }}">
                            @error('email')<p class="mt-1.5 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <button type="submit" class="px-8 py-3.5 rounded-full bg-gradient-to-r from-amber-700 to-rose-600 text-white font-bold text-lg shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition">Lưu thông tin</button>
                </form>
            </section>

            {{-- ============ Bài đang học ============ --}}
            <section id="hoc-tap" class="{{ $card }} p-6 sm:p-8 scroll-mt-24">
                <h2 class="text-2xl font-extrabold text-stone-900 mb-1">Bài đang học</h2>
                <p class="text-stone-700 mb-6">Video bạn xem gần đây. Bấm để tiếp tục đúng chỗ đang xem dở.</p>

                @forelse($recent as $p)
                    @php $v = $p->video; $pct = $p->percent(); @endphp
                    <a href="{{ route('courses.video', [$v->lesson, $v]) }}" class="group flex items-center gap-4 p-3 -mx-3 rounded-2xl hover:bg-amber-50 transition">
                        <span class="w-14 h-14 shrink-0 rounded-2xl flex items-center justify-center text-2xl {{ $p->completed ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $p->completed ? '✓' : '▶' }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-bold text-lg text-stone-900 truncate group-hover:text-amber-800">{{ $v->title }}</span>
                            <span class="block text-sm text-stone-600 truncate">{{ $v->lesson?->title }} · {{ $p->updated_at->diffForHumans() }}</span>
                            <span class="mt-2 flex items-center gap-3">
                                <span class="h-2.5 flex-1 rounded-full bg-stone-200 overflow-hidden"><span class="block h-full rounded-full {{ $p->completed ? 'bg-emerald-600' : 'bg-amber-600' }}" style="width: {{ $pct }}%"></span></span>
                                <span class="text-sm font-bold text-stone-800 w-24 text-right">{{ $p->completed ? 'Đã xong' : $pct.'% đã xem' }}</span>
                            </span>
                        </span>
                        <span class="hidden sm:inline-flex shrink-0 px-4 py-2 rounded-full bg-amber-700 group-hover:bg-amber-800 text-white font-bold transition">{{ $p->completed ? 'Xem lại' : 'Tiếp tục' }}</span>
                    </a>
                @empty
                    <div class="text-center py-10 rounded-2xl bg-cream border-2 border-dashed border-stone-300">
                        <p class="text-lg text-stone-800 font-semibold">Bạn chưa xem video nào.</p>
                        <a href="{{ route('courses.index') }}" class="mt-4 inline-flex px-8 py-3 rounded-full bg-amber-700 hover:bg-amber-800 text-white font-bold transition">Bắt đầu học ngay</a>
                    </div>
                @endforelse
            </section>

            {{-- ============ Đổi mật khẩu ============ --}}
            <section id="mat-khau" class="{{ $card }} p-6 sm:p-8 scroll-mt-24">
                <h2 class="text-2xl font-extrabold text-stone-900 mb-6">Đổi mật khẩu</h2>
                <form method="POST" action="{{ route('password.update') }}" class="space-y-5 max-w-xl" x-data="{ show: false }">
                    @csrf @method('PUT')
                    <div>
                        <label for="current_password" class="{{ $lb }}">Mật khẩu hiện tại</label>
                        <input id="current_password" name="current_password" :type="show ? 'text' : 'password'" autocomplete="current-password" required class="{{ $in }}">
                        @if($errors->updatePassword->has('current_password'))<p class="mt-1.5 text-sm font-semibold text-rose-700">{{ $errors->updatePassword->first('current_password') }}</p>@endif
                    </div>
                    <div>
                        <label for="password" class="{{ $lb }}">Mật khẩu mới</label>
                        <input id="password" name="password" :type="show ? 'text' : 'password'" autocomplete="new-password" required minlength="8" maxlength="100" class="{{ $in }}">
                        @if($errors->updatePassword->has('password'))<p class="mt-1.5 text-sm font-semibold text-rose-700">{{ $errors->updatePassword->first('password') }}</p>@else<p class="mt-1.5 text-sm text-stone-600">Ít nhất 8 ký tự.</p>@endif
                    </div>
                    <div>
                        <label for="password_confirmation" class="{{ $lb }}">Nhập lại mật khẩu mới</label>
                        <input id="password_confirmation" name="password_confirmation" data-match="#password" :type="show ? 'text' : 'password'" autocomplete="new-password" required minlength="8" maxlength="100" class="{{ $in }}">
                    </div>
                    <label class="inline-flex items-center gap-2 text-base text-stone-800 cursor-pointer"><input type="checkbox" x-model="show" class="w-5 h-5 rounded border-stone-400 text-amber-700 focus:ring-amber-600"> Hiện mật khẩu</label>
                    <div><button type="submit" class="px-8 py-3.5 rounded-full bg-stone-900 hover:bg-stone-800 text-white font-bold text-lg shadow transition">Đổi mật khẩu</button></div>
                </form>
            </section>

            {{-- ============ Xoá tài khoản ============ --}}
            <section id="xoa-tai-khoan" class="rounded-3xl border-2 border-rose-300 bg-rose-50 p-6 sm:p-8 scroll-mt-24" x-data="{ open: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }">
                <h2 class="text-2xl font-extrabold text-rose-900">Xoá tài khoản</h2>
                <p class="mt-1 text-rose-900">Toàn bộ thông tin và tiến độ học của bạn sẽ bị xoá vĩnh viễn và không thể khôi phục.</p>
                <button type="button" x-show="!open" @click="open = true" class="mt-4 px-6 py-3 rounded-full border-2 border-rose-700 text-rose-800 font-bold hover:bg-rose-700 hover:text-white transition">Tôi muốn xoá tài khoản</button>

                <form method="POST" action="{{ route('profile.destroy') }}" x-show="open" x-cloak class="mt-5 space-y-4 max-w-xl">
                    @csrf @method('DELETE')
                    <p class="font-bold text-rose-900">Bạn chắc chắn chứ? Nhập mật khẩu để xác nhận:</p>
                    <input name="password" type="password" placeholder="Mật khẩu của bạn" autocomplete="current-password" class="{{ $in }}">
                    @if($errors->userDeletion->has('password'))<p class="text-sm font-semibold text-rose-700">{{ $errors->userDeletion->first('password') }}</p>@endif
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="px-6 py-3 rounded-full bg-rose-700 hover:bg-rose-800 text-white font-bold transition">Xoá vĩnh viễn</button>
                        <button type="button" @click="open = false" class="px-6 py-3 rounded-full bg-white border-2 border-stone-300 text-stone-800 font-bold hover:bg-stone-50 transition">Không, giữ lại</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
