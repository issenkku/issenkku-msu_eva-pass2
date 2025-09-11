@extends('layouts.app')

@section('title', 'Dashboard - ระบบประเมิน')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    <!-- Profile Card at Top -->
    <x-profile-card 
        :user="$user"
        title="ข้อมูลผู้รับการประเมิน"/>
    
    <!-- Evaluation Header -->
    <div class="mx-5 px-10 pb-6 pt-6 rounded-2xl shadow-md border"
        style="background: linear-gradient(135deg, #f5f3ff 0%, #fff 50%, #fdf2f8 100%); border-color: #ede9fe;">
        
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-extrabold text-purple-700 tracking-wide flex items-center gap-2">
                <i class="fas fa-bell text-fuchsia-500"></i>
                การประเมินที่ยังไม่เสร็จ
            </h3>
        </div>

        <div class="grid grid-cols-1 gap-6">
            @forelse($unfinishedAssignments as $assignment)
                <x-evaluation-header
                    :title="$assignment['title']"
                    :period="$assignment['period']"
                    :deadline="$assignment['deadline']"
                    :daysLeft="$assignment['daysLeft']"
                    :evaluationId="$assignment['id']"
                />
            @empty
                <div class="col-span-full text-center py-10 bg-white rounded-xl shadow-inner border border-gray-100">
                    <p class="text-gray-500 text-lg">
                        🎉 ไม่มีการประเมินที่ค้างอยู่
                    </p>
                </div>
            @endforelse
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pr-10 pl-10">
        <div class="grid grid-cols-1 md:grid-rows-2 gap-2">
            <x-summary-score
                title="คะแนนสูงสุด"
                :value="$highestScore"
                subtitle="คะแนนสูงสุดทุกปีการประเมิน"
                color="blue"
                icon="fas fa-trophy"
                iconSize="text-3xl"
            />

            <x-summary-score
                title="คะแนนเฉลี่ย"
                :value="$averageScore"
                subtitle="คะแนนเฉลี่ยทุกปีการประเมิน"
                color="purple"
            />
        </div>

        <x-scatter-chart-component 
            :scatter-data="$scatterData"
            chart-id="myChart"
            title="กราฟการกระจายตัวของคะแนน"
        />

    </div>

    <!-- Evaluation Summary -->
    <x-evaluation-summary 
        :evaluations="$evaluations" 
        :status-counts="$statusCounts"
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