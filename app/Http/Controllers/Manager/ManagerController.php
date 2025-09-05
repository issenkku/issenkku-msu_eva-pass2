<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Reports;
use App\Models\Setting\Departments;
use App\Services\EvaluationService;
use App\Services\GraphDataService;
use App\Services\ScoreService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ManagerController extends Controller
{
    private function countByStatus($evaluations, $statuses)
    {
        return $evaluations->filter(function ($assignment) use ($statuses) {
            $reportStatus = optional($assignment->report)->status ?? 'Assigned';

            return in_array($reportStatus, $statuses);
        })->count();
    }

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

        // Get user's assignments as evaluatee (where user is being evaluated)
        $userAsEvaluatee = $evaluationService->getUserAsEvaluatee($user);
        $userAsEvaluator = $evaluationService->getUserAsEvaluator($user);

        // Count status for ALL evaluations (Director sees everything)
        $statusCounts = [
            'ทั้งหมด' => $evaluations->count(),
            'รอการกรอกข้อมูล' => $this->countByStatus($evaluations,
                ['Assigned', 'Draft', 'Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft']),
            'ยังไม่ประเมิน' => $this->countByStatus($evaluations, ['Manager_assign']),
            'กำลังดำเนินการ' => $this->countByStatus($evaluations, ['Manager_draft']),
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

        // dd($chartData);

        $totalEvaluatees = $evaluations
            ->filter(fn ($assignment) => $assignment->evaluateeUser) // Ensure no nulls
            ->groupBy('evaluateeUser.id')
            ->count();

        $userReports = $evaluations->map(function ($assignment) {
            return $assignment->report;
        })->filter();

        $averageScore = ScoreService::calculateAverageScore($userReports);
        $scatterData = GraphDataService::scatterData($userReports);
        $countData = GraphDataService::statusCounts($userReports);
        $chartData = array_values($countData);
        $statusLabels = GraphDataService::getStatusLabels();
        $statusColors = GraphDataService::getStatusColors();

        return view('manager_dashboard.index', [
            'user' => $user,
            'statusCounts' => $statusCounts,
            'departmentCounts' => $departmentCounts, // Department breakdown
            'evaluations' => $evaluations, // ALL evaluations (Director view)
            'userAsEvaluatee' => $userAsEvaluatee, // Director's evaluatee assignments
            'userAsEvaluator' => $userAsEvaluator, // Director's evaluator assignments
            'allReportsData' => $allReportsData, // Complete reports data
            'years' => $evaluations->pluck('assignmentData.start_time')->map(fn($d) =>  Carbon::parse($d)->year)->unique()->sortDesc(),
            'averageScore' => $averageScore,
            'scatterData' => $scatterData,
            'chartData' => $chartData,
            'totalEvaluations' => $totalEvaluations,
            'totalEvaluatees' => $totalEvaluatees,
            'statusLabels' => $statusLabels,
            'statusColors' => $statusColors,
            'departments' => $departments,
        ]);
    }
}
