<?php

namespace App\Http\Controllers\Evaluatee;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    // public function index(Request $request): View
    // {
    //     $user = $request->user()->load('position', 'department');

    //     // Sample data - replace with your actual data source
    //     $evaluations = [
    //         [
    //             'title' => 'ยังไม่ประเมิน',
    //             'description' => null,
    //             'start_date' => '1 เมษายน 2568',
    //             'end_date' => '15 เมษายน 2568',
    //             'evaluator' => 'ผู้บังคับบัญชา',
    //             'status' => 'ยังไม่ประเมิน',
    //             'id' => 1
    //         ],
    //         [
    //             'title' => 'บันทึกกิจวัตรประจำวัน',
    //             'description' => null,
    //             'start_date' => '1 เมษายน 2568',
    //             'end_date' => '30 เมษายน 2568',
    //             'evaluator' => 'ตนเอง',
    //             'status' => 'กำลังดำเนินการ',
    //             'id' => 2
    //         ]
    // }

    public function index(Request $request)
    {
        $user = $request->user()->load([
            'position',
            'department',
            'assignment.assignmentData',
            'assignment.report.reportData',
        ]);

        $evaluations = $user->assignment->pluck('report')->filter();

        $statusCounts = [
            'ทั้งหมด' => $evaluations->count(),
            'ยังไม่ประเมิน' => $evaluations->where('status', 'Assigned')->count(),
            'บันทึกร่างประเมิน' => $evaluations->where('status', 'Draft')->count(),
            'รอผลการประเมิน' => $evaluations->where('status', 'Pending')->count(),
            'ประเมินเสร็จสิ้น' => $evaluations->where('status', 'Completed')->count(),
        ];

        return view('evaluatee.dashboard', [
            'user' => $user,
            'statusCounts' => $statusCounts,
            'evaluations' => $evaluations,
        ]);
    }
    
    public function evaluation(Request $request, $id)
    {
        $user = $request->user()->load('position', 'department');
        // Handle individual evaluation view/edit
        return view('evaluatee.evaluation', [
            'id' => $id,
            'user' => $user
        ]);
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