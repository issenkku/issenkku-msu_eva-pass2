<?php

namespace App\Services;

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

            $totalScore += ($quantityScore + $qualityScore);
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
            $totalScore = $quantityScore + $qualityScore;

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
                DB::raw('SUM(quality_scores.score) as total_score')
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

        return DB::table('quantity_scores')
            ->whereIn('report_id', $reportIds)
            ->selectRaw('report_id, COALESCE(SUM(score_D), 0) as total_score')
            ->groupBy('report_id')
            ->pluck('total_score', 'report_id');
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
