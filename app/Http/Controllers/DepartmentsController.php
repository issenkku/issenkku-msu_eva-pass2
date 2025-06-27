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
        return view('createDepartments', compact('departments'));
        // --- IGNORE ---
        // return view('index', ['departments' => $departments]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
      $departments = Departments::paginate(5);
   return view('createDepartments', compact('departments'));

       
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

    return redirect()->route('create')->with('success', 'เพิ่มข้อมูลเรียบร้อยแล้ว');
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

    return redirect()->route('create')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
}



    /**
     * Remove the specified resource from storage.
     */
public function destroy($id)
{
    $department = Departments::findOrFail($id);
    $department->delete();

    return redirect()->route('create')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
}


}
