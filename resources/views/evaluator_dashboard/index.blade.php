@extends('layouts.app')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    <!-- Profile Card at Top -->
    <x-profile-card 
        :user="$user"
        title="ข้อมูลผู้ประเมิน"/>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pr-10 pl-10">
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pr-10 pl-10">
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

    <x-evaluator-table  
        :evaluations="$evaluations"  
        :statusCounts="$statusCounts"
        :years="$years" />
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
    </script>
@endpush
