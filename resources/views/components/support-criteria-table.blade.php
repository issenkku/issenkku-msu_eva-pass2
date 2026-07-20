@props([
    'items' => [],
    'readonly' => false,
    'evidenceEditable' => false,
    'requireReason' => false,
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

        <div class="hidden overflow-x-auto md:block">
            <table class="hidden md:table min-w-[1180px] w-full border-collapse text-left text-sm">
                <thead class="bg-amber-50 text-xs font-semibold uppercase tracking-wide text-amber-950">
                    <tr>
                        <th scope="col" class="w-16 px-3 py-3 text-center">ลำดับ</th>
                        <th scope="col" class="min-w-[240px] px-3 py-3">กิจกรรม/โครงการ/งาน</th>
                        <th scope="col" class="min-w-[360px] px-3 py-3">ตัวชี้วัด/เกณฑ์การประเมิน</th>
                        <th scope="col" class="min-w-[110px] px-3 py-3 text-right">ระดับค่าเป้าหมาย</th>
                        <th scope="col" class="min-w-[80px] px-3 py-3 text-right">น้ำหนัก</th>
                        <th scope="col" class="min-w-[100px] px-3 py-3 text-right">ค่าคะแนนที่ได้</th>
                        <th scope="col" class="min-w-[120px] px-3 py-3 text-right">คะแนนถ่วงน้ำหนัก</th>
                        <th scope="col" class="min-w-[90px] px-3 py-3 text-center">หลักฐาน</th>
                        @if (!$readonly)
                            <th scope="col" class="min-w-[100px] px-3 py-3 text-center">จัดการ</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-100 bg-white text-slate-700">
                    @foreach ($items as $item)
                        @php($evidenceCount = count(array_filter($item['evidence_links'] ?? [])))
                        <tr>
                            <td class="px-3 py-4 text-center font-semibold text-amber-800">{{ $item['sequence'] }}</td>
                            <td class="px-3 py-4 font-medium text-slate-900">{{ $item['activity_name'] }}</td>
                            <td class="px-3 py-4 leading-6">{{ $item['indicator'] }}</td>
                            <td class="px-3 py-4 text-right tabular-nums">{{ $item['target_value'] }}</td>
                            <td class="px-3 py-4 text-right tabular-nums">{{ $item['weight'] }}</td>
                            <td class="px-3 py-4 text-right font-semibold tabular-nums"
                                data-support-achieved-display="{{ $item['id'] }}">
                                {{ filled($item['achieved_score']) ? $item['achieved_score'] : '-' }}
                            </td>
                            <td class="px-3 py-4 text-right">
                                <span class="inline-flex min-w-16 justify-end rounded-full bg-amber-100 px-2.5 py-1 font-bold tabular-nums text-amber-900"
                                    data-support-weighted-display="{{ $item['id'] }}">
                                    {{ filled($item['weighted_score']) ? $item['weighted_score'] : '-' }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-center">
                                <span data-support-evidence-count="{{ $item['id'] }}">
                                    @if ($evidenceCount > 0)
                                        <button type="button" data-support-evidence-open="{{ $item['id'] }}"
                                            aria-label="ดูหลักฐานของ {{ $item['activity_name'] }} {{ $evidenceCount }} ลิงก์"
                                            class="font-semibold text-blue-700 underline decoration-blue-300 underline-offset-4 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                            {{ $evidenceCount }} ลิงก์
                                        </button>
                                    @else
                                        <span class="text-slate-400">ไม่มีหลักฐาน</span>
                                    @endif
                                </span>
                            </td>
                            @if (!$readonly)
                                <td class="px-3 py-4 text-center">
                                    <button type="button" data-support-manage-open="{{ $item['id'] }}"
                                        aria-label="{{ filled($item['achieved_score']) || $evidenceCount > 0 ? 'แก้ไขข้อมูล' : 'กรอกข้อมูล' }}สำหรับ {{ $item['activity_name'] }}"
                                        class="rounded-lg bg-amber-100 px-3 py-2 font-semibold text-amber-900 transition hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                        {{ filled($item['achieved_score']) || $evidenceCount > 0 ? 'แก้ไขข้อมูล' : 'กรอกข้อมูล' }}
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="space-y-3 p-4 md:hidden">
            @foreach ($items as $item)
                @php($evidenceCount = count(array_filter($item['evidence_links'] ?? [])))
                <article class="rounded-xl border border-amber-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wide text-amber-700">รายการ {{ $item['sequence'] }}</span>
                            <h4 class="mt-1 font-bold text-slate-900">{{ $item['activity_name'] }}</h4>
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
                            <dd class="mt-1 leading-6 text-slate-800">{{ $item['indicator'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">ระดับค่าเป้าหมาย</dt>
                            <dd class="mt-1 font-semibold tabular-nums text-slate-900">{{ $item['target_value'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">น้ำหนัก</dt>
                            <dd class="mt-1 font-semibold tabular-nums text-slate-900">{{ $item['weight'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">ค่าคะแนนที่ได้</dt>
                            <dd class="mt-1 font-semibold tabular-nums text-slate-900"
                                data-support-achieved-display="{{ $item['id'] }}">
                                {{ filled($item['achieved_score']) ? $item['achieved_score'] : '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">คะแนนถ่วงน้ำหนัก</dt>
                            <dd class="mt-1 font-bold tabular-nums text-amber-800"
                                data-support-weighted-display="{{ $item['id'] }}">
                                {{ filled($item['weighted_score']) ? $item['weighted_score'] : '-' }}
                            </dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-amber-100 pt-4">
                        <div>
                            <span class="block text-xs font-medium text-slate-500">หลักฐาน</span>
                            <span class="mt-1 block" data-support-evidence-count="{{ $item['id'] }}">
                                @if ($evidenceCount > 0)
                                    <button type="button" data-support-evidence-open="{{ $item['id'] }}"
                                        aria-label="ดูหลักฐานของ {{ $item['activity_name'] }} {{ $evidenceCount }} ลิงก์"
                                        class="font-semibold text-blue-700 underline decoration-blue-300 underline-offset-4 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                        {{ $evidenceCount }} ลิงก์
                                    </button>
                                @else
                                    <span class="text-sm text-slate-400">ไม่มีหลักฐาน</span>
                                @endif
                            </span>
                        </div>
                        @if (!$readonly)
                            <button type="button" data-support-manage-open="{{ $item['id'] }}"
                                aria-label="{{ filled($item['achieved_score']) || $evidenceCount > 0 ? 'แก้ไขข้อมูล' : 'กรอกข้อมูล' }}สำหรับ {{ $item['activity_name'] }}"
                                class="rounded-lg bg-amber-100 px-3 py-2 text-sm font-semibold text-amber-900 transition hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                {{ filled($item['achieved_score']) || $evidenceCount > 0 ? 'แก้ไขข้อมูล' : 'กรอกข้อมูล' }}
                            </button>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="hidden" data-support-editor-store aria-hidden="true">
            @foreach ($items as $item)
                <article class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-5"
                    data-support-item
                    data-support-id="{{ $item['id'] }}"
                    data-support-sequence="{{ $item['sequence'] }}"
                    data-support-activity="{{ $item['activity_name'] }}"
                    data-support-required="{{ !empty($item['require_evidence']) ? '1' : '0' }}"
                    data-support-require-reason="{{ $requireReason ? '1' : '0' }}"
                    data-support-existing-weighted="{{ $item['weighted_score'] ?? '' }}">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">กรอกผลรายการ {{ $item['sequence'] }}</p>
                            <h4 class="mt-1 font-bold text-slate-900">{{ $item['activity_name'] }}</h4>
                        </div>
                        @if (!empty($item['require_evidence']))
                            <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                                บังคับแนบหลักฐาน
                            </span>
                        @endif
                    </div>

                    @if (!$readonly)
                        <input type="hidden"
                            name="support_list[{{ $item['id'] }}][support_criteria_id]"
                            value="{{ $item['id'] }}">

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
                                            aria-label="ลิงก์หลักฐานสำหรับ {{ $item['activity_name'] }}"
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
                                            aria-label="ลิงก์หลักฐานสำหรับ {{ $item['activity_name'] }}"
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

                    @if (!empty($item['histories']))
                        <details class="mt-4 rounded-lg border border-slate-200 bg-white p-3">
                            <summary class="cursor-pointer text-sm font-semibold text-slate-700">ประวัติการแก้ไขคะแนน ({{ count($item['histories']) }})</summary>
                            <div class="mt-3 space-y-3">
                                @foreach ($item['histories'] as $history)
                                    <div class="rounded-lg bg-slate-50 p-3 text-sm text-slate-700">
                                        <div class="grid gap-2 sm:grid-cols-2">
                                            <p>ค่าคะแนนเดิม: <strong>{{ $history['previous_achieved_score'] ?? '-' }}</strong></p>
                                            <p>ค่าคะแนนใหม่: <strong>{{ $history['new_achieved_score'] ?? '-' }}</strong></p>
                                            <p>คะแนนถ่วงน้ำหนักเดิม: <strong>{{ $history['previous_weighted_score'] ?? '-' }}</strong></p>
                                            <p>คะแนนถ่วงน้ำหนักใหม่: <strong>{{ $history['new_weighted_score'] ?? '-' }}</strong></p>
                                        </div>
                                        <p class="mt-2">เหตุผล: {{ $history['reason'] ?? '-' }}</p>
                                        <p class="mt-2 text-xs text-slate-500">
                                            แก้ไขโดย {{ $history['modified_by_name'] ?: '-' }}
                                            @if (!empty($history['modified_by_role']))
                                                ({{ $history['modified_by_role'] }})
                                            @endif
                                            · {{ $history['created_at'] ?? '-' }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </details>
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
@endif
