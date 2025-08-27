<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\EvidenceAnswer;
use App\Models\QualityScore;
use App\Models\QuantityScore;
use App\Models\Reports;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DirectorScoreController extends Controller
{
    protected $allowedEditStatuses = ['Director_assigned', 'Director_draft'];

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

        $report = Reports::with([
            'reportData.criteriaVersion.quantityMainCriterias.quantitySubCriterias',
            'assignments.assignmentData',
            'assignments.evaluateeUser.department',
            'assignments.evaluateeUser.position',
            'assignments.evaluatorUser',
        ])->findOrFail($id);

        if (in_array($report->status, ['Assigned', 'Draft', 'Pending', 'Evaluator_draft'])) {
            abort(403, 'ไม่สามารถเข้าถึงหน้าประเมินนี้ได้ เนื่องจากสถานะไม่อนุญาต');
        }

        $assignment = $report->assignments;

        // Add evaluatee info like in dashboard
        $assignment->evaluateeName = $assignment->evaluateeUser?->name ?? '-';
        $assignment->evaluateeDepartment = $assignment->evaluateeUser?->department?->department_name ?? '-';
        $assignment->evaluateePosition = $assignment->evaluateeUser?->position?->name ?? '-';
        $assignment->evaluatorName = $assignment->evaluatorUser?->name ?? '-';
        $assignment->evaluatorPosition = $assignment->evaluatorUser?->position?->name ?? '-';

        $formatThai = function ($datetime) {
            if (! $datetime) {
                return '-';
            }
            \Carbon\Carbon::setLocale('th');
            setlocale(LC_TIME, 'th_TH.UTF-8');
            $date = \Carbon\Carbon::parse($datetime);
            $year = $date->year + 543;

            return $date->translatedFormat('j F')." {$year}";
        };

        $startTime = $assignment && $assignment->assignmentData ? $assignment->assignmentData->start_time : null;
        $endTime = $assignment && $assignment->assignmentData ? $assignment->assignmentData->end_time : null;
        $reportName = $assignment && $assignment->report->reportData ? $assignment->report->reportData->report_title : 'ไม่พบชื่อรายงาน';
        $versionName = $assignment && $assignment->report->reportData->criteriaVersion ? $assignment->report->reportData->criteriaVersion->version_name : 'ไม่พบชื่อรายงาน';
        $reportComment = $assignment && $assignment->report->reportData ? $assignment->report->reportData->comment : null;
        $reportDescription = $assignment && $assignment->report->reportData ? $assignment->report->reportData->report_description : null;
        $assessmentType = $assignment && $assignment->report->reportData ? $assignment->report->reportData->assessment_type : 'ไม่พบชื่อรายงาน';
        $startTimeFormatted = $startTime ? $formatThai($startTime) : '-';
        $endTimeFormatted = $endTime ? $formatThai($endTime) : '-';
        $reportComment = $assignment && $assignment->report->reportData ? $assignment->report->reportData->comment : '-';

        $criteriaVersion = $assignment->report->reportData->criteriaVersion ?? null;
        $quantityMainCriterias = $criteriaVersion ? $criteriaVersion->quantityMainCriterias : collect();

        $quantityScores = QuantityScore::where('report_id', $id)
            ->get()
            ->keyBy('quantity_sub_criteria_id');

        $qualityScores = QualityScore::where('report_id', $id)
            ->get()
            ->keyBy('quality_sub_criteria_id');

        $evidenceAnswers = EvidenceAnswer::where('report_id', $id)
            ->get()
            ->groupBy('evaluation_list_id');

        $evidenceMap = $evidenceAnswers->mapWithKeys(function ($items, $evalListId) {
            return [$evalListId => $items->pluck('link')->filter()->values()->toArray()];
        });

        $canEdit = in_array($report->status, ['Director_assigned', 'Director_draft']);
        $readonly = ! $canEdit; // true if status is something else

        // Process categories and their evaluation lists
        $categoryItems = [];

        if ($report && $report->reportData && $report->reportData->criteriaVersion) {
            $categories = $report->reportData->criteriaVersion->categories()
                ->with(['evaluationLists' => function ($query) {
                    $query->with([
                        'quantitySubCriterias.mainCriteria',
                        'qualitySubCriterias.mainCriteria',
                    ])->orderBy('sequence');
                }])
                ->orderBy('sequence')
                ->get();

            foreach ($categories as $category) {
                $categoryData = [
                    'id' => $category->id,
                    'main_categories' => $category->main_categories,
                    'sub_categories' => $category->sub_categories,
                    'sequence' => $category->sequence,
                    'evaluation_lists' => [],
                ];

                foreach ($category->evaluationLists as $list) {
                    $evaluationListData = [
                        'id' => $list->id,
                        'name' => $list->name,
                        'annotation' => $list->annotation,
                        'sum_score' => $list->sum_score,
                        'sequence' => $list->sequence,
                        'quantity_items' => [],
                        'quality_items' => [],
                    ];

                    // Process quantity items for this evaluation list
                    if ($list->quantitySubCriterias && $list->quantitySubCriterias->count() > 0) {
                        $quantityMainGroups = $list->quantitySubCriterias->groupBy('quantity_main_criteria_id');

                        foreach ($quantityMainGroups as $mainCriteriaId => $subCriterias) {
                            $mainCriteria = $subCriterias->first()->mainCriteria;

                            if ($mainCriteria) {
                                $mainCriteriaData = [
                                    'id' => $mainCriteria->id,
                                    'name' => $mainCriteria->name,
                                    'tooltips' => $mainCriteria->tooltips,
                                    'sub_criterias' => [],
                                ];

                                foreach ($subCriterias->sortBy('sequence') as $subCriteria) {
                                    $quantityScore = $quantityScores[$subCriteria->id] ?? null;
                                    $evidenceLinks = $evidenceMap[$list->id] ?? [];

                                    $mainCriteriaData['sub_criterias'][] = [
                                        'id' => $subCriteria->id,
                                        'name' => $subCriteria->name,
                                        'sequence' => $subCriteria->sequence,
                                        'description' => $subCriteria->description ?? null,
                                        'score_a' => $subCriteria->score_a,
                                        'score_b' => $subCriteria->score_b,
                                        'tor_compliant' => $quantityScore?->score_C ?? '',
                                        'score_d' => $quantityScore?->score_D ?? '',
                                        'score_description' => $quantityScore->description ?? '',
                                        'evidence' => $evidenceLinks,
                                    ];
                                }

                                $evaluationListData['quantity_items'][] = $mainCriteriaData;
                            }
                        }
                    }

                    // Process quality items for this evaluation list
                    if ($list->qualitySubCriterias && $list->qualitySubCriterias->count() > 0) {
                        $qualityMainGroups = $list->qualitySubCriterias->groupBy('quality_main_criteria_id');

                        foreach ($qualityMainGroups as $mainCriteriaId => $subCriterias) {
                            $mainCriteria = $subCriterias->first()->mainCriteria;

                            if ($mainCriteria) {
                                $mainCriteriaData = [
                                    'id' => $mainCriteria->id,
                                    'name' => $mainCriteria->name,
                                    'tooltips' => $mainCriteria->tooltips,
                                    'sub_criterias' => [],
                                ];

                                foreach ($subCriterias->sortBy('sequence') as $subCriteria) {
                                    $qualityScore = $qualityScores[$subCriteria->id] ?? null;
                                    $evidenceLinks = $evidenceMap[$list->id] ?? [];
                                    
                                    $hasScore = $qualityScore && $qualityScore->score !== null && $qualityScore->score !== '';
                                    $userSelected = $hasScore || ($qualityScore && $qualityScore->score !== null);

                                    $mainCriteriaData['sub_criterias'][] = [
                                        'id' => $subCriteria->id,
                                        'name' => $subCriteria->name,
                                        'sequence' => $subCriteria->sequence,
                                        'num_score' => $subCriteria->num_score,
                                        'user_selected' => $userSelected,
                                        'score' => $qualityScore?->score ?? '',
                                        'evidence' => $evidenceLinks,
                                    ];
                                }

                                $evaluationListData['quality_items'][] = $mainCriteriaData;
                            }
                        }
                    }

                    $categoryData['evaluation_lists'][] = $evaluationListData;
                }

                $categoryItems[] = $categoryData;
            }
        }

        if ($readonly && $request->query('readonly') != 1) {
            return redirect()->route('director.show', ['id' => $id, 'readonly' => 1]);
        }

        return view('director_dashboard.director', compact(
            'id', 'user', 'report', 'assignment', 'formatThai',
            'startTime', 'endTime', 'reportName',
            'startTimeFormatted', 'endTimeFormatted', 'assessmentType',
            'quantityMainCriterias', 'categoryItems', 'evidenceMap',
            'readonly', 'versionName', 'reportComment', 'reportDescription'
        ));
    }

    public function storeDirectorScores(Request $request, $reportId)
    {
        try {
            $reportId = is_array($reportId) ? $reportId[0] : (int) $reportId;
            $report = Reports::findOrFail($reportId);

            $statusCheck = $this->checkReportEditableStatus($report, 'process evaluation scores');
            if ($statusCheck) {
                return $statusCheck;
            }

            $validated = $request->validate([
                'quantity_list' => 'nullable|array',
                'quantity_list.*.quantity_sub_criteria_id' => 'nullable|integer|exists:quantity_sub_criterias,id',
                'quantity_list.*.score_C' => 'nullable|numeric',

                'quality_list' => 'nullable|array',
                'quality_list.*.quality_sub_criteria_id' => 'nullable|integer|exists:quality_sub_criterias,id',
                'quality_list.*.score' => 'nullable|numeric',

                'status' => 'required|string|in:Director_assigned,Manager_assign,Director_draft,Submitted',
                'comment' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // Delete existing records for this report
            QuantityScore::where('report_id', $reportId)->delete();
            QualityScore::where('report_id', $reportId)->delete();

            if (isset($validated['quantity_list'])) {
                foreach ($validated['quantity_list'] as $item) {
                    $subCriteriaId = is_array($item['quantity_sub_criteria_id'])
                        ? $item['quantity_sub_criteria_id'][0]
                        : (int) $item['quantity_sub_criteria_id'];

                    $subCriteria = \App\Models\QuantitySubCriteria::find($subCriteriaId);
                    $scoreC = $item['score_C'] ?? null;

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
                    ]);
                }
            }

            // ✅ Quality loop with check
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
                }
            }

            $status = $validated['status'];
            $report->status = $status;

            if (isset($validated['comment'])) {
                $report->comment = $validated['comment'];
            }

            $report->save();
            // if ($report->save() && $status === 'Pending') {
            //     $this->sendEvaluationCompletedMail($reportId);
            // }

            DB::commit();

            $message = $status === 'Director_draft' ? 'บันทึกข้อมูลเรียบร้อยแล้ว' : 'ส่งรายงานเรียบร้อยแล้ว';

            return redirect('/director-dashboard')->with('success', $message);

        } catch (Exception $e) {
            DB::rollback();

            return response()->json(['message' => 'Error processing evaluation scores', 'error' => $e->getMessage()], 500);
        }
    }
}
