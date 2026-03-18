<?php

namespace App\Http\Controllers\Workload;


use App\Http\Controllers\Controller;
use App\Http\Requests\Workload\StoreSubjectRequest;
use App\Http\Requests\Workload\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * เมธอด: index
     * จุดประสงค์: แสดงหน้า subjects.index ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: หน้า subjects.index
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(Subject::all());
        }

        $sort = $request->input('sort', 'code_asc');
        $status = $request->input('status');

        $subjects = Subject::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name_th', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false));

        match ($sort) {
            'latest' => $subjects->orderByDesc('id'),
            'oldest' => $subjects->orderBy('id'),
            'code_desc' => $subjects->orderByDesc('code'),
            'name_asc' => $subjects->orderBy('name_th'),
            'name_desc' => $subjects->orderByDesc('name_th'),
            default => $subjects->orderBy('code'),
        };

        $subjects = $subjects->paginate(10)->withQueryString();

        return view('subjects.index', compact('subjects'));
    }

    /**
     * เมธอด: show
     * จุดประสงค์: ส่งข้อมูลแบบ JSON
     * อินพุต: ตัวระบุ ($id)
     * เอาต์พุต: ข้อมูล JSON
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function show($id)
    {
        try {
            return response()->json(Subject::findOrFail($id));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Subject not found'], 404);
        }
    }

    /**
     * เมธอด: store
     * จุดประสงค์: บันทึกข้อมูล Subject ส่งข้อมูลแบบ JSON และเปลี่ยนเส้นทางไปที่ route subjects.index
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ข้อมูล JSON
     * @param StoreSubjectRequest $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function store(StoreSubjectRequest $request)
    {
        $subject = Subject::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json($subject, 201);
        }

        $redirectTo = $request->input('redirect_to')
            ?? $request->query('redirect_to')
            ?? $request->headers->get('referer');
        if ($redirectTo) {
            return redirect()->to($redirectTo)->with('success', 'เพิ่มข้อมูลรายวิชาเรียบร้อยแล้ว');
        }

        return redirect()->route('subjects.index')->with('success', 'เพิ่มข้อมูลรายวิชาเรียบร้อยแล้ว');
    }

    /**
     * เมธอด: update
     * จุดประสงค์: อัปเดตข้อมูล ส่งข้อมูลแบบ JSON และเปลี่ยนเส้นทางไปที่ route subjects.index
     * อินพุต: ข้อมูลจากคำขอ, ตัวระบุ ($id)
     * เอาต์พุต: ข้อมูล JSON
     * @param UpdateSubjectRequest $request ค่าที่รับเข้ามา
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function update(UpdateSubjectRequest $request, $id)
    {
        try {
            $subject = Subject::findOrFail($id);
            $subject->update($request->validated());

            if ($request->expectsJson()) {
                return response()->json($subject);
            }

            $redirectTo = $request->input('redirect_to')
                ?? $request->query('redirect_to')
                ?? $request->headers->get('referer');
            if ($redirectTo) {
                return redirect()->to($redirectTo)->with('success', 'อัปเดตข้อมูลรายวิชาเรียบร้อยแล้ว');
            }

            return redirect()->route('subjects.index')->with('success', 'อัปเดตข้อมูลรายวิชาเรียบร้อยแล้ว');
        } catch (ModelNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Subject not found'], 404);
            }

            $redirectTo = $request->input('redirect_to')
                ?? $request->query('redirect_to')
                ?? $request->headers->get('referer');
            if ($redirectTo) {
                return redirect()->to($redirectTo)->with('error', 'ไม่พบรายวิชาที่ต้องการแก้ไข');
            }

            return redirect()->route('subjects.index')->with('error', 'ไม่พบรายวิชาที่ต้องการแก้ไข');
        }
    }

    /**
     * เมธอด: destroy
     * จุดประสงค์: ลบข้อมูล ส่งข้อมูลแบบ JSON และเปลี่ยนเส้นทางไปที่ route subjects.index
     * อินพุต: ตัวระบุ ($id)
     * เอาต์พุต: ข้อมูล JSON
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function destroy($id)
    {
        try {
            $subject = Subject::findOrFail($id);
            $subject->delete();

            if (request()->expectsJson()) {
                return response()->json(['message' => 'Subject deleted successfully']);
            }

            $redirectTo = request()->input('redirect_to')
                ?? request()->query('redirect_to')
                ?? request()->headers->get('referer');
            if ($redirectTo) {
                return redirect()->to($redirectTo)->with('success', 'ลบข้อมูลรายวิชาเรียบร้อยแล้ว');
            }

            return redirect()->route('subjects.index')->with('success', 'ลบข้อมูลรายวิชาเรียบร้อยแล้ว');
        } catch (ModelNotFoundException $e) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Subject not found'], 404);
            }

            $redirectTo = request()->input('redirect_to')
                ?? request()->query('redirect_to')
                ?? request()->headers->get('referer');
            if ($redirectTo) {
                return redirect()->to($redirectTo)->with('error', 'ไม่พบรายวิชาที่ต้องการลบ');
            }

            return redirect()->route('subjects.index')->with('error', 'ไม่พบรายวิชาที่ต้องการลบ');
        }
    }
}
