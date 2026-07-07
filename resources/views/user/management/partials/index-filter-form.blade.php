{{-- ฟอร์มกรองข้อมูลผู้ใช้งานตามหน่วยงาน ประเภท และตำแหน่ง --}}
<form method="GET" class="flex flex-wrap gap-4 mb-4 items-end">
    <div class="flex items-center gap-2">
        <x-button
            type="primary"
            text="ทั้งหมด"
            href="{{ route('users.index') }}"
        />
    </div>

    <x-filter
        name="department_id"
        label="หน่วยงาน"
        :options="$departments->pluck('department_name', 'id')->toArray()"
    />

    <x-filter
        name="personnel_type"
        label="ประเภทเจ้าหน้าที่"
        :options="$personnelTypes"
    />

    <x-filter
        name="position_id"
        label="ตำแหน่งงาน"
        :options="$positions->pluck('name', 'id')->toArray()"
    />

    <x-filter
        name="job_level_id"
        label="ระดับตำแหน่งงาน"
        :options="$jobLevels->pluck('name', 'id')->toArray()"
    />
    <x-filter
        name="status"
        label="สถานะ"
        :options="['active' => 'Active', 'inactive' => 'Inactive']"
    />
</form>
