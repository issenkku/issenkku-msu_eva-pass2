<tr class="reorder-row" data-id="{{ $position->id }}" data-resource-row data-resource-id="{{ $position->id }}">
    <td class="text-center align-middle">
        <input type="checkbox" class="form-check-input" value="{{ $position->id }}" aria-label="เลือก {{ $position->name }}" data-bulk-checkbox>
    </td>
    <td class="text-center align-middle"><span class="reorder-handle" data-drag-handle title="ลากเพื่อจัดอันดับ">⋮⋮</span></td>
    <td class="align-middle" data-sequence>{{ $sequence ?? '' }}</td>
    <td class="align-middle"><strong>{{ $position->name }}</strong></td>
    <td class="align-middle">
        <div class="d-flex gap-2 align-items-center">
            <x-button type="warning" text="แก้ไข" class="text-sm" icon="fas fa-edit" data-position-edit data-position-id="{{ $position->id }}" data-position-name="{{ $position->name }}" />
            <x-button type="danger" text="ลบ" class="text-sm" icon="fas fa-trash-alt" data-delete-trigger data-delete-id="{{ $position->id }}" />
        </div>
    </td>
</tr>
