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
    public function index(Request $request)
    {
        $sort = $request->input('sort', 'latest');
        $usage = $request->input('usage');

        $departments = Departments::query()
            ->withCount('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where('department_name', 'like', "%{$search}%");
            })
            ->when($usage === 'used', fn ($query) => $query->has('user'))
            ->when($usage === 'unused', fn ($query) => $query->doesntHave('user'));

        match ($sort) {
            'name_asc' => $departments->orderBy('department_name'),
            'name_desc' => $departments->orderByDesc('department_name'),
            'most_users' => $departments->orderByDesc('user_count')->orderBy('department_name'),
            'least_users' => $departments->orderBy('user_count')->orderBy('department_name'),
            'oldest' => $departments->orderBy('id'),
            default => $departments->orderByDesc('id'),
        };

        $departments = $departments->paginate(10)->withQueryString();

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

        $userCount = $department->user()->count();

        if ($userCount > 0) {
            return redirect()->route('departments.index')->with(
                'error',
                "ไม่สามารถลบหน่วยงาน {$department->department_name} ได้ เนื่องจากยังมีการผูกกับผู้ใช้ {$userCount} รายการ"
            );
        }

        $department->delete();

        return redirect()->route('departments.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
