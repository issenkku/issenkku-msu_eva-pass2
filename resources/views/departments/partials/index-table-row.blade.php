<tr class="reorder-row" data-id="{{ $department->id }}" data-resource-row data-resource-id="{{ $department->id }}">
    <td class="text-center align-middle">
        <input type="checkbox" class="form-check-input" value="{{ $department->id }}" aria-label="เลือก {{ $department->department_name }}" data-bulk-checkbox>
    </td>
    <td class="text-center align-middle"><span class="reorder-handle" data-drag-handle title="ลากเพื่อจัดอันดับ">⋮⋮</span></td>
    <td class="align-middle" data-sequence>{{ $sequence ?? '' }}</td>
    <td class="align-middle"><strong>{{ $department->department_name }}</strong></td>
    <td class="align-middle">
        <div class="d-flex gap-2 align-items-center">
            <x-button type="warning" text="แก้ไข" class="text-sm" icon="fas fa-edit" data-department-edit data-department-id="{{ $department->id }}" data-department-name="{{ $department->department_name }}" />
            <x-button type="danger" text="ลบ" class="text-sm" icon="fas fa-trash-alt" data-delete-trigger data-delete-id="{{ $department->id }}" />
        </div>
    </td>
</tr>
