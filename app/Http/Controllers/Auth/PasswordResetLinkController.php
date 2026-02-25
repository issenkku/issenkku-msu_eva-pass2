<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    /**
     * เมธอด: create
     * จุดประสงค์: แสดงหน้า user.management.forgotPassword
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: หน้า user.management.forgotPassword
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function create(Request $request)
    {
        // Serve a Blade fallback to avoid blank page when Vite/Inertia assets are unavailable
        return view('user.management.forgotPassword', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * เมธอด: store
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ และย้อนกลับหน้าก่อน
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ย้อนกลับหน้าก่อน
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        Password::sendResetLink(
            $request->only('email')
        );

        return back()->with('status', __('เราได้ส่งลิงก์รีเซ็ตรหัสผ่านไปยังอีเมลของคุณแล้ว'));
    }
}

