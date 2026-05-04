<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function profile()
    {
        return view('admin.pages.profile.index');
    }

    public function index()
    {
        return view('admin.pages.settings.index');
    }

    public function password()
    {
        return view('admin.pages.profile.password');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $avatarDisk = config('filesystems.avatar_disk', 'public');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'] ?: null;
        $user->phone = $validated['phone'] ?: null;
        $user->gender = $validated['gender'] ?: null;
        $user->date_of_birth = $validated['date_of_birth'] ?: null;
        $user->address = $validated['address'] ?: null;

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk($avatarDisk)->delete($user->avatar);
            }

            $user->avatar = $request->file('avatar')->store('avatars', $avatarDisk);
        }

        $user->save();

        return redirect()
            ->route('app.profile.index')
            ->with('status', 'Cập nhật thông tin thành công.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất :min ký tự.',
            'password.max' => 'Mật khẩu mới không được vượt quá :max ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'password.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ], [
            'current_password' => 'mật khẩu hiện tại',
            'password' => 'mật khẩu mới',
            'password_confirmation' => 'xác nhận mật khẩu mới',
        ]);

        $user = $request->user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()
            ->route('app.profile.index')
            ->with('status', 'Đổi mật khẩu thành công.');
    }
}
