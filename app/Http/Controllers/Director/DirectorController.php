<?php

namespace App\Http\Controllers\Director;


use App\Http\Controllers\Concerns\BuildsDashboardMetrics;
use App\Http\Controllers\Controller;
use App\Models\Reports;
use App\Models\Setting\Departments;
use App\Services\GraphDataService;
use App\Services\ScoreService;
use App\Support\Dashboard\DirectorDashboardMeta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\EvaluationService;
use Illuminate\Pagination\LengthAwarePaginator;

class DirectorController extends Controller
{
    use BuildsDashboardMetrics;

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

        $filters = $request->only(['search', 'year', 'start_time', 'end_time', 'department_name', 'status', 'urgency']);
        $hasDirectorFilters = $this->hasActiveFilters($request, ['search', 'start_time', 'end_time', 'department_name', 'status', 'urgency', 'year']);
        $activeDirectorFilters = $this->activeFilters($request, ['search', 'start_time', 'end_time', 'department_name', 'status', 'urgency', 'year']);
        $departments = Departments::all();

        // Get ALL reports with complete data (Director has access to everything)
        $allReportsData = $evaluationService->getAllReportsWithAssignments();
        $evaluations = $evaluationService->mapAssignments($allReportsData);
        $evaluations = $evaluations->filter(function ($assignment) use ($user) {
            $directorId = optional($assignment->assignmentData)->director_id;

            return ! $directorId || (int) $directorId === (int) $user->id;
        })->values();
        $evaluations = $evaluationService->filterEvaluations($evaluations, $filters);
        if ($request->filled('status')) {
            $status = $request->input('status');
            $statusGroups = DirectorDashboardMeta::statusGroups();

            if (isset($statusGroups[$status])) {
                $evaluations = $evaluations->filter(function ($assignment) use ($statusGroups, $status) {
                    return in_array(optional($assignment->report)->status, $statusGroups[$status], true);
                });
            }
        }
        $evaluations = $evaluationService->sortEvaluations($evaluations);

        $userAsEvaluatee = $evaluationService->getUserAsEvaluatee($user);
        $userAsEvaluator = $evaluationService->getUserAsEvaluator($user);

        // Count status for ALL evaluations (Director sees everything)
        $statusCounts = [
            'ทั้งหมด' => $evaluations->count(),
            'รอการกรอกข้อมูล' => $this->countByStatuses($evaluations, ['Assigned', 'Draft', 'Pending', 'Evaluator_draft']),
            'ยังไม่ประเมิน' => $this->countByStatuses($evaluations, ['Director_assigned']),
            'กำลังดำเนินการ' => $this->countByStatuses($evaluations, ['Director_draft']),
            'รอผลการประเมิน' => $this->countByStatuses($evaluations, ['Manager_draft', 'Manager_assign']),
            'ประเมินเสร็จสิ้น' => $this->countByStatuses($evaluations, ['Completed']),
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
        $dueSoonCount = $this->countDueSoonEvaluations($evaluations);
        $overdueCount = $this->countOverdueEvaluations($evaluations);
        $followUpEvaluations = $evaluations
            ->filter(fn ($assignment) => optional($assignment->report)->status !== 'Completed')
            ->sortBy([
                fn ($assignment) => DirectorDashboardMeta::followUpPriority(optional($assignment->report)->status),
                fn ($assignment) => optional($assignment->assignmentData)->end_time ?? '9999-12-31',
            ])
            ->map(function ($assignment) {
                $statusMeta = DirectorDashboardMeta::statusMeta(optional($assignment->report)->status);
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
        $totalUsers = \App\Models\User::count();
        $overviewEvaluateeStatusCounts = DirectorDashboardMeta::summarizeOverviewStatuses($evaluations);
        $awaitingDirectorCount = $overviewEvaluateeStatusCounts['ยังไม่ประเมิน'] ?? 0;
        $directorInProgressCount = $overviewEvaluateeStatusCounts['กำลังดำเนินการ'] ?? 0;
        $forwardedToManagerCount = $overviewEvaluateeStatusCounts['รอผลการประเมิน'] ?? 0;
        $completedCount = $overviewEvaluateeStatusCounts['ประเมินเสร็จสิ้น'] ?? 0;
        $progressPercent = $totalEvaluatees > 0
            ? round(($completedCount / $totalEvaluatees) * 100, 1)
            : 0;
        $directorOverviewChart = [
            'id' => 'directorOverviewChart',
            'labels' => ['รอการกรอกข้อมูล', 'ยังไม่ประเมิน', 'กำลังดำเนินการ', 'รอผลการประเมิน', 'ประเมินเสร็จสิ้น'],
            'data' => [
                $overviewEvaluateeStatusCounts['รอการกรอกข้อมูล'] ?? 0,
                $awaitingDirectorCount,
                $directorInProgressCount,
                $forwardedToManagerCount,
                $completedCount,
            ],
            'colors' => ['#f97316', '#ef4444', '#3b82f6', '#f59e0b', '#22c55e'],
            'filters' => [
                $request->fullUrlWithQuery(['status' => 'รอการกรอกข้อมูล']),
                $request->fullUrlWithQuery(['status' => 'ยังไม่ประเมิน']),
                $request->fullUrlWithQuery(['status' => 'กำลังดำเนินการ']),
                $request->fullUrlWithQuery(['status' => 'รอผลการประเมิน']),
                $request->fullUrlWithQuery(['status' => 'ประเมินเสร็จสิ้น']),
            ],
            'centerValue' => $progressPercent.'%',
            'centerLabel' => 'ความคืบหน้ารวม',
            'centerMeta' => "ปิดงานแล้ว {$completedCount} จาก {$totalEvaluatees} คน",
        ];
        $directorOverviewPercents = $this->buildOverviewPercents($directorOverviewChart['data'], $totalEvaluatees);

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
            'hasDirectorFilters' => $hasDirectorFilters,
            'activeDirectorFilters' => $activeDirectorFilters,
            'directorFilterStatusOptions' => DirectorDashboardMeta::filterStatusOptions(),
            'averageScore' => $averageScore,
            'totalEvaluations' => $totalEvaluations,
            'totalEvaluatees' => $totalEvaluatees,
            'totalUsers' => $totalUsers,
            'overviewEvaluateeStatusCounts' => $overviewEvaluateeStatusCounts,
            'departments' => $departments,
            'awaitingDirectorCount' => $awaitingDirectorCount,
            'directorInProgressCount' => $directorInProgressCount,
            'forwardedToManagerCount' => $forwardedToManagerCount,
            'completedCount' => $completedCount,
            'progressPercent' => $progressPercent,
            'directorOverviewChart' => $directorOverviewChart,
            'directorOverviewPercents' => $directorOverviewPercents,
            'dueSoonCount' => $dueSoonCount,
            'overdueCount' => $overdueCount,
            'followUpEvaluations' => $followUpEvaluations,
        ]);
    }

}
