<?php

namespace App\Http\Controllers;

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\User;
use App\Support\AssignmentFlow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AssignmentDataController extends Controller
{
    // Controller นี้ดูแลทั้งหน้า list และฟอร์ม create/edit ของรอบการประเมิน
    // ฝั่ง view จะพยายามรับข้อมูลที่จัดรูปเสร็จแล้วให้มากที่สุด เพื่อลด @php ใน Blade

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

        // แปลงข้อมูล relation ที่ซ้อนกันให้เป็นแถวพร้อมแสดงผลในตาราง
        $assignmentRows = $assignmentData->getCollection()
            ->map(fn (AssignmentData $assignment) => $this->buildAssignmentIndexRow($assignment));

        $assignmentData->setCollection($assignmentRows);

        return view('assignment-data.index', compact('assignmentData'));
    }

    /**
     * แสดงหน้าสร้างรอบการประเมินใหม่
     */
    public function create()
    {
        $report_data = $this->availableReportData();
        $users = User::with(['roles', 'position', 'department'])->get();
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
                $this->sendEvaluationNotification($report->id, $evaluatee, $payload['reviewers']);
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
        $report_data = $this->availableReportData();
        $users = User::with(['roles', 'position', 'department'])->get();
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

                $this->sendEvaluationNotification($report->id, $evaluatee, $payload['reviewers']);
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
            if ($request->filled('report_data_id') && ! ReportData::whereKey($request->report_data_id)->whereHas('criteriaVersion')->exists()) {
                $validator->errors()->add('report_data_id', 'เกณฑ์การประเมินนี้ถูกลบหรือไม่พร้อมใช้งานแล้ว');
            }

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

            $selectedStageOrders = collect($request->input('stage_order', []))
                ->only(array_keys($selectedActors))
                ->filter(fn ($order) => filled($order))
                ->map(fn ($order) => (int) $order)
                ->values();

            if ($selectedStageOrders->count() !== $selectedStageOrders->unique()->count()) {
                $validator->errors()->add('evaluation_flow', 'ลำดับการประเมินของแต่ละบทบาทต้องไม่ซ้ำกัน');
            }
        });

        return $validator;
    }

    private function availableReportData()
    {
        return ReportData::query()
            ->whereHas('criteriaVersion')
            ->orderBy('report_title')
            ->get();
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
    private function sendEvaluationNotification($reportId, $evaluatee, array $reviewers)
    {
        $report = Reports::with(['reportData', 'reportData.criteriaVersion'])->find($reportId);
        if (! $report || ! $evaluatee->email) {
            return;
        }

        $reviewerContacts = collect([
            ['label' => 'หัวหน้างาน', 'name' => $reviewers['evaluator']?->name ?? null],
            ['label' => 'กรรมการ', 'name' => $reviewers['director']?->name ?? null],
            ['label' => 'ผู้บริหาร', 'name' => $reviewers['manager']?->name ?? null],
        ])->filter(fn ($item) => filled($item['name']))->values()->all();

        $mailData = [
            'name' => $evaluatee->name,
            'report_title' => optional($report->reportData)->report_title,
            'version_name' => optional(optional($report->reportData)->criteriaVersion)->version_name,
            'status' => $report->status,
            'evaluator_name' => $reviewers['evaluator']?->name ?? '-',
            'reviewer_contacts' => $reviewerContacts,
            'action_url' => route('evaluation.show', ['id' => $reportId]),
            'action_text' => 'เข้าสู่แบบประเมิน',
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

    private function buildAssignmentIndexRow(AssignmentData $assignment): array
    {
        // เตรียม object วันที่ไว้ใช้ทั้งการแสดงผลและคำนวณสถานะของรอบประเมิน
        $startTime = $assignment->start_time ? Carbon::parse($assignment->start_time) : null;
        $endTime = $assignment->end_time ? Carbon::parse($assignment->end_time) : null;
        $reportData = $assignment->assignments->first()?->report?->reportData;
        $evaluateeCount = $assignment->assignments->count();
        $now = now();
        $stageLabels = [
            'evaluator' => 'ผู้ประเมิน',
            'director' => 'กรรมการ',
            'manager' => 'ผู้บริหาร',
        ];

        // แปลง evaluation_flow ให้เป็นรายการ reviewer ที่พร้อม render ในตาราง
        // จุดนี้ตั้งใจตัด reviewer ที่ไม่มีอยู่จริงออก เพื่อไม่ให้ view ต้องเช็กซ้ำหลายชั้น
        $reviewers = collect(AssignmentFlow::stagesFor($assignment))
            ->map(function (string $stage) use ($assignment, $stageLabels) {
                $user = match ($stage) {
                    'evaluator' => $assignment->evaluatorUser,
                    'director' => $assignment->directorUser,
                    'manager' => $assignment->managerUser,
                    default => null,
                };

                if (! $user) {
                    return null;
                }

                return [
                    'label' => $stageLabels[$stage] ?? 'ผู้ประเมิน',
                    'name' => $user->name,
                    'position' => $user->position?->name ?? '-',
                ];
            })
            ->filter()
            ->values();

        return [
            // เก็บ model เดิมไว้สำหรับ action เช่น edit/delete โดยไม่ต้องยิง query เพิ่ม
            'model' => $assignment,
            // จัดรูปข้อมูลช่วงวันเป็นก้อนเดียว เพื่อให้ row partial อ่านง่าย
            'date_range' => [
                'start' => $this->formatThaiDate($startTime),
                'end' => $this->formatThaiDate($endTime),
                'days' => $startTime && $endTime ? $startTime->diffInDays($endTime) + 1 : 0,
            ],
            // รายงานใช้ข้อมูลจาก assignment แรก เพราะหนึ่งรอบประเมินอ้างถึงเกณฑ์เดียวกัน
            'report' => [
                'title' => $reportData?->report_title ?? '-',
                'description' => $reportData?->report_description,
            ],
            'reviewers' => $reviewers,
            'evaluatee_count' => $evaluateeCount,
            // ส่ง label/icon/class มาเป็นแพ็กเดียว เพื่อให้ฝั่ง Blade render badge ได้ทันที
            'status' => $this->resolveAssignmentIndexStatus($startTime, $endTime, $now),
        ];
    }

    private function resolveAssignmentIndexStatus(?Carbon $startTime, ?Carbon $endTime, Carbon $now): array
    {
        // กันเคสข้อมูลวันที่ไม่ครบก่อน เพื่อให้หน้า list แสดงผลได้แม้ข้อมูลเก่าไม่สมบูรณ์
        if (! $startTime || ! $endTime) {
            return [
                'label' => '-',
                'icon' => 'fas fa-minus-circle',
                'class' => 'bg-gray-100 text-gray-800',
            ];
        }

        if ($now->lt($startTime)) {
            return [
                'label' => 'รอเริ่มต้น',
                'icon' => 'fas fa-clock',
                'class' => 'bg-yellow-100 text-yellow-800',
            ];
        }

        if ($now->between($startTime, $endTime)) {
            return [
                'label' => 'กำลังดำเนินการ',
                'icon' => 'fas fa-play-circle',
                'class' => 'bg-green-100 text-green-800',
            ];
        }

        return [
            'label' => 'สิ้นสุดแล้ว',
            'icon' => 'fas fa-check-circle',
            'class' => 'bg-gray-100 text-gray-800',
        ];
    }

    private function formatThaiDate(?Carbon $date): string
    {
        if (! $date) {
            return '-';
        }

        // รวม logic ปี พ.ศ. และชื่อเดือนภาษาไทยไว้จุดเดียว
        // ถ้ารูปแบบวันที่ต้องเปลี่ยนในอนาคต จะได้แก้ที่เดียวแล้วทุกหน้าตรงกัน
        Carbon::setLocale('th');
        setlocale(LC_TIME, 'th_TH.UTF-8');

        $thaiMonth = $date->translatedFormat('j F');
        $buddhistYear = $date->year + 543;

        return "{$thaiMonth} {$buddhistYear}";
    }
}
