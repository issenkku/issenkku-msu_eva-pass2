<?php
/**
 * ไฟล์คอนโทรลเลอร์: app/Http/Controllers\Auth\AuthController.php
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * เมธอด: showLoginForm
     * จุดประสงค์: แสดงหน้า user.management.loginForm
     * อินพุต: ไม่มี
     * เอาต์พุต: หน้า user.management.loginForm
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function showLoginForm()
    {
        return view('user.management.loginForm'); // Blade view for login form
    }

    /**
     * เมธอด: login
     * จุดประสงค์: ดำเนินการเข้าสู่ระบบ/ยืนยันตัวตน ตรวจสอบข้อมูลจากคำขอ ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ข้อมูล JSON
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function login(Request $request)
    {

        $credentials = $request->validate([
            'employee_id' => ['required'],
            'password' => ['required'],
        ]);

        $normalizedEmployeeId = Str::lower(trim((string) $request->input('employee_id')));
        $key = $normalizedEmployeeId . '|' . $request->ip();
        $maxAttempts = 5;
        $decaySeconds = 60;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'คุณพยายามเข้าสู่ระบบมากเกินไป กรุณารอ 1 นาทีแล้วลองใหม่อีกครั้ง.',
            ], 429);
        }

        // Case-insensitive, trimmed lookup for employee_id
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
        $request->session()->regenerate(); // prevent session fixation

        activity()
            ->causedBy($request->user()) // who did it
            ->useLog('การเข้าใช้งาน')
            ->withProperties(['ip' => $request->ip()])
            ->log("ผู้ใช้เข้าสู่ระบบ");

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

    /**
     * เมธอด: logout
     * จุดประสงค์: ดำเนินการออกจากระบบ
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function logout(Request $request)
    {
        activity()
            ->causedBy($request->user()) // who did it
            ->useLog('การเข้าใช้งาน')
            ->withProperties(['ip' => $request->ip()])
            ->log("ผู้ใช้ออกจากระบบ");

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();



        return redirect('/login')->with('success', 'ออกจากระบบสำเร็จ');
    }

    /**
     * เมธอด: user
     * จุดประสงค์: แสดงหน้า auth.profile
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: หน้า auth.profile
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function user(Request $request)
    {
        return view('auth.profile', ['user' => $request->user()]);
    }
}
