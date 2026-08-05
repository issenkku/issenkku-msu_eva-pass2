@php
    $reportTitle = $criteriaVersion->reportDatas->first()?->report_title ?: 'ไม่ระบุชื่อรายงาน';
    $creatorName = $criteriaVersion->createdByUser?->name ?: '-';
@endphp

<div
    class="bg-gray-100 p-6 rounded-lg shadow-sm flex flex-col justify-between"
    data-resource-row
    data-resource-id="{{ $criteriaVersion->id }}"
>
    <h3 class="text-xl font-semibold text-gray-900">{{ $reportTitle }}</h3>
    <p class="text-sm text-gray-600 mb-4">สร้างโดย: <span class="font-semibold">{{ $creatorName }}</span></p>
    <div class="flex space-x-2 items-center justify-center">
        <x-button
            type="secondary"
            text="คัดลอก"
            buttonType="button"
            icon="fas fa-copy"
            data-copy-criteria-version="{{ $criteriaVersion->id }}"
        />
        <x-button
            type="warning"
            text="แก้ไข"
            icon="fas fa-edit"
            href="{{ route('criteria_config.edit', $criteriaVersion->id) }}"
        />
        <x-button
            type="danger"
            text="ลบ"
            buttonType="button"
            icon="fas fa-trash-alt"
            data-delete-criteria-version="{{ $criteriaVersion->id }}"
        />
    </div>
</div>
