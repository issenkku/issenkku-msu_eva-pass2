<?php

namespace App\Http\Controllers;

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AssignmentDataController extends Controller
{
    public function index()
    {
        $assignmentData = AssignmentData::with([
            'assignments.evaluateeUser.position',
            'assignments.report.reportData',
            'evaluatorUser',
        ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('assignment-data.index', compact('assignmentData'));
    }

    public function create()
    {
        $report_data = ReportData::all();
        $users = User::all();

        return view('assignment-data.create', compact('report_data', 'users'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'report_data_id' => 'required|exists:report_datas,id',
            'evaluator_id' => 'required|exists:users,id',
            'evaluatees' => 'required|array|min:1',
            'evaluatees.*' => 'required|exists:users,id',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (in_array($request->evaluator_id, $request->evaluatees ?? [])) {
                $validator->errors()->add('evaluatees', 'ผู้ประเมินไม่สามารถเป็นผู้รับการประเมินได้');
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
            // Create assignment data
            $assignmentData = AssignmentData::create([
                'evaluator_id' => $request->evaluator_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            $evaluator = User::find($request->evaluator_id);
            
            // Assign role to evaluator
            $this->assignUserRole($evaluator, 'ผู้ประเมิน');

            // Create assignments for each evaluatee
            foreach ($request->evaluatees as $evaluateeId) {
                $evaluatee = User::find($evaluateeId);
                
                // Assign role to evaluatee
                $this->assignUserRole($evaluatee, 'ผู้รับการประเมิน');

                $report = Reports::create([
                    'report_data_id' => $request->report_data_id,
                    'status' => 'Assigned',
                ]);

                Assignments::create([
                    'assignment_data_id' => $assignmentData->id,
                    'report_id' => $report->id,
                    'evaluatee_id' => $evaluateeId,
                ]);

                // Send email notification
                $this->sendEvaluationNotification($report->id, $evaluatee, $evaluator);
            }

            DB::commit();

            return redirect()->route('assignment-data.index')->with('success', 'สร้าง Assignment สำเร็จแล้ว');
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
            'assignments.report',
            'evaluatorUser',
        ]);

        return response()->json($assignmentData);
    }

    public function edit(AssignmentData $assignmentData)
    {
        $report_data = ReportData::all();
        $users = User::all();

        $assignmentData->load([
            'assignments.evaluateeUser',
            'assignments.report.reportData',
            'evaluatorUser',
        ]);

        // Get selected evaluatees
        $selectedEvaluatees = $assignmentData->assignments->pluck('evaluatee_id')->toArray();

        return view('assignment-data.edit', compact(
            'assignmentData',
            'report_data',
            'users',
            'selectedEvaluatees'
        ));
    }

    public function update(Request $request, AssignmentData $assignmentData)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'report_data_id' => 'required|exists:report_datas,id',
            'evaluator_id' => 'required|exists:users,id',
            'evaluatees' => 'required|array|min:1',
            'evaluatees.*' => 'required|exists:users,id',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (in_array($request->evaluator_id, $request->evaluatees ?? [])) {
                $validator->errors()->add('evaluatees', 'ผู้ประเมินไม่สามารถเป็นผู้รับการประเมินได้');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $assignmentData->update([
                'evaluator_id' => $request->evaluator_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            // Delete old assignments and their reports
            foreach ($assignmentData->assignments as $assignment) {
                if ($assignment->report) {
                    $assignment->report->delete();
                }
            }
            $assignmentData->assignments()->delete();

            $evaluator = User::find($request->evaluator_id);
            $this->assignUserRole($evaluator, 'ผู้ประเมิน');

            // Create new assignments
            foreach ($request->evaluatees as $evaluateeId) {
                $evaluatee = User::find($evaluateeId);
                $this->assignUserRole($evaluatee, 'ผู้รับการประเมิน');

                $report = Reports::create([
                    'report_data_id' => $request->report_data_id,
                    'status' => 'Assigned',
                ]);

                Assignments::create([
                    'assignment_data_id' => $assignmentData->id,
                    'report_id' => $report->id,
                    'evaluatee_id' => $evaluateeId,
                ]);

                // Send email notification
                $this->sendEvaluationNotification($report->id, $evaluatee, $evaluator);
            }

            DB::commit();

            return redirect()->route('assignment-data.index')->with('success', 'แก้ไขรอบการประเมินเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating assignment data', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withErrors(['update_error' => 'เกิดข้อผิดพลาดในการแก้ไข: '.$e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(AssignmentData $assignmentData)
    {
        try {
            DB::beginTransaction();

            // Delete reports associated with assignments
            foreach ($assignmentData->assignments as $assignment) {
                if ($assignment->report) {
                    $assignment->report->delete();
                }
            }

            // Delete assignments
            $assignmentData->assignments()->delete();
            
            // Delete assignment data
            $assignmentData->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ลบรอบการประเมินเรียบร้อยแล้ว',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting assignment data', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการลบ: '.$e->getMessage(),
            ], 500);
        }
    }

    private function sendEvaluationNotification($reportId, $evaluatee, $evaluator)
    {
        $report = Reports::with(['reportData', 'reportData.criteriaVersion'])->find($reportId);
        if (!$report || !$evaluatee->email) {
            return;
        }

        $mailData = [
            'name' => $evaluatee->name,
            'report_title' => optional($report->reportData)->report_title,
            'version_name' => optional(optional($report->reportData)->criteriaVersion)->version_name,
            'status' => $report->status,
            'evaluator_name' => $evaluator->name,
        ];

        try {
            \Mail::send('emails.assignment_Notify', $mailData, function ($message) use ($evaluatee) {
                $message->to($evaluatee->email, $evaluatee->name)
                    ->subject('แจ้งเตือน: คุณได้รับการมอบหมายจัดทำแบบประเมิน');
            });
        } catch (\Exception $e) {
            Log::warning('Failed to send email notification', [
                'evaluatee_id' => $evaluatee->id,
                'report_id' => $reportId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function assignUserRole($user, $roleName)
    {
        if (!$user) {
            throw new \Exception("ไม่พบผู้ใช้");
        }

        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            throw new \Exception("ไม่พบ role: {$roleName}");
        }

        if (!$user->hasRole($roleName)) {
            $user->assignRole($role);
        }
    }
}