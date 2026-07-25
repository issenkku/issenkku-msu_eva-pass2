<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\Assignments;
use App\Models\QualityScore;
use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\Reports;
use App\Rules\ActiveQuantitySubCriteria;
use App\Services\ReportDataService;
use App\Services\SupportScoreService;
use App\Support\AssignmentFlow;
use App\Support\QualityScoreHistoryRecorder;
use App\Support\QuantityScoreHistoryRecorder;
use App\Support\SupportScoreRules;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DirectorScoreController extends Controller
{
    protected $allowedEditStatuses = ['Director_assigned', 'Director_draft'];

    protected $reportDataService;

    public function __construct(
        ReportDataService $reportDataService,
        private SupportScoreService $supportScoreService
    ) {
        $this->reportDataService = $reportDataService;
    }

    protected function checkReportEditableStatus(Reports $report, $action)
    {
        if (! in_array($report->status, $this->allowedEditStatuses)) {
            return response()->json([
                'message' => "Cannot {$action}. Report must be in Director_assigned or Director_draft status.",
            ], 403);
        }

        return null; // ถ้าผ่านการตรวจสอบ
    }

    public function director(Request $request, $id)
    {
        $user = $request->user()->load('position', 'department');

        $data = $this->reportDataService->getReportData($id);
        $report = $data['report'];
        $assignmentData = optional($data['assignment'])->assignmentData;

        if ($assignmentData?->director_id && (int) $assignmentData->director_id !== (int) $user->id) {
            abort(403, 'Unauthorized director');
        }

        if (in_array($report->status, ['Assigned', 'Draft', 'Pending', 'Evaluator_draft'])) {
            abort(403, 'ไม่สามารถเข้าถึงหน้าประเมินนี้ได้ เนื่องจากสถานะไม่อนุญาต');
        }

        $canEdit = in_array($report->status, ['Director_assigned', 'Director_draft']);
        $readonly = ! $canEdit; // true if status is something else

        if ($readonly && $request->query('readonly') != 1) {
            return redirect()->route('director.show', ['id' => $id, 'readonly' => 1]);
        }

        return view('director_dashboard.director', array_merge($data, [
            'id' => $id,
            'user' => $user,
            'readonly' => $readonly,
        ]));
    }

    public function storeDirectorScores(Request $request, $reportId)
    {
        $transactionStarted = false;

        try {
            $reportId = is_array($reportId) ? $reportId[0] : (int) $reportId;
            $report = Reports::findOrFail($reportId);
            $assignment = Assignments::with('assignmentData')->where('report_id', $reportId)->first();
            $assignmentData = $assignment?->assignmentData;
            if (! $assignment || ($assignmentData?->director_id && (int) $assignmentData->director_id !== (int) $request->user()->id)) {
                abort(403, 'Unauthorized director');
            }

            $statusCheck = $this->checkReportEditableStatus($report, 'process evaluation scores');
            if ($statusCheck) {
                return $statusCheck;
            }

            $criteriaVersionId = $report->reportData?->criteria_version_id;
            $validated = $request->validate([
                'quantity_list' => 'nullable|array',
                'quantity_list.*.quantity_sub_criteria_id' => [
                    'nullable',
                    'integer',
                    new ActiveQuantitySubCriteria((int) $criteriaVersionId),
                ],
                'quantity_list.*.score_C' => 'nullable|numeric|min:0',
                'quantity_list.*.description' => 'nullable|string',
                'quantity_list.*.modification_reason' => 'nullable|string|max:2000',

                'quality_list' => 'nullable|array',
                'quality_list.*.quality_sub_criteria_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('quality_sub_criterias', 'id')
                        ->where(fn ($query) => $query->where('criteria_version_id', $criteriaVersionId)),
                ],
                'quality_list.*.score' => 'nullable|numeric',
                'quality_list.*.modification_reason' => 'nullable|string|max:2000',

                'status' => 'required|string|in:Director_assigned,Manager_assign,Director_draft,Submitted',
                'comment' => 'nullable|string',

                ...SupportScoreRules::validation(),
            ]);

            DB::beginTransaction();
            $transactionStarted = true;
            $modifierRole = $request->user()?->getRoleNames()->first() ?: 'กรรมการ';

            $activeQuantitySubCriteriaIds = QuantitySubCriteria::query()
                ->active()
                ->where('criteria_version_id', $criteriaVersionId)
                ->pluck('id');
            $oldQuantityScores = QuantityScore::where('report_id', $reportId)
                ->whereIn('quantity_sub_criteria_id', $activeQuantitySubCriteriaIds)
                ->get();
            $oldQualityScores = QualityScore::where('report_id', $reportId)->get();

            // Delete existing records for this report
            QuantityScore::where('report_id', $reportId)
                ->whereIn('quantity_sub_criteria_id', $activeQuantitySubCriteriaIds)
                ->delete();
            QualityScore::where('report_id', $reportId)->delete();

            $newQuantityScores = [];
            if (isset($validated['quantity_list'])) {
                foreach ($validated['quantity_list'] as $inputKey => $item) {
                    $subCriteriaId = is_array($item['quantity_sub_criteria_id'])
                        ? $item['quantity_sub_criteria_id'][0]
                        : (int) $item['quantity_sub_criteria_id'];

                    $subCriteria = QuantitySubCriteria::find($subCriteriaId);
                    $scoreC = $item['score_C'] ?? null;
                    $description = isset($item['description']) ? trim((string) $item['description']) : null;
                    $modificationReason = $item['modification_reason'] ?? null;

                    $newQuantityScores[] = compact(
                        'subCriteriaId',
                        'scoreC',
                        'description',
                        'modificationReason',
                        'inputKey'
                    );

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
                    $newQuantityScores[array_key_last($newQuantityScores)]['scoreD'] = $scoreD;
                }
            }

            QuantityScoreHistoryRecorder::record(
                $reportId,
                $oldQuantityScores,
                $newQuantityScores,
                $request->user()?->id,
                $modifierRole,
                true
            );

            // ✅ Quality loop with check
            $newQualityScores = [];
            if (isset($validated['quality_list'])) {
                foreach ($validated['quality_list'] as $item) {
                    $score = $item['score'] ?? null;
                    if ($score === null) {
                        continue;
                    }

                    $subCriteriaId = is_array($item['quality_sub_criteria_id'])
                        ? $item['quality_sub_criteria_id'][0]
                        : (int) $item['quality_sub_criteria_id'];

                    QualityScore::create([
                        'quality_sub_criteria_id' => $subCriteriaId,
                        'report_id' => $reportId,
                        'score' => $score,
                    ]);
                    $newQualityScores[] = compact('subCriteriaId', 'score');
                }
            }

            $persistedQualityScores = collect($newQualityScores)->keyBy('subCriteriaId');
            $newQualityScoreSnapshots = collect($validated['quality_list'] ?? [])
                ->map(function ($item, $inputKey) use ($persistedQualityScores) {
                    $subCriteriaId = is_array($item['quality_sub_criteria_id'])
                        ? (int) $item['quality_sub_criteria_id'][0]
                        : (int) $item['quality_sub_criteria_id'];

                    return [
                        'subCriteriaId' => $subCriteriaId,
                        'score' => $persistedQualityScores->get($subCriteriaId)['score'] ?? null,
                        'modificationReason' => $item['modification_reason'] ?? null,
                        'inputKey' => $inputKey,
                    ];
                })
                ->values()
                ->all();

            QualityScoreHistoryRecorder::record(
                $reportId,
                $oldQualityScores,
                $newQualityScoreSnapshots,
                $request->user()?->id,
                $modifierRole,
                true
            );

            $supportResult = $this->supportScoreService->persist(
                $report,
                $validated['support_list'] ?? [],
                $request->user(),
                $modifierRole,
                true
            );

            $statusMessages = [
                'Director_draft' => 'กรรมการกรอกคะแนน',
                'Manager_assign' => 'กรรมการอนุมัติ',
                'Assigned' => 'ระบบมอบหมาย',
                'Submitted' => 'รายงานถูกส่งเรียบร้อยแล้ว',
            ];

            $oldStatus = $report->status;
            $oldComment = $report->director_comment;

            $status = $validated['status'] === 'Manager_assign'
                ? AssignmentFlow::nextStatusAfter('director', $assignment?->assignmentData)
                : $validated['status'];
            $report->status = $status;

            if (isset($validated['comment'])) {
                $report->director_comment = $validated['comment'];
                $report->comment = $validated['comment'];
            }
            $newComment = $report->director_comment;

            $report->save();

            activity()
                ->causedBy($request->user()) // who did it
                ->useLog('การประเมิน')
                ->performedOn($report)     // which model
                ->withProperties([
                    'สถานะรายงานก่อนหน้า' => $oldStatus,
                    'อัพเดตสถานะรายงาน' => $status,
                    'ความคิดเห็นก่อนหน้า' => $oldComment,
                    'อัพเดตความคิดเห็น' => $newComment,
                    'คะแนนเชิงปริมาณก่อนหน้า' => $oldQuantityScores,
                    'อัพเดตคะแนนเชิงปริมาณ' => $newQuantityScores,
                    'คะแนนเชิงคุณภาพก่อนหน้า' => $oldQualityScores,
                    'อัพเดตคะแนนเชิงคุณภาพ' => $newQualityScores,
                    'คะแนนสายสนับสนุนก่อนหน้า' => $supportResult['old_scores'],
                    'อัพเดตคะแนนสายสนับสนุน' => $supportResult['new_scores'],
                    'ผลรวมคะแนนสายสนับสนุนจริง' => $supportResult['support_score_total'],
                ])
                ->log($statusMessages[$status] ?? "เปลี่ยนสถานะเป็น {$status}");

            DB::commit();
            $transactionStarted = false;

            $message = $status === 'Director_draft' ? 'บันทึกข้อมูลเรียบร้อยแล้ว' : 'ส่งรายงานเรียบร้อยแล้ว';

            return redirect('/director-dashboard')->with('success', $message);

        } catch (ValidationException $e) {
            if ($transactionStarted) {
                DB::rollBack();
            }

            throw $e;
        } catch (Exception $e) {
            if ($transactionStarted) {
                DB::rollBack();
            }

            return response()->json(['message' => 'Error processing evaluation scores', 'error' => $e->getMessage()], 500);
        }
    }
}
