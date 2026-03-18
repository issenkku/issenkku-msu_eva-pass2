<?php

namespace App\Http\Controllers\Settings;


use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * เมธอด: show
     * จุดประสงค์: แสดงหน้า user.profile.show-profile
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: หน้า user.profile.show-profile
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function show(Request $request)
    {
        $user = $request->user()->load('position', 'department', 'roles');

        return view('user.profile.show-profile', compact('user'));
    }

    /**
     * เมธอด: showPublic
     * จุดประสงค์: แสดงหน้า user.profile.show-profile-public
     * อินพุต: ตัวระบุ ($uuid)
     * เอาต์พุต: หน้า user.profile.show-profile-public
     * @param mixed $uuid ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function showPublic($uuid)
    {
        $user = User::where('public_profile_uuid', $uuid)
            ->where('is_public_profile_enabled', true)
            ->firstOrFail();

        $user = $user->load('position', 'department', 'roles');

        return view('user.profile.show-profile-public', compact('user'));
    }

    /**
     * เมธอด: edit
     * จุดประสงค์: แสดงหน้า user.profile.edit-profile
     * อินพุต: ไม่มี
     * เอาต์พุต: หน้า user.profile.edit-profile
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function edit()
    {
        $user = auth()->user();
        $positions = Positions::all(); // Your positions
        $departments = Departments::all(); // Your departments

        return view('user.profile.edit-profile', compact('user', 'positions', 'departments'));
    }

    /**
     * เมธอด: update
     * จุดประสงค์: บันทึกข้อมูล ลบข้อมูล และเปลี่ยนเส้นทางไปที่ route profile.show
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: Redirect ไปที่ route profile.show
     * @param ProfileUpdateRequest $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $validated['education_history'] = $this->normalizeEducationHistory($validated['education_history'] ?? []);
        $validated['bio'] = $this->buildEducationBio($validated['education_history'], $validated['bio'] ?? null);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Store new photo
            $photoPath = $request->file('profile_photo')->store('profile-photos', 'public');
            $validated['profile_photo_path'] = $photoPath;
        }

        // Remove the profile_photo from validated data as we handle it separately
        unset($validated['profile_photo']);

        // Handle password update
        if ($request->filled('current_password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง']);
            }

            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request->password);
            }
        }

        // Remove password fields if not updating password
        if (! $request->filled('password')) {
            unset($validated['current_password'], $validated['password'], $validated['password_confirmation']);
        }

        // Fill and save user data
        $user->fill($validated);

        // Check if email was changed
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('profile.show')->with('success', 'โปรไฟล์ได้รับการอัปเดตเรียบร้อยแล้ว');
    }

    private function normalizeEducationHistory(array $entries): ?array
    {
        $normalized = collect($entries)
            ->filter(fn ($entry) => is_array($entry))
            ->map(fn (array $entry) => [
                'graduation_year' => filled($entry['graduation_year'] ?? null) ? (string) $entry['graduation_year'] : null,
                'degree' => filled($entry['degree'] ?? null) ? trim((string) $entry['degree']) : null,
                'university' => filled($entry['university'] ?? null) ? trim((string) $entry['university']) : null,
            ])
            ->filter(fn (array $entry) => filled($entry['graduation_year']) || filled($entry['degree']) || filled($entry['university']))
            ->values()
            ->all();

        return $normalized === [] ? null : $normalized;
    }

    private function buildEducationBio(?array $educationHistory, ?string $fallbackBio = null): ?string
    {
        if (!empty($educationHistory)) {
            return collect($educationHistory)
                ->map(function (array $entry) {
                    return collect([
                        $entry['graduation_year'] ?? null,
                        $entry['degree'] ?? null,
                        $entry['university'] ?? null,
                    ])->filter(fn ($value) => filled($value))->implode(' ');
                })
                ->filter(fn ($line) => filled($line))
                ->implode(PHP_EOL);
        }

        return filled($fallbackBio) ? trim($fallbackBio) : null;
    }

    /**
     * เมธอด: destroy
     * จุดประสงค์: ดำเนินการออกจากระบบ ตรวจสอบข้อมูลจากคำขอ ลบข้อมูล
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * เมธอด: sendPasswordResetLink
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ และย้อนกลับหน้าก่อน
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ย้อนกลับหน้าก่อน
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function sendPasswordResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'ลิงก์รีเซ็ตรหัสผ่านถูกส่งไปยังอีเมลของคุณแล้ว');
        }

        return back()->with('error', 'เกิดข้อผิดพลาดในการส่งอีเมล กรุณาลองใหม่อีกครั้ง');
    }
}
