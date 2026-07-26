@props([
    'items' => [],
    'readonly' => false,
    'evidenceEditable' => false,
    'requireReason' => false,
    'activityEntryRole' => 'readonly',
])

@if (!empty($items))
    <section class="mb-8 overflow-hidden rounded-xl border border-amber-200 bg-amber-50/40 shadow-sm"
        aria-label="เกณฑ์สำหรับสายสนับสนุน">
        <div class="flex flex-col gap-2 border-b border-amber-200 bg-amber-100/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-bold text-amber-950">เกณฑ์สำหรับสายสนับสนุน</h3>
                <p class="mt-1 text-sm text-amber-800">คะแนนถ่วงน้ำหนัก = น้ำหนัก × ค่าคะแนนที่ได้ ÷ 100</p>
            </div>
            <span class="w-fit rounded-full border border-amber-300 bg-white px-3 py-1 text-xs font-semibold text-amber-800">
                {{ count($items) }} รายการ
            </span>
        </div>

            <table class="hidden w-full table-fixed border-collapse text-left text-sm lg:table">
                <thead class="bg-amber-50 text-xs font-semibold uppercase tracking-wide text-amber-950">
                    <tr>
                        <th scope="col" class="w-[5%] break-words px-2 py-3 text-center">ลำดับ</th>
                        <th scope="col" class="w-[17%] break-words px-2 py-3">กิจกรรม/โครงการ/งาน</th>
                        <th scope="col" class="w-[23%] break-words px-2 py-3">ตัวชี้วัด/เกณฑ์การประเมิน</th>
                        <th scope="col" class="w-[8%] break-words px-2 py-3 text-right">ระดับค่าเป้าหมาย</th>
                        <th scope="col" class="w-[7%] break-words px-2 py-3 text-right">น้ำหนัก</th>
                        <th scope="col" class="w-[8%] break-words px-2 py-3 text-right">ค่าคะแนนที่ได้</th>
                        <th scope="col" class="w-[9%] break-words px-2 py-3 text-right">คะแนนถ่วงน้ำหนัก</th>
                        <th scope="col" class="w-[7%] break-words px-2 py-3 text-center">ประวัติการแก้ไข</th>
                        <th scope="col" class="w-[8%] break-words px-2 py-3 text-center">หลักฐาน</th>
                        @if (!$readonly)
                            <th scope="col" class="w-[8%] break-words px-2 py-3 text-center">จัดการ</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-100 bg-white text-slate-700">
                    @foreach ($items as $item)
                        @php
                            $evidenceLinks = !empty($item['allow_activity_entries'])
                                ? collect($item['activity_entries'] ?? [])
                                    ->flatMap(fn (array $entry) => array_filter($entry['evidence_links'] ?? []))
                                    ->values()
                                    ->all()
                                : array_values(array_filter($item['evidence_links'] ?? []));
                            $evidenceCount = count($evidenceLinks);
                            $activityNameText = \App\Support\SafeHtml::plainText($item['activity_name'] ?? '');
                            $alignedEntryRows = !empty($item['allow_activity_entries'])
                                && (!empty($item['allow_evaluatee_indicator']) || !empty($item['allow_evaluatee_weight']))
                                    ? array_values($item['activity_entries'] ?? [])
                                    : [];
                            $alignedRowCount = max(count($alignedEntryRows), 1);
                        @endphp
                        @if ($alignedEntryRows !== [])
                            <tr class="border-b border-amber-100 bg-amber-50/35"
                                data-support-criterion-heading="{{ $item['id'] }}">
                                <td class="border-r border-amber-100 px-2 py-3"></td>
                                <th scope="rowgroup" colspan="{{ $readonly ? 8 : 9 }}"
                                    class="px-3 py-3 text-left font-semibold text-slate-900">
                                    <div class="support-criteria-rich-text">
                                        {!! \App\Support\SafeHtml::richText($item['activity_name'] ?? '') !!}
                                    </div>
                                </th>
                            </tr>
                            @foreach ($alignedEntryRows as $entryIndex => $entry)
                                <tr data-support-entry-row="{{ $item['id'] }}"
                                    data-support-entry-index="{{ $entryIndex }}"
                                    class="{{ $entryIndex > 0 ? 'border-t border-slate-100' : '' }}">
                                    @if ($entryIndex === 0)
                                        <td rowspan="{{ $alignedRowCount }}"
                                            class="border-r border-amber-100 px-2 py-4 text-center align-middle font-semibold text-amber-800">
                                            {{ $item['sequence'] }}
                                        </td>
                                    @endif
                                    <td class="break-words px-2 py-4 align-top" data-support-entry-activity-cell>
                                        <div class="flex gap-2 text-slate-700">
                                            <span class="shrink-0 text-xs font-semibold text-amber-700">{{ $entryIndex + 1 }}.</span>
                                            <div class="support-criteria-rich-text min-w-0 break-words"
                                                data-support-entry-activity-value>
                                                {!! \App\Support\SafeHtml::richText($entry['content'] ?? '') !!}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="break-words px-2 py-4 align-top leading-6"
                                        data-support-entry-indicator-cell>
                                        <div class="support-criteria-rich-text break-words"
                                            data-support-entry-indicator-list="{{ $item['id'] }}">
                                            {!! \App\Support\SafeHtml::richText($entry['indicator'] ?? '') !!}
                                        </div>
                                    </td>
                                    @if ($entryIndex === 0)
                                        <td rowspan="{{ $alignedRowCount }}"
                                            class="border-x border-amber-100 px-2 py-4 text-right align-middle tabular-nums">
                                            {{ $item['target_value'] }}
                                        </td>
                                    @endif
                                    <td class="px-2 py-4 text-right align-top tabular-nums"
                                        data-support-entry-weight-list="{{ $item['id'] }}">
                                        {{ filled($entry['weight'] ?? null) ? $entry['weight'] : '-' }}
                                    </td>
                                    <td class="px-2 py-4 text-right align-top font-semibold tabular-nums"
                                        data-support-entry-score-list="{{ $item['id'] }}">
                                        {{ filled($entry['achieved_score'] ?? null) ? $entry['achieved_score'] : '-' }}
                                    </td>
                                    @if ($entryIndex === 0)
                                        <td rowspan="{{ $alignedRowCount }}"
                                            class="border-x border-amber-100 px-2 py-4 text-right align-middle">
                                            <span class="inline-flex min-w-16 justify-end rounded-full bg-amber-100 px-2.5 py-1 font-bold tabular-nums text-amber-900"
                                                data-support-weighted-display="{{ $item['id'] }}">
                                                {{ filled($item['weighted_score']) ? $item['weighted_score'] : '-' }}
                                            </span>
                                        </td>
                                        <td rowspan="{{ $alignedRowCount }}"
                                            class="px-2 py-4 text-center align-middle">
                                            @if (count($item['histories'] ?? []) > 0)
                                                <a href="#support-history-modal" role="button" aria-haspopup="dialog"
                                                    data-support-history-open="{{ $item['id'] }}"
                                                    class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-400">
                                                    {{ count($item['histories']) }} ครั้ง
                                                </a>
                                            @else
                                                <span class="text-slate-400" aria-label="ไม่มีประวัติการแก้ไข">–</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-2 py-4 align-top" data-support-entry-evidence-cell>
                                        <div class="space-y-1 text-left"
                                            data-support-entry-evidence-list="{{ $entryIndex }}"
                                            data-support-activity-evidence-list="{{ $entry['id'] ?? "new-{$entryIndex}" }}">
                                            @forelse (array_values(array_filter($entry['evidence_links'] ?? [])) as $linkIndex => $link)
                                                @php
                                                    $entryEvidenceCount = count(array_filter($entry['evidence_links'] ?? []));
                                                    $evidenceLabel = $entryEvidenceCount > 1
                                                        ? 'ไฟล์ '.($linkIndex + 1)
                                                        : 'เปิดดู';
                                                @endphp
                                                <x-support-evidence-link
                                                    :href="$link"
                                                    :label="$evidenceLabel"
                                                    :aria-label="'เปิดหลักฐาน '.($linkIndex + 1).' สำหรับรายการ '.($entryIndex + 1)" />
                                            @empty
                                                <span class="text-slate-400">ไม่มีหลักฐาน</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    @if (!$readonly && $entryIndex === 0)
                                        <td rowspan="{{ $alignedRowCount }}"
                                            class="border-l border-amber-100 px-2 py-4 text-center align-middle">
                                            <button type="button" data-support-manage-open="{{ $item['id'] }}"
                                                aria-label="{{ $evidenceCount > 0 ? 'แก้ไขข้อมูล' : 'กรอกข้อมูล' }}สำหรับ {{ $activityNameText }}"
                                                class="rounded-lg bg-amber-100 px-3 py-2 font-semibold text-amber-900 transition hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                                {{ $evidenceCount > 0 ? 'แก้ไขข้อมูล' : 'กรอกข้อมูล' }}
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                        <tr>
                            <td class="px-2 py-4 text-center font-semibold text-amber-800">{{ $item['sequence'] }}</td>
                            <td class="break-words px-2 py-4 align-top">
                                <x-support-activity-display :item="$item" />
                            </td>
                            <td class="break-words px-2 py-4 leading-6">
                                @if (!empty($item['allow_evaluatee_indicator']))
                                    <ol class="list-decimal space-y-2 pl-5" data-support-entry-indicator-list="{{ $item['id'] }}">
                                        @forelse ($item['activity_entries'] ?? [] as $entry)
                                            <li class="support-criteria-rich-text break-words">
                                                {!! \App\Support\SafeHtml::richText($entry['indicator'] ?? '') !!}
                                            </li>
                                        @empty
                                            <li class="list-none text-slate-400">-</li>
                                        @endforelse
                                    </ol>
                                @else
                                    <x-support-indicator-display :item="$item" />
                                @endif
                            </td>
                            <td class="px-2 py-4 text-right tabular-nums">{{ $item['target_value'] }}</td>
                            <td class="px-2 py-4 text-right tabular-nums">
                                @if (!empty($item['allow_evaluatee_weight']))
                                    <ol class="space-y-2" data-support-entry-weight-list="{{ $item['id'] }}">
                                        @forelse ($item['activity_entries'] ?? [] as $entry)
                                            <li>{{ filled($entry['weight'] ?? null) ? $entry['weight'] : '-' }}</li>
                                        @empty
                                            <li class="text-slate-400">-</li>
                                        @endforelse
                                    </ol>
                                @else
                                    {{ $item['weight'] }}
                                @endif
                            </td>
                            <td class="px-2 py-4 text-right font-semibold tabular-nums"
                                data-support-achieved-display="{{ $item['id'] }}">
                                @if (!empty($item['allow_evaluatee_weight']))
                                    <ol class="space-y-2" data-support-entry-score-list="{{ $item['id'] }}">
                                        @forelse ($item['activity_entries'] ?? [] as $entry)
                                            <li>{{ filled($entry['achieved_score'] ?? null) ? $entry['achieved_score'] : '-' }}</li>
                                        @empty
                                            <li class="text-slate-400">-</li>
                                        @endforelse
                                    </ol>
                                @else
                                    {{ filled($item['achieved_score']) ? $item['achieved_score'] : '-' }}
                                @endif
                            </td>
                            <td class="px-2 py-4 text-right">
                                <span class="inline-flex min-w-16 justify-end rounded-full bg-amber-100 px-2.5 py-1 font-bold tabular-nums text-amber-900"
                                    data-support-weighted-display="{{ $item['id'] }}">
                                    {{ filled($item['weighted_score']) ? $item['weighted_score'] : '-' }}
                                </span>
                            </td>
                            <td class="px-2 py-4 text-center">
                                @if (count($item['histories'] ?? []) > 0)
                                    <a href="#support-history-modal" role="button" aria-haspopup="dialog"
                                        data-support-history-open="{{ $item['id'] }}"
                                        class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-400">
                                        {{ count($item['histories']) }} ครั้ง
                                    </a>
                                @else
                                    <span class="text-slate-400" aria-label="ไม่มีประวัติการแก้ไข">–</span>
                                @endif
                            </td>
                            <td class="px-2 py-4 text-center">
                                <x-support-evidence-summary :item="$item" />
                            </td>
                            @if (!$readonly)
                                <td class="px-2 py-4 text-center">
                                    <button type="button" data-support-manage-open="{{ $item['id'] }}"
                                        aria-label="{{ filled($item['achieved_score']) || $evidenceCount > 0 ? 'แก้ไขข้อมูล' : 'กรอกข้อมูล' }}สำหรับ {{ $activityNameText }}"
                                        class="rounded-lg bg-amber-100 px-3 py-2 font-semibold text-amber-900 transition hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                        {{ filled($item['achieved_score']) || $evidenceCount > 0 ? 'แก้ไขข้อมูล' : 'กรอกข้อมูล' }}
                                    </button>
                                </td>
                            @endif
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>

        <div class="space-y-3 p-4 lg:hidden">
                    @foreach ($items as $item)
                @php
                    $evidenceLinks = !empty($item['allow_activity_entries'])
                        ? collect($item['activity_entries'] ?? [])
                            ->flatMap(fn (array $entry) => array_filter($entry['evidence_links'] ?? []))
                            ->values()
                            ->all()
                        : array_values(array_filter($item['evidence_links'] ?? []));
                    $evidenceCount = count($evidenceLinks);
                    $activityNameText = \App\Support\SafeHtml::plainText($item['activity_name'] ?? '');
                @endphp
                <article class="rounded-xl border border-amber-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wide text-amber-700">รายการ {{ $item['sequence'] }}</span>
                            <div class="mt-1 font-bold text-slate-900">
                                <x-support-activity-display :item="$item" />
                            </div>
                        </div>
                        @if (!empty($item['require_evidence']))
                            <span class="shrink-0 rounded-full bg-red-50 px-2 py-1 text-xs font-semibold text-red-700">
                                บังคับแนบหลักฐาน
                            </span>
                        @endif
                    </div>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div class="col-span-2">
                            <dt class="text-xs font-medium text-slate-500">ตัวชี้วัด/เกณฑ์การประเมิน</dt>
                            <dd class="mt-1 leading-6 text-slate-800">
                                @if (!empty($item['allow_evaluatee_indicator']))
                                    <ol class="list-decimal space-y-2 pl-5" data-support-entry-indicator-list="{{ $item['id'] }}">
                                        @forelse ($item['activity_entries'] ?? [] as $entry)
                                            <li class="support-criteria-rich-text break-words">
                                                {!! \App\Support\SafeHtml::richText($entry['indicator'] ?? '') !!}
                                            </li>
                                        @empty
                                            <li class="list-none text-slate-400">-</li>
                                        @endforelse
                                    </ol>
                                @else
                                    <x-support-indicator-display :item="$item" />
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">ระดับค่าเป้าหมาย</dt>
                            <dd class="mt-1 font-semibold tabular-nums text-slate-900">{{ $item['target_value'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">น้ำหนัก</dt>
                            <dd class="mt-1 font-semibold tabular-nums text-slate-900">
                                @if (!empty($item['allow_evaluatee_weight']))
                                    <ol class="space-y-2" data-support-entry-weight-list="{{ $item['id'] }}">
                                        @forelse ($item['activity_entries'] ?? [] as $entry)
                                            <li>{{ filled($entry['weight'] ?? null) ? $entry['weight'] : '-' }}</li>
                                        @empty
                                            <li class="text-slate-400">-</li>
                                        @endforelse
                                    </ol>
                                @else
                                    {{ $item['weight'] }}
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">ค่าคะแนนที่ได้</dt>
                            <dd class="mt-1 font-semibold tabular-nums text-slate-900"
                                data-support-achieved-display="{{ $item['id'] }}">
                                @if (!empty($item['allow_evaluatee_weight']))
                                    <ol class="space-y-2" data-support-entry-score-list="{{ $item['id'] }}">
                                        @forelse ($item['activity_entries'] ?? [] as $entry)
                                            <li>{{ filled($entry['achieved_score'] ?? null) ? $entry['achieved_score'] : '-' }}</li>
                                        @empty
                                            <li class="text-slate-400">-</li>
                                        @endforelse
                                    </ol>
                                @else
                                    {{ filled($item['achieved_score']) ? $item['achieved_score'] : '-' }}
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">คะแนนถ่วงน้ำหนัก</dt>
                            <dd class="mt-1 font-bold tabular-nums text-amber-800"
                                data-support-weighted-display="{{ $item['id'] }}">
                                {{ filled($item['weighted_score']) ? $item['weighted_score'] : '-' }}
                            </dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-xs font-medium text-slate-500">ประวัติการแก้ไข</dt>
                            <dd class="mt-1">
                                @if (count($item['histories'] ?? []) > 0)
                                    <a href="#support-history-modal" role="button" aria-haspopup="dialog"
                                        data-support-history-open="{{ $item['id'] }}"
                                        class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-400">
                                        {{ count($item['histories']) }} ครั้ง
                                    </a>
                                @else
                                    <span class="text-slate-400" aria-label="ไม่มีประวัติการแก้ไข">–</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-amber-100 pt-4">
                        <div>
                            <span class="block text-xs font-medium text-slate-500">หลักฐาน</span>
                            <div class="mt-1">
                                <x-support-evidence-summary :item="$item" />
                            </div>
                        </div>
                        @if (!$readonly)
                            <button type="button" data-support-manage-open="{{ $item['id'] }}"
                                aria-label="{{ filled($item['achieved_score']) || $evidenceCount > 0 ? 'แก้ไขข้อมูล' : 'กรอกข้อมูล' }}สำหรับ {{ $activityNameText }}"
                                class="rounded-lg bg-amber-100 px-3 py-2 text-sm font-semibold text-amber-900 transition hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                {{ filled($item['achieved_score']) || $evidenceCount > 0 ? 'แก้ไขข้อมูล' : 'กรอกข้อมูล' }}
                            </button>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        @foreach ($items as $item)
            <script type="application/json" data-support-history-payload="{{ $item['id'] }}">@json($item['histories'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
        @endforeach

        <div class="hidden" data-support-editor-store aria-hidden="true">
            @foreach ($items as $item)
                @php
                    $activityNameText = \App\Support\SafeHtml::plainText($item['activity_name'] ?? '');
                    $canEditActivities = !$readonly
                        && !empty($item['allow_activity_entries'])
                        && in_array($activityEntryRole, ['evaluatee', 'reviewer'], true);
                @endphp
                <article class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-5"
                    data-support-item
                    data-support-id="{{ $item['id'] }}"
                    data-support-sequence="{{ $item['sequence'] }}"
                    data-support-activity="{{ $activityNameText }}"
                    data-support-required="{{ !empty($item['require_evidence']) ? '1' : '0' }}"
                    data-support-require-reason="{{ $requireReason ? '1' : '0' }}"
                    data-support-activity-role="{{ $readonly ? 'readonly' : $activityEntryRole }}"
                    data-support-grouped="{{ !empty($item['group_activity_entries_by_indicator']) ? '1' : '0' }}"
                    data-support-allow-entry-indicator="{{ !empty($item['allow_evaluatee_indicator']) ? '1' : '0' }}"
                    data-support-allow-entry-weight="{{ !empty($item['allow_evaluatee_weight']) ? '1' : '0' }}"
                    data-support-existing-weighted="{{ $item['weighted_score'] ?? '' }}">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">กรอกผลรายการ {{ $item['sequence'] }}</p>
                            <h4 class="support-criteria-rich-text mt-1 font-bold text-slate-900">{!! \App\Support\SafeHtml::richText($item['activity_name'] ?? '') !!}</h4>
                        </div>
                        @if (!empty($item['require_evidence']))
                            <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                                บังคับแนบหลักฐาน
                            </span>
                        @endif
                    </div>

                    @if (!empty($item['allow_activity_entries']))
                        <section class="mb-5 rounded-xl border border-amber-200 bg-white p-4"
                            data-support-activity-section>
                            @if (!empty($item['group_activity_entries_by_indicator']))
                                <div class="mb-4">
                                    <h5 class="font-semibold text-slate-800">กิจกรรม/โครงการตามตัวชี้วัดย่อย</h5>
                                    <p class="mt-1 text-xs text-slate-500">หนึ่งโครงการอยู่ได้เพียงข้อย่อยเดียว</p>
                                </div>

                                <div class="space-y-4">
                                    @foreach ($item['indicator_items'] ?? [] as $indicatorItem)
                                        @php
                                            $groupEntries = collect($item['activity_entries'] ?? [])->filter(
                                                fn ($entry) => (int) ($entry['support_indicator_item_id'] ?? 0) === (int) $indicatorItem['id']
                                            );
                                            $indicatorReference = preg_split(
                                                '/\s+/u',
                                                trim((string) ($indicatorItem['code'] ?? '')),
                                                2
                                            )[0] ?? '';
                                        @endphp
                                        <section class="rounded-xl border border-amber-200 bg-amber-50/50 p-4"
                                            data-support-activity-group="{{ $indicatorItem['id'] }}"
                                            data-support-indicator-code="{{ $indicatorItem['code'] }}">
                                            <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <h6 class="font-bold text-amber-950">ข้อ {{ $indicatorReference }}</h6>
                                                </div>
                                                @if ($canEditActivities && $activityEntryRole === 'evaluatee')
                                                    <button type="button"
                                                        data-add-support-activity="{{ $item['id'] }}"
                                                        data-support-indicator-item-id="{{ $indicatorItem['id'] }}"
                                                        class="shrink-0 rounded-lg bg-amber-100 px-3 py-2 text-sm font-semibold text-amber-900 transition hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                                        + เพิ่มโครงการในข้อ {{ $indicatorReference }}
                                                    </button>
                                                @endif
                                            </div>

                                            <div class="space-y-4" data-support-activity-container>
                                                @foreach (($item['activity_entries'] ?? []) as $entryIndex => $entry)
                                                    @continue((int) ($entry['support_indicator_item_id'] ?? 0) !== (int) $indicatorItem['id'])
                                                    <x-support-activity-entry-editor
                                                        :item="$item"
                                                        :entry="$entry"
                                                        :entry-index="$entryIndex"
                                                        :can-edit-activities="$canEditActivities"
                                                        :activity-entry-role="$activityEntryRole"
                                                        :evidence-editable="$evidenceEditable" />
                                                @endforeach
                                            </div>
                                            <p class="rounded-lg border border-dashed border-slate-300 px-3 py-3 text-sm text-slate-500 {{ $groupEntries->isNotEmpty() ? 'hidden' : '' }}"
                                                data-support-activity-empty>
                                                ยังไม่มีโครงการในข้อนี้
                                            </p>
                                        </section>
                                    @endforeach
                                </div>
                            @else
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h5 class="font-semibold text-slate-800">กิจกรรม/โครงการเพิ่มเติม</h5>
                                    <p class="mt-1 text-xs text-slate-500">รายการนี้เป็นข้อมูลเพิ่มเติมจากหัวข้อที่ Admin กำหนด</p>
                                </div>
                                @if ($canEditActivities && $activityEntryRole === 'evaluatee')
                                    <button type="button" data-add-support-activity="{{ $item['id'] }}"
                                        class="rounded-lg bg-amber-100 px-3 py-2 text-sm font-semibold text-amber-900 transition hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                        + เพิ่มกิจกรรม/โครงการ
                                    </button>
                                @endif
                            </div>

                            <div class="space-y-4" data-support-activity-container>
                                @foreach ($item['activity_entries'] ?? [] as $entryIndex => $entry)
                                    <x-support-activity-entry-editor
                                        :item="$item"
                                        :entry="$entry"
                                        :entry-index="$entryIndex"
                                        :can-edit-activities="$canEditActivities"
                                        :activity-entry-role="$activityEntryRole"
                                        :evidence-editable="$evidenceEditable" />
                                @endforeach

                                <p class="rounded-lg border border-dashed border-slate-300 px-3 py-3 text-sm text-slate-500 {{ !empty($item['activity_entries']) ? 'hidden' : '' }}"
                                    data-support-activity-empty>
                                    ยังไม่มีกิจกรรม/โครงการเพิ่มเติม
                                </p>
                            </div>
                            @endif
                        </section>
                    @endif

                    @if (!$readonly)
                        <input type="hidden"
                            name="support_list[{{ $item['id'] }}][support_criteria_id]"
                            value="{{ $item['id'] }}">

                        @if (empty($item['allow_evaluatee_weight']))
                        <div class="grid gap-4 lg:grid-cols-2">
                            <label for="support-score-{{ $item['id'] }}" class="block text-sm font-semibold text-slate-700">
                                ค่าคะแนนที่ได้
                                <input id="support-score-{{ $item['id'] }}" type="number" min="0" step="0.01"
                                    name="support_list[{{ $item['id'] }}][achieved_score]"
                                    value="{{ $item['achieved_score'] }}"
                                    aria-describedby="support-score-help-{{ $item['id'] }}"
                                    data-support-score
                                    data-support-id="{{ $item['id'] }}"
                                    data-support-weight="{{ $item['weight'] }}"
                                    data-support-original-score="{{ $item['achieved_score'] }}"
                                    class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 shadow-sm outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200"
                                    placeholder="0.00">
                                <span id="support-score-help-{{ $item['id'] }}" class="mt-1 block text-xs font-normal text-slate-500">กรอกได้ตั้งแต่ 0 และทศนิยมไม่เกิน 2 ตำแหน่ง</span>
                            </label>

                            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                                <span class="text-xs font-semibold uppercase tracking-wide text-amber-700">คะแนนถ่วงน้ำหนัก</span>
                                <div class="mt-1 text-2xl font-bold tabular-nums text-amber-950"
                                    data-support-weighted-display="{{ $item['id'] }}">
                                    {{ filled($item['weighted_score']) ? $item['weighted_score'] : '-' }}
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif

                    @if (empty($item['allow_activity_entries']))
                    <div class="mt-4" data-support-evidence-section>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <h5 class="text-sm font-semibold text-slate-700">หลักฐาน</h5>
                            @if (!$readonly && $evidenceEditable)
                                <button type="button" data-add-support-evidence="{{ $item['id'] }}"
                                    class="rounded-lg bg-amber-100 px-3 py-1.5 text-sm font-semibold text-amber-900 transition hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                    + เพิ่มลิงก์หลักฐาน
                                </button>
                            @endif
                        </div>

                        @if (!$readonly && $evidenceEditable)
                            <div id="support-evidence-links-{{ $item['id'] }}" class="space-y-2" data-support-evidence-container>
                                @forelse ($item['evidence_links'] as $link)
                                    <div class="support-evidence-row flex items-center gap-2">
                                        <input type="url" data-support-evidence-input
                                            name="support_list[{{ $item['id'] }}][evidence_links][]"
                                            value="{{ $link }}"
                                            aria-label="ลิงก์หลักฐานสำหรับ {{ $activityNameText }}"
                                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 shadow-sm outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200"
                                            placeholder="https://example.com/evidence">
                                        <button type="button" data-remove-support-evidence
                                            class="rounded-lg bg-red-50 px-3 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100"
                                            aria-label="ลบลิงก์หลักฐาน">ลบ</button>
                                    </div>
                                @empty
                                    <div class="support-evidence-row flex items-center gap-2">
                                        <input type="url" data-support-evidence-input
                                            name="support_list[{{ $item['id'] }}][evidence_links][]"
                                            aria-label="ลิงก์หลักฐานสำหรับ {{ $activityNameText }}"
                                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 shadow-sm outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200"
                                            placeholder="https://example.com/evidence">
                                        <button type="button" data-remove-support-evidence
                                            class="rounded-lg bg-red-50 px-3 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100"
                                            aria-label="ลบลิงก์หลักฐาน">ลบ</button>
                                    </div>
                                @endforelse
                            </div>
                        @else
                            <div class="space-y-2">
                                @forelse ($item['evidence_links'] as $link)
                                    <a href="{{ $link }}" target="_blank" rel="noopener noreferrer"
                                        class="block break-all rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-700 hover:underline">
                                        {{ $link }}
                                    </a>
                                    @if (!$readonly)
                                        <input type="hidden" data-support-evidence-input
                                            name="support_list[{{ $item['id'] }}][evidence_links][]"
                                            value="{{ $link }}">
                                    @endif
                                @empty
                                    <p class="rounded-lg border border-dashed border-slate-300 px-3 py-2 text-sm text-slate-500">ไม่มีหลักฐานแนบ</p>
                                @endforelse
                            </div>
                        @endif
                    </div>
                    @endif

                    @if (!$readonly && $requireReason)
                        <label for="support-reason-{{ $item['id'] }}" class="mt-4 block text-sm font-semibold text-slate-700">
                            เหตุผลที่แก้ไขคะแนน
                            <textarea id="support-reason-{{ $item['id'] }}"
                                name="support_list[{{ $item['id'] }}][modification_reason]"
                                data-support-reason rows="3" maxlength="2000"
                                class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 shadow-sm outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200"
                                placeholder="ระบุเมื่อแก้ไขค่าคะแนนเดิม">{{ $item['modification_reason'] }}</textarea>
                        </label>
                    @endif

                </article>
            @endforeach
        </div>
    </section>

    <div class="fixed inset-0 z-[1100] hidden items-center justify-center bg-slate-950/60 p-4"
        data-support-modal role="dialog" aria-modal="true" aria-labelledby="support-modal-title">
        <div class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
            data-support-modal-panel>
            <header class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700" data-support-modal-sequence></p>
                    <h4 id="support-modal-title" class="mt-1 text-lg font-bold text-slate-950" data-support-modal-title></h4>
                </div>
                <button type="button" data-support-modal-cancel aria-label="ปิดหน้าต่าง"
                    class="rounded-lg p-2 text-xl leading-none text-slate-500 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-400">×</button>
            </header>
            <div id="support-modal-errors" class="hidden border-b border-red-200 bg-red-50 px-5 py-3 text-sm text-red-700"
                data-support-modal-errors role="alert" aria-live="assertive"></div>
            <div class="overflow-y-auto p-5" data-support-modal-body></div>
            <footer class="flex justify-end gap-3 border-t border-slate-200 px-5 py-4">
                <button type="button" data-support-modal-cancel
                    class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400">
                    {{ $readonly ? 'ปิด' : 'ยกเลิก' }}
                </button>
                @if (!$readonly)
                    <button type="button" data-support-modal-save
                        class="rounded-lg bg-amber-500 px-4 py-2 font-semibold text-white transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2">
                        บันทึก
                    </button>
                @endif
            </footer>
        </div>
    </div>

    <div id="support-history-modal"
        class="fixed inset-0 z-[1200] hidden items-center justify-center bg-slate-950/60 p-4"
        role="dialog" aria-modal="true" aria-labelledby="support-history-modal-title">
        <div class="flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h4 id="support-history-modal-title" class="text-lg font-bold text-slate-950">ประวัติการแก้ไขคะแนน</h4>
                <a href="#" role="button" data-support-history-close aria-label="ปิดประวัติการแก้ไข"
                    class="rounded-lg p-2 text-xl leading-none text-slate-500 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400">×
                </a>
            </header>
            <div class="space-y-3 overflow-y-auto p-5" data-support-history-list></div>
            <footer class="flex justify-end border-t border-slate-200 px-5 py-4">
                <a href="#" role="button" data-support-history-close
                    class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400">
                    ปิด
                </a>
            </footer>
        </div>
    </div>
@endif
