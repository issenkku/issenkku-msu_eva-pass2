<?php

namespace App\Http\Controllers;

use App\Models\Assignments;
use App\Models\Category;
use App\Models\Reports;
use App\Models\User;
use App\Services\ReportDataService;
use App\Support\AdminDashboardQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function index(Request $request, AdminDashboardQuery $dashboardQuery)
    {
        $viewData = $dashboardQuery->handle($request)->toViewData();

        if ($request->header('X-Dashboard-Fragment') === 'evaluation-list') {
            return response()
                ->view('dashboard.partials.index-evaluation-list', $viewData)
                ->header('X-Dashboard-Fragment', 'evaluation-list');
        }

        return view('dashboard.index', $viewData);
    }

    public function admin(Request $request, $id)
    {
        $user = $request->user()->load('position', 'department');

        $data = $this->reportDataService->getReportData($id);
        $report = $data['report'];

        //     'Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft'])) {

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
        if ($assignment->assignmentData && $assignment->assignmentData->evaluator_id) {
            $evaluatorUser = User::find($assignment->assignmentData->evaluator_id);
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
                'qm.allow_multiple as allow_multiple',
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
                    'qualitySubCriterias.mainCriteria:id,name,tooltips,ratio,sequence,allow_multiple',
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
