<?php

namespace App\Services;

use App\Models\QuantityScore;
use Illuminate\Support\Facades\DB;

class GraphDataService
{
    public static function scatterData($reports)
    {
        $scatterData = [];

        if ($reports->isEmpty()) {
            return $scatterData;
        }

        $i = 1;
        foreach ($reports as $report) {
            $reportId = $report->id ?? $report->report_id;

            if (($report->status ?? $report->report_status ?? null) !== 'Completed') {
                continue;
            }

            // ✅ Quantity Score
            $quantityScore = QuantityScore::where('report_id', $reportId)->sum('score_D') ?? 0;

            // ✅ Quality Score (aggregated in SQL)
            $qualityData = DB::table('quality_scores')
                ->join('quality_sub_criterias', 'quality_scores.quality_sub_criteria_id', '=', 'quality_sub_criterias.id')
                ->join('quality_main_criterias', 'quality_sub_criterias.quality_main_criteria_id', '=', 'quality_main_criterias.id')
                ->join('evaluation_lists', 'quality_sub_criterias.evaluation_list_id', '=', 'evaluation_lists.id')
                ->where('quality_scores.report_id', $reportId)
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

            // ✅ Calculate quality score
            $arrScoreEva = [];
            foreach ($qualityData as $row) {
                $maxSum = (float) $row->total_max_score;
                $accSum = (float) $row->total_score;
                $ratio = (float) $row->ratio;
                $sumScoreEva = (float) $row->sum_score;

                if ($maxSum > 0) {
                    // adjust this depending on how "ratio" is stored:
                    // if ratio=25 (percent) → divide by 100
                    // if ratio=0.25 (fraction) → remove /100
                    $scoreRatioMain = $ratio * ($accSum / $maxSum);
                    $arrScoreEva[] = ($scoreRatioMain / 100) * $sumScoreEva;
                }
            }

            $qualityScore = array_sum($arrScoreEva);

            // ✅ Total
            $totalScore = $quantityScore + $qualityScore;

            $scatterData[] = [
                'x'        => $i++,
                'y'        => round($totalScore, 2),
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

        return ['มอบหมาย', 'เริ่มกรอกข้อมูล', 'อยู่ระหว่างการรับรอง', 'เสร็จสิ้น'];
    }

    public static function getStatusColors()
    {
        return [
            'rgba(251, 36, 36, 0.8)',   // มอบหมาย
            'rgba(59, 130, 246, 0.8)',  // เริ่มกรอกข้อมูล
            'rgba(251, 191, 36, 0.8)',  // อยู่ระหว่างการรับรอง
            'rgba(16, 185, 129, 0.8)'   // เสร็จสิ้น
        ];
    }
}
