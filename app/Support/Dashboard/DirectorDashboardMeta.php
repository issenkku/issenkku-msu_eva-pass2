<?php

namespace App\Support\Dashboard;

final class DirectorDashboardMeta
{
    public static function statusGroups(): array
    {
        return [
            'director_waiting' => ['Director_assigned'],
            'in_progress' => ['Director_draft'],
            'forwarded' => ['Manager_assign', 'Manager_draft'],
            'completed' => ['Completed'],
            'รอการกรอกข้อมูล' => ['Assigned', 'Draft', 'Pending', 'Evaluator_draft'],
            'ยังไม่ประเมิน' => ['Director_assigned'],
            'กำลังดำเนินการ' => ['Director_draft'],
            'รอผลการประเมิน' => ['Manager_assign', 'Manager_draft'],
            'ประเมินเสร็จสิ้น' => ['Completed'],
        ];
    }

    public static function filterStatusOptions(): array
    {
        return [
            'director_waiting' => 'รอกรรมการพิจารณา',
            'in_progress' => 'กำลังพิจารณา',
            'forwarded' => 'ส่งต่อผู้บริหารแล้ว',
            'completed' => 'เสร็จสิ้น',
        ];
    }

    public static function statusMeta(?string $status): array
    {
        return match ($status) {
            'Assigned', 'Draft', 'Pending', 'Evaluator_draft' => ['label' => 'อยู่ในขั้นตอนก่อนถึงกรรมการ', 'progress' => 50],
            'Director_assigned' => ['label' => 'รอกรรมการพิจารณา', 'progress' => 75],
            'Director_draft' => ['label' => 'กรรมการกำลังพิจารณา', 'progress' => 85],
            'Manager_assign', 'Manager_draft' => ['label' => 'ส่งต่อผู้บริหารแล้ว', 'progress' => 95],
            'Completed' => ['label' => 'รับรองเสร็จสิ้น', 'progress' => 100],
            default => ['label' => $status ?? '-', 'progress' => 0],
        };
    }

    public static function followUpPriority(?string $status): int
    {
        return match ($status) {
            'Director_assigned' => 1,
            'Director_draft' => 2,
            'Assigned', 'Draft', 'Pending', 'Evaluator_draft' => 3,
            default => 4,
        };
    }
}
