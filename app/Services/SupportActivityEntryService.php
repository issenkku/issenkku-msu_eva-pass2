<?php

namespace App\Services;

use App\Models\Reports;
use App\Models\SupportActivityEntry;
use App\Models\SupportActivityEntryHistory;
use App\Models\SupportCriteria;
use App\Models\User;
use App\Rules\HasRichText;
use App\Support\SupportWeightedScore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SupportActivityEntryService
{
    /**
     * @param  Collection<int, SupportCriteria>  $allowedCriteria
     * @param  array<int, array<string, mixed>>  $items
     */
    public function persist(
        Reports $report,
        Collection $allowedCriteria,
        array $items,
        ?User $actor,
        ?string $modifierRole,
        bool $requireReasonForChanges
    ): void {
        foreach ($items as $itemIndex => $item) {
            $criterionId = (int) $item['support_criteria_id'];
            /** @var SupportCriteria|null $criterion */
            $criterion = $allowedCriteria->get($criterionId);
            if (! $criterion) {
                continue;
            }

            $activityEntries = array_values($item['activity_entries'] ?? []);
            if (! $criterion->allow_activity_entries) {
                if ($activityEntries !== []) {
                    throw ValidationException::withMessages([
                        "support_list.{$itemIndex}.activity_entries" => [
                            'เกณฑ์นี้ไม่อนุญาตให้เพิ่มกิจกรรมหรือโครงการ',
                        ],
                    ]);
                }

                continue;
            }

            $existingEntries = SupportActivityEntry::query()
                ->where('report_id', $report->id)
                ->where('support_criteria_id', $criterionId)
                ->orderBy('sequence')
                ->orderBy('id')
                ->get();
            $existingById = $existingEntries->keyBy('id');

            foreach ($activityEntries as $entryIndex => &$entryData) {
                $entryData['support_indicator_item_id'] = $this->validateIndicatorAssignment(
                    $criterion,
                    $entryData,
                    $itemIndex,
                    $entryIndex,
                    $existingById
                );
                $entryData = [
                    ...$entryData,
                    ...$this->scoreAttributes($criterion, $entryData, $itemIndex, $entryIndex),
                ];
            }
            unset($entryData);

            if ($requireReasonForChanges) {
                $this->persistReviewerChanges(
                    $existingEntries,
                    $activityEntries,
                    $itemIndex,
                    $actor,
                    $modifierRole
                );

                continue;
            }

            $this->persistEvaluateeChanges(
                $report,
                $criterion,
                $existingEntries,
                $activityEntries,
                $itemIndex,
                $actor
            );
        }
    }

    /**
     * @param  Collection<int, SupportActivityEntry>  $existingEntries
     * @param  array<int, array<string, mixed>>  $activityEntries
     */
    private function persistEvaluateeChanges(
        Reports $report,
        SupportCriteria $criterion,
        Collection $existingEntries,
        array $activityEntries,
        int|string $itemIndex,
        ?User $actor
    ): void {
        $existingById = $existingEntries->keyBy('id');
        $keptIds = [];

        foreach ($activityEntries as $entryIndex => $entryData) {
            $entryId = isset($entryData['id']) ? (int) $entryData['id'] : null;
            $content = (string) $entryData['content'];
            $indicatorItemId = $entryData['support_indicator_item_id'];
            $scoreAttributes = [
                'indicator' => $entryData['indicator'],
                'weight' => $entryData['weight'],
                'achieved_score' => $entryData['achieved_score'],
                'weighted_score' => $entryData['weighted_score'],
            ];

            if ($entryId !== null) {
                /** @var SupportActivityEntry|null $entry */
                $entry = $existingById->get($entryId);
                if (! $entry || in_array($entryId, $keptIds, true)) {
                    throw ValidationException::withMessages([
                        "support_list.{$itemIndex}.activity_entries.{$entryIndex}.id" => [
                            'ไม่พบรายการกิจกรรมในรายงานและเกณฑ์นี้',
                        ],
                    ]);
                }

                if ($entry->support_indicator_item_id !== $indicatorItemId) {
                    throw ValidationException::withMessages([
                        "support_list.{$itemIndex}.activity_entries.{$entryIndex}.support_indicator_item_id" => [
                            'ไม่สามารถย้ายโครงการเดิมไปยังตัวชี้วัดย่อยอื่นได้',
                        ],
                    ]);
                }

                $entry->update([
                    'sequence' => $entryIndex + 1,
                    'content' => $content,
                    ...$scoreAttributes,
                    'updated_by' => $actor?->id,
                ]);
                $this->replaceEvidence(
                    $entry,
                    $criterion,
                    $entryData['evidence_links'] ?? []
                );
                $keptIds[] = $entryId;

                continue;
            }

            $created = SupportActivityEntry::create([
                'report_id' => $report->id,
                'support_criteria_id' => $criterion->id,
                'support_indicator_item_id' => $indicatorItemId,
                'sequence' => $entryIndex + 1,
                'content' => $content,
                ...$scoreAttributes,
                'created_by' => $actor?->id,
                'updated_by' => $actor?->id,
            ]);
            $this->replaceEvidence(
                $created,
                $criterion,
                $entryData['evidence_links'] ?? []
            );
            $keptIds[] = $created->id;
        }

        $existingEntries
            ->reject(fn (SupportActivityEntry $entry) => in_array($entry->id, $keptIds, true))
            ->each(fn (SupportActivityEntry $entry) => $entry->delete());
    }

    /** @param array<int, string> $links */
    private function replaceEvidence(
        SupportActivityEntry $entry,
        SupportCriteria $criterion,
        array $links
    ): void {
        $entry->evidenceAnswers()->delete();

        foreach ($links as $link) {
            $entry->evidenceAnswers()->create([
                'evaluation_list_id' => $criterion->evaluation_list_id,
                'support_criteria_id' => $criterion->id,
                'report_id' => $entry->report_id,
                'link' => $link,
            ]);
        }
    }

    /**
     * @param  Collection<int, SupportActivityEntry>  $existingEntries
     * @param  array<int, array<string, mixed>>  $activityEntries
     */
    private function persistReviewerChanges(
        Collection $existingEntries,
        array $activityEntries,
        int|string $itemIndex,
        ?User $actor,
        ?string $modifierRole
    ): void {
        $existingIds = $existingEntries->pluck('id')->map(fn ($id) => (int) $id)->all();
        $submittedIds = collect($activityEntries)
            ->map(fn (array $entry) => isset($entry['id']) ? (int) $entry['id'] : null)
            ->all();

        if ($submittedIds !== $existingIds) {
            throw ValidationException::withMessages([
                "support_list.{$itemIndex}.activity_entries" => [
                    'ผู้ประเมินไม่สามารถเพิ่ม ลบ หรือจัดลำดับรายการกิจกรรมได้',
                ],
            ]);
        }

        foreach ($activityEntries as $entryIndex => $entryData) {
            /** @var SupportActivityEntry $entry */
            $entry = $existingEntries[$entryIndex];
            if ($entry->support_indicator_item_id !== $entryData['support_indicator_item_id']) {
                throw ValidationException::withMessages([
                    "support_list.{$itemIndex}.activity_entries.{$entryIndex}.support_indicator_item_id" => [
                        'ผู้ประเมินไม่สามารถย้ายโครงการไปยังตัวชี้วัดย่อยอื่นได้',
                    ],
                ]);
            }

            $content = (string) $entryData['content'];
            $previous = [
                'content' => $entry->content,
                'indicator' => $entry->indicator,
                'weight' => filled($entry->weight) ? (float) $entry->weight : null,
                'achieved_score' => filled($entry->achieved_score) ? (float) $entry->achieved_score : null,
                'weighted_score' => filled($entry->weighted_score) ? (float) $entry->weighted_score : null,
            ];
            $next = [
                'content' => $content,
                'indicator' => $entryData['indicator'],
                'weight' => $entryData['weight'],
                'achieved_score' => $entryData['achieved_score'],
                'weighted_score' => $entryData['weighted_score'],
            ];
            if ($previous === $next) {
                continue;
            }

            $reason = trim((string) ($entryData['modification_reason'] ?? ''));
            if ($reason === '') {
                throw ValidationException::withMessages([
                    "support_list.{$itemIndex}.activity_entries.{$entryIndex}.modification_reason" => [
                        'กรุณาระบุเหตุผลที่แก้ไขกิจกรรมหรือโครงการ',
                    ],
                ]);
            }

            SupportActivityEntryHistory::create([
                'support_activity_entry_id' => $entry->id,
                'previous_content' => $entry->content,
                'new_content' => $content,
                'previous_indicator' => $entry->indicator,
                'new_indicator' => $entryData['indicator'],
                'previous_weight' => $entry->weight,
                'new_weight' => $entryData['weight'],
                'previous_achieved_score' => $entry->achieved_score,
                'new_achieved_score' => $entryData['achieved_score'],
                'previous_weighted_score' => $entry->weighted_score,
                'new_weighted_score' => $entryData['weighted_score'],
                'reason' => $reason,
                'modified_by' => $actor?->id,
                'modified_by_role' => $modifierRole,
            ]);
            $entry->update([
                'content' => $content,
                'indicator' => $entryData['indicator'],
                'weight' => $entryData['weight'],
                'achieved_score' => $entryData['achieved_score'],
                'weighted_score' => $entryData['weighted_score'],
                'updated_by' => $actor?->id,
            ]);
        }
    }

    /** @param array<string, mixed> $entryData */
    private function validateIndicatorAssignment(
        SupportCriteria $criterion,
        array $entryData,
        int|string $itemIndex,
        int $entryIndex,
        Collection $existingById
    ): ?int {
        $indicatorItemId = filled($entryData['support_indicator_item_id'] ?? null)
            ? (int) $entryData['support_indicator_item_id']
            : null;
        $errorKey = "support_list.{$itemIndex}.activity_entries.{$entryIndex}.support_indicator_item_id";

        if ($criterion->group_activity_entries_by_indicator) {
            if (! $indicatorItemId
                || ! $criterion->indicatorItems->contains('id', $indicatorItemId)) {
                throw ValidationException::withMessages([
                    $errorKey => ['กรุณาเลือกตัวชี้วัดย่อยที่อยู่ในเกณฑ์นี้'],
                ]);
            }

            return $indicatorItemId;
        }

        if ($indicatorItemId === null) {
            return null;
        }

        $entryId = filled($entryData['id'] ?? null)
            ? (int) $entryData['id']
            : null;
        /** @var SupportActivityEntry|null $existing */
        $existing = $entryId ? $existingById->get($entryId) : null;

        if (! $existing
            || (int) $existing->support_indicator_item_id !== $indicatorItemId
            || ! $criterion->indicatorItems->contains('id', $indicatorItemId)) {
            throw ValidationException::withMessages([
                $errorKey => ['โหมดนี้อนุญาตให้คงตัวชี้วัดย่อยเดิมของโครงการที่มีอยู่เท่านั้น'],
            ]);
        }

        return $indicatorItemId;
    }

    /**
     * @param  array<string, mixed>  $entryData
     * @return array{indicator:?string,weight:?float,achieved_score:?int,weighted_score:?float}
     */
    private function scoreAttributes(
        SupportCriteria $criterion,
        array $entryData,
        int|string $itemIndex,
        int $entryIndex
    ): array {
        $base = "support_list.{$itemIndex}.activity_entries.{$entryIndex}";
        $payload = [
            'support_list' => [
                $itemIndex => [
                    'activity_entries' => [
                        $entryIndex => $entryData,
                    ],
                ],
            ],
        ];

        Validator::make($payload, [
            "{$base}.indicator" => $criterion->allow_evaluatee_indicator
                ? ['required', 'string', new HasRichText]
                : ['prohibited'],
            "{$base}.weight" => $criterion->allow_evaluatee_weight
                ? ['required', 'numeric', 'gt:0', 'max:100', 'decimal:0,2']
                : ['prohibited'],
            "{$base}.achieved_score" => $criterion->allow_evaluatee_weight
                ? ['required', 'numeric', 'multiple_of:1', 'between:1,5', 'max:'.$criterion->target_value]
                : ['prohibited'],
        ])->validate();

        $indicator = $criterion->allow_evaluatee_indicator
            ? (string) $entryData['indicator']
            : null;
        $weight = $criterion->allow_evaluatee_weight
            ? round((float) $entryData['weight'], 2)
            : null;
        $achievedScore = $criterion->allow_evaluatee_weight
            ? (int) $entryData['achieved_score']
            : null;

        return [
            'indicator' => $indicator,
            'weight' => $weight,
            'achieved_score' => $achievedScore,
            'weighted_score' => $criterion->allow_evaluatee_weight
                ? SupportWeightedScore::calculate($weight, $achievedScore)
                : null,
        ];
    }
}
