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

        <form
            method="POST"
            action="{{ route('users.bulk-status') }}"
            class="hidden"
            data-user-bulk-status-form
        >
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="" data-user-bulk-status-value>
            <div data-user-bulk-status-selected-inputs></div>

            <div class="dropdown">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg border-2 border-purple-500 bg-white px-4 py-2 font-semibold text-purple-500 hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2"
                    id="bulk-user-status-menu"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <i class="fas fa-user-check" aria-hidden="true"></i>
                    <span>เปลี่ยนสถานะ</span>
                    <i class="fas fa-chevron-down text-xs" aria-hidden="true"></i>
                </button>
                <ul class="dropdown-menu" aria-labelledby="bulk-user-status-menu">
                    <li>
                        <button class="dropdown-item" type="submit" data-user-bulk-status-option value="active">
                            Active
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item" type="submit" data-user-bulk-status-option value="inactive">
                            Inactive
                        </button>
                    </li>
                </ul>
            </div>
        </form>

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
