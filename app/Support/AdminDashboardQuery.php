<?php

namespace App\Support;

use App\Models\Department;
use App\Models\QuantityScore;
use App\Models\Setting\Positions;
use App\Models\User;
use App\Services\EvaluationService;
use App\Services\GraphDataService;
use App\Services\ScoreService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminDashboardQuery
{
    public function __construct(
        private readonly EvaluationService $evaluationService,
        private readonly AdminDashboardStatusSummary $statusSummary,
    ) {}

    public function handle(Request $request): AdminDashboardData
    {
        $startDate = $request->input('start_time');
        $endDate = $request->input('end_time');

        $filters = $request->only(['search', 'year', 'start_time', 'end_time', 'department_name', 'position_name']);
        $departments = Department::all();
        $positions = Positions::orderBy('name')->get();

        $allReportsData = $this->evaluationService->getAllReportsWithAssignments();
        $evaluations = $this->evaluationService->mapAssignments($allReportsData);
        $availableYears = $evaluations
            ->pluck('assignmentData.start_time')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->year)
            ->unique()
            ->sortDesc()
            ->values();
        $evaluations = $this->evaluationService->filterEvaluations($evaluations, $filters);
        $evaluations = $this->evaluationService->sortEvaluations($evaluations);

        $activeStatus = $this->statusSummary->normalizeGroup($request->input('status'));
        $statusCounts = $this->statusSummary->statusCounts($evaluations);
        $listEvaluations = $this->statusSummary->filterByGroup($evaluations, $activeStatus);

        $totalEvaluations = $evaluations->count();
        $notStartedCount = $this->statusSummary->notStartedStatusesCount($evaluations);
        $draftCount = $this->statusSummary->draftStatusesCount($evaluations);
        $inReviewCount = $this->statusSummary->inReviewStatusesCount($evaluations);
        $completedCount = $this->statusSummary->completedStatusesCount($evaluations);
        $startedCount = max($totalEvaluations - $notStartedCount, 0);
        $progressPercent = $totalEvaluations > 0 ? round(($completedCount / $totalEvaluations) * 100, 1) : 0;
        $startedPercent = $totalEvaluations > 0 ? round(($startedCount / $totalEvaluations) * 100, 1) : 0;

        $totalEvaluatees = $evaluations
            ->filter(fn ($assignment) => $assignment->evaluateeUser)
            ->groupBy('evaluateeUser.id')
            ->count();
        $completedEvaluatees = $evaluations
            ->filter(function ($assignment) {
                return optional($assignment->report)->status === 'Completed' && $assignment->evaluateeUser;
            })
            ->groupBy('evaluateeUser.id')
            ->count();
        $notStartedEvaluatees = $evaluations
            ->filter(function ($assignment) {
                return in_array(optional($assignment->report)->status ?? 'Assigned', ['Assigned', 'Manager_assign']) && $assignment->evaluateeUser;
            })
            ->groupBy('evaluateeUser.id')
            ->count();
        $startedEvaluatees = $evaluations
            ->filter(function ($assignment) {
                return in_array(optional($assignment->report)->status ?? 'Assigned', [
                    'Draft',
                    'Pending',
                    'Evaluator_draft',
                    'Director_assigned',
                    'Director_draft',
                    'Manager_draft',
                ]) && $assignment->evaluateeUser;
            })
            ->groupBy('evaluateeUser.id')
            ->count();
        $completedEvaluateesPercent = $totalEvaluatees > 0
            ? round(($completedEvaluatees / $totalEvaluatees) * 100, 1)
            : 0;
        $startedEvaluateesPercent = $totalEvaluatees > 0
            ? round(($startedEvaluatees / $totalEvaluatees) * 100, 1)
            : 0;
        $overviewEvaluateeStatusCounts = $this->statusSummary->overviewStatusCounts($evaluations);
        $overviewCompletedEvaluatees = $overviewEvaluateeStatusCounts['ประเมินเสร็จสิ้น'] ?? 0;
        $overviewCompletedEvaluateesPercent = $totalEvaluatees > 0
            ? round(($overviewCompletedEvaluatees / $totalEvaluatees) * 100, 1)
            : 0;
        $totalUsers = User::count();

        $userReports = $evaluations->map(function ($assignment) {
            return $assignment->report;
        })->filter();

        $averageScore = ScoreService::calculateAverageScore($userReports);
        $scatterData = GraphDataService::scatterData($userReports);
        $countData = GraphDataService::statusCounts($userReports);
        $chartData = array_values($countData);
        $statusLabels = GraphDataService::getStatusLabels();
        $statusColors = GraphDataService::getStatusColors();
        $reportsWithScores = $this->reportsWithScores($evaluations);

        $followUpEvaluations = $evaluations
            ->filter(fn ($assignment) => optional($assignment->report)->status !== 'Completed')
            ->sortBy(function ($assignment) {
                $progress = $this->statusToProgress(optional($assignment->report)->status ?? 'Assigned');
                $endTime = optional($assignment->assignmentData)->end_time;

                return [
                    $progress,
                    $endTime ? Carbon::parse($endTime)->timestamp : PHP_INT_MAX,
                ];
            })
            ->take(5)
            ->values();

        $page = max((int) $request->input('page', 1), 1);
        $perPage = 10;
        $paginatedEvaluations = new LengthAwarePaginator(
            $listEvaluations->forPage($page, $perPage)->values(),
            $listEvaluations->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return new AdminDashboardData([
            'averageScore' => $averageScore,
            'statusCounts' => $statusCounts,
            'activeStatus' => $activeStatus,
            'evaluations' => $paginatedEvaluations,
            'scatterData' => $scatterData,
            'chartData' => $chartData,
            'totalEvaluations' => $totalEvaluations,
            'totalEvaluatees' => $totalEvaluatees,
            'totalUsers' => $totalUsers,
            'completedEvaluatees' => $completedEvaluatees,
            'completedEvaluateesPercent' => $completedEvaluateesPercent,
            'notStartedEvaluatees' => $notStartedEvaluatees,
            'startedEvaluatees' => $startedEvaluatees,
            'startedEvaluateesPercent' => $startedEvaluateesPercent,
            'overviewEvaluateeStatusCounts' => $overviewEvaluateeStatusCounts,
            'overviewCompletedEvaluatees' => $overviewCompletedEvaluatees,
            'overviewCompletedEvaluateesPercent' => $overviewCompletedEvaluateesPercent,
            'notStartedCount' => $notStartedCount,
            'draftCount' => $draftCount,
            'inReviewCount' => $inReviewCount,
            'completedCount' => $completedCount,
            'startedCount' => $startedCount,
            'progressPercent' => $progressPercent,
            'startedPercent' => $startedPercent,
            'followUpEvaluations' => $followUpEvaluations,
            'statusLabels' => $statusLabels,
            'statusColors' => $statusColors,
            'departments' => $departments,
            'positions' => $positions,
            'reports' => $reportsWithScores,
            'evaluationPeriod' => $this->getEvaluationPeriod($startDate, $endDate),
            'years' => $availableYears,
        ]);
    }

    private function statusToProgress(string $status): int
    {
        return match ($status) {
            'Assigned', 'Manager_assign' => 0,
            'Draft' => 25,
            'Pending', 'Evaluator_draft' => 50,
            'Director_assigned', 'Manager_draft' => 75,
            'Director_draft' => 90,
            'Completed' => 100,
            default => 0,
        };
    }

    private function reportsWithScores($reports): array
    {
        if ($reports->isEmpty()) {
            return [];
        }

        $quantityScores = QuantityScore::query()
            ->whereIn('report_id', $reports->pluck('report_id')->filter()->unique()->values())
            ->selectRaw('report_id, COALESCE(SUM(score_D), 0) as total_score')
            ->groupBy('report_id')
            ->pluck('total_score', 'report_id');
        $qualityScores = ScoreService::calculateQualityScoresRawByReportIds(
            $reports->pluck('report_id')->filter()->unique()->values()
        );

        $reports_score = [];
        foreach ($reports as $report) {
            $quantityScore = (float) ($quantityScores[$report->report_id] ?? 0);
            $qualityScore = (float) ($qualityScores[$report->report_id] ?? 0);
            $report->report->quantity_score = round($quantityScore, 2);
            $report->report->quality_score = round($qualityScore, 2);
            $report->report->score = round($quantityScore + $qualityScore, 2);

            $reports_score[] = [
                'assignment_data_id' => $report->assignment_data_id,
                'start_time' => $report->start_time,
                'end_time' => $report->end_time,
                'evaluatee_department_id' => $report->evaluatee_department_id,
                'evaluatee_id' => $report->evaluatee_id,
                'evaluatee_name' => $report->evaluatee_name,
                'evaluatee_personnel_type' => $report->evaluatee_personnel_type,
                'evaluatee_position_id' => $report->evaluatee_position_id,
                'evaluatee_position_name' => $report->evaluatee_position_name,
                'evaluatee_department_name' => $report->evaluatee_department_name,
                'evaluator_position_id' => $report->evaluator_position_id,
                'evaluator_position_name' => $report->evaluator_position_name,
                'evaluator_user_id' => $report->evaluator_user_id ?? null,
                'evaluator_name' => $report->evaluator_user_name ?
                    trim(($report->evaluator_user_prefix ?? '').' '.$report->evaluator_user_name) :
                    ('ตำแหน่ง: '.$report->evaluator_position_name),
                'report_id' => $report->report_id,
                'status' => $report->report_status,
                'created_at' => date('Y-m-d', strtotime($report->report_created_at)),
                'updated_at' => date('Y-m-d', strtotime($report->report_updated_at)),
                'quantity_score' => round($quantityScore, 2),
                'quality_score' => round($qualityScore, 2),
                'score' => round($quantityScore + $qualityScore, 2),
                'comment' => $report->comment ?? null,
            ];
        }

        return $reports_score;
    }

    private function getEvaluationPeriod($startDate, $endDate): string
    {
        if ($startDate && $endDate) {
            return Carbon::parse($startDate)->format('M d, Y').' - '.Carbon::parse($endDate)->format('M d, Y');
        }

        return 'All Periods';
    }
}
