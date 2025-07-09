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

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load([
            'position',
            'department',
            'assignment.assignmentData', // Load nested relationships
            'assignment.report.reportData',
            'assignment.evaluatorUser', // Load evaluator user relationship
        ]);

        $evaluations = $user->assignment->pluck('report')->filter();

        $statusCounts = [
            'ทั้งหมด' => $evaluations->count(),
            'ยังไม่ประเมิน' => $evaluations->where('status', 'Assigned')->count(),
            'กำลังดำเนินการ' => $evaluations->where('status', 'Draft')->count(),
            'รอผลการประเมิน' => $evaluations->where('status', 'Pending')->count(),
            'ประเมินเสร็จสิ้น' => $evaluations->where('status', 'Completed')->count(),
        ];

        return view('evaluatee.dashboard', [
            'user' => $user,
            'statusCounts' => $statusCounts,
            'evaluations' => $user->assignment,
        ]);
    }

    public function evaluation(Request $request, $id)
    {
        $user = $request->user()->load('position', 'department');

        $report = Reports::with([
            'reportData.criteriaVersion.quantityMainCriterias.quantitySubCriterias',
            'assignments.evaluatorUser',
            'assignments.assignmentData',
        ])->findOrFail($id);
        
        // Find the assignment for the current user
        $assignment = $report->assignments->where('evaluatee', $user->id)->first();

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

        $evaluatorName = $assignment && $assignment->evaluatorUser ? $assignment->evaluatorUser->name : 'ไม่พบข้อมูล';
        $startTime = $assignment && $assignment->assignmentData ? $assignment->assignmentData->start_time : null;
        $endTime = $assignment && $assignment->assignmentData ? $assignment->assignmentData->end_time : null;
        $reportName = $assignment && $assignment->report->reportData ? $assignment->report->reportData->report_title : 'ไม่พบชื่อรายงาน';
        $assessmentType = $assignment && $assignment->report->reportData ? $assignment->report->reportData->assessment_type : 'ไม่พบชื่อรายงาน';
        $startTimeFormatted = $startTime ? $formatThai($startTime) : '-';
        $endTimeFormatted = $endTime ? $formatThai($endTime) : '-';

        $criteriaVersion = $assignment->report->reportData->criteriaVersion ?? null;
        $quantityMainCriterias = $criteriaVersion ? $criteriaVersion->quantityMainCriterias : collect();

        $quantityScores = QuantityScore::where('report_id', $id)
        ->get()
        ->keyBy('quantity_sub_criteria_id');

        $evidenceAnswers = EvidenceAnswer::where('report_id', $id)
        ->get()
        ->keyBy('evaluation_list_id');

        $evidenceMap = $evidenceAnswers->mapWithKeys(function ($item) {
            return [$item->evaluation_list_id => $item->link];
        });

        $readonly = $request->boolean('readonly');

        $evaluationItems = [];

        if ($report && $report->reportData && $report->reportData->criteriaVersion) {
            $evaluationLists = $report->reportData->criteriaVersion->evaluationLists;
            $firstList = $evaluationLists->first();

            if ($firstList) {
                $evaluationItems[] = [
                    'title' => $firstList->name,
                    'subtitle' => $firstList->annotation, 
                    'is_main' => true,
                    'is_evaluation_list' => true,
                    'evaluation_list_id' => $firstList->id,
                ];

                $mainCriteriaIds = $firstList->quantitySubCriterias->pluck('quantity_main_criteria_id')->unique();
                
                foreach ($mainCriteriaIds as $mainCriteriaId) {
                    $mainCriteria = QuantityMainCriteria::find($mainCriteriaId);
                    
                    if ($mainCriteria) {
                        $evaluationItems[] = [
                            'title' => $mainCriteria->name,
                            'subtitle' => $mainCriteria->tooltips,
                            'is_main' => true,
                            'is_evaluation_list' => false,
                            'main_criteria_id' => $mainCriteria->id,
                            'evaluation_list_id' => $firstList->id,
                        ];

                        $subCriterias = $firstList->quantitySubCriterias->where('quantity_main_criteria_id', $mainCriteriaId);
                        
                        foreach ($subCriterias as $subCriteria) {
                            $quantityScore = $quantityScores[$subCriteria->id] ?? null;
                            $evidenceLink = $evidenceAnswers[$firstList->id]->link ?? '';

                            $evaluationItems[] = [
                                'title' => $subCriteria->name,
                                'subtitle' => null,
                                'is_main' => false,
                                'is_evaluation_list' => false,
                                'sequence' => $subCriteria->sequence,
                                'sub_criteria_id' => $subCriteria->id,
                                'main_criteria_id' => $mainCriteria->id,
                                'evaluation_list_id' => $firstList->id,
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
        }

        $qualityScores = QualityScore::where('report_id', $id)
            ->get()
            ->keyBy('quality_sub_criteria_id');

        $qualityItems = [];

        if ($report && $report->reportData && $report->reportData->criteriaVersion) {
            $evaluationLists = $report->reportData->criteriaVersion->evaluationLists;
            
            foreach ($evaluationLists->skip(1) as $lists) {
                $qualityItems[] = [
                    'title' => $lists->name,
                    'subtitle' => $lists->annotation, 
                    'is_main' => true,
                    'is_evaluation_list' => true,
                    'evaluation_list_id' => $lists->id,
                ];

                $mainCriteriaIds = $lists->qualitySubCriterias->pluck('quality_main_criteria_id')->unique();
                
                foreach ($mainCriteriaIds as $mainCriteriaId) {
                    $mainCriteria = QualityMainCriteria::find($mainCriteriaId);
                    
                    if ($mainCriteria) {
                        $qualityItems[] = [
                            'title' => $mainCriteria->name,
                            'subtitle' => $mainCriteria->tooltips,
                            'is_main' => true,
                            'is_evaluation_list' => false,
                            'main_criteria_id' => $mainCriteria->id,
                            'evaluation_list_id' => $lists->id,
                        ];

                        $subCriterias = $lists->qualitySubCriterias->where('quality_main_criteria_id', $mainCriteriaId);
                        
                        foreach ($subCriterias as $subCriteria) {
                            $qualityScore = $qualityScores[$subCriteria->id] ?? null;
                            $evidenceLink = $evidenceAnswers[$lists->id]->link ?? '';
                            
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
                                'evaluation_list_id' => $lists->id,
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
        
        return view('evaluatee.evaluation', compact(
            'id', 'user', 'report', 'assignment', 'formatThai',
            'evaluatorName', 'startTime', 'endTime', 'reportName',
            'startTimeFormatted', 'endTimeFormatted', 'assessmentType',
            'quantityMainCriterias', 'evaluationItems', 'qualityItems', 'evidenceMap',
            'readonly'
        ));
    }
}