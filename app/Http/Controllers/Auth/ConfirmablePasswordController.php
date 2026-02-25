<?php
/**
 * ไฟล์คอนโทรลเลอร์: app/Http/Controllers\Auth\ConfirmablePasswordController.php
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ConfirmablePasswordController extends Controller
{
    /**
     * เมธอด: show
     * จุดประสงค์: แสดงหน้า auth/ConfirmPassword
     * อินพุต: ไม่มี
     * เอาต์พุต: หน้า auth/ConfirmPassword
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function show(): Response
    {
        return Inertia::render('auth/ConfirmPassword');
    }

    /**
     * เมธอด: store
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ และเปลี่ยนเส้นทางไปที่ route dashboard
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: Redirect ไปที่ route dashboard
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
