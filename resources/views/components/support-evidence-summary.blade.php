@props([
    'item',
    'entry' => null,
    'entryIndex' => null,
    'entries' => null,
    'evidenceGroup' => null,
])

@php
    $showsSingleEntry = $entryIndex !== null;
    $activityEntries = $showsSingleEntry
        ? (is_array($entry) ? [$entry] : [])
        : ($entries !== null ? array_values($entries) : array_values($item['activity_entries'] ?? []));
    $activityEvidenceGroups = collect($activityEntries)
        ->map(function (array $activityEntry, int $index): array {
            $sequence = $activityEntry['sequence'] ?? ($index + 1);
            $content = \Illuminate\Support\Str::limit(
                \App\Support\SafeHtml::plainText($activityEntry['content'] ?? ''),
                60
            );

            return [
                'id' => $activityEntry['id'] ?? "new-{$index}",
                'label' => 'รายการ '.$sequence.($content !== '' ? ' · '.$content : ''),
                'links' => array_values(array_filter($activityEntry['evidence_links'] ?? [])),
            ];
        })
        ->filter(fn (array $group): bool => $group['links'] !== [])
        ->values();
    $criterionEvidenceLinks = array_values(array_filter($item['evidence_links'] ?? []));
@endphp

<div class="space-y-2 text-left" data-support-evidence-list="{{ $item['id'] }}"
    @if ($showsSingleEntry) data-support-entry-evidence-index="{{ $entryIndex }}" @endif
    @if ($showsSingleEntry && is_array($entry)) data-support-entry-evidence-id="{{ $entry['id'] ?? 'new-'.$entryIndex }}" @endif
    @if ($evidenceGroup !== null) data-support-evidence-group="{{ $evidenceGroup }}" @endif>
    @if (!empty($item['allow_activity_entries']))
        @forelse ($activityEvidenceGroups as $group)
            <div class="space-y-1" data-support-activity-evidence-list="{{ $group['id'] }}">
                @if (!$showsSingleEntry && $activityEvidenceGroups->count() > 1)
                    <span class="block text-xs font-semibold text-slate-500">{{ $group['label'] }}</span>
                @endif
                @foreach ($group['links'] as $linkIndex => $link)
                    <x-support-evidence-link
                        :href="$link"
                        :label="count($group['links']) > 1 ? 'ไฟล์ '.($linkIndex + 1) : 'เปิดดู'"
                        :aria-label="'เปิดหลักฐาน '.($linkIndex + 1).' สำหรับ'.$group['label']" />
                @endforeach
            </div>
        @empty
            <span class="text-slate-400">ไม่มีหลักฐาน</span>
        @endforelse
    @else
        @forelse ($criterionEvidenceLinks as $linkIndex => $link)
            <x-support-evidence-link
                :href="$link"
                :label="count($criterionEvidenceLinks) > 1 ? 'ไฟล์ '.($linkIndex + 1) : 'เปิดดู'"
                :aria-label="'เปิดหลักฐาน '.($linkIndex + 1)" />
        @empty
            <span class="text-slate-400">ไม่มีหลักฐาน</span>
        @endforelse
    @endif
</div>
