{{-- แผงกรองข้อมูลของหน้า dashboard/admin --}}
<div class="mb-6 animate-fadeIn overflow-hidden rounded-xl bg-white shadow-lg">
    <button
        type="button"
        id="dashboardFilterToggle"
        aria-controls="dashboardFilterPanel"
        aria-expanded="{{ $hasDashboardFilters ? 'true' : 'false' }}"
        class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left transition hover:bg-slate-50">
        <div class="flex items-center gap-2.5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2l-7 7v5l-4 2v-7L3 6V4z" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold leading-tight text-gray-800">กรองข้อมูลการประเมิน</h2>
                <p class="mt-0.5 text-xs text-gray-500 sm:text-sm">
                    {{ $hasDashboardFilters ? 'มีตัวกรองที่กำลังใช้งานอยู่ กดเพื่อแก้ไขหรือรีเซ็ต' : 'กดเพื่อแสดงตัวเลือกการกรองเพิ่มเติม' }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if ($hasDashboardFilters)
                <span class="hidden rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100 sm:inline-flex">
                    กำลังกรอง
                </span>
            @endif
            <svg id="dashboardFilterChevron" class="h-4.5 w-4.5 text-gray-500 transition-transform duration-200 {{ $hasDashboardFilters ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </button>

    <div
        id="dashboardFilterPanel"
        aria-hidden="{{ $hasDashboardFilters ? 'false' : 'true' }}"
        class="{{ $hasDashboardFilters ? '' : 'hidden' }} border-t border-gray-100 px-5 pb-5 pt-2">
        <form id="filterForm" method="get" class="space-y-1">
            <div class="flex flex-col space-y-3 md:flex-row md:space-x-4 md:space-y-0">
                <div>
                    <label for="dashboard_start_time" class="mb-1 block text-sm font-medium text-gray-700">วันที่เริ่มต้น</label>
                    <input
                        id="dashboard_start_time"
                        name="start_time"
                        type="date"
                        value="{{ request('start_time', '') }}"
                        class="w-48 rounded-lg bg-gray-100 px-4 py-2 text-black focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label for="dashboard_end_time" class="mb-1 block text-sm font-medium text-gray-700">วันที่สิ้นสุด</label>
                    <input
                        id="dashboard_end_time"
                        name="end_time"
                        type="date"
                        value="{{ request('end_time', '') }}"
                        class="w-48 rounded-lg bg-gray-100 px-4 py-2 text-black focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label for="dashboard_department_name" class="mb-1 block text-sm text-gray-700">หน่วยงาน/แผนก</label>
                    <div class="relative">
                        <select
                            id="dashboard_department_name"
                            name="department_name"
                            class="w-full appearance-none rounded-lg bg-gray-100 px-4 py-2 pr-10 text-black focus:border-blue-500 focus:ring-blue-500">
                            <option value="">ทุกหน่วยงาน</option>
                            @foreach ($departments ?? [] as $dept)
                                <option value="{{ $dept->department_name }}" {{ request('department_name') == $dept->department_name ? 'selected' : '' }}>
                                    {{ $dept->department_name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
                            <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div>
                    <label for="dashboard_position_name" class="mb-1 block text-sm text-gray-700">ตำแหน่งงาน</label>
                    <div class="relative">
                        <select
                            id="dashboard_position_name"
                            name="position_name"
                            class="w-full appearance-none rounded-lg bg-gray-100 px-4 py-2 pr-10 text-black focus:border-blue-500 focus:ring-blue-500">
                            <option value="">ทุกตำแหน่งงาน</option>
                            @foreach ($positions ?? [] as $position)
                                <option value="{{ $position->name }}" {{ request('position_name') == $position->name ? 'selected' : '' }}>
                                    {{ $position->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
                            <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col space-y-3 pt-2 md:flex-row md:justify-end md:space-x-2 md:space-y-0 lg:justify-end">
                <button
                    type="button"
                    data-reset-filters
                    class="rounded-lg bg-gray-200 px-5 py-2 text-gray-800 transition hover:bg-gray-300">
                    ล้างค่า
                </button>
                <button
                    type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-white transition hover:bg-blue-700">
                    กรองข้อมูล
                </button>
            </div>
        </form>
    </div>
</div>
