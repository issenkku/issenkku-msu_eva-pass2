<?php

namespace App\Support;

use App\Models\EvidenceAnswer;
use App\Models\Reports;
use App\Models\SupportActivityEntry;
use App\Models\SupportActivityEntryHistory;
use App\Models\SupportCriteria;
use App\Models\SupportScore;
use App\Models\SupportScoreHistory;

class SupportCriteriaReadModel
{
    /**
     * @return array<int, array<int, array<string, mixed>>>
     */
    public function forReport(Reports $report): array
    {
        $report->loadMissing('reportData');
        $criteriaVersionId = $report->reportData?->criteria_version_id;

        if (! $criteriaVersionId) {
            return [];
        }

        $criteria = SupportCriteria::query()
            ->with('indicatorItems:id,support_criteria_id,sequence,code')
            ->whereHas('evaluationList', function ($query) use ($criteriaVersionId) {
                $query->where('criteria_version_id', $criteriaVersionId);
            })
            ->orderBy('evaluation_list_id')
            ->orderBy('sequence')
            ->get();

        if ($criteria->isEmpty()) {
            return [];
        }

        $criterionIds = $criteria->pluck('id');
        $scores = SupportScore::query()
            ->where('report_id', $report->id)
            ->whereIn('support_criteria_id', $criterionIds)
            ->get()
            ->keyBy('support_criteria_id');
        $histories = SupportScoreHistory::with('modifierUser:id,prefix,name')
            ->where('report_id', $report->id)
            ->whereIn('support_criteria_id', $criterionIds)
            ->latest()
            ->get()
            ->groupBy('support_criteria_id');
        $evidence = EvidenceAnswer::query()
            ->where('report_id', $report->id)
            ->whereIn('support_criteria_id', $criterionIds)
            ->whereNotNull('support_criteria_id')
            ->get()
            ->groupBy('support_criteria_id');
        $activityEntries = SupportActivityEntry::query()
            ->with('histories.modifierUser:id,prefix,name')
            ->where('report_id', $report->id)
            ->whereIn('support_criteria_id', $criterionIds)
            ->orderBy('support_criteria_id')
            ->orderBy('sequence')
            ->orderBy('id')
            ->get()
            ->groupBy('support_criteria_id');

        return $criteria
            ->groupBy('evaluation_list_id')
            ->map(function ($listCriteria) use ($scores, $histories, $evidence, $activityEntries) {
                return $listCriteria->map(function (SupportCriteria $criterion) use ($scores, $histories, $evidence, $activityEntries) {
                    $score = $scores->get($criterion->id);

                    return [
                        'id' => $criterion->id,
                        'sequence' => $criterion->sequence,
                        'activity_name' => $criterion->activity_name,
                        'indicator' => $criterion->indicator,
                        'target_value' => $criterion->target_value,
                        'weight' => $criterion->weight,
                        'require_evidence' => (bool) $criterion->require_evidence,
                        'allow_activity_entries' => (bool) $criterion->allow_activity_entries,
                        'group_activity_entries_by_indicator' => (bool) $criterion->group_activity_entries_by_indicator,
                        'indicator_items' => $criterion->indicatorItems->map(fn ($item) => [
                            'id' => $item->id,
                            'sequence' => $item->sequence,
                            'code' => $item->code,
                        ])->values()->all(),
                        'activity_entries' => ! $criterion->allow_activity_entries
                            ? []
                            : ($activityEntries->get($criterion->id) ?? collect())
                                ->map(function (SupportActivityEntry $entry) {
                                    return [
                                        'id' => $entry->id,
                                        'sequence' => $entry->sequence,
                                        'support_indicator_item_id' => $entry->support_indicator_item_id,
                                        'content' => $entry->content,
                                        'histories' => $entry->histories
                                            ->map(function (SupportActivityEntryHistory $history) {
                                                return [
                                                    'previous_content' => $history->previous_content,
                                                    'new_content' => $history->new_content,
                                                    'reason' => $history->reason,
                                                    'modified_by_name' => $history->modifierUser?->display_name
                                                        ?? $history->modifierUser?->name
                                                        ?? '',
                                                    'modified_by_role' => $history->modified_by_role ?? '',
                                                    'created_at' => optional($history->created_at)->format('d/m/Y H:i'),
                                                ];
                                            })
                                            ->values()
                                            ->all(),
                                    ];
                                })
                                ->values()
                                ->all(),
                        'achieved_score' => $score?->achieved_score,
                        'weighted_score' => $score?->weighted_score,
                        'modification_reason' => $score?->modification_reason,
                        'evidence_links' => ($evidence->get($criterion->id) ?? collect())
                            ->pluck('link')
                            ->filter()
                            ->unique()
                            ->values()
                            ->all(),
                        'histories' => ($histories->get($criterion->id) ?? collect())
                            ->map(function (SupportScoreHistory $history) {
                                return [
                                    'previous_achieved_score' => $history->previous_achieved_score,
                                    'new_achieved_score' => $history->new_achieved_score,
                                    'previous_weighted_score' => $history->previous_weighted_score,
                                    'new_weighted_score' => $history->new_weighted_score,
                                    'reason' => $history->reason,
                                    'modified_by_name' => $history->modifierUser?->display_name
                                        ?? $history->modifierUser?->name
                                        ?? '',
                                    'modified_by_role' => $history->modifier_role ?? '',
                                    'created_at' => optional($history->created_at)->format('d/m/Y H:i'),
                                ];
                            })
                            ->values()
                            ->all(),
                    ];
                })->values()->all();
            })
            ->all();
    }
}
