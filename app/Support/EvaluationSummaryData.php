<?php

// ไฟล์คลาสของระบบ: app/Support/EvaluationSummaryData.php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class EvaluationSummaryData
{
    public static function fromAssignments(iterable $evaluations, array $statusCounts, ?string $currentStatus = null): array
    {
        $items = collect($evaluations);
        $firstStatus = array_key_first($statusCounts);

        return [
            'statusFilters' => self::buildStatusFilters($statusCounts, $firstStatus, $currentStatus),
            'rows' => self::buildRows($items),
        ];
    }

    private static function buildStatusFilters(array $statusCounts, ?string $firstStatus, ?string $currentStatus): array
    {
        $statusStyles = [
            'ทั้งหมด' => 'bg-gray-100 text-gray-800 hover:bg-gray-200',
            'ยังไม่ประเมิน' => 'bg-red-100 text-red-800 hover:bg-red-200',
            'กำลังดำเนินการ' => 'bg-blue-100 text-blue-800 hover:bg-blue-200',
            'รอผลการประเมิน' => 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
            'ประเมินเสร็จสิ้น' => 'bg-green-100 text-green-800 hover:bg-green-200',
        ];

        return collect($statusCounts)->map(function ($count, $status) use ($firstStatus, $currentStatus, $statusStyles) {
            $isShowAll = $status === $firstStatus;
            $isActive = $isShowAll ? $currentStatus === null : $currentStatus === $status;

            return [
                'label' => $status,
                'count' => $count,
                'filter' => $isShowAll ? 'all' : $status,
                'classes' => $statusStyles[$status] ?? 'bg-gray-100 text-gray-800 hover:bg-gray-200',
                'active' => $isActive,
            ];
        })->values()->all();
    }

    private static function buildRows(Collection $evaluations): array
    {
        $statusMapping = [
            'Assigned' => 'ยังไม่ประเมิน',
            'Draft' => 'กำลังดำเนินการ',
            'Pending' => 'รอผู้ประเมินประเมิน',
            'Evaluator_draft' => 'ผู้ประเมินเริ่มประเมิน',
            'Director_assigned' => 'รอกรรมการรับรองผล',
            'Director_draft' => 'กรรมการเริ่มรับรองผล',
            'Manager_assign' => 'รอผู้บริหารรับรองผล',
            'Manager_draft' => 'ผู้บริหารเริ่มรับรองผล',
            'Completed' => 'ประเมินเสร็จสิ้น',
        ];

        $statusGroupMapping = [
            'Assigned' => 'ยังไม่ประเมิน',
            'Draft' => 'กำลังดำเนินการ',
            'Pending' => 'รอผลการประเมิน',
            'Evaluator_draft' => 'รอผลการประเมิน',
            'Director_assigned' => 'รอผลการประเมิน',
            'Director_draft' => 'รอผลการประเมิน',
            'Manager_assign' => 'รอผลการประเมิน',
            'Manager_draft' => 'รอผลการประเมิน',
            'Completed' => 'ประเมินเสร็จสิ้น',
        ];

        $statusClasses = [
            'ยังไม่ประเมิน' => 'bg-red-100 text-red-800',
            'กำลังดำเนินการ' => 'bg-blue-100 text-blue-800',
            'รอผู้ประเมินประเมิน' => 'bg-yellow-100 text-yellow-800',
            'ผู้ประเมินเริ่มประเมิน' => 'bg-yellow-100 text-yellow-800',
            'รอกรรมการรับรองผล' => 'bg-yellow-100 text-yellow-800',
            'กรรมการเริ่มรับรองผล' => 'bg-yellow-100 text-yellow-800',
            'รอผู้บริหารรับรองผล' => 'bg-yellow-100 text-yellow-800',
            'ผู้บริหารเริ่มรับรองผล' => 'bg-yellow-100 text-yellow-800',
            'ประเมินเสร็จสิ้น' => 'bg-green-100 text-green-800',
        ];

        $actions = [
            'ยังไม่ประเมิน' => ['label' => 'เริ่มประเมิน', 'classes' => 'bg-red-500 hover:bg-red-600 text-white'],
            'กำลังดำเนินการ' => ['label' => 'ประเมินต่อ', 'classes' => 'bg-blue-500 hover:bg-blue-600 text-white'],
            'รอผู้ประเมินประเมิน' => ['label' => 'ดูการกรอกข้อมูล', 'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white'],
            'ผู้ประเมินเริ่มประเมิน' => ['label' => 'ดูการกรอกข้อมูล', 'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white'],
            'รอกรรมการรับรองผล' => ['label' => 'ดูการกรอกข้อมูล', 'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white'],
            'กรรมการเริ่มรับรองผล' => ['label' => 'ดูการกรอกข้อมูล', 'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white'],
            'รอผู้บริหารรับรองผล' => ['label' => 'ดูการกรอกข้อมูล', 'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white'],
            'ผู้บริหารเริ่มรับรองผล' => ['label' => 'ดูการกรอกข้อมูล', 'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white'],
            'ประเมินเสร็จสิ้น' => ['label' => 'ดูผล', 'classes' => 'bg-green-500 hover:bg-green-600 text-white'],
        ];

        return $evaluations
            ->sortByDesc(function ($assignment) {
                $endTime = optional($assignment->assignmentData)->end_time;
                if ($endTime) {
                    return Carbon::parse($endTime)->timestamp;
                }

                $startTime = optional($assignment->assignmentData)->start_time;
                if ($startTime) {
                    return Carbon::parse($startTime)->timestamp;
                }

                return optional($assignment->report)->updated_at
                    ? Carbon::parse($assignment->report->updated_at)->timestamp
                    : (optional($assignment)->created_at ? Carbon::parse($assignment->created_at)->timestamp : 0);
            })
            ->values()
            ->map(function ($assignment, $index) use ($statusMapping, $statusGroupMapping, $statusClasses, $actions) {
                $report = $assignment->report;
                $assignmentData = $assignment->assignmentData;

                $reportTitle = optional(optional($assignmentData)->report)->reportData->report_title
                    ?? optional($report)->reportData->report_title
                    ?? '-';

                $statusFromDB = optional($report)->status ?? 'Assigned';
                $status = $statusMapping[$statusFromDB] ?? $statusFromDB;
                $statusGroup = $statusGroupMapping[$statusFromDB] ?? $status;
                $reviewerEntries = collect($assignment->reviewerEntries ?? [])->values()->all();
                $primaryReviewer = $reviewerEntries[0] ?? null;
                $additionalReviewerCount = max(count($reviewerEntries) - 1, 0);

                $start = optional($assignmentData)->start_time ? Carbon::parse($assignmentData->start_time) : null;
                $end = optional($assignmentData)->end_time ? Carbon::parse($assignmentData->end_time) : null;
                $startFormatted = self::formatThaiDate($start);
                $endFormatted = self::formatThaiDate($end);

                $isRecent = false;
                if ($end && $end->gt(Carbon::now()->subDays(3))) {
                    $isRecent = true;
                } elseif (!$end && $start && $start->gt(Carbon::now()->subDays(3))) {
                    $isRecent = true;
                }

                $url = route('evaluation.show', ['id' => $report->id ?? 0]);
                if ($statusGroup === 'รอผลการประเมิน' || $status === 'ประเมินเสร็จสิ้น') {
                    $url .= '?readonly=1';
                }

                return [
                    'index' => $index + 1,
                    'title' => $reportTitle,
                    'status' => $status,
                    'status_group' => $statusGroup,
                    'status_classes' => $statusClasses[$status] ?? 'bg-gray-100 text-gray-800',
                    'action' => $actions[$status] ?? null,
                    'url' => $url,
                    'reviewers' => $reviewerEntries,
                    'primary_reviewer' => $primaryReviewer,
                    'additional_reviewer_count' => $additionalReviewerCount,
                    'start' => $startFormatted,
                    'end' => $endFormatted,
                    'is_recent' => $isRecent,
                ];
            })
            ->all();
    }

    private static function formatThaiDate(?Carbon $date): string|array
    {
        if (!$date) {
            return '-';
        }

        Carbon::setLocale('th');
        setlocale(LC_TIME, 'th_TH.UTF-8');

        $thaiMonth = $date->translatedFormat('j F');
        $buddhistYear = $date->year + 543;
        $time = $date->format('H:i');

        return [
            'date' => "{$thaiMonth} {$buddhistYear}",
            'time' => "{$time} น.",
        ];
    }
}
