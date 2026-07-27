<?php

namespace App\Services;

use App\Models\SupportCriteria;
use Illuminate\Validation\ValidationException;

class SupportIndicatorItemService
{
    /** @param array<int, array<string, mixed>> $items */
    public function sync(
        SupportCriteria $criterion,
        bool $grouped,
        array $items,
        bool $wasGrouped
    ): void {
        $existing = $criterion->indicatorItems()
            ->withCount('activityEntries')
            ->get()
            ->keyBy('id');

        if (! $grouped) {
            return;
        }

        if (! $wasGrouped
            && $criterion->activityEntries()
                ->whereNull('support_indicator_item_id')
                ->exists()) {
            throw ValidationException::withMessages([
                'support_criterias' => [
                    'ไม่สามารถเปิดการแบ่งตามตัวชี้วัดย่อยได้ เนื่องจากมีโครงการที่ยังไม่ได้สังกัดตัวชี้วัดย่อย',
                ],
            ]);
        }

        $keptIds = [];

        foreach (array_values($items) as $index => $data) {
            $id = isset($data['support_indicator_item_id'])
                ? (int) $data['support_indicator_item_id']
                : null;
            $attributes = [
                'sequence' => $index + 1,
                'code' => trim((string) $data['code']),
            ];

            if ($id) {
                $item = $existing->get($id);

                if (! $item) {
                    throw ValidationException::withMessages([
                        'support_criterias' => ['ไม่พบตัวชี้วัดย่อยในเกณฑ์นี้'],
                    ]);
                }

                $item->update($attributes);
            } else {
                $item = $criterion->indicatorItems()->create($attributes);
            }

            $keptIds[] = $item->id;
        }

        $removed = $existing->reject(
            fn ($item) => in_array($item->id, $keptIds, true)
        );

        if ($removed->contains(fn ($item) => $item->activity_entries_count > 0)) {
            throw ValidationException::withMessages([
                'support_criterias' => ['ไม่สามารถลบตัวชี้วัดย่อยที่มีโครงการอ้างอิงอยู่'],
            ]);
        }

        $removed->each->delete();
    }
}
