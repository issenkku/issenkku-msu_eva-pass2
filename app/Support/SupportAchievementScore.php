<?php

namespace App\Support;

final class SupportAchievementScore
{
    public const TARGET_LEVEL_COUNT = 5;

    public static function calculate(float $weightedTotal): float
    {
        $cappedTotal = min(max($weightedTotal, 0.0), self::TARGET_LEVEL_COUNT);

        return round($cappedTotal / self::TARGET_LEVEL_COUNT, 2);
    }
}
