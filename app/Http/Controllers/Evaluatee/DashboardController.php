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
            'reportData',
            'assignments.evaluatorUser',
            'assignments.assignmentData',
        ])->findOrFail($id);
        
        $currentAssignment = $report->assignments
        ->where('evaluatee', $user->id)
        ->first();

        function formatThai($datetime) {
        if (!$datetime) return '-';
            \Carbon\Carbon::setLocale('th');
            setlocale(LC_TIME, 'th_TH.UTF-8');
            $date = \Carbon\Carbon::parse($datetime);
            $year = $date->year + 543;
            return $date->translatedFormat('j F') . " {$year} เวลา " . $date->format('H:i') . ' น.';
        }
        
        return view('evaluatee.evaluation', [
            'id' => $id,
            'user' => $user,
            'report' => $report,
            'assignment' => $currentAssignment,
        ]);
    }
    
    // public function evaluation(Request $request, $id)
    // {
    //     $user = $request->user()->load('position', 'department');
        
    //     $assignment = Assignments::with([
    //         'assignmentData',
    //         'report.reportData',
    //         'evaluatorUser:id,name',
    //     ]);
        
    //     // if ($assignment->evaluatee !== $user->id) {
    //     //     abort(403, 'คุณไม่มีสิทธิ์เข้าถึงการประเมินนี้');
    //     // }
        
    //     $report = $assignment->report;
    //     $assignmentData = $assignment->assignmentData;
    //     $evaluator = $assignment->evaluatorUser;

    //     function formatThai($datetime) {
    //     if (!$datetime) return '-';
    //         \Carbon\Carbon::setLocale('th');
    //         setlocale(LC_TIME, 'th_TH.UTF-8');
    //         $date = \Carbon\Carbon::parse($datetime);
    //         $year = $date->year + 543;
    //         return $date->translatedFormat('j F') . " {$year} เวลา " . $date->format('H:i') . ' น.';
    //     }
        
    //     // Map status to Thai
    //     $statusFromDB = optional($report)->status ?? 'Assigned';
    //     $statusMapping = [
    //         'Assigned' => 'ยังไม่ประเมิน',
    //         'Draft' => 'กำลังดำเนินการ',
    //         'Pending' => 'รอผลการประเมิน',
    //         'Completed' => 'ประเมินเสร็จสิ้น',
    //     ];
    //     $status = $statusMapping[$statusFromDB] ?? $statusFromDB;
        
    //     return view('evaluatee.evaluation', [
    //         'id' => $id,
    //         'user' => $user,
    //         'assignment' => $assignment,
    //         'report' => $report,
    //         'assignmentData' => $assignmentData,
    //         'evaluator' => $evaluator,
    //         'status' => $status,
    //         'statusFromDB' => $statusFromDB,
    //         'startTime' => formatThai(optional($assignmentData)->start_time),
    //         'endTime' => formatThai(optional($assignmentData)->end_time),
    //         'canEdit' => in_array($statusFromDB, ['Assigned', 'Draft']),
    //     ]);
    // }

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