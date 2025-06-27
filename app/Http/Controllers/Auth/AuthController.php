<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('user.management.loginForm'); // Blade view for login form
    }

    public function login(Request $request)
    {
        $request->ensureIsNotRateLimited();

        $credentials = $request->validate([
            'employee_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('employee_id', $request->employee_id)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($request->throttleKey(), 60);

            return back()->withErrors([
                'employee_id' => 'กรุณากรอกหมายเลขประจำตัวและรหัสผ่านให้ถูกต้อง',
            ])->withInput();
        }

        RateLimiter::clear($request->throttleKey());

        // Log the user in using Laravel session auth
        Auth::login($user);
        $token = $user->createToken('auth_token')->plainTextToken;

        return redirect()->intended('/users')->with('success', 'เข้าสู่ระบบสำเร็จ');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'ออกจากระบบสำเร็จ');
    }

    public function user(Request $request)
    {
        return view('auth.profile', ['user' => $request->user()]);
    }
}
