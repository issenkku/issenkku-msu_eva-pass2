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
        return view('user.management.loginForm');
    }

    public function login(Request $request)
    {
        $request->validate([
            'employee_id' => ['required'],
            'password' => ['required'],
        ]);

        $normalizedEmployeeId = Str::lower(trim((string) $request->input('employee_id')));
        $key = $normalizedEmployeeId.'|'.$request->ip();
        $maxAttempts = 5;
        $decaySeconds = 60;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'คุณพยายามเข้าสู่ระบบมากเกินไป กรุณารอ 1 นาทีแล้วลองใหม่อีกครั้ง.',
            ], 429);
        }

        $employeeId = (string) $request->input('employee_id');
        $user = User::where('employee_id', $employeeId)->first();

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
        $request->session()->regenerate();

        activity()
            ->causedBy($request->user())
            ->useLog('การเข้าใช้งาน')
            ->withProperties(['ip' => $request->ip()])
            ->log('ผู้ใช้เข้าสู่ระบบ');

        $redirect = route($user->defaultDashboardRoute() ?? 'home', [], false);

        return response()->json(['redirect' => $redirect]);
    }

    public function logout(Request $request)
    {
        activity()
            ->causedBy($request->user())
            ->useLog('การเข้าใช้งาน')
            ->withProperties(['ip' => $request->ip()])
            ->log('ผู้ใช้ออกจากระบบ');

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
