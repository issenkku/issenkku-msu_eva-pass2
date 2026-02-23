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
            $qualityScore = self::calculateQualityScoreRaw($reportId);

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
            $qualityScore = self::calculateQualityScoreRaw($reportId);

            // --- Total Score for this report ---
            $totalScore = $quantityScore + $qualityScore;

            // Update highest if larger
            if ($totalScore > $highestScore) {
                $highestScore = $totalScore;
            }
        }

        return round($highestScore, 2);
    }

    public static function calculateQualityScoreRaw($reportId)
    {
        $reportId = is_array($reportId) ? $reportId[0] : (int) $reportId;

        $maxQualityScore = self::getMaxQualityScore($reportId);

        $listScores = DB::table('quality_scores')
            ->join('quality_sub_criterias', 'quality_scores.quality_sub_criteria_id', '=', 'quality_sub_criterias.id')
            ->join('evaluation_lists', 'quality_sub_criterias.evaluation_list_id', '=', 'evaluation_lists.id')
            ->select(
                'evaluation_lists.id as evaluation_list_id',
                'evaluation_lists.sum_score',
                DB::raw('SUM(quality_scores.score) as total_score')
            )
            ->where('quality_scores.report_id', $reportId)
            ->groupBy('evaluation_lists.id', 'evaluation_lists.sum_score')
            ->get();

        $qualityScore = 0;
        foreach ($listScores as $row) {
            $listMax = (float) $row->sum_score;
            $listTotal = (float) $row->total_score;
            if ($listMax > 0 && $listTotal > $listMax) {
                $listTotal = $listMax;
            }
            $qualityScore += $listTotal;
        }

        if ($maxQualityScore > 0 && $qualityScore > $maxQualityScore) {
            $qualityScore = $maxQualityScore;
        }

        return round($qualityScore, 2);
    }

    private static function getMaxQualityScore($reportId)
    {
        $criteriaVersionId = DB::table('reports')
            ->join('report_datas', 'reports.report_data_id', '=', 'report_datas.id')
            ->where('reports.id', $reportId)
            ->value('report_datas.criteria_version_id');

        if (! $criteriaVersionId) {
            return 0;
        }

        $lists = DB::table('evaluation_lists')
            ->join('quality_sub_criterias', 'evaluation_lists.id', '=', 'quality_sub_criterias.evaluation_list_id')
            ->where('evaluation_lists.criteria_version_id', $criteriaVersionId)
            ->select('evaluation_lists.id', 'evaluation_lists.sum_score')
            ->distinct()
            ->get();

        return (float) $lists->sum('sum_score');
    }
}
