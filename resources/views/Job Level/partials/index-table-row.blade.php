<tr class="reorder-row" data-id="{{ $jobLevel->id }}" data-resource-row data-resource-id="{{ $jobLevel->id }}">
    <td class="text-center align-middle">
        <input type="checkbox" class="form-check-input" value="{{ $jobLevel->id }}" aria-label="เลือก {{ $jobLevel->name }}" data-bulk-checkbox>
    </td>
    <td class="text-center align-middle"><span class="reorder-handle" data-drag-handle title="ลากเพื่อจัดอันดับ">⋮⋮</span></td>
    <td class="align-middle" data-sequence>{{ $sequence ?? '' }}</td>
    <td class="align-middle"><strong>{{ $jobLevel->name }}</strong></td>
    <td class="align-middle">
        <div class="d-flex gap-2 align-items-center">
            <x-button type="warning" text="แก้ไข" class="text-sm" icon="fas fa-edit" data-job-level-edit data-job-level-id="{{ $jobLevel->id }}" data-job-level-name="{{ $jobLevel->name }}" />
            <x-button type="danger" text="ลบ" class="text-sm" icon="fas fa-trash-alt" data-delete-trigger data-delete-id="{{ $jobLevel->id }}" />
        </div>
    </td>
</tr>
