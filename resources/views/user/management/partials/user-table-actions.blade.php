{{-- ปุ่มจัดการของแต่ละแถวในตารางผู้ใช้ --}}
<div class="d-flex justify-content-center gap-2 align-items-center">
    <x-button
        type="warning"
        text="แก้ไข"
        class="text-sm"
        icon="fas fa-edit"
        data-user-edit-trigger
        data-user="{{ $editUserPayloadEncoded }}"
    />
    <x-button
        type="danger"
        text="ลบ"
        buttonType="button"
        class="text-sm"
        icon="fas fa-trash-alt"
        data-user-delete-trigger
        data-user-id="{{ $employee['id'] }}"
    />
</div>
