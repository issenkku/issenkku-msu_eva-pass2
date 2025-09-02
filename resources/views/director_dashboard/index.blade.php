@extends('layouts.app')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    <!-- Profile Card at Top -->
    <x-profile-card 
        :user="$user"
        title="ข้อมูลกรรมการ"/>

     <div class=" py-4 rounded-xl lg:mx-10 my-4 lg:px-13">
        <div class="mb-4">
            <h2 class="text-2xl font-semibold text-gray-800">สรุปผลการประเมิน</h2>
            <p class="text-gray-600">ภาพรวมของผลการประเมินทั้งหมด</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6 mb-8 animate-fadeIn border border-gray-200">
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
                        <select name="department_name"
                            class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full">
                            <option value="">ทุกหน่วยงาน</option>
                            @foreach ($departments ?? [] as $dept)
                                <option value="{{ $dept->department_name }}"
                                    {{ request('department_name') == $dept->department_name ? 'selected' : '' }}>
                                    {{ $dept->department_name }}
                                </option>
                            @endforeach
                        </select>
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
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <x-summary-score
                title="จำนวนผู้เข้ารับการประเมิน"
                :value="$totalEvaluatees"
                subtitle="จำนวนผู้เข้าร่วมการประเมินทั้งหมด"
                color="blue"
                icon="fas fa-users"
                iconSize="text-3xl"
            />

            <x-summary-score
                title="คะแนนเฉลี่ย"
                :value="$averageScore"
                subtitle="คะแนนเฉลี่ยทุกปีการประเมิน"
                color="purple"
            />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-bar-chart 
                chart-id="statusChart"
                title="สถานะผลการประเมิน"
                :data="$chartData"
                :labels="$statusLabels"
                :colors="$statusColors"
            />

            <x-scatter-chart-component 
                :scatter-data="$scatterData"
                chart-id="myChart"
                title="กราฟการกระจายตัวของคะแนน"
            />

        </div>
    </div>

    <x-director-table  
        :evaluations="$evaluations"  
        :statusCounts="$statusCounts"
        :years="$years"
     />
</div>

<!-- Mobile-friendly spacing -->
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
        $(document).ready(function() {
            // Simple fade in animation
            $('.table-card, .sidebar-card').css('opacity', '0').animate({
                opacity: 1
            }, 200);

            // Simple hover effect for table rows
            $('.evaluation-table tbody tr').hover(
                function() {
                    $(this).addClass('hover-row');
                },
                function() {
                    $(this).removeClass('hover-row');
                }
            );

            // Auto refresh every 5 minutes
            setInterval(function() {
                console.log('Auto refreshing data...');
            }, 300000);
        });

        function resetFilters() {
            document.querySelector('input[name="start_time"]').value = '';
            document.querySelector('input[name="end_time"]').value = '';
            document.getElementById('filterForm').submit();
            document.querySelector('select[name="department_name"]').value = '';
        }
    </script>
@endpush
