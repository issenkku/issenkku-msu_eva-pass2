@props([
    'item',
])

@php
    $criterionId = $item['id'];
    $entries = $item['activity_entries'] ?? [];
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
    @endif
</div>
