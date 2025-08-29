<?php

namespace App\Http\Controllers\Evaluatee;

use App\Http\Controllers\Controller;
use App\Models\EvidenceAnswer;
use App\Models\QualityScore;
use App\Models\QuantityScore;
use App\Models\Reports;
use App\Services\GraphDataService;
use App\Services\ScoreService;
use Illuminate\Http\Request;

class DashboardEvaluateeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load([
            'position',
            'department',
            'assignment.assignmentData', // Load nested relationships
            'assignment.report.reportData',
        ]);

        // $evaluations = $user->assignment->pluck('report')->filter();

        $evaluations = $user->assignment->map(function ($assignment) {
            $assignment->load('evaluatorUser');

            return $assignment;
        });

        $userReports = $user->assignment->map(function ($assignment) {
            return $assignment->report;
        })->filter();

        $averageScore = ScoreService::calculateAverageScore($userReports);
        $highestScore = ScoreService::calculateHighestScore($userReports);
        $scatterData = GraphDataService::scatterData($userReports);

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $evaluations = $evaluations->filter(function ($assignment) use ($searchTerm) {
                $evaluatorName = optional($assignment->evaluatorUser)->name ?? '';
                $reportTitle = optional(optional($assignment->report)->reportData)->report_title ?? '';

                return str_contains(strtolower($evaluatorName), strtolower($searchTerm))
                    || str_contains(strtolower($reportTitle), strtolower($searchTerm));
            });
        }

        $years = $user->assignment->pluck('assignmentData.start_time')
            ->filter()
            ->map(function ($dt) {
                return \Carbon\Carbon::parse($dt)->year;
            })
            ->unique()
            ->sortDesc()
            ->values();

        if ($request->filled('year')) {
            $evaluations = $evaluations->filter(function ($assignment) use ($request) {
                $year = \Carbon\Carbon::parse(optional($assignment->assignmentData)->start_time)->year ?? null;

                return $year == $request->input('year');
            });
        }

        $statusCounts = [
            'ทั้งหมด' => $evaluations->count(),
            'ยังไม่ประเมิน' => $this->countByStatus($evaluations, ['Assigned']),
            'กำลังดำเนินการ' => $this->countByStatus($evaluations, ['Draft']),
            'รอผลการประเมิน' => $this->countByStatus($evaluations, [
                'Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft',
                'Manager_assign', 'Manager_draft',
            ]),
            'ประเมินเสร็จสิ้น' => $this->countByStatus($evaluations, ['Completed']),
        ];

        return view('evaluatee.dashboard', [
            'user' => $user,
            'statusCounts' => $statusCounts,
            'evaluations' => $evaluations,
            'years' => $years,
            'averageScore' => $averageScore,
            'highestScore' => $highestScore,
            'scatterData' => $scatterData,
        ]);
    }

    private function countByStatus($evaluations, $statuses)
    {
        return $evaluations->filter(function ($assignment) use ($statuses) {
            $reportStatus = optional($assignment->report)->status ?? 'Assigned';

            return in_array($reportStatus, $statuses);
        })->count();
    }

    public function evaluation(Request $request, $id)
    {
        $user = $request->user()->load('position', 'department');

        $report = Reports::with([
            'reportData.criteriaVersion.quantityMainCriterias.quantitySubCriterias.evaluationList',
            'reportData.criteriaVersion.qualityMainCriterias.qualitySubCriterias.evaluationList',
            'reportData.criteriaVersion.categories.evaluationLists.quantitySubCriterias.mainCriteria',
            'reportData.criteriaVersion.categories.evaluationLists.qualitySubCriterias.mainCriteria',
            'assignments.assignmentData',
        ])->findOrFail($id);

        // Find the assignment for the current user
        $assignment = $report->assignments->where('evaluatee_id', $user->id)->first();
        $evaluator = $assignment->getEvaluatorUser();

        if (! $assignment) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงรายงานนี้');
        }

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

        $canEdit = in_array($report->status, ['Draft', 'Assigned']);
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
                                    'formulas' => $mainCriteria->formulas->map(function ($formula) {
                                        return [
                                            'id' => $formula->id,
                                            'condition' => $formula->condition,
                                        ];
                                    }),
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
            return redirect()->route('evaluation.show', ['id' => $id, 'readonly' => 1]);
        }

        return view('evaluatee.evaluation', compact(
            'id', 'user', 'report', 'assignment', 'formatThai',
            'startTime', 'endTime', 'reportName', 'evaluator',
            'startTimeFormatted', 'endTimeFormatted', 'assessmentType',
            'quantityMainCriterias', 'categoryItems', 'evidenceMap',
            'readonly', 'versionName', 'reportComment', 'reportDescription'
        ));
    }
}

// Debug output - you can remove this after checking
// dd([
//     'evaluation_lists_count' => $evaluationLists->count(),
//     'evaluation_lists_structure' => $evaluationLists->map(function($list) {
//         return [
//             'id' => $list->id,
//             'name' => $list->name,
//             'annotation' => $list->annotation,
//             'quantity_sub_criterias_count' => $list->quantitySubCriterias ? $list->quantitySubCriterias->count() : 0,
//             'quality_sub_criterias_count' => $list->qualitySubCriterias ? $list->qualitySubCriterias->count() : 0,
//             'quantity_sub_criterias' => $list->quantitySubCriterias ? $list->quantitySubCriterias->pluck('name', 'id') : [],
//             'quality_sub_criterias' => $list->qualitySubCriterias ? $list->qualitySubCriterias->pluck('name', 'id') : [],
//         ];
//     }),
// ]);
