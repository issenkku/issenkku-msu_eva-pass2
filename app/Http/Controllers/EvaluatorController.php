<?php

namespace App\Http\Controllers;

use App\Models\Assignments;
use App\Models\Category;
use App\Models\QualityScore;
use App\Models\QualitySubCriteria;
use App\Models\User;
use App\Models\Reports;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Debugbar; // Assuming you have installed Laravel Debugbar for debugging

class EvaluatorController extends Controller
{
    /**
     * Display evaluator dashboard - หน้าหลักของผู้ประเมิน
     */
    public function dashboard(Request $request)
    {
        // Load user with all necessary relationships
        $user = $request->user()->load([
            'position',
            'department',
        ]);

        // Only get assignments where evaluatee is from the same department
        $sameDepartmentAssignments = $user->evaluatorAssignments()
            ->whereHas('evaluateeUser', function ($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->with([
                'evaluateeUser.department',
                'assignmentData',
                'report.reportData',
            ])
            ->get();

        $evaluations = $sameDepartmentAssignments->map(function ($assignment) use ($user) {
            $assignment->evaluatorName = $user->name; // current user
            $assignment->evaluateeName = $assignment->evaluateeUser?->name ?? '-';
            $assignment->sameDepartment = true; // Always true since we filtered at database level
            
            // Optional: Add department names for display
            $assignment->evaluateeDepartment = $assignment->evaluateeUser?->department?->name ?? '-';
            $assignment->evaluatorDepartment = $user->department?->name ?? '-';
            
            return $assignment;
        });

        // Count status for filtered assignments only (same department only)
        $statusCounts = [
            'ทั้งหมด' => $evaluations->count(),
            'รอการกรอกข้อมูล' => $this->countByStatus($evaluations, ['Assigned', 'Draft']),
            'ยังไม่ประเมิน' => $this->countByStatus($evaluations, ['Pending']),
            'กำลังดำเนินการ' => $this->countByStatus($evaluations, ['Evaluator_draft']),
            'รอผลการประเมิน' => $this->countByStatus($evaluations, [
                'Director_assigned', 'Director_draft', 
                'Manager_draft', 'Manager_draft'
            ]),
            'ประเมินเสร็จสิ้น' => $this->countByStatus($evaluations, ['Completed']),
        ];

        return view('evaluator_dashboard.index', [
            'user' => $user,
            'statusCounts' => $statusCounts,
            'evaluations' => $evaluations, // Only same-department evaluations
        ]);
    }

    private function countByStatus($evaluations, $statuses)
    {
        return $evaluations->filter(function($assignment) use ($statuses) {
            $reportStatus = optional($assignment->report)->status ?? 'Assigned';
            return in_array($reportStatus, $statuses);
        })->count();
    }

    public function show(Request $request, $assignmentId)
    {
        $userId = Auth::id();

        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        $currentUser = User::with('department', 'position')->findOrFail($userId);

        $assignment = Assignments::with([
            'assignmentData',
            'report.reportData',
            'report.reportData.criteriaVersion',
            'evaluateeUser.department',
            'evaluateeUser.position',
            'evaluatorUser'
        ])
            ->where('report_id', $assignmentId)
            ->where('evaluator', $userId)
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
                'name' => optional($assignment->evaluateeUser)->prefix . ' ' . optional($assignment->evaluateeUser)->name ?? '-',
                'employee_id' => optional($assignment->evaluateeUser)->employee_id,
                'department' => optional(optional($assignment->evaluateeUser)->department)->department_name ?? '-',
                'position' => optional(optional($assignment->evaluateeUser)->position)->name ?? '-',
            ],
            'evaluator' => [
                'name' => optional($assignment->evaluatorUser)->prefix . ' ' . optional($assignment->evaluatorUser)->name,
                'employee_id' => optional($assignment->evaluatorUser)->employee_id,
            ],
            'dates' => [
                'created_at' => $this->formatThaiDate($report->created_at),
                'updated_at' => $this->formatThaiDate($report->updated_at),
            ]
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
                ]
            ];
        });

        //  โหลด Categories พร้อม EvaluationLists และ SubCriterias + MainCriteria
        $categories = Category::with([
            'evaluationLists' => function ($query) {
                $query->orderBy('sequence')->with([
                    'quantitySubCriterias.mainCriteria:id,name,tooltips',
                    'qualitySubCriterias.mainCriteria:id,name,tooltips,ratio,sequence',
                ]);
            }
        ])
            ->where('criteria_version_id', $criteriaVersionId)
            ->orderBy('sequence')
            ->get()
            ->map(function ($category) {
                $category->sum_score = $category->evaluationLists->sum('sum_score');
                return $category;
            });

        $quantityMap = collect($quantityCriteria)
            ->flatMap(fn($items) => $items)
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
            ->flatMap(fn($items) => $items)
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

        return view('evaluator_dashboard.evaluatee_show', [
            'assignment' => $assignmentDetails,
            'canEdit' => $canEdit,
            'currentUser' => $currentUser,
            'mergedCriteria' => $mergedCriteria,
            'categories' => $categories,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $userId = Auth::id() ?? 2;

        $user = $request->user()->load('position', 'department');

        $report = Reports::with([
            'reportData.criteriaVersion.quantityMainCriterias.quantitySubCriterias',
            'assignments.assignmentData',
            'assignments.evaluateeUser.department',
            'assignments.evaluateeUser.position',
            'assignments.evaluatorUser',
        ])->findOrFail($id);

        // Find the assignment for the current evaluator
        $assignment = Assignments::with([
                'assignmentData',
                'evaluateeUser.department',
                'evaluateeUser.position',
            ])
            ->where('report_id', $id)
            ->whereHas('assignmentData', function ($q) use ($user) {
                $q->where('evaluator_position_id', $user->position_id);
            })
            ->whereHas('evaluateeUser', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })
            ->first();

        if (!$assignment) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงรายงานนี้');
        }

        // Add evaluatee info like in dashboard
        $assignment->evaluateeName = $assignment->evaluateeUser?->name ?? '-';
        $assignment->evaluateeDepartment = $assignment->evaluateeUser?->department?->name ?? '-';
        $assignment->evaluateePosition = $assignment->evaluateeUser?->position?->name ?? '-';
        $assignment->evaluatorName = $assignment->evaluatorUser?->name ?? '-';

        $criteriaVersionId = $assignment->report->reportData->criteria_version_id ?? null;

        // โหลดคะแนนของ quantity_sub_criterias
        $quantityScores = DB::table('quantity_scores')
            ->where('report_id', $assignment->report_id)
            ->get()
            ->keyBy('quantity_sub_criteria_id');

        // โหลดคะแนนของ quality_sub_criterias
        $filledScores = QualityScore::where('report_id', $assignment->report_id)
            ->pluck('score', 'quality_sub_criteria_id')
            ->toArray();

        // โหลด evidence ของแต่ละ evaluation_list_id
        $evidenceAnswers = DB::table('evidence_answers')
            ->where('report_id', $assignment->report_id)
            ->get()
            ->groupBy('evaluation_list_id');

        $categories = collect();

        if ($criteriaVersionId) {
            $categories = Category::with([
                'evaluationLists' => function ($query) {
                    $query->orderBy('sequence')->with([
                        'quantitySubCriterias.mainCriteria:id,name,tooltips',
                        'qualitySubCriterias.mainCriteria:id,name,tooltips,ratio,sequence',
                    ]);
                }
            ])
                ->where('criteria_version_id', $criteriaVersionId)
                ->orderBy('sequence')
                ->get();
        }

        

        // ผูกคะแนนและ evidence เข้า quantitySubCriterias และ qualitySubCriterias
        foreach ($categories as $category) {
            foreach ($category->evaluationLists as $list) {
                // Quantity
                foreach ($list->quantitySubCriterias as $criteria) {
                    $score = $quantityScores->get($criteria->id);
                    if ($score) {
                        $criteria->score_c = $score->score_C;
                        $criteria->score_d = $score->score_D;
                    }

                    // ดึง evidence link
                    $evidences = $evidenceAnswers->get($criteria->evaluation_list_id);
                    $criteria->evidence_links = $evidences ? $evidences->pluck('link')->all() : [];
                }

                // Quality
                foreach ($list->qualitySubCriterias as $criteria) {
                    // คะแนนคุณภาพ
                    $criteria->filled_score = $filledScores[$criteria->id] ?? null;

                    // ดึง evidence link
                    $evidences = $evidenceAnswers->get($criteria->evaluation_list_id);
                    $criteria->evidence_links = $evidences ? $evidences->pluck('link')->all() : [];
                }
            }
        }

        return view('evaluator_dashboard.evaluatee_form', compact('assignment', 'categories'));
    }

    public function update(Request $request, $id)
    {
        \Log::info('Update evaluation scores for report ID: ' . $id);
        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'required|numeric|min:0', 
            'comment' => 'nullable|string|max:2000',
        ]);

        foreach ($validated['scores'] as $criteriaId => $score) {
            if (empty($criteriaId) || $criteriaId == 0) continue;

            QualityScore::updateOrCreate(
                [
                    'quality_sub_criteria_id' => $criteriaId,
                    'report_id' => $id,
                ],
                [
                    'score' => $score,
                ]
            );
        }

        Reports::where('id', $id)->update([
            'comment' => $request->input('comment'),
            'status' => $request->has('change_status') ? 'Director_assigned' : DB::raw('status'),
        ]);

        // ส่งอีเมลแจ้งเตือนเมื่อประเมินเสร็จ
        if ($request->has('change_status')) {
            $this->sendEvaluationCompletedMail($id);
        }

        return redirect()->route('evaluator.index')->with('success', 'บันทึกคะแนนเรียบร้อยแล้ว');
    }

    public function reject($reportId)
    {
        $report = Reports::findOrFail($reportId);
        $report->status = 'Assigned'; // หรือ status ที่คุณต้องการ
        $report->save();

        return redirect()->route('evaluator.index')->with('success', 'ไม่อนุมัติแบบประเมินเรียบร้อยแล้ว');
    }

    private function formatThaiDate($datetime)
    {
        if (!$datetime) return '-';

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
            12 => 'ธ.ค.'
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
        if (!$endTime) {
            return [
                'text' => 'สถานะไม่ระบุ',
                'class' => 'unknown',
                'color' => '#6c757d'
            ];
        }

        $endDate = Carbon::parse($endTime);
        switch ($status) {
            case 'Assigned':
                return [
                    'text' => 'ยังไม่ประเมิน (มอบหมายแล้ว)',
                    'class' => 'Assigned',
                    'color' => '#FF0000'
                ];

            case 'draft':
                return [
                    'text' => 'บันทึกแล้ว (รออนุมัติ)',
                    'class' => 'draft',
                    'color' => '#ffc107'
                ];

            case 'Pending':
                return [
                    'text' => 'รอผลประเมิน (รอกดอนุมัติ)',
                    'class' => 'Pending',
                    'color' => '#17a2b8'
                ];

            case 'Completed':
                return [
                    'text' => 'ประเมินเสร็จสิ้น (อนุมัติแล้ว)',
                    'class' => 'Completed',
                    'color' => '#28a745'
                ];

            default:
                return [
                    'text' => 'ไม่ทราบสถานะ',
                    'class' => 'unknown',
                    'color' => '#6c757d'
                ];
        }
    }

    private function calculateExperience($createdAt)
    {
        $years = now()->diffInYears($createdAt);
        return $years > 0 ? $years : 1;
    }

    private function getAverageEvaluationScore($userId)
    {
        // สมมติให้เป็น 4.5 เป็นค่า default
        return 4.5;
    }

    // อีเมลแจ้งเตือนเมื่อประเมินเสร็จ
    private function sendEvaluationCompletedMail($reportId)
    {
        $report = \App\Models\Reports::with(['reportData', 'reportData.criteriaVersion'])->find($reportId);
        if (!$report) return;

        // สมมติว่าต้องการแจ้งเตือน evaluatee (ผู้ถูกประเมิน)
        $assignment = \App\Models\Assignments::where('report_id', $reportId)->first();
        if (!$assignment) return;
        $user = \App\Models\User::find($assignment->evaluatee);
        if (!$user || !$user->email) return;

        $mailData = [
            'name' => $user->name,
            'report_title' => optional($report->reportData)->report_title,
            'version_name' => optional(optional($report->reportData)->criteriaVersion)->version_name,
            'status' => $report->status,
        ];

        \Mail::send('emails.evaluation_completed', $mailData, function($message) use ($user) {
            $message->to($user->email, $user->name)
                ->subject('แจ้งเตือน: ผลการประเมินของคุณเสร็จสมบูรณ์');
        });
    }
}