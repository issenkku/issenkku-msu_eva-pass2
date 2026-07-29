@props([
    'item',
    'entry',
    'entryIndex',
    'canEditActivities' => false,
    'activityEntryRole' => 'readonly',
    'evidenceEditable' => false,
])

@php
    $safeActivityContent = (string) \App\Support\SafeHtml::richText($entry['content'] ?? '');
    $canEditEvidence = $canEditActivities
        && $evidenceEditable
        && $activityEntryRole === 'evaluatee';
    $allowEvaluateeIndicator = !empty($item['allow_evaluatee_indicator']);
    $allowEvaluateeWeight = !empty($item['allow_evaluatee_weight']);
    $evidenceLinks = array_values(array_filter($entry['evidence_links'] ?? []));
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

        @if ($allowEvaluateeIndicator)
            <label class="mt-4 block text-sm font-semibold text-slate-700">
                ตัวชี้วัด/เกณฑ์การประเมิน
                <textarea rows="6"
                    name="support_list[{{ $item['id'] }}][activity_entries][{{ $entryIndex }}][indicator]"
                    class="support-activity-richtext mt-2 block w-full rounded-lg border border-slate-300 p-2.5"
                    data-support-entry-indicator
                    data-original-indicator="{{ (string) \App\Support\SafeHtml::richText($entry['indicator'] ?? '') }}">{{ (string) \App\Support\SafeHtml::richText($entry['indicator'] ?? '') }}</textarea>
            </label>
        @endif

        @if ($allowEvaluateeWeight)
            @php
                $entryScoreMaximum = min(5, (float) $item['target_value']);
                $entryScoreHelpId = "support-entry-score-help-{$item['id']}-{$entryIndex}";
                $entryScoreErrorId = "support-entry-score-error-{$item['id']}-{$entryIndex}";
            @endphp
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <label class="block text-sm font-semibold text-slate-700">
                    น้ำหนัก
                    <input type="number" min="0.01" max="100" step="0.01"
                        name="support_list[{{ $item['id'] }}][activity_entries][{{ $entryIndex }}][weight]"
                        value="{{ $entry['weight'] ?? '' }}"
                        data-support-entry-weight
                        data-original-weight="{{ $entry['weight'] ?? '' }}"
                        class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900">
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    ค่าคะแนนที่ได้
                    <input type="number" min="1" max="{{ $entryScoreMaximum }}" step="1"
                        name="support_list[{{ $item['id'] }}][activity_entries][{{ $entryIndex }}][achieved_score]"
                        value="{{ $entry['achieved_score'] ?? '' }}"
                        aria-describedby="{{ $entryScoreHelpId }} {{ $entryScoreErrorId }}"
                        data-support-entry-score
                        data-support-entry-target="{{ $item['target_value'] }}"
                        data-original-score="{{ $entry['achieved_score'] ?? '' }}"
                        class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900">
                    <span id="{{ $entryScoreHelpId }}" data-support-score-help
                        class="mt-1 block text-xs font-normal text-slate-500">
                        กรอกเฉพาะจำนวนเต็มตั้งแต่ 1–5 และต้องไม่เกินระดับค่าเป้าหมาย {{ $item['target_value'] }}
                    </span>
                    <span id="{{ $entryScoreErrorId }}" data-support-score-error
                        class="mt-1 hidden text-xs font-normal text-red-600" aria-live="polite"></span>
                </label>
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                    <span class="text-xs font-semibold text-amber-700">คะแนนถ่วงน้ำหนัก</span>
                    <div class="mt-2 text-xl font-bold tabular-nums text-amber-950" data-support-entry-weighted>
                        {{ filled($entry['weighted_score'] ?? null) ? $entry['weighted_score'] : '-' }}
                    </div>
                </div>
            </div>
        @endif

        @if ($activityEntryRole === 'evaluatee')
            <button type="button" data-remove-support-activity
                class="mt-2 rounded-lg bg-red-50 px-3 py-1.5 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                ลบรายการ
            </button>
        @elseif ($activityEntryRole === 'reviewer')
            <label class="mt-3 block text-sm font-semibold text-slate-700">
                เหตุผลที่แก้ไขข้อมูลกิจกรรม/โครงการ
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
        @if ($allowEvaluateeIndicator)
            <div class="mt-3">
                <p class="text-xs font-semibold text-slate-500">ตัวชี้วัด/เกณฑ์การประเมิน</p>
                <div class="support-criteria-rich-text mt-1 text-sm text-slate-800">
                    {!! \App\Support\SafeHtml::richText($entry['indicator'] ?? '') !!}
                </div>
            </div>
        @endif
        @if ($allowEvaluateeWeight)
            <dl class="mt-3 grid grid-cols-3 gap-3 text-sm">
                <div><dt class="text-xs text-slate-500">น้ำหนัก</dt><dd>{{ $entry['weight'] ?? '-' }}</dd></div>
                <div><dt class="text-xs text-slate-500">ค่าคะแนนที่ได้</dt><dd>{{ $entry['achieved_score'] ?? '-' }}</dd></div>
                <div><dt class="text-xs text-slate-500">คะแนนถ่วงน้ำหนัก</dt><dd>{{ $entry['weighted_score'] ?? '-' }}</dd></div>
            </dl>
        @endif
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

    <section class="mt-4 border-t border-slate-200 pt-4" data-support-evidence-section>
        <div class="flex items-center justify-between gap-3">
            <h6 class="text-sm font-semibold text-slate-700">หลักฐาน</h6>
            @if ($canEditEvidence)
                <button type="button" data-add-support-evidence
                    class="rounded-lg bg-amber-100 px-3 py-2 text-sm font-semibold text-amber-900 transition hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    + เพิ่มลิงก์หลักฐาน
                </button>
            @endif
        </div>

        <div class="mt-2 space-y-2" data-support-evidence-container>
            @if ($canEditEvidence)
                @foreach ($evidenceLinks ?: [''] as $link)
                    <div class="support-evidence-row flex items-center gap-2">
                        <input type="url" data-support-evidence-input
                            name="support_list[{{ $item['id'] }}][activity_entries][{{ $entryIndex }}][evidence_links][]"
                            value="{{ $link }}"
                            aria-label="ลิงก์หลักฐานสำหรับรายการ {{ $entryIndex + 1 }}"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 shadow-sm outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200"
                            placeholder="https://example.com/evidence">
                        <button type="button" data-remove-support-evidence
                            class="rounded-lg bg-red-50 px-3 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100"
                            aria-label="ลบลิงก์หลักฐาน">ลบ</button>
                    </div>
                @endforeach
            @else
                @forelse ($evidenceLinks as $link)
                    <a href="{{ $link }}" target="_blank" rel="noopener noreferrer"
                        class="block break-all rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-700 hover:underline">
                        {{ $link }}
                    </a>
                    @if ($canEditActivities)
                        <input type="hidden" data-support-evidence-input
                            name="support_list[{{ $item['id'] }}][activity_entries][{{ $entryIndex }}][evidence_links][]"
                            value="{{ $link }}">
                    @endif
                @empty
                    <p class="rounded-lg border border-dashed border-slate-300 px-3 py-2 text-sm text-slate-500">
                        ไม่มีหลักฐานแนบ
                    </p>
                @endforelse
            @endif
        </div>
    </section>
</article>
