<?php

namespace App\Support;

final class ReportScoreSummary
{
    public const SUPPORT_TOTAL_CAP = 100.0;

    /**
     * @return array{quantity: float, quality: float, support_raw: float, support: float, support_achievement: float, total: float}
     */
    public static function fromTotals(float $quantity, float $quality, float $supportRaw): array
    {
        $support = min($supportRaw, self::SUPPORT_TOTAL_CAP);

        return [
            'quantity' => round($quantity, 2),
            'quality' => round($quality, 2),
            'support_raw' => round($supportRaw, 2),
            'support' => round($support, 2),
            'support_achievement' => SupportAchievementScore::calculate($supportRaw),
            'total' => round($quantity + $quality + $support, 2),
        ];
    }
}
