<?php
/**
 * ไฟล์คอนโทรลเลอร์: app/Http/Controllers\FileExportController.php
 */

namespace App\Http\Controllers;

use App\Exports\ReportsExport;
use App\Exports\SingleReportExport;
use App\Models\Reports;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FileExportController extends Controller
{
    /**
     * เมธอด: exportDashboard
     * จุดประสงค์: บันทึกข้อมูล ReportsExport ส่งไฟล์สำหรับดาวน์โหลด
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ไฟล์ดาวน์โหลด
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function exportDashboard(Request $request)
    {
        $user = $request->user();

        // Directors/Managers/Admins should export from the full dataset
        if ($user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'ผู้บริหาร', 'กรรมการ'])) {
            $query = $this->adminFilteredAssignmentsQuery($request);
        } else {
            $query = $this->filteredAssignmentsQuery($request);
        }

        return Excel::download(new ReportsExport($query), 'รายงานการประเมินผล.xlsx');
    }

    /**
     * เมธอด: adminExportDashboard
     * จุดประสงค์: บันทึกข้อมูล ReportsExport ส่งไฟล์สำหรับดาวน์โหลด
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ไฟล์ดาวน์โหลด
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function adminExportDashboard(Request $request)
    {
        $query = $this->adminFilteredAssignmentsQuery($request);

        return Excel::download(new ReportsExport($query), 'รายงานการประเมินผล.xlsx');
    }

    /**
     * เมธอด: exportSingleReport
     * จุดประสงค์: บันทึกข้อมูล SingleReportExport ส่งไฟล์สำหรับดาวน์โหลด
     * อินพุต: ตัวระบุ ($id)
     * เอาต์พุต: ไฟล์ดาวน์โหลด
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function exportSingleReport($id)
    {
        // Find the report first
        $report = Reports::with(
            'assignments.evaluateeUser.department',
            'assignments.evaluateeUser.position',
            'assignments.report.quantityScores',
            'assignments.report.qualityScores',
            'reportData.criteriaVersion.categories.evaluationLists.quantitySubCriterias.mainCriteria',
            'reportData.criteriaVersion.categories.evaluationLists.qualitySubCriterias.mainCriteria'
        )->findOrFail($id);

        $assignment = $report->assignments;

        if (! $assignment) {
            abort(404, 'Assignment not found for this report.');
        }

        $assignment->loadMissing([
            'report.quantityScores',
            'report.qualityScores',
            'evaluateeUser',
            'evaluateeUser.department',
            'evaluateeUser.position',
            'assignmentData.evaluatorPosition',
        ]);

        $username = $assignment->evaluateeUser?->name ?? 'ไม่ทราบชื่อ';

        $fileName = 'รายงานผลการประเมินรายบุคคล-'.str_replace(' ', '_', $username).'.xlsx';

        return Excel::download(new SingleReportExport($assignment), $fileName);
    }

    /**
     * เมธอด: filteredAssignmentsQuery
     * จุดประสงค์: ประมวลผลคำขอ
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function filteredAssignmentsQuery(Request $request)
    {
        $user = $request->user();

        $query = $user->assignmentsForDashboard();

        // Search filter
        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));
            $query->where(function ($q) use ($search) {
                // Search evaluatee name
                $q->whereHas('evaluateeUser', fn ($q2) => $q2->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                );

                // Search report title
                $q->orWhereHas('report.reportData', fn ($q2) => $q2->whereRaw('LOWER(report_title) LIKE ?', ["%{$search}%"])
                );
            });
        }

        // Year filter
        if ($request->filled('year')) {
            $year = $request->input('year');
            $query->whereHas('assignmentData', fn ($q) => $q->whereYear('start_time', $year));
        }

        // Start/End date filters
        if ($request->filled('start_time')) {
            $query->whereHas('assignmentData', fn ($q) => $q->where('start_time', '>=', $request->input('start_time')));
        }
        if ($request->filled('end_time')) {
            $query->whereHas('assignmentData', fn ($q) => $q->where('end_time', '<=', $request->input('end_time')));
        }

        // Department filter (if provided)
        if ($request->has('department_name') && $request->input('department_name') !== '') {
            $department_name = $request->input('department_name');
            $query->whereHas('evaluateeUser.department', fn ($q) => $q->whereRaw('LOWER(department_name) = ?', [strtolower($department_name)]));
        }

        return $query;
    }

    /**
     * เมธอด: adminFilteredAssignmentsQuery
     * จุดประสงค์: ประมวลผลคำขอ
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function adminFilteredAssignmentsQuery(Request $request)
    {
        $user = $request->user();

        $query = $user->allAssignmentsForDashboard();

        // Search filter
        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));
            $query->where(function ($q) use ($search) {
                // Search evaluatee name
                $q->whereHas('evaluateeUser', fn ($q2) => $q2->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                );

                // Search report title
                $q->orWhereHas('report.reportData', fn ($q2) => $q2->whereRaw('LOWER(report_title) LIKE ?', ["%{$search}%"])
                );

                // Search evaluator names via assignmentData -> evaluatorUser
                $q->orWhereHas('assignmentData.evaluatorUser', fn ($q2) => $q2->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]));
            });
        }

        // Year filter
        if ($request->filled('year')) {
            $year = $request->input('year');
            $query->whereHas('assignmentData', fn ($q) => $q->whereYear('start_time', $year));
        }

        // Start/End date filters
        if ($request->filled('start_time')) {
            $query->whereHas('assignmentData', fn ($q) => $q->where('start_time', '>=', $request->input('start_time')));
        }
        if ($request->filled('end_time')) {
            $query->whereHas('assignmentData', fn ($q) => $q->where('end_time', '<=', $request->input('end_time')));
        }

        if ($request->has('department_name') && $request->input('department_name') !== '') {
            $department_name = $request->input('department_name');

            $query->whereHas('evaluateeUser.department', fn ($q) => $q->whereRaw('LOWER(department_name) = ?', [strtolower($department_name)])
            );
        }

        // dd($query->toSql(), $query->getBindings(), $query->count());

        return $query;
    }
}
