<?php

namespace App\Http\Controllers\Director;


use App\Http\Controllers\Controller;
use App\Models\Reports;
use App\Models\Setting\Departments;
use App\Services\GraphDataService;
use App\Services\ScoreService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\EvaluationService;
use Illuminate\Pagination\LengthAwarePaginator;

class DirectorController extends Controller
{
    private function countByStatus($evaluations, $statuses)
    {
        return $evaluations->filter(function ($assignment) use ($statuses) {
            $reportStatus = optional($assignment->report)->status ?? 'Assigned';

            return in_array($reportStatus, $statuses);
        })->count();
    }

    /**
     * เมธอด: dashboard
     * จุดประสงค์: แสดงหน้า director_dashboard.index บันทึกข้อมูล LengthAwarePaginator
     * อินพุต: ข้อมูลจากคำขอ, โมเดล EvaluationService
     * เอาต์พุต: หน้า director_dashboard.index
     * @param Request $request ค่าที่รับเข้ามา
     * @param EvaluationService $evaluationService ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function dashboard(Request $request, EvaluationService $evaluationService)
    {
        // Load user with comprehensive relationships based on actual schema
        $user = $request->user()->load([
            'position',
            'department',
        ]);

        $filters = $request->only(['search', 'year', 'start_time', 'end_time', 'department_name']);
        $departments = Departments::all();

        // Get ALL reports with complete data (Director has access to everything)
        $allReportsData = $evaluationService->getAllReportsWithAssignments();
        $evaluations = $evaluationService->mapAssignments($allReportsData);
        $evaluations = $evaluationService->filterEvaluations($evaluations, $filters);
        $evaluations = $evaluationService->sortEvaluations($evaluations);

        $userAsEvaluatee = $evaluationService->getUserAsEvaluatee($user);
        $userAsEvaluator = $evaluationService->getUserAsEvaluator($user);

        // Count status for ALL evaluations (Director sees everything)
        $statusCounts = [
            'ทั้งหมด' => $evaluations->count(),
            'รอการกรอกข้อมูล' => $this->countByStatus($evaluations, ['Assigned', 'Draft', 'Pending', 'Evaluator_draft']),
            'ยังไม่ประเมิน' => $this->countByStatus($evaluations, ['Director_assigned']),
            'กำลังดำเนินการ' => $this->countByStatus($evaluations, ['Director_draft']),
            'รอผลการประเมิน' => $this->countByStatus($evaluations, ['Manager_draft', 'Manager_assign']),
            'ประเมินเสร็จสิ้น' => $this->countByStatus($evaluations, ['Completed']),
        ];

        // Additional counts by department (useful for director overview)
        $departmentCounts = $evaluations->groupBy('evaluateeDepartment')->map(function ($deptEvaluations) {
            return [
                'total' => $deptEvaluations->count(),
                'completed' => $deptEvaluations->where('report.status', 'Completed')->count(),
                'pending' => $deptEvaluations->where('report.status', '!=', 'Completed')->count(),
            ];
        });

        $totalEvaluations = $evaluations->count();
        $awaitingDirectorCount = $this->countByStatus($evaluations, ['Director_assigned']);
        $directorInProgressCount = $this->countByStatus($evaluations, ['Director_draft']);
        $forwardedToManagerCount = $this->countByStatus($evaluations, ['Manager_assign', 'Manager_draft']);
        $completedCount = $this->countByStatus($evaluations, ['Completed']);
        $progressPercent = $totalEvaluations > 0
            ? round(($completedCount / $totalEvaluations) * 100, 1)
            : 0;
        $dueSoonCount = $evaluations->filter(function ($assignment) {
            $endTime = optional($assignment->assignmentData)->end_time;
            $status = optional($assignment->report)->status;

            if (! $endTime || $status === 'Completed') {
                return false;
            }

            return Carbon::parse($endTime)->between(now()->startOfDay(), now()->copy()->addDays(7)->endOfDay());
        })->count();
        $overdueCount = $evaluations->filter(function ($assignment) {
            $endTime = optional($assignment->assignmentData)->end_time;
            $status = optional($assignment->report)->status;

            if (! $endTime || $status === 'Completed') {
                return false;
            }

            return Carbon::parse($endTime)->endOfDay()->lt(now());
        })->count();
        $followUpEvaluations = $evaluations
            ->filter(fn ($assignment) => optional($assignment->report)->status !== 'Completed')
            ->sortBy([
                fn ($assignment) => $this->getFollowUpPriority(optional($assignment->report)->status),
                fn ($assignment) => optional($assignment->assignmentData)->end_time ?? '9999-12-31',
            ])
            ->map(function ($assignment) {
                $statusMeta = $this->getStatusMeta(optional($assignment->report)->status);
                $endTime = optional($assignment->assignmentData)->end_time;

                return [
                    'evaluatee_name' => $assignment->evaluateeName ?? '-',
                    'pretty_status' => $statusMeta['label'],
                    'progress_percent' => $statusMeta['progress'],
                    'due_date' => $endTime ? Carbon::parse($endTime)->format('d/m/Y') : '-',
                    'remaining_text' => $this->formatRemainingText($endTime),
                ];
            })
            ->values();

        $totalEvaluatees = $evaluations
            ->filter(fn ($assignment) => $assignment->evaluateeUser) // Ensure no nulls
            ->groupBy('evaluateeUser.id')
            ->count();

        $userReports = $evaluations->map(function ($assignment) {
            return $assignment->report;
        })->filter();

        $averageScore = ScoreService::calculateAverageScore($userReports);

        $page = $request->input('page', 1);
        $perPage = 10;
        $paginatedEvaluations = new LengthAwarePaginator(
            $evaluations->forPage($page, $perPage),
            $evaluations->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('director_dashboard.index', [
            'user' => $user,
            'statusCounts' => $statusCounts,
            'departmentCounts' => $departmentCounts, // Department breakdown
            'evaluations' => $paginatedEvaluations, // ALL evaluations (Director view)
            'userAsEvaluatee' => $userAsEvaluatee, // Director's evaluatee assignments
            'userAsEvaluator' => $userAsEvaluator, // Director's evaluator assignments
            'allReportsData' => $allReportsData, // Complete reports data
            'years' => $evaluations->pluck('assignmentData.start_time')->map(fn($d) => Carbon::parse($d)->year)->unique()->sortDesc(),
            'averageScore' => $averageScore,
            'totalEvaluations' => $totalEvaluations,
            'totalEvaluatees' => $totalEvaluatees,
            'departments' => $departments,
            'awaitingDirectorCount' => $awaitingDirectorCount,
            'directorInProgressCount' => $directorInProgressCount,
            'forwardedToManagerCount' => $forwardedToManagerCount,
            'completedCount' => $completedCount,
            'progressPercent' => $progressPercent,
            'dueSoonCount' => $dueSoonCount,
            'overdueCount' => $overdueCount,
            'followUpEvaluations' => $followUpEvaluations,
        ]);
    }

    private function getStatusMeta(?string $status): array
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

    private function getFollowUpPriority(?string $status): int
    {
        return match ($status) {
            'Director_assigned' => 1,
            'Director_draft' => 2,
            'Assigned', 'Draft', 'Pending', 'Evaluator_draft' => 3,
            default => 4,
        };
    }

    private function formatRemainingText($endTime): string
    {
        if (! $endTime) {
            return '-';
        }

        $days = now()->startOfDay()->diffInDays(Carbon::parse($endTime)->startOfDay(), false);

        if ($days < 0) {
            return 'เลยกำหนด '.abs($days).' วัน';
        }

        if ($days === 0) {
            return 'ครบกำหนดวันนี้';
        }

        return 'เหลือ '.$days.' วัน';
    }
}
