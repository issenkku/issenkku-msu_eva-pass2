<?php

namespace App\Services;

use App\Models\EvidenceAnswer;
use App\Models\Reports;
use App\Models\SupportCriteria;
use App\Models\SupportScore;
use App\Models\SupportScoreHistory;
use App\Models\User;
use App\Support\SupportScoreRules;
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
     * @return array{old_scores: Collection, new_scores: Collection, support_score_total: float}
     */
    public function persist(
        Reports $report,
        array $items,
        ?User $actor,
        ?string $modifierRole,
        bool $requireReasonForChanges
    ): array {
        $items = collect($items)->map(function (array $item): array {
            $item['evidence_links'] = collect($item['evidence_links'] ?? [])
                ->map(fn ($link) => trim((string) $link))
                ->filter()
                ->unique()
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
                $weightedScore = $achievedScore === null
                    ? null
                    : round(((float) $criterion->weight * $achievedScore) / 100, 2);
                $scoreChanged = $existingScore !== null
                    && ! $this->scoresAreEqual($existingScore->achieved_score, $achievedScore);
                $reason = filled($item['modification_reason'])
                    ? trim((string) $item['modification_reason'])
                    : null;

                if ($scoreChanged && $requireReasonForChanges && $reason === null) {
                    throw ValidationException::withMessages([
                        "support_list.{$index}.modification_reason" => ['กรุณาระบุเหตุผลที่แก้ไขค่าคะแนน'],
                    ]);
                }

                if ($scoreChanged) {
                    SupportScoreHistory::create([
                        'report_id' => $report->id,
                        'support_criteria_id' => $criterion->id,
                        'previous_achieved_score' => $existingScore->achieved_score,
                        'new_achieved_score' => $achievedScore,
                        'previous_weighted_score' => $existingScore->weighted_score,
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

                EvidenceAnswer::query()
                    ->where('report_id', $report->id)
                    ->where('support_criteria_id', $criterion->id)
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

            $this->activityEntryService->persist(
                $report,
                $allowedCriteria,
                $normalizedItems,
                $actor,
                $modifierRole,
                $requireReasonForChanges
            );

            $supportScoreTotal = round((float) SupportScore::query()
                ->where('report_id', $report->id)
                ->sum('weighted_score'), 2);
            $report->update(['support_score_total' => $supportScoreTotal]);

            return [
                'old_scores' => $oldScores,
                'new_scores' => SupportScore::query()
                    ->where('report_id', $report->id)
                    ->get()
                    ->keyBy('support_criteria_id'),
                'support_score_total' => $supportScoreTotal,
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
        $itemIndexByCriterion = [];

        foreach ($items as $index => $item) {
            $criterionId = (int) $item['support_criteria_id'];
            if (! $allowedCriteria->has($criterionId)) {
                throw ValidationException::withMessages([
                    'support_list' => ['พบเกณฑ์สายสนับสนุนที่ไม่อยู่ในรายงานนี้'],
                ]);
            }

            $links = collect($item['evidence_links'] ?? [])
                ->map(fn ($link) => trim((string) $link))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $normalizedItems[$index] = [
                ...$item,
                'support_criteria_id' => $criterionId,
                'achieved_score' => $item['achieved_score'] === null || $item['achieved_score'] === ''
                    ? null
                    : round((float) $item['achieved_score'], 2),
                'modification_reason' => $item['modification_reason'] ?? null,
                'evidence_links' => $links,
            ];
            $itemIndexByCriterion[$criterionId] = $index;
        }

        foreach ($allowedCriteria->where('require_evidence', true) as $criterion) {
            $itemIndex = $itemIndexByCriterion[$criterion->id] ?? null;
            if ($itemIndex === null) {
                throw ValidationException::withMessages([
                    'support_list' => ['กรุณาแนบหลักฐานสำหรับเกณฑ์สายสนับสนุนที่กำหนด'],
                ]);
            }

            if ($normalizedItems[$itemIndex]['evidence_links'] === []) {
                throw ValidationException::withMessages([
                    "support_list.{$itemIndex}.evidence_links" => ['กรุณาแนบหลักฐานสำหรับเกณฑ์นี้'],
                ]);
            }
        }

        if ($requireReasonForChanges) {
            $existingScores = SupportScore::query()
                ->where('report_id', $report->id)
                ->get()
                ->keyBy('support_criteria_id');

            foreach ($normalizedItems as $index => $item) {
                $existingScore = $existingScores->get($item['support_criteria_id']);
                if ($existingScore === null
                    || $this->scoresAreEqual($existingScore->achieved_score, $item['achieved_score'])) {
                    continue;
                }

                if (! filled($item['modification_reason'])) {
                    throw ValidationException::withMessages([
                        "support_list.{$index}.modification_reason" => ['กรุณาระบุเหตุผลที่แก้ไขค่าคะแนน'],
                    ]);
                }
            }
        }

        return $normalizedItems;
    }

    private function scoresAreEqual(mixed $first, mixed $second): bool
    {
        if ($first === null || $second === null) {
            return $first === null && $second === null;
        }

        return round((float) $first, 2) === round((float) $second, 2);
    }
}
