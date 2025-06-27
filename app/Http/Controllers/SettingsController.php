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
            'university' => 'required|string|max:255',
            'faculty' => 'required|string|max:255',
        ]);

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
}
