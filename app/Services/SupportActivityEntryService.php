<?php

namespace App\Services;

use App\Models\Reports;
use App\Models\SupportActivityEntry;
use App\Models\SupportActivityEntryHistory;
use App\Models\SupportCriteria;
use App\Models\User;
use Illuminate\Support\Collection;
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

            foreach ($activityEntries as $entryIndex => &$entryData) {
                $entryData['support_indicator_item_id'] = $this->validateIndicatorAssignment(
                    $criterion,
                    $entryData,
                    $itemIndex,
                    $entryIndex
                );
            }
            unset($entryData);

            $existingEntries = SupportActivityEntry::query()
                ->where('report_id', $report->id)
                ->where('support_criteria_id', $criterionId)
                ->orderBy('sequence')
                ->orderBy('id')
                ->get();

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
            if ($content === $entry->content) {
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
                'reason' => $reason,
                'modified_by' => $actor?->id,
                'modified_by_role' => $modifierRole,
            ]);
            $entry->update([
                'content' => $content,
                'updated_by' => $actor?->id,
            ]);
        }
    }

    /** @param array<string, mixed> $entryData */
    private function validateIndicatorAssignment(
        SupportCriteria $criterion,
        array $entryData,
        int|string $itemIndex,
        int $entryIndex
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

        if ($indicatorItemId !== null) {
            throw ValidationException::withMessages([
                $errorKey => ['เกณฑ์นี้ไม่ได้แบ่งโครงการตามตัวชี้วัดย่อย'],
            ]);
        }

        return null;
    }
}
