<?php

namespace App\Services;

class GraphDataService
{
    public static function scatterData($reports)
    {
        $reports = collect($reports);
        $scatterData = [];

        if ($reports->isEmpty()) {
            return $scatterData;
        }

        $completedReports = $reports->filter(function ($report) {
            return ($report->status ?? $report->report_status ?? null) === 'Completed';
        })->values();

        if ($completedReports->isEmpty()) {
            return $scatterData;
        }

        $reportIds = $completedReports
            ->map(fn ($report) => $report->id ?? $report->report_id)
            ->filter()
            ->unique()
            ->values();

        $quantityScores = ScoreService::calculateQuantityScoresRawByReportIds($reportIds);
        $qualityScores = ScoreService::calculateQualityScoresRawByReportIds($reportIds);

        $i = 1;
        foreach ($completedReports as $report) {
            $reportId = $report->id ?? $report->report_id;

            $quantityScore = (float) ($quantityScores[$reportId] ?? 0);
            $qualityScore = (float) ($qualityScores[$reportId] ?? 0);
            $totalScore = $quantityScore + $qualityScore;

            $scatterData[] = [
                'x' => $i++,
                'y' => round($totalScore, 2),
                'quantity' => round($quantityScore, 2),
                'quality' => round($qualityScore, 2),
            ];
        }

        return $scatterData;
    }

    public static function statusCounts($reports)
    {
        $statusCounts = [
            'Assigned' => 0,
            'Draft' => 0,
            'Pending' => 0,
            'Completed' => 0,
        ];

        foreach ($reports as $report) {
            $status = $report->status ?? $report->report_status ?? null;

            if (in_array($status, [
                'Assigned',
            ])) {
                $statusCounts['Assigned']++;
            } elseif (in_array($status, [
                'Draft',
            ])) {
                $statusCounts['Draft']++;
            } elseif (in_array($status, [
                'Pending',
                'Evaluator_draft',
                'Director_assigned',
                'Director_draft',
                'Manager_assign',
                'Manager_draft',
            ])) {
                $statusCounts['Pending']++;
            } elseif (in_array($status, [
                'Completed',
            ])) {
                $statusCounts['Completed']++;
            }
        }

        return $statusCounts;
    }

    public static function getStatusLabels()
    {
        return ['มอบหมาย', 'เริ่มกรอกข้อมูล', 'อยู่ระหว่างการรับรอง', 'เสร็จสิ้น'];
    }

    public static function getStatusColors()
    {
        return [
            'rgba(251, 36, 36, 0.8)',   // มอบหมาย
            'rgba(59, 130, 246, 0.8)',  // เริ่มกรอกข้อมูล
            'rgba(251, 191, 36, 0.8)',  // อยู่ระหว่างการรับรอง
            'rgba(16, 185, 129, 0.8)',   // เสร็จสิ้น
        ];
    }
}
