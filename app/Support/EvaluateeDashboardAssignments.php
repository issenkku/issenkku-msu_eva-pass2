<?php

// ไฟล์คลาสของระบบ: app/Support/EvaluateeDashboardAssignments.php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class EvaluateeDashboardAssignments
{
    public static function build(Collection $evaluations): array
    {
        $statusCounts = [
            'ทั้งหมด' => $evaluations->count(),
            'ยังไม่ประเมิน' => self::countByStatus($evaluations, ['Assigned']),
            'กำลังดำเนินการ' => self::countByStatus($evaluations, ['Draft']),
            'รอผลการประเมิน' => self::countByStatus($evaluations, [
                'Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft',
                'Manager_assign', 'Manager_draft',
            ]),
            'ประเมินเสร็จสิ้น' => self::countByStatus($evaluations, ['Completed']),
        ];

        $openAssignments = $evaluations->filter(function ($assignment) {
            $status = optional($assignment->report)->status ?? 'Assigned';

            return in_array($status, ['Assigned', 'Draft'], true);
        })->map(function ($assignment) {
            $startTime = optional($assignment->assignmentData)->start_time;
            $endTime = optional($assignment->assignmentData)->end_time;

            return [
                'id' => $assignment->report->id ?? null,
                'title' => optional($assignment->report->reportData)->report_title ?? 'ไม่พบชื่อรายงาน',
                'period' => self::formatThai($startTime) . ' - ' . self::formatThai($endTime),
                'deadline' => self::formatThai($endTime),
                'daysLeft' => $endTime ? now()->startOfDay()->diffInDays(Carbon::parse($endTime)->startOfDay(), false) : null,
                'status' => optional($assignment->report)->status ?? 'Assigned',
            ];
        })->values();

        $unfinishedAssignments = $openAssignments
            ->filter(fn ($assignment) => $assignment['daysLeft'] !== null && $assignment['daysLeft'] >= 0)
            ->values();

        $overdueAssignments = $openAssignments
            ->filter(fn ($assignment) => $assignment['daysLeft'] !== null && $assignment['daysLeft'] < 0)
            ->values();

        return [
            'statusCounts' => $statusCounts,
            'unfinishedAssignments' => $unfinishedAssignments,
            'overdueAssignments' => $overdueAssignments,
            'completedAssignments' => self::countByStatus($evaluations, ['Completed']),
            'notStartedAssignments' => self::countByStatus($evaluations, ['Assigned']),
            'inProgressAssignments' => self::countByStatus($evaluations, ['Draft']),
            'actionRequiredAssignments' => self::countByStatus($evaluations, ['Assigned', 'Draft']),
            'inReviewAssignments' => self::countByStatus($evaluations, [
                'Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft',
                'Manager_assign', 'Manager_draft',
            ]),
            'totalAssignments' => $evaluations->count(),
            'dueSoonAssignments' => $unfinishedAssignments
                ->filter(fn ($assignment) => $assignment['daysLeft'] !== null && $assignment['daysLeft'] <= 3)
                ->count(),
        ];
    }

    private static function countByStatus(Collection $evaluations, array $statuses): int
    {
        return $evaluations->filter(function ($assignment) use ($statuses) {
            $reportStatus = optional($assignment->report)->status ?? 'Assigned';

            return in_array($reportStatus, $statuses, true);
        })->count();
    }

    private static function formatThai($datetime): string
    {
        if (! $datetime) {
            return '-';
        }

        Carbon::setLocale('th');
        setlocale(LC_TIME, 'th_TH.UTF-8');
        $date = Carbon::parse($datetime);

        return $date->translatedFormat('j F') . ' ' . ($date->year + 543);
    }
}
