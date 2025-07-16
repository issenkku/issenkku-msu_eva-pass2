<?php

namespace App\Http\Controllers;

use App\Models\AssignmentData;
use App\Models\Department;
use App\Models\QuantityScore;
use App\Models\Reports;
use App\Models\User;
use App\Models\Assignments;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $startDate = $request->input('start_time');
        $endDate = $request->input('end_time');
        $departmentName = $request->input('department_name');

        // Fetch all departments for the filter dropdown
        $departments = Department::all();

        // If no filters are provided, don't set default dates to ensure all data is fetched
        // $latestPeriod = AssignmentData::latest('end_time')->first();
        // if (!$startDate && !$endDate && $latestPeriod) {
        //     $startDate = $latestPeriod->start_time;
        //     $endDate = $latestPeriod->end_time;
        // }

        // Base query for reports
        $reportsQuery = Reports::query()
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
                'reports.report_data_id as report_data_id',
                'reports.comment as comment'
            )->orderBy('reports.updated_at', 'desc');

        // Apply date filters if provided
        if ($startDate) {
            $reportsQuery->where('assignment_datas.start_time', '>=', $startDate);
        }

        if ($endDate) {
            $reportsQuery->where('assignment_datas.end_time', '<=', $endDate);
        }

        // Apply department filter if provided
        if ($departmentName) {
            $reportsQuery->where('evaluatees_dept.department_name', $departmentName);
        }

        // Get total participants (unique evaluatees)
        $totalParticipants = $reportsQuery->count('evaluatees.id');
        // $totalParticipants = $reportsQuery->distinct('evaluatees.id')->count('evaluatees.id');

        // Calculate average score
        $averageScore = $this->calculateAverageScore($reportsQuery->get());

        // Status Chart (Bar Chart)
        $statusCounts = $this->statusCounts($reportsQuery->get());

        // Score Distribution Chart (Scatter Plot)
        $scatterData = $this->scatterData($reportsQuery->get());

        // Get reports with scores
        $reportsWithScores = $this->reportsWithScores($reportsQuery->get());

        // Evaluation period for display
        $evaluationPeriod = $this->getEvaluationPeriod($startDate, $endDate);

        return view('dashboard.index', [
            // 'reports' => $groupedMainCriterias,

            'totalParticipants' => $totalParticipants,
            'averageScore' => $averageScore,
            'departments' => $departments,
            'statusCounts_chart' => $statusCounts,
            'scatterData_chart' => $scatterData,
            // 'reports' => $reportsQuery->get(),
            'reports' => $reportsWithScores,
            'evaluationPeriod' => $evaluationPeriod,
        ]);

        // return response()->json([
        //     'totalParticipants' => $totalParticipants,
        //     'averageScore' => $averageScore,
        //     'departments' => $departments,
        //     'statusCounts_chart' => $statusCounts,
        //     'scatterData_chart' => $scatterData,
        //     // 'reports' => $reportsQuery->get(),
        //     'reports' => $reportsWithScores,
        //     'evaluationPeriod' => $evaluationPeriod,
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
                    'reports.id',
                    'evaluation_lists.sum_score'

                )
                ->get();

            $groupedMainCriterias = [];
            foreach ($qualityData as $subCriteria) {
                $evalListId = $subCriteria->evaluation_list_id;
                $mainCriteriaId = $subCriteria->quality_main_criteria_id;

                if (! isset($groupedMainCriterias[$evalListId])) {
                    $groupedMainCriterias[$evalListId] = [];
                }
                if (! isset($groupedMainCriterias[$evalListId][$mainCriteriaId])) {
                    $groupedMainCriterias[$evalListId][$mainCriteriaId] = [];
                }

                $groupedMainCriterias[$evalListId][$mainCriteriaId][] = $subCriteria;
            }
            // Process the grouped data
            $arrScoreEva = [];
            foreach ($groupedMainCriterias as $evalListId => $mainCriterias) {
                foreach ($mainCriterias as $mainCriteriaId => $subCriterias) {
                    $sum_score_Eva = (float) $subCriterias[0]->sum_score;
                    $ratio = (float) $subCriterias[0]->ratio;
                    $SumMaxScoreSub = [];
                    $SumAccScoreSub = [];
                    foreach ($subCriterias as $subCriteria) {
                        $maxScorePerSub = round((float) $subCriteria->num_score, 2);
                        $score = round((float) $subCriteria->score, 2);
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

            $totalScore += ($quantityScore + $qualityScore);
            $reportCount++;
        }

        return round($totalScore / $reportCount, 2);
    }

    private function statusCounts($reports)
    {
        $statusCounts = [
            'Assigned' => 0,
            'Draft' => 0,
            'Pending' => 0,
            'Completed' => 0,
        ];

        foreach ($reports as $report) {
            switch ($report->report_status) {
                case 'Assigned':
                    $statusCounts['Assigned']++;
                    break;
                case 'Draft':
                    $statusCounts['Draft']++;
                    break;
                case 'Pending':
                    $statusCounts['Pending']++;
                    break;
                case 'Completed':
                    $statusCounts['Completed']++;
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
                    'reports.id',
                    'evaluation_lists.sum_score'
                )
                ->get();

            $groupedMainCriterias = [];
            foreach ($qualityData as $subCriteria) {
                $evalListId = $subCriteria->evaluation_list_id;
                $mainCriteriaId = $subCriteria->quality_main_criteria_id;

                if (! isset($groupedMainCriterias[$evalListId])) {
                    $groupedMainCriterias[$evalListId] = [];
                }
                if (! isset($groupedMainCriterias[$evalListId][$mainCriteriaId])) {
                    $groupedMainCriterias[$evalListId][$mainCriteriaId] = [];
                }

                $groupedMainCriterias[$evalListId][$mainCriteriaId][] = $subCriteria;
            }

            $arrScoreEva = [];
            foreach ($groupedMainCriterias as $evalListId => $mainCriterias) {
                foreach ($mainCriterias as $mainCriteriaId => $subCriterias) {
                    $sum_score_Eva = (float) $subCriterias[0]->sum_score;
                    $ratio = (float) $subCriterias[0]->ratio;
                    $SumMaxScoreSub = [];
                    $SumAccScoreSub = [];
                    foreach ($subCriterias as $subCriteria) {
                        $maxScorePerSub = round((float) $subCriteria->num_score, 2);
                        $score = round((float) $subCriteria->score, 2);
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

            $scatterData[] = [
                'x' => $i++,
                'y' => round($totalScore, 2),
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
                    'reports.id as report_id',
                    'reports.comment as comment',
                )
                ->where('quality_scores.report_id', $report->report_id)
                ->groupBy(
                    'quality_sub_criterias.evaluation_list_id',
                    'quality_main_criterias.id',
                    'quality_scores.quality_sub_criteria_id',
                    'quality_scores.score',
                    'quality_sub_criterias.num_score',
                    'quality_main_criterias.ratio',
                    'reports.id',
                    'evaluation_lists.sum_score'
                )
                ->get();

            $groupedMainCriterias = [];
            foreach ($qualityData as $subCriteria) {
                $evalListId = $subCriteria->evaluation_list_id;
                $mainCriteriaId = $subCriteria->quality_main_criteria_id;

                if (! isset($groupedMainCriterias[$evalListId])) {
                    $groupedMainCriterias[$evalListId] = [];
                }
                if (! isset($groupedMainCriterias[$evalListId][$mainCriteriaId])) {
                    $groupedMainCriterias[$evalListId][$mainCriteriaId] = [];
                }

                $groupedMainCriterias[$evalListId][$mainCriteriaId][] = $subCriteria;
            }
            $arrScoreEva = [];
            foreach ($groupedMainCriterias as $evalListId => $mainCriterias) {
                foreach ($mainCriterias as $mainCriteriaId => $subCriterias) {
                    $sum_score_Eva = (float) $subCriterias[0]->sum_score;
                    $ratio = (float) $subCriterias[0]->ratio;
                    $SumMaxScoreSub = [];
                    $SumAccScoreSub = [];
                    foreach ($subCriterias as $subCriteria) {
                        $maxScorePerSub = round((float) $subCriteria->num_score, 2);
                        $score = round((float) $subCriteria->score, 2);
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
                'evaluator_id' => $report->evaluator_id,
                'evaluator_name' => $report->evaluator_name,
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

    private function getEvaluationPeriod($startDate, $endDate)
    {
        if ($startDate && $endDate) {
            return Carbon::parse($startDate)->format('M d, Y').' - '.Carbon::parse($endDate)->format('M d, Y');
        }

        return 'All Periods';
    }

    public function show($id)
    {
        $userId = Auth::id();

        if (! $userId) {
            abort(403, 'Unauthorized');
        }

        $currentUser = User::with('department', 'position')->findOrFail($userId);

        $assignment = Assignments::with([
            'assignmentData',
            'report.reportData',
            'report.reportData.criteriaVersion',
            'evaluateeUser.department',
            'evaluateeUser.position',
            'evaluatorUser',
        ])
            ->where('report_id', $id)
            ->firstOrFail();

        $report = $assignment->report;
        $reportData = $report->reportData;

        $statusInfo = $this->getStatusInfo($report->status, $assignment->assignmentData->end_time);

        $assignmentDetails = [
            'assignment_id' => $assignment->assignment_data_id,
            'report_id' => $report->id,
            'report_title' => $reportData->report_title,
            'report_description' => $reportData->report_description ?? '-',
            'comment' => $reportData->comment ?? '-',
            'comment_report' => $report->comment ?? '-',
            'assessment_type' => $reportData->assessment_type,
            'version_name' => optional($reportData->criteriaVersion)->version_name ?? '-',
            'start_date' => $this->formatThaiDate($assignment->assignmentData->start_time),
            'end_date' => $this->formatThaiDate($assignment->assignmentData->end_time),
            'status_text' => $statusInfo['text'],
            'status_class' => $statusInfo['class'],
            'status_color' => $statusInfo['color'],
            'evaluatee' => [
                'id' => optional($assignment->evaluateeUser)->id,
                'name' => optional($assignment->evaluateeUser)->prefix.' '.optional($assignment->evaluateeUser)->name ?? '-',
                'employee_id' => optional($assignment->evaluateeUser)->employee_id,
                'department' => optional(optional($assignment->evaluateeUser)->department)->department_name ?? '-',
                'position' => optional(optional($assignment->evaluateeUser)->position)->name ?? '-',
            ],
            'evaluator' => [
                'name' => optional($assignment->evaluatorUser)->prefix.' '.optional($assignment->evaluatorUser)->name,
                'employee_id' => optional($assignment->evaluatorUser)->employee_id,
            ],
            'dates' => [
                'created_at' => $this->formatThaiDate($report->created_at),
                'updated_at' => $this->formatThaiDate($report->updated_at),
            ],
        ];

        $canEdit = in_array($report->status, ['Assigned', 'Draft']) &&
            now()->lte($assignment->assignmentData->end_time);

        $criteriaVersionId = $reportData->criteria_version_id;

        // Quantity Criteria
        $quantityCriteria = DB::table('quantity_main_criterias as qm')
            ->join('quantity_sub_criterias as qs', 'qm.id', '=', 'qs.quantity_main_criteria_id')
            ->leftJoin('quantity_scores as qscore', function ($join) use ($report) {
                $join->on('qs.id', '=', 'qscore.quantity_sub_criteria_id')
                    ->where('qscore.report_id', '=', $report->id);
            })
            ->leftJoin('evidence_answers as eanswer', function ($join) use ($report) {
                $join->on('qs.id', '=', 'eanswer.evaluation_list_id')
                    ->where('eanswer.report_id', '=', $report->id);
            })
            ->select(
                'qm.id as main_id',
                'qm.name as main_name',
                'qm.tooltips as main_tooltips',
                'qs.id as sub_id',
                'qs.name as sub_name',
                'qs.sequence as sub_sequence',
                'qs.score_a',
                'qs.score_b',
                'qscore.score_C',
                'qscore.score_D',
                'eanswer.link as evidence_link'
            )
            ->orderBy('qm.id')
            ->orderBy('qs.sequence')
            ->get()
            ->groupBy('main_id');

        // Quality Criteria
        $qualityCriteria = DB::table('quality_main_criterias as qm')
            ->join('quality_sub_criterias as qs', 'qm.id', '=', 'qs.quality_main_criteria_id')
            ->leftJoin('quality_scores as qscore', function ($join) use ($report) {
                $join->on('qs.id', '=', 'qscore.quality_sub_criteria_id')
                    ->where('qscore.report_id', '=', $report->id);
            })
            ->leftJoin('evidence_answers as eanswer', function ($join) use ($report) {
                $join->on('qs.evaluation_list_id', '=', 'eanswer.evaluation_list_id')
                    ->where('eanswer.report_id', '=', $report->id);
            })
            ->select(
                'qm.id as main_id',
                'qm.name as main_name',
                'qm.tooltips as main_tooltips',
                'qm.sequence as main_sequence',
                'qm.ratio as main_ratio',
                'qs.id as sub_id',
                'qs.name as sub_name',
                'qs.sequence as sub_sequence',
                'qs.num_score',
                'qscore.score as filled_score',
                'eanswer.link as evidence_link'
            )
            ->where('qs.criteria_version_id', $criteriaVersionId)
            ->orderBy('qm.sequence')
            ->orderBy('qs.sequence')
            ->get()
            ->groupBy('main_id');

        $allMainIds = $quantityCriteria->keys()->merge($qualityCriteria->keys())->unique();

        $mergedCriteria = $allMainIds->mapWithKeys(function ($mainId) use ($quantityCriteria, $qualityCriteria) {
            return [
                $mainId => [
                    'main_id' => $mainId,
                    'quantity' => $quantityCriteria->get($mainId, collect()),
                    'quality' => $qualityCriteria->get($mainId, collect()),
                ],
            ];
        });

        //  โหลด Categories พร้อม EvaluationLists และ SubCriterias + MainCriteria
        $categories = Category::with([
            'evaluationLists' => function ($query) {
                $query->orderBy('sequence')->with([
                    'quantitySubCriterias.mainCriteria:id,name,tooltips',
                    'qualitySubCriterias.mainCriteria:id,name,tooltips,ratio,sequence',
                ]);
            },
        ])
            ->where('criteria_version_id', $criteriaVersionId)
            ->orderBy('sequence')
            ->get()
            ->map(function ($category) {
                $category->sum_score = $category->evaluationLists->sum('sum_score');

                return $category;
            });

        $quantityMap = collect($quantityCriteria)
            ->flatMap(fn ($items) => $items)
            ->keyBy('sub_id');

        $categories->each(function ($category) use ($quantityMap) {
            foreach ($category->evaluationLists as $list) {
                foreach ($list->quantitySubCriterias as $sub) {
                    $data = $quantityMap->get($sub->id);
                    if ($data) {
                        $sub->score_c = $data->score_C;
                        $sub->score_d = $data->score_D;
                        $sub->evidence_link = $data->evidence_link;
                    }
                }
            }
        });
        $qualityMap = collect($qualityCriteria)
            ->flatMap(fn ($items) => $items)
            ->keyBy('sub_id');

        $categories->each(function ($category) use ($qualityMap) {
            foreach ($category->evaluationLists as $list) {
                foreach ($list->qualitySubCriterias as $sub) {
                    $data = $qualityMap->get($sub->id);
                    if ($data) {
                        $sub->filled_score = $data->filled_score;
                        $sub->evidence_link = $data->evidence_link;
                    }
                }
            }
        });

        return view('dashboard.show', [
            'assignment' => $assignmentDetails,
            'canEdit' => $canEdit,
            'currentUser' => $currentUser,
            'mergedCriteria' => $mergedCriteria,
            'categories' => $categories,
        ]);
    }

    private function formatThaiDate($datetime)
    {
        if (! $datetime) {
            return '-';
        }

        $thaiMonths = [
            1 => 'ม.ค.',
            2 => 'ก.พ.',
            3 => 'มี.ค.',
            4 => 'เม.ย.',
            5 => 'พ.ค.',
            6 => 'มิ.ย.',
            7 => 'ก.ค.',
            8 => 'ส.ค.',
            9 => 'ก.ย.',
            10 => 'ต.ค.',
            11 => 'พ.ย.',
            12 => 'ธ.ค.',
        ];

        $dateObj = Carbon::parse($datetime);
        $day = $dateObj->day;
        $month = $thaiMonths[$dateObj->month];
        $year = $dateObj->year + 543;

        return sprintf('%02d/%s/%d', $day, $month, $year);
    }

    private function getStatusInfo($status, $endTime)
    {
        $now = now();
        if (! $endTime) {
            return [
                'text' => 'สถานะไม่ระบุ',
                'class' => 'unknown',
                'color' => '#6c757d',
            ];
        }

        $endDate = Carbon::parse($endTime);
        switch ($status) {
            case 'Assigned':
                return [
                    'text' => 'ยังไม่ประเมิน (มอบหมายแล้ว)',
                    'class' => 'Assigned',
                    'color' => '#FF0000',
                ];

            case 'draft':
                return [
                    'text' => 'บันทึกแล้ว (รออนุมัติ)',
                    'class' => 'draft',
                    'color' => '#ffc107',
                ];

            case 'Pending':
                return [
                    'text' => 'รอผลประเมิน (รอกดอนุมัติ)',
                    'class' => 'Pending',
                    'color' => '#17a2b8',
                ];

            case 'Completed':
                return [
                    'text' => 'ประเมินเสร็จสิ้น (อนุมัติแล้ว)',
                    'class' => 'Completed',
                    'color' => '#28a745',
                ];

            default:
                return [
                    'text' => 'ไม่ทราบสถานะ',
                    'class' => 'unknown',
                    'color' => '#6c757d',
                ];
        }
    }
}
