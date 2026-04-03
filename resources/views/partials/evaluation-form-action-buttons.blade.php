{{-- ปุ่มย้อนกลับ บันทึกร่าง และปุ่มยืนยันส่งฟอร์ม ใช้ร่วมกันระหว่างหลายหน้า --}}
<div class="flex justify-center gap-4 mt-8">
    <x-button
        type="default"
        text="ย้อนกลับ"
        icon="fas fa-arrow-left"
        :href="$backHref" />

    @unless($readonly)
        <x-button
            type="secondary"
            buttonType="submit"
            text="บันทึกร่าง"
            onclick="setFormStatus('{{ $draftStatus }}')"
            icon="fas fa-save" />
        <x-button
            type="primary"
            buttonType="button"
            :text="$submitText ?? 'รับรองการประเมิน'"
            id="openModalBtn"
            icon="fa-solid fa-check-to-slot" />
    @endunless
</div>
