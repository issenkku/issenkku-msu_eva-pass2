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
                <span class="block text-xs font-semibold text-slate-500">{{ $group['label'] }}</span>
                @foreach ($group['links'] as $link)
                    <a href="{{ $link }}" target="_blank" rel="noopener noreferrer"
                        class="block break-all text-sm font-semibold text-blue-700 underline decoration-blue-300 underline-offset-4 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        {{ $link }}
                    </a>
                @endforeach
            </div>
        @empty
            <span class="text-slate-400">ไม่มีหลักฐาน</span>
        @endforelse
    @else
        @forelse ($criterionEvidenceLinks as $link)
            <a href="{{ $link }}" target="_blank" rel="noopener noreferrer"
                class="block break-all text-sm font-semibold text-blue-700 underline decoration-blue-300 underline-offset-4 focus:outline-none focus:ring-2 focus:ring-blue-400">
                {{ $link }}
            </a>
        @empty
            <span class="text-slate-400">ไม่มีหลักฐาน</span>
        @endforelse
    @endif
</div>
