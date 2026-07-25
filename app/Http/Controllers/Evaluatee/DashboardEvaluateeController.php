<?php

namespace App\Http\Controllers\Evaluatee;

use App\Http\Controllers\Controller;
use App\Models\EvidenceAnswer;
use App\Models\QualityScore;
use App\Models\QualityScoreHistory;
use App\Models\QuantityScore;
use App\Models\QuantityScoreHistory;
use App\Models\QuantitySubCriteria;
use App\Models\Reports;
use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;
use App\Services\GraphDataService;
use App\Services\ScoreService;
use App\Support\AssignmentFlow;
use App\Support\EvaluateeDashboardAssignments;
use App\Support\EvaluateeDashboardOverview;
use App\Support\EvaluationScoreSummary;
use App\Support\EvaluationSummaryData;
use App\Support\SupportCriteriaReadModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardEvaluateeController extends Controller
{
    /**
     * ???????????????????????????????????
     */
    public function index(Request $request)
    {
        $user = $request->user()->load([
            'position',
            'department',
            'assignment.assignmentData.evaluatorUser.position',
            'assignment.assignmentData.directorUser.position',
            'assignment.assignmentData.managerUser.position',
            'assignment.report.reportData',
        ]);

        $evaluations = $user->assignment->map(function ($assignment) {
            $assignment->reviewerEntries = $this->reviewerEntries($assignment->assignmentData)->all();
            $assignment->evaluatorName = $this->buildReviewerText($assignment->assignmentData);

            return $assignment;
        });

        $userReports = $user->assignment->map(function ($assignment) {
            return $assignment->report;
        })->filter();

        $averageScore = ScoreService::calculateAverageScore($userReports);
        $highestScore = ScoreService::calculateHighestScore($userReports);
        $scatterData = GraphDataService::scatterData($userReports);
        $latestCompletedScore = optional(
            $user->assignment
                ->filter(fn ($assignment) => optional($assignment->report)->status === 'Completed')
                ->sortByDesc(fn ($assignment) => optional($assignment->report)->updated_at ?? optional($assignment->assignmentData)->end_time)
                ->first()
        )->report->score ?? 0;

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $evaluations = $evaluations->filter(function ($assignment) use ($searchTerm) {
                $evaluatorName = strtolower($assignment->evaluatorName ?? $this->buildReviewerText($assignment->assignmentData));
                $reportTitle = optional(optional($assignment->report)->reportData)->report_title ?? '';

                return str_contains(strtolower($evaluatorName), strtolower($searchTerm))
                    || str_contains(strtolower($reportTitle), strtolower($searchTerm));
            });
        }

        $years = $user->assignment->pluck('assignmentData.start_time')
            ->filter()
            ->map(function ($dt) {
                return Carbon::parse($dt)->year;
            })
            ->unique()
            ->sortDesc()
            ->values();

        if ($request->filled('year')) {
            $evaluations = $evaluations->filter(function ($assignment) use ($request) {
                $year = Carbon::parse(optional($assignment->assignmentData)->start_time)->year ?? null;

                return $year == $request->input('year');
            });
        }

        // สรุปข้อมูลงานค้างและสถานะทั้งหมดจาก collection กลางเพื่อลด logic ซ้ำใน controller
        $assignmentSummary = EvaluateeDashboardAssignments::build($evaluations);
        $statusCounts = $assignmentSummary['statusCounts'];
        $unfinishedAssignments = $assignmentSummary['unfinishedAssignments'];
        $overdueAssignments = $assignmentSummary['overdueAssignments'];
        $totalAssignments = $assignmentSummary['totalAssignments'];
        $completedAssignments = $assignmentSummary['completedAssignments'];
        $notStartedAssignments = $assignmentSummary['notStartedAssignments'];
        $inProgressAssignments = $assignmentSummary['inProgressAssignments'];
        $actionRequiredAssignments = $assignmentSummary['actionRequiredAssignments'];
        $inReviewAssignments = $assignmentSummary['inReviewAssignments'];
        $dueSoonAssignments = $assignmentSummary['dueSoonAssignments'];
        $evaluateeOverview = EvaluateeDashboardOverview::build(
            $notStartedAssignments,
            $inProgressAssignments,
            $actionRequiredAssignments,
            $inReviewAssignments,
            $completedAssignments,
            $totalAssignments,
            collect($unfinishedAssignments),
            collect($overdueAssignments)
        );

        $page = $request->input('page', 1);
        $perPage = 10;
        $paginatedEvaluations = new LengthAwarePaginator(
            $evaluations->forPage($page, $perPage)->values(),
            $evaluations->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $evaluationSummary = EvaluationSummaryData::fromAssignments(
            $paginatedEvaluations->getCollection(),
            $statusCounts,
            $request->input('status')
        );

        return view('evaluatee.dashboard', [
            'user' => $user,
            'statusCounts' => $statusCounts,
            'evaluations' => $paginatedEvaluations,
            'years' => $years,
            'averageScore' => $averageScore,
            'highestScore' => $highestScore,
            'scatterData' => $scatterData,
            'latestCompletedScore' => round((float) $latestCompletedScore, 2),
            'unfinishedAssignments' => $unfinishedAssignments,
            'overdueAssignments' => $overdueAssignments,
            'totalAssignments' => $totalAssignments,
            'notStartedAssignments' => $notStartedAssignments,
            'inProgressAssignments' => $inProgressAssignments,
            'completedAssignments' => $completedAssignments,
            'actionRequiredAssignments' => $actionRequiredAssignments,
            'inReviewAssignments' => $inReviewAssignments,
            'dueSoonAssignments' => $dueSoonAssignments,
            'overdueCount' => $overdueAssignments->count(),
            'evaluationSummary' => $evaluationSummary,
            'evaluateeOverview' => $evaluateeOverview,
        ]);
    }

    private function buildReviewerText($assignmentData): string
    {
        $reviewers = $this->reviewerEntries($assignmentData)
            ->map(fn (array $reviewer) => "{$reviewer['label']}: {$reviewer['name']}");

        return $reviewers->isNotEmpty() ? $reviewers->implode(', ') : '-';
    }

    private function reviewerEntries($assignmentData): Collection
    {
        if (! $assignmentData) {
            return collect();
        }

        $stageLabels = [
            'evaluator' => 'ผู้ประเมิน',
            'director' => 'กรรมการ',
            'manager' => 'ผู้บริหาร',
        ];

        return collect(AssignmentFlow::stagesFor($assignmentData))
            ->map(function (string $stage) use ($assignmentData, $stageLabels) {
                $user = match ($stage) {
                    'evaluator' => $assignmentData->evaluatorUser,
                    'director' => $assignmentData->directorUser,
                    'manager' => $assignmentData->managerUser,
                    default => null,
                };

                if (! $user) {
                    return null;
                }

                return [
                    'label' => $stageLabels[$stage] ?? 'ผู้ประเมิน',
                    'name' => $user->name,
                    'position' => $user->position?->name ?? null,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * ???????????????????????????????????????
     */
    public function evaluation(Request $request, $id, SupportCriteriaReadModel $supportCriteriaReadModel)
    {
        $user = $request->user()->load('position', 'department');

        $report = Reports::with([
            'reportData.criteriaVersion.quantityMainCriterias.quantitySubCriterias' => fn ($query) => $query
                ->active()
                ->with('evaluationList'),
            'reportData.criteriaVersion.qualityMainCriterias.qualitySubCriterias.evaluationList',
            'reportData.criteriaVersion.categories.evaluationLists.quantitySubCriterias' => fn ($query) => $query
                ->active()
                ->with('mainCriteria'),
            'reportData.criteriaVersion.categories.evaluationLists.qualitySubCriterias.mainCriteria',
            'reportData.criteriaVersion.categories.evaluationLists.supportCriterias',
            'assignments.assignmentData.evaluatorUser',
        ])->findOrFail($id);
        $supportItemsByList = $supportCriteriaReadModel->forReport($report);

        // Single assignment per report (report_id is unique in assignments)
        $assignment = $report->assignments;

        if (! $assignment || $assignment->evaluatee_id !== $user->id) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงรายงานนี้');
        }

        $formatThai = function ($datetime) {
            if (! $datetime) {
                return '-';
            }
            Carbon::setLocale('th');
            setlocale(LC_TIME, 'th_TH.UTF-8');
            $date = Carbon::parse($datetime);
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

        $quantityScores = QuantityScore::with('modifierUser:id,name,prefix')
            ->where('report_id', $id)
            ->get()
            ->keyBy('quantity_sub_criteria_id');

        $quantityScoreHistories = QuantityScoreHistory::with('modifierUser:id,name,prefix')
            ->where('report_id', $id)
            ->latest()
            ->get()
            ->groupBy('quantity_sub_criteria_id');

        $qualityScores = QualityScore::where('report_id', $id)
            ->get()
            ->keyBy('quality_sub_criteria_id');

        $qualityScoreHistories = QualityScoreHistory::with('modifierUser:id,name,prefix')
            ->where('report_id', $id)
            ->latest()
            ->get()
            ->groupBy('quality_sub_criteria_id');

        $evidenceAnswers = EvidenceAnswer::where('report_id', $id)
            ->whereNull('support_criteria_id')
            ->get()
            ->groupBy('evaluation_list_id');

        $evidenceMap = $evidenceAnswers->mapWithKeys(function ($items, $evalListId) {
            return [$evalListId => $items->pluck('link')->filter()->values()->toArray()];
        });

        $qualityEvidenceMap = EvidenceAnswer::where('report_id', $id)
            ->whereNotNull('quality_main_criteria_id')
            ->get()
            ->groupBy('quality_main_criteria_id')
            ->mapWithKeys(function ($items, $mainId) {
                return [$mainId => $items->pluck('link')->filter()->values()->toArray()];
            });

        $qualityData = DB::table('quality_scores')
            ->join('quality_sub_criterias', 'quality_scores.quality_sub_criteria_id', '=', 'quality_sub_criterias.id')
            ->join('quality_main_criterias', 'quality_sub_criterias.quality_main_criteria_id', '=', 'quality_main_criterias.id')
            ->join('evaluation_lists', 'quality_sub_criterias.evaluation_list_id', '=', 'evaluation_lists.id')
            ->where('quality_scores.report_id', $id)
            ->selectRaw('
                quality_sub_criterias.evaluation_list_id,
                quality_main_criterias.id as main_id,
                quality_main_criterias.ratio,
                evaluation_lists.sum_score,
                SUM(quality_scores.score) as total_score,
                SUM(quality_sub_criterias.num_score) as total_max_score
            ')
            ->groupBy(
                'quality_sub_criterias.evaluation_list_id',
                'quality_main_criterias.id',
                'quality_main_criterias.ratio',
                'evaluation_lists.sum_score'
            )
            ->get();

        // ✅ Build arrScoreEva lookup array
        $arrScoreEva = [];
        foreach ($qualityData as $row) {
            $maxSum = (float) $row->total_max_score;
            $accSum = (float) $row->total_score;
            $ratio = (float) $row->ratio;
            $sumScoreEva = (float) $row->sum_score;

            if ($maxSum > 0) {
                $scoreRatioMain = $ratio * ($accSum / $maxSum);
                $calculatedScore = ($scoreRatioMain / 100) * $sumScoreEva;

                // Store with composite key for lookup
                $key = $row->evaluation_list_id.'_'.$row->main_id;
                $arrScoreEva[$key] = $calculatedScore;
            }
        }

        $canEdit = in_array($report->status, ['Draft', 'Assigned']);
        $readonly = ! $canEdit; // true if status is something else

        // Process categories and their evaluation lists
        $categoryItems = [];

        if ($report && $report->reportData && $report->reportData->criteriaVersion) {
            $categories = $report->reportData->criteriaVersion->categories()
                ->with(['evaluationLists' => function ($query) {
                    $query->with([
                        'quantitySubCriterias' => fn ($quantityQuery) => $quantityQuery
                            ->active()
                            ->with('mainCriteria'),
                        'qualitySubCriterias.mainCriteria',
                        'supportCriterias',
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
                        'quantity_enabled' => (bool) $list->quantity_enabled,
                        'quantity_items' => [],
                        'quality_items' => [],
                        'support_items' => $supportItemsByList[$list->id] ?? [],
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
                                    $scoreHistories = ($quantityScoreHistories[$subCriteria->id] ?? collect())->map(function ($history) {
                                        return [
                                            'previous_value' => $history->previous_score_c,
                                            'new_value' => $history->new_score_c,
                                            'reason' => $history->reason,
                                            'modified_by_name' => $history->modifierUser?->display_name ?? $history->modifierUser?->name ?? '',
                                            'modified_by_role' => $history->modifier_role ?? '',
                                            'created_at' => optional($history->created_at)->format('d/m/Y H:i'),
                                        ];
                                    })->values()->all();
                                    $evidenceLinks = $evidenceMap[$list->id] ?? [];

                                    $mainCriteriaData['sub_criterias'][] = [
                                        'id' => $subCriteria->id,
                                        'name' => $subCriteria->name,
                                        'sequence' => $subCriteria->sequence,
                                        'description' => $subCriteria->description ?? null,
                                        'require_evidence' => (bool) $subCriteria->require_evidence,
                                        'score_a' => $subCriteria->score_a,
                                        'score_b' => $subCriteria->score_b,
                                        'tor_compliant' => $quantityScore?->score_C !== null ? max(0, (float) $quantityScore->score_C) : '',
                                        'score_d' => $quantityScore?->score_D !== null ? max(0, (float) $quantityScore->score_D) : '',
                                        'score_description' => $quantityScore->description ?? '',
                                        'score_modified_by_name' => $quantityScore?->modifierUser?->display_name ?? $quantityScore?->modifierUser?->name ?? '',
                                        'score_modified_by_role' => $quantityScore?->modifier_role ?? '',
                                        'score_histories' => $scoreHistories,
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
                                $arrScoreEvaKey = $list->id.'_'.$mainCriteriaId;
                                $mainCalculatedScore = $arrScoreEva[$arrScoreEvaKey] ?? 0;

                                $mainCriteriaData = [
                                    'id' => $mainCriteria->id,
                                    'name' => $mainCriteria->name,
                                    'tooltips' => $mainCriteria->tooltips,
                                    'ratio' => $mainCriteria->ratio,
                                    'require_evidence' => (bool) $mainCriteria->require_evidence,
                                    'allow_multiple' => (bool) ($mainCriteria->allow_multiple ?? false),
                                    'main_calculated_score' => round($mainCalculatedScore, 2),
                                    'sub_criterias' => [],
                                ];

                                // Calculate totals for sub-criteria distribution
                                $totalScore = 0;
                                $totalMaxScore = 0;
                                foreach ($subCriterias as $subCriteria) {
                                    $qualityScore = $qualityScores[$subCriteria->id] ?? null;
                                    if ($qualityScore && $qualityScore->score !== null && $qualityScore->score !== '') {
                                        $totalScore += (float) $qualityScore->score;
                                    }
                                    $totalMaxScore += (float) $subCriteria->num_score;
                                }

                                foreach ($subCriterias->sortBy('sequence') as $subCriteria) {
                                    $qualityScore = $qualityScores[$subCriteria->id] ?? null;
                                    $scoreHistories = ($qualityScoreHistories[$subCriteria->id] ?? collect())->map(function ($history) {
                                        return [
                                            'previous_value' => $history->previous_score,
                                            'new_value' => $history->new_score,
                                            'reason' => $history->reason,
                                            'modified_by_name' => $history->modifierUser?->display_name ?? $history->modifierUser?->name ?? '',
                                            'modified_by_role' => $history->modifier_role ?? '',
                                            'created_at' => optional($history->created_at)->format('d/m/Y H:i'),
                                        ];
                                    })->values()->all();
                                    $evidenceLinks = $evidenceMap[$list->id] ?? [];

                                    $hasScore = $qualityScore && $qualityScore->score !== null && $qualityScore->score !== '';
                                    $userSelected = $hasScore || ($qualityScore && $qualityScore->score !== null);

                                    // ✅ Calculate sub-criteria's portion of the main calculated score
                                    $calculatedScore = null;
                                    if ($hasScore && $totalMaxScore > 0) {
                                        $subRatio = $subCriteria->num_score / $totalMaxScore;
                                        $calculatedScore = round($mainCalculatedScore * $subRatio, 2);
                                    }

                                    $mainCriteriaData['sub_criterias'][] = [
                                        'id' => $subCriteria->id,
                                        'name' => $subCriteria->name,
                                        'sequence' => $subCriteria->sequence,
                                        'num_score' => $subCriteria->num_score,
                                        'description' => $subCriteria->description,
                                        'user_selected' => $userSelected,
                                        'score' => $qualityScore?->score ?? '',
                                        'calculated_score' => $calculatedScore,
                                        'score_histories' => $scoreHistories,
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

        $quantitySubCriteriaIds = collect($categoryItems)
            ->flatMap(function ($category) {
                return collect($category['evaluation_lists'] ?? [])
                    ->flatMap(function ($list) {
                        return collect($list['quantity_items'] ?? [])
                            ->flatMap(function ($main) {
                                return collect($main['sub_criterias'] ?? [])->pluck('id');
                            });
                    });
            })
            ->filter()
            ->unique()
            ->values();

        $workloadMap = [];
        if ($quantitySubCriteriaIds->isNotEmpty()) {
            $subCriteriaModels = QuantitySubCriteria::with(['groups.items'])
                ->whereIn('id', $quantitySubCriteriaIds)
                ->get()
                ->keyBy('id');

            $workloadFormsBySubCriteria = WorkloadForm::with(['fields', 'items', 'subCriteriaItem.group'])
                ->whereIn('quantity_sub_criteria_id', $quantitySubCriteriaIds)
                ->get()
                ->groupBy('quantity_sub_criteria_id');

            $allFormIds = $workloadFormsBySubCriteria
                ->flatten(1)
                ->pluck('id')
                ->filter()
                ->unique()
                ->values();

            $workloadEntriesByFormId = collect();
            if ($allFormIds->isNotEmpty()) {
                $workloadEntriesByFormId = WorkloadEntry::with(['subject'])
                    ->where('report_id', $id)
                    ->whereIn('workload_form_id', $allFormIds)
                    ->orderByDesc('created_at')
                    ->get()
                    ->groupBy('workload_form_id');
            }

            $evidenceLinksByEntryId = EvidenceAnswer::where('report_id', $id)
                ->whereNotNull('workload_entry_id')
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('workload_entry_id')
                ->map(fn ($answers) => $answers->pluck('link')->filter()->values());

            foreach ($quantitySubCriteriaIds as $subCriteriaId) {
                $workloadMap[$subCriteriaId] = [
                    'subCriteria' => $subCriteriaModels->get($subCriteriaId),
                    'workloadForms' => $workloadFormsBySubCriteria->get($subCriteriaId, collect()),
                    'workloadEntriesByFormId' => $workloadEntriesByFormId,
                    'evidenceLinksByEntryId' => $evidenceLinksByEntryId,
                ];
            }
        }

        if ($readonly && $request->query('readonly') != 1) {
            return redirect()->route('evaluation.show', ['id' => $id, 'readonly' => 1]);
        }

        $scoreSummary = EvaluationScoreSummary::fromCategoryItems($categoryItems);

        return view('evaluatee.evaluation', compact(
            'id', 'user', 'report', 'assignment', 'formatThai',
            'startTime', 'endTime', 'reportName',
            'startTimeFormatted', 'endTimeFormatted', 'assessmentType',
            'quantityMainCriterias', 'categoryItems', 'evidenceMap', 'qualityEvidenceMap',
            'readonly', 'versionName', 'reportComment', 'reportDescription',
            'workloadMap', 'scoreSummary'
        ));
    }
}
