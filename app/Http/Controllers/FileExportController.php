<?php

namespace App\Http\Controllers;

use App\Exports\ReportsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FileExportController extends Controller
{
    public function exportDashboard(Request $request)
    {
        // $user = $request->user();

        // $userId = $request->input('user_id');

        $query = $this->filteredAssignmentsQuery($request);

        return Excel::download(new ReportsExport($query), 'evaluation_results.xlsx');
    }

    public function filteredAssignmentsQuery(Request $request)
    {
        $user = $request->user();

        $query = $request->user()->assignmentsForDashboard();

        // Access control
        // if ($user->evaluatorAssignments()->exists()) {
        //     // Current user is an evaluator: restrict to same department
        //     $query->whereHas('evaluateeUser', fn($q) => $q->where('department_id', $user->department_id));
        // }

        // Search filter
        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));
            $query->whereHas('evaluateeUser', fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]));
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

        return $query;
    }
}
