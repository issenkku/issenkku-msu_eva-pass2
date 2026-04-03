{{-- ปุ่มส่งฟอร์ม ใช้ร่วมได้ทั้งกรณีสร้างครั้งแรกและอัปเดตข้อมูลเดิม --}}
<div class="text-center mt-4">
    @if(isset($settings) && $setting)
        <x-button
            type="primary"
            text="อัปเดตข้อมูล"
            icon="fas fa-save"
            buttonType="submit" />
    @else
        <x-button
            type="primary"
            text="บันทึกข้อมูล"
            icon="fas fa-save"
            buttonType="submit" />
    @endif
</div>
