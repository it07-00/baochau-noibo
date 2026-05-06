<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt([...$credentials, 'is_active' => true], $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            $defaultRoute = $user->hasAnyRole(['it', 'giam-doc', 'admin', 'quan-ly', 'ke-toan'])
                ? route('app.dashboard')
                : route('app.home');

            return redirect()->intended($defaultRoute);
        }

        $lockedUser = User::where('username', $credentials['username'])->first();

        if ($lockedUser && !$lockedUser->is_active && Auth::validate($credentials)) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'username' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
                ]);
        }

        return back()
            ->withInput($request->only('username'))
            ->withErrors([
                'username' => 'Tên đăng nhập hoặc mật khẩu không đúng.',
            ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
