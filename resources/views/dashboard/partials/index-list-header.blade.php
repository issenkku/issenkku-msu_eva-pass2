{{-- ส่วนหัวของตารางผลการประเมินรายบุคคล --}}
<div class="evaluation-list-header px-6 pt-4">
    <h3
        id="evaluation-list-heading"
        tabindex="-1"
        class="mb-2 text-lg font-semibold text-gray-900 focus:outline-none sm:mb-0">
        ผลการประเมินรายบุคคล
    </h3>
    <div class="evaluation-list-toolbar flex flex-col border-b pb-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="evaluation-toolbar-search flex flex-col gap-3 sm:flex-row">
            <div class="relative">
                <x-search-bar placeholder="ค้นหาในหน้านี้: ชื่อ, ชื่องาน, ผู้ประเมิน..." />
            </div>
        </div>
        <div class="evaluation-toolbar-actions flex flex-wrap justify-between gap-2">
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
