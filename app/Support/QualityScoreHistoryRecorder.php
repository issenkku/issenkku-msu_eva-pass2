<?php

namespace App\Support;

use App\Models\QualityScoreHistory;
use Illuminate\Support\Collection;

final class QualityScoreHistoryRecorder
{
    public static function record(
        int $reportId,
        Collection $oldScores,
        array $newQualityScores,
        ?int $modifierUserId,
        ?string $modifierRole,
        bool $requireReason
    ): void {
        $oldBySubCriteria = $oldScores->keyBy('quality_sub_criteria_id');
        $newBySubCriteria = collect($newQualityScores)
            ->filter(fn (array $item) => (int) ($item['subCriteriaId'] ?? 0) > 0)
            ->keyBy(fn (array $item) => (int) $item['subCriteriaId']);

        $subCriteriaIds = $oldBySubCriteria->keys()
            ->merge($newBySubCriteria->keys())
            ->unique();

        foreach ($subCriteriaIds as $subCriteriaId) {
            $oldScore = $oldBySubCriteria->get($subCriteriaId);
            $newScore = $newBySubCriteria->get($subCriteriaId);
            $changed = ScoreChangePolicy::numbersDiffer(
                $oldScore?->score,
                $newScore['score'] ?? null
            );
            $inputKey = $newScore['inputKey'] ?? $subCriteriaId;
            $reason = ScoreChangePolicy::validatedReason(
                $changed,
                $newScore['modificationReason'] ?? null,
                $requireReason,
                "quality_list.{$inputKey}.modification_reason"
            );

            if (! $changed) {
                continue;
            }

            QualityScoreHistory::create([
                'report_id' => $reportId,
                'quality_sub_criteria_id' => $subCriteriaId,
                'previous_score' => $oldScore?->score,
                'new_score' => $newScore['score'] ?? null,
                'reason' => $reason,
                'modifier_user_id' => $modifierUserId,
                'modifier_role' => $modifierRole,
            ]);
        }
    }
}
