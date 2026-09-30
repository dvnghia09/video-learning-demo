<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\VietnamesePhone;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    private const FILTERS = ['vip' => 'Thành viên VIP', 'free' => 'Miễn phí', 'admin' => 'Quản trị viên'];

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $filter = array_key_exists($request->query('type', ''), self::FILTERS) ? $request->query('type') : null;

        $items = User::query()
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->when($filter === 'vip', fn ($query) => $query->where('is_vip', true))
            ->when($filter === 'free', fn ($query) => $query->where('is_vip', false)->where('role', '!=', 'admin'))
            ->when($filter === 'admin', fn ($query) => $query->where('role', 'admin'))
            ->latest()->paginate(10)->withQueryString();

        $counts = [
            'all' => User::count(),
            'vip' => User::where('is_vip', true)->count(),
            'free' => User::where('is_vip', false)->where('role', '!=', 'admin')->count(),
            'admin' => User::where('role', 'admin')->count(),
        ];

        return view('admin.users.index', ['items' => $items, 'q' => $q, 'filter' => $filter, 'counts' => $counts, 'filters' => self::FILTERS]);
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['phone' => Phone::normalize($request->input('phone'))]);
        $data = $request->validate($this->rules(), $this->messages());

        User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'is_vip' => $request->boolean('is_vip'),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Đã thêm người dùng mới.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->merge(['phone' => Phone::normalize($request->input('phone'))]);
        $data = $request->validate($this->rules($user), $this->messages());

        // Không cho tự hạ quyền của chính mình (tránh bị khoá khỏi trang quản trị)
        if ($user->is($request->user()) && $data['role'] !== 'admin') {
            return back()->withInput()->withErrors(['role' => 'Bạn không thể tự bỏ quyền quản trị của chính mình.']);
        }

        $user->fill([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'role' => $data['role'],
            'is_vip' => $request->boolean('is_vip'),
        ]);
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Đã cập nhật người dùng.');
    }

    /** Nâng cấp / hạ gói VIP nhanh ngay trên danh sách. */
    public function toggleVip(User $user): RedirectResponse
    {
        $user->update(['is_vip' => ! $user->is_vip]);

        return back()->with('success', $user->is_vip
            ? "Đã nâng cấp “{$user->name}” lên thành viên VIP."
            : "Đã chuyển “{$user->name}” về tài khoản miễn phí.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Bạn không thể xoá chính tài khoản đang đăng nhập.');
        }

        $user->avatar_path && Storage::disk('public')->delete($user->avatar_path);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Đã xoá người dùng.');
    }

    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', new VietnamesePhone, Rule::unique('users', 'phone')->ignore($user?->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'max:100'],
            'role' => ['required', Rule::in(['user', 'admin'])],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ tên.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.unique' => 'Số điện thoại này đã có người dùng.',
            'email.email' => 'Email chưa đúng định dạng.',
            'email.unique' => 'Email này đã có người dùng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu tối thiểu 8 ký tự.',
            'role.in' => 'Vai trò không hợp lệ.',
        ];
    }
}
