<?php

/**
 * ĐĂNG KÝ tài khoản (nhóm B).
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        // confirmed = phải có ô password_confirmation khớp; unique:users,email = email không được trùng.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Chỉ lấy 3 trường; vai trò luôn là "member" (mặc định ở DB).
        $user = User::create($data);

        // Đăng nhập luôn sau khi đăng ký. regenerate() đổi session id để chống session fixation.
        Auth::login($user);
        $request->session()->regenerate();

        Log::info('Đăng ký tài khoản', ['user_id' => $user->id, 'email' => $user->email]);

        return redirect()->route('home')->with('success', 'Chào mừng bạn đến với blog!');
    }
}
