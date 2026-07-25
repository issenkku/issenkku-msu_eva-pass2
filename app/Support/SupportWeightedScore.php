<?php

namespace App\Support;

final class SupportWeightedScore
{
    public static function calculate(float $weight, float $achievedScore): float
    {
        return round(($weight * $achievedScore) / 100, 2);
    }
}
