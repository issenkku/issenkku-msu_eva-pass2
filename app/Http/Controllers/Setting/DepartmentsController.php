<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting\Departments;
use Illuminate\Http\Request;

class DepartmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Departments::paginate(5);

        return view('departments.index', compact('departments'));
        // --- IGNORE ---
        // return view('index', ['departments' => $departments]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_name' => 'required|string|max:255',
            //'faculty' => 'required|string|max:255',
        ]);

        // ตรวจสอบชื่อภาควิชาซ้ำ
        $existingDepartment = Departments::where('department_name', $request->department_name)->first();

        if ($existingDepartment) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['department_name' => 'ชื่อแผนกนี้มีอยู่แล้วในระบบ กรุณาใช้ชื่ออื่น']);
        }

        Departments::create([
            'department_name' => $request->department_name,
            //'faculty' => $request->faculty,
        ]);

        return redirect()->route('departments.index')->with('success', 'เพิ่มข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'department_name' => 'required|string|max:255',
            //'faculty' => 'required|string|max:255',
        ]);

        // ตรวจสอบชื่อภาควิชาซ้ำ (ยกเว้นตัวเอง)
        $existingDepartment = Departments::where('department_name', $request->department_name)
            ->where('id', '!=', $id)
            ->first();

        if ($existingDepartment) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['department_name' => 'ชื่อภาควิชานี้มีอยู่แล้วในระบบ กรุณาใช้ชื่ออื่น']);
        }

        $department = Departments::findOrFail($id);
        $department->update([
            'department_name' => $request->department_name,
            //'faculty' => $request->faculty,
        ]);

        return redirect()->route('departments.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $department = Departments::findOrFail($id);
        $department->delete();

        return redirect()->route('departments.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
