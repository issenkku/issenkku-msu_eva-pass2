<?php

namespace App\Http\Controllers;


use App\Models\Assignments;
use App\Models\EvidenceAnswer;
use App\Models\QualityScore;
use App\Models\QuantityScore;
use App\Models\Reports;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ReportDataService;
use App\Support\AssignmentFlow;
use App\Support\EvaluationScoreSummary;
use App\Support\QuantityScoreHistoryRecorder;

class EvaluatorScoreController extends Controller
{
    protected $allowedEditStatuses = ['Pending', 'Evaluator_draft'];

    protected $reportDataService;

    /**
     * เมธอด: __construct
     * จุดประสงค์: ประมวลผลคำขอ
     * อินพุต: โมเดล ReportDataService
     * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
     * @param ReportDataService $reportDataService ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function __construct(ReportDataService $reportDataService)
    {
        $this->reportDataService = $reportDataService;
    }

    protected function checkReportEditableStatus(Reports $report, $action)
    {
        if (! in_array($report->status, $this->allowedEditStatuses)) {
            return response()->json([
                'message' => "Cannot {$action}. Report must be in Pending or Evaluator_draft status.",
            ], 403);
        }

        return null; // ถ้าผ่านการตรวจสอบ
    }

    /**
     * เมธอด: evaluator
     * จุดประสงค์: แสดงหน้า evaluator_dashboard.evaluator และเปลี่ยนเส้นทางไปที่ route evaluator.evaluator.show
     * อินพุต: ข้อมูลจากคำขอ, ตัวระบุ ($id), โมเดล ReportDataService
     * เอาต์พุต: หน้า evaluator_dashboard.evaluator
     * @param Request $request ค่าที่รับเข้ามา
     * @param mixed $id ค่าที่รับเข้ามา
     * @param ReportDataService $reportDataService ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function evaluator(Request $request, $id, ReportDataService $reportDataService)
    {
        $user = $request->user()->load('position', 'department');

        $assignment = $reportDataService->getEvaluatorAssignmentForUser($id, $user);
        if (! $assignment) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงรายงานนี้');
        }

        // Get full report data
        $data = $reportDataService->getReportData($id);
        $data['scoreSummary'] = EvaluationScoreSummary::fromCategoryItems($data['categoryItems'] ?? []);

        $report = $data['report'];

        if (in_array($report->status, ['Assigned', 'Draft'])) {
            abort(403, 'ไม่สามารถเข้าถึงหน้าประเมินนี้ได้ เนื่องจากสถานะไม่อนุญาต');
        }

        $readonly = !in_array($report->status, ['Pending', 'Evaluator_draft']);
        if ($readonly && $request->query('readonly') != 1) {
            return redirect()->route('evaluator.evaluator.show', ['id' => $id, 'readonly' => 1]);
        }

        return view('evaluator_dashboard.evaluator', array_merge(
            $data,
            compact('id', 'user', 'readonly')
        ));
    }

    

    /**
     * เมธอด: storeEvaluatorScores
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ บันทึกข้อมูล QuantityScore, QualityScore ลบข้อมูล ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ, ตัวระบุ ($reportId)
     * เอาต์พุต: ข้อมูล JSON
     * @param Request $request ค่าที่รับเข้ามา
     * @param mixed $reportId ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function storeEvaluatorScores(Request $request, $reportId)
    {
        try {
            $reportId = is_array($reportId) ? $reportId[0] : (int) $reportId;
            $report = Reports::findOrFail($reportId);
            $assignment = $this->reportDataService->getEvaluatorAssignmentForUser(
                $reportId,
                $request->user()->loadMissing('position')
            );
            if (! $assignment) {
                abort(403, 'Unauthorized evaluator');
            }

            $statusCheck = $this->checkReportEditableStatus($report, 'process evaluation scores');
            if ($statusCheck) {
                return $statusCheck;
            }

            $validated = $request->validate([
                'quantity_list' => 'nullable|array',
                'quantity_list.*.quantity_sub_criteria_id' => 'nullable|integer|exists:quantity_sub_criterias,id',
                'quantity_list.*.score_C' => 'nullable|numeric',
                'quantity_list.*.description' => 'nullable|string',

                'quality_list' => 'nullable|array',
                'quality_list.*.quality_sub_criteria_id' => 'nullable|integer|exists:quality_sub_criterias,id',
                'quality_list.*.score' => 'nullable|numeric|min:0',

                'status' => 'required|string|in:Director_assigned,Pending,Evaluator_draft,Submitted',
                'comment' => 'nullable|string',
            ]);

            DB::beginTransaction();
            $modifierRole = $request->user()?->getRoleNames()->first() ?: 'ผู้ประเมิน';

            $oldQuantityScores = QuantityScore::where('report_id', $reportId)->get();
            $oldQualityScores  = QualityScore::where('report_id', $reportId)->get();

            // Delete existing records for this report
            QuantityScore::where('report_id', $reportId)->delete();
            QualityScore::where('report_id', $reportId)->delete();

            $newQuantityScores = [];
            if (isset($validated['quantity_list'])) {
                foreach ($validated['quantity_list'] as $item) {
                    $subCriteriaId = is_array($item['quantity_sub_criteria_id'])
                        ? $item['quantity_sub_criteria_id'][0]
                        : (int) $item['quantity_sub_criteria_id'];

                    $subCriteria = \App\Models\QuantitySubCriteria::find($subCriteriaId);
                    $scoreC = $item['score_C'] ?? null;
                    $description = isset($item['description']) ? trim((string) $item['description']) : null;

                    if ($scoreC === null) {
                        continue;
                    }

                    $scoreD = null;
                    if ($subCriteria && $subCriteria->score_b != 0) {
                        $scoreD = ($subCriteria->score_a * $scoreC) / $subCriteria->score_b;
                    }

                    QuantityScore::create([
                        'quantity_sub_criteria_id' => $subCriteriaId,
                        'report_id' => $reportId,
                        'score_C' => $scoreC,
                        'score_D' => $scoreD,
                        'description' => $description !== '' ? $description : null,
                        'modifier_user_id' => $request->user()?->id,
                        'modifier_role' => $modifierRole,
                    ]);
                    $newQuantityScores[] = compact('subCriteriaId', 'scoreC', 'scoreD', 'description');
                }
            }

            QuantityScoreHistoryRecorder::record(
                $reportId,
                $oldQuantityScores,
                $newQuantityScores,
                $request->user()?->id,
                $modifierRole
            );

            // ✅ Quality loop with check
            $newQualityScores = [];
            if (isset($validated['quality_list'])) {
                $qualityItems = $validated['quality_list'];
                $qualitySubIds = collect($qualityItems)
                    ->map(function ($item) {
                        return is_array($item['quality_sub_criteria_id'])
                            ? (int) $item['quality_sub_criteria_id'][0]
                            : (int) $item['quality_sub_criteria_id'];
                    })
                    ->unique()
                    ->values();

                $subCriteriaMap = \App\Models\QualitySubCriteria::whereIn('id', $qualitySubIds)
                    ->get(['id', 'num_score', 'evaluation_list_id'])
                    ->keyBy('id');

                $listIds = $subCriteriaMap->pluck('evaluation_list_id')->filter()->unique()->values();
                $listMaxMap = \App\Models\EvaluationList::whereIn('id', $listIds)
                    ->get(['id', 'sum_score'])
                    ->keyBy('id');

                $tempScores = [];
                $listTotals = [];

                foreach ($qualityItems as $item) {
                    $score = $item['score'] ?? null;
                    if ($score === null) {
                        continue;
                    }

                    $subCriteriaId = is_array($item['quality_sub_criteria_id'])
                        ? (int) $item['quality_sub_criteria_id'][0]
                        : (int) $item['quality_sub_criteria_id'];

                    $subCriteria = $subCriteriaMap->get($subCriteriaId);
                    if (! $subCriteria) {
                        continue;
                    }

                    $scoreValue = max(0, (float) $score);
                    if ($subCriteria->num_score !== null) {
                        $scoreValue = min($scoreValue, (float) $subCriteria->num_score);
                    }

                    $listId = (int) $subCriteria->evaluation_list_id;
                    $tempScores[$subCriteriaId] = [
                        'score' => $scoreValue,
                        'list_id' => $listId,
                    ];
                    $listTotals[$listId] = ($listTotals[$listId] ?? 0) + $scoreValue;
                }

                $listScales = [];
                foreach ($listTotals as $listId => $sum) {
                    $listMax = (float) ($listMaxMap->get($listId)?->sum_score ?? 0);
                    $scale = 1.0;
                    if ($listMax > 0 && $sum > $listMax) {
                        $scale = $listMax / $sum;
                    }
                    $listScales[$listId] = $scale;
                }

                foreach ($tempScores as $subCriteriaId => $data) {
                    $scale = $listScales[$data['list_id']] ?? 1.0;
                    $finalScore = round($data['score'] * $scale, 2);

                    QualityScore::create([
                        'quality_sub_criteria_id' => $subCriteriaId,
                        'report_id' => $reportId,
                        'score' => $finalScore,
                    ]);
                    $newQualityScores[] = [
                        'subCriteriaId' => $subCriteriaId,
                        'score' => $finalScore,
                    ];
                }
            }

            $statusMessages = [
                'Evaluator_draft'   => 'ผู้ประเมินกรอกคะแนน',
                'Director_assigned' => 'ผู้ประเมินอนุมัติ',
                'Assigned'=> 'ระบบมอบหมาย',
                'Submitted' => 'รายงานถูกส่งเรียบร้อยแล้ว',
            ];

            $oldStatus = $report->status;
            $oldComment = $report->evaluator_comment;

            $assignment = Assignments::with('assignmentData')->where('report_id', $reportId)->first();
            $status = in_array($validated['status'], ['Pending', 'Director_assigned', 'Submitted'], true)
                ? AssignmentFlow::nextStatusAfter('evaluator', $assignment?->assignmentData)
                : $validated['status'];
            $report->status = $status;

            if (isset($validated['comment'])) {
                $report->evaluator_comment = $validated['comment'];
                $report->comment = $validated['comment'];
            }
            $newComment = $report->evaluator_comment;

            $report->save();
            // if ($report->save() && $status === 'Pending') {
            //     $this->sendEvaluationCompletedMail($reportId);
            // }

            activity()
                ->causedBy($request->user()) // who did it
                ->useLog('การประเมิน')
                ->performedOn($report)     // which model
                ->withProperties([
                    'สถานะรายงานก่อนหน้า' => $oldStatus,
                    'อัพเดตสถานะรายงาน' => $status,
                    'ความคิดเห็นก่อนหน้า' => $oldComment,
                    'อัพเดตความคิดเห็น'   => $newComment,
                    'คะแนนเชิงปริมาณก่อนหน้า' => $oldQuantityScores,
                    'อัพเดตคะแนนเชิงปริมาณ' => $newQuantityScores,
                    'คะแนนเชิงคุณภาพก่อนหน้า' => $oldQualityScores,
                    'อัพเดตคะแนนเชิงคุณภาพ' => $newQualityScores,
                ])
                ->log($statusMessages[$status] ?? "เปลี่ยนสถานะเป็น {$status}");

            DB::commit();

            $message = $status === 'Evaluator_draft' ? 'บันทึกข้อมูลเรียบร้อยแล้ว' : 'ส่งรายงานเรียบร้อยแล้ว';

            return redirect('/evaluator-dashboard')->with('success', $message);

        } catch (Exception $e) {
            DB::rollback();

            return response()->json(['message' => 'Error processing evaluation scores', 'error' => $e->getMessage()], 500);
        }
    }
}
