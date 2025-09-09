<?php

namespace App\Http\Controllers;

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\Category;
use App\Models\Department;
use App\Models\QuantityScore;
use App\Models\Reports;
use App\Models\User;
use App\Services\GraphDataService;
use App\Services\ScoreService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\EvaluationService;
use App\Services\ReportDataService;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    protected $allowedEditStatuses = [];

    protected $reportDataService;

    public function __construct(ReportDataService $reportDataService)
    {
        $this->reportDataService = $reportDataService;
    }

    protected function checkReportEditableStatus(Reports $report, $action)
    {
        if (! in_array($report->status, $this->allowedEditStatuses)) {
            return response()->json([
                'message' => "Cannot {$action}.",
            ], 403);
        }

        return null; // ถ้าผ่านการตรวจสอบ
    }
    private function countByStatus($evaluations, $statuses)
    {
        return $evaluations->filter(function ($assignment) use ($statuses) {
            $reportStatus = optional($assignment->report)->status ?? 'Assigned';

            return in_array($reportStatus, $statuses);
        })->count();
    }

    public function index(Request $request, EvaluationService $evaluationService)
    {
        // Get filter parameters
        $startDate = $request->input('start_time');
        $endDate = $request->input('end_time');

        // Fetch all departments for the filter dropdown
        $filters = $request->only(['search', 'year', 'start_time', 'end_time', 'department_name']);
        $departments = Department::all();

        // If no filters are provided, don't set default dates to ensure all data is fetched
        // $latestPeriod = AssignmentData::latest('end_time')->first();
        // if (!$startDate && !$endDate && $latestPeriod) {
        //     $startDate = $latestPeriod->start_time;
        //     $endDate = $latestPeriod->end_time;
        // }

        // Base query for reports
        $allReportsData = $evaluationService->getAllReportsWithAssignments();
        $evaluations = $evaluationService->mapAssignments($allReportsData);
        $evaluations = $evaluationService->filterEvaluations($evaluations, $filters);
        $evaluations = $evaluationService->sortEvaluations($evaluations);

        $statusCounts = [
            'ทั้งหมด' => $evaluations->count(),
            'มอบหมาย' => $this->countByStatus($evaluations, ['Assigned']),
            'เริ่มกรอกข้อมูล' => $this->countByStatus($evaluations, ['Draft']),
            'กำลังดำเนินการ' => $this->countByStatus($evaluations, 
            ['Pending','Evaluator_draft','Director_assigned','Director_draft', 'Manager_draft', 'Manager_assign']),
            'ประเมินเสร็จสิ้น' => $this->countByStatus($evaluations, ['Completed']),
        ];

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

        // Get reports with scores
        $reportsWithScores = $this->reportsWithScores($evaluations);

        // Evaluation period for display
        $evaluationPeriod = $this->getEvaluationPeriod($startDate, $endDate);

        $page = $request->input('page', 1);
        $perPage = 10;
        $paginatedEvaluations = new LengthAwarePaginator(
            $evaluations->forPage($page, $perPage),
            $evaluations->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('dashboard.index', [
            'averageScore' => $averageScore,
            'statusCounts' => $statusCounts,
            'evaluations' => $paginatedEvaluations,
            'scatterData' => $scatterData,
            'chartData' => $chartData,
            'totalEvaluations' => $totalEvaluations,
            'totalEvaluatees' => $totalEvaluatees,
            'statusLabels' => $statusLabels,
            'statusColors' => $statusColors,
            'departments' => $departments,
            // 'reports' => $reportsQuery->get(),
            'reports' => $reportsWithScores,
            'evaluationPeriod' => $evaluationPeriod,
            'years' => $evaluations->pluck('assignmentData.start_time')->map(fn($d) => Carbon::parse($d)->year)->unique()->sortDesc(),
        ]);
    }

    public function admin(Request $request, $id)
    {
        $user = $request->user()->load('position', 'department');

        $data = $this->reportDataService->getReportData($id);
        $report = $data['report'];

        // if (in_array($report->status, ['Assigned', 'Draft',
        //     'Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft'])) {
        //     abort(403, 'ไม่สามารถเข้าถึงหน้าประเมินนี้ได้ เนื่องจากสถานะไม่อนุญาต');
        // }

        $canEdit = in_array($report->status, []);
        $readonly = ! $canEdit; // true if status is something else

        if ($readonly && $request->query('readonly') != 1) {
            return redirect()->route('admin.show', ['id' => $id, 'readonly' => 1]);
        }

        return view('dashboard.admin', array_merge($data, [
            'id' => $id,
            'user' => $user,
            'readonly' => $readonly,
        ]));
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
        ])
            ->where('report_id', $id)
            ->firstOrFail();

        // Try to get evaluator user separately to avoid relationship issues
        $evaluatorUser = null;
        if ($assignment->assignmentData && $assignment->assignmentData->evaluator_position_id) {
            $evaluatorUser = User::where('position_id', $assignment->assignmentData->evaluator_position_id)
                ->first();
        }

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
                'name' => $evaluatorUser ? ($evaluatorUser->prefix.' '.$evaluatorUser->name) : '-',
                'employee_id' => $evaluatorUser ? $evaluatorUser->employee_id : '-',
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
