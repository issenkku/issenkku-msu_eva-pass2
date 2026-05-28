<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait BuildsDashboardMetrics
{
    protected function hasActiveFilters(Request $request, array $keys): bool
    {
        return $this->activeFilters($request, $keys)->isNotEmpty();
    }

    protected function activeFilters(Request $request, array $keys): Collection
    {
        return collect($request->only($keys))
            ->filter(fn ($value) => filled($value));
    }

    protected function countByStatuses($evaluations, array $statuses): int
    {
        return $evaluations->filter(function ($assignment) use ($statuses) {
            $reportStatus = optional($assignment->report)->status ?? 'Assigned';

            return in_array($reportStatus, $statuses, true);
        })->count();
    }

    protected function countDueSoonEvaluations($evaluations): int
    {
        return $evaluations->filter(function ($assignment) {
            $endTime = optional($assignment->assignmentData)->end_time;
            $status = optional($assignment->report)->status;

            if (! $endTime || $status === 'Completed') {
                return false;
            }

            return Carbon::parse($endTime)->between(now()->startOfDay(), now()->copy()->addDays(7)->endOfDay());
        })->count();
    }

    protected function countOverdueEvaluations($evaluations): int
    {
        return $evaluations->filter(function ($assignment) {
            $endTime = optional($assignment->assignmentData)->end_time;
            $status = optional($assignment->report)->status;

            if (! $endTime || $status === 'Completed') {
                return false;
            }

            return Carbon::parse($endTime)->endOfDay()->lt(now());
        })->count();
    }

    protected function buildOverviewPercents(array $data, int $totalEvaluatees): array
    {
        return collect($data)
            ->map(fn ($count) => $totalEvaluatees > 0 ? round(($count / $totalEvaluatees) * 100, 1) : 0)
            ->values()
            ->all();
    }

    protected function formatRemainingText($endTime): string
    {
        if (! $endTime) {
            return '-';
        }

        $days = now()->startOfDay()->diffInDays(Carbon::parse($endTime)->startOfDay(), false);

        if ($days < 0) {
            return 'เลยกำหนด '.abs($days).' วัน';
        }

        if ($days === 0) {
            return 'ครบกำหนดวันนี้';
        }

        return 'เหลือ '.$days.' วัน';
    }
}
