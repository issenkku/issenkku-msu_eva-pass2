<?php

namespace App\Services;

use App\Support\ReportScoreSummary;
use Illuminate\Support\Facades\DB;

class ScoreService
{
    public static function calculateAverageScore($reports)
    {
        $reports = collect($reports);

        if ($reports->isEmpty()) {
            return 0;
        }

        $completedReports = $reports->filter(function ($report) {
            return ($report->status ?? $report->report_status ?? null) === 'Completed';
        })->values();

        if ($completedReports->isEmpty()) {
            return 0;
        }

        $reportIds = $completedReports
            ->map(fn ($report) => $report->id ?? $report->report_id)
            ->filter()
            ->unique()
            ->values();

        $quantityScores = self::calculateQuantityScoresRawByReportIds($reportIds);
        $qualityScores = self::calculateQualityScoresRawByReportIds($reportIds);

        $totalScore = 0;
        $reportCount = 0;

        foreach ($completedReports as $report) {
            $reportId = $report->id ?? $report->report_id;

            $quantityScore = (float) ($quantityScores[$reportId] ?? 0);
            $qualityScore = (float) ($qualityScores[$reportId] ?? 0);
            $scores = ReportScoreSummary::fromTotals(
                $quantityScore,
                $qualityScore,
                (float) ($report->support_score_total ?? 0),
            );

            $totalScore += $scores['total'];
            $reportCount++;
        }

        return round($totalScore / $reportCount, 2);
    }

    public static function calculateHighestScore($reports)
    {
        $reports = collect($reports);

        if ($reports->isEmpty()) {
            return 0;
        }

        $completedReports = $reports->filter(function ($report) {
            return ($report->status ?? $report->report_status ?? null) === 'Completed';
        })->values();

        if ($completedReports->isEmpty()) {
            return 0;
        }

        $reportIds = $completedReports
            ->map(fn ($report) => $report->id ?? $report->report_id)
            ->filter()
            ->unique()
            ->values();

        $quantityScores = self::calculateQuantityScoresRawByReportIds($reportIds);
        $qualityScores = self::calculateQualityScoresRawByReportIds($reportIds);

        $highestScore = 0;

        foreach ($completedReports as $report) {
            $reportId = $report->id ?? $report->report_id;

            $quantityScore = (float) ($quantityScores[$reportId] ?? 0);
            $qualityScore = (float) ($qualityScores[$reportId] ?? 0);
            $totalScore = ReportScoreSummary::fromTotals(
                $quantityScore,
                $qualityScore,
                (float) ($report->support_score_total ?? 0),
            )['total'];

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
                DB::raw('SUM('.self::cappedQualityScoreSql().') as total_score')
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

    public static function calculateQualityScoresRawByReportIds($reportIds)
    {
        $reportIds = collect($reportIds)
            ->flatten()
            ->filter()
            ->map(fn ($reportId) => (int) $reportId)
            ->unique()
            ->values();

        if ($reportIds->isEmpty()) {
            return collect();
        }

        $qualityScores = $reportIds->mapWithKeys(fn ($reportId) => [$reportId => 0.0]);

        $maxScores = DB::table('reports')
            ->join('report_datas', 'reports.report_data_id', '=', 'report_datas.id')
            ->join('evaluation_lists', 'report_datas.criteria_version_id', '=', 'evaluation_lists.criteria_version_id')
            ->join('quality_sub_criterias', 'evaluation_lists.id', '=', 'quality_sub_criterias.evaluation_list_id')
            ->whereIn('reports.id', $reportIds)
            ->select('reports.id as report_id', 'evaluation_lists.id as evaluation_list_id', 'evaluation_lists.sum_score')
            ->distinct()
            ->get()
            ->groupBy('report_id')
            ->map(fn ($lists) => (float) $lists->sum('sum_score'));

        $listScores = DB::table('quality_scores')
            ->join('quality_sub_criterias', 'quality_scores.quality_sub_criteria_id', '=', 'quality_sub_criterias.id')
            ->join('evaluation_lists', 'quality_sub_criterias.evaluation_list_id', '=', 'evaluation_lists.id')
            ->select(
                'quality_scores.report_id',
                'evaluation_lists.id as evaluation_list_id',
                'evaluation_lists.sum_score',
                DB::raw('SUM('.self::cappedQualityScoreSql().') as total_score')
            )
            ->whereIn('quality_scores.report_id', $reportIds)
            ->groupBy('quality_scores.report_id', 'evaluation_lists.id', 'evaluation_lists.sum_score')
            ->get()
            ->groupBy('report_id');

        foreach ($listScores as $reportId => $scores) {
            $qualityScore = 0;

            foreach ($scores as $row) {
                $listMax = (float) $row->sum_score;
                $listTotal = (float) $row->total_score;

                if ($listMax > 0 && $listTotal > $listMax) {
                    $listTotal = $listMax;
                }

                $qualityScore += $listTotal;
            }

            $maxQualityScore = (float) ($maxScores[$reportId] ?? 0);

            if ($maxQualityScore > 0 && $qualityScore > $maxQualityScore) {
                $qualityScore = $maxQualityScore;
            }

            $qualityScores[(int) $reportId] = round($qualityScore, 2);
        }

        return $qualityScores;
    }

    public static function calculateQuantityScoresRawByReportIds($reportIds)
    {
        $reportIds = collect($reportIds)
            ->flatten()
            ->filter()
            ->map(fn ($reportId) => (int) $reportId)
            ->unique()
            ->values();

        if ($reportIds->isEmpty()) {
            return collect();
        }

        $quantityScores = $reportIds->mapWithKeys(fn ($reportId) => [$reportId => 0.0]);

        $normalizedQuantityScoreSql = self::normalizedQuantityScoreSql();

        $listScores = DB::table('quantity_scores')
            ->join(
                'quantity_sub_criterias',
                'quantity_scores.quantity_sub_criteria_id',
                '=',
                'quantity_sub_criterias.id'
            )
            ->join(
                'evaluation_lists',
                'quantity_sub_criterias.evaluation_list_id',
                '=',
                'evaluation_lists.id'
            )
            ->whereIn('quantity_scores.report_id', $reportIds)
            ->where('evaluation_lists.quantity_enabled', true)
            ->selectRaw('
                quantity_scores.report_id,
                evaluation_lists.id as evaluation_list_id,
                evaluation_lists.sum_score,
                COALESCE(SUM('.$normalizedQuantityScoreSql.'), 0) as total_score
            ')
            ->groupBy(
                'quantity_scores.report_id',
                'evaluation_lists.id',
                'evaluation_lists.sum_score'
            )
            ->get()
            ->groupBy('report_id');

        foreach ($listScores as $reportId => $scores) {
            $quantityTotal = 0.0;

            foreach ($scores as $score) {
                $listTotal = (float) $score->total_score;
                $listMax = (float) $score->sum_score;

                if ($listMax > 0 && $listTotal > $listMax) {
                    $listTotal = $listMax;
                }

                $quantityTotal += $listTotal;
            }

            $quantityScores[(int) $reportId] = round($quantityTotal, 2);
        }

        return $quantityScores;
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

    private static function normalizedQuantityScoreSql(): string
    {
        return 'CASE
            WHEN quantity_scores.score_D IS NULL OR quantity_scores.score_D < 0 THEN 0
            ELSE quantity_scores.score_D
        END';
    }

    private static function cappedQualityScoreSql(): string
    {
        return 'CASE
            WHEN quality_scores.score IS NULL OR quality_scores.score < 0 THEN 0
            WHEN quality_sub_criterias.num_score IS NOT NULL
                AND quality_scores.score > quality_sub_criterias.num_score
                THEN quality_sub_criterias.num_score
            ELSE quality_scores.score
        END';
    }
}
