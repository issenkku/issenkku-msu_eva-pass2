<?php

namespace App\Http\Controllers;

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\User;
use App\Support\AssignmentFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AssignmentDataController extends Controller
{
    /**
     * แสดงรายการรอบการประเมิน
     */
    public function index()
    {
        $assignmentData = AssignmentData::with([
            'assignments.evaluateeUser.position',
            'assignments.report.reportData',
            'evaluatorUser.position',
            'directorUser.position',
            'managerUser.position',
        ])->orderBy('created_at', 'desc')->paginate(10);

        return view('assignment-data.index', compact('assignmentData'));
    }

    /**
     * แสดงหน้าสร้างรอบการประเมินใหม่
     */
    public function create()
    {
        $report_data = ReportData::all();
        $users = User::with(['roles', 'position'])->get();
        $evaluatorUsers = $users->filter(fn ($user) => $user->hasRole('ผู้ประเมิน'))->values();
        $directorUsers = $users->filter(fn ($user) => $user->hasRole('กรรมการ'))->values();
        $managerUsers = $users->filter(fn ($user) => $user->hasRole('ผู้บริหาร'))->values();

        return view('assignment-data.create', compact(
            'report_data',
            'users',
            'evaluatorUsers',
            'directorUsers',
            'managerUsers'
        ));
    }

    /**
     * สร้างรอบการประเมินใหม่ พร้อม assignment ของผู้รับการประเมินแต่ละคน
     */
    public function store(Request $request)
    {
        $validator = $this->buildValidator($request);

        if ($validator->fails()) {
            return redirect()
                ->route('assignment-data.create')
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $payload = $this->preparePayload($request);

            // สร้างข้อมูลรอบการประเมินหลัก
            $assignmentData = AssignmentData::create($payload['assignment_data']);

            // กำหนด role ให้ reviewer ที่ถูกเลือกในรอบนี้
            $this->assignSelectedRoles($payload['reviewers']);

            foreach ($request->evaluatees as $evaluateeId) {
                $evaluatee = User::findOrFail($evaluateeId);
                $this->assignUserRole($evaluatee, 'ผู้รับการประเมิน');

                // สร้างรายงานสำหรับผู้รับการประเมินแต่ละคน
                $report = Reports::create([
                    'report_data_id' => $request->report_data_id,
                    'status' => 'Assigned',
                ]);

                // ผูกผู้รับการประเมินเข้ากับรอบการประเมิน
                Assignments::create([
                    'assignment_data_id' => $assignmentData->id,
                    'report_id' => $report->id,
                    'evaluatee_id' => $evaluateeId,
                ]);

                // ส่งอีเมลแจ้งเตือนโดยใช้ reviewer คนแรกใน flow
                $this->sendEvaluationNotification($report->id, $evaluatee, $payload['first_reviewer']);
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

    /**
     * แสดงรายละเอียดรอบการประเมินในรูปแบบ JSON
     */
    public function show(AssignmentData $assignmentData)
    {
        $assignmentData->load([
            'assignments.evaluateeUser',
            'assignments.report',
            'evaluatorUser',
            'directorUser',
            'managerUser',
        ]);

        return response()->json($assignmentData);
    }

    /**
     * แสดงหน้าแก้ไขรอบการประเมิน
     */
    public function edit(AssignmentData $assignmentData)
    {
        $report_data = ReportData::all();
        $users = User::with(['roles', 'position'])->get();
        $evaluatorUsers = $users->filter(fn ($user) => $user->hasRole('ผู้ประเมิน'))->values();
        $directorUsers = $users->filter(fn ($user) => $user->hasRole('กรรมการ'))->values();
        $managerUsers = $users->filter(fn ($user) => $user->hasRole('ผู้บริหาร'))->values();

        $assignmentData->load([
            'assignments.evaluateeUser',
            'assignments.report.reportData',
            'evaluatorUser',
            'directorUser',
            'managerUser',
        ]);

        // ดึงรายชื่อผู้รับการประเมินเดิมเพื่อแสดงในฟอร์ม
        $selectedEvaluatees = $assignmentData->assignments->pluck('evaluatee_id')->toArray();

        return view('assignment-data.edit', compact(
            'assignmentData',
            'report_data',
            'users',
            'selectedEvaluatees',
            'evaluatorUsers',
            'directorUsers',
            'managerUsers'
        ));
    }

    /**
     * อัปเดตรอบการประเมินและสร้าง assignment ใหม่ตามข้อมูลล่าสุด
     */
    public function update(Request $request, AssignmentData $assignmentData)
    {
        $validator = $this->buildValidator($request);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $payload = $this->preparePayload($request);

            // อัปเดตข้อมูลหลักของรอบการประเมิน
            $assignmentData->update($payload['assignment_data']);

            // ลบ assignment และ report เดิมก่อนสร้างใหม่
            foreach ($assignmentData->assignments as $assignment) {
                if ($assignment->report) {
                    $assignment->report->delete();
                }
            }
            $assignmentData->assignments()->delete();

            // อัปเดต role ของ reviewer ตามชุดข้อมูลล่าสุด
            $this->assignSelectedRoles($payload['reviewers']);

            foreach ($request->evaluatees as $evaluateeId) {
                $evaluatee = User::findOrFail($evaluateeId);
                $this->assignUserRole($evaluatee, 'ผู้รับการประเมิน');

                // สร้างรายงานและ assignment ใหม่ให้ผู้รับการประเมินแต่ละคน
                $report = Reports::create([
                    'report_data_id' => $request->report_data_id,
                    'status' => 'Assigned',
                ]);

                Assignments::create([
                    'assignment_data_id' => $assignmentData->id,
                    'report_id' => $report->id,
                    'evaluatee_id' => $evaluateeId,
                ]);

                $this->sendEvaluationNotification($report->id, $evaluatee, $payload['first_reviewer']);
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
                ->withErrors(['update_error' => 'เกิดข้อผิดพลาดในการแก้ไข: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * ลบรอบการประเมินพร้อมข้อมูลลูกที่เกี่ยวข้อง
     */
    public function destroy(AssignmentData $assignmentData)
    {
        try {
            DB::beginTransaction();

            // ลบ report ที่ผูกอยู่กับ assignment แต่ละรายการก่อน
            foreach ($assignmentData->assignments as $assignment) {
                if ($assignment->report) {
                    $assignment->report->delete();
                }
            }

            // ลบ assignment และตัวรอบการประเมิน
            $assignmentData->assignments()->delete();
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
                'message' => 'เกิดข้อผิดพลาดในการลบ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * สร้าง validator กลางสำหรับหน้า create และ edit
     */
    private function buildValidator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'report_data_id' => 'required|exists:report_datas,id',
            'evaluator_id' => 'nullable|exists:users,id',
            'director_id' => 'nullable|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
            'stage_order' => 'nullable|array',
            'stage_order.*' => 'nullable|integer|min:1|max:3',
            'evaluatees' => 'required|array|min:1',
            'evaluatees.*' => 'required|exists:users,id',
        ]);

        $validator->after(function ($validator) use ($request) {
            // รวบรวมผู้ที่ถูกเลือกในแต่ละขั้นของ flow
            $selectedActors = array_filter(AssignmentFlow::selectedActorsFromRequest($request->all()));

            if (empty($selectedActors)) {
                $validator->errors()->add('evaluation_flow', 'กรุณากำหนดผู้ประเมินอย่างน้อย 1 ขั้นตอน');
            }

            // หนึ่งคนต้องไม่ถูกเลือกซ้ำหลายบทบาท
            if (count($selectedActors) !== count(array_unique($selectedActors))) {
                $validator->errors()->add('evaluation_flow', 'บุคคลในแต่ละบทบาทต้องไม่ซ้ำกัน');
            }

            // ตรวจว่าลำดับ flow ครบทุกบทบาทที่เลือก
            $flow = AssignmentFlow::normalize($request->input('stage_order', []), $selectedActors);
            if (count($flow) !== count($selectedActors)) {
                $validator->errors()->add('evaluation_flow', 'กรุณาระบุลำดับการประเมินให้ครบทุกบทบาทที่เลือก');
            }
        });

        return $validator;
    }

    /**
     * แปลงข้อมูลจาก request ให้พร้อมสำหรับ insert/update
     */
    private function preparePayload(Request $request): array
    {
        // ดึงผู้ใช้ตามบทบาทที่ถูกเลือกในฟอร์ม
        $selectedActors = AssignmentFlow::selectedActorsFromRequest($request->all());
        $reviewers = [
            'evaluator' => $request->filled('evaluator_id') ? User::find($request->evaluator_id) : null,
            'director' => $request->filled('director_id') ? User::find($request->director_id) : null,
            'manager' => $request->filled('manager_id') ? User::find($request->manager_id) : null,
        ];

        // เรียงลำดับ flow ตาม stage_order ที่ผู้ใช้กำหนด
        $flow = AssignmentFlow::normalize($request->input('stage_order', []), array_filter($selectedActors));
        $firstReviewer = collect($flow)->map(fn ($stage) => $reviewers[$stage] ?? null)->first();

        return [
            'assignment_data' => [
                'evaluator_id' => $request->evaluator_id,
                'evaluator_position_id' => $reviewers['evaluator']?->position_id,
                'director_id' => $request->director_id,
                'director_position_id' => $reviewers['director']?->position_id,
                'manager_id' => $request->manager_id,
                'manager_position_id' => $reviewers['manager']?->position_id,
                'evaluation_flow' => $flow,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ],
            'reviewers' => $reviewers,
            'first_reviewer' => $firstReviewer,
        ];
    }

    /**
     * กำหนด role ให้ reviewer ตามบทบาทที่ถูกเลือก
     */
    private function assignSelectedRoles(array $reviewers): void
    {
        if ($reviewers['evaluator']) {
            $this->assignUserRole($reviewers['evaluator'], 'ผู้ประเมิน');
        }

        if ($reviewers['director']) {
            $this->assignUserRole($reviewers['director'], 'กรรมการ');
        }

        if ($reviewers['manager']) {
            $this->assignUserRole($reviewers['manager'], 'ผู้บริหาร');
        }
    }

    /**
     * ส่งอีเมลแจ้งเตือนให้ผู้รับการประเมิน
     */
    private function sendEvaluationNotification($reportId, $evaluatee, $reviewer)
    {
        $report = Reports::with(['reportData', 'reportData.criteriaVersion'])->find($reportId);
        if (! $report || ! $evaluatee->email) {
            return;
        }

        $mailData = [
            'name' => $evaluatee->name,
            'report_title' => optional($report->reportData)->report_title,
            'version_name' => optional(optional($report->reportData)->criteriaVersion)->version_name,
            'status' => $report->status,
            'evaluator_name' => $reviewer?->name ?? '-',
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

    /**
     * เพิ่ม role ให้ผู้ใช้ เมื่อยังไม่มี role ดังกล่าว
     */
    private function assignUserRole($user, $roleName)
    {
        if (! $user) {
            throw new \Exception('ไม่พบผู้ใช้');
        }

        $role = Role::where('name', $roleName)->first();

        if (! $role) {
            throw new \Exception("ไม่พบ role: {$roleName}");
        }

        if (! $user->hasRole($roleName)) {
            $user->assignRole($role);
        }
    }
}
