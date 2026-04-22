{{-- ปุ่มจัดการของแต่ละแถวในตารางผู้ใช้ --}}
<div class="d-flex justify-content-center gap-2 align-items-center">
    <x-button
        type="warning"
        text="แก้ไข"
        class="text-sm"
        icon="fas fa-edit"
        onclick="openEditModalFromButton(this)"
        data-user="{{ $editUserPayloadEncoded }}"
    />
    <x-button
        type="danger"
        text="ลบ"
        buttonType="submit"
        class="text-sm"
        icon="fas fa-trash-alt"
        onclick="confirmDelete({{ $employee['id'] }})"
    />
</div>
