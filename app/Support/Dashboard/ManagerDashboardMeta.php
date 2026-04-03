<?php

namespace App\Support\Dashboard;

final class ManagerDashboardMeta
{
    public static function statusGroups(): array
    {
        return [
            'manager_waiting' => ['Manager_assign'],
            'in_progress' => ['Manager_draft'],
            'completed' => ['Completed'],
            'รอการกรอกข้อมูล' => ['Assigned', 'Draft', 'Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft'],
            'ยังไม่ประเมิน' => ['Manager_assign'],
            'กำลังดำเนินการ' => ['Manager_draft'],
            'ประเมินเสร็จสิ้น' => ['Completed'],
        ];
    }

    public static function filterStatusOptions(): array
    {
        return [
            'manager_waiting' => 'รอผู้บริหารรับรอง',
            'in_progress' => 'กำลังรับรอง',
            'completed' => 'รับรองเสร็จสิ้น',
        ];
    }

    public static function statusMeta(?string $status): array
    {
        return match ($status) {
            'Assigned', 'Draft', 'Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft' => ['label' => 'อยู่ในขั้นตอนก่อนถึงผู้บริหาร', 'progress' => 60],
            'Manager_assign' => ['label' => 'รอผู้บริหารรับรอง', 'progress' => 85],
            'Manager_draft' => ['label' => 'ผู้บริหารกำลังรับรอง', 'progress' => 95],
            'Completed' => ['label' => 'รับรองเสร็จสิ้น', 'progress' => 100],
            default => ['label' => $status ?? '-', 'progress' => 0],
        };
    }

    public static function followUpPriority(?string $status): int
    {
        return match ($status) {
            'Manager_assign' => 1,
            'Manager_draft' => 2,
            default => 3,
        };
    }
}
