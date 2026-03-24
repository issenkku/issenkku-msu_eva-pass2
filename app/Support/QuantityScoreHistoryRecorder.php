<?php

namespace App\Support;

use App\Models\QuantityScoreHistory;
use Illuminate\Support\Collection;

class QuantityScoreHistoryRecorder
{
    public static function record(
        int $reportId,
        Collection $oldScores,
        array $newQuantityScores,
        ?int $modifierUserId,
        ?string $modifierRole
    ): void {
        $oldBySubCriteria = $oldScores->keyBy('quantity_sub_criteria_id');

        foreach ($newQuantityScores as $item) {
            $subCriteriaId = (int) ($item['subCriteriaId'] ?? 0);
            if ($subCriteriaId <= 0) {
                continue;
            }

            $oldScore = $oldBySubCriteria->get($subCriteriaId);
            if (! $oldScore) {
                continue;
            }

            $oldScoreC = self::normalizeNumber($oldScore->score_C);
            $newScoreC = self::normalizeNumber($item['scoreC'] ?? null);
            $oldDescription = self::normalizeText($oldScore->description);
            $newDescription = self::normalizeText($item['description'] ?? null);

            if ($oldScoreC === $newScoreC && $oldDescription === $newDescription) {
                continue;
            }

            QuantityScoreHistory::create([
                'report_id' => $reportId,
                'quantity_sub_criteria_id' => $subCriteriaId,
                'previous_score_c' => $oldScoreC,
                'new_score_c' => $newScoreC,
                'previous_description' => $oldDescription,
                'new_description' => $newDescription,
                'modifier_user_id' => $modifierUserId,
                'modifier_role' => $modifierRole,
            ]);
        }
    }

    private static function normalizeNumber($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    private static function normalizeText($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
