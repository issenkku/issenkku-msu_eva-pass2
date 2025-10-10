<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class AuthController extends Controller
{
    /**
     * แสดงหน้าแบบฟอร์มเข้าสู่ระบบ
     */
    public function showLoginForm()
    {
        return view('user.management.loginForm');
    }

    /**
     * จัดการเข้าสู่ระบบ (Login)
     */
    public function login(Request $request)
    {
        try {
            // ✅ Validate Input
            $credentials = $request->validate([
                'employee_id' => ['required'],
                'password' => ['required'],
            ]);

            // ✅ Normalize Employee ID (ตัดช่องว่าง, แปลงเป็นตัวพิมพ์เล็ก)
            $normalizedEmployeeId = Str::lower(trim((string) $request->input('employee_id')));
            $key = $normalizedEmployeeId . '|' . $request->ip();
            $maxAttempts = 5;
            $decaySeconds = 60;

            // ✅ ตรวจสอบจำนวนครั้งที่พยายามเข้าสู่ระบบ
            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                return response()->json([
                    'message' => 'คุณพยายามเข้าสู่ระบบมากเกินไป กรุณารอ 1 นาทีแล้วลองใหม่อีกครั้ง.',
                ], 429);
            }

            // ✅ ค้นหาผู้ใช้แบบ case-insensitive
            $user = User::whereRaw('LOWER(TRIM(employee_id)) = ?', [$normalizedEmployeeId])->first();

            // ✅ ตรวจสอบรหัสผ่าน
            if (! $user || ! Hash::check($request->password, $user->password)) {
                RateLimiter::hit($key, $decaySeconds);

                // 🔒 Log การพยายามล็อกอินล้มเหลว
                activity()
                    ->useLog('การเข้าใช้งาน')
                    ->withProperties([
                        'employee_id' => $normalizedEmployeeId,
                        'ip' => $request->ip(),
                    ])
                    ->log('พยายามเข้าสู่ระบบแต่ไม่สำเร็จ');

                return response()->json([
                    'message' => 'กรุณากรอกหมายเลขประจำตัวและรหัสผ่านให้ถูกต้อง',
                ], 401);
            }

            // ✅ ตรวจสอบสถานะผู้ใช้
            if ($user->status === 'inactive') {
                return response()->json([
                    'message' => 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ',
                ], 413);
            }

            // ✅ ล้างตัวนับ RateLimiter
            RateLimiter::clear($key);

            // ✅ เข้าสู่ระบบและสร้าง session ใหม่
            Auth::login($user);
            $request->session()->regenerate();

            // ✅ บันทึก Activity Log
            activity()
                ->causedBy($request->user())
                ->useLog('การเข้าใช้งาน')
                ->withProperties(['ip' => $request->ip()])
                ->log('ผู้ใช้เข้าสู่ระบบ');

            // ✅ กำหนด redirect path ตาม role
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

            // ✅ ส่งข้อมูลกลับให้ Frontend
            return response()->json([
                'message' => 'เข้าสู่ระบบสำเร็จ',
                'redirect' => $redirect,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->getRoleNames()->first(),
                    'status' => $user->status,
                ],
            ]);
        } catch (\Throwable $e) {
            // 🧯 บันทึก error log
            report($e);

            return response()->json([
                'message' => 'เกิดข้อผิดพลาดภายในระบบ กรุณาลองใหม่อีกครั้ง',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * ออกจากระบบ (Logout)
     */
    public function logout(Request $request)
    {
        if ($request->user()) {
            activity()
                ->causedBy($request->user())
                ->useLog('การเข้าใช้งาน')
                ->withProperties(['ip' => $request->ip()])
                ->log('ผู้ใช้ออกจากระบบ');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'ออกจากระบบสำเร็จ');
    }

    /**
     * แสดงข้อมูลโปรไฟล์ผู้ใช้ที่ล็อกอินอยู่
     */
    public function user(Request $request)
    {
        return view('auth.profile', ['user' => $request->user()]);
    }
}
