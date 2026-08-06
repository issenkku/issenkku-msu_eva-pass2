@php
    $displayName = $subject->display_name ?? $subject->name_th ?? $subject->name_en ?? '';
    $secondaryName = !empty($subject->name_th) ? ($subject->name_en ?? null) : null;
@endphp
<tr class="reorder-row" data-id="{{ $subject->id }}" data-resource-row data-resource-id="{{ $subject->id }}">
    <td class="text-center align-middle">
        <input type="checkbox" class="form-check-input" value="{{ $subject->id }}" aria-label="เลือก {{ $subject->code }} {{ $displayName }}" data-bulk-checkbox>
    </td>
    <td class="text-center align-middle"><span class="reorder-handle" data-drag-handle title="ลากเพื่อจัดอันดับ">⋮⋮</span></td>
    <td class="align-middle" data-sequence>{{ $sequence ?? '' }}</td>
    <td class="text-start align-middle">
        <strong>{{ $subject->code }}: {{ $displayName }}</strong>
        @if (!empty($secondaryName))<div class="text-muted text-sm">{{ $secondaryName }}</div>@endif
    </td>
    <td class="align-middle">
        <div class="fw-semibold">{{ $subject->credits }}</div>
        <div class="text-muted text-sm">( {{ $subject->lecture_credits ?? 0 }} / {{ $subject->lab_credits ?? 0 }} / {{ $subject->self_study_credits ?? 0 }})</div>
    </td>
    <td class="align-middle">
        <div class="d-flex gap-2 align-items-center justify-content-center">
            <x-button type="warning" text="แก้ไข" class="text-sm" icon="fas fa-edit" data-id="{{ $subject->id }}" data-code="{{ $subject->code }}" data-name-th="{{ $subject->name_th }}" data-name-en="{{ $subject->name_en ?? '' }}" data-credits="{{ $subject->credits }}" data-lecture-credits="{{ $subject->lecture_credits ?? 0 }}" data-lab-credits="{{ $subject->lab_credits ?? 0 }}" data-self-study-credits="{{ $subject->self_study_credits ?? 0 }}" data-lecture-hours="{{ $subject->lecture_hours ?? 0 }}" data-lab-hours="{{ $subject->lab_hours ?? 0 }}" data-self-study-hours="{{ $subject->self_study_hours ?? 0 }}" data-role="subject-edit-trigger" />
            <x-button type="danger" text="ลบ" class="text-sm" icon="fas fa-trash-alt" data-delete-trigger data-delete-id="{{ $subject->id }}" />
        </div>
    </td>
</tr>
