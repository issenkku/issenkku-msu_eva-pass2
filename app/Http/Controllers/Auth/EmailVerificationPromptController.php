<?php
/**
 * ไฟล์คอนโทรลเลอร์: app/Http/Controllers\Auth\EmailVerificationPromptController.php
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * เมธอด: __invoke
     * จุดประสงค์: แสดงหน้า auth/VerifyEmail
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: หน้า auth/VerifyEmail
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard', absolute: false))
                    : Inertia::render('auth/VerifyEmail', ['status' => $request->session()->get('status')]);
    }
}
