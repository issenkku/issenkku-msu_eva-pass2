@props([
    'item',
    'entry',
    'entryIndex',
    'canEditActivities' => false,
    'activityEntryRole' => 'readonly',
])

@php
    $safeActivityContent = (string) \App\Support\SafeHtml::richText($entry['content'] ?? '');
@endphp

<article class="rounded-lg border border-slate-200 bg-slate-50 p-3"
    data-support-activity-entry
    data-support-activity-entry-id="{{ $entry['id'] }}"
    data-support-indicator-item-id="{{ $entry['support_indicator_item_id'] ?? '' }}">
    @if ($canEditActivities)
        <input type="hidden"
            name="support_list[{{ $item['id'] }}][activity_entries][{{ $entryIndex }}][id]"
            value="{{ $entry['id'] }}" data-support-activity-id>
        <input type="hidden"
            name="support_list[{{ $item['id'] }}][activity_entries][{{ $entryIndex }}][support_indicator_item_id]"
            value="{{ $entry['support_indicator_item_id'] ?? '' }}"
            data-support-activity-indicator-id>
        <label class="block text-sm font-semibold text-slate-700">
            <span data-support-activity-entry-label>รายการ {{ $entryIndex + 1 }}</span>
            <textarea rows="6"
                name="support_list[{{ $item['id'] }}][activity_entries][{{ $entryIndex }}][content]"
                class="support-activity-richtext mt-2 block w-full rounded-lg border border-slate-300 p-2.5"
                data-support-activity-content
                data-original-content="{{ $safeActivityContent }}">{{ $safeActivityContent }}</textarea>
        </label>

        @if ($activityEntryRole === 'evaluatee')
            <button type="button" data-remove-support-activity
                class="mt-2 rounded-lg bg-red-50 px-3 py-1.5 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                ลบรายการ
            </button>
        @elseif ($activityEntryRole === 'reviewer')
            <label class="mt-3 block text-sm font-semibold text-slate-700">
                เหตุผลที่แก้ไขกิจกรรม/โครงการ
                <textarea rows="2" maxlength="2000"
                    name="support_list[{{ $item['id'] }}][activity_entries][{{ $entryIndex }}][modification_reason]"
                    data-support-activity-reason
                    class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900"
                    placeholder="ระบุเมื่อแก้ไขข้อความเดิม"></textarea>
            </label>
        @endif
    @else
        <div class="support-criteria-rich-text text-sm text-slate-800">
            {!! \App\Support\SafeHtml::richText($entry['content'] ?? '') !!}
        </div>
    @endif

    @if (!empty($entry['histories']))
        <details class="mt-3 rounded-lg border border-slate-200 bg-white p-3">
            <summary class="cursor-pointer text-sm font-semibold text-slate-700">
                ประวัติการแก้ไขกิจกรรม/โครงการ ({{ count($entry['histories']) }})
            </summary>
            <div class="mt-3 space-y-3">
                @foreach ($entry['histories'] as $activityHistory)
                    <div class="rounded-lg bg-slate-50 p-3 text-sm text-slate-700">
                        <p class="font-medium">ข้อความเดิม</p>
                        <div class="support-criteria-rich-text mt-1">
                            {!! \App\Support\SafeHtml::richText($activityHistory['previous_content'] ?? '') !!}
                        </div>
                        <p class="mt-2 font-medium">ข้อความใหม่</p>
                        <div class="support-criteria-rich-text mt-1">
                            {!! \App\Support\SafeHtml::richText($activityHistory['new_content'] ?? '') !!}
                        </div>
                        <p class="mt-2">เหตุผล: {{ $activityHistory['reason'] ?? '-' }}</p>
                        <p class="mt-2 text-xs text-slate-500">
                            แก้ไขโดย {{ $activityHistory['modified_by_name'] ?: '-' }}
                            @if (!empty($activityHistory['modified_by_role']))
                                ({{ $activityHistory['modified_by_role'] }})
                            @endif
                            · {{ $activityHistory['created_at'] ?? '-' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </details>
    @endif
</article>
