<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting\Positions;
use Illuminate\Http\Request;

class PositionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $positions = Positions::paginate(5);
        return view('positions.index', compact('positions'));
        // --- IGNORE ---
        // return view('index', ['positions' => $positions]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        // ตรวจสอบชื่อตำแหน่งซ้ำ
        $existingPosition = Positions::where('name', $request->name)->first();
        
        if ($existingPosition) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'ชื่อตำแหน่งนี้มีอยู่แล้วในระบบ กรุณาใช้ชื่ออื่น']);
        }

        Positions::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        
        return redirect()->route('positions.index')->with('success', 'เพิ่มข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        // ตรวจสอบชื่อตำแหน่งซ้ำ (ยกเว้นตัวเอง)
        $existingPosition = Positions::where('name', $request->name)
            ->where('id', '!=', $id)
            ->first();
        
        if ($existingPosition) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'ชื่อตำแหน่งนี้มีอยู่แล้วในระบบ กรุณาใช้ชื่ออื่น']);
        }

        $positions = Positions::findOrFail($id);
        $positions->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        
        return redirect()->route('positions.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $positions = Positions::findOrFail($id);
        $positions->delete();
        return redirect()->route('positions.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
        // --- IGNORE ---
        // return redirect()->route('index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}