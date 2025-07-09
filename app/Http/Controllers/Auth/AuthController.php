<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
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

        $key = Str::lower($request->input('employee_id')) . '|' . $request->ip();
        $maxAttempts = 5;
        $decaySeconds = 60;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'คุณพยายามเข้าสู่ระบบมากเกินไป กรุณารอ 1 นาทีแล้วลองใหม่อีกครั้ง.'
            ], 429);
        }

        $user = User::where('employee_id', $request->employee_id)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, $decaySeconds);
            return response()->json([
                'message' => 'กรุณากรอกหมายเลขประจำตัวและรหัสผ่านให้ถูกต้อง'
            ], 401);
        }

        RateLimiter::clear($key);

        Auth::login($user);
        $request->session()->regenerate(); // prevent session fixation

        $redirectUrl = $this->getRedirectUrlForUser($user);
    
        return redirect($redirectUrl);
    }

    private function getRedirectUrlForUser($user)
    {
        $redirectRules = [
            ['type' => 'role', 'name' => 'admin', 'url' => '/users'],
            
            ['type' => 'permission', 'name' => 'Employee Management', 'url' => '/users'],
            
            ['type' => 'role', 'name' => 'ผู้บริหาร', 'url' => '/dashboard'],
            ['type' => 'role', 'name' => 'ผู้ประเมิน', 'url' => '/dashboard'],
            
            ['type' => 'role', 'name' => 'ผู้รับการประเมิน', 'url' => '/dashboard'],
            ['type' => 'permission', 'name' => 'Employee Dashboard', 'url' => '/dashboard'],
        ];
        
        // Check each rule in order
        foreach ($redirectRules as $rule) {
            if ($rule['type'] === 'role' && $user->hasRole($rule['name'])) {
                return $rule['url'];
            } elseif ($rule['type'] === 'permission' && $user->hasPermissionTo($rule['name'])) {
                return $rule['url'];
            }
        }
        
        // Default fallback
        return '/dashboard';
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
