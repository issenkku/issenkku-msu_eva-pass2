<?php

namespace App\Support;

use App\Models\AssignmentData;

class AssignmentFlow
{
    public const STAGES = ['evaluator', 'director', 'manager'];

    public static function selectedActorsFromRequest(array $data): array
    {
        return [
            'evaluator' => $data['evaluator_id'] ?? null,
            'director' => $data['director_id'] ?? null,
            'manager' => $data['manager_id'] ?? null,
        ];
    }

    public static function normalize(?array $stageOrder, array $selectedActors): array
    {
        return collect(self::STAGES)
            ->filter(fn (string $stage) => ! empty($selectedActors[$stage]))
            ->mapWithKeys(function (string $stage) use ($stageOrder) {
                $order = $stageOrder[$stage] ?? null;

                return [$stage => is_numeric($order) ? (int) $order : PHP_INT_MAX];
            })
            ->sort()
            ->keys()
            ->values()
            ->all();
    }

    public static function stagesFor(?AssignmentData $assignmentData): array
    {
        if (! $assignmentData) {
            return [];
        }

        $configured = collect($assignmentData->evaluation_flow ?? [])
            ->filter(fn ($stage) => in_array($stage, self::STAGES, true))
            ->values();

        if ($configured->isNotEmpty()) {
            return $configured->all();
        }

        return self::STAGES;
    }

    public static function statusForStage(?string $stage): ?string
    {
        return match ($stage) {
            'evaluator' => 'Pending',
            'director' => 'Director_assigned',
            'manager' => 'Manager_assign',
            default => null,
        };
    }

    public static function nextStatusAfter(string $currentStage, ?AssignmentData $assignmentData): string
    {
        $stages = self::stagesFor($assignmentData);
        $currentIndex = array_search($currentStage, $stages, true);

        if ($currentIndex === false) {
            return 'Completed';
        }

        $nextStage = $stages[$currentIndex + 1] ?? null;

        return self::statusForStage($nextStage) ?? 'Completed';
    }

    public static function stageColumn(string $stage): string
    {
        return "{$stage}_id";
    }
}
