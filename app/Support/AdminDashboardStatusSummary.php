<?php

namespace App\Support;

class AdminDashboardStatusSummary
{
    public function statusCounts($evaluations): array
    {
        return [
            'ทั้งหมด' => $evaluations->count(),
            'มอบหมาย' => $this->notStartedStatusesCount($evaluations),
            'เริ่มกรอกข้อมูล' => $this->draftStatusesCount($evaluations),
            'กำลังดำเนินการ' => $this->inReviewStatusesCount($evaluations),
            'ประเมินเสร็จสิ้น' => $this->completedStatusesCount($evaluations),
        ];
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
        return $this->countByStatus($evaluations, $this->notStartedStatuses());
    }

    public function draftStatusesCount($evaluations): int
    {
        return $this->countByStatus($evaluations, $this->draftStatuses());
    }

    public function inReviewStatusesCount($evaluations): int
    {
        return $this->countByStatus($evaluations, $this->inReviewStatuses());
    }

    public function completedStatusesCount($evaluations): int
    {
        return $this->countByStatus($evaluations, $this->completedStatuses());
    }

    private function countByStatus($evaluations, array $statuses): int
    {
        return $evaluations->filter(function ($assignment) use ($statuses) {
            $reportStatus = optional($assignment->report)->status ?? 'Assigned';

            return in_array($reportStatus, $statuses, true);
        })->count();
    }

    private function notStartedStatuses(): array
    {
        return ['Assigned', 'Manager_assign'];
    }

    private function draftStatuses(): array
    {
        return ['Draft'];
    }

    private function inReviewStatuses(): array
    {
        return ['Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft', 'Manager_draft', 'Manager_assign'];
    }

    private function completedStatuses(): array
    {
        return ['Completed'];
    }
}
