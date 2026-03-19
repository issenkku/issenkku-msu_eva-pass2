<?php

namespace App\Services;

use App\Models\Reports;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EvaluationService
{
     public function getAllReportsWithAssignments()
    {
        return Reports::with([
            'reportData',
            'assignments.assignmentData.evaluatorUser',
            'assignments.evaluateeUser.department',
            'assignments.evaluateeUser.position',
        ])->get();
    }

    public function mapAssignments($reports, $user = null, $type = 'all')
    {
        return $reports->map(function ($report) use ($user, $type) {
            if (!$report->assignments) {
                return null;
            }

            $assignment = $report->assignments;
            $assignment->setRelation('report', $report);

            // Common info
            $assignment->evaluateeName       = $assignment->evaluateeUser?->name ?? '-';
            $assignment->evaluateeDepartment = $assignment->evaluateeUser?->department?->department_name ?? '-';
            $assignment->evaluateePosition   = $assignment->evaluateeUser?->position?->name ?? '-';
            $assignment->evaluatorPosition   = $assignment->assignmentData->evaluatorUser?->position?->name ?? '-';
            $assignment->evaluateeAssignedPosition = $assignment->assignmentData?->evaluateePosition?->name ?? '-';
            $assignment->evaluatorName       = $assignment->assignmentData->evaluatorUser?->name ?? '-';;
            $assignment->startTime = $assignment->assignmentData?->start_time ?? null;
            $assignment->endTime   = $assignment->assignmentData?->end_time ?? null;

            // Special cases for evaluator view
            if ($type === 'evaluator' && $user) {
                $assignment->evaluatorName       = $user->name;
                $assignment->evaluatorDepartment = $user->department?->name ?? '-';
                $assignment->sameDepartment      = $assignment->evaluateeUser &&
                    $assignment->evaluateeUser->department_id === $user->department_id;
            }

            return $assignment;
        })->filter();
    }

    public function filterEvaluations($evaluations, $filters)
    {
        if (!empty($filters['search'])) {
            $searchTerm = strtolower($filters['search']);
            $evaluations = $evaluations->filter(function ($assignment) use ($searchTerm) {
                $evaluateeName = strtolower($assignment->evaluateeUser?->name ?? '');
                $evaluatorName = strtolower($assignment->assignmentData->evaluatorUser?->name ?? '-');
                $reportTitle   = strtolower($assignment->report?->reportData?->report_title ?? '');

                return Str::contains($evaluateeName, $searchTerm)
                    || Str::contains($reportTitle, $searchTerm)
                    || Str::contains($evaluatorName, $searchTerm);
            });
        }

        if (! empty($filters['year'])) {
            $evaluations = $evaluations->filter(function ($assignment) use ($filters) {
                $year = Carbon::parse(optional($assignment->assignmentData)->start_time)->year ?? null;

                return $year == $filters['year'];
            });
        }

        if (!empty($filters['start_time'])) {
            $startDate = Carbon::parse($filters['start_time']);
            $evaluations = $evaluations->filter(function ($assignment) use ($startDate) {
                $assignmentStart = optional($assignment->assignmentData)->start_time;
                return $assignmentStart && Carbon::parse($assignmentStart)->gte($startDate);
            });
        }

        if (!empty($filters['end_time'])) {
            $endDate = Carbon::parse($filters['end_time']);
            $evaluations = $evaluations->filter(function ($assignment) use ($endDate) {
                $assignmentEnd = optional($assignment->assignmentData)->end_time;
                return $assignmentEnd && Carbon::parse($assignmentEnd)->lte($endDate);
            });
        }

        if (! empty($filters['department_name'])) {
            $evaluations = $evaluations->filter(function ($assignment) use ($filters) {
                return optional($assignment->evaluateeUser?->department)->department_name === $filters['department_name'];
            });
        }

        if (! empty($filters['position_name'])) {
            $evaluations = $evaluations->filter(function ($assignment) use ($filters) {
                return optional($assignment->evaluateeUser?->position)->name === $filters['position_name'];
            });
        }

        return $evaluations;
    }

    public function getUserAsEvaluatee($user)
    {
        $reports = Reports::whereHas('assignments', function ($query) use ($user) {
            $query->where('evaluatee_id', $user->id);
        })->with([
            'reportData',
            'assignments.assignmentData',
        ])->get();

        return $this->mapAssignments($reports, $user, 'evaluatee');
    }

    public function getUserAsEvaluator($user)
    {
        $reports = Reports::whereHas('assignments.assignmentData', function ($query) use ($user) {
            $query->where('evaluator_id', $user->id);
        })->with([
            'reportData',
            'assignments.assignmentData',
            'assignments.evaluateeUser.department',
            'assignments.evaluateeUser.position',
        ])->get();

        return $this->mapAssignments($reports, $user, 'evaluator');
    }

    public function sortEvaluations($evaluations, $direction = 'desc')
    {
        $sorted = $evaluations->sortBy(function ($assignment) {
            $endTime = optional($assignment->assignmentData)->end_time;
            if ($endTime) {
                return Carbon::parse($endTime)->timestamp;
            }

            $startTime = optional($assignment->assignmentData)->start_time;
            if ($startTime) {
                return Carbon::parse($startTime)->timestamp;
            }

            return optional($assignment->report)->updated_at
                ? Carbon::parse($assignment->report->updated_at)->timestamp
                : (optional($assignment)->created_at
                    ? Carbon::parse($assignment->created_at)->timestamp
                    : 0);
        });

        return $direction === 'desc' ? $sorted->reverse()->values() : $sorted->values();
    }
}
