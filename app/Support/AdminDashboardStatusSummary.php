<?php

namespace App\Support;

class AdminDashboardStatusSummary
{
    public const ALL = 'all';

    public const ASSIGNED = 'มอบหมาย';

    public const STARTED = 'เริ่มกรอกข้อมูล';

    public const IN_PROGRESS = 'กำลังดำเนินการ';

    public const COMPLETED = 'ประเมินเสร็จสิ้น';

    private const STATUS_GROUPS = [
        'Assigned' => self::ASSIGNED,
        'Draft' => self::STARTED,
        'Pending' => self::IN_PROGRESS,
        'Evaluator_draft' => self::IN_PROGRESS,
        'Director_assigned' => self::IN_PROGRESS,
        'Director_draft' => self::IN_PROGRESS,
        'Manager_assign' => self::IN_PROGRESS,
        'Manager_draft' => self::IN_PROGRESS,
        'Completed' => self::COMPLETED,
    ];

    public function statusCounts($evaluations): array
    {
        $groupedCounts = $evaluations
            ->countBy(fn ($assignment) => $this->groupForStatus(
                optional($assignment->report)->status
            ));

        return [
            'ทั้งหมด' => $evaluations->count(),
            self::ASSIGNED => $groupedCounts->get(self::ASSIGNED, 0),
            self::STARTED => $groupedCounts->get(self::STARTED, 0),
            self::IN_PROGRESS => $groupedCounts->get(self::IN_PROGRESS, 0),
            self::COMPLETED => $groupedCounts->get(self::COMPLETED, 0),
        ];
    }

    public function groupForStatus(?string $status): string
    {
        return self::STATUS_GROUPS[$status ?? 'Assigned'] ?? self::ASSIGNED;
    }

    public function normalizeGroup(?string $group): string
    {
        return in_array($group, [
            self::ASSIGNED,
            self::STARTED,
            self::IN_PROGRESS,
            self::COMPLETED,
        ], true) ? $group : self::ALL;
    }

    public function filterByGroup($evaluations, ?string $group)
    {
        $normalizedGroup = $this->normalizeGroup($group);

        if ($normalizedGroup === self::ALL) {
            return $evaluations->values();
        }

        return $evaluations
            ->filter(fn ($assignment) => $this->groupForStatus(
                optional($assignment->report)->status
            ) === $normalizedGroup)
            ->values();
    }

    public function overviewStatusCounts($evaluations): array
    {
        $counts = [
            'มอบหมาย' => 0,
            'เริ่มกรอกข้อมูล' => 0,
            'กำลังดำเนินการ' => 0,
            'ประเมินเสร็จสิ้น' => 0,
        ];

        $evaluations
            ->filter(fn ($assignment) => $assignment->evaluateeUser)
            ->groupBy('evaluateeUser.id')
            ->each(function ($assignments) use (&$counts) {
                $statuses = $assignments
                    ->map(fn ($assignment) => optional($assignment->report)->status ?? 'Assigned')
                    ->filter()
                    ->values();

                if ($statuses->isEmpty()) {
                    $counts['มอบหมาย']++;

                    return;
                }

                $allCompleted = $statuses->every(fn ($status) => $status === 'Completed');
                $allAssigned = $statuses->every(fn ($status) => in_array($status, $this->notStartedStatuses(), true));
                $hasDraftOnly = $statuses->contains('Draft')
                    && $statuses->every(fn ($status) => in_array($status, ['Assigned', 'Manager_assign', 'Draft'], true));

                if ($allCompleted) {
                    $counts['ประเมินเสร็จสิ้น']++;
                } elseif ($allAssigned) {
                    $counts['มอบหมาย']++;
                } elseif ($hasDraftOnly) {
                    $counts['เริ่มกรอกข้อมูล']++;
                } else {
                    $counts['กำลังดำเนินการ']++;
                }
            });

        return $counts;
    }

    public function notStartedStatusesCount($evaluations): int
    {
        return $this->countByGroup($evaluations, self::ASSIGNED);
    }

    public function draftStatusesCount($evaluations): int
    {
        return $this->countByGroup($evaluations, self::STARTED);
    }

    public function inReviewStatusesCount($evaluations): int
    {
        return $this->countByGroup($evaluations, self::IN_PROGRESS);
    }

    public function completedStatusesCount($evaluations): int
    {
        return $this->countByGroup($evaluations, self::COMPLETED);
    }

    private function countByGroup($evaluations, string $group): int
    {
        return $evaluations
            ->filter(fn ($assignment) => $this->groupForStatus(
                optional($assignment->report)->status
            ) === $group)
            ->count();
    }
}
