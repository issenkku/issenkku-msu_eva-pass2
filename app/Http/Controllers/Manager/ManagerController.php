<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Reports;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    private function countByStatus($evaluations, $statuses)
    {
        return $evaluations->filter(function($assignment) use ($statuses) {
            $reportStatus = optional($assignment->report)->status ?? 'Assigned';
            return in_array($reportStatus, $statuses);
        })->count();
    }

    public function dashboard(Request $request)
    {
        // Load user with comprehensive relationships based on actual schema
        $user = $request->user()->load([
            'position',
            'department',
        ]);

        // Get ALL reports with complete data (Director has access to everything)
        $allReportsData = Reports::with([
            'reportData', // report_datas table
            'assignments.assignmentData.evaluatorPosition', // assignment_datas -> positions
            'assignments.assignmentData.evaluateePosition', // assignment_datas -> positions  
            'assignments.evaluateeUser.department', // users -> departments (evaluatee)
            'assignments.evaluateeUser.position', // users -> positions (evaluatee)
        ])->get();

        // Get ALL evaluations (Director can see everything, no department filtering)
        $evaluations = $allReportsData->map(function($report) {
            if ($report->assignments) {
                $assignment = $report->assignments;
                $assignment->report = $report;
                
                // Get evaluatee information
                $assignment->evaluateeName = $assignment->evaluateeUser?->name ?? '-';
                $assignment->evaluateeDepartment = $assignment->evaluateeUser?->department?->name ?? '-';
                $assignment->evaluateePosition = $assignment->evaluateeUser?->position?->name ?? '-';
                
                // Get evaluator information from assignment_data
                $assignment->evaluatorPosition = $assignment->assignmentData?->evaluatorPosition?->name ?? '-';
                $assignment->evaluateeAssignedPosition = $assignment->assignmentData?->evaluateePosition?->name ?? '-';
                
                // Add time information
                $assignment->startTime = $assignment->assignmentData?->start_time ?? null;
                $assignment->endTime = $assignment->assignmentData?->end_time ?? null;
                
                return $assignment;
            }
            return null;
        })->filter(); // Remove null values

        // Get user's assignments as evaluatee (where user is being evaluated)
        $userAsEvaluatee = Reports::whereHas('assignments', function($query) use ($user) {
            $query->where('evaluatee', $user->id);
        })->with([
            'reportData',
            'assignments.assignmentData.evaluatorPosition',
            'assignments.assignmentData.evaluateePosition',
        ])->get()->map(function($report) {
            if ($report->assignments) {
                $assignment = $report->assignments;
                $assignment->report = $report;
                $assignment->evaluatorPosition = $assignment->assignmentData?->evaluatorPosition?->name ?? '-';
                $assignment->evaluateePosition = $assignment->assignmentData?->evaluateePosition?->name ?? '-';
                $assignment->startTime = $assignment->assignmentData?->start_time ?? null;
                $assignment->endTime = $assignment->assignmentData?->end_time ?? null;
                return $assignment;
            }
            return null;
        })->filter();

        // For evaluator assignments, we need to find reports where the user's position 
        // matches the evaluator_position_id in assignment_data
        $userAsEvaluator = Reports::whereHas('assignments.assignmentData', function($query) use ($user) {
            $query->where('evaluator_position_id', $user->position_id);
        })->with([
            'reportData',
            'assignments.assignmentData.evaluatorPosition',
            'assignments.assignmentData.evaluateePosition',
            'assignments.evaluateeUser.department',
            'assignments.evaluateeUser.position',
        ])->get()->map(function($report) use ($user) {
            if ($report->assignments) {
                $assignment = $report->assignments;
                $assignment->report = $report;
                $assignment->evaluatorName = $user->name;
                $assignment->evaluateeName = $assignment->evaluateeUser?->name ?? '-';
                $assignment->evaluateeDepartment = $assignment->evaluateeUser?->department?->name ?? '-';
                $assignment->evaluatorDepartment = $user->department?->name ?? '-';
                $assignment->evaluatorPosition = $assignment->assignmentData?->evaluatorPosition?->name ?? '-';
                $assignment->evaluateePosition = $assignment->assignmentData?->evaluateePosition?->name ?? '-';
                $assignment->startTime = $assignment->assignmentData?->start_time ?? null;
                $assignment->endTime = $assignment->assignmentData?->end_time ?? null;
                
                $assignment->sameDepartment = $assignment->evaluateeUser && 
                    $assignment->evaluateeUser->department_id === $user->department_id;
                return $assignment;
            }
            return null;
        })->filter();

        // Count status for ALL evaluations (Director sees everything)
        $statusCounts = [
            'ทั้งหมด' => $evaluations->count(),
            'รอการกรอกข้อมูล' => $this->countByStatus($evaluations, 
                ['Assigned', 'Draft', 'Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft' ]),
            'ยังไม่ประเมิน' => $this->countByStatus($evaluations, ['Manager_assign']),
            'กำลังดำเนินการ' => $this->countByStatus($evaluations, ['Manager_draft']),
            'ประเมินเสร็จสิ้น' => $this->countByStatus($evaluations, ['Completed']),
        ];

        // Additional counts by department (useful for director overview)
        $departmentCounts = $evaluations->groupBy('evaluateeDepartment')->map(function($deptEvaluations) {
            return [
                'total' => $deptEvaluations->count(),
                'completed' => $deptEvaluations->where('report.status', 'Completed')->count(),
                'pending' => $deptEvaluations->where('report.status', '!=', 'Completed')->count(),
            ];
        });

        return view('manager_dashboard.index', [
            'user' => $user,
            'statusCounts' => $statusCounts,
            'departmentCounts' => $departmentCounts, // Department breakdown
            'evaluations' => $evaluations, // ALL evaluations (Director view)
            'userAsEvaluatee' => $userAsEvaluatee, // Director's evaluatee assignments
            'userAsEvaluator' => $userAsEvaluator, // Director's evaluator assignments
            'allReportsData' => $allReportsData, // Complete reports data
        ]);
    }
}
