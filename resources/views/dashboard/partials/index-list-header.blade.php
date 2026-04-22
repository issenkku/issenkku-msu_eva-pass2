{{-- ส่วนหัวของตารางผลการประเมินรายบุคคล --}}
<div class="px-6 pt-4">
    <h3 class="mb-2 text-lg font-semibold text-gray-900 sm:mb-0">ผลการประเมินรายบุคคล</h3>
    <div class="flex flex-col border-b pb-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="relative">
                <x-search-bar placeholder="ค้นหาชื่อ, รายงาน..." />
            </div>
        </div>
        <div class="flex flex-wrap justify-between gap-2">
            <x-export-button
                :route="route('admin.export.reports', request()->query())"
                label="ส่งออกExcelทั้งหมด" />
            <x-filter-badge-single
                name="year"
                placeholder="ปีการประเมินทั้งหมด"
                :options="$years->mapWithKeys(fn($y) => [$y => $y + 543])->toArray()" />
        </div>
    </div>
</div>
