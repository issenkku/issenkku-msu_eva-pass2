{{-- ปุ่มดำเนินการ --}}
<div class="action-section">
    <x-button
        type="secondary"
        text="ย้อนกลับ"
        icon="fas fa-arrow-left"
        href="{{ route('evaluator.index') }}"
    />
    <x-button
        type="primary"
        buttonType="button"
        text="รับรองผล"
        icon="fas fa-check-circle"
        onclick="confirmSubmit()"
    />
</div>
