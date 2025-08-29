<?php

namespace App\Services;

use App\Models\QuantityScore;
use Illuminate\Support\Facades\DB;

class ScoreService
{
    public static function calculateAverageScore($reports)
    {
        if ($reports->isEmpty()) {
            return 0;
        }

        $totalScore = 0;
        $reportCount = 0;

        foreach ($reports as $report) {
            $reportId = $report->id ?? $report->report_id;

            if (($report->status ?? $report->report_status ?? null) !== 'Completed') {
                continue;
            }

            $quantityScore = QuantityScore::where('report_id', $reportId)->sum('score_D') ?? 0;

            $qualityData = DB::table('quality_scores')
                ->join('quality_sub_criterias', 'quality_scores.quality_sub_criteria_id', '=', 'quality_sub_criterias.id')
                ->join('quality_main_criterias', 'quality_sub_criterias.quality_main_criteria_id', '=', 'quality_main_criterias.id')
                ->join('evaluation_lists', 'quality_sub_criterias.evaluation_list_id', '=', 'evaluation_lists.id')
                ->join('reports', 'quality_scores.report_id', '=', 'reports.id')
                ->select(
                    'quality_scores.quality_sub_criteria_id',
                    'quality_scores.score',
                    'quality_sub_criterias.evaluation_list_id',
                    'quality_sub_criterias.num_score',
                    'quality_main_criterias.id as quality_main_criteria_id',
                    'quality_main_criterias.ratio',
                    'evaluation_lists.sum_score',
                    'reports.id as report_id',
                )
                ->where('quality_scores.report_id', $reportId)
                ->groupBy(
                    'quality_sub_criterias.evaluation_list_id',
                    'quality_main_criterias.id',
                    'quality_scores.quality_sub_criteria_id',
                    'quality_scores.score',
                    'quality_sub_criterias.num_score',
                    'quality_main_criterias.ratio',
                    'reports.id',
                    'evaluation_lists.sum_score'
                )
                ->get();

            $groupedMainCriterias = [];
            foreach ($qualityData as $subCriteria) {
                $groupedMainCriterias[$subCriteria->evaluation_list_id][$subCriteria->quality_main_criteria_id][] = $subCriteria;
            }

            $arrScoreEva = [];
            foreach ($groupedMainCriterias as $evalListId => $mainCriterias) {
                foreach ($mainCriterias as $mainCriteriaId => $subCriterias) {
                    $sum_score_Eva = (float) $subCriterias[0]->sum_score;
                    $ratio = (float) $subCriterias[0]->ratio;
                    $SumMaxScoreSub = [];
                    $SumAccScoreSub = [];

                    foreach ($subCriterias as $subCriteria) {
                        $maxScorePerSub = round((float) $subCriteria->num_score, 2);
                        $score = round((float) $subCriteria->score, 2);
                        if ($maxScorePerSub > 0) {
                            $SumMaxScoreSub[] = $maxScorePerSub;
                            $SumAccScoreSub[] = $score;
                        }
                    }

                    $maxSum = array_sum($SumMaxScoreSub);
                    $accSum = array_sum($SumAccScoreSub);
                    $scoreRatioMain = 0;
                    if ($maxSum > 0) {
                        $scoreRatioMain = $ratio * ($accSum / $maxSum);
                    }
                    $arrScoreEva[] = ($scoreRatioMain / 100) * $sum_score_Eva;
                }
            }

            $qualityScore = array_sum($arrScoreEva);

            $totalScore += ($quantityScore + $qualityScore);
            $reportCount++;
        }

        if ($reportCount === 0) {
            return 0;
        }

        return round($totalScore / $reportCount, 2);
    }

    public static function calculateHighestScore($reports)
    {
        if ($reports->isEmpty()) {
            return 0;
        }

        $highestScore = 0;

        foreach ($reports as $report) {
            $reportId = $report->id ?? $report->report_id;

            // Only completed reports should count
            if (($report->status ?? $report->report_status ?? null) !== 'Completed') {
                continue;
            }

            // --- Quantity Score ---
            $quantityScore = QuantityScore::where('report_id', $reportId)->sum('score_D') ?? 0;

            // --- Quality Score ---
            $qualityData = DB::table('quality_scores')
                ->join('quality_sub_criterias', 'quality_scores.quality_sub_criteria_id', '=', 'quality_sub_criterias.id')
                ->join('quality_main_criterias', 'quality_sub_criterias.quality_main_criteria_id', '=', 'quality_main_criterias.id')
                ->join('evaluation_lists', 'quality_sub_criterias.evaluation_list_id', '=', 'evaluation_lists.id')
                ->join('reports', 'quality_scores.report_id', '=', 'reports.id')
                ->select(
                    'quality_scores.quality_sub_criteria_id',
                    'quality_scores.score',
                    'quality_sub_criterias.evaluation_list_id',
                    'quality_sub_criterias.num_score',
                    'quality_main_criterias.id as quality_main_criteria_id',
                    'quality_main_criterias.ratio',
                    'evaluation_lists.sum_score',
                    'reports.id as report_id',
                )
                ->where('quality_scores.report_id', $reportId)
                ->groupBy(
                    'quality_sub_criterias.evaluation_list_id',
                    'quality_main_criterias.id',
                    'quality_scores.quality_sub_criteria_id',
                    'quality_scores.score',
                    'quality_sub_criterias.num_score',
                    'quality_main_criterias.ratio',
                    'reports.id',
                    'evaluation_lists.sum_score'
                )
                ->get();

            $groupedMainCriterias = [];
            foreach ($qualityData as $subCriteria) {
                $groupedMainCriterias[$subCriteria->evaluation_list_id][$subCriteria->quality_main_criteria_id][] = $subCriteria;
            }

            $arrScoreEva = [];
            foreach ($groupedMainCriterias as $evalListId => $mainCriterias) {
                foreach ($mainCriterias as $mainCriteriaId => $subCriterias) {
                    $sum_score_Eva = (float) $subCriterias[0]->sum_score;
                    $ratio = (float) $subCriterias[0]->ratio;
                    $SumMaxScoreSub = [];
                    $SumAccScoreSub = [];

                    foreach ($subCriterias as $subCriteria) {
                        $maxScorePerSub = round((float) $subCriteria->num_score, 2);
                        $score = round((float) $subCriteria->score, 2);
                        if ($maxScorePerSub > 0) {
                            $SumMaxScoreSub[] = $maxScorePerSub;
                            $SumAccScoreSub[] = $score;
                        }
                    }

                    $maxSum = array_sum($SumMaxScoreSub);
                    $accSum = array_sum($SumAccScoreSub);
                    $scoreRatioMain = 0;
                    if ($maxSum > 0) {
                        $scoreRatioMain = $ratio * ($accSum / $maxSum);
                    }
                    $arrScoreEva[] = ($scoreRatioMain / 100) * $sum_score_Eva;
                }
            }

            $qualityScore = array_sum($arrScoreEva);

            // --- Total Score for this report ---
            $totalScore = $quantityScore + $qualityScore;

            // Update highest if larger
            if ($totalScore > $highestScore) {
                $highestScore = $totalScore;
            }
        }

        return round($highestScore, 2);
    }
}
