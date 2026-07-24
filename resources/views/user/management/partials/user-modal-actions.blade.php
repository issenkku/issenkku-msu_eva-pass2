{{-- ปุ่มท้าย modal ใช้ยกเลิกหรือบันทึกข้อมูลผู้ใช้หลังตรวจสอบครบแล้ว --}}
<div data-user-modal-actions class="flex flex-none justify-center gap-4 border-t bg-white px-4 py-4 sm:px-6">
    <x-button type="secondary" text="ย้อนกลับ" data-user-modal-close icon="fas fa-arrow-left" />
    <x-button type="primary" text="บันทึก" icon="fas fa-save" buttonType="submit" />
</div>
