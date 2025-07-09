<?php

namespace App\Http\Controllers;

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\Setting\Departments;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AssignmentDataController extends Controller
{
    public function index()
    {
        $assignmentData = AssignmentData::with([
            'assignments.evaluateeUser',
            'assignments.evaluatorUser',
            'assignments.report',
        ])->get();

        $users = User::all();
        $reportData = ReportData::all();
        $departments = Departments::all();

        return view('assignment-data.create', [
            'assignmentData' => $assignmentData,
            'users' => $users,
            'report_data' => $reportData,
            'departments' => $departments,
            'evaluatees' => $users,
            'evaluators' => $users,
        ]);
    }

    public function create()
    {
        $users = User::all();
        $reportData = ReportData::all();
        $departments = Departments::all();

        return view('assignment-data.create', [
            'report_data' => $reportData,
            'departments' => $departments,
            'users' => $users,
            'evaluatees' => $users,
            'evaluators' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'assignments' => 'required|array|min:1',
            'assignments.*.report_data_id' => 'required|exists:report_datas,id',
            'assignments.*.evaluatee' => 'required|exists:users,id',
            'assignments.*.evaluator' => 'required|exists:users,id',
        ]);

        $validator->after(function ($validator) use ($request) {
            foreach ($request->assignments as $index => $item) {
                if ($item['evaluatee'] == $item['evaluator']) {
                    $validator->errors()->add("assignments.$index.evaluator", 'ผู้ประเมินต้องไม่ตรงกับผู้รับการประเมิน');
                }
            }
        });

        if ($validator->fails()) {
            return redirect()
                ->route('assignment-data.create')
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $assignmentData = AssignmentData::create([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            foreach ($request->assignments as $item) {
                $report = Reports::create([
                    'report_data_id' => $item['report_data_id'],
                    'status' => 'assigned',
                ]);

                Assignments::create([
                    'assignment_data_id' => $assignmentData->id,
                    'report_id' => $report->id,
                    'evaluatee' => $item['evaluatee'],
                    'evaluator' => $item['evaluator'],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('assignment-data.create')
                ->with('success', 'สร้าง Assignment สำเร็จแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error storing assignment data', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return redirect()
                ->route('assignment-data.create')
                ->withErrors(['store_error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function show(AssignmentData $assignmentData)
    {
        $assignmentData->load([
            'assignments.evaluateeUser',
            'assignments.evaluatorUser',
            'assignments.report',
        ]);

        return response()->json($assignmentData);
    }

    public function edit(AssignmentData $assignmentData)
    {
        $users = User::all();
        $reports = Reports::all();

        $assignmentData->load([
            'assignments.evaluateeUser',
            'assignments.evaluatorUser',
            'assignments.report',
        ]);

        return response()->json([
            'assignmentData' => $assignmentData,
            'users' => $users,
            'reports' => $reports,
        ]);
    }

    public function update(Request $request, AssignmentData $assignmentData)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'assignments' => 'required|array|min:1',
            'assignments.*.report_data_id' => 'required|exists:report_datas,id',
            'assignments.*.evaluatee' => 'required|exists:users,id',
            'assignments.*.evaluator' => 'required|exists:users,id',
        ]);

        $validator->after(function ($validator) use ($request) {
            foreach ($request->assignments as $index => $item) {
                if ($item['evaluatee'] == $item['evaluator']) {
                    $validator->errors()->add("assignments.$index.evaluator", 'ผู้ประเมินต้องไม่ตรงกับผู้รับการประเมิน');
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $assignmentData->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            $assignmentData->assignments()->delete();

            foreach ($request->assignments as $item) {
                $report = Reports::create([
                    'report_data_id' => $item['report_data_id'],
                    'status' => 'assigned',
                ]);

                Assignments::create([
                    'assignment_data_id' => $assignmentData->id,
                    'report_id' => $report->id,
                    'evaluatee' => $item['evaluatee'],
                    'evaluator' => $item['evaluator'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Assignment data updated successfully',
                'data' => $assignmentData->load('assignments'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating assignment data', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Error updating assignment data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(AssignmentData $assignmentData)
    {
        try {
            $assignmentData->delete();

            return response()->json([
                'message' => 'Assignment data deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting assignment data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
