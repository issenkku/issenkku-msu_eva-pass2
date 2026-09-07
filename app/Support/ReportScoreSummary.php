<?php

namespace App\Support;

final class ReportScoreSummary
{
    public const SUPPORT_TOTAL_CAP = 5.0;

    /**
     * @return array{quantity: float, quality: float, support_raw: float, support: float, support_achievement: float, total: float}
     */
    public static function fromTotals(float $quantity, float $quality, float $supportRaw): array
    {
        $support = min(max($supportRaw, 0.0), self::SUPPORT_TOTAL_CAP);

        return [
            'quantity' => round($quantity, 2),
            'quality' => round($quality, 2),
            'support_raw' => round($support, 2),
            'support' => round($support, 2),
            'support_achievement' => SupportAchievementScore::calculate($support),
            'total' => round($quantity + $quality + $support, 2),
        ];
    }
}
