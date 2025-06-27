<?php

namespace App\Http\Controllers;

use App\Models\Departments;
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
            'faculty' => 'required|string|max:255',
        ]);

        Departments::create([
            'department_name' => $request->department_name,
            'faculty' => $request->faculty,
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
            'faculty' => 'required|string|max:255',
        ]);

        $department = Departments::findOrFail($id);
        $department->update([
            'department_name' => $request->department_name,
            'faculty' => $request->faculty,
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
