<?php

namespace App\Http\Controllers;

use App\Models\QualityScore;
use App\Models\User;
use App\Models\Reports;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvaluatorController extends Controller
{
    public function dashboard(Request $request)
    {
        $userId = Auth::id() ?? 2;
        if (!$userId) abort(403, 'Unauthorized');

        $currentUser = User::with('department', 'position')->find($userId);
        if (!$currentUser) abort(404, 'ไม่พบผู้ใช้งาน');

        $statusFilter = $request->input('status');

        $assignments = DB::table('assignments')
            ->join('assignment_datas', 'assignments.assignment_data_id', '=', 'assignment_datas.id')
            ->join('reports', 'assignments.report_id', '=', 'reports.id')
            ->join('report_datas', 'reports.report_data_id', '=', 'report_datas.id')
            ->join('users as evaluatee', 'assignments.evaluatee', '=', 'evaluatee.id')
            ->where('assignments.evaluator', $currentUser->id)
            ->whereNotIn('reports.status', ['Assigned', 'Draft']);

        if (!empty($statusFilter)) {
            $assignments->where('reports.status', $statusFilter);
        }

        $assignments = $assignments->select(
            'assignments.assignment_data_id',
            'assignment_datas.start_time',
            'assignment_datas.end_time',
            'reports.status',
            'reports.id as report_id',
            'report_datas.report_title',
            'report_datas.assessment_type',
            'evaluatee.id as evaluatee_id',
            'evaluatee.prefix as evaluatee_prefix',
            'evaluatee.name as evaluatee_name',
            'evaluatee.employee_id as evaluatee_employee_id'
        )
            ->orderBy('assignment_datas.start_time', 'desc')
            ->paginate(5);

        $formattedAssignments = $assignments->getCollection()->map(function ($assignment, $index) use ($assignments) {
            $statusInfo = $this->getStatusInfo($assignment->status ?? null, $assignment->end_time);

            return (object) [
                'sequence' => ($assignments->currentPage() - 1) * $assignments->perPage() + $index + 1,
                'assignment_data_id' => $assignment->assignment_data_id,
                'report_id' => $assignment->report_id,
                'report_title' => $assignment->report_title,
                'assessment_type' => $assignment->assessment_type,
                'evaluatee_id' => $assignment->evaluatee_id,
                'evaluatee_name' => $assignment->evaluatee_prefix . $assignment->evaluatee_name,
                'evaluatee_employee_id' => $assignment->evaluatee_employee_id,
                'start_date' => $this->formatThaiDate($assignment->start_time),
                'end_date' => $this->formatThaiDate($assignment->end_time),
                'status_text' => $statusInfo['text'],
                'status_class' => $statusInfo['class'],
                'status_color' => $statusInfo['color'],
            ];
        });

        $assignments->setCollection($formattedAssignments);

        $evaluatorInfo = [
            'name' => $currentUser->prefix . $currentUser->name,
            'employee_id' => $currentUser->employee_id,
            'department' => optional($currentUser->department)->department_name ?? '-',
            'position' => optional($currentUser->position)->name ?? '-',
            'experience' => $this->calculateExperience($currentUser->created_at),
            'average_score' => $this->getAverageEvaluationScore($currentUser->id)
        ];

        return view('evaluator_dashboard.index', [
            'assignments' => $assignments,
            'evaluatorInfo' => $evaluatorInfo,
            'statusFilter' => $statusFilter
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'required|numeric|min:0|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        foreach ($validated['scores'] as $criteriaId => $score) {
            if (empty($criteriaId)) continue;

            QualityScore::updateOrCreate([
                'quality_sub_criteria_id' => $criteriaId,
                'report_id' => $id,
            ], [
                'score' => $score,
            ]);
        }

        Reports::where('id', $id)->update([
            'comment' => $request->input('comment'),
            'status' => $request->has('change_status') ? 'Completed' : DB::raw('status'),
        ]);

        return redirect()->route('evaluator.index')->with('success', 'บันทึกคะแนนเรียบร้อยแล้ว');
    }

    public function reject($reportId)
    {
        $report = Reports::findOrFail($reportId);
        $report->status = 'Assigned';
        $report->save();

        return redirect()->route('evaluator.index')->with('success', 'ไม่อนุมัติแบบประเมินเรียบร้อยแล้ว');
    }

    private function formatThaiDate($datetime)
    {
        if (!$datetime) return '-';

        $thaiMonths = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.',
            6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.',
            10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];

        $date = Carbon::parse($datetime);
        return sprintf('%02d/%s/%d', $date->day, $thaiMonths[$date->month], $date->year + 543);
    }

    private function getStatusInfo($status, $endTime)
    {
        if (!$endTime) {
            return ['text' => 'สถานะไม่ระบุ', 'class' => 'unknown', 'color' => '#6c757d'];
        }

        return match ($status) {
            'Assigned' => ['text' => 'ยังไม่ประเมิน (มอบหมายแล้ว)', 'class' => 'Assigned', 'color' => '#FF0000'],
            'draft' => ['text' => 'บันทึกแล้ว (รออนุมัติ)', 'class' => 'draft', 'color' => '#ffc107'],
            'Pending' => ['text' => 'รอผลประเมิน (รอกดอนุมัติ)', 'class' => 'Pending', 'color' => '#17a2b8'],
            'Completed' => ['text' => 'ประเมินเสร็จสิ้น (อนุมัติแล้ว)', 'class' => 'Completed', 'color' => '#28a745'],
            default => ['text' => 'ไม่ทราบสถานะ', 'class' => 'unknown', 'color' => '#6c757d'],
        };
    }

    private function calculateExperience($createdAt)
    {
        $years = now()->diffInYears($createdAt);
        return max($years, 1);
    }

    private function getAverageEvaluationScore($userId)
    {
        return 4.5; // Default value
    }
}