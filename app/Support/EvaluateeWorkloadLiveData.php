<?php

namespace App\Support;

use App\Models\EvidenceAnswer;
use App\Models\QuantitySubCriteria;
use App\Models\WorkloadEntry;
use Illuminate\Support\Collection;

class EvaluateeWorkloadLiveData
{
    public static function build(QuantitySubCriteria $quantitySubCriteria, Collection $workloadForms, int $reportId): array
    {
        $workloadEntriesByFormId = collect();
        $formIds = $workloadForms->pluck('id')->filter()->unique()->values();

        if ($formIds->isNotEmpty()) {
            $workloadEntriesByFormId = WorkloadEntry::with('subject')
                ->where('report_id', $reportId)
                ->whereIn('workload_form_id', $formIds)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('workload_form_id');
        }

        $workloadTotalScore = $workloadEntriesByFormId
            ->flatten(1)
            ->sum(fn ($entry) => max(0, (float) ($entry->calculated_score ?? 0)));

        $evidenceLinksByEntryId = collect();
        if ($quantitySubCriteria->evaluation_list_id) {
            $evidenceLinksByEntryId = EvidenceAnswer::where('report_id', $reportId)
                ->where('evaluation_list_id', $quantitySubCriteria->evaluation_list_id)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('workload_entry_id')
                ->map(fn ($answers) => $answers->pluck('link')->filter()->values());
        }

        $workloadView = EvaluateeWorkloadViewData::build(
            $quantitySubCriteria,
            $workloadForms,
            $workloadEntriesByFormId,
            $evidenceLinksByEntryId,
        );

        return [
            'workloadEntriesByFormId' => $workloadEntriesByFormId,
            'evidenceLinksByEntryId' => $evidenceLinksByEntryId,
            'workloadTotalScore' => $workloadTotalScore,
            'workloadView' => $workloadView,
        ];
    }
}
