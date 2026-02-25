<?php
/**
 * ไฟล์คอนโทรลเลอร์: app/Http/Controllers\Settings\PasswordController.php
 */

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    /**
     * เมธอด: edit
     * จุดประสงค์: แสดงหน้า settings/Password
     * อินพุต: ไม่มี
     * เอาต์พุต: หน้า settings/Password
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function edit(): Response
    {
        return Inertia::render('settings/Password');
    }

    /**
     * เมธอด: update
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ อัปเดตข้อมูล และย้อนกลับหน้าก่อน
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ย้อนกลับหน้าก่อน
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }
}
