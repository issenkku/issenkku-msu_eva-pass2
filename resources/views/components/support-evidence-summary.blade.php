@props(['item'])

@php
    $activityEvidenceGroups = collect($item['activity_entries'] ?? [])
        ->map(function (array $entry, int $index): array {
            return [
                'id' => $entry['id'] ?? "new-{$index}",
                'label' => 'รายการ '.($index + 1),
                'links' => array_values(array_filter($entry['evidence_links'] ?? [])),
            ];
        })
        ->filter(fn (array $group): bool => $group['links'] !== [])
        ->values();
    $criterionEvidenceLinks = array_values(array_filter($item['evidence_links'] ?? []));
@endphp

<div class="space-y-2 text-left" data-support-evidence-list="{{ $item['id'] }}">
    @if (!empty($item['allow_activity_entries']))
        @forelse ($activityEvidenceGroups as $group)
            <div class="space-y-1" data-support-activity-evidence-list="{{ $group['id'] }}">
                @if ($activityEvidenceGroups->count() > 1)
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
