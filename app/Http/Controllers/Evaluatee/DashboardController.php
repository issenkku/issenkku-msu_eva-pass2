<?php

namespace App\Http\Controllers\Evaluatee;

use App\Models\Assignments;
use App\Models\Report;
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

        $report = Report::with([
            'reportData.criteriaVersion.quantityMainCriterias.quantitySubCriterias',
            'assignments.evaluatorUser',
            'assignments.assignmentData',
        ])->findOrFail($id);
        
        $assignment = $report->assignments;

        if (!$assignment || $assignment->evaluatee !== $user->id) {
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

        $criteriaVersion = $assignment->$report->reportData->criteriaVersion ?? null;
        $quantityMainCriterias = $criteriaVersion ? $criteriaVersion->quantityMainCriterias : collect();

        // dd([
        //     'report_id' => $report->id,
        //     'report_data' => $report->reportData ? 'exists' : 'null',
        //     'criteria_version' => $report ? $report->reportData->criteriaVersion->version_name : 'null',
        //     'evaluationLists' => $report ? $report->reportData->criteriaVersion->evaluationLists->map(function($main){
        //         return [
        //             'id' => $main->id,
        //             'name' => $main->name,
        //             'qualitySubCriterias' => $main->qualitySubCriterias->count(),
        //         ];
        //     }) : 'null',
        //     'main_criterias' => $report ? $report->reportData->criteriaVersion->quantityMainCriterias->map(function($main) {
        //         return [
        //             'id' => $main->id,
        //             'name' => $main->name,
        //             'tooltips' => $main->tooltips,
        //             'sub_criterias_count' => $main->quantitySubCriterias->count(),
        //             'sub_criterias' => $main->quantitySubCriterias->map(function($sub) {
        //                 return [
        //                     'id' => $sub->id,
        //                     'name' => $sub->name,
        //                     'sequence' => $sub->sequence,
        //                     'score_a' => $sub->score_a,
        //                     'score_b' => $sub->score_b,
        //                 ];
        //             })
        //         ];
        //     }) : 'null - quantityMainCriterias is null'
        // ]);

        $evaluationItems = [];
    
        if ($report && $report->reportData && $report->reportData->criteriaVersion) {
            $quantityMainCriterias = $report->reportData->criteriaVersion->quantityMainCriterias;
            
            foreach ($quantityMainCriterias as $mainCriteria) {
                // Add main criteria as a header row
                $evaluationItems[] = [
                    'title' => $mainCriteria->name,
                    'subtitle' => $mainCriteria->tooltips,
                    'is_main' => true,
                    'main_criteria_id' => $mainCriteria->id,
                ];
                
                // Add sub criterias
                foreach ($mainCriteria->quantitySubCriterias as $subCriteria) {
                    $evaluationItems[] = [
                        'title' => $subCriteria->name,
                        'subtitle' => null,
                        'is_main' => false,
                        'sequence' => $subCriteria->sequence,
                        'sub_criteria_id' => $subCriteria->id,
                        'score_a' => $subCriteria->score_a,
                        'score_b' => $subCriteria->score_b,
                        'tor_compliant' => '', // This will be filled by user or from existing data
                        'user_score' => '', // This will be filled by user or from existing data
                        'evidence' => '', // This will be filled by user or from existing data
                    ];
                }
            }
        }
        
        return view('evaluatee.evaluation', compact(
            'id', 'user', 'report', 'assignment', 'formatThai',
            'evaluatorName', 'startTime', 'endTime', 'reportName',
            'startTimeFormatted', 'endTimeFormatted', 'assessmentType',
            'quantityMainCriterias', 'evaluationItems'
        ));
    }

    public function updateEvaluation(Request $request, $id)
    {
        // Handle evaluation update
        $request->validate([
            'status' => 'required|string',
            // Add other validation rules
        ]);

        // Update evaluation logic here
        
        return redirect()->route('dashboard')->with('success', 'อัปเดตการประเมินเรียบร้อยแล้ว');
    }
}