<?php

namespace App\Http\Controllers\Setting;


use App\Http\Controllers\Controller;
use App\Models\AssignmentData;
use App\Models\Setting\Positions;
use Illuminate\Http\Request;

class PositionsController extends Controller
{
    /**
     * เมธอด: index
     * จุดประสงค์: แสดงหน้า positions.index
     * อินพุต: ไม่มี
     * เอาต์พุต: หน้า positions.index
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function index(Request $request)
    {
        $sort = $request->input('sort', 'latest');
        $usage = $request->input('usage');

        $positions = Positions::query()
            ->withCount('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where('name', 'like', "%{$search}%");
            })
            ->when($usage === 'used', fn ($query) => $query->has('user'))
            ->when($usage === 'unused', fn ($query) => $query->doesntHave('user'));

        match ($sort) {
            'name_asc' => $positions->orderBy('name'),
            'name_desc' => $positions->orderByDesc('name'),
            'most_users' => $positions->orderByDesc('user_count')->orderBy('name'),
            'least_users' => $positions->orderBy('user_count')->orderBy('name'),
            'oldest' => $positions->orderBy('id'),
            default => $positions->orderByDesc('id'),
        };

        $positions = $positions->paginate(10)->withQueryString();

        return view('positions.index', compact('positions'));
        // --- IGNORE ---
        // return view('index', ['positions' => $positions]);
    }

    /**
     * เมธอด: store
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ บันทึกข้อมูล Positions และเปลี่ยนเส้นทางไปที่ route positions.index
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: Redirect ไปที่ route positions.index
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            // 'description' => 'nullable|string|max:500',
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
            // 'description' => $request->description,
        ]);

        return redirect()->route('positions.index')->with('success', 'เพิ่มข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * เมธอด: update
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ อัปเดตข้อมูล และเปลี่ยนเส้นทางไปที่ route positions.index
     * อินพุต: ข้อมูลจากคำขอ, ตัวระบุ ($id)
     * เอาต์พุต: Redirect ไปที่ route positions.index
     * @param Request $request ค่าที่รับเข้ามา
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            // 'description' => 'nullable|string|max:500',
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
            // 'description' => $request->description,
        ]);

        return redirect()->route('positions.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * เมธอด: destroy
     * จุดประสงค์: ลบข้อมูล และเปลี่ยนเส้นทางไปที่ route positions.index
     * อินพุต: ตัวระบุ ($id)
     * เอาต์พุต: Redirect ไปที่ route positions.index
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function destroy($id)
    {
        $positions = Positions::findOrFail($id);

        $userCount = $positions->user()->count();
        $evaluatorAssignmentCount = AssignmentData::where('evaluator_position_id', $positions->id)->count();
        $evaluateeAssignmentCount = AssignmentData::where('evaluatee_position_id', $positions->id)->count();

        $bindings = [];

        if ($userCount > 0) {
            $bindings[] = "ผู้ใช้ {$userCount} รายการ";
        }

        if ($evaluatorAssignmentCount > 0) {
            $bindings[] = "รอบประเมินในฝั่งผู้ประเมิน {$evaluatorAssignmentCount} รายการ";
        }

        if ($evaluateeAssignmentCount > 0) {
            $bindings[] = "รอบประเมินในฝั่งผู้ถูกประเมิน {$evaluateeAssignmentCount} รายการ";
        }

        if ($bindings !== []) {
            return redirect()->route('positions.index')->with(
                'error',
                "ไม่สามารถลบตำแหน่ง {$positions->name} ได้ เนื่องจากยังมีการผูกกับ ".implode(', ', $bindings)
            );
        }

        $positions->delete();

        return redirect()->route('positions.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
        // --- IGNORE ---
        // return redirect()->route('index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
