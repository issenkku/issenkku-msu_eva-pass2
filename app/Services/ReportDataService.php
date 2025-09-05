<?php
namespace App\Services;

use App\Models\Reports;
use App\Models\QuantityScore;
use App\Models\QualityScore;
use App\Models\EvidenceAnswer;
use Illuminate\Support\Facades\DB;

class ReportDataService
{
    public function getReportData($id)
    {
        $report = Reports::with([
            'reportData.criteriaVersion.quantityMainCriterias.quantitySubCriterias',
            'assignments.assignmentData',
            'assignments.evaluateeUser.department',
            'assignments.evaluateeUser.position',
            'assignments.evaluatorUser',
        ])->findOrFail($id);

        $assignment = $report->assignments;
        $evaluators = $assignment ? $assignment->getEvaluatorUsers() : collect();

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

        // Build arrScoreEva lookup array
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
                $key = $row->evaluation_list_id . '_' . $row->main_id;
                $arrScoreEva[$key] = $calculatedScore;
            }
        }

        // Process categories and their evaluation lists
        $categoryItems = $this->processCategoryItems($report, $quantityScores, $qualityScores, $evidenceMap, $arrScoreEva);

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
        ];
    }

    private function processCategoryItems($report, $quantityScores, $qualityScores, $evidenceMap, $arrScoreEva)
    {
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

                    // Process quantity items
                    $evaluationListData['quantity_items'] = $this->processQuantityItems($list, $quantityScores, $evidenceMap);
                    
                    // Process quality items
                    $evaluationListData['quality_items'] = $this->processQualityItems($list, $qualityScores, $evidenceMap, $arrScoreEva);

                    $categoryData['evaluation_lists'][] = $evaluationListData;
                }

                $categoryItems[] = $categoryData;
            }
        }

        return $categoryItems;
    }

    private function processQuantityItems($list, $quantityScores, $evidenceMap)
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
                        'formulas' => $mainCriteria->formulas->map(function($formula) {
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

                    $quantityItems[] = $mainCriteriaData;
                }
            }
        }

        return $quantityItems;
    }

    private function processQualityItems($list, $qualityScores, $evidenceMap, $arrScoreEva)
    {
        $qualityItems = [];

        if ($list->qualitySubCriterias && $list->qualitySubCriterias->count() > 0) {
            $qualityMainGroups = $list->qualitySubCriterias->groupBy('quality_main_criteria_id');

            foreach ($qualityMainGroups as $mainCriteriaId => $subCriterias) {
                $mainCriteria = $subCriterias->first()->mainCriteria;

                if ($mainCriteria) {
                    $arrScoreEvaKey = $list->id . '_' . $mainCriteriaId;
                    $mainCalculatedScore = $arrScoreEva[$arrScoreEvaKey] ?? 0;

                    $mainCriteriaData = [
                        'id' => $mainCriteria->id,
                        'name' => $mainCriteria->name,
                        'tooltips' => $mainCriteria->tooltips,
                        'ratio' => $mainCriteria->ratio,
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
                        $evidenceLinks = $evidenceMap[$list->id] ?? [];
                        
                        $hasScore = $qualityScore && $qualityScore->score !== null && $qualityScore->score !== '';
                        $userSelected = $hasScore || ($qualityScore && $qualityScore->score !== null);

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
                            'user_selected' => $userSelected,
                            'score' => $qualityScore?->score ?? '',
                            'calculated_score' => $calculatedScore,
                            'evidence' => $evidenceLinks,
                        ];
                    }

                    $qualityItems[] = $mainCriteriaData;
                }
            }
        }

        return $qualityItems;
    }
}