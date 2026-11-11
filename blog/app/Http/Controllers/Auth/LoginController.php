<?php

/**
 * ĐĂNG NHẬP / ĐĂNG XUẤT (nhóm B).
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Auth::attempt so khớp email + mật khẩu (đã băm). Sai thì ghi log cảnh báo rồi quay lại form.
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            Log::warning('Đăng nhập thất bại', ['email' => $credentials['email'], 'ip' => $request->ip()]);

            return back()->withErrors(['email' => 'Email hoặc mật khẩu không đúng.'])->onlyInput('email');
        }

        // Đổi session id sau khi đăng nhập để chống tấn công session fixation.
        $request->session()->regenerate();

        Log::info('Đăng nhập', ['user_id' => Auth::id()]);

        // intended(): quay về trang người dùng định vào trước khi bị yêu cầu đăng nhập.
        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Log::info('Đăng xuất', ['user_id' => Auth::id()]);

        // Đăng xuất đúng cách: hủy session và tạo CSRF token mới.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
