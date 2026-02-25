<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * เมธอด: create
     * จุดประสงค์: แสดงหน้า auth/Register
     * อินพุต: ไม่มี
     * เอาต์พุต: หน้า auth/Register
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * เมธอด: store
     * จุดประสงค์: ดำเนินการเข้าสู่ระบบ/ยืนยันตัวตน ตรวจสอบข้อมูลจากคำขอ บันทึกข้อมูล User, Registered
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
