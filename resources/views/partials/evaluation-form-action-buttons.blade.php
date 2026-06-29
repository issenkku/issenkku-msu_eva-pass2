{{-- ปุ่มย้อนกลับ บันทึกร่าง และยืนยันส่งฟอร์ม ใช้ร่วมกันหลายหน้า --}}
<div class="mt-8 flex justify-center gap-4">
    <x-button
        type="default"
        text="ย้อนกลับ"
        icon="fas fa-arrow-left"
        :href="$backHref" />

    @unless ($readonly)
    <x-button
        type="secondary"
        buttonType="submit"
        text="บันทึกร่าง"
        data-form-status-trigger
        data-form-status="{{ $draftStatus }}"
        icon="fas fa-save" />
        <x-button
            type="primary"
            buttonType="button"
            :text="$submitText ?? 'รับรองการประเมิน'"
            id="openModalBtn"
            icon="fa-solid fa-check-to-slot" />
    @endunless
</div>
