<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\Setting\Department;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */

    public function show(Request $request)
    {
        $user = $request->user()->load('position', 'department','roles');

        return view('user.profile.show-profile', compact('user'));
    }

    public function edit()
    {
        $user = auth()->user();
        $positions = Position::all(); // Your positions
        $departments = Department::all(); // Your departments
        
        return view('user.profile.edit-profile', compact('user', 'positions', 'departments'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Handle photo upload
        // if ($request->hasFile('photo')) {
        //     // Delete old photo if exists
        //     if ($user->photo_url && Storage::disk('public')->exists($user->photo_url)) {
        //         Storage::disk('public')->delete($user->photo_url);
        //     }

        //     // Store new photo
        //     $photoPath = $request->file('photo')->store('profile-photos', 'public');
        //     $validated['photo_url'] = $photoPath;
        // }

        // Handle password update
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง']);
            }
            
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request->password);
            }
        }

        // Remove password fields if not updating password
        if (!$request->filled('password')) {
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

    /**
     * Delete the user's profile.
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
