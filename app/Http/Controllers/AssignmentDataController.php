<?php

namespace App\Http\Controllers;

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\Departments;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\User;

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
        $report_data = ReportData::all();
        $departments = Departments::all();

        $evaluatees = $users; // หรือ filter ตามต้องการ
        $evaluators = $users;

        return view('assignment-data.create', compact('assignmentData', 'users', 'report_data', 'departments', 'evaluatees', 'evaluators'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Departments::all();

        $report_data = ReportData::all();
        $users = User::all();

        $evaluatees = $users;
        $evaluators = $users;

        return view('assignment-data.create', compact('report_data', 'departments', 'evaluatees', 'evaluators'));
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
            'assignments.*.report_data_id' => 'required|exists:report_datas,id', // แก้ชื่อ table
            'assignments.*.evaluatee' => 'required|exists:users,id',
            'assignments.*.evaluator' => 'required|exists:users,id|different:assignments.*.evaluatee',
        ]);

        DB::beginTransaction();

        try {
            $assignmentData = AssignmentData::create([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            foreach ($request->assignments as $assignmentItem) {
                $reportDataId = $assignmentItem['report_data_id'];

                // สร้าง Reports ใหม่
                $report = Reports::create([
                    'report_data_id' => $reportDataId,
                    'status' => 'assigned',
                ]);

                // สร้าง Assignments ใหม่
                Assignments::create([
                    'assignment_data_id' => $assignmentData->id,
                    'report_id' => $report->id,
                    'evaluatee' => $assignmentItem['evaluatee'],
                    'evaluator' => $assignmentItem['evaluator'],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('assignment-data.create')
                ->with('success', 'สร้าง Assignment และ Reports สำเร็จแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('assignment-data.create')
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
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
        $reports = Reports::all();
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
            'assignments.*.report_data_id' => 'required|exists:report_datas,id', // แก้ชื่อ table
            'assignments.*.evaluatee' => 'required|exists:users,id',
            'assignments.*.evaluator' => 'required|exists:users,id|different:assignments.*.evaluatee',
        ]);

        DB::beginTransaction();
        try {
            $assignmentData->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            // ลบ assignments เดิม
            $assignmentData->assignments()->delete();

            // สร้าง assignments ใหม่ทั้งหมด
            foreach ($request->assignments as $assignmentItem) {
                $reportDataId = $assignmentItem['report_data_id'];

                $report = Reports::create([
                    'report_data_id' => $reportDataId,
                    'status' => 'assigned',
                ]);

                Assignments::create([
                    'assignment_data_id' => $assignmentData->id,
                    'report_id' => $report->id,
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
            DB::rollBack();

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
            $assignmentData->delete(); // ลบ assignments ที่เกี่ยวข้องด้วย cascade

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
