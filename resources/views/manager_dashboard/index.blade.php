@extends('layouts.app')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    <x-profile-card
        :user="$user"
        title="ข้อมูลผู้บริหาร" />

    <div class="py-4 rounded-xl lg:mx-10 my-4 lg:px-13">
        <div class="mb-4">
            <h2 class="text-2xl font-semibold text-gray-800">ภาพรวมงานรับรองผลของผู้บริหาร</h2>
            <p class="text-gray-600">เน้นดูว่างานไหนรอการรับรองจากคุณ งานไหนกำลังดำเนินการ และงานไหนปิดงานแล้ว</p>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 mb-8 border border-gray-200">
            <h2 class="text-xl font-bold mb-6 text-gray-800">กรองข้อมูลการประเมิน</h2>
            <form id="filterForm" method="get" class="space-y-1">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                    <div class="md:col-span-2 xl:col-span-1">
                        <label class="block mb-1 text-gray-700 font-medium text-sm">ค้นหาชื่อ / รหัสพนักงาน / ชื่องาน</label>
                        <input
                            name="search"
                            type="text"
                            value="{{ request('search', '') }}"
                            placeholder="พิมพ์เพื่อค้นหา"
                            class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full" />
                    </div>
                    <div>
                        <label class="block mb-1 text-gray-700 font-medium text-sm">วันที่เริ่มต้น</label>
                        <input
                            name="start_time"
                            type="date"
                            value="{{ request('start_time', '') }}"
                            class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full" />
                    </div>
                    <div>
                        <label class="block mb-1 text-gray-700 font-medium text-sm">วันที่สิ้นสุด</label>
                        <input
                            name="end_time"
                            type="date"
                            value="{{ request('end_time', '') }}"
                            class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full" />
                    </div>
                    <div>
                        <label class="block mb-1 text-gray-700 font-medium text-sm">หน่วยงาน / แผนก</label>
                        <select
                            name="department_name"
                            class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full">
                            <option value="">ทุกหน่วยงาน</option>
                            @foreach ($departments ?? [] as $dept)
                                <option value="{{ $dept->department_name }}" {{ request('department_name') == $dept->department_name ? 'selected' : '' }}>
                                    {{ $dept->department_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-gray-700 font-medium text-sm">สถานะ</label>
                        <select
                            name="status"
                            class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full">
                            <option value="">ทั้งหมด</option>
                            <option value="manager_waiting" {{ request('status') === 'manager_waiting' ? 'selected' : '' }}>รอผู้บริหารรับรอง</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>กำลังรับรอง</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>รับรองเสร็จสิ้น</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-gray-700 font-medium text-sm">ความเร่งด่วน</label>
                        <select
                            name="urgency"
                            class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full">
                            <option value="">ทั้งหมด</option>
                            <option value="due_soon" {{ request('urgency') === 'due_soon' ? 'selected' : '' }}>ใกล้ครบกำหนด</option>
                            <option value="overdue" {{ request('urgency') === 'overdue' ? 'selected' : '' }}>เลยกำหนด</option>
                            <option value="normal" {{ request('urgency') === 'normal' ? 'selected' : '' }}>ยังไม่เร่งด่วน</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-gray-700 font-medium text-sm">ปีประเมิน</label>
                        <select
                            name="year"
                            class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full">
                            <option value="">ทั้งหมด</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" {{ (string) request('year') === (string) $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row md:justify-end lg:justify-end gap-2 pt-3">
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
                        <h3 class="text-2xl font-bold text-gray-900">ภาพรวมการรับรองผล</h3>
                        <p class="mt-1 text-sm text-gray-500">ใช้ติดตามคิวงานที่กำลังรอผู้บริหารรับรองและงานที่ยังอยู่ก่อนถึงขั้นตอนของคุณ</p>
                    </div>
                    <div class="text-right">
                        <div class="text-4xl font-extrabold text-gray-900">{{ $progressPercent }}%</div>
                        <div class="text-sm text-gray-500">รับรองเสร็จแล้ว {{ $completedCount }} จาก {{ $totalEvaluations }} รายการ</div>
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
                        title="รอผู้บริหารรับรอง"
                        :value="$awaitingManagerCount"
                        subtitle="รายการที่รอการตัดสินใจจากคุณ"
                        color="red"
                        icon="fas fa-signature"
                        iconSize="text-3xl"
                    />

                    <x-summary-score
                        title="กำลังรับรอง"
                        :value="$managerInProgressCount"
                        subtitle="รายการที่คุณเริ่มพิจารณาแล้ว"
                        color="yellow"
                        icon="fas fa-spinner"
                        iconSize="text-3xl"
                    />

                    <x-summary-score
                        title="รับรองเสร็จสิ้น"
                        :value="$completedCount"
                        subtitle="รายการที่ปิดงานเรียบร้อยแล้ว"
                        color="green"
                        icon="fas fa-check-circle"
                        iconSize="text-3xl"
                    />
                </div>

            
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-md border border-gray-100">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">รายการที่ควรติดตาม</h3>
                        <p class="mt-1 text-sm text-gray-500">แสดงงานที่ใกล้ถึงคิวผู้บริหารและงานที่รอการรับรองก่อน</p>
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
        <x-manager-table
            :evaluations="$evaluations"
            :statusCounts="$statusCounts"
            :years="$years" />
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
            document.querySelector('input[name="search"]').value = '';
            document.querySelector('input[name="start_time"]').value = '';
            document.querySelector('input[name="end_time"]').value = '';
            document.querySelector('select[name="department_name"]').value = '';
            document.querySelector('select[name="status"]').value = '';
            document.querySelector('select[name="urgency"]').value = '';
            document.querySelector('select[name="year"]').value = '';
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
