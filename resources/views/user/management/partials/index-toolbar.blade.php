{{-- แถบเครื่องมือด้านบน แสดงจำนวนผู้ใช้และปุ่มการทำงานหลัก --}}
<div class="d-flex flex-column flex-md-row justify-between items-start md:items-center mb-4 gap-3">
    <h2 class="text-xl font-bold">รายชื่อเจ้าหน้าที่ทั้งหมด ({{ $users->total() }} คน)</h2>

    <div class="d-flex gap-2 align-items-center flex-wrap">
        <x-button
            type="danger"
            text="ลบรายการที่เลือก"
            class="hidden"
            icon="fas fa-trash-alt"
            data-user-bulk-delete-open
        />

        <x-button
            type="secondary"
            text="เพิ่มไฟล์เจ้าหน้าที่"
            data-import-modal-open
            data-action="{{ route('users.import') }}"
            icon="fas fa-file-import"
        />

        <x-button
            type="primary"
            text="เพิ่มเจ้าหน้าที่"
            data-create-modal-open
            data-action="{{ route('users.store') }}"
            icon="fas fa-user-plus"
        />
    </div>
</div>
