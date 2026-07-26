<?php

namespace App\Support;

final class ScoreHistoryVisibility
{
    public static function shouldDisplay(
        ?int $modifierUserId,
        ?string $modifierRole,
        ?int $evaluateeId
    ): bool {
        if ($modifierUserId === null && $modifierRole === null) {
            return false;
        }

        return $evaluateeId === null || $modifierUserId !== $evaluateeId;
    }
}
