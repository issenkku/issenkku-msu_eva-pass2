<?php

namespace App\Http\Controllers\Setting;


use App\Http\Controllers\Controller;
use App\Models\Setting\Departments;
use Illuminate\Http\Request;

class DepartmentsController extends Controller
{
    /**
     * เมธอด: index
     * จุดประสงค์: แสดงหน้า departments.index
     * อินพุต: ไม่มี
     * เอาต์พุต: หน้า departments.index
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function index()
    {
        $departments = Departments::paginate(10);

        return view('departments.index', compact('departments'));
        // --- IGNORE ---
        // return view('index', ['departments' => $departments]);
    }

    /**
     * เมธอด: store
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ บันทึกข้อมูล Departments และเปลี่ยนเส้นทางไปที่ route departments.index
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: Redirect ไปที่ route departments.index
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_name' => 'required|string|max:255',
            // 'faculty' => 'required|string|max:255',
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
            // 'faculty' => $request->faculty,
        ]);

        return redirect()->route('departments.index')->with('success', 'เพิ่มข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * เมธอด: update
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ อัปเดตข้อมูล และเปลี่ยนเส้นทางไปที่ route departments.index
     * อินพุต: ข้อมูลจากคำขอ, ตัวระบุ ($id)
     * เอาต์พุต: Redirect ไปที่ route departments.index
     * @param Request $request ค่าที่รับเข้ามา
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'department_name' => 'required|string|max:255',
            // 'faculty' => 'required|string|max:255',
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
            // 'faculty' => $request->faculty,
        ]);

        return redirect()->route('departments.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * เมธอด: destroy
     * จุดประสงค์: ลบข้อมูล และเปลี่ยนเส้นทางไปที่ route departments.index
     * อินพุต: ตัวระบุ ($id)
     * เอาต์พุต: Redirect ไปที่ route departments.index
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function destroy($id)
    {
        $department = Departments::findOrFail($id);
        $department->delete();

        return redirect()->route('departments.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
