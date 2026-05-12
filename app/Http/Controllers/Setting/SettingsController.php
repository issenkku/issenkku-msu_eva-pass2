<?php

namespace App\Http\Controllers\Setting;


use App\Http\Controllers\Controller;
use App\Models\Setting\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * เมธอด: index
     * จุดประสงค์: แสดงหน้า settings.index
     * อินพุต: ไม่มี
     * เอาต์พุต: หน้า settings.index
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function index()
    {
        $setting = Settings::first();

        return view('settings.index', compact('setting'));
        // --- IGNORE ---
        // return view('indexSettings', ['settings' => $settings]);
    }

    /**
     * เมธอด: store
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ อัปเดตข้อมูล และเปลี่ยนเส้นทางไปที่ route settings.index
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: Redirect ไปที่ route settings.index
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function store(Request $request)
    {
        $request->validate([
            'university' => [
                'required',
                'string',
                'max:255',
                'regex:/^[ก-๙a-zA-Z\s]+$/u', // ตรวจสอบว่าเป็นภาษาไทย เว้นวรรค เท่านั้น
            ],
            'faculty' => [
                'required',
                'string',
                'max:255',
                'regex:/^[ก-๙a-zA-Z\s]+$/u', // ตรวจสอบว่าเป็นภาษาไทย เว้นวรรค เท่านั้น
            ],
            'notification_days' => [
                'required',
                'integer',
                'min:1',
                'max:30',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,svg',
                'max:2048',
            ],
            'remove_logo' => [
                'nullable',
                'boolean',
            ],
        ], [
            // ข้อความแจ้งเตือนแบบกำหนดเอง
            'university.regex' => 'ชื่อมหาวิทยาลัยต้องเป็นภาษาไทยหรืออังกฤษเท่านั้น ห้ามใช้อักษรพิเศษหรือตัวเลข',
            'faculty.regex' => 'ชื่อคณะต้องเป็นภาษาไทยหรืออังกฤษเท่านั้น ห้ามใช้อักษรพิเศษหรือตัวเลข',
            'university.required' => 'กรุณากรอกชื่อมหาวิทยาลัย',
            'faculty.required' => 'กรุณากรอกชื่อคณะ',
            'university.max' => 'ชื่อมหาวิทยาลัยต้องไม่เกิน 255 ตัวอักษร',
            'faculty.max' => 'ชื่อคณะต้องไม่เกิน 255 ตัวอักษร',
            'notification_days.required' => 'กรุณาระบุจำนวนวันแจ้งเตือน',
            'notification_days.integer' => 'จำนวนวันแจ้งเตือนต้องเป็นตัวเลขเท่านั้น',
            'notification_days.min' => 'จำนวนวันแจ้งเตือนต้องไม่น้อยกว่า 1 วัน',
            'notification_days.max' => 'จำนวนวันแจ้งเตือนต้องไม่เกิน 30 วัน',
            'logo.image' => 'ไฟล์โลโก้ต้องเป็นรูปภาพเท่านั้น',
            'logo.mimes' => 'โลโก้ต้องเป็นไฟล์ jpg, jpeg, png, webp หรือ svg เท่านั้น',
            'logo.max' => 'ขนาดโลโก้ต้องไม่เกิน 2MB',
        ]);

        // เช็คเพิ่มเติมด้วย PHP function (สำรอง)
        if (! $this->isThaiOrEnglish($request->university)) {
            return redirect()->back()
                ->withErrors(['university' => 'ชื่อมหาวิทยาลัยต้องเป็นภาษาไทยหรืออังกฤษเท่านั้น ห้ามใช้อักษรพิเศษ'])
                ->withInput();
        }

        if (! $this->isThaiOrEnglish($request->faculty)) {
            return redirect()->back()
                ->withErrors(['faculty' => 'ชื่อคณะต้องเป็นภาษาไทยหรืออังกฤษเท่านั้น ห้ามใช้อักษรพิเศษ'])
                ->withInput();
        }

        $data = $request->only(['university', 'faculty', 'notification_days']);

        if ($request->has('id')) {
            // อัปเดตข้อมูลเดิม
            $setting = Settings::findOrFail($request->id);

            if ($request->hasFile('logo')) {
                $data['logo_path'] = $this->storeLogo($request, $setting);
            } elseif ($request->boolean('remove_logo')) {
                $this->deleteLogo($setting);
                $data['logo_path'] = null;
            }

            $setting->update($data);
            $message = 'อัปเดตข้อมูลสำเร็จ!';
        } else {
            // สร้างข้อมูลใหม่ หรือ upsert
            $setting = Settings::first();

            if ($request->hasFile('logo')) {
                $data['logo_path'] = $this->storeLogo($request, $setting);
            } elseif ($request->boolean('remove_logo') && $setting) {
                $this->deleteLogo($setting);
                $data['logo_path'] = null;
            }

            Settings::updateOrCreate(
                ['id' => 1], // เงื่อนไขค้นหา
                $data
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

    private function storeLogo(Request $request, ?Settings $setting = null): string
    {
        $this->deleteLogo($setting);

        return $request->file('logo')->store('site-logos', 'public');
    }

    private function deleteLogo(?Settings $setting = null): void
    {
        if ($setting?->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
            Storage::disk('public')->delete($setting->logo_path);
        }
    }
}
