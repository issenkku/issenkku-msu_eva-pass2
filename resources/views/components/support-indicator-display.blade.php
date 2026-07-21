@props(['item'])

@if (!empty($item['group_activity_entries_by_indicator']))
    <ol class="space-y-3">
        @foreach ($item['indicator_items'] ?? [] as $indicatorItem)
            <li class="break-words">
                <span class="font-semibold text-amber-900">{{ $indicatorItem['code'] }}</span>
                <div class="support-criteria-rich-text mt-1">
                    {!! \App\Support\SafeHtml::richText($indicatorItem['description'] ?? '') !!}
                </div>
            </li>
        @endforeach
    </ol>
@else
    <div class="support-criteria-rich-text break-words leading-6">
        {!! \App\Support\SafeHtml::richText($item['indicator'] ?? '') !!}
    </div>
@endif
