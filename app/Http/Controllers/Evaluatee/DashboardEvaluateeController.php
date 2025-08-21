<?php

namespace App\Http\Controllers\Evaluatee;

use App\Models\Assignments;
use App\Models\EvidenceAnswer;
use App\Models\QualityMainCriteria;
use App\Models\QualityScore;
use App\Models\QuantityMainCriteria;
use App\Models\QuantityScore;
use App\Models\Reports;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

class DashboardEvaluateeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load([
            'position',
            'department',
            'assignment.assignmentData', // Load nested relationships
            'assignment.report.reportData',
        ]);

        // $evaluations = $user->assignment->pluck('report')->filter();

        $evaluations = $user->assignment->map(function ($assignment) {
            $assignment->evaluatorUser = $assignment->evaluatorUser(); 
            return $assignment;
        });

        $statusCounts = [
            'ทั้งหมด' => $evaluations->count(),
            'ยังไม่ประเมิน' => $this->countByStatus($evaluations, ['Assigned']),
            'กำลังดำเนินการ' => $this->countByStatus($evaluations, ['Draft']),
            'รอผลการประเมิน' => $this->countByStatus($evaluations, [
                'Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft', 
                'Manager_assign', 'Manager_draft'
            ]),
            'ประเมินเสร็จสิ้น' => $this->countByStatus($evaluations, ['Completed']),
        ];

        return view('evaluatee.dashboard', [
            'user' => $user,
            'statusCounts' => $statusCounts,
            'evaluations' => $user->assignment,
        ]);
    }

    private function countByStatus($evaluations, $statuses)
    {
        return $evaluations->filter(function($assignment) use ($statuses) {
            $reportStatus = optional($assignment->report)->status ?? 'Assigned';
            return in_array($reportStatus, $statuses);
        })->count();
    }

    public function evaluation(Request $request, $id)
    {
        $user = $request->user()->load('position', 'department');

        $report = Reports::with([
            'reportData.criteriaVersion.quantityMainCriterias.quantitySubCriterias',
            'reportData.criteriaVersion.qualityMainCriterias.qualitySubCriterias',
            'reportData.criteriaVersion.evaluationLists.quantitySubCriterias',
            'reportData.criteriaVersion.evaluationLists.qualitySubCriterias',
            'assignments.assignmentData',
        ])->findOrFail($id);
        
        // Find the assignment for the current user
        $assignment = $report->assignments->where('evaluatee', $user->id)->first();
        $evaluator = $assignment->getEvaluatorUser();

        if (!$assignment) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงรายงานนี้');
        }

        $formatThai = function($datetime) {
            if (!$datetime) return '-';
            \Carbon\Carbon::setLocale('th');
            setlocale(LC_TIME, 'th_TH.UTF-8');
            $date = \Carbon\Carbon::parse($datetime);
            $year = $date->year + 543;
            return $date->translatedFormat('j F') . " {$year}";
        };

        $startTime = $assignment && $assignment->assignmentData ? $assignment->assignmentData->start_time : null;
        $endTime = $assignment && $assignment->assignmentData ? $assignment->assignmentData->end_time : null;
        $reportName = $assignment && $assignment->report->reportData ? $assignment->report->reportData->report_title : 'ไม่พบชื่อรายงาน';
        $versionName = $assignment && $assignment->report->reportData->criteriaVersion ? $assignment->report->reportData->criteriaVersion->version_name : 'ไม่พบชื่อรายงาน';
        $reportComment = $assignment && $assignment->report->reportData ? $assignment->report->reportData->comment : null;
        $reportDescription = $assignment && $assignment->report->reportData ? $assignment->report->reportData->report_description : null;
        $assessmentType = $assignment && $assignment->report->reportData ? $assignment->report->reportData->assessment_type : 'ไม่พบชื่อรายงาน';
        $startTimeFormatted = $startTime ? $formatThai($startTime) : '-';
        $endTimeFormatted = $endTime ? $formatThai($endTime) : '-';
        $reportComment = $assignment && $assignment->report->reportData ? $assignment->report->reportData->comment : '-';

        $criteriaVersion = $assignment->report->reportData->criteriaVersion ?? null;
        $quantityMainCriterias = $criteriaVersion ? $criteriaVersion->quantityMainCriterias : collect();

        $quantityScores = QuantityScore::where('report_id', $id)
            ->get()
            ->keyBy('quantity_sub_criteria_id');

        $qualityScores = QualityScore::where('report_id', $id)
            ->get()
            ->keyBy('quality_sub_criteria_id');

        $evidenceAnswers = EvidenceAnswer::where('report_id', $id)
            ->get()
            ->keyBy('evaluation_list_id');

        $evidenceMap = $evidenceAnswers->mapWithKeys(function ($item) {
            return [$item->evaluation_list_id => $item->link];
        });

        $readonly = $request->boolean('readonly');

        // DEBUG: Let's see the structure
        if ($report && $report->reportData && $report->reportData->criteriaVersion) {
            $evaluationLists = $report->reportData->criteriaVersion->evaluationLists;
            
            // Debug output - you can remove this after checking
            // dd([
            //     'evaluation_lists_count' => $evaluationLists->count(),
            //     'evaluation_lists_structure' => $evaluationLists->map(function($list) {
            //         return [
            //             'id' => $list->id,
            //             'name' => $list->name,
            //             'annotation' => $list->annotation,
            //             'quantity_sub_criterias_count' => $list->quantitySubCriterias ? $list->quantitySubCriterias->count() : 0,
            //             'quality_sub_criterias_count' => $list->qualitySubCriterias ? $list->qualitySubCriterias->count() : 0,
            //             'quantity_sub_criterias' => $list->quantitySubCriterias ? $list->quantitySubCriterias->pluck('name', 'id') : [],
            //             'quality_sub_criterias' => $list->qualitySubCriterias ? $list->qualitySubCriterias->pluck('name', 'id') : [],
            //         ];
            //     }),
            // ]);
        }

        // FIXED: Process all evaluation lists properly
        $evaluationItems = [];
        $qualityItems = [];

        if ($report && $report->reportData && $report->reportData->criteriaVersion) {
            $evaluationLists = $report->reportData->criteriaVersion->evaluationLists;
            
            foreach ($evaluationLists as $list) {
                // Check if this evaluation list has quantity criteria
                if ($list->quantitySubCriterias && $list->quantitySubCriterias->count() > 0) {
                    $evaluationItems[] = [
                        'title' => $list->name,
                        'subtitle' => $list->annotation, 
                        'is_main' => true,
                        'is_evaluation_list' => true,
                        'evaluation_list_id' => $list->id,
                    ];

                    $mainCriteriaIds = $list->quantitySubCriterias->pluck('quantity_main_criteria_id')->unique();
                    
                    foreach ($mainCriteriaIds as $mainCriteriaId) {
                        $mainCriteria = QuantityMainCriteria::find($mainCriteriaId);
                        
                        if ($mainCriteria) {
                            $evaluationItems[] = [
                                'title' => $mainCriteria->name,
                                'subtitle' => $mainCriteria->tooltips,
                                'is_main' => true,
                                'is_evaluation_list' => false,
                                'main_criteria_id' => $mainCriteria->id,
                                'evaluation_list_id' => $list->id,
                            ];

                            $subCriterias = $list->quantitySubCriterias->where('quantity_main_criteria_id', $mainCriteriaId);
                            
                            foreach ($subCriterias as $subCriteria) {
                                $quantityScore = $quantityScores[$subCriteria->id] ?? null;
                                $evidenceLink = $evidenceAnswers[$list->id]->link ?? '';

                                $evaluationItems[] = [
                                    'title' => $subCriteria->name,
                                    'subtitle' => null,
                                    'is_main' => false,
                                    'is_evaluation_list' => false,
                                    'sequence' => $subCriteria->sequence,
                                    'sub_criteria_id' => $subCriteria->id,
                                    'main_criteria_id' => $mainCriteria->id,
                                    'evaluation_list_id' => $list->id,
                                    'score_a' => $subCriteria->score_a,
                                    'score_b' => $subCriteria->score_b,
                                    'tor_compliant' => $quantityScore?->score_C ?? '', 
                                    'user_score' => '',
                                    'evidence' => $evidenceLink, 
                                ];
                            }
                        }
                    }
                }
                
                // Check if this evaluation list has quality criteria
                if ($list->qualitySubCriterias && $list->qualitySubCriterias->count() > 0) {
                    $qualityItems[] = [
                        'title' => $list->name,
                        'subtitle' => $list->annotation, 
                        'is_main' => true,
                        'is_evaluation_list' => true,
                        'evaluation_list_id' => $list->id,
                    ];

                    $mainCriteriaIds = $list->qualitySubCriterias->pluck('quality_main_criteria_id')->unique();
                    
                    foreach ($mainCriteriaIds as $mainCriteriaId) {
                        $mainCriteria = QualityMainCriteria::find($mainCriteriaId);
                        
                        if ($mainCriteria) {
                            $qualityItems[] = [
                                'title' => $mainCriteria->name,
                                'subtitle' => $mainCriteria->tooltips,
                                'is_main' => true,
                                'is_evaluation_list' => false,
                                'main_criteria_id' => $mainCriteria->id,
                                'evaluation_list_id' => $list->id,
                            ];

                            $subCriterias = $list->qualitySubCriterias->where('quality_main_criteria_id', $mainCriteriaId);
                            
                            foreach ($subCriterias as $subCriteria) {
                                $qualityScore = $qualityScores[$subCriteria->id] ?? null;
                                $evidenceLink = $evidenceAnswers[$list->id]->link ?? '';
                                
                                $hasScore = $qualityScore && $qualityScore->score !== null && $qualityScore->score !== '';
                                $userSelected = $hasScore || ($qualityScore && $qualityScore->score !== null);

                                $qualityItems[] = [
                                    'title' => $subCriteria->name,
                                    'subtitle' => null,
                                    'is_main' => false,
                                    'is_evaluation_list' => false,
                                    'sequence' => $subCriteria->sequence,
                                    'sub_criteria_id' => $subCriteria->id,
                                    'main_criteria_id' => $mainCriteria->id,
                                    'evaluation_list_id' => $list->id,
                                    'num_score' => $subCriteria->num_score,
                                    'user_selected' => $userSelected,
                                    'score' => $qualityScore?->score ?? '',
                                    'evidence' => $evidenceLink, 
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        return view('evaluatee.evaluation', compact(
            'id', 'user', 'report', 'assignment', 'formatThai',
            'startTime', 'endTime', 'reportName', 'evaluator',
            'startTimeFormatted', 'endTimeFormatted', 'assessmentType',
            'quantityMainCriterias', 'evaluationItems', 'qualityItems', 'evidenceMap',
            'readonly', 'versionName', 'reportComment', 'reportDescription'
        ));
    }
}