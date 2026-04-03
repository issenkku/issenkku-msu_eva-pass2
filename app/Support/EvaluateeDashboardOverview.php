<?php

namespace App\Support;

use Illuminate\Support\Collection;

class EvaluateeDashboardOverview
{
    public static function build(
        int $notStartedAssignments,
        int $inProgressAssignments,
        int $actionRequiredAssignments,
        int $inReviewAssignments,
        int $completedAssignments,
        int $totalAssignments,
        Collection $unfinishedAssignments,
        Collection $overdueAssignments
    ): array {
        $statusTotal = max($notStartedAssignments + $inProgressAssignments + $inReviewAssignments + $completedAssignments, 1);

        $dueSoonList = $unfinishedAssignments
            ->filter(fn ($assignment) => $assignment['daysLeft'] !== null && $assignment['daysLeft'] <= 3)
            ->take(5)
            ->values()
            ->map(fn ($assignment) => self::withAction($assignment))
            ->all();

        $overdueList = $overdueAssignments
            ->take(5)
            ->values()
            ->map(fn ($assignment) => self::withAction($assignment))
            ->all();

        return [
            'statusChart' => [
                'id' => 'evaluateeStatusChart',
                'labels' => ['ยังไม่ประเมิน', 'กำลังดำเนินการ', 'รอผลการประเมิน', 'ประเมินเสร็จสิ้น'],
                'data' => [$notStartedAssignments, $inProgressAssignments, $inReviewAssignments, $completedAssignments],
                'colors' => ['#ef4444', '#3b82f6', '#eab308', '#22c55e'],
                'filters' => ['ยังไม่ประเมิน', 'กำลังดำเนินการ', 'รอผลการประเมิน', 'ประเมินเสร็จสิ้น'],
                'centerValue' => $totalAssignments,
                'centerLabel' => 'งานประเมินทั้งหมด',
            ],
            'overviewCards' => [
                [
                    'filter' => 'ยังไม่ประเมิน',
                    'title' => 'ยังไม่ประเมิน',
                    'count' => $notStartedAssignments,
                    'percent' => round(($notStartedAssignments / $statusTotal) * 100, 1),
                    'tone' => 'rose',
                ],
                [
                    'filter' => 'กำลังดำเนินการ',
                    'title' => 'กำลังดำเนินการ',
                    'count' => $inProgressAssignments,
                    'percent' => round(($inProgressAssignments / $statusTotal) * 100, 1),
                    'tone' => 'blue',
                ],
                [
                    'filter' => 'รอผลการประเมิน',
                    'title' => 'รอผลการประเมิน',
                    'count' => $inReviewAssignments,
                    'percent' => round(($inReviewAssignments / $statusTotal) * 100, 1),
                    'tone' => 'amber',
                ],
                [
                    'filter' => 'ประเมินเสร็จสิ้น',
                    'title' => 'ประเมินเสร็จสิ้น',
                    'count' => $completedAssignments,
                    'percent' => round(($completedAssignments / $statusTotal) * 100, 1),
                    'tone' => 'emerald',
                ],
            ],
            'dueSoonCount' => count($dueSoonList),
            'overdueCount' => count($overdueList),
            'dueSoonList' => $dueSoonList,
            'overdueList' => $overdueList,
            'actionRequiredAssignments' => $actionRequiredAssignments,
        ];
    }

    private static function withAction(array $assignment): array
    {
        $action = match ($assignment['status'] ?? 'Assigned') {
            'Draft' => [
                'label' => 'ประเมินต่อ',
                'classes' => 'bg-blue-500 text-white hover:bg-blue-600',
            ],
            default => [
                'label' => 'เริ่มประเมิน',
                'classes' => 'bg-red-500 text-white hover:bg-red-600',
            ],
        };

        $assignment['action'] = $action;

        return $assignment;
    }
}
