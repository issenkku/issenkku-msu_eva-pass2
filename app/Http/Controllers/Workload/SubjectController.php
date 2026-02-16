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
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(Subject::all());
        }

        $subjects = Subject::orderBy('code')->paginate(10);

        return view('subjects.index', compact('subjects'));
    }

    public function show($id)
    {
        try {
            return response()->json(Subject::findOrFail($id));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Subject not found'], 404);
        }
    }

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
