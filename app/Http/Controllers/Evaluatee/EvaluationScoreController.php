<?php

namespace App\Http\Controllers\Evaluatee;

use App\Http\Controllers\Controller;
use App\Models\Assignments;
use App\Models\EvaluationList;
use App\Models\EvidenceAnswer;
use App\Models\QualityScore;
use App\Models\QualitySubCriteria;
use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\Reports;
use App\Models\User;
use App\Services\SupportScoreService;
use App\Support\AssignmentFlow;
use App\Support\QualityScoreHistoryRecorder;
use App\Support\QuantityScoreHistoryRecorder;
use App\Support\SupportScoreRules;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\Activity;

class EvaluationScoreController extends Controller
{
    protected $allowedEditStatuses = ['Assigned', 'Draft'];

    public function __construct(private SupportScoreService $supportScoreService) {}

    protected function checkReportEditableStatus(Reports $report, $action)
    {
        if (! in_array($report->status, $this->allowedEditStatuses)) {
            return response()->json([
                'message' => "Cannot {$action}. Report must be in Assigned or Draft status.",
            ], 403);
        }

        return null; // ถ้าผ่านการตรวจสอบ
    }

    public function storeEvaluationScores(Request $request, $reportId)
    {
        $transactionStarted = false;

        try {
            $reportId = is_array($reportId) ? $reportId[0] : (int) $reportId;
            $report = Reports::findOrFail($reportId);

            $statusCheck = $this->checkReportEditableStatus($report, 'process evaluation scores');
            if ($statusCheck) {
                return $statusCheck;
            }

            // --- Flatten evidence_list to array of {evaluation_list_id, quality_main_criteria_id, link} ---
            $evidenceInput = $request->input('evidence_list', []);
            $evidenceFlat = [];
            foreach ($evidenceInput as $mainCriteriaId => $item) {
                $qualityMainId = isset($item['quality_main_criteria_id'])
                    ? (int) $item['quality_main_criteria_id']
                    : (int) $mainCriteriaId;
                $evaluationListId = isset($item['evaluation_list_id'])
                    ? (int) $item['evaluation_list_id']
                    : null;

                if (! $evaluationListId) {
                    continue;
                }

                if (isset($item['links']) && is_array($item['links'])) {
                    foreach ($item['links'] as $link) {
                        $link = trim($link);
                        if ($link !== '') {
                            $evidenceFlat[] = [
                                'evaluation_list_id' => $evaluationListId,
                                'quality_main_criteria_id' => $qualityMainId,
                                'link' => $link,
                            ];
                        }
                    }
                }
            }
            $request->merge(['evidence_list_flat' => $evidenceFlat]);
            // ------------------------------------------------------------

            $criteriaVersionId = $report->reportData?->criteria_version_id;
            $validated = $request->validate([
                'quantity_list' => 'nullable|array',
                'quantity_list.*.quantity_sub_criteria_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('quantity_sub_criterias', 'id')
                        ->where(fn ($query) => $query->where('criteria_version_id', $criteriaVersionId)),
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
                'quality_list.*.score' => 'nullable|numeric|min:0',
                'quality_list.*.modification_reason' => 'nullable|string|max:2000',

                'evidence_list_flat' => 'nullable|array',
                'evidence_list_flat.*.evaluation_list_id' => 'required|integer|exists:evaluation_lists,id',
                'evidence_list_flat.*.quality_main_criteria_id' => 'required|integer|exists:quality_main_criterias,id',
                'evidence_list_flat.*.link' => 'required|string',

                'status' => 'required|string|in:Draft,Pending,Assigned,Submitted',

                ...SupportScoreRules::validation(),
            ]);

            if (
                ($validated['status'] ?? 'Draft') === 'Pending'
                && Schema::hasColumn('quality_main_criterias', 'require_evidence')
            ) {
                $selectedQualitySubIds = collect($validated['quality_list'] ?? [])
                    ->filter(function ($item) {
                        return array_key_exists('score', $item)
                            && $item['score'] !== null
                            && $item['score'] !== '';
                    })
                    ->map(function ($item) {
                        return is_array($item['quality_sub_criteria_id'])
                            ? (int) $item['quality_sub_criteria_id'][0]
                            : (int) $item['quality_sub_criteria_id'];
                    })
                    ->filter()
                    ->unique()
                    ->values();

                if ($selectedQualitySubIds->isNotEmpty()) {
                    $requiredMains = QualitySubCriteria::query()
                        ->with('mainCriteria:id,name,require_evidence')
                        ->whereIn('id', $selectedQualitySubIds)
                        ->get()
                        ->pluck('mainCriteria')
                        ->filter(function ($mainCriteria) {
                            return $mainCriteria && $mainCriteria->require_evidence;
                        })
                        ->unique('id')
                        ->values();

                    if ($requiredMains->isNotEmpty()) {
                        $evidenceCountsByMain = collect($validated['evidence_list_flat'] ?? [])
                            ->groupBy('quality_main_criteria_id')
                            ->map->count();

                        $missingEvidenceNames = $requiredMains
                            ->filter(function ($mainCriteria) use ($evidenceCountsByMain) {
                                return (int) ($evidenceCountsByMain[$mainCriteria->id] ?? 0) === 0;
                            })
                            ->pluck('name')
                            ->values()
                            ->all();

                        if (! empty($missingEvidenceNames)) {
                            throw ValidationException::withMessages([
                                'evidence_list' => [
                                    'กรุณาแนบหลักฐานให้ครบสำหรับเกณฑ์ที่กำหนด: '.implode(', ', $missingEvidenceNames),
                                ],
                            ]);
                        }
                    }
                }
            }

            DB::beginTransaction();
            $transactionStarted = true;

            $oldQuantityScores = QuantityScore::where('report_id', $reportId)->get();
            $oldQualityScores = QualityScore::where('report_id', $reportId)->get();
            $oldEvidences = EvidenceAnswer::where('report_id', $reportId)->get();

            // Delete existing records for this report
            QuantityScore::where('report_id', $reportId)->delete();
            QualityScore::where('report_id', $reportId)->delete();
            EvidenceAnswer::where('report_id', $reportId)
                ->whereNull('support_criteria_id')
                ->whereNull('workload_entry_id')
                ->delete();

            $newQuantityScores = [];
            if (isset($validated['quantity_list'])) {
                foreach ($validated['quantity_list'] as $inputKey => $item) {
                    $subCriteriaId = is_array($item['quantity_sub_criteria_id'])
                        ? $item['quantity_sub_criteria_id'][0]
                        : (int) $item['quantity_sub_criteria_id'];

                    $subCriteria = QuantitySubCriteria::find($subCriteriaId);
                    $scoreC = $item['score_C'] ?? null;
                    $description = $item['description'] ?? null;
                    $modificationReason = $item['modification_reason'] ?? null;

                    $newQuantityScores[] = compact(
                        'subCriteriaId',
                        'scoreC',
                        'description',
                        'modificationReason',
                        'inputKey'
                    );

                    if ($scoreC === null && empty(trim($description ?? ''))) {
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
                        'description' => $description,
                        'modifier_user_id' => null,
                        'modifier_role' => null,
                    ]);
                    $newQuantityScores[array_key_last($newQuantityScores)]['scoreD'] = $scoreD;
                }
            }

            QuantityScoreHistoryRecorder::record(
                $reportId,
                $oldQuantityScores,
                $newQuantityScores,
                null,
                null,
                false
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

                $subCriteriaMap = QualitySubCriteria::whereIn('id', $qualitySubIds)
                    ->get(['id', 'num_score', 'evaluation_list_id'])
                    ->keyBy('id');

                $listIds = $subCriteriaMap->pluck('evaluation_list_id')->filter()->unique()->values();
                $listMaxMap = EvaluationList::whereIn('id', $listIds)
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
                null,
                false
            );

            // Save all evidence links
            $newEvidences = [];
            foreach ($validated['evidence_list_flat'] ?? [] as $item) {
                EvidenceAnswer::create([
                    'evaluation_list_id' => $item['evaluation_list_id'],
                    'quality_main_criteria_id' => $item['quality_main_criteria_id'],
                    'report_id' => $reportId,
                    'link' => $item['link'],
                ]);
                $newEvidences[] = $item;
            }

            $supportResult = $this->supportScoreService->persist(
                $report,
                $validated['support_list'] ?? [],
                $request->user(),
                null,
                false
            );

            $statusMessages = [
                'Draft' => 'ผู้รับประเมินกรอกข้อมูล',
                'Pending' => 'ผู้รับประเมินส่งข้อมูล',
                'Assigned' => 'ระบบมอบหมาย',
                'Submitted' => 'รายงานถูกส่งเรียบร้อยแล้ว',
            ];

            $oldStatus = $report->status;
            $status = $validated['status'];
            $assignment = Assignments::with('assignmentData')->where('report_id', $reportId)->first();
            if ($status === 'Pending') {
                $status = AssignmentFlow::statusForStage(AssignmentFlow::stagesFor($assignment?->assignmentData)[0] ?? null) ?? 'Completed';
            }
            $report->status = $status;
            $report->save();

            // ---- Spatie Activity Log ----
            activity()
                ->causedBy($request->user()) // who did it
                ->useLog('การประเมิน')
                ->performedOn($report)     // which model
                ->withProperties([
                    'สถานะรายงานก่อนหน้า' => $oldStatus,
                    'อัพเดตสถานะรายงาน' => $status,
                    'คะแนนเชิงปริมาณก่อนหน้า' => $oldQuantityScores,
                    'อัพเดตคะแนนเชิงปริมาณ' => $newQuantityScores,
                    'คะแนนเชิงคุณภาพก่อนหน้า' => $oldQualityScores,
                    'อัพเดตคะแนนเชิงคุณภาพ' => $newQualityScores,
                    'หลักฐานก่อนหน้า' => $oldEvidences,
                    'อัพเดตหลักฐาน' => $newEvidences,
                    'คะแนนสายสนับสนุนก่อนหน้า' => $supportResult['old_scores'],
                    'อัพเดตคะแนนสายสนับสนุน' => $supportResult['new_scores'],
                    'ผลรวมคะแนนสายสนับสนุนจริง' => $supportResult['support_score_total'],
                ])
                ->log($statusMessages[$status] ?? "เปลี่ยนสถานะเป็น {$status}");

            if (in_array($status, ['Pending', 'Director_assigned', 'Manager_assign', 'Completed'], true)) {
                $this->sendEvaluationCompletedMail($reportId);
            }

            DB::commit();
            $transactionStarted = false;

            $message = $status === 'Draft' ? 'บันทึกข้อมูลเรียบร้อยแล้ว' : 'ส่งรายงานเรียบร้อยแล้ว';

            return redirect('/evaluatee-dashboard')->with('success', $message);

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

    // อีเมลแจ้งเตือนเมื่อส่งแบบประเมิน
    private function sendEvaluationCompletedMail($reportId)
    {
        $report = Reports::with(['reportData', 'reportData.criteriaVersion'])->find($reportId);
        if (! $report) {
            return;
        }

        // สมมติว่าต้องการแจ้งเตือน evaluator (ผู้ประเมิน)
        $assignment = Assignments::where('report_id', $reportId)->first();
        if (! $assignment) {
            return;
        }

        $assignment->load('assignmentData.evaluatorUser', 'assignmentData.directorUser', 'assignmentData.managerUser');
        $evaluatee = User::find($assignment->evaluatee_id);
        $reviewerContacts = collect([
            ['label' => 'หัวหน้างาน', 'name' => $assignment->assignmentData->evaluatorUser->name ?? null],
            ['label' => 'กรรมการ', 'name' => $assignment->assignmentData->directorUser->name ?? null],
            ['label' => 'ผู้บริหาร', 'name' => $assignment->assignmentData->managerUser->name ?? null],
        ])->filter(fn ($item) => filled($item['name']))->values()->all();
        $targetUser = match ($report->status) {
            'Pending' => $assignment->assignmentData->evaluatorUser,
            'Director_assigned' => $assignment->assignmentData->directorUser,
            'Manager_assign' => $assignment->assignmentData->managerUser,
            'Completed' => $evaluatee,
            default => null,
        };

        $targetUsers = collect([$targetUser])->filter();
        foreach ($targetUsers as $user) {
            if (! $user || ! $user->email) {
                continue;
            }

            $actionUrl = match ($report->status) {
                'Pending' => route('evaluator.evaluator.show', ['id' => $reportId]),
                'Director_assigned' => route('director.show', ['id' => $reportId]),
                'Manager_assign' => route('manager.show', ['id' => $reportId]),
                'Completed' => route('evaluation.show', ['id' => $reportId]),
                default => route('evaluatee.dashboard'),
            };

            $mailData = [
                'name' => $user->name,
                'report_title' => optional($report->reportData)->report_title,
                'version_name' => optional(optional($report->reportData)->criteriaVersion)->version_name,
                'status' => $report->status,
                'evaluatee_name' => optional($evaluatee)->name,
                'reviewer_contacts' => $reviewerContacts,
                'action_url' => $actionUrl,
                'action_text' => 'เข้าสู่รายการประเมิน',
            ];

            \Mail::send('emails.evaluatee_pending', $mailData, function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('แจ้งเตือน: มีผู้ทำการประเมินส่งแบบประเมินให้คุณตรวจสอบ');
            });
        }
    }
}
