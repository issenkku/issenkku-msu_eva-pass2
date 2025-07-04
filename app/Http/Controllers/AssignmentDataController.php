<?php

namespace App\Http\Controllers;

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\Setting\Department;
use App\Models\User;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assignmentData = AssignmentData::with([
            'assignments.evaluateeUser',
            'assignments.evaluatorUser',
            'assignments.report',
        ])->get();

        $users = User::all();
        $reports = Report::all();
        $departments = Department::all();

        $evaluatees = $users; // หรือ filter ตามต้องการ
        $evaluators = $users;

        return view('assignment-data.create', compact('assignmentData', 'users', 'reports', 'departments', 'evaluatees', 'evaluators'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        $reports = Report::all();
        $users = User::all();

        $evaluatees = $users;
        $evaluators = $users;

        return view('assignment-data.create', compact('reports', 'departments', 'evaluatees', 'evaluators'));
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'assignments' => 'required|array|min:1',
            'assignments.*.report_id' => 'required|exists:reports,id',
            'assignments.*.evaluatee' => 'required|exists:users,id',
            'assignments.*.evaluator' => 'required|exists:users,id|different:assignments.*.evaluatee',
        ]);

        DB::beginTransaction();
        try {
            // สร้าง AssignmentData
            $assignmentData = AssignmentData::create([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            // สร้าง Assignments
            foreach ($request->assignments as $assignmentItem) {
                Assignments::create([
                    'assignment_data_id' => $assignmentData->id,
                    'report_id' => $assignmentItem['report_id'],
                    'evaluatee' => $assignmentItem['evaluatee'],
                    'evaluator' => $assignmentItem['evaluator'],
                ]);
            }

            DB::commit();
            return redirect()->route('assignment-data.create')->with('success', 'บันทึกข้อมูลเรียบร้อย.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AssignmentData $assignmentData)
    {
        $assignmentData->load(['assignments.evaluateeUser', 'assignments.evaluatorUser', 'assignments.report']);

        return response()->json($assignmentData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AssignmentData $assignmentData)
    {
        $users = User::all();
        $reports = Report::all();
        $assignmentData->load(['assignments.evaluateeUser', 'assignments.evaluatorUser', 'assignments.report']);

        return response()->json([
            'assignmentData' => $assignmentData,
            'users' => $users,
            'reports' => $reports
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssignmentData $assignmentData)
    {
        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'assignments' => 'required|array|min:1',
            'assignments.*.report_id' => 'required|exists:reports,id',
            'assignments.*.evaluatee' => 'required|exists:users,id',
            'assignments.*.evaluator' => 'required|exists:users,id|different:assignments.*.evaluatee',
        ]);

        DB::beginTransaction();
        try {
            // อัปเดต AssignmentData
            $assignmentData->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            // ลบ assignments เก่า
            $assignmentData->assignments()->delete();

            // สร้าง assignments ใหม่
            foreach ($request->assignments as $assignmentItem) {
                Assignments::create([
                    'assignment_data_id' => $assignmentData->id,
                    'report_id' => $assignmentItem['report_id'],
                    'evaluatee' => $assignmentItem['evaluatee'],
                    'evaluator' => $assignmentItem['evaluator'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Assignment data updated successfully',
                'data' => $assignmentData->load('assignments')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error updating assignment data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssignmentData $assignmentData)
    {
        try {
            $assignmentData->delete(); // จะลบ assignments ที่เกี่ยวข้องด้วย เนื่องจากมี cascade

            return response()->json([
                'message' => 'Assignment data deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting assignment data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
