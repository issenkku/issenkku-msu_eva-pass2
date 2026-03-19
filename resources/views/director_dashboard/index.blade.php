@extends('layouts.app')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    <x-profile-card
        :user="$user"
        title="ข้อมูลกรรมการ" />

    <div class="py-4 rounded-xl lg:mx-10 my-4 lg:px-13">
        <div class="mb-4">
            <h2 class="text-2xl font-semibold text-gray-800">ภาพรวมงานพิจารณาของกรรมการ</h2>
            <p class="text-gray-600">เน้นดูว่างานไหนรอกรรมการพิจารณา งานไหนกำลังดำเนินการ และงานไหนส่งต่อผู้บริหารแล้ว</p>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 mb-8 border border-gray-200">
            <h2 class="text-xl font-bold mb-6 text-gray-800">กรองข้อมูลการประเมิน</h2>
            <form id="filterForm" method="get" class="space-y-1">
                <div class="flex flex-col md:flex-row md:space-x-4 space-y-3 md:space-y-0">
                    <div>
                        <label class="block mb-1 text-gray-700 font-medium text-sm">วันที่เริ่มต้น</label>
                        <input name="start_time" type="date" value="{{ request('start_time', '') }}"
                            class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-48" />
                    </div>
                    <div>
                        <label class="block mb-1 text-gray-700 font-medium text-sm">วันที่สิ้นสุด</label>
                        <input name="end_time" type="date" value="{{ request('end_time', '') }}"
                            class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-48" />
                    </div>
                    <div>
                        <label class="block mb-1 text-gray-700 text-sm">หน่วยงาน/แผนก</label>
                        <div class="relative">
                            <select name="department_name"
                                class="appearance-none text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full pr-10">
                                <option value="">ทุกหน่วยงาน</option>
                                @foreach ($departments ?? [] as $dept)
                                    <option value="{{ $dept->department_name }}"
                                        {{ request('department_name') == $dept->department_name ? 'selected' : '' }}>
                                        {{ $dept->department_name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex md:justify-end lg:justify-end space-x-2 pt-2">
                    <button type="button" onclick="resetFilters()"
                        class="px-5 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 transition">ล้างค่า</button>
                    <button type="submit"
                        class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">กรองข้อมูล</button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 rounded-2xl bg-white p-6 shadow-md border border-gray-100">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">ภาพรวมการพิจารณา</h3>
                        <p class="mt-1 text-sm text-gray-500">ใช้ติดตามคิวงานที่ต้องเข้าพิจารณาและงานที่รอส่งต่อให้ผู้บริหาร</p>
                    </div>
                    <div class="text-right">
                        <div class="text-4xl font-extrabold text-gray-900">{{ $progressPercent }}%</div>
                        <div class="text-sm text-gray-500">ปิดงานแล้ว {{ $completedCount }} จาก {{ $totalEvaluations }} รายการ</div>
                    </div>
                </div>

                <div class="mt-6 h-4 w-full overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full rounded-full bg-green-500" style="width: {{ min($progressPercent, 100) }}%;"></div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <x-summary-score
                        title="ผู้เข้าประเมิน"
                        :value="$totalEvaluatees"
                        subtitle="จำนวนบุคลากรที่อยู่ในรอบประเมิน"
                        color="blue"
                        icon="fas fa-users"
                        iconSize="text-3xl"
                    />

                    <x-summary-score
                        title="งานทั้งหมด"
                        :value="$totalEvaluations"
                        subtitle="รายการประเมินที่อยู่ในระบบ"
                        color="blue"
                        icon="fas fa-layer-group"
                        iconSize="text-3xl"
                    />

                    <x-summary-score
                        title="รอกรรมการพิจารณา"
                        :value="$awaitingDirectorCount"
                        subtitle="รายการที่รอการดำเนินการจากกรรมการ"
                        color="red"
                        icon="fas fa-stamp"
                        iconSize="text-3xl"
                    />

                    <x-summary-score
                        title="กำลังพิจารณา"
                        :value="$directorInProgressCount"
                        subtitle="รายการที่กรรมการเริ่มทำแล้ว"
                        color="yellow"
                        icon="fas fa-spinner"
                        iconSize="text-3xl"
                    />

                    <x-summary-score
                        title="ส่งต่อผู้บริหารแล้ว"
                        :value="$forwardedToManagerCount"
                        subtitle="รายการที่พ้นขั้นตอนกรรมการแล้ว"
                        color="green"
                        icon="fas fa-check-circle"
                        iconSize="text-3xl"
                    />
                </div>

                <div class="mt-6 rounded-2xl bg-gray-50 p-5">
                    <h4 class="text-lg font-semibold text-gray-900">สรุปสำหรับกรรมการ</h4>
                    <p class="mt-2 text-sm text-gray-600">
                        ขณะนี้มีงานใกล้ครบกำหนด {{ $dueSoonCount }} รายการ
                        และงานเลยกำหนด {{ $overdueCount }} รายการ
                        หากต้องเร่งติดตาม ให้ดูรายการด้านขวาเป็นหลัก
                    </p>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-md border border-gray-100">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">รายการที่ควรติดตาม</h3>
                        <p class="mt-1 text-sm text-gray-500">แสดงงานที่รอกรรมการพิจารณาก่อน และเรียงตามกำหนดเวลา</p>
                    </div>
                    <a href="#evaluation-table" class="text-sm font-medium text-blue-600 hover:text-blue-700">ดูทั้งหมด</a>
                </div>

                <div class="mt-5 space-y-4">
                    @forelse($followUpEvaluations->take(5) as $item)
                        <div class="rounded-2xl border border-gray-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-lg font-semibold text-gray-900">{{ $item['evaluatee_name'] }}</p>
                                    <p class="text-sm text-gray-500">สถานะ: {{ $item['pretty_status'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-900">{{ $item['progress_percent'] }}%</p>
                                </div>
                            </div>
                            <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-blue-500" style="width: {{ min($item['progress_percent'], 100) }}%;"></div>
                            </div>
                            <div class="mt-3 flex items-center justify-between text-sm text-gray-500">
                                <span>ครบกำหนด {{ $item['due_date'] }}</span>
                                <span>{{ $item['remaining_text'] }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-200 p-8 text-center text-sm text-gray-500">
                            ไม่มีรายการที่ต้องติดตาม
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div id="evaluation-table">
        <x-director-table
            :evaluations="$evaluations"
            :statusCounts="$statusCounts"
            :years="$years"
         />
    </div>
</div>

@if(session('success'))
    <div id="successMessage" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
        <div class="flex items-center space-x-3">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
@endif

<style>
@media (max-width: 768px) {
    .space-y-6 > * + * {
        margin-top: 1rem;
    }

    .max-w-4xl {
        max-width: 100%;
        padding: 0 1rem;
    }
}
</style>
@endsection

@push('scripts')
    <script>
        function resetFilters() {
            document.querySelector('input[name="start_time"]').value = '';
            document.querySelector('input[name="end_time"]').value = '';
            document.querySelector('select[name="department_name"]').value = '';
            document.getElementById('filterForm').submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('#successMessage, #warningMessage, #errorMessage');
            messages.forEach(function(message) {
                setTimeout(function() {
                    if (message.parentElement) {
                        message.style.transform = 'translateX(100%)';
                        setTimeout(function() {
                            if (message.parentElement) {
                                message.remove();
                            }
                        }, 300);
                    }
                }, 5000);
            });
        });
    </script>
@endpush
