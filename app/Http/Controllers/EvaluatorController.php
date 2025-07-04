<?php

namespace App\Http\Controllers;

use App\Models\Assignments;
use App\Models\User;
use App\Models\Reports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\json;

class EvaluatorController extends Controller
{
    /**
     * Display evaluator dashboard - หน้าหลักของผู้ประเมิน
     */
    public function dashboard(Request $request)
    {
        $userId = Auth::id() ?? 2; // user ล็อกอิน หรือ default 2
        $currentUser = User::find($userId);

        if (!$currentUser) {
            abort(404, 'ไม่พบผู้ใช้งาน');
        }

        $statusFilter = $request->input('status');

        // ดึงข้อมูล assignments แบบ paginate
        $assignments = DB::table('assignments')
            ->join('assignment_datas', 'assignments.assignment_data_id', '=', 'assignment_datas.id')
            ->join('reports', 'assignments.report_id', '=', 'reports.id')
            ->join('report_datas', 'reports.report_data_id', '=', 'report_datas.id')
            ->join('users as evaluatee', 'assignments.evaluatee', '=', 'evaluatee.id')
            ->where('assignments.evaluator', $currentUser->id);

        if ($statusFilter && $statusFilter !== '') {
            $assignments->where('reports.status', $statusFilter);
        }

        $assignments = $assignments->select(
            'assignments.assignment_data_id as assignment_data_id',
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

        // แปลงข้อมูลเพื่อเพิ่มฟิลด์ sequence, format วันที่ และสถานะ
        $formattedAssignments = $assignments->getCollection()->map(function ($assignment, $index) use ($assignments) {
            $statusInfo = $this->getStatusInfo($assignment->status, $assignment->end_time);

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

        // เซ็ต collection ใหม่ใน paginator
        $assignments->setCollection($formattedAssignments);

        // ตัวอย่างข้อมูล evaluatorInfo แบบง่าย
        $evaluatorInfo = [
            'name' => $currentUser->prefix . $currentUser->name,
            'employee_id' => $currentUser->employee_id,
            'experience' => $this->calculateExperience($currentUser->created_at),
            'average_score' => $this->getAverageEvaluationScore($currentUser->id)
        ];

        return view('evaluator_dashboard.index', [
            'assignments' => $assignments,
            'evaluatorInfo' => $evaluatorInfo,
            'statusFilter' => $statusFilter
        ]);
    }

    // ตัวอย่างฟังก์ชันช่วยเหลือแปลงวันที่และสถานะ (ควรวางใน Controller เดียวกัน)
    private function formatThaiDate($date)
    {
        if (!$date) return '-';

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

        $dateObj = \Carbon\Carbon::parse($date);
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

        $endDate = \Carbon\Carbon::parse($endTime);

        switch ($status) {
            case 'completed':
                return ['text' => 'ประเมิณเสร็จสิ้น', 'class' => 'completed', 'color' => '#28a745'];
            case 'pending_approval':
                return ['text' => 'รอผลประเมิณ', 'class' => 'pending-approval', 'color' => '#17a2b8'];
            case 'draft':
                return ['text' => 'บันทึกแล้ว', 'class' => 'draft', 'color' => '#ffc107'];
            case 'assigned':
                if ($now > $endDate) {
                    return ['text' => 'เกินกำหนด', 'class' => 'overdue', 'color' => '#dc3545'];
                }
                return ['text' => 'ยังไม่ประเมิณ', 'class' => 'assigned', 'color' => '#6c757d'];
            case 'in_progress':
                if ($now > $endDate) {
                    return ['text' => 'เกินกำหนด', 'class' => 'overdue', 'color' => '#dc3545'];
                }
                return ['text' => 'กำลังดำเนินการ', 'class' => 'in-progress', 'color' => '#ffc107'];
            case 'pending':
            default:
                if ($now > $endDate) {
                    return ['text' => 'เกินกำหนด', 'class' => 'overdue', 'color' => '#dc3545'];
                }
                return ['text' => 'รอดำเนินการ', 'class' => 'pending', 'color' => '#6c757d'];
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
}
