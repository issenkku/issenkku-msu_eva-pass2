<?php

namespace App\Http\Controllers;

use App\Exports\ReportsExport;
use App\Exports\SingleReportExport;
use App\Models\Reports;
use App\Support\AuditLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FileExportController extends Controller
{
    public function exportDashboard(Request $request)
    {
        $user = $request->user();

        // Directors/Managers/Admins should export from the full dataset
        if ($user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'ผู้บริหาร', 'กรรมการ'])) {
            $query = $this->adminFilteredAssignmentsQuery($request);
        } else {
            $query = $this->filteredAssignmentsQuery($request);
        }

        AuditLog::record('ส่งออกข้อมูล', 'ส่งออกรายงานภาพรวม', [
            'export_type' => 'dashboard',
            'scope' => 'role_aware',
            'filters' => $request->only(['search', 'year', 'start_time', 'end_time', 'department_name']),
        ], null, $user);

        return Excel::download(new ReportsExport($query), 'รายงานการประเมินผล.xlsx');
    }

    public function adminExportDashboard(Request $request)
    {
        $query = $this->adminFilteredAssignmentsQuery($request);

        AuditLog::record('ส่งออกข้อมูล', 'ส่งออกรายงานภาพรวม', [
            'export_type' => 'dashboard',
            'scope' => 'admin',
            'filters' => $request->only(['search', 'year', 'start_time', 'end_time', 'department_name']),
        ], null, $request->user());

        return Excel::download(new ReportsExport($query), 'รายงานการประเมินผล.xlsx');
    }

    public function exportSingleReport(Request $request, $id)
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

        $user = $request->user();
        $canExportAll = $user->hasAnyRole(['admin', 'ผู้บริหาร', 'กรรมการ']);

        if (! $canExportAll) {
            $isAssignedEvaluator = $user->assignmentsForDashboard()
                ->where('assignments.assignment_data_id', $assignment->assignment_data_id)
                ->where('assignments.report_id', $assignment->report_id)
                ->where('assignments.evaluatee_id', $assignment->evaluatee_id)
                ->exists();

            abort_unless($isAssignedEvaluator, 403);
        }

        abort_unless($report->status === 'Completed', 409, 'Report is not completed.');

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

        AuditLog::record('ส่งออกข้อมูล', 'ส่งออกรายงานรายบุคคล', [
            'export_type' => 'single_report',
            'report_id' => $report->id,
            'assignment_data_id' => $assignment->assignment_data_id,
            'evaluatee_id' => $assignment->evaluatee_id,
        ], $report, $user);

        return Excel::download(new SingleReportExport($assignment), $fileName);
    }

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

        return $query;
    }
}
