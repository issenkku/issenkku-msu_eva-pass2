<?php

namespace App\Support;

use App\Models\Reports;
use App\Models\SupportActivityEntry;
use App\Models\SupportScore;

final class SupportScoreTotal
{
    public static function forReport(Reports $report): float
    {
        $legacyTotal = SupportScore::query()
            ->where('report_id', $report->id)
            ->whereHas(
                'supportCriteria',
                fn ($query) => $query->where('allow_evaluatee_weight', false)
            )
            ->sum('weighted_score');

        $entryTotal = SupportActivityEntry::query()
            ->where('report_id', $report->id)
            ->whereHas(
                'supportCriteria',
                fn ($query) => $query->where('allow_evaluatee_weight', true)
            )
            ->sum('weighted_score');

        $total = round((float) $legacyTotal + (float) $entryTotal, 2);

        return min(max($total, 0.0), SupportAchievementScore::TARGET_LEVEL_COUNT);
    }
}
