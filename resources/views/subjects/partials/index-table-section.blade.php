@php
    $canReorder = request('sort', 'manual') === 'manual' && !request()->filled('search') && !request()->filled('status');
@endphp

<style>
    .reorder-row.is-dragging { opacity: .55; }
    .reorder-row.is-drag-over { box-shadow: inset 0 -3px 0 #0d6efd; }
    .reorder-handle { cursor: grab; user-select: none; color: #6c757d; font-weight: 700; letter-spacing: 1px; }
    .reorder-handle:active { cursor: grabbing; }
    .reorder-disabled .reorder-handle { cursor: not-allowed; opacity: .45; }
</style>

<div class="table-container" data-async-table-region data-reorder-table data-reorder-url="{{ route('subjects.reorder') }}" data-can-reorder="{{ $canReorder ? 1 : 0 }}" data-start-order="{{ $subjects->firstItem() ?? 1 }}">
    <div class="table-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <h4><i class="fas fa-table me-2"></i>ข้อมูลรายวิชา</h4>
        <small class="text-muted" data-reorder-status></small>
    </div>

    <div class="table-responsive">
        @if (isset($subjects) && $subjects->count() > 0)
            <table class="table table-custom {{ $canReorder ? '' : 'reorder-disabled' }}">
                <thead>
                    <tr>
                        <th style="width: 6%" class="text-center">
                            <input type="checkbox" class="form-check-input" aria-label="เลือกรายวิชาทั้งหมดในหน้านี้" data-bulk-select-all>
                        </th>
                        <th style="width: 8%"></th>
                        <th style="width: 10%">ลำดับ</th>
                        <th style="width: 36%">ชื่อรายวิชา</th>
                        <th style="width: 15%">หน่วยกิต</th>
                        <th style="width: 25%">การจัดการ</th>
                    </tr>
                </thead>
                <tbody data-reorder-body data-resource-rows>
                    @foreach ($subjects as $index => $subject)
                        @php
                            $displayName = $subject->display_name ?? $subject->name_th ?? $subject->name_en ?? '';
                            $secondaryName = !empty($subject->name_th) ? ($subject->name_en ?? null) : null;
                            [$lectureDisplay, $labDisplay, $selfStudyDisplay] = $subject->display_component_values;
                            $usesHours = collect([$subject->lecture_hours, $subject->lab_hours, $subject->self_study_hours])
                                ->contains(fn ($value) => (int) $value > 0);
                        @endphp
                        <tr class="reorder-row" data-id="{{ $subject->id }}" data-resource-row data-resource-id="{{ $subject->id }}">
                            <td class="text-center align-middle">
                                <input type="checkbox" class="form-check-input" value="{{ $subject->id }}" aria-label="เลือก {{ $subject->code }} {{ $displayName }}" data-bulk-checkbox>
                            </td>
                            <td class="text-center align-middle"><span class="reorder-handle" data-drag-handle title="ลากเพื่อจัดอันดับ">⋮⋮</span></td>
                            <td class="align-middle" data-sequence>{{ $subjects->firstItem() + $index }}</td>
                            <td class="text-start align-middle">
                                <strong>{{ $subject->code }}: {{ $displayName }}</strong>
                                @if (!empty($secondaryName))
                                    <div class="text-muted text-sm">{{ $secondaryName }}</div>
                                @endif
                            </td>
                            <td class="align-middle">
                                <div class="fw-semibold">{{ $subject->credits }}</div>
                                <div class="text-muted text-sm">( {{ $lectureDisplay }} / {{ $labDisplay }} / {{ $selfStudyDisplay }})</div>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex gap-2 align-items-center justify-content-center">
                                    <x-button type="default" text="รายละเอียด" class="text-sm !bg-teal-600 hover:!bg-teal-700 !text-white" icon="fas fa-eye" data-role="subject-detail-trigger" data-code="{{ $subject->code }}" data-display-name="{{ $displayName }}" data-secondary-name="{{ $secondaryName ?? '' }}" data-credits="{{ $subject->credits }}" data-lecture-credits="{{ $subject->lecture_credits ?? 0 }}" data-lab-credits="{{ $subject->lab_credits ?? 0 }}" data-self-study-credits="{{ $subject->self_study_credits ?? 0 }}" data-lecture-hours="{{ $subject->lecture_hours ?? 0 }}" data-lab-hours="{{ $subject->lab_hours ?? 0 }}" data-self-study-hours="{{ $subject->self_study_hours ?? 0 }}" data-is-active="{{ ($subject->is_active ?? true) ? 1 : 0 }}" data-display-source="{{ $usesHours ? 'hours' : 'credits' }}" data-display-lecture="{{ $lectureDisplay }}" data-display-lab="{{ $labDisplay }}" data-display-self-study="{{ $selfStudyDisplay }}" aria-label="ดูรายละเอียด {{ $subject->code }} {{ $displayName }}" />
                                    <x-button type="warning" text="แก้ไข" class="text-sm" icon="fas fa-edit" data-id="{{ $subject->id }}" data-code="{{ $subject->code }}" data-name-th="{{ $subject->name_th }}" data-name-en="{{ $subject->name_en ?? '' }}" data-credits="{{ $subject->credits }}" data-lecture-credits="{{ $subject->lecture_credits ?? 0 }}" data-lab-credits="{{ $subject->lab_credits ?? 0 }}" data-self-study-credits="{{ $subject->self_study_credits ?? 0 }}" data-lecture-hours="{{ $subject->lecture_hours ?? 0 }}" data-lab-hours="{{ $subject->lab_hours ?? 0 }}" data-self-study-hours="{{ $subject->self_study_hours ?? 0 }}" data-role="subject-edit-trigger" />
                                    <x-button type="danger" text="ลบ" class="text-sm" icon="fas fa-trash-alt" data-delete-trigger data-delete-id="{{ $subject->id }}" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-3">
                {{ $subjects->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-book"></i>
                <h5>ยังไม่มีข้อมูล</h5>
                <p>คลิกปุ่ม "เพิ่มรายวิชา" เพื่อเริ่มต้นเพิ่มข้อมูลรายวิชา</p>
            </div>
        @endif
    </div>
</div>
