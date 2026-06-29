<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $setting = Settings::first();
        $backgroundAssets = $this->getBackgroundAssets();

        return view('settings.index', compact('setting', 'backgroundAssets'));
        // --- IGNORE ---
    }

    public function store(Request $request)
    {
        $existingSetting = $request->has('id')
            ? Settings::findOrFail($request->id)
            : Settings::first();
        $nameRequirement = $existingSetting ? 'sometimes' : 'required';

        $request->validate([
            'university' => [
                $nameRequirement,
                'required',
                'string',
                'max:255',
                'regex:/^[ก-๙a-zA-Z\s]+$/u', // ตรวจสอบว่าเป็นภาษาไทย เว้นวรรค เท่านั้น
            ],
            'faculty' => [
                $nameRequirement,
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
            'background' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'selected_background_path' => [
                'nullable',
                'string',
            ],
            'delete_background_paths' => [
                'nullable',
                'array',
            ],
            'delete_background_paths.*' => [
                'nullable',
                'string',
            ],
            'remove_background' => [
                'nullable',
                'boolean',
            ],
            'use_white_background' => [
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
            'background.uploaded' => 'อัปโหลดรูปพื้นหลังไม่สำเร็จ กรุณาใช้ไฟล์ไม่เกิน 4MB และตรวจสอบค่า upload_max_filesize/post_max_size ของ PHP',
            'background.image' => 'ไฟล์พื้นหลังต้องเป็นรูปภาพเท่านั้น',
            'background.mimes' => 'พื้นหลังต้องเป็นไฟล์ jpg, jpeg, png หรือ webp เท่านั้น',
            'background.max' => 'ขนาดพื้นหลังต้องไม่เกิน 4MB',
        ]);

        // เช็คเพิ่มเติมด้วย PHP function (สำรอง)
        if ($request->has('university') && ! $this->isThaiOrEnglish($request->university)) {
            return redirect()->back()
                ->withErrors(['university' => 'ชื่อมหาวิทยาลัยต้องเป็นภาษาไทยหรืออังกฤษเท่านั้น ห้ามใช้อักษรพิเศษ'])
                ->withInput();
        }

        if ($request->has('faculty') && ! $this->isThaiOrEnglish($request->faculty)) {
            return redirect()->back()
                ->withErrors(['faculty' => 'ชื่อคณะต้องเป็นภาษาไทยหรืออังกฤษเท่านั้น ห้ามใช้อักษรพิเศษ'])
                ->withInput();
        }

        $data = $request->only(['university', 'faculty', 'notification_days']);
        $data['use_white_background'] = $request->boolean('use_white_background');
        $selectedBackgroundPath = $request->input('selected_background_path');
        $deleteBackgroundPaths = collect($request->input('delete_background_paths', []))
            ->filter()
            ->unique()
            ->values();

        if ($selectedBackgroundPath && ! $this->isValidBackgroundPath($selectedBackgroundPath)) {
            return redirect()->back()
                ->withErrors(['selected_background_path' => 'รูปพื้นหลังที่เลือกไม่มีอยู่ในระบบ'])
                ->withInput();
        }

        if ($deleteBackgroundPaths->contains(fn ($path) => ! $this->isValidBackgroundPath($path))) {
            return redirect()->back()
                ->withErrors(['delete_background_paths' => 'ไม่สามารถลบรูปพื้นหลังที่เลือกได้'])
                ->withInput();
        }

        if ($request->has('id')) {
            // อัปเดตข้อมูลเดิม
            $setting = $existingSetting;
            $isCurrentBackgroundDeleted = $deleteBackgroundPaths->contains($setting->background_path);

            if ($request->hasFile('logo')) {
                $data['logo_path'] = $this->storeLogo($request, $setting);
            } elseif ($request->boolean('remove_logo')) {
                $this->deleteLogo($setting);
                $data['logo_path'] = null;
            }

            if ($request->hasFile('background')) {
                $data['background_path'] = $this->storeBackground($request, $setting);
            } elseif ($selectedBackgroundPath && ! $deleteBackgroundPaths->contains($selectedBackgroundPath)) {
                $data['background_path'] = $selectedBackgroundPath;
            } elseif ($request->boolean('remove_background') || $isCurrentBackgroundDeleted) {
                $this->deleteBackground($setting);
                $data['background_path'] = null;
            }

            $setting->update($data);
            $this->deleteBackgroundFiles($deleteBackgroundPaths->all());
            $message = 'อัปเดตข้อมูลสำเร็จ!';
        } else {
            // สร้างข้อมูลใหม่ หรือ upsert
            $setting = $existingSetting;
            $isCurrentBackgroundDeleted = $setting && $deleteBackgroundPaths->contains($setting->background_path);

            if ($request->hasFile('logo')) {
                $data['logo_path'] = $this->storeLogo($request, $setting);
            } elseif ($request->boolean('remove_logo') && $setting) {
                $this->deleteLogo($setting);
                $data['logo_path'] = null;
            }

            if ($request->hasFile('background')) {
                $data['background_path'] = $this->storeBackground($request, $setting);
            } elseif ($selectedBackgroundPath && ! $deleteBackgroundPaths->contains($selectedBackgroundPath)) {
                $data['background_path'] = $selectedBackgroundPath;
            } elseif (($request->boolean('remove_background') && $setting) || $isCurrentBackgroundDeleted) {
                $this->deleteBackground($setting);
                $data['background_path'] = null;
            }

            Settings::updateOrCreate(
                ['id' => 1], // เงื่อนไขค้นหา
                $data
            );
            $this->deleteBackgroundFiles($deleteBackgroundPaths->all());
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

    private function storeBackground(Request $request, ?Settings $setting = null): string
    {
        $file = $request->file('background');
        $fileName = $this->makeBackgroundFileName($file->getClientOriginalExtension());

        return $file->storeAs('site-backgrounds', $fileName, 'public');
    }

    private function deleteBackground(?Settings $setting = null): void
    {
        // Keep uploaded backgrounds in the library so admins can select them again later.
    }

    private function deleteBackgroundFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if ($this->isValidBackgroundPath($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function getBackgroundAssets(): array
    {
        return collect(Storage::disk('public')->files('site-backgrounds'))
            ->filter(fn ($path) => $this->isSupportedBackgroundFile($path))
            ->sortDesc()
            ->map(fn ($path) => [
                'path' => $path,
                'name' => basename($path),
                'url' => asset('storage/'.$path),
            ])
            ->values()
            ->all();
    }

    private function isValidBackgroundPath(?string $path): bool
    {
        return is_string($path)
            && str_starts_with($path, 'site-backgrounds/')
            && Storage::disk('public')->exists($path)
            && $this->isSupportedBackgroundFile($path);
    }

    private function isSupportedBackgroundFile(string $path): bool
    {
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true);
    }

    private function makeBackgroundFileName(string $extension): string
    {
        $baseName = $this->makeSafeFileName(config('app.name', 'msu-eva'));
        $timestamp = now()->format('Ymd-His');
        $suffix = bin2hex(random_bytes(2));

        return "{$baseName}-background-{$timestamp}-{$suffix}.".strtolower($extension);
    }

    private function makeSafeFileName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/[^\pL\pN]+/u', '-', $name);
        $name = trim($name, '-');
        $name = $name === '' ? 'msu-eva' : $name;
        $name = function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : strtolower($name);

        return function_exists('mb_substr') ? mb_substr($name, 0, 70, 'UTF-8') : substr($name, 0, 70);
    }
}
