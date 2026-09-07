<?php

namespace App\Exports;

use App\Models\QuantityScore;
use Illuminate\Support\Collection;

/** Quantity scores for exports only; assessment and dashboard scores retain their own policy. */
final class ReportQuantityScores
{
    public static function cap(mixed $score, mixed $maximum): float
    {
        $value = max(0.0, (float) $score);

        return round($maximum === null ? $value : min($value, max(0.0, (float) $maximum)), 2);
    }

    /** @return Collection<int, array{details: Collection<int, float>, total: float}> */
    public static function forReportIds(iterable $reportIds): Collection
    {
        return QuantityScore::query()
            ->with('quantitySubCriteria.evaluationList')
            ->whereIn('report_id', collect($reportIds)->all())
            ->whereHas('quantitySubCriteria', fn ($query) => $query->active())
            ->get()
            ->groupBy('report_id')
            ->map(function (Collection $scores): array {
                $criteria = $scores->groupBy('quantity_sub_criteria_id');
                $details = $criteria->map(fn (Collection $items) => self::cap(
                    $items->sum(fn ($item) => max(0.0, (float) $item->score_D)),
                    $items->first()->quantitySubCriteria->score_a,
                ));
                $total = $criteria->groupBy(fn (Collection $items) => $items->first()->quantitySubCriteria->evaluation_list_id)
                    ->sum(function (Collection $groups) use ($details): float {
                        $list = $groups->first()->first()->quantitySubCriteria->evaluationList;
                        $sum = $groups->sum(fn (Collection $items) => $details[$items->first()->quantity_sub_criteria_id]);

                        return (float) $list->sum_score > 0 ? min($sum, (float) $list->sum_score) : $sum;
                    });

                return ['details' => $details, 'total' => round($total, 2)];
            });
    }
}
