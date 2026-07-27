@props([
    'item',
    'showHeading' => true,
])

@php
    $criterionId = $item['id'];
    $entries = $item['activity_entries'] ?? [];
@endphp

<div class="space-y-2">
    @if ($showHeading)
        <div class="support-criteria-rich-text font-semibold text-slate-900"
            data-support-admin-heading>
            {!! \App\Support\SafeHtml::richText($item['activity_name'] ?? '') !!}
        </div>
    @endif

    @if (!empty($item['allow_activity_entries']))
        <div data-support-activity-list="{{ $criterionId }}"
            data-support-activity-grouped="{{ !empty($item['group_activity_entries_by_indicator']) ? '1' : '0' }}">
            @if (!empty($item['group_activity_entries_by_indicator']))
                <div class="space-y-3">
                    @foreach ($item['indicator_items'] ?? [] as $indicatorItem)
                        @php
                            $groupEntries = collect($entries)->filter(
                                fn ($entry) => (int) ($entry['support_indicator_item_id'] ?? 0) === (int) $indicatorItem['id']
                            );
                            $indicatorReference = preg_split(
                                '/\s+/u',
                                trim((string) ($indicatorItem['code'] ?? '')),
                                2
                            )[0] ?? '';
                        @endphp
                        <section data-support-display-group="{{ $indicatorItem['id'] }}">
                            <p class="text-xs font-semibold text-amber-800">ข้อ {{ $indicatorReference }}</p>
                            <div data-support-display-group-entries>
                                @if ($groupEntries->isNotEmpty())
                                    <div class="space-y-2">
                                        @foreach ($groupEntries->values() as $entryIndex => $entry)
                                            <div class="flex gap-2">
                                                <span data-support-grouped-project-number
                                                    class="inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-semibold text-amber-800">
                                                    {{ $entryIndex + 1 }}
                                                </span>
                                                <div class="support-criteria-rich-text min-w-0 break-words text-sm font-normal text-amber-800">
                                                    {!! \App\Support\SafeHtml::richText($entry['content'] ?? '') !!}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs font-normal text-slate-400">ยังไม่มีโครงการในข้อนี้</p>
                                @endif
                            </div>
                        </section>
                    @endforeach
                </div>
            @elseif (!empty($entries))
                <div class="space-y-3">
                    @foreach ($entries as $entryIndex => $entry)
                        <div class="flex gap-2">
                            <span class="inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-semibold text-amber-800">
                                {{ $entryIndex + 1 }}
                            </span>
                            <div class="support-criteria-rich-text min-w-0 break-words text-sm font-normal text-amber-800">
                                {!! \App\Support\SafeHtml::richText($entry['content'] ?? '') !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs font-normal text-slate-400">ยังไม่มีกิจกรรม/โครงการเพิ่มเติม</p>
            @endif
        </div>
    @endif
</div>
