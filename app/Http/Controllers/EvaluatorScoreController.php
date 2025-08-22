<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignments;
use App\Models\EvidenceAnswer;
use App\Models\Reports;
use App\Models\QualityMainCriteria;
use App\Models\QualityScore;
use App\Models\QuantityMainCriteria;
use App\Models\QuantityScore;

class EvaluatorScoreController extends Controller
{
    public function evaluator(Request $request, $id)
    {
        $user = $request->user()->load('position', 'department');

        $report = Reports::with([
            'reportData.criteriaVersion.quantityMainCriterias.quantitySubCriterias',
            'assignments.assignmentData',
            'assignments.evaluateeUser.department',
            'assignments.evaluateeUser.position',
            'assignments.evaluatorUser',
        ])->findOrFail($id);

        // Find the assignment for the current evaluator
        $assignment = Assignments::with([
                'assignmentData',
                'evaluateeUser.department',
                'evaluateeUser.position',
            ])
            ->where('report_id', $id)
            ->whereHas('assignmentData', function ($q) use ($user) {
                $q->where('evaluator_position_id', $user->position_id);
            })
            ->whereHas('evaluateeUser', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })
            ->first();

        if (!$assignment) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงรายงานนี้');
        }

        // Add evaluatee info like in dashboard
        $assignment->evaluateeName = $assignment->evaluateeUser?->name ?? '-';
        $assignment->evaluateeDepartment = $assignment->evaluateeUser?->department?->department_name ?? '-';
        $assignment->evaluateePosition = $assignment->evaluateeUser?->position?->name ?? '-';
        $assignment->evaluatorName = $assignment->evaluatorUser?->name ?? '-';

        $formatThai = function($datetime) {
            if (!$datetime) return '-';
            \Carbon\Carbon::setLocale('th');
            setlocale(LC_TIME, 'th_TH.UTF-8');
            $date = \Carbon\Carbon::parse($datetime);
            $year = $date->year + 543;
            return $date->translatedFormat('j F') . " {$year}";
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
            ->keyBy('evaluation_list_id');

        $evidenceMap = $evidenceAnswers->mapWithKeys(function ($item) {
            return [$item->evaluation_list_id => $item->link];
        });

        $canEdit = in_array($report->status, ['Pending', 'Evaluator_draft']);
        $readonly = !$canEdit; // true if status is something else

        // Process categories and their evaluation lists
        $categoryItems = [];

        if ($report && $report->reportData && $report->reportData->criteriaVersion) {
            $categories = $report->reportData->criteriaVersion->categories()
                ->with(['evaluationLists' => function($query) {
                    $query->with([
                        'quantitySubCriterias.mainCriteria', 
                        'qualitySubCriterias.mainCriteria'
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
                    'evaluation_lists' => []
                ];

                foreach ($category->evaluationLists as $list) {
                    $evaluationListData = [
                        'id' => $list->id,
                        'name' => $list->name,
                        'annotation' => $list->annotation,
                        'sum_score' => $list->sum_score,
                        'sequence' => $list->sequence,
                        'quantity_items' => [],
                        'quality_items' => []
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
                                    'sub_criterias' => []
                                ];

                                foreach ($subCriterias->sortBy('sequence') as $subCriteria) {
                                    $quantityScore = $quantityScores[$subCriteria->id] ?? null;
                                    $evidenceLink = $evidenceAnswers[$list->id]->link ?? '';

                                    $mainCriteriaData['sub_criterias'][] = [
                                        'id' => $subCriteria->id,
                                        'name' => $subCriteria->name,
                                        'sequence' => $subCriteria->sequence,
                                        'description' => $subCriteria->description ?? null,
                                        'score_a' => $subCriteria->score_a,
                                        'score_b' => $subCriteria->score_b,
                                        'tor_compliant' => $quantityScore?->score_C ?? '', 
                                        'score_description' => $quantityScore->description ?? '',
                                        'evidence' => $evidenceLink,
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
                                    'sub_criterias' => []
                                ];

                                foreach ($subCriterias->sortBy('sequence') as $subCriteria) {
                                    $qualityScore = $qualityScores[$subCriteria->id] ?? null;
                                    $evidenceLink = $evidenceAnswers[$list->id]->link ?? '';
                                    
                                    $hasScore = $qualityScore && $qualityScore->score !== null && $qualityScore->score !== '';
                                    $userSelected = $hasScore || ($qualityScore && $qualityScore->score !== null);

                                    $mainCriteriaData['sub_criterias'][] = [
                                        'id' => $subCriteria->id,
                                        'name' => $subCriteria->name,
                                        'sequence' => $subCriteria->sequence,
                                        'num_score' => $subCriteria->num_score,
                                        'user_selected' => $userSelected,
                                        'score' => $qualityScore?->score ?? '',
                                        'evidence' => $evidenceLink,
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
            return redirect()->route('evaluator.evaluator.show', ['id' => $id, 'readonly' => 1]);
        }
        
        return view('evaluator_dashboard.evaluator', compact(
            'id', 'user', 'report', 'assignment', 'formatThai',
            'startTime', 'endTime', 'reportName',
            'startTimeFormatted', 'endTimeFormatted', 'assessmentType',
            'quantityMainCriterias', 'categoryItems', 'evidenceMap',
            'readonly', 'versionName', 'reportComment', 'reportDescription'
        ));
    }

    protected $allowedEditStatuses = ['Pending', 'Director_assigned'];

    protected function checkReportEditableStatus(Reports $report, $action)
    {
        if (! in_array($report->status, $this->allowedEditStatuses)) {
            return response()->json([
                'message' => "Cannot {$action}. Report must be in Assigned or Draft status.",
            ], 403);
        }

        return null; // ถ้าผ่านการตรวจสอบ
    }

    public function storeEvaluatorScores (Request $request, $reportId)
    {
        
    }
}
