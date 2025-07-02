<?php

namespace App\Http\Controllers\Evaluatee;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('position', 'department');

        // Sample data - replace with your actual data source
        $evaluations = [
            [
                'title' => 'ยังไม่ประเมิน',
                'description' => null,
                'start_date' => '1 เมษายน 2568',
                'end_date' => '15 เมษายน 2568',
                'evaluator' => 'ผู้บังคับบัญชา',
                'status' => 'ยังไม่ประเมิน',
                'id' => 1
            ],
            [
                'title' => 'บันทึกกิจวัตรประจำวัน',
                'description' => null,
                'start_date' => '1 เมษายน 2568',
                'end_date' => '30 เมษายน 2568',
                'evaluator' => 'ตนเอง',
                'status' => 'กำลังดำเนินการ',
                'id' => 2
            ],
            [
                'title' => 'รอผลการประเมิน',
                'description' => null,
                'start_date' => '15 เมษายน 2568',
                'end_date' => '25 เมษายน 2568',
                'evaluator' => 'คณะกรรมการ',
                'status' => 'รอผลการประเมิน',
                'id' => 3
            ],
            [
                'title' => 'ประเมินแล้ว',
                'description' => null,
                'start_date' => '1 เมษายน 2568',
                'end_date' => '20 เมษายน 2568',
                'evaluator' => 'ระบบอัตโนมัติ',
                'status' => 'ประเมินแล้ว',
                'id' => 4
            ],
            [
                'title' => 'แสดงผลการประเมิน',
                'description' => null,
                'start_date' => '20 เมษายน 2568',
                'end_date' => '30 เมษายน 2568',
                'evaluator' => 'ผู้ดูแลระบบ',
                'status' => 'แสดงผลการประเมิน',
                'id' => 5
            ]
        ];

        // Calculate status counts
        $statusCounts = [
            'ทั้งหมด' => count($evaluations),
            'ยังไม่ประเมิน' => collect($evaluations)->where('status', 'ยังไม่ประเมิน')->count(),
            'กำลังดำเนินการ' => collect($evaluations)->where('status', 'กำลังดำเนินการ')->count(),
            'รอผลการประเมิน' => collect($evaluations)->where('status', 'รอผลการประเมิน')->count(),
            'ประเมินแล้ว' => collect($evaluations)->where('status', 'ประเมินแล้ว')->count(),
            'แสดงผลการประเมิน' => collect($evaluations)->where('status', 'แสดงผลการประเมิน')->count(),
        ];

        // Calculate completion percentage
        $completedEvaluations = collect($evaluations)->whereIn('status', ['ประเมินแล้ว', 'แสดงผลการประเมิน'])->count();
        $completionPercentage = count($evaluations) > 0 ? round(($completedEvaluations / count($evaluations)) * 100) : 0;

        return view('evaluatee.dashboard', [
            'user' => $user,
            'evaluations' => $evaluations,
            'statusCounts' => $statusCounts,
            'completionPercentage' => $completionPercentage
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