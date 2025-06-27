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

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     $settings = Settings::all();
    //     return view('createSettings', compact('settings'));
    //     return redirect()->route('index')->with('last_settings', $request->only(['faculty', 'university']));

    // }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'faculty' => 'required|string|max:255',
    //         'university' => 'required|string|max:255',
    //     ]);

    //     $data = Settings::create([
    //         'faculty' => $request->faculty,
    //         'university' => $request->university,
    //     ]);

    //     // ส่งค่าที่บันทึกล่าสุดไปหน้า createSettings
    //     return redirect()
    //         ->route('settings.index')
    //         ->with('last', $data)
    //         ->with('success', 'Settings created successfully');
    // }
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
    /**
     * Display the specified resource.
     */
    public function show(Settings $settings)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Settings $settings)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Settings $settings)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Settings $settings)
    {
        //
    }
}
