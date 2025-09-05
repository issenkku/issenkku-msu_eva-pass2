<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('user.management.loginForm'); // Blade view for login form
    }

    public function login(Request $request)
    {

        $credentials = $request->validate([
            'employee_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $key = Str::lower($request->input('employee_id')).'|'.$request->ip();
        $maxAttempts = 5;
        $decaySeconds = 60;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'คุณพยายามเข้าสู่ระบบมากเกินไป กรุณารอ 1 นาทีแล้วลองใหม่อีกครั้ง.',
            ], 429);
        }

        $user = User::where('employee_id', $request->employee_id)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, $decaySeconds);

            return response()->json([
                'message' => 'กรุณากรอกหมายเลขประจำตัวและรหัสผ่านให้ถูกต้อง',
            ], 401);
        }

        if ($user->status === 'inactive') {
            return response()->json([
                'message' => 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ',
            ], 413);
        }

        RateLimiter::clear($key);

        Auth::login($user);
        $request->session()->regenerate(); // prevent session fixation

        // กำหนด path redirect ตาม role (ส่งกลับไปให้ JS ใช้ window.location.href = response.data.redirect)
        $redirect = '/';
        if ($user->hasRole('admin')) {
            $redirect = '/dashboard';
        } elseif ($user->hasRole('ผู้บริหาร')) {
            $redirect = '/manager-dashboard';
        } elseif ($user->hasRole('กรรมการ')) {
            $redirect = '/director-dashboard';
        } elseif ($user->hasRole('ผู้ประเมิน')) {
            $redirect = '/evaluator-dashboard';
        } elseif ($user->hasRole('ผู้รับการประเมิน')) {
            $redirect = '/evaluatee-dashboard';
        }

        return response()->json(['redirect' => $redirect]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'ออกจากระบบสำเร็จ');
    }

    public function user(Request $request)
    {
        return view('auth.profile', ['user' => $request->user()]);
    }
}
