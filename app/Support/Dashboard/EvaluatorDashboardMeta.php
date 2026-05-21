<?php

// ไฟล์คลาสของระบบ: app/Support/Dashboard/EvaluatorDashboardMeta.php

namespace App\Support\Dashboard;

final class EvaluatorDashboardMeta
{
    public static function summarizeOverviewStatuses($evaluations): array
    {
        $counts = [
            'รอการกรอกข้อมูล' => 0,
            'รอคุณประเมิน' => 0,
            'กำลังประเมิน' => 0,
            'ส่งต่อแล้ว' => 0,
            'เสร็จสิ้น' => 0,
        ];

        $evaluations
            ->filter(fn ($assignment) => $assignment->evaluateeUser)
            ->groupBy('evaluatee_id')
            ->each(function ($assignments) use (&$counts) {
                $statuses = $assignments
                    ->map(fn ($assignment) => optional($assignment->report)->status ?? 'Assigned')
                    ->filter()
                    ->values();

                if ($statuses->isEmpty()) {
                    $counts['รอการกรอกข้อมูล']++;
                    return;
                }

                $allCompleted = $statuses->every(fn ($status) => $status === 'Completed');

                if ($allCompleted) {
                    $counts['เสร็จสิ้น']++;
                } elseif ($statuses->contains(fn ($status) => in_array($status, ['Director_assigned', 'Director_draft', 'Manager_assign', 'Manager_draft'], true))) {
                    $counts['ส่งต่อแล้ว']++;
                } elseif ($statuses->contains('Evaluator_draft')) {
                    $counts['กำลังประเมิน']++;
                } elseif ($statuses->contains('Pending')) {
                    $counts['รอคุณประเมิน']++;
                } else {
                    $counts['รอการกรอกข้อมูล']++;
                }
            });

        return $counts;
    }

    public static function statusGroups(): array
    {
        return [
            'waiting' => ['Assigned', 'Draft', 'Pending'],
            'in_progress' => ['Evaluator_draft'],
            'forwarded' => ['Director_assigned', 'Director_draft', 'Manager_assign', 'Manager_draft'],
            'completed' => ['Completed'],
            'รอการกรอกข้อมูล' => ['Assigned', 'Draft'],
            'ยังไม่ประเมิน' => ['Pending'],
            'กำลังดำเนินการ' => ['Evaluator_draft'],
            'รอผลการประเมิน' => ['Director_assigned', 'Director_draft', 'Manager_assign', 'Manager_draft'],
            'ประเมินเสร็จสิ้น' => ['Completed'],
        ];
    }

    public static function filterStatusOptions(): array
    {
        return [
            'waiting' => 'รอคุณประเมิน',
            'in_progress' => 'กำลังประเมิน',
            'forwarded' => 'ส่งต่อแล้ว',
            'completed' => 'เสร็จสิ้น',
        ];
    }

    public static function statusMeta(?string $status): array
    {
        return match ($status) {
            'Assigned' => ['label' => 'รอผู้รับการประเมินเริ่มกรอก', 'progress' => 0],
            'Draft' => ['label' => 'ผู้รับการประเมินกำลังกรอก', 'progress' => 20],
            'Pending' => ['label' => 'รอคุณประเมิน', 'progress' => 40],
            'Evaluator_draft' => ['label' => 'คุณกำลังประเมิน', 'progress' => 60],
            'Director_assigned', 'Director_draft' => ['label' => 'ส่งต่อกรรมการแล้ว', 'progress' => 80],
            'Manager_assign', 'Manager_draft' => ['label' => 'ส่งต่อผู้บริหารแล้ว', 'progress' => 90],
            'Completed' => ['label' => 'ประเมินเสร็จสิ้น', 'progress' => 100],
            default => ['label' => $status ?? '-', 'progress' => 0],
        };
    }

    public static function followUpPriority(?string $status): int
    {
        return match ($status) {
            'Pending' => 1,
            'Evaluator_draft' => 2,
            'Assigned', 'Draft' => 3,
            'Director_assigned', 'Director_draft', 'Manager_assign', 'Manager_draft' => 4,
            default => 5,
        };
    }
}
