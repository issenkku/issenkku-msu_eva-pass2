{{-- แผงตัวกรองของหน้า dashboard ใช้ร่วมกันระหว่าง evaluator/director/manager --}}
@php
    $searchLabel = $searchLabel ?? 'ค้นหาชื่อ / รหัสพนักงาน / ชื่องาน';
    $searchPlaceholder = $searchPlaceholder ?? 'พิมพ์เพื่อค้นหา';
    $filterTitle = $filterTitle ?? 'กรองข้อมูลการประเมิน';
    $activeText = $activeText ?? 'มีตัวกรองที่กำลังใช้งานอยู่ กดเพื่อแก้ไขหรือล้างค่า';
    $inactiveText = $inactiveText ?? 'กดเพื่อแสดงตัวเลือกการกรองเพิ่มเติม';
    $showDepartment = $showDepartment ?? false;
    $statusOptions = $statusOptions ?? [];
    $urgencyOptions = $urgencyOptions ?? [
        'due_soon' => 'ใกล้ครบกำหนด',
        'overdue' => 'เลยกำหนด',
        'normal' => 'ยังไม่เร่งด่วน',
    ];
    $years = $years ?? collect();
    $departments = $departments ?? [];
@endphp

<div class="mb-8 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-md">
    <button
        type="button"
        id="{{ $scope }}FilterToggle"
        class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left transition hover:bg-slate-50">
        <div class="flex items-center gap-2.5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2l-7 7v5l-4 2v-7L3 6V4z" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold leading-tight text-gray-800">{{ $filterTitle }}</h2>
                <p class="mt-0.5 text-xs text-gray-500 sm:text-sm">{{ $hasFilters ? $activeText : $inactiveText }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if ($hasFilters)
                <span class="hidden rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100 sm:inline-flex">กำลังกรอง</span>
            @endif
            <svg id="{{ $scope }}FilterChevron" class="h-4.5 w-4.5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </button>

    <div id="{{ $scope }}FilterPanel" class="hidden border-t border-gray-100 px-5 pb-5 pt-2">
        <form id="filterForm" method="get" class="space-y-1">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                <div class="md:col-span-2 xl:col-span-1">
                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ $searchLabel }}</label>
                    <input
                        name="search"
                        type="text"
                        value="{{ request('search', '') }}"
                        placeholder="{{ $searchPlaceholder }}"
                        class="w-full rounded-lg bg-gray-100 px-4 py-2 text-black focus:border-blue-500 focus:ring-blue-500" />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">วันที่เริ่มต้น</label>
                    <input name="start_time" type="date" value="{{ request('start_time', '') }}" class="w-full rounded-lg bg-gray-100 px-4 py-2 text-black focus:border-blue-500 focus:ring-blue-500" />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">วันที่สิ้นสุด</label>
                    <input name="end_time" type="date" value="{{ request('end_time', '') }}" class="w-full rounded-lg bg-gray-100 px-4 py-2 text-black focus:border-blue-500 focus:ring-blue-500" />
                </div>

                @if ($showDepartment)
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">หน่วยงาน / แผนก</label>
                        <select name="department_name" class="w-full rounded-lg bg-gray-100 px-4 py-2 text-black focus:border-blue-500 focus:ring-blue-500">
                            <option value="">ทุกหน่วยงาน</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->department_name }}" @selected(request('department_name') == $department->department_name)>{{ $department->department_name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">สถานะ</label>
                    <select name="status" class="w-full rounded-lg bg-gray-100 px-4 py-2 text-black focus:border-blue-500 focus:ring-blue-500">
                        <option value="">ทั้งหมด</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">ความเร่งด่วน</label>
                    <select name="urgency" class="w-full rounded-lg bg-gray-100 px-4 py-2 text-black focus:border-blue-500 focus:ring-blue-500">
                        <option value="">ทั้งหมด</option>
                        @foreach ($urgencyOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('urgency') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">ปีประเมิน</label>
                    <select name="year" class="w-full rounded-lg bg-gray-100 px-4 py-2 text-black focus:border-blue-500 focus:ring-blue-500">
                        <option value="">ทั้งหมด</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-col gap-2 pt-3 md:flex-row md:justify-end">
                <button type="button" data-dashboard-reset-filters class="rounded-lg bg-gray-200 px-5 py-2 text-gray-800 transition hover:bg-gray-300">ล้างค่า</button>
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-white transition hover:bg-blue-700">กรองข้อมูล</button>
            </div>
        </form>
    </div>
</div>
