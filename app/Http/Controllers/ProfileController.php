<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\Img;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /** Trang "Tài khoản của tôi". */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $recent = $user->progress()->with('video.lesson')->whereHas('video')->latest('updated_at')->limit(6)->get();
        $stats = [
            'completed' => $user->progress()->where('completed', true)->count(),
            'started' => $user->progress()->where('completed', false)->count(),
        ];

        return view('profile.edit', compact('user', 'recent', 'stats'));
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->only(['name', 'phone', 'email']));
        $user->email = $user->email ?: null;

        if ($request->hasFile('avatar')) {
            $user->avatar_path && Storage::disk('public')->delete($user->avatar_path);
            $user->avatar_path = Img::store($request->file('avatar'), 'avatars', 512);
        } elseif ($request->boolean('remove_avatar') && $user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->avatar_path = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu để xác nhận.',
            'password.current_password' => 'Mật khẩu chưa đúng.',
        ]);

        $user = $request->user();

        Auth::logout();

        $user->avatar_path && Storage::disk('public')->delete($user->avatar_path);
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
