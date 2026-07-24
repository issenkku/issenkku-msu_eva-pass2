<?php

namespace App\Services;

use App\Models\Assignments;
use App\Models\EvidenceAnswer;
use App\Models\QualityScore;
use App\Models\QualityScoreHistory;
use App\Models\QuantityScore;
use App\Models\QuantityScoreHistory;
use App\Models\QuantitySubCriteria;
use App\Models\Reports;
use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;
use App\Support\EvaluationScoreSummary;
use App\Support\SupportCriteriaReadModel;
use Carbon\Carbon;

class ReportDataService
{
    public function __construct(private SupportCriteriaReadModel $supportCriteriaReadModel) {}

    public function getEvaluatorAssignmentForUser($reportId, $user)
    {
        return Assignments::with([
            'assignmentData',
            'evaluateeUser.department',
            'evaluateeUser.position',
            'report.reportData.criteriaVersion', // Optional but useful
        ])
            ->where('report_id', $reportId)
            ->whereHas('assignmentData', function ($q) use ($user) {
                $q->where('evaluator_id', $user->id)
                    ->orWhere('evaluator_position_id', $user->position_id);
            })
            ->first();
    }

    public function getReportData($id)
    {
        $report = Reports::with([
            'reportData.criteriaVersion.quantityMainCriterias.quantitySubCriterias',
            'assignments.assignmentData',
            'assignments.assignmentData.evaluatorUser.position',
            'assignments.assignmentData.directorUser.position',
            'assignments.assignmentData.managerUser.position',
            'assignments.evaluateeUser.department',
            'assignments.evaluateeUser.position',
            'assignments',
        ])->findOrFail($id);
        $supportItemsByList = $this->supportCriteriaReadModel->forReport($report);

        $assignment = $report->assignments;
        $evaluators = $assignment->assignmentData->evaluatorUser?->name ?? '-';

        // Add evaluatee info like in dashboard
        $assignment->evaluateeName = $assignment->evaluateeUser?->name ?? '-';
        $assignment->evaluateeDepartment = $assignment->evaluateeUser?->department?->department_name ?? '-';
        $assignment->evaluateePosition = $assignment->evaluateeUser?->position?->name ?? '-';
        $assignment->evaluatorName = $assignment->assignmentData->evaluatorUser?->name ?? '-';
        $assignment->evaluatorPosition = $assignment->assignmentData->evaluatorUser?->position?->name ?? '-';
        $assignment->directorName = $assignment->assignmentData->directorUser?->name ?? '-';
        $assignment->directorPosition = $assignment->assignmentData->directorUser?->position?->name ?? '-';
        $assignment->managerName = $assignment->assignmentData->managerUser?->name ?? '-';
        $assignment->managerPosition = $assignment->assignmentData->managerUser?->position?->name ?? '-';

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

        // Process categories and their evaluation lists
        $categoryItems = $this->processCategoryItems(
            $report,
            $quantityScores,
            $quantityScoreHistories,
            $qualityScores,
            $qualityScoreHistories,
            $evidenceMap,
            $supportItemsByList
        );
        $workloadMap = $this->buildWorkloadMap($categoryItems, $id);

        return [
            'report' => $report,
            'assignment' => $assignment,
            'evaluators' => $evaluators,
            'formatThai' => $formatThai,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'reportName' => $reportName,
            'versionName' => $versionName,
            'reportComment' => $reportComment,
            'reportDescription' => $reportDescription,
            'assessmentType' => $assessmentType,
            'startTimeFormatted' => $startTimeFormatted,
            'endTimeFormatted' => $endTimeFormatted,
            'quantityMainCriterias' => $quantityMainCriterias,
            'categoryItems' => $categoryItems,
            'evidenceMap' => $evidenceMap,
            'qualityEvidenceMap' => $qualityEvidenceMap,
            'workloadMap' => $workloadMap,
            'scoreSummary' => EvaluationScoreSummary::fromCategoryItems($categoryItems),
        ];
    }

    private function buildWorkloadMap($categoryItems, $reportId)
    {
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

        if ($quantitySubCriteriaIds->isEmpty()) {
            return [];
        }

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
                ->where('report_id', $reportId)
                ->whereIn('workload_form_id', $allFormIds)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('workload_form_id');
        }

        $evidenceLinksByEntryId = EvidenceAnswer::where('report_id', $reportId)
            ->whereNotNull('workload_entry_id')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('workload_entry_id')
            ->map(fn ($answers) => $answers->pluck('link')->filter()->values());

        $workloadMap = [];
        foreach ($quantitySubCriteriaIds as $subCriteriaId) {
            $workloadMap[$subCriteriaId] = [
                'subCriteria' => $subCriteriaModels->get($subCriteriaId),
                'workloadForms' => $workloadFormsBySubCriteria->get($subCriteriaId, collect()),
                'workloadEntriesByFormId' => $workloadEntriesByFormId,
                'evidenceLinksByEntryId' => $evidenceLinksByEntryId,
            ];
        }

        return $workloadMap;
    }

    private function processCategoryItems(
        $report,
        $quantityScores,
        $quantityScoreHistories,
        $qualityScores,
        $qualityScoreHistories,
        $evidenceMap,
        $supportItemsByList
    ) {
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
                        'support_items' => $supportItemsByList[$list->id] ?? [],
                    ];

                    // Process quantity items
                    $evaluationListData['quantity_items'] = $this->processQuantityItems($list, $quantityScores, $quantityScoreHistories, $evidenceMap);

                    // Process quality items
                    $evaluationListData['quality_items'] = $this->processQualityItems($list, $qualityScores, $qualityScoreHistories, $evidenceMap);

                    $categoryData['evaluation_lists'][] = $evaluationListData;
                }

                $categoryItems[] = $categoryData;
            }
        }

        return $categoryItems;
    }

    private function processQuantityItems($list, $quantityScores, $quantityScoreHistories, $evidenceMap)
    {
        $quantityItems = [];

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

                    $quantityItems[] = $mainCriteriaData;
                }
            }
        }

        return $quantityItems;
    }

    private function processQualityItems($list, $qualityScores, $qualityScoreHistories, $evidenceMap)
    {
        $qualityItems = [];

        if ($list->qualitySubCriterias && $list->qualitySubCriterias->count() > 0) {
            $qualityMainGroups = $list->qualitySubCriterias->groupBy('quality_main_criteria_id');
            $scoreMaps = $this->buildQualityMainScoreMaps($list, $qualityScores);
            $mainScoreRaw = $scoreMaps['raw'];
            $mainScoreScaled = $scoreMaps['scaled'];

            foreach ($qualityMainGroups as $mainCriteriaId => $subCriterias) {
                $mainCriteria = $subCriterias->first()->mainCriteria;

                if ($mainCriteria) {
                    $mainCalculatedScore = $mainScoreScaled[$mainCriteriaId] ?? 0;
                    $mainRawScore = $mainScoreRaw[$mainCriteriaId] ?? 0;

                    $mainCriteriaData = [
                        'id' => $mainCriteria->id,
                        'name' => $mainCriteria->name,
                        'tooltips' => $mainCriteria->tooltips,
                        'ratio' => $mainCriteria->ratio,
                        'require_evidence' => (bool) $mainCriteria->require_evidence,
                        'main_calculated_score' => round($mainCalculatedScore, 2),
                        'sub_criterias' => [],
                    ];

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

                        $calculatedScore = null;
                        if ($hasScore && $mainRawScore > 0) {
                            $subRatio = (float) $qualityScore->score / $mainRawScore;
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

                    $qualityItems[] = $mainCriteriaData;
                }
            }
        }

        return $qualityItems;
    }

    private function buildQualityMainScoreMaps($list, $qualityScores)
    {
        $rawScores = [];
        $scaledScores = [];

        $qualityMainGroups = $list->qualitySubCriterias->groupBy('quality_main_criteria_id');
        $listTotal = 0.0;

        foreach ($qualityMainGroups as $mainCriteriaId => $subCriterias) {
            $mainTotal = 0.0;
            foreach ($subCriterias as $subCriteria) {
                $qualityScore = $qualityScores[$subCriteria->id] ?? null;
                if ($qualityScore && $qualityScore->score !== null && $qualityScore->score !== '') {
                    $mainTotal += (float) $qualityScore->score;
                }
            }
            $rawScores[$mainCriteriaId] = $mainTotal;
            $listTotal += $mainTotal;
        }

        $listMax = (float) ($list->sum_score ?? 0);
        $scale = 1.0;
        if ($listMax > 0 && $listTotal > 0 && $listTotal > $listMax) {
            $scale = $listMax / $listTotal;
        }

        foreach ($rawScores as $mainId => $mainTotal) {
            $scaledScores[$mainId] = round($mainTotal * $scale, 2);
        }

        return [
            'raw' => $rawScores,
            'scaled' => $scaledScores,
        ];
    }
}
