<?php

namespace App\Support;

final class SupportAchievementScore
{
    public const TARGET_LEVEL_COUNT = 5;

    public static function calculate(float $weightedTotal): float
    {
        return round($weightedTotal / self::TARGET_LEVEL_COUNT, 2);
    }
}
