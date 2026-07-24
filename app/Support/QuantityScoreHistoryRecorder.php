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
        ?string $modifierRole,
        bool $requireReason
    ): void {
        $oldBySubCriteria = $oldScores->keyBy('quantity_sub_criteria_id');
        $newBySubCriteria = collect($newQuantityScores)
            ->filter(fn (array $item) => (int) ($item['subCriteriaId'] ?? 0) > 0)
            ->keyBy(fn (array $item) => (int) $item['subCriteriaId']);

        $subCriteriaIds = $oldBySubCriteria->keys()
            ->merge($newBySubCriteria->keys())
            ->unique();

        foreach ($subCriteriaIds as $subCriteriaId) {
            $oldScore = $oldBySubCriteria->get($subCriteriaId);
            $newScore = $newBySubCriteria->get($subCriteriaId);
            $scoreChanged = ScoreChangePolicy::numbersDiffer(
                $oldScore?->score_C,
                $newScore['scoreC'] ?? null
            );
            $descriptionChanged = ScoreChangePolicy::textsDiffer(
                $oldScore?->description,
                $newScore['description'] ?? null
            );
            $changed = $scoreChanged || $descriptionChanged;
            $inputKey = $newScore['inputKey'] ?? $subCriteriaId;
            $reason = ScoreChangePolicy::validatedReason(
                $changed,
                $newScore['modificationReason'] ?? null,
                $requireReason,
                "quantity_list.{$inputKey}.modification_reason"
            );

            if (! $changed) {
                continue;
            }

            QuantityScoreHistory::create([
                'report_id' => $reportId,
                'quantity_sub_criteria_id' => $subCriteriaId,
                'previous_score_c' => $oldScore?->score_C,
                'new_score_c' => $newScore['scoreC'] ?? null,
                'previous_description' => $oldScore?->description,
                'new_description' => $newScore['description'] ?? null,
                'reason' => $reason,
                'modifier_user_id' => $modifierUserId,
                'modifier_role' => $modifierRole,
            ]);
        }
    }
}
