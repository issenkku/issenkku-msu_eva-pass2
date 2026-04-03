{{-- ส่วนหัวของหน้ารายการเกณฑ์การประเมิน --}}
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-gray-800">
        กำหนดเกณฑ์การประเมิน
    </h2>
    <x-button
        type="primary"
        text="เพิ่มเกณฑ์"
        href="{{ route('criteria_config.create') }}"
        icon="fas fa-plus" />
</div>

<hr class="mb-8">
