@props([
    'item',
    'readonly' => false,
    'activityEntryRole' => 'readonly',
])

@php
    $criterionId = $item['id'];
    $entries = $item['activity_entries'] ?? [];
    $canEditActivities = !$readonly
        && !empty($item['allow_activity_entries'])
        && in_array($activityEntryRole, ['evaluatee', 'reviewer'], true);
@endphp

<div class="space-y-2">
    <div class="support-criteria-rich-text font-medium text-slate-900">
        {!! \App\Support\SafeHtml::richText($item['activity_name'] ?? '') !!}
    </div>

    @if (!empty($item['allow_activity_entries']))
        <div data-support-activity-list="{{ $criterionId }}">
            @if (!empty($entries))
                <ol class="list-decimal space-y-2 pl-5 text-sm font-normal text-slate-700">
                    @foreach ($entries as $entry)
                        <li class="support-criteria-rich-text break-words">
                            {!! \App\Support\SafeHtml::richText($entry['content'] ?? '') !!}
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="text-xs font-normal text-slate-400">ยังไม่มีกิจกรรม/โครงการเพิ่มเติม</p>
            @endif
        </div>

        @if ($canEditActivities)
            <button type="button" data-support-activity-open="{{ $criterionId }}"
                class="rounded-md border border-amber-300 bg-amber-50 px-2.5 py-1.5 text-xs font-semibold text-amber-900 transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-400">
                {{ $activityEntryRole === 'evaluatee' ? 'จัดการกิจกรรม/โครงการ' : 'แก้ไขกิจกรรม/โครงการ' }}
            </button>
        @endif
    @endif
</div>
