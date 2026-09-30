@php
    $isEdit = isset($user);
    $in = 'w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition bg-white';
    $lb = 'block text-sm font-semibold text-slate-700 mb-1.5';
    $isSelf = $isEdit && $user->is(auth()->user());
@endphp

<form action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST" class="max-w-3xl space-y-6" x-data="{ show: false, vip: {{ old('is_vip', $user->is_vip ?? false) ? 'true' : 'false' }} }">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
        <h3 class="font-bold text-slate-900">Thông tin cá nhân</h3>
        <div>
            <label for="name" class="{{ $lb }}">Họ và tên <span class="text-rose-500">*</span></label>
            <input id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required maxlength="255" class="{{ $in }}" placeholder="Nguyễn Văn A">
            @error('name')<p class="text-rose-600 text-sm mt-1.5">{{ $message }}</p>@enderror
        </div>
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="phone" class="{{ $lb }}">Số điện thoại <span class="text-rose-500">*</span> <span class="font-normal text-slate-500">(dùng để đăng nhập)</span></label>
                <input id="phone" name="phone" type="tel" inputmode="numeric" value="{{ old('phone', $user->phone ?? '') }}" required maxlength="16" data-vn-phone class="{{ $in }}" placeholder="0912345678">
                @error('phone')<p class="text-rose-600 text-sm mt-1.5">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="{{ $lb }}">Email <span class="font-normal text-slate-500">(không bắt buộc)</span></label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" class="{{ $in }}" placeholder="ten@example.com">
                @error('email')<p class="text-rose-600 text-sm mt-1.5">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-3">
        <h3 class="font-bold text-slate-900">Mật khẩu</h3>
        <div class="max-w-md">
            <label for="password" class="{{ $lb }}">{{ $isEdit ? 'Mật khẩu mới' : 'Mật khẩu' }} @unless($isEdit)<span class="text-rose-500">*</span>@endunless</label>
            <div class="relative">
                <input id="password" name="password" :type="show ? 'text' : 'password'" autocomplete="new-password" @unless($isEdit) required @endunless minlength="8" class="{{ $in }} pr-20" placeholder="{{ $isEdit ? 'Để trống nếu không đổi' : 'Ít nhất 8 ký tự' }}">
                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-amber-700 hover:text-amber-800" x-text="show ? 'Ẩn' : 'Hiện'"></button>
            </div>
            @error('password')<p class="text-rose-600 text-sm mt-1.5">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
        <h3 class="font-bold text-slate-900">Phân quyền &amp; gói học</h3>

        <div class="max-w-md">
            <label for="role" class="{{ $lb }}">Vai trò</label>
            <select id="role" name="role" class="{{ $in }}" @if($isSelf) title="Bạn không thể tự bỏ quyền quản trị" @endif>
                <option value="user" @selected(old('role', $user->role ?? 'user') === 'user')>Học viên — chỉ dùng phía website</option>
                <option value="admin" @selected(old('role', $user->role ?? 'user') === 'admin')>Quản trị viên — được vào trang quản trị</option>
            </select>
            @if($isSelf)<p class="text-xs text-slate-500 mt-1.5">Đây là tài khoản của bạn nên không thể đổi thành Học viên.</p>@endif
            @error('role')<p class="text-rose-600 text-sm mt-1.5">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-start gap-4 rounded-xl border-2 p-4 cursor-pointer transition" :class="vip ? 'border-amber-400 bg-amber-50' : 'border-slate-200 hover:bg-slate-50'">
            <input type="checkbox" name="is_vip" value="1" x-model="vip" class="mt-1 h-5 w-5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
            <span>
                <span class="block font-bold text-slate-900">⭐ Thành viên VIP</span>
                <span class="block text-sm text-slate-600">Bật: xem được <b>toàn bộ</b> video. Tắt: tài khoản miễn phí, chỉ xem các video được đánh dấu “Miễn phí”.</span>
            </span>
        </label>
    </section>

    <div class="flex flex-col-reverse sm:flex-row gap-3">
        <a href="{{ route('admin.users.index') }}" class="px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold text-center hover:bg-slate-50 transition">Quay lại</a>
        <button type="submit" class="px-8 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow transition">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo người dùng' }}</button>
    </div>
</form>
