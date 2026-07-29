<?php

namespace App\Services;

use App\Models\EvidenceAnswer;
use App\Models\Reports;
use App\Models\SupportCriteria;
use App\Models\SupportScore;
use App\Models\SupportScoreHistory;
use App\Models\User;
use App\Support\ScoreChangePolicy;
use App\Support\SupportAchievementScore;
use App\Support\SupportScoreRules;
use App\Support\SupportScoreTotal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SupportScoreService
{
    public function __construct(
        private readonly SupportActivityEntryService $activityEntryService
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{old_scores: Collection, new_scores: Collection, support_score_total: float, support_achievement_score: float}
     */
    public function persist(
        Reports $report,
        array $items,
        ?User $actor,
        ?string $modifierRole,
        bool $requireReasonForChanges
    ): array {
        $normalizeLinks = static fn (array $links): array => collect($links)
            ->map(fn ($link) => trim((string) $link))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $items = collect($items)->map(function (array $item) use ($normalizeLinks): array {
            $item['evidence_links'] = $normalizeLinks($item['evidence_links'] ?? []);
            $item['activity_entries'] = collect($item['activity_entries'] ?? [])
                ->map(function (array $entry) use ($normalizeLinks): array {
                    $entry['evidence_links'] = $normalizeLinks($entry['evidence_links'] ?? []);

                    return $entry;
                })
                ->values()
                ->all();

            return $item;
        })->all();

        Validator::make(
            ['support_list' => $items],
            SupportScoreRules::validation()
        )->validate();

        $criteriaVersionId = $report->reportData()->value('criteria_version_id');
        $allowedCriteria = SupportCriteria::query()
            ->with('indicatorItems:id,support_criteria_id,sequence,code')
            ->whereHas('evaluationList', function ($query) use ($criteriaVersionId) {
                $query->where('criteria_version_id', $criteriaVersionId);
            })
            ->get()
            ->keyBy('id');

        $normalizedItems = $this->normalizeAndValidateItems(
            $items,
            $allowedCriteria,
            $report,
            $requireReasonForChanges
        );

        return DB::transaction(function () use (
            $report,
            $actor,
            $modifierRole,
            $requireReasonForChanges,
            $allowedCriteria,
            $normalizedItems
        ): array {
            $oldScores = SupportScore::query()
                ->where('report_id', $report->id)
                ->get()
                ->keyBy('support_criteria_id');

            foreach ($normalizedItems as $index => $item) {
                /** @var SupportCriteria $criterion */
                $criterion = $allowedCriteria->get($item['support_criteria_id']);
                /** @var SupportScore|null $existingScore */
                $existingScore = $oldScores->get($criterion->id);
                $achievedScore = $item['achieved_score'];
                if ($criterion->allow_evaluatee_weight) {
                    if ($achievedScore !== null) {
                        throw ValidationException::withMessages([
                            "support_list.{$index}.achieved_score" => [
                                'เกณฑ์นี้ใช้คะแนนที่ผู้ถูกประเมินกรอกแยกตามโครงการ',
                            ],
                        ]);
                    }

                    continue;
                }

                $weightedScore = $achievedScore === null
                    ? null
                    : round(((float) $criterion->weight * $achievedScore) / 100, 2);
                $scoreChanged = ScoreChangePolicy::numbersDiffer(
                    $existingScore?->achieved_score,
                    $achievedScore
                );
                $reason = ScoreChangePolicy::validatedReason(
                    $scoreChanged,
                    $item['modification_reason'] ?? null,
                    $requireReasonForChanges,
                    "support_list.{$index}.modification_reason"
                );

                if ($scoreChanged) {
                    SupportScoreHistory::create([
                        'report_id' => $report->id,
                        'support_criteria_id' => $criterion->id,
                        'previous_achieved_score' => $existingScore?->achieved_score,
                        'new_achieved_score' => $achievedScore,
                        'previous_weighted_score' => $existingScore?->weighted_score,
                        'new_weighted_score' => $weightedScore,
                        'reason' => $reason,
                        'modifier_user_id' => $actor?->id,
                        'modifier_role' => $modifierRole,
                    ]);
                }

                SupportScore::updateOrCreate(
                    [
                        'report_id' => $report->id,
                        'support_criteria_id' => $criterion->id,
                    ],
                    [
                        'achieved_score' => $achievedScore,
                        'weighted_score' => $weightedScore,
                        'modification_reason' => $reason,
                        'modifier_user_id' => $actor?->id,
                        'modifier_role' => $modifierRole,
                    ]
                );

                if (! $criterion->allow_activity_entries) {
                    EvidenceAnswer::query()
                        ->where('report_id', $report->id)
                        ->where('support_criteria_id', $criterion->id)
                        ->whereNull('support_activity_entry_id')
                        ->delete();

                    foreach ($item['evidence_links'] as $link) {
                        EvidenceAnswer::create([
                            'evaluation_list_id' => $criterion->evaluation_list_id,
                            'support_criteria_id' => $criterion->id,
                            'report_id' => $report->id,
                            'link' => $link,
                        ]);
                    }
                }
            }

            $this->activityEntryService->persist(
                $report,
                $allowedCriteria,
                $normalizedItems,
                $actor,
                $modifierRole,
                $requireReasonForChanges
            );

            $supportScoreTotal = SupportScoreTotal::forReport($report);
            $supportAchievementScore = SupportAchievementScore::calculate($supportScoreTotal);
            $report->update([
                'support_score_total' => $supportScoreTotal,
                'support_achievement_score' => $supportAchievementScore,
            ]);

            return [
                'old_scores' => $oldScores,
                'new_scores' => SupportScore::query()
                    ->where('report_id', $report->id)
                    ->get()
                    ->keyBy('support_criteria_id'),
                'support_score_total' => $supportScoreTotal,
                'support_achievement_score' => $supportAchievementScore,
            ];
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  Collection<int, SupportCriteria>  $allowedCriteria
     * @return array<int, array<string, mixed>>
     */
    private function normalizeAndValidateItems(
        array $items,
        Collection $allowedCriteria,
        Reports $report,
        bool $requireReasonForChanges
    ): array {
        $normalizedItems = [];

        foreach ($items as $index => $item) {
            $criterionId = (int) $item['support_criteria_id'];
            if (! $allowedCriteria->has($criterionId)) {
                throw ValidationException::withMessages([
                    'support_list' => ['พบเกณฑ์สายสนับสนุนที่ไม่อยู่ในรายงานนี้'],
                ]);
            }

            /** @var SupportCriteria $criterion */
            $criterion = $allowedCriteria->get($criterionId);
            $links = collect($item['evidence_links'] ?? [])
                ->map(fn ($link) => trim((string) $link))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $achievedScore = $item['achieved_score'] ?? null;
            $normalizedScore = $achievedScore === null || $achievedScore === ''
                ? null
                : (int) $achievedScore;

            if ($normalizedScore !== null && $normalizedScore > (float) $criterion->target_value) {
                throw ValidationException::withMessages([
                    "support_list.{$index}.achieved_score" => [
                        "ค่าคะแนนที่ได้ต้องไม่เกินระดับค่าเป้าหมาย {$criterion->target_value}",
                    ],
                ]);
            }

            $normalizedItems[$index] = [
                ...$item,
                'support_criteria_id' => $criterionId,
                'achieved_score' => $normalizedScore,
                'modification_reason' => $item['modification_reason'] ?? null,
                'evidence_links' => $links,
            ];
        }

        return $normalizedItems;
    }
}
