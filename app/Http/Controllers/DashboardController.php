<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Models\Department;
use App\Models\AssignmentData;
use App\Models\Assignment;
use App\Exports\UsersExport;
use App\Models\QuantityScore;
use App\Models\Reports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $startDate = $request->input('start_time');
        $endDate = $request->input('end_time');
        $departmentName = $request->input('department_name');

        if (!$startDate && !$endDate) {
            $latestPeriod = AssignmentData::latest('end_time')->first();
            if ($latestPeriod) {
                $startDate = $latestPeriod->start_time;
                $endDate = $latestPeriod->end_time;
            }
        }

        // Base query for reports
        $reportsQuery = Report::query()
            ->join('assignments', 'reports.id', '=', 'assignments.report_id')
            ->join('assignment_datas', 'assignments.assignment_data_id', '=', 'assignment_datas.id')
            ->join('users as evaluatees', 'assignments.evaluatee', '=', 'evaluatees.id')
            ->join('users as evaluators', 'assignments.evaluator', '=', 'evaluators.id')
            ->join('departments as evaluatees_dept', 'evaluatees.department_id', '=', 'evaluatees_dept.id')
            ->join('positions as evaluatees_position', 'evaluatees.position_id', '=', 'evaluatees_position.id')
            ->select(
                'assignment_datas.id as assignment_data_id',
                'assignment_datas.start_time',
                'assignment_datas.end_time',

                'evaluatees.department_id as evaluatee_department_id',
                'evaluatees.id as evaluatee_id',
                'evaluatees.name as evaluatee_name',
                'evaluatees.personnel_type as evaluatee_personnel_type',
                'evaluatees.position_id as evaluatee_position_id',
                'evaluatees_position.name as evaluatee_position_name',
                'evaluatees_dept.department_name as evaluatee_department_name',

                'evaluators.id as evaluator_id',
                'evaluators.name as evaluator_name',

                'reports.id as report_id',
                'reports.status as report_status',
                'reports.created_at as report_created_at',
                'reports.updated_at as report_updated_at',
                'reports.report_data_id as report_data_id'
            )->orderBy('reports.updated_at', 'desc');


        // Apply date filters if provided
        // if ($startDate) {
        //     $reportsQuery->where('assignment_datas.start_time', '>=', $startDate);
        // }

        // if ($endDate) {
        //     $reportsQuery->where('assignment_datas.end_time', '<=', $endDate);
        // }

        // // Apply department filter if provided
        // if ($departmentName) {
        //     $reportsQuery->join('departments', 'evaluatees.department_id', '=', 'departments.id')
        //         ->where('departments.name', $departmentName);
        // }

        // Get total participants (unique evaluatees)
        // $totalParticipants = $reportsQuery->distinct('evaluatees.id')->count('evaluatees.id');
        $totalParticipants = $reportsQuery->count('evaluatees.id');

        // Get total score per report
        // $scorePerReport = $this->getScorePerReport($reportsQuery->get());

        // $assignments = $this->getAssignmentsWithScores($reportsQuery);
        // // Calculate average score
        $averageScore = $this->calculateAverageScore($reportsQuery->get());

        // Status Chart (Bar Chart)
        $statusCounts = $this->statusCounts($reportsQuery->get());
        // Score Distribution Chart (Scatter Plot)
        $scatterData = $this->scatterData($reportsQuery->get());

        $reportsWithScores = $this->reportsWithScores($reportsQuery->get());

        // // Count passed and failed participants
        // $passThreshold = 60; // Assuming 60 is the passing threshold
        // $passFailData = $this->getPassFailData($reportsQuery->get(), $passThreshold);

        // Calculate pass rate
        // $passRate = $totalParticipants > 0 ? round(($passFailData['passedCount'] / $totalParticipants) * 100, 1) : 0;

        // // Get participants with scores for the table
        // $users = $this->getUsersWithScores($reportsQuery);

        // // Get scatter plot data for score distribution
        // $scatterData = $this->getScatterData($users);

        // // Get all departments for filter dropdown
        // $departments = Department::all();

        // // Build evaluation period string
        // $evaluationPeriod = $this->getEvaluationPeriod($startDate, $endDate);

        return view('dashboard.index', [
            // 'reports' => $groupedMainCriterias,

            'totalParticipants' => $totalParticipants,
            'averageScore' => $averageScore,
            'statusCounts_chart' => $statusCounts,
            'scatterData_chart' => $scatterData,
            // 'reports' => $reportsQuery->get(),

            'reports' => $reportsWithScores,
        ]);




        // return response()->json([
        //     // 'reports' => $groupedMainCriterias,

        //     'totalParticipants' => $totalParticipants,
        //     'averageScore' => $averageScore,
        //     'statusCounts_chart' => $statusCounts,
        //     'scatterData_chart' => $scatterData,
        //     // 'reports' => $reportsQuery->get(),

        //     'reports' => $reportsWithScores,

        // ]);
    }

    private function calculateAverageScore($reports)
    {
        if ($reports->isEmpty()) {
            return 0;
        }

        $totalScore = 0;
        $reportCount = 0;


        foreach ($reports as $report) {
            $quantityScore = QuantityScore::where('report_id', $report->report_id)
                ->sum('score_D') ?? 0;

            $qualityData = DB::table('quality_scores')
                ->join('quality_sub_criterias', 'quality_scores.quality_sub_criteria_id', '=', 'quality_sub_criterias.id')
                ->join('quality_main_criterias', 'quality_sub_criterias.quality_main_criteria_id', '=', 'quality_main_criterias.id')
                ->join('evaluation_lists', 'quality_sub_criterias.evaluation_list_id', '=', 'evaluation_lists.id')
                ->join('reports', 'quality_scores.report_id', '=', 'reports.id')
                ->select(
                    'quality_scores.quality_sub_criteria_id',
                    'quality_scores.score',
                    'quality_sub_criterias.evaluation_list_id',
                    'quality_sub_criterias.num_score',
                    'quality_main_criterias.id as quality_main_criteria_id',
                    'quality_main_criterias.ratio',
                    'evaluation_lists.sum_score',
                    'reports.id as report_id',
                )
                ->where('quality_scores.report_id', $report->report_id)
                ->groupBy(
                    'quality_sub_criterias.evaluation_list_id',
                    'quality_main_criterias.id',
                    'quality_scores.quality_sub_criteria_id',
                    'quality_scores.score',
                    'quality_sub_criterias.num_score',
                    'quality_main_criterias.ratio',
                    'reports.id'
                )
                ->get();

            $groupedMainCriterias = [];
            foreach ($qualityData as $subCriteria) {
                $evalListId = $subCriteria->evaluation_list_id;
                $mainCriteriaId = $subCriteria->quality_main_criteria_id;

                if (!isset($groupedMainCriterias[$evalListId])) {
                    $groupedMainCriterias[$evalListId] = [];
                }
                if (!isset($groupedMainCriterias[$evalListId][$mainCriteriaId])) {
                    $groupedMainCriterias[$evalListId][$mainCriteriaId] = [];
                }

                $groupedMainCriterias[$evalListId][$mainCriteriaId][] = $subCriteria;
            }
            // 2. Now process the grouped data
            $arrScoreEva = [];
            foreach ($groupedMainCriterias as $evalListId => $mainCriterias) {
                foreach ($mainCriterias as $mainCriteriaId => $subCriterias) {
                    // $sum_score_Eva = (float)$subCriterias[0]->sum_score;
                    $sum_score_Eva = (float)$subCriterias[0]->sum_score;
                    $ratio = (float)$subCriterias[0]->ratio; // ratio may not always be int!
                    $SumMaxScoreSub = [];
                    $SumAccScoreSub = [];
                    foreach ($subCriterias as $subCriteria) {
                        $maxScorePerSub = round((float)$subCriteria->num_score, 2);
                        $score = round((float)$subCriteria->score, 2);
                        if ($maxScorePerSub > 0) {
                            $SumMaxScoreSub[] = $maxScorePerSub;
                            $SumAccScoreSub[] = $score;
                        }
                    }
                    $maxSum = array_sum($SumMaxScoreSub);
                    $accSum = array_sum($SumAccScoreSub);
                    $scoreRatioMain = 0;
                    if ($maxSum > 0) {
                        $scoreRatioMain = $ratio * ($accSum / $maxSum);
                    }
                    $arrScoreEva[] = ($scoreRatioMain / 100) * $sum_score_Eva;
                }
            }
            $qualityScore = array_sum($arrScoreEva);
            // logger("mss-quantityScore : " . $quantityScore);
            // logger("mss-qualityScore : " . $qualityScore);

            $totalScore += ($quantityScore + $qualityScore); // Assuming quantity and quality scores are averaged
            $reportCount++;
        }
        return  round($totalScore / $reportCount, 2);
        // return  $totalScore ;
    }

    private function statusCounts($reports)
    {
        $statusCounts = [
            'ASSIGNED' => 0,
            'DRAFT' => 0,
            'PENDING' => 0,
            'COMPLETED' => 0
        ];

        foreach ($reports as $report) {
            switch ($report->report_status) {
                case 'ASSIGNED':
                    $statusCounts['ASSIGNED']++;
                    break;
                case 'DRAFT':
                    $statusCounts['DRAFT']++;
                    break;
                case 'PENDING':
                    $statusCounts['PENDING']++;
                    break;
                case 'COMPLETED':
                    $statusCounts['COMPLETED']++;
                    break;
            }
        }

        return $statusCounts;
    }

    private function scatterData($reports)
    {
        $scatterData = [];

        if ($reports->isEmpty()) {
            return $scatterData;
        }

        $i = 1;
        foreach ($reports as $report) {
            // --- Calculate quantity score ---
            $quantityScore = QuantityScore::where('report_id', $report->report_id)
                ->sum('score_D') ?? 0;

            // --- Calculate quality score ---
            $qualityData = DB::table('quality_scores')
                ->join('quality_sub_criterias', 'quality_scores.quality_sub_criteria_id', '=', 'quality_sub_criterias.id')
                ->join('quality_main_criterias', 'quality_sub_criterias.quality_main_criteria_id', '=', 'quality_main_criterias.id')
                ->join('evaluation_lists', 'quality_sub_criterias.evaluation_list_id', '=', 'evaluation_lists.id')
                ->join('reports', 'quality_scores.report_id', '=', 'reports.id')
                ->select(
                    'quality_scores.quality_sub_criteria_id',
                    'quality_scores.score',
                    'quality_sub_criterias.evaluation_list_id',
                    'quality_sub_criterias.num_score',
                    'quality_main_criterias.id as quality_main_criteria_id',
                    'quality_main_criterias.ratio',
                    'evaluation_lists.sum_score',
                    'reports.id as report_id',
                )
                ->where('quality_scores.report_id', $report->report_id)
                ->groupBy(
                    'quality_sub_criterias.evaluation_list_id',
                    'quality_main_criterias.id',
                    'quality_scores.quality_sub_criteria_id',
                    'quality_scores.score',
                    'quality_sub_criterias.num_score',
                    'quality_main_criterias.ratio',
                    'reports.id'
                )
                ->get();

            $groupedMainCriterias = [];
            foreach ($qualityData as $subCriteria) {
                $evalListId = $subCriteria->evaluation_list_id;
                $mainCriteriaId = $subCriteria->quality_main_criteria_id;

                if (!isset($groupedMainCriterias[$evalListId])) {
                    $groupedMainCriterias[$evalListId] = [];
                }
                if (!isset($groupedMainCriterias[$evalListId][$mainCriteriaId])) {
                    $groupedMainCriterias[$evalListId][$mainCriteriaId] = [];
                }

                $groupedMainCriterias[$evalListId][$mainCriteriaId][] = $subCriteria;
            }

            $arrScoreEva = [];
            foreach ($groupedMainCriterias as $evalListId => $mainCriterias) {
                foreach ($mainCriterias as $mainCriteriaId => $subCriterias) {
                    $sum_score_Eva = (float)$subCriterias[0]->sum_score;
                    $ratio = (float)$subCriterias[0]->ratio;
                    $SumMaxScoreSub = [];
                    $SumAccScoreSub = [];
                    foreach ($subCriterias as $subCriteria) {
                        $maxScorePerSub = round((float)$subCriteria->num_score, 2);
                        $score = round((float)$subCriteria->score, 2);
                        if ($maxScorePerSub > 0) {
                            $SumMaxScoreSub[] = $maxScorePerSub;
                            $SumAccScoreSub[] = $score;
                        }
                    }
                    $maxSum = array_sum($SumMaxScoreSub);
                    $accSum = array_sum($SumAccScoreSub);
                    $scoreRatioMain = 0;
                    if ($maxSum > 0) {
                        $scoreRatioMain = $ratio * ($accSum / $maxSum);
                    }
                    $arrScoreEva[] = ($scoreRatioMain / 100) * $sum_score_Eva;
                }
            }
            $qualityScore = array_sum($arrScoreEva);

            $totalScore = ($quantityScore + $qualityScore);

            // Build the output for Chart.js
            $scatterData[] = [
                'x' => $i++,                 // or $report->report_id if you prefer
                'y' => round($totalScore, 2) // rounded to 2 decimal points
            ];
        }

        return $scatterData;
    }

    private function reportsWithScores($reports)
    {
        if ($reports->isEmpty()) {
            return [];
        }

        $reports_score = [];
        foreach ($reports as $report) {
            $quantityScore = QuantityScore::where('report_id', $report->report_id)
                ->sum('score_D') ?? 0;

            $qualityData = DB::table('quality_scores')
                ->join('quality_sub_criterias', 'quality_scores.quality_sub_criteria_id', '=', 'quality_sub_criterias.id')
                ->join('quality_main_criterias', 'quality_sub_criterias.quality_main_criteria_id', '=', 'quality_main_criterias.id')
                ->join('evaluation_lists', 'quality_sub_criterias.evaluation_list_id', '=', 'evaluation_lists.id')
                ->join('reports', 'quality_scores.report_id', '=', 'reports.id')
                ->select(
                    'quality_scores.quality_sub_criteria_id',
                    'quality_scores.score',
                    'quality_sub_criterias.evaluation_list_id',
                    'quality_sub_criterias.num_score',
                    'quality_main_criterias.id as quality_main_criteria_id',
                    'quality_main_criterias.ratio',
                    'evaluation_lists.sum_score',
                    'reports.id as report_id'
                )
                ->where('quality_scores.report_id', $report->report_id)
                ->groupBy(
                    'quality_sub_criterias.evaluation_list_id',
                    'quality_main_criterias.id',
                    'quality_scores.quality_sub_criteria_id',
                    'quality_scores.score',
                    'quality_sub_criterias.num_score',
                    'quality_main_criterias.ratio',
                    'reports.id'
                )
                ->get();

            $groupedMainCriterias = [];
            foreach ($qualityData as $subCriteria) {
                $evalListId = $subCriteria->evaluation_list_id;
                $mainCriteriaId = $subCriteria->quality_main_criteria_id;

                if (!isset($groupedMainCriterias[$evalListId])) {
                    $groupedMainCriterias[$evalListId] = [];
                }
                if (!isset($groupedMainCriterias[$evalListId][$mainCriteriaId])) {
                    $groupedMainCriterias[$evalListId][$mainCriteriaId] = [];
                }

                $groupedMainCriterias[$evalListId][$mainCriteriaId][] = $subCriteria;
            }
            $arrScoreEva = [];
            foreach ($groupedMainCriterias as $evalListId => $mainCriterias) {
                foreach ($mainCriterias as $mainCriteriaId => $subCriterias) {
                    $sum_score_Eva = (float)$subCriterias[0]->sum_score;
                    $ratio = (float)$subCriterias[0]->ratio;
                    $SumMaxScoreSub = [];
                    $SumAccScoreSub = [];
                    foreach ($subCriterias as $subCriteria) {
                        $maxScorePerSub = round((float)$subCriteria->num_score, 2);
                        $score = round((float)$subCriteria->score, 2);
                        if ($maxScorePerSub > 0) {
                            $SumMaxScoreSub[] = $maxScorePerSub;
                            $SumAccScoreSub[] = $score;
                        }
                    }
                    $maxSum = array_sum($SumMaxScoreSub);
                    $accSum = array_sum($SumAccScoreSub);
                    $scoreRatioMain = 0;
                    if ($maxSum > 0) {
                        $scoreRatioMain = $ratio * ($accSum / $maxSum);
                    }
                    $arrScoreEva[] = ($scoreRatioMain / 100) * $sum_score_Eva;
                }
            }
            $qualityScore = array_sum($arrScoreEva);

            $reports_score[] = [
                "assignment_data_id" => $report->assignment_data_id,
                "start_time" => $report->start_time,
                "end_time" => $report->end_time,
                "evaluatee_department_id" => $report->evaluatee_department_id,
                "evaluatee_id" => $report->evaluatee_id,
                "evaluatee_name" => $report->evaluatee_name,
                "evaluatee_personnel_type" => $report->evaluatee_personnel_type,
                "evaluatee_position_id" => $report->evaluatee_position_id,
                "evaluatee_position_name" => $report->evaluatee_position_name,
                "evaluatee_department_name" => $report->evaluatee_department_name,
                "evaluator_id" => $report->evaluator_id,
                "evaluator_name" => $report->evaluator_name,
                'report_id' => $report->report_id,
                'status' => $report->report_status,
                'created_at' => date('Y-m-d',strtotime($report->report_created_at)),
                'updated_at' => date('Y-m-d',strtotime($report->report_updated_at)),
                'quantity_score' => round($quantityScore, 2),
                'quality_score' => round($qualityScore, 2),
                'score' => round($quantityScore + $qualityScore, 2),
            ];
        }
        return $reports_score;
    }

    // private function getPassFailData($reports, $threshold)
    // {
    //     $passedCount = 0;
    //     $failedCount = 0;
    //     $evaluateeScores = [];

    //     foreach ($reports as $report) {
    //         $evaluateeId = $report->evaluatee;

    //         // Calculate score for this report
    //         $qualityScore = DB::table('quality_scores')
    //             ->where('report_id', $report->id)
    //             ->avg('score') ?? 0;

    //         $quantityScore = DB::table('quantity_scores')
    //             ->where('report_id', $report->id)
    //             ->selectRaw('AVG((score_C + score_D) / 2) as avg_score')
    //             ->value('avg_score') ?? 0;

    //         $reportScore = ($qualityScore + $quantityScore) / 2;

    //         // Store the highest score for each evaluatee
    //         if (!isset($evaluateeScores[$evaluateeId]) || $reportScore > $evaluateeScores[$evaluateeId]) {
    //             $evaluateeScores[$evaluateeId] = $reportScore;
    //         }
    //     }

    //     // Count passed and failed based on the highest score for each evaluatee
    //     foreach ($evaluateeScores as $score) {
    //         if ($score >= $threshold) {
    //             $passedCount++;
    //         } else {
    //             $failedCount++;
    //         }
    //     }

    //     return [
    //         'passedCount' => $passedCount,
    //         'failedCount' => $failedCount
    //     ];
    // }

    // private function getUsersWithScores($reportsQuery)
    // {
    //     $evaluateeIds = $reportsQuery->pluck('evaluatees.id')->unique();

    //     $users = User::whereIn('id', $evaluateeIds)->get();

    //     foreach ($users as $user) {
    //         // Get the latest report for this user
    //         $latestReport = Report::join('assignments', 'reports.id', '=', 'assignments.report_id')
    //             ->where('assignments.evaluatee', $user->id)
    //             ->orderBy('reports.updated_at', 'desc')
    //             ->first();

    //         if ($latestReport) {
    //             // Calculate score for this report
    //             $qualityScore = DB::table('quality_scores')
    //                 ->where('report_id', $latestReport->id)
    //                 ->avg('score') ?? 0;

    //             $quantityScore = DB::table('quantity_scores')
    //                 ->where('report_id', $latestReport->id)
    //                 ->selectRaw('AVG((score_C + score_D) / 2) as avg_score')
    //                 ->value('avg_score') ?? 0;

    //             $user->score = round(($qualityScore + $quantityScore) / 2, 1);
    //         } else {
    //             $user->score = 0;
    //         }
    //     }

    //     return $users;
    // }

    // private function getScatterData($users)
    // {
    //     $scatterData = [];

    //     foreach ($users as $index => $user) {
    //         $scatterData[] = [
    //             'x' => $index + 1, // Using index for x-axis
    //             'y' => $user->score
    //         ];
    //     }

    //     return $scatterData;
    // }

    // private function getEvaluationPeriod($startDate, $endDate)
    // {
    //     if ($startDate && $endDate) {
    //         return Carbon::parse($startDate)->format('M d, Y') . ' - ' . Carbon::parse($endDate)->format('M d, Y');
    //     }

    //     // If no dates provided, get the most recent evaluation period
    //     $latestPeriod = AssignmentData::latest('end_time')->first();

    //     if ($latestPeriod) {
    //         return Carbon::parse($latestPeriod->start_time)->format('M d, Y') . ' - ' .
    //             Carbon::parse($latestPeriod->end_time)->format('M d, Y');
    //     }

    //     return 'Current Period';
    // }

    // /**
    //  * Get chart data for AJAX updates
    //  */
    // public function getChartData(Request $request)
    // {
    //     // Get filter parameters
    //     $startDate = $request->input('start_time');
    //     $endDate = $request->input('end_time');
    //     $departmentName = $request->input('department_name');

    //     // Use the same query logic as in the index method
    //     $reportsQuery = Report::query()
    //         ->join('assignments', 'reports.id', '=', 'assignments.report_id')
    //         ->join('assignment_datas', 'assignments.assignment_data_id', '=', 'assignment_datas.id')
    //         ->join('users as evaluatees', 'assignments.evaluatee', '=', 'evaluatees.id');

    //     // Apply the same filters
    //     if ($startDate) {
    //         $reportsQuery->where('assignment_datas.start_time', '>=', $startDate);
    //     }

    //     if ($endDate) {
    //         $reportsQuery->where('assignment_datas.end_time', '<=', $endDate);
    //     }

    //     if ($departmentName) {
    //         $reportsQuery->join('departments', 'evaluatees.department_id', '=', 'departments.id')
    //             ->where('departments.name', $departmentName);
    //     }

    //     // Get the required data
    //     $passFailData = $this->getPassFailData($reportsQuery->get(), 60);
    //     $users = $this->getUsersWithScores($reportsQuery);
    //     $scatterData = $this->getScatterData($users);

    //     // Return data as JSON
    //     return response()->json([
    //         'passFailData' => [
    //             'failed' => $passFailData['failedCount'],
    //             'passed' => $passFailData['passedCount']
    //         ],
    //         'scatterData' => $scatterData
    //     ]);
    // }

    /**
     * Export dashboard data to Excel
     */
    // public function exportData(Request $request)
    // {
    //     // Get filter parameters
    //     $startDate = $request->input('start_time');
    //     $endDate = $request->input('end_time');
    //     $departmentName = $request->input('department_name');

    //     // Build query with filters
    //     $reportsQuery = Reports::query()
    //         ->join('assignments', 'reports.id', '=', 'assignments.report_id')
    //         ->join('assignment_datas', 'assignments.assignment_data_id', '=', 'assignment_datas.id')
    //         ->join('users as evaluatees', 'assignments.evaluatee', '=', 'evaluatees.id');

    //     // Apply filters
    //     if ($startDate) {
    //         $reportsQuery->where('assignment_datas.start_time', '>=', $startDate);
    //     }

    //     if ($endDate) {
    //         $reportsQuery->where('assignment_datas.end_time', '<=', $endDate);
    //     }

    //     if ($departmentName) {
    //         $reportsQuery->join('departments', 'evaluatees.department_id', '=', 'departments.id')
    //             ->where('departments.name', $departmentName);
    //     }

    //     // Get users with scores
    //     $users = $this->getUsersWithScores($reportsQuery);

    //     // Create Excel file using Laravel Excel or similar library
    //     return Excel::download(new UsersExport($users), 'evaluation_results.xlsx');
    // }
}
