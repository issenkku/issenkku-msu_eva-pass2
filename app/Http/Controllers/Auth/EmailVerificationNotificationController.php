<?php
/**
 * ไฟล์คอนโทรลเลอร์: app/Http/Controllers\Auth\EmailVerificationNotificationController.php
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * เมธอด: store
     * จุดประสงค์: และเปลี่ยนเส้นทางไปที่ route dashboard
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: Redirect ไปที่ route dashboard
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
