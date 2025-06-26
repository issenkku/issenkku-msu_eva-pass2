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
        $departments = Departments::all();
        return view('departments', compact('departments'));
        // --- IGNORE ---
        // return view('index', ['departments' => $departments]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
      $departments = Departments::all();
    return view('createDepartments', compact('departments'));
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_name' => 'required|string|max:255|',
            'faculty_id' => 'required|exists:faculties,id',
        ], [
            'department_name.regex' => 'กรุณากรอกเฉพาะตัวอักษรเท่านั้น (ไม่อนุญาตให้มีตัวเลข)',
        ]);
        Departments::create($request->only('department_name', 'faculty_id'));
        return redirect()->route('index')->with('success', 'Post created successfully!');

    }

    /**
     * Display the specified resource.
     */
    public function show(Departments $departments)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Departments $departments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Departments $departments)
    {
        $request->validate([
            'department_name' => 'required|string|max:255',
            'faculty_id' => 'required|exists:faculties,id',
        ], [
            'department_name.regex' => 'กรุณากรอกเฉพาะตัวอักษรเท่านั้น (ไม่อนุญาตให้มีตัวเลข)',
        ]);

        $departments->update($request->only('department_name', 'faculty_id'));
        return redirect()->route('index')->with('success', ' updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Departments $departments)
    {
         $department = Departments::findOrFail($id);
    $department->delete();

    return redirect()->route('index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
