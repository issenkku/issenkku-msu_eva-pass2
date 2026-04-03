{{-- ปุ่มท้าย modal ใช้ยกเลิกหรือบันทึกข้อมูลผู้ใช้หลังตรวจสอบครบแล้ว --}}
<div class="mt-8 flex justify-center gap-4">
    <x-button type= defualt text="ย้อนกลับ" onclick="closeModal()" icon="fas fa-arrow-left" />
    <x-button type="primary" text="บันทึก" icon="fas fa-save" buttonType="submit" />
</div>
