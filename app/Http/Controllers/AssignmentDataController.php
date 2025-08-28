<?php

namespace App\Http\Controllers;

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AssignmentDataController extends Controller
{
    public function index()
    {
        $assignmentData = AssignmentData::with([
            'assignments.evaluateeUser',
            'assignments.report.reportData',
            'evaluatorPosition',
            'evaluateePosition',
        ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('assignment-data.index', compact('assignmentData'));
    }

    public function create()
    {
        $departments = Departments::all();
        $report_data = ReportData::all();
        $users = User::all();
        $positions = Positions::with('user')->get();

        $evaluatees = $positions;
        $evaluators = $positions;

        return view('assignment-data.create', compact('report_data', 'departments', 'users', 'positions', 'evaluatees', 'evaluators'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'report_data_id' => 'required|exists:report_datas,id',
            'evaluatees' => 'required|exists:positions,id',
            'evaluators' => 'required|exists:positions,id',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->evaluatees == $request->evaluators) {
                $validator->errors()->add('evaluators', 'ตำแหน่งผู้ประเมินต้องไม่ตรงกับตำแหน่งผู้รับการประเมิน');
            }
        });

        if ($validator->fails()) {
            return redirect()->route('assignment-data.create')
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $assignmentData = AssignmentData::create([
                'evaluator_position_id' => $request->evaluators,
                'evaluatee_position_id' => $request->evaluatees,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            // ดึงผู้ใช้จากตำแหน่งที่เลือก
            $evaluateePosition = Positions::find($request->evaluatees);
            $evaluatorPosition = Positions::find($request->evaluators);

            // ดึงผู้ใช้ทั้งหมดที่มีตำแหน่งนี้
            $evaluateeUsers = $evaluateePosition->user;
            $evaluatorUsers = $evaluatorPosition->user;

            if ($evaluateeUsers->isEmpty()) {
                throw new \Exception('ไม่พบผู้ใช้ในตำแหน่งที่เลือก');
            }

            // กำหนดบทบาทให้ผู้ประเมิน
            $this->assignUserRoles($evaluatorUsers, 'ผู้ประเมิน', null);

            // กำหนดบทบาทให้ผู้รับการประเมิน
            $this->assignUserRoles($evaluateeUsers, 'ผู้รับการประเมิน', null);

            // สร้าง Assignment สำหรับผู้ใช้ทุกคนในตำแหน่งนี้
            foreach ($evaluateeUsers as $evaluateeUser) {
                $report = Reports::create([
                    'report_data_id' => $request->report_data_id,
                    'status' => 'Assigned',
                ]);

                Assignments::create([
                    'assignment_data_id' => $assignmentData->id,
                    'report_id' => $report->id,
                    'evaluatee_id' => $evaluateeUser->id,
                ]);

                // Send email notification for each user
                $this->sendEvaluationCompletedMail($report->id);
            }

            DB::commit();

            return redirect()->route('assignment-data.create')->with('success', 'สร้าง Assignment สำเร็จแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing assignment data', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return redirect()->route('assignment-data.create')
                ->withErrors(['store_error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function show(AssignmentData $assignmentData)
    {
        $assignmentData->load([
            'assignments.evaluateeUser',
            'assignments.report',
            'evaluatorPosition',
            'evaluateePosition',
        ]);

        return response()->json($assignmentData);
    }

    public function edit(AssignmentData $assignmentData)
    {
        $departments = Departments::all();
        $report_data = ReportData::all();
        $users = User::all();
        $positions = Positions::with('user')->get();

        $evaluatees = $positions;
        $evaluators = $positions;

        $assignmentData->load([
            'assignments.evaluateeUser',
            'assignments.report.reportData',
            'evaluatorPosition',
            'evaluateePosition',
        ]);

        return view('assignment-data.edit', compact(
            'assignmentData',
            'report_data',
            'departments',
            'users',
            'positions',
            'evaluatees',
            'evaluators'
        ));
    }

    public function update(Request $request, AssignmentData $assignmentData)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'report_data_id' => 'required|exists:report_datas,id',
            'evaluatees' => 'required|exists:positions,id',
            'evaluators' => 'required|exists:positions,id',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->evaluatees == $request->evaluators) {
                $validator->errors()->add('evaluators', 'ตำแหน่งผู้ประเมินต้องไม่ตรงกับตำแหน่งผู้รับการประเมิน');
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
                'evaluator_position_id' => $request->evaluators,
                'evaluatee_position_id' => $request->evaluatees,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            $assignmentData->assignments()->delete();

            $report = Reports::create([
                'report_data_id' => $request->report_data_id,
                'status' => 'Assigned',
            ]);

            // ดึงผู้ใช้จากตำแหน่งที่เลือก
            $evaluateePosition = Positions::find($request->evaluatees);
            $evaluatorPosition = Positions::find($request->evaluators);

            // ดึงผู้ใช้ทั้งหมดที่มีตำแหน่งนี้
            $evaluateeUsers = $evaluateePosition->user;
            $evaluatorUsers = $evaluatorPosition->user;

            if ($evaluateeUsers->isEmpty() || $evaluatorUsers->isEmpty()) {
                throw new \Exception('ไม่พบผู้ใช้ในตำแหน่งที่เลือก');
            }

            // กำหนดบทบาทให้ผู้ประเมิน
            $this->assignUserRoles($evaluatorUsers, 'ผู้ประเมิน', 'ผู้รับการประเมิน');

            // กำหนดบทบาทให้ผู้รับการประเมิน
            $this->assignUserRoles($evaluateeUsers, 'ผู้รับการประเมิน', 'ผู้ประเมิน');

            // สร้าง Assignment สำหรับผู้ใช้ทุกคนในตำแหน่งผู้รับการประเมิน
            // โดยใช้ผู้ประเมินคนแรกในตำแหน่งผู้ประเมิน
            $evaluatorUser = $evaluatorUsers->first();

            foreach ($evaluateeUsers as $evaluateeUser) {
                Assignments::create([
                    'assignment_data_id' => $assignmentData->id,
                    'report_id' => $report->id,
                    'evaluatee_id' => $evaluateeUser->id,
                ]);
            }

            DB::commit();

            return redirect()->route('assignment-data.index')->with('success', 'แก้ไขรอบการประเมินเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating assignment data', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
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

            // ดึงข้อมูลผู้ใช้ที่เกี่ยวข้องก่อนลบ
            $assignmentData->load(['evaluatorPosition.user', 'evaluateePosition.user']);

            $evaluatorUsers = $assignmentData->evaluatorPosition->user ?? collect();
            $evaluateeUsers = $assignmentData->evaluateePosition->user ?? collect();

            // ลบ assignments ที่เกี่ยวข้องก่อน
            $assignmentData->assignments()->delete();

            // ลบ assignment data
            $assignmentData->delete();

            // หมายเหตุ: การคืนค่า roles จะต้องพิจารณาว่าผู้ใช้ยังมี assignments อื่นอยู่หรือไม่
            // สำหรับความปลอดภัย ควรตรวจสอบก่อนลบ role
            // ในที่นี้จะไม่ลบ role เนื่องจากผู้ใช้อาจมี assignments อื่นอยู่

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

    private function sendEvaluationCompletedMail($reportId)
    {
        $report = \App\Models\Reports::with(['reportData', 'reportData.criteriaVersion'])->find($reportId);
        if (! $report) {
            return;
        }

        // สมมติว่าต้องการแจ้งเตือน evaluatee (ผู้ถูกประเมิน)
        $assignment = \App\Models\Assignments::where('report_id', $reportId)->first();
        if (! $assignment) {
            return;
        }
        $user = \App\Models\User::find($assignment->evaluatee_id);
        if (! $user || ! $user->email) {
            return;
        }
        $evaluator_name = \App\Models\User::find($assignment->evaluator);
        if (! $evaluator_name || ! $evaluator_name->email) {
            return;
        }

        $mailData = [
            'name' => $user->name,
            'report_title' => optional($report->reportData)->report_title,
            'version_name' => optional(optional($report->reportData)->criteriaVersion)->version_name,
            'status' => $report->status,
            'evaluator_name' => $evaluator_name->name,
        ];

        Mail::send('emails.assignment_Notify', $mailData, function ($message) use ($user) {
            $message->to($user->email, $user->name)
                ->subject('แจ้งเตือน: คุณได้รับการมอบหมายจัดทำแบบประเมิน');
        });
    }

    /**
     * จัดการ roles สำหรับผู้ใช้อย่างปลอดภัย
     *
     * @param  mixed  $users
     * @param  string  $newRole
     * @param  string  $removeRole
     */
    private function assignUserRoles($users, $newRole, $removeRole = null)
    {
        $role = Role::where('name', $newRole)->first();

        if (! $role) {
            throw new \Exception("ไม่พบ role: {$newRole}");
        }

        // ตรวจสอบและแปลงเป็น collection ที่เหมาะสม
        if ($users instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
            // ถ้าเป็น HasMany relation ให้ get ข้อมูล
            $users = $users->get();
        } elseif ($users instanceof \Illuminate\Database\Eloquent\Builder) {
            // ถ้าเป็น Query Builder ให้ get ข้อมูล
            $users = $users->get();
        }
        // ถ้าเป็น Collection อยู่แล้ว ไม่ต้องทำอะไร

        foreach ($users as $user) {
            // ตรวจสอบว่า $user เป็น User model จริงหรือไม่
            if (! ($user instanceof \App\Models\User)) {
                continue;
            }

            try {
                // ลบ role เก่าถ้าระบุ
                if ($removeRole && $user->hasRole($removeRole)) {
                    $user->removeRole($removeRole);
                }

                // เพิ่ม role ใหม่ถ้ายังไม่มี
                if (! $user->hasRole($newRole)) {
                    $user->assignRole($role);
                }
            } catch (\Exception $e) {
                Log::warning("Failed to assign role {$newRole} to user {$user->id}: ".$e->getMessage());

                continue;
            }
        }
    }

    /**
     * ตรวจสอบว่าผู้ใช้ยังมี assignments อื่นอยู่หรือไม่
     *
     * @param  User  $user
     * @param  int  $currentAssignmentDataId
     * @return bool
     */
    private function hasOtherAssignments($user, $currentAssignmentDataId = null)
    {
        $query = Assignments::where('evaluatee_id', $user->id);

        if ($currentAssignmentDataId) {
            $query->whereHas('assignmentData', function ($q) use ($currentAssignmentDataId) {
                $q->where('id', '!=', $currentAssignmentDataId);
            });
        }

        return $query->exists();
    }
}
