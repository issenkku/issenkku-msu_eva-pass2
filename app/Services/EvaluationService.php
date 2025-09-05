<?php

namespace App\Services;

use App\Models\Reports;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EvaluationService
{
    public function getAllReportsWithRelations(): Collection
    {
        return Reports::with([
            'reportData',
            'assignments.assignmentData.evaluatorPosition',
            'assignments.assignmentData.evaluateePosition',
            'assignments.evaluateeUser.department',
            'assignments.evaluateeUser.position',
        ])->get();
    }

    public function mapAssignments(Collection $reports, ?User $user = null, string $role = 'director'): Collection
    {
        return $reports->map(function ($report) use ($user, $role) {
            $assignment = $report->assignments;
            if (! $assignment) {
                return null;
            }

            $assignment->setRelation('report', $report);
            $assignment->evaluateeName = $assignment->evaluateeUser?->name ?? '-';
            $assignment->evaluateeDepartment = $assignment->evaluateeUser?->department?->department_name ?? '-';
            $assignment->evaluateePosition = $assignment->evaluateeUser?->position?->name ?? '-';
            $assignment->evaluatorPosition = $assignment->assignmentData?->evaluatorPosition?->name ?? '-';
            $assignment->evaluateeAssignedPosition = $assignment->assignmentData?->evaluateePosition?->name ?? '-';
            $assignment->evaluatorName = $assignment->getEvaluatorUsers()->pluck('name')->implode(', ') ?: '-';
            $assignment->startTime = $assignment->assignmentData?->start_time ?? null;
            $assignment->endTime = $assignment->assignmentData?->end_time ?? null;

            if ($role === 'evaluator' && $user) {
                $assignment->evaluatorName = $user->name;
                $assignment->evaluatorDepartment = $user->department?->name ?? '-';
                $assignment->sameDepartment = $assignment->evaluateeUser &&
                    $assignment->evaluateeUser->department_id === $user->department_id;
            }

            return $assignment;
        })->filter();
    }

    public function filterEvaluations(Collection $evaluations, array $filters): Collection
    {
        if (! empty($filters['search'])) {
            $evaluations = $evaluations->filter(function ($assignment) use ($filters) {
                $evaluateeName = $assignment->evaluateeUser?->name ?? '';
                $evaluatorName = strtolower($assignment->getEvaluatorUsers()->pluck('name')->implode(' '));
                $reportTitle = $assignment->report?->reportData?->report_title ?? '';

                return Str::contains(strtolower($evaluateeName), strtolower($filters['search']))
                    || Str::contains(strtolower($evaluatorName), strtolower($filters['search']))
                    || Str::contains(strtolower($reportTitle), strtolower($filters['search']));
            });
        }

        if (! empty($filters['year'])) {
            $evaluations = $evaluations->filter(function ($assignment) use ($filters) {
                $year = Carbon::parse(optional($assignment->assignmentData)->start_time)->year ?? null;

                return $year == $filters['year'];
            });
        }

        if (! empty($filters['start_time'])) {
            $evaluations = $evaluations->filter(function ($assignment) use ($filters) {
                return Carbon::parse(optional($assignment->assignmentData)->start_time)
                    ->gte(Carbon::parse($filters['start_time']));
            });
        }

        if (! empty($filters['end_time'])) {
            $evaluations = $evaluations->filter(function ($assignment) use ($filters) {
                return Carbon::parse(optional($assignment->assignmentData)->end_time)
                    ->lte(Carbon::parse($filters['end_time']));
            });
        }

        if (! empty($filters['department_name'])) {
            $evaluations = $evaluations->filter(function ($assignment) use ($filters) {
                return optional($assignment->evaluateeUser?->department)->department_name === $filters['department_name'];
            });
        }

        return $evaluations;
    }

    public function getYears(Collection $evaluations): Collection
    {
        return $evaluations->pluck('assignmentData.start_time')
            ->filter()
            ->map(fn ($dt) => Carbon::parse($dt)->year)
            ->unique()
            ->sortDesc()
            ->values();
    }
}
