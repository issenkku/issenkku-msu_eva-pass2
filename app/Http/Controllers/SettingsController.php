<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $setting = Settings::first();
        return view('settings.index', compact('setting'));
        // --- IGNORE ---
        // return view('indexSettings', ['settings' => $settings]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'university' => [
                'required',
                'string',
                'max:255',
                'regex:/^[ก-๙a-zA-Z\s]+$/u' // ตรวจสอบว่าเป็นภาษาไทย เว้นวรรค เท่านั้น
            ],
            'faculty' => [
                'required',
                'string',
                'max:255',
                'regex:/^[ก-๙a-zA-Z\s]+$/u' // ตรวจสอบว่าเป็นภาษาไทย เว้นวรรค เท่านั้น
            ],
        ], [
            // ข้อความแจ้งเตือนแบบกำหนดเอง
            'university.regex' => 'ชื่อมหาวิทยาลัยต้องเป็นภาษาไทยหรืออังกฤษเท่านั้น ห้ามใช้อักษรพิเศษหรือตัวเลข',
            'faculty.regex' => 'ชื่อคณะต้องเป็นภาษาไทยหรืออังกฤษเท่านั้น ห้ามใช้อักษรพิเศษหรือตัวเลข',
            'university.required' => 'กรุณากรอกชื่อมหาวิทยาลัย',
            'faculty.required' => 'กรุณากรอกชื่อคณะ',
            'university.max' => 'ชื่อมหาวิทยาลัยต้องไม่เกิน 255 ตัวอักษร',
            'faculty.max' => 'ชื่อคณะต้องไม่เกิน 255 ตัวอักษร'
        ]);

        // เช็คเพิ่มเติมด้วย PHP function (สำรอง)
        if (!$this->isThaiOrEnglish($request->university)) {
            return redirect()->back()
                ->withErrors(['university' => 'ชื่อมหาวิทยาลัยต้องเป็นภาษาไทยหรืออังกฤษเท่านั้น ห้ามใช้อักษรพิเศษ'])
                ->withInput();
        }

        if (!$this->isThaiOrEnglish($request->faculty)) {
            return redirect()->back()
                ->withErrors(['faculty' => 'ชื่อคณะต้องเป็นภาษาไทยหรืออังกฤษเท่านั้น ห้ามใช้อักษรพิเศษ'])
                ->withInput();
        }

        if ($request->has('id')) {
            // อัปเดตข้อมูลเดิม
            $setting = Settings::findOrFail($request->id);
            $setting->update($request->only(['university', 'faculty']));
            $message = 'อัปเดตข้อมูลสำเร็จ!';
        } else {
            // สร้างข้อมูลใหม่ หรือ upsert
            Settings::updateOrCreate(
                ['id' => 1], // เงื่อนไขค้นหา
                $request->only(['university', 'faculty'])
            );
            $message = 'บันทึกข้อมูลสำเร็จ!';
        }

        return redirect()->route('settings.index')->with('success', $message);
    }

    /**
     * ตรวจสอบว่าข้อความเป็นภาษาไทยเท่านั้น
     */
    private function isThaiOrEnglish($text)
{
    // ตรวจสอบว่าเป็นภาษาไทย, ภาษาอังกฤษ และช่องว่างเท่านั้น
    return preg_match('/^[ก-๙a-zA-Z\s]+$/u', $text);
}
}